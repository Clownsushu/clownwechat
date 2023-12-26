<?php
namespace clown\wechat\xcx;

/**
 * 小程序基础接口类
 */
class Xcx extends Base
{
    /**
     * 构造函数
     * @param $appId string 小程序appid
     * @param $appSecret string 小程序秘钥
     * @throws \RedisException
     */
    public function __construct($appId = '', $appSecret = '')
    {
        parent::__construct($appId, $appSecret);
    }

    /**
     * 获取openid
     * @param $js_code string 登录时获取的 code，可通过wx.login获取
     * @return array ["session_key" => "2CYWpg6I9L2AZUMc+t3yQQ==", "openid" => "oR9yD4nyATdhe7RbAAY1PGNP_bsQ"]
     * @throws \Exception
     */
    public function code2Session($js_code = '')
    {
        if(empty($js_code)){
            throw new \Exception('请传入js_code参数, 登录时获取的 code，可通过wx.login获取');
        }

        //组装请求参数
        $params = [
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'js_code' => $js_code,
            'grant_type' => 'authorization_code'
        ];
        $params = '?' . http_build_query($params);
        //拼接请求地址
        $url = $this->config['code2Session'] . $params;
        //获取返回内容
        $result = json_decode(file_get_contents($url), true);

        if(!isset($result['openid'])){
            throw new \Exception('获取openid失败, 返回内容: ' . json_encode($result));
        }

        return $result;
    }

    /**
     * 获取手机号码
     * @param $code string  手机号获取凭证
     * @param $is_return_phone bool 是否直接返回手机号码 默认 true 直接返回手机号码
     * @return mixed
     * @throws \Exception
     */
    public function getPhoneNumber($code = '', $is_return_phone = true)
    {
        if(empty($code)) throw new \Exception('请传入code参数, 手机号授权时获取的 code');

        $params = [
            'code' => $code,
        ];

        //获取请求地址
        $url = $this->replaceUrl($this->config['getPhoneNumber'], 'ACCESS_TOKEN', $this->access_token);
        //post请求
        $result = curlPost($url, $params);

        if(isset($result['errcode']) && $result['errcode']){
            throw new \Exception($result['errmsg']);
        }

        if(!isset($result['phone_info'])){
            throw new \Exception('未返回手机号信息');
        }

        if($is_return_phone && isset($result['phone_info']['purePhoneNumber'])) return $result['phone_info']['purePhoneNumber'];

        return $result['phone_info'];
    }
}