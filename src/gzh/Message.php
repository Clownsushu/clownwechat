<?php
namespace clown\wechat\gzh;

/**
 * 公众号回复消息类
 */
class Message extends Base
{
    /**
     * 获取xml回复模板内容
     * @param $name string 模板名称
     * @return false|string
     */
    public function getXmlTemplate($name = 'text')
    {
        return file_get_contents(__DIR__ . '/xml/' . $name . '.xml');
    }

    /**
     * 获取消息类型
     * @param $array array 微信传过来的数据, 如果MsgType == event 的时候获取Event里的内容
     * @return string
     */
    public function getMsgType($array = [])
    {
        $string = '';

        if(isset($array['MsgType'])){
            $string = $array['MsgType'];
            if($string == 'event'){
                $string = isset($array['Event']) ? $array['Event'] : '';
            }
        }

        return $string;
    }

    /**
     * 回复文本消息
     * @param $array array 要回复的内容
     * @return string
     * @throws BaseException
     */
    public function responseTextMsg($array = [])
    {
        if(!isset($array['ToUserName'])) throw new \Exception('请传入接收方账号（收到的OpenID）ToUserName');

        if(!isset($array['FromUserName'])) throw new \Exception('开发者微信号 FromUserName');

        if(!isset($array['Content'])) throw new \Exception('回复的消息内容（换行：在content中能够换行，微信客户端就支持换行显示））Content');

        //创建时间戳, 没有就当前时间戳
        if(!isset($array['CreateTime'])){
            $array['CreateTime'] = time();
        }

        $type = 'text';

        $xml = $this->getXmlTemplate();

        $result = sprintf($xml, $array['FromUserName'], $array['ToUserName'], $array['CreateTime'], $type, $array['Content']);

        return $result;
    }

    /**
     * 回复图片消息
     * @param $array array 要回复的内容数组
     * @return string
     * @throws BaseException
     */
    public function responseImageMsg($array = [])
    {
        if(!isset($array['ToUserName'])) throw new \Exception('请传入接收方账号（收到的OpenID）ToUserName');

        if(!isset($array['FromUserName'])) throw new \Exception('开发者微信号 FromUserName');

        if(!isset($array['MediaId'])) throw new \Exception('通过素材管理中的接口上传多媒体文件，得到的id。 MediaId');

        //创建时间戳, 没有就当前时间戳
        if(!isset($array['CreateTime'])){
            $array['CreateTime'] = time();
        }

        $type = 'image';

        $xml = $this->getXmlTemplate($type);

        $result = sprintf($xml, $array['FromUserName'], $array['ToUserName'], $array['CreateTime'], $type, $array['MediaId']);

        return $result;
    }

    /**
     * 回复语音消息
     * @param $array array 要回复的内容数组
     * @return string
     * @throws BaseException
     */
    public function responseVoiceMsg($array = [])
    {
        if(!isset($array['ToUserName'])){
            throw new \Exception( '请传入接收方账号（收到的OpenID）ToUserName');
        }

        if(!isset($array['FromUserName'])){
            throw new \Exception( '开发者微信号 FromUserName');
        }

        if(!isset($array['MediaId'])){
            throw new \Exception( '通过素材管理中的接口上传多媒体文件，得到的id。 MediaId');
        }

        //创建时间戳, 没有就当前时间戳
        if(!isset($array['CreateTime'])){
            $array['CreateTime'] = time();
        }

        $type = 'voice';

        $xml = $this->getXmlTemplate($type);

        $result = sprintf($xml, $array['FromUserName'], $array['ToUserName'], $array['CreateTime'], $type, $array['MediaId']);

        return $result;
    }

    /**
     * 回复视频消息
     * @param $array array 要回复的内容数组
     * @return string
     * @throws BaseException
     */
    public function responseVideoMsg($array = [])
    {
        if(!isset($array['ToUserName'])){
            throw new \Exception( '请传入接收方账号（收到的OpenID）ToUserName');
        }
        if(!isset($array['FromUserName'])){
            throw new \Exception( '开发者微信号 FromUserName');
        }
        if(!isset($array['MediaId'])){
            throw new \Exception( '通过素材管理中的接口上传多媒体文件，得到的id。 MediaId');
        }
        //创建时间戳, 没有就当前时间戳
        if(!isset($array['CreateTime'])){
            $array['CreateTime'] = time();
        }

        $type = 'video';

        if(!isset($array['Title'])){
            $array['Title'] = '标题';
        }

        if(!isset($array['Description'])){
            $array['Description'] = '简介';
        }

        $xml = $this->getXmlTemplate($type);

        $result = sprintf($xml, $array['FromUserName'], $array['ToUserName'], $array['CreateTime'], $type, $array['MediaId'], $array['Title'], $array['Description']);

        return $result;
    }

    /**
     * 回复音乐消息
     * @param $array array 要回复的内容数组
     * @return string
     * @throws BaseException
     */
    public function responseMusicMsg($array = [])
    {
        if(!isset($array['ToUserName'])){
            throw new \Exception( '请传入接收方账号（收到的OpenID）ToUserName');
        }

        if(!isset($array['FromUserName'])){
            throw new \Exception( '开发者微信号 FromUserName');
        }

        if(!isset($array['MediaId'])){
            throw new \Exception( '通过素材管理中的接口上传多媒体文件，得到的id。 MediaId');
        }

        //创建时间戳, 没有就当前时间戳
        if(!isset($array['CreateTime'])){
            $array['CreateTime'] = time();
        }

        if(!isset($array['MsgType'])){
            $array['MsgType'] = 'music';
        }

        if(!isset($array['Title'])){
            $array['Title'] = '';
        }

        if(!isset($array['Description'])){
            $array['Description'] = '';
        }

        if(!isset($array['MusicURL'])){
            $array['MusicURL'] = '';
        }

        if(!isset($array['HQMusicUrl'])){
            $array['HQMusicUrl'] = '';
        }

        if(!isset($array['ThumbMediaId'])){
            throw new \Exception( '缩略图的媒体id，通过素材管理中的接口上传多媒体文件，得到的id');
        }

        $xml = $this->getXmlTemplate('music');

        $result = sprintf($xml, $array['FromUserName'], $array['ToUserName'], $array['CreateTime'], $array['MsgType'], $array['Title'], $array['Description'], $array['MusicURL'], $array['HQMusicUrl'], $array['ThumbMediaId']);

        return $result;
    }

    /**
     * 回复图文消息
     * @param $array array 要回复的数组内容
     * @return string
     * @throws BaseException
     */
    public function responseNewsMsg($array = [])
    {
        if(!isset($array['ToUserName'])){
            throw new \Exception( '请传入接收方账号（收到的OpenID）ToUserName');
        }

        if(!isset($array['FromUserName'])){
            throw new \Exception( '开发者微信号 FromUserName');
        }
        //要返回的图文数组
        if(!isset($array['Articles'])){
            throw new \Exception( '图文消息信息，注意，如果图文数超过限制，则将只发限制内的条数');
        }

        $articles = $array['Articles'];

        if(empty($array['ArticleCount'])){
            if(empty($articles)){
                throw new \Exception( '图文消息信息，注意，如果图文数超过限制，则将只发限制内的条数');
            }else{
                $array['ArticleCount'] = count($articles);
            }
        }

        //创建时间戳, 没有就当前时间戳
        if(!isset($array['CreateTime'])){
            $array['CreateTime'] = time();
        }

        $type = 'news';

        $xml = $this->getXmlTemplate($type);
        $item_xml = '';
        if(!empty($articles)){
            foreach ($articles as $article){
                if(empty($article['Title'])){
                    throw new \Exception( '请传入图文消息标题');
                }
                if(empty($article['Description'])){
                    throw new \Exception( '请传入图文消息描述');
                }
                if(empty($article['PicUrl'])){
                    throw new \Exception( '请传入图片链接，支持JPG、PNG格式，较好的效果为大图360*200，小图200*200');
                }
                if(empty($article['Url'])){
                    throw new \Exception( '请传入点击图文消息跳转链接');
                }
                $item_xml .= "<item><Title><![CDATA[" . $article['Title'] . "]]></Title>";
                $item_xml .= "<Description><![CDATA[" . $article['Description'] . "]]></Description>";
                $item_xml .= "<PicUrl><![CDATA[" . $article['PicUrl'] . "]]></PicUrl>";
                $item_xml .= "<Url><![CDATA[" . $article['Url'] . "]]></Url></item>";
            }
        }

        $result = sprintf($xml, $array['FromUserName'], $array['ToUserName'], $array['CreateTime'], $type, $array['ArticleCount'], $item_xml);

        return $result;
    }

    /**
     * 回复地理位置信息
     * @param $array array 要回复的数组内容
     * @return string
     * @throws BaseException
     */
    public function responseLocationMsg($array = [])
    {
        if(!isset($array['ToUserName'])){
            throw new \Exception( '请传入接收方账号（收到的OpenID）ToUserName');
        }

        if(!isset($array['FromUserName'])){
            throw new \Exception( '开发者微信号 FromUserName');
        }

        if(!isset($array['CreateTime'])){
            $array['CreateTime'] = time();
        }

        if(!isset($array['Location_X'])){
            throw new \Exception( '地理位置纬度 Location_X');
        }

        if(!isset($array['Location_Y'])){
            throw new \Exception( '地理位置经度 Location_Y');
        }

        if(!isset($array['Scale'])){
            $array['Scale'] = 20;
        }

        if(!isset($array['Label'])){
            throw new \Exception( '地理位置信息 Label');
        }

        $type = 'LOCATION';

        $xml = $this->getXmlTemplate(strtolower($type));

        $result = sprintf($xml, $array['FromUserName'], $array['ToUserName'], $array['CreateTime'], $type, $array['Location_X'], $array['Location_Y'], $array['Scale'], $array['Label']);

        return $result;
    }

    /**
     * 回复链接消息
     * @param $array array 要回复的内容数组
     * @return string
     */
    public function responseLinkMsg($array = [])
    {
        $result = '';

        return $result;
    }

    /**
     * 回复小视频消息
     * @param $array array 要回复的数组内容
     * @return string
     */
    public function responseShortvideoMsg($array = [])
    {
        $result = '';

        return $result;
    }
}