<?php
namespace clown\wechat\xcx;

/**
 * 小程序生成小程序码和二维码类
 */
class XcxQrCode extends Base
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
     * 获取小程序码 有数量限制 最高100000
     * @param $path string 扫码进入的小程序页面路径，最大长度 1024 个字符，不能为空
     * @param $save_path string 保存图片的路径 不传直接返回二进制字符串
     * @param $other array 请求接口的其他参数 参考https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/qrcode-link/qr-code/getQRCode.html
     * @return true|void
     * @throws \Exception
     */
    public function getQrCode($path = '', $save_path = '', $other = [])
    {
        if(empty($path)){
            throw new \Exception('请传入path参数, 扫码进入的小程序页面路径，最大长度 1024 个字符，不能为空');
        }
        //获取请求地址
        $url = $this->replaceUrl($this->config['getQRCode'], 'ACCESS_TOKEN', $this->access_token);
        //数组合并
        $params = array_merge(['path' => $path], $other);
        //发起请求
        $result = curlPost($url, $params, 'json', true);

        if(!empty(json_decode($result, true))){
            throw  new \Exception("请求失败, 返回内容: " . $result);
        }

        //不传保存路径直接返回二进制字符串
        if(empty($save_path)) return $result;

        //字符串转图片并保存
        stringToImage($result, $save_path);

        return true;
    }

    /**
     * @param $scene string 最大32个可见字符，只支持数字，大小写英文以及部分特殊字符：!#$&'()*+,/:;=?@-._~，
     * @param $save_path string 保存图片的路径 不传直接返回二进制字符串
     * @param $other array 请求接口的其他参数 参考: https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/qrcode-link/qr-code/getUnlimitedQRCode.html
     * @return array|mixed|true
     * @throws \Exception
     */
    public function getUnlimitedQRCode($scene = '', $save_path = '', $other = [])
    {
        if(empty($scene)){
            throw new \Exception("请传入scene参数, 最大32个可见字符，只支持数字，大小写英文以及部分特殊字符：!#$&'()*+,/:;=?@-._~，");
        }

        //获取请求地址
        $url = $this->replaceUrl($this->config['getUnlimitedQRCode'], 'ACCESS_TOKEN', $this->access_token);

        //数组合并
        $params = array_merge(['scene' => $scene], $other);

        //发起请求
        $result = curlPost($url, $params, 'json', true);

        if(!empty(json_decode($result, true))){
            throw  new \Exception("请求失败, 返回内容: " . $result);
        }
        //保存路径为空直接返回
        if(empty($save_path)) return $result;

        //字符串转图片并保存
        stringToImage($result, $save_path);

        return true;
    }

    /**
     * 获取小程序二维码，适用于需要的码数量较少的业务场景。通过该接口生成的小程序码，永久有效，有数量限制, 参考https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/qrcode-link/qr-code/createQRCode.html
     * @param $path string 扫码进入的小程序页面路径，最大长度 128 个字符，不能为空
     * @param $save_path string 保存图片的路径 不传直接返回二进制字符串
     * @param $width int 二维码的宽度，单位 px。最小 280px，最大 1280px;默认是430
     * @return array|mixed|true
     * @throws \Exception
     */
    public function createQRCode($path = '', $save_path = '', $width = 430)
    {
        if(empty($path)){
            throw new \Exception('请传入path参数, 扫码进入的小程序页面路径，最大长度 128 个字符，不能为空');
        }
        //获取请求地址
        $url = $this->replaceUrl($this->config['createQRCode'], 'ACCESS_TOKEN', $this->access_token);
        //组装参数
        $params = [
            'path' => $path,
            'width' => $width,
        ];

        //发起请求
        $result = curlPost($url, $params, 'json', true);

        if(!empty(json_decode($result, true))){
            throw  new \Exception("请求失败, 返回内容: " . $result);
        }
        //保存路径为空直接返回
        if(empty($save_path)) return $result;

        //字符串转图片并保存
        stringToImage($result, $save_path);

        return true;
    }
}