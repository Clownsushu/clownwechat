<?php
namespace clown\wechat\pay\wxpay;

use clown\wechat\pay\Pay;
use clown\wechat\pay\WxPayBase;
use WeChatPay\Crypto\AesGcm;
use WeChatPay\Crypto\Rsa;
use WeChatPay\Formatter;

/**
 * 微信JSAPI支付
 */
class JsapiPay extends WxPayBase implements Pay
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
        //组装支付需要的参数
        $json = [
            'appid' => $this->appid,
            'mchid' => $this->mch_id,
            'description' => $data['description'],
            'out_trade_no' => $data['out_trade_no'],
            'notify_url' => $this->notify_url,
            'amount'       => [
                'total'    => $data['total'],
                'currency' => 'CNY'
            ],
            'payer' => [
                'openid' => $data['openid'],
            ],
        ];
        //交易结束时间
        if(!empty($data['time_expire'])) $json['time_expire'] = $data['time_expire'];
        //附加数据
        if(!empty($data['attach'])) $json['attach'] = $data['attach'];
        //订单优惠标记
        if(!empty($data['goods_tag'])) $json['goods_tag'] = $data['goods_tag'];
        //电子发票入口开放标识
        if(!empty($data['support_fapiao'])) $json['support_fapiao'] = true;
        //优惠功能
        if(!empty($data['detail'])) $json['detail'] = $data['detail'];
        //场景信息
        if(!empty($data['scene_info'])) $json['scene_info'] = $data['scene_info'];
        //结算信息
        if(!empty($data['settle_info'])) $json['settle_info'] = $data['settle_info'];

        try{
            $resp = $this->instance->chain('v3/pay/transactions/jsapi')
                ->post([ 'json' => $json]);
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
        return parent::notify($inBody);
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
        // TODO: Implement orderQuery() method.
        return parent::orderQuery($order_code, $order_field);
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
        return parent::closeOrder($out_trade_no);
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
        return parent::refunds($data);
    }

    /**
     * 退款查询
     * @param $out_refund_no string 平台退款订单号
     * @return mixed
     * @throws \Exception
     */
    public function refundQuery($out_refund_no = '')
    {
        return parent::refundQuery($out_refund_no);
    }


}