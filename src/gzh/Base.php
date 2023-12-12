<?php
namespace clown\wechat\gzh;

use clown\redis\Redis;

/**
 * 公众号基础类
 */
class Base
{
    /**
     * @var array|mixed 配置信息
     */
    protected $config = [];

    /**
     * @var string 开发者ID
     */
    protected $appId = '';

    /**
     * @var string 开发者密钥
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
     * @param $appId string 开发者ID
     * @param $appSecret string 开发者密码
     * @throws \RedisException
     */
    public function __construct($appId = '', $appSecret = '')
    {
        if(empty($appId)){
            $appId = env('WECHAT_GZH_APPID');
            if(empty($appId)) throw new \Exception('请传入appId参数或在.env文件下配置WECHAT_GZH_APPID参数');
        }
        if(empty($appSecret)){
            $appSecret = env('WECHAT_GZH_APPSECRET');
            if(empty($appSecret)) throw new \Exception('请传入appSecret参数或在.env文件下配置WECHAT_GZH_APPSECRET参数');
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
            //拼接地址
            $url = $this->replaceUrl($this->config['getAccessToken'], [
                'APPID',
                'APPSECRET'
            ], [
                $this->appId,
                $this->appSecret
            ]);

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
     * @param $replace array 需要替换的内容
     * @param $with array 替换后的字符串
     * @return array|mixed|string|string[]
     */
    public function replaceUrl($url = '', $replace = [], $with = [])
    {
        return str_replace($replace, $with, $url);
    }
}