<?php
namespace clown\wechat\gzh;


/**
 * 素材管理
 */
class Material extends Base
{
    /**
     * 能上传的类型
     * @var string[]
     */
    protected $type_arr = ['image', 'voice', 'video', 'thumb'];

    /**
     * 各个类型最大上传
     * @var float[]|int[]
     */
    protected $type_max_size = [
        'image' => 1024 * 1024 * 10,
        'voice' => 1024 * 1024 * 2,
        'video' => 1024 * 1024 * 10,
        'thumb' => 1024 * 64,
    ];

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
     * 添加临时素材
     * @param $path string 图片路径
     * @param $type string 上传类型
     * @return array|bool|string
     */
    public function addTemporarily($path = '', $type = 'image')
    {
        if(!in_array($type, $this->type_arr)){
            throw new \Exception('仅支持: ' . implode(',', $this->type_arr));
        }

        $file_data = new \CURLFile($path);

        if(filesize($file_data->name) > $this->type_max_size[$type]){
            throw new \Exception($type . '类型最大支持' . $this->type_max_size[$type] . '字节');
        }
        //组装url地址
        $url = $this->replaceUrl($this->config['addTemporarily'], [
            'ACCESS_TOKEN', 'TYPE'
        ], [$this->access_token, $type]);

        $result = curlFormData($url, ['media' => $file_data]);

        return $result;
    }

    /**
     * 获取临时素材
     * @param $media_id string 媒体文件id
     * @return false|mixed|string
     */
    public function getTemporarily($media_id = '')
    {
        if(empty($media_id)) throw new \Exception("请传入媒体文件id");

        $url = $this->replaceUrl($this->config['getTemporarily'], [
            'ACCESS_TOKEN',
            'MEDIA_ID'
        ], [
            $this->access_token,
            $media_id
        ]);

        $result = file_get_contents($url);

        if(ctype_print($result)){
            $result = json_decode($result, true);
        }

        return $result;
    }

    /**
     * 新增永久素材
     * 如果是image 就直接返回url, 其他类型请参考文档
     * image 返回 [
    "url" => "http://mmbiz.qpic.cn/sz_mmbiz_jpg/owbfPPaQeH18pmjgBPFib4fKEktJibQtZS3zr8ug49XPiasw9dyiaE3k91O2f8z8LBLOwia9g5VLuzWpVjgfhBSXSsA/0"
     *           ]
     * thumb 返回 [
    "media_id" => "zPHvY5VPIy6m1UPsCXSJELbvdtmvCIPLWulU6GLg0qeck3MD81x14A0xaPm8lLCJ"
    "url" => "http://mmbiz.qpic.cn/sz_mmbiz_jpg/owbfPPaQeH18pmjgBPFib4fKEktJibQtZSgTHX5B8vYZicWneUgEqPelBupv18PoicJ2n40xDLInubNnGxRZCW9m5w/0?wx_fmt=jpeg"
    "item" => []
     *            ]
     * video 返回 [
    "media_id" => "zPHvY5VPIy6m1UPsCXSJEOYg7Tj0NCHJ9MOPUEKlB9hXdrhVPOq8JGVt6C4o-M_M"
    "item" => []
     *            ]
     * voice 返回 [
    "media_id" => "zPHvY5VPIy6m1UPsCXSJEMdUQEvgb1ADwfQYWurDXpxqtmWQUnv7BWZg9FNxck-F"
    "item" => []
     *            ]
     * @param $path string 图片路径
     * @param $type string 上传类型
     * @return array|bool|string
     * @throws BaseException
     */
    public function addPermanent($path = '', $type = 'image', $title = '', $introduction = '')
    {
        if(!in_array($type, $this->type_arr)){
            throw new \Exception('仅支持: ' . implode(',', $this->type_arr));
        }

        $file_data = new \CURLFile($path);

        if(filesize($file_data->name) > $this->type_max_size[$type]){
            throw new \Exception($type . '类型最大支持' . $this->type_max_size[$type] . '字节');
        }

        $post_data = ['media' => $file_data];

        if($type == 'image'){ //图片类型单独接口
            $url = $this->replaceUrl($this->config['addPermanentImage'], 'ACCESS_TOKEN', $this->access_token);

        }else{//其他类型永久素材
            $url = $this->replaceUrl($this->config['addPermanentOther'], [
                'ACCESS_TOKEN',
                'TYPE'
            ], [
                $this->access_token,
                $type
            ]);
            //上传视频需要传入标题和简介
            if($type == 'video'){
                if(empty($title)){
                    throw new \Exception('请传入视频标题');
                }
                if(empty($introduction)){
                    throw new \Exception('请传入视频简介');
                }

                $post_data['description'] = json_encode(['title' => $title, 'introduction' => $introduction], JSON_UNESCAPED_UNICODE);
            }
        }

        $result = curlFormData($url, $post_data);

        if(isset($result['errcode'])){
            throw new \Exception($result['errcode'] . ':' . $result['errmsg']);
        }

        return $result;
    }

    /**
     * 获取永久素材 支持视频素材, 其他类型直接为素材内容, 自行保存
     * @param $media_id string 媒体文件标识
     * @return array|mixed
     */
    public function getPermanent($media_id = '')
    {
        if(empty($media_id)) throw new \Exception('请传入媒体文件id');

        $url = $this->replaceUrl($this->config['getPermanent'], 'ACCESS_TOKEN', $this->access_token);

        $result = curlPost($url, ['media_id' => $media_id]);

        return $result;
    }

    /**
     * 删除永久素材
     * @param $media_id string 媒体文件标识
     * @return bool
     */
    public function delPermanent($media_id = '')
    {
        if(empty($media_id)) throw new \Exception('请传入媒体文件id');

        $url = $this->replaceUrl($this->config['delPermanent'], 'ACCESS_TOKEN', $this->access_token);

        $result = curlPost($url, ['media_id' => $media_id]);

        if(isset($result['errcode']) && !$result['errcode']) return true;

        return false;
    }

    /**
     * 获取素材总数
     * [
    "voice_count" => 2 // 语音总数
    "video_count" => 2 // 视频总数
    "image_count" => 1 // 图片总数
    "news_count" => 0 // 图文总数
     * ]
     * @return mixed
     */
    public function getAllPermanent()
    {
        $url = $this->replaceUrl($this->config['getAllPermanent'], 'ACCESS_TOKEN', $this->access_token);

        $result = json_decode(file_get_contents($url), true);

        return $result;
    }

    /**
     * 获取素材列表
     * @param $offset int 从全部素材的该偏移位置开始返回，0表示从第一个素材 返回
     * @param $count int 返回素材的数量，取值在1到20之间
     * @param $type string 素材的类型，图片（image）、视频（video）、语音 （voice）、图文（news）
     * @return array|mixed
     */
    public function getPermanentList($offset = 0, $count = 20, $type = 'image')
    {
        $url = $this->replaceUrl($this->config['getPermanentList'], 'ACCESS_TOKEN', $this->access_token);

        $post_data = [
            'type' => $type,
            'offset' => $offset,
            'count' => $count,
        ];

        $result = curlPost($url, $post_data);

        return $result;
    }

}