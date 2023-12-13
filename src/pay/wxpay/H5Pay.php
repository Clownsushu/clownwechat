<?php
namespace clown\wechat\pay\wxpay;

use clown\wechat\pay\Base;
use clown\wechat\pay\Pay;

class H5Pay extends Base implements Pay
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





}