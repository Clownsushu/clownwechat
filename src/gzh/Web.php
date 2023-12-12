<?php
namespace clown\wechat\gzh;


class Web extends Base
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
     * 授权并跳转
     * @param $redirect_uri string 要跳转的地址
     * @param $scope string 跳转参数, 参考微信公众号文档
     * @param $state string 额外参数
     * @param $forcePopup bool 强制此次授权需要用户弹窗确认；默认为false；需要注意的是，若用户命中了特殊场景下的静默授权逻辑，则此参数不生效
     * @return void
     * @throws \Exception
     */
    public function authorize($redirect_uri = '', $scope = 'snsapi_userinfo', $state = '', $forcePopup = false)
    {
        if(empty($redirect_uri)) throw new \Exception('请传入重定向地址');

        if(empty($scope)) throw new \Exception('请传入重定向地址');

        $params = [
            'appid' => $this->appId,
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'scope' => $scope,
            'state' => 'STATE'
        ];

        if($forcePopup){
            $params['forcePopup'] = true;
        }

        $params = http_build_query($params);

        $url = $this->config['authorize'] . '?' . $params . '#wechat_redirect';

        //组装好地址后进行跳转
        header("Location: {$url}");exit;
    }

    /**
     * 根据code换取openid
     * @param $code string authorize跳转授权后返回的code参数
     * @param $is_openid bool false 返回全部参数 true 只返回openid
     * @return mixed
     * @throws \Exception
     */
    public function getOpenid($code = '', $is_openid = false)
    {
        if(empty($code)) throw new \Exception('请传入code参数');
        //组装参数
        $params = [
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'code' => $code,
            'grant_type' => 'authorization_code'
        ];
        $params = '?' . http_build_query($params);
        //组装请求地址
        $url = $this->config['code'] . $params;

        $reuslt = json_decode(file_get_contents($url), true);

        if($is_openid && isset($reuslt['openid'])) return $reuslt['openid'];

        return $reuslt;
    }
}