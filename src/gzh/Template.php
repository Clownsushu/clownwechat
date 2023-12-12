<?php
namespace clown\wechat\gzh;


/**
 * 公众号模板消息类
 */
class Template extends Base
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
     * 发送模板消息
     * @param $openids array|string 传string发一个人 array发所有人
     * @param $send_data array 要发送的数据组装成数组
     * @return array
     * @throws BaseException
     */
    public function sendTemplate($openids, $send_data = [])
    {
        if(empty($openids)) throw new \Exception('请传入要发送的用户openid');

        if(empty($send_data)) throw new \Exception('请传入要发送的模板消息内容');

        $url = $this->replaceUrl($this->config['sendTemplateMessage'], 'ACCESS_TOKEN', $this->access_token);

        $ids = [];

        if(!is_array($openids)){
            $ids[] = $openids;
        }else{
            $ids = $openids;
        }

        if(empty($ids)){
            throw new BaseException(500, '发送用户的openid为空');
        }

        $return = [];

        foreach ($ids as $k => $v){
            $data = $send_data;
            $data['touser'] = $v;
            $result = curlPost($url, $data, 'json');
            if(isset($result['errcode']) && !$result['errcode']){
                $return[$k] = [
                    'openid' => $v,
                    'status' => true
                ];
            }else{
                $return[$k] = [
                    'openid' => $v,
                    'status' => false
                ];
            }
        }

        return $return;
    }
}