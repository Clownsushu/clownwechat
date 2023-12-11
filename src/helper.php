<?php
if(!function_exists('curlPost')){
    /**
     * curlpost请求
     * @param $url string 请求地址
     * @param $data array 发送内容
     * @param $type string 请求类型
     * @param bool $is_return 是否直接返回
     * @return array|mixed
     */
    function curlPost($url = '', $data = [], $type = 'json', $is_return = false)
    {
        $chr = curl_init();
        curl_setopt($chr, CURLOPT_URL, $url);
        curl_setopt($chr, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($chr, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($chr, CURLOPT_POST, true);

        if($type=='json'){
            curl_setopt($chr, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }else{
            curl_setopt($chr, CURLOPT_HTTPHEADER, [
                'Content-Type: multipart/form-data',
            ]);
        }
        curl_setopt($chr, CURLOPT_POSTFIELDS, $data);
        curl_setopt($chr, CURLOPT_RETURNTRANSFER, true); //true回调结果，false直接echo输出
        curl_setopt($chr, CURLOPT_CONNECTTIMEOUT, 3); //在发起连接前等待的时间
        curl_setopt($chr, CURLOPT_TIMEOUT, 10); //允许最大执行时间
        $output = curl_exec($chr);

        curl_close($chr);

        if($is_return) return $output;

        try{
            return json_decode($output, true);
        }catch (exception $e){
            return (array) $output;
        }
    }
}

if(!function_exists('stringToImage')){
    /**
     * 二进制转图片
     * @param $string string 图片内容, 二进制
     * @param $save_path string 要保存的文件路径和文件名
     * @return true
     * @throws Exception
     */
    function stringToImage($string = '', $save_path = '')
    {
        //获取文件后缀
        $ext = pathinfo($save_path, PATHINFO_EXTENSION);

        if(!empty($string)){
            //通过字符串创建一个图像资源
            $image = imagecreatefromstring($string);
            switch ($ext){
                case 'jpg':
                case 'jpeg':
                    imagejpeg($image, $save_path);
                    break;
                case 'png':
                    imagepng($image, $save_path);
                    break;
                default:
                    throw new \Exception('目前只支持, jpg、jpeg和png格式');
            }
            imagedestroy($image);
            return true;
        }

        throw new \Exception('字符串为空');
    }
}