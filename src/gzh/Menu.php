<?php
namespace clown\wechat\gzh;

class Menu extends Base
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
     * 创建菜单
     * 组装格式
     * [
    'button' => [
    'type' => 'view',
    'name' => '我的博客',
    'url' => 'http://blog.sszwl.cn/',
    ]
    ]
     * @param $menus array 要生成的菜单
     * @return bool
     * @throws \Exception
     */
    public function createMenu($menus = [])
    {
        if(empty($menus)) throw new \Exception('请传入要创建的菜单');

        $url = $this->replaceUrl($this->config['createMenu'], 'ACCESS_TOKEN', $this->access_token);

        $result = curlPost($url, $menus);

        if(!$result['errcode']) return true;

        return false;
    }

    /**
     * 查询菜单
     * @return mixed
     */
    public function selectMenus()
    {
        $url = $this->replaceUrl($this->config['selectMenu'], 'ACCESS_TOKEN', $this->access_token);

        $result = json_decode(file_get_contents($url), true);

        return $result;
    }

    /**
     * 删除菜单
     * @return bool
     */
    public function deleteMenu()
    {
        $url = $this->replaceUrl($this->config['deleteMenu'], 'ACCESS_TOKEN', $this->access_token);

        $result = json_decode(file_get_contents($url), true);

        if(isset($result['errcode']) && !$result['errcode']) return true;

        return false;
    }
}