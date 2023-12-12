<?php
namespace clown\wechat\xcx;

use clown\redis\Redis;

/**
 * 小程序基础类
 */
class Base
{
    /**
     * @var array|mixed 配置信息
     */
    protected $config = [];

    /**
     * @var string 小程序ID
     */
    protected $appId = '';

    /**
     * @var string 小程序密钥
     */
    protected $appSecret = '';

    /**
     * @var \Redis|null redis
     */
    protected $redis = null;

    /**
     * @var string 接口调用凭证
     */
    protected $access_token = '';

    /**
     * 构造函数
     * @param $appId string 小程序appid
     * @param $appSecret string 小程序秘钥
     * @throws \RedisException
     */
    public function __construct($appId = '', $appSecret = '')
    {
        if(empty($appId)){
            $appId = env('WECHAT_XCX_APPID');
            if(empty($appId)) throw new \Exception('请传入appId参数或在.env文件下配置WECHAT_XCX_APPID参数');
        }
        if(empty($appSecret)){
            $appSecret = env('WECHAT_XCX_APPSECRET');
            if(empty($appSecret)) throw new \Exception('请传入appSecret参数或在.env文件下配置WECHAT_XCX_APPSECRET参数');
        }
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        //读取url里的地址信息
        $this->config = include 'config.php';
        //接入redis
        $this->redis = (new Redis())->getRedis();
        //获取accesstoken
        $this->getAccessToken();
    }

    /**
     * 获取access_token
     * @param $grant_type
     * @return false|mixed|\Redis|string
     * @throws \RedisException
     */
    public function getAccessToken($grant_type = 'client_credential')
    {
        //拼接缓存前缀
       $cache_key = $this->config['cache_prefix'] . 'access_token';

       if($this->redis->exists($cache_key)){
           $this->access_token = $this->redis->get($cache_key);
       }else{
           //组装参数
           $params = [
               'grant_type' => $grant_type,
               'appid' => $this->appId,
               'secret' => $this->appSecret
           ];
           //转成url字符串形式
           $string = '?' . http_build_query($params);
           //拼接地址
           $url = $this->config['getAccessToken'] . $string;
           //获取内容
           $result = json_decode(file_get_contents($url), true);

           if(isset($result['access_token'])){
               $this->access_token = $result['access_token'];
               //写入缓存
               $this->redis->set($cache_key, $this->access_token, 7100);
           }else{
               throw new \Exception('获取access_token失败, 返回内容: '. json_encode($result));
           }
       }

       return $this->access_token;
    }

    /**
     * 字符串替换
     * @param $url string 地址
     * @param $replace string 需要替换的内容
     * @param $with string 替换后的字符串
     * @return array|mixed|string|string[]
     */
    public function replaceUrl($url = '', $replace = '', $with = '')
    {
        return str_replace($replace, $with, $url);
    }
}