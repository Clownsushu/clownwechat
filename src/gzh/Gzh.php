<?php
namespace clown\wechat\gzh;


class Gzh extends Base
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
     * 测试
     * @param $token string 公众号服务器配置的token
     * @return string
     * @throws \Exception
     */
    public function test($token = '')
    {
        //存在这个就校验环境
        if(isset($_GET['echostr'])){
            return $this->checkVerify($token);
        }else{ // 不存在就处理消息
            // 接收php://input流中的原始数据
            $data = file_get_contents('php://input');

            $xml = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);

            $array = json_decode(json_encode($xml), true);

            //获取消息类型
            $msg_type = $this->getMsgType($array);

            return $this->responseMsg($msg_type, $array);
        }
    }

    /**
     * 服务器配置环境校验
     * @param $token string 公众号服务器配置的token
     * @return string
     */
    public function checkVerify($token = '')
    {
        if(empty($token)) throw new \Exception('请传入Token参数');

        ob_clean();

        $echoStr = isset($_GET['echostr']) ? $_GET['echostr'] : '';

        $signature = isset($_GET['signature']) ? $_GET['signature'] : '';

        $timestamp = isset($_GET['timestamp']) ? $_GET['timestamp'] : '';

        $nonce = isset($_GET['nonce']) ? $_GET['nonce'] : '';

        $token = !empty($token) ? $token : '';

        $tmpArr = [$token, $timestamp, $nonce];

        sort($tmpArr, SORT_STRING);

        $tmpStr = implode($tmpArr);

        $tmpStr = sha1($tmpStr);

        if($tmpStr == $signature) return $echoStr;

        return "";
    }

    /**
     * 获取rid查询
     * @param $rid string rid查询
     * @return mixed
     */
    public function getRid($rid = '')
    {
        $url = $this->replaceUrl($this->config['getRid'], 'ACCESS_TOKEN', $this->access_token);

        $result = curlPost($url,['rid' => $rid]);

        return $result;
    }

    /**
     * 获取消息类型
     * @param $array array 微信传过来的数据
     * @return void|XML
     */
    public function getMsgType($array = [])
    {
        $message = new Message();

        return $message->getMsgType($array);
    }

    
    /**
     * 消息返回, 可获取消息类型后吗, 自行封装返回内容
     * @param $msg_type string 要回复的消息类型
     * @param $response_data array 要回复的内容
     * @return void|XML
     */
    public function responseMsg($msg_type, $response_data = [])
    {
        $message = new Message();

        switch ($msg_type) {
            case 'event': // 点击推事件
                break;
            case 'CLICK': //单击事件
                break;
            case 'SCAN': // 用户已关注时事件推送
                break;
            case 'subscribe': // 用户关注事件
                break;
            case 'unsubscribe': // 用户取消关注时间
                break;
            case 'LOCATION': //地理位置
                break;
            case 'image': // 图片消息
                $result = $message->responseImageMsg($response_data);
                break;
            case 'voice': // 语音消息
                $result = $message->responseVoiceMsg($response_data);
                break;
            case 'video': // 视频消息
                $result = $message->responseVideoMsg($response_data);
                break;
            case 'news': //图文消息
                $result = $message->responseNewsMsg($response_data);
                break;
            case 'location': // 地理位置
                $result = $message->responseLocationMsg($response_data);
                break;
            case 'link': // 链接消息
                $result = $message->responseLinkMsg($response_data);
                break;
            case 'shortvideo': // 小视频
                $result = $message->responseShortvideoMsg($response_data);
                break;
            case 'text': // 文本消息
                $result = $message->responseTextMsg($response_data);
                break;
            default:
                $result = '';
        }

        return $result;
    }

}