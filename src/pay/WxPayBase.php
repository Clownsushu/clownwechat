<?php
namespace clown\wechat\pay;


use WeChatPay\Builder;
use WeChatPay\Crypto\AesGcm;
use WeChatPay\Crypto\Rsa;
use WeChatPay\Formatter;
use WeChatPay\Util\PemUtil;

/**
 * 微信支付基础类
 */
class WxPayBase
{
    /**
     * @var mixed|string 微信appid
     */
    protected $appid = '';

    /**
     * @var mixed|string 商户号id
     */
    protected $mch_id = '';

    /**
     * @var mixed|string 商户号秘钥
     */
    protected $mch_key = '';

    /**
     * @var mixed|string 支付通知地址
     */
    protected $notify_url = '';

    /**
     * @var mixed|string 证书序列号
     */
    protected $serial_number = '';

    /**
     * @var mixed|string apiclient_key.pem文件的相对路径
     */
    protected $apiclient_key_path = '';

    /**
     * @var string wechatpay.pem文件的相对路径
     */
    protected $wechatpay_path = '';

    /**
     * @var Builder APIv3 客户端实例
     */
    protected $instance;

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
        if(empty($appid)){
            $appid = env('WECHAT_PAY_APPID');
            if(empty($appid)) throw new \Exception('请传入appid参数或在.env文件中配置WECHAT_PAY_APPID');
        }
        $this->appid = $appid;

        if(empty($mch_id)){
            $mch_id = env('WECHAT_PAY_MCH_ID');
            if(empty($mch_id)) throw new \Exception('请传入mch_id商户号id参数或在.env文件中配置WECHAT_PAY_MCH_ID');
        }
        $this->mch_id = $mch_id;

        if(empty($mch_key)){
            $mch_key = env('WECHAT_PAY_MCH_KEY');
            if(empty($mch_key)) throw new \Exception('请传入mch_key商户号密钥参数或在.env文件中配置WECHAT_PAY_MCH_KEY');
        }
        $this->mch_key = $mch_key;

        if(empty($notify_url)){
            $notify_url = env('WECHAT_PAY_NOTIFY_URL');
            if(empty($notify_url)) throw new \Exception('请传入notify_url通知地址参数或在.env文件中配置WECHAT_PAY_NOTIFY_URL');
        }
        $this->notify_url = $notify_url;

        if(empty($serial_number)){
            $serial_number = env('WECHAT_PAY_SERIAL_NUMBER');
            if(empty($serial_number)) throw new \Exception('请传入商户号证书序列号参数或在.env文件中配置WECHAT_PAY_SERIAL_NUMBER');
        }
        $this->serial_number = $serial_number;

        if(empty($apiclient_key_path)){
            $apiclient_key_path = env('WECHAT_PAY_APICLIENT_KEY_PATH');
            if(empty($apiclient_key_path)) throw new \Exception('请传入apiclient_key.pem文件的相对路径证书路径参数或在.env文件中配置WECHAT_PAY_APICLIENT_KEY_PATH');
        }
        $this->apiclient_key_path = $apiclient_key_path;

        if(empty($wechatpay_path)){
            $wechatpay_path = env('WECHAT_PAY_WECHATPAY_PATH');
            if(empty($wechatpay_path)) throw new \Exception('请传入wechatpay.pem文件的相对路径参数或在.env文件中配置WECHAT_PAY_WECHATPAY_PATH');
        }
        $this->wechatpay_path = $wechatpay_path;

        $this->init($debug);
    }

    /**
     * 初始化创建APIv3客户端实例
     * @param $debug bool 是否开启调试模式
     * @return void
     */
    public function init($debug)
    {
        // 设置参数

        // 商户号
        $merchantId = $this->mch_id;

        // 从本地文件中加载「商户API私钥」，「商户API私钥」会用来生成请求的签名
        $merchantPrivateKeyFilePath = 'file://' . $this->apiclient_key_path;

        $merchantPrivateKeyInstance = Rsa::from($merchantPrivateKeyFilePath, Rsa::KEY_TYPE_PRIVATE);

        // 「商户API证书」的「证书序列号」
        $merchantCertificateSerial = $this->serial_number;

        // 从本地文件中加载「微信支付平台证书」，用来验证微信支付应答的签名
        $platformCertificateFilePath = 'file://' . $this->wechatpay_path;

        $platformPublicKeyInstance = Rsa::from($platformCertificateFilePath, Rsa::KEY_TYPE_PUBLIC);

        // 从「微信支付平台证书」中获取「证书序列号」
        $platformCertificateSerial = PemUtil::parseCertificateSerialNo($platformCertificateFilePath);

        // 构造一个 APIv3 客户端实例
        $instance = Builder::factory([
            'mchid'      => $merchantId,
            'serial'     => $merchantCertificateSerial,
            'privateKey' => $merchantPrivateKeyInstance,
            'certs'      => [
                $platformCertificateSerial => $platformPublicKeyInstance,
            ],
        ]);

        // 发送请求
        $resp = $instance->chain('v3/certificates')->get(
            ['debug' => $debug] // 调试模式，https://docs.guzzlephp.org/en/stable/request-options.html#debug
        );

        $this->instance = $instance;
    }

    /**
     * 获取客户端ip
     * @return mixed|string
     */
    public function getClientIP()
    {
        // 判断是否存在代理IP
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $clientIP = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // 判断是否存在X-Forwarded-For头部
            $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $clientIP = trim($ipList[0]);
        } else {
            // 直接获取REMOTE_ADDR
            $clientIP = $_SERVER['REMOTE_ADDR'];
        }

        return $clientIP;
    }

    /**
     * 订单查询
     * @param $order_code string 订单号, 支持微信订单号和平台订单号
     * @param $order_field string 订单号字段名, transaction_id 代表微信订单号, out_trade_no 代表平台订单号
     * 如果返回存在code, 那么就是查询失败,例如: ["code" => "PARAM_ERROR", "message" => "微信订单号非法"]
     * 如果返回存在out_trade_no, 且存在trade_state 那么就是查询成功
     * @return mixed
     */
    public function orderQuery($order_code = '', $order_field = 'transaction_id')
    {
        // TODO: Implement selectOrder() method.

        if(empty($order_code)) throw new \Exception('请传入订单号');

        switch ($order_field){
            case 'transaction_id': // 微信订单号
                $promise = $this->instance
                    ->v3->pay->transactions->id->_transaction_id_;
                break;
            case 'out_trade_no': // 平台订单号
                $promise = $this->instance
                    ->v3->pay->transactions->outTradeNo->_out_trade_no_;
                break;
            default:
                throw new \Exception('目前支持transaction_id微信订单号和out_trade_no平台订单号查询');
        }

        //调用
        $promise = $promise->getAsync([
                // Query 参数
                'query' => ['mchid' => $this->mch_id],
                // 变量名 => 变量值
                $order_field => $order_code,
            ])->then(static function($response) {
                // 正常逻辑回调处理
                return $response;
            })->otherwise(static function($e) {
                // 异常错误处理
                $r = $e->getResponse();
                return $r;
            })->wait();

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
        if(empty($out_trade_no)) throw new \Exception('平台订单号不能为空');

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
        //组装退款需要的参数
        $json = [
            'out_refund_no' => $data['out_refund_no'],
            'amount' => [
                'refund' => $data['refund'],
                'total' => $data['total'],
                'currency' => 'CNY',
            ],
        ];
        //微信订单号
        if(!empty($data['transaction_id'])) $json['transaction_id'] = $data['transaction_id'];
        //平台订单号
        if(!empty($data['out_trade_no'])) $json['out_trade_no'] = $data['out_trade_no'];
        //退款原因
        if(!empty($data['reason'])) $json['reason'] = $data['reason'];
        //退款通知地址
        if(!empty($data['notify_url'])) $json['notify_url'] = $data['notify_url'];
        //退款资金来源
        if(!empty($data['funds_account'])) $json['funds_account'] = $data['funds_account'];
        //退款商品
        if(!empty($data['goods_detail'])) $json['goods_detail'] = $data['goods_detail'];

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
        if(empty($out_refund_no)) throw new \Exception("请传入平台退款订单号out_refund_no");

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

    /**
     * 退款通知
     * @param $inBody array 退款通知数据
     * @return array|false
     * 正常返回以下json转的数组:
     * {
     * "mchid":"1607529862",
     * "out_trade_no":"61893511e032024552f3aaed707e7953",
     * "transaction_id":"4200002137202312158509989834",
     * "out_refund_no":"f26520fb76e47077bb329a908d1011ff",
     * "refund_id":"50310108072023121504276947708",
     * "refund_status":"SUCCESS",
     * "success_time":"2023-12-15T17:26:58+08:00",
     * "amount":{
     *      "total":1,
     *      "refund":1,
     *      "payer_total":1,
     *      "payer_refund":1
     * },
     * "user_received_account":"支付用户零钱"
     * }
     */
    public function refundNotify($inBody = [])
    {
        $apiv3Key = $this->mch_key;// 在商户平台上设置的APIv3密钥
        //返回的数据流
        $inBody = empty($inBody) ? file_get_contents('php://input') : $inBody;
        //解密失败
        if(empty($inBody)) return false;
        //不是数组就解析
        if(!is_array($inBody)) $inBody = json_decode($inBody, true);
        //使用key、nonce和associated_data，对数据密文resource.ciphertext进行解密，得到JSON形式的资源对象。
        if(!isset($inBody['resource']['nonce']) // 加密使用的随机串
            || !isset($inBody['resource']['ciphertext']) // Base64编码后的开启/停用结果数据密文
            || !isset($inBody['resource']['associated_data']) // 附加数据
        ) return false;
        //需要使用base64解密
        $ciphertext = base64_decode($inBody['resource']['ciphertext']);

        $nonce = $inBody['resource']['nonce'];

        $associated_data = $inBody['resource']['associated_data'];
        //使用sodium来解密
        $decryptedData = sodium_crypto_aead_aes256gcm_decrypt($ciphertext, $associated_data, $nonce, $apiv3Key);

        if($decryptedData === false){
            throw new \Exception("解密失败");
        }

        $result = json_decode($decryptedData, true);

        return $result;
    }

    /**
     * 成功之后返回给微信
     * @return void
     */
    public function result($code = 'SUCCESS')
    {
        header('HTTP/1.1 200 OK');
        header('Content-Type: application/json');
        header('Wechatpay-Nonce: ' . uniqid());
        echo json_encode([
            'code' => $code,
            'message' => 'OK'
        ]);
    }

}