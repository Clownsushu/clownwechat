<?php
namespace clown\wechat\pay\wxpay;

use clown\wechat\pay\Base;
use clown\wechat\pay\Pay;
use WeChatPay\Crypto\AesGcm;
use WeChatPay\Crypto\Rsa;
use WeChatPay\Formatter;

class JsapiPay extends Base implements Pay
{
    /**
     * 构造函数
     * @param $appid string 微信appid
     * @param $mch_id string 商户号id
     * @param $mch_key string 商户号秘钥
     * @param $notify_url string 回调通知地址
     * @param $serial_number string 支付证书序列号
     * @param $apiclient_key_path string apiclient_key.pem文件的相对路径
     * @param $wechatpay_path string wechatpay.pem文件的相对路径
     * @param bool $debug 是否开启调试模式
     * @throws \Exception
     */
    public function __construct(
        $appid = '',
        $mch_id = '',
        $mch_key = '',
        $notify_url = '',
        $serial_number = '',
        $apiclient_key_path = '',
        $wechatpay_path = '',
        $debug = false)
    {
        parent::__construct($appid, $mch_id, $mch_key, $notify_url, $serial_number, $apiclient_key_path, $wechatpay_path, $debug);
    }

    /**
     * 获取签名算法
     * @param $prepay_id string 预支付id
     * @return array
     */
    public function getSign($prepay_id = '')
    {
        $merchantPrivateKeyFilePath = 'file://' . $this->apiclient_key_path;

        $merchantPrivateKeyInstance = Rsa::from($merchantPrivateKeyFilePath);

        $params = [
            'appId'     => $this->appid,
            'timeStamp' => (string)Formatter::timestamp(),
            'nonceStr'  => Formatter::nonce(),
            'package'   => $prepay_id,
        ];
        $params += ['paySign' => Rsa::sign(
            Formatter::joinedByLineFeed(...array_values($params)),
            $merchantPrivateKeyInstance
        ), 'signType' => 'RSA'];

        return $params;
    }

    /**
     * 统一下单
     * @param array $data 支付请求参数
     * @return array
     * @throws \Exception
     */
    public function getPrePay(array $data)
    {
        // TODO: Implement getPrePay() method.
        if(empty($data['description'])) throw new \Exception("请传入商品简介description");

        if(empty($data['out_trade_no'])) throw new \Exception("请传入商品订单号out_trade_no");

        if(empty($data['total'])) throw new \Exception("请传入支付金额, 单位:分total");

        if(empty($data['openid'])) throw new \Exception("请传入支付用户openid");

        try{
            $resp = $this->instance->chain('v3/pay/transactions/jsapi')
                ->post([ 'json' => [
                    'mchid'        => $this->mch_id,
                    'out_trade_no' => $data['out_trade_no'],
                    'appid'        => $this->appid,
                    'description'  => $data['description'],
                    'notify_url'   => $this->notify_url,
                    'amount'       => [
                        'total'    => $data['total'],
                        'currency' => 'CNY'
                    ],
                    'payer' => [
                        'openid' => $data['openid'],
                    ],
                ]]);
            $result = json_decode($resp->getBody(), true);

            if(!isset($result['prepay_id'])){
                throw new \Exception('没有返回预支付id, 返回内容: ' . json_encode($result));
            }

            //调用签名并返回
            return $this->getSign(http_build_query($result));
        }catch (\Exception $e){
            throw new \Exception('下单失败: ' . $e->getMessage());
        }
    }

    /**
     * 支付通知
     * @param $inBody array 微信返回的内容
     * @return array|false 正常返回内容如下: {"mchid":"xxx","appid":"xxx","out_trade_no":"xxxx","transaction_id":"xxx","trade_type":"JSAPI","trade_state":"SUCCESS","trade_state_desc":"支付成功","bank_type":"OTHERS","attach":"","success_time":"2023-12-13T15:33:57+08:00","payer":{"openid":"xxxx"},"amount":{"total":1,"payer_total":1,"currency":"CNY","payer_currency":"CNY"}}
     */
    public function notify($inBody = [])
    {
        // TODO: Implement notify() method.
        //返回签名
        $inWechatpaySignature = $_SERVER['HTTP_WECHATPAY_SIGNATURE'] ?? '';// 请根据实际情况获取
        //返回时间戳
        $inWechatpayTimestamp = $_SERVER['HTTP_WECHATPAY_TIMESTAMP'] ?? '';// 请根据实际情况获取
        //证书号
        $inWechatpaySerial = $_SERVER['HTTP_WECHATPAY_SERIAL'] ?? '';// 请根据实际情况获取
        //随机字符串
        $inWechatpayNonce = $_SERVER['HTTP_WECHATPAY_NONCE'] ?? '';// 请根据实际情况获取
        //返回的数据流
        $inBody = empty($inBody) ? file_get_contents('php://input') : $inBody;

        $apiv3Key = $this->mch_key;// 在商户平台上设置的APIv3密钥

        // 根据通知的平台证书序列号，查询本地平台证书文件，
        // 假定为 `/path/to/wechatpay/inWechatpaySerial.pem`
        $platformPublicKeyInstance = Rsa::from('file://' . $this->wechatpay_path, Rsa::KEY_TYPE_PUBLIC);

        // 检查通知时间偏移量，允许5分钟之内的偏移
        $timeOffsetStatus = 300 >= abs(Formatter::timestamp() - (int)$inWechatpayTimestamp);
        $verifiedStatus = Rsa::verify(
        // 构造验签名串
            Formatter::joinedByLineFeed($inWechatpayTimestamp, $inWechatpayNonce, $inBody),
            $inWechatpaySignature,
            $platformPublicKeyInstance
        );

        if ($timeOffsetStatus && $verifiedStatus) {
            // 转换通知的JSON文本消息为PHP Array数组
            $inBodyArray = (array)json_decode($inBody, true);
            // 使用PHP7的数据解构语法，从Array中解构并赋值变量
            ['resource' => [
                'ciphertext'      => $ciphertext,
                'nonce'           => $nonce,
                'associated_data' => $aad
            ]] = $inBodyArray;
            // 加密文本消息解密
            $inBodyResource = AesGcm::decrypt($ciphertext, $apiv3Key, $nonce, $aad);
            // 把解密后的文本转换为PHP Array数组
            $inBodyResourceArray = (array)json_decode($inBodyResource, true);

            if($inBodyResourceArray['mchid'] == $this->mch_id &&
                $inBodyResourceArray['appid'] == $this->appid &&
                $inBodyResourceArray['trade_state'] == 'SUCCESS'){
                return $inBodyResourceArray;
            }
        }

        return false;
    }

    /**
     * 订单查询
     * @param $transaction_id string 微信返回的订单号notify方法里返回的transaction_id
     * @return mixed
     * 如果返回存在code, 那么就是查询失败,例如: ["code" => "PARAM_ERROR", "message" => "微信订单号非法"]
     * 如果返回存在out_trade_no, 且存在trade_state 那么就是查询成功
     */
    public function selectOrder($transaction_id = '')
    {
        // TODO: Implement selectOrder() method.
        $promise = $this->instance
            ->v3->pay->transactions->id->_transaction_id_
            ->getAsync([
                // Query 参数
                'query' => ['mchid' => $this->mch_id],
                // 变量名 => 变量值
                'transaction_id' => $transaction_id,
            ])->then(static function($response) {
                // 正常逻辑回调处理
                return $response;
            })
            ->otherwise(static function($e) {
                // 异常错误处理
                $r = $e->getResponse();
                return $r;
            })
            ->wait();

        return json_decode($promise->getBody(), true);;
    }

    /**
     * 订单关闭
     * @param $out_trade_no string 平台订单号
     * @return mixed
     * 正常返回null
     * 异常返回
     * [
     *      "code" => "PARAM_ERROR",
     *      "detail" => array:2 [▼
     *          "location" => "uri_template",
     *          "value" => 35
     *      ],
     *      "message" => "输入源“/uri_template/out_trade_no”映射到值字段“商户订单号”字符串规则校验失败，字节数 35，大于最大值 32"
     * ]
     */
    public function closeOrder($out_trade_no = '')
    {
        $promise = $this->instance
            ->v3->pay->transactions->outTradeNo->_out_trade_no_->close
            ->postAsync([
                // 请求消息
                'json' => ['mchid' => $this->mch_id],
                // 变量名 => 变量值
                'out_trade_no' => $out_trade_no,
            ])->then(static function($response) {
                // 正常逻辑回调处理
                return $response;
            })->otherwise(static function($e) {
                // 异常错误处理
                $r = $e->getResponse();
                return $r;
            })
            ->wait();

        return json_decode($promise->getBody(), true);
    }

    /**
     * 退款
     * @param array $data 退款请求参数
     * @return mixed
     * @throws \Exception
     * 异常返回 ["code" => "INVALID_REQUEST", "message" => "支付单号校验不一致，请核实后再试"]
     * 正常返回
     */
    public function refunds(array $data)
    {
        if(empty($data['transaction_id']) && empty($data['out_trade_no'])){
            throw new \Exception("请传入平台订单号out_trade_no或微信订单号transaction_id,二选一");
        }

        if(empty($data['out_refund_no'])) throw new \Exception("请传入退款订单号out_refund_no");

        if(empty($data['total'])) throw new \Exception("请传入支付金额total, 单位: 分");

        if(empty($data['refund'])) throw new \Exception("请传入退款金额refund, 单位: 分");

        $json = [
            'out_refund_no' => $data['out_refund_no'],
            'amount' => [
                'refund' => $data['refund'],
                'total' => $data['total'],
                'currency' => 'CNY',
            ],
        ];
        //微信订单号
        if(!empty($data['transaction_id'])){
            $json['transaction_id'] = $data['transaction_id'];
        }
        //平台订单号
        if(!empty($data['out_trade_no'])){
            $json['out_trade_no'] = $data['out_trade_no'];
        }
        //退款原因
        if(!empty($data['reason'])){
            $json['reason'] = $data['reason'];
        }
        //退款通知地址
        if(!empty($data['notify_url'])){
            $json['notify_url'] = $data['notify_url'];
        }
        //退款资金来源
        if(!empty($data['funds_account'])){
            $json['funds_account'] = $data['funds_account'];
        }
        //退款商品
        if(!empty($data['goods_detail'])){
            $json['goods_detail'] = $data['goods_detail'];
        }

        $res = $this->instance
            ->chain('v3/refund/domestic/refunds')
            ->postAsync([
                'json' => $json,
            ])
            ->then(static function($response) {
                // 正常逻辑回调处理
                return $response;
            })
            ->otherwise(static function($e) {
                // 异常错误处理
                $r = $e->getResponse();
                return $r;
            })
            ->wait();

        return json_decode($res->getBody(), true);
    }

    /**
     * 退款查询
     * @param $out_refund_no string 平台退款订单号
     * @return mixed
     * @throws \Exception
     */
    public function refundQuery($out_refund_no = '')
    {
        if(empty($out_refund_no)) throw new \Exception("请传入退款订单号out_refund_no");

        $res = $this->instance
            ->chain('v3/refund/domestic/refunds/' . $out_refund_no)
            ->getAsync()
            ->then(static function($response) {
                // 正常逻辑回调处理
                return $response;
            })->otherwise(static function($e) {
                // 异常错误处理
                $r = $e->getResponse();
                return $r;
            })
            ->wait();

        return json_decode($res->getBody(), true);
    }


}