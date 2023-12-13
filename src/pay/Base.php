<?php
namespace clown\wechat\pay;


use WeChatPay\Builder;
use WeChatPay\Crypto\Rsa;
use WeChatPay\Util\PemUtil;

/**
 * 微信支付基础类
 */
class Base
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
     * 成功之后返回给微信
     * @return void
     */
    public function result()
    {
        header('HTTP/1.1 200 OK');
        header('Content-Type: application/json');
        header('Wechatpay-Nonce: ' . uniqid());
        echo json_encode([
            'code' => 'SUCCESS',
            'message' => 'OK'
        ]);
    }
}