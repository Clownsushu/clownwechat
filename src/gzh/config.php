<?php
/**
 * 获取url地址
 */
return [
    //缓存前缀
    'cache_prefix' => 'wechat:gzh:',
    //获取access_token
    'getAccessToken' => 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=APPID&secret=APPSECRET',
    //发送模板消息
    'sendTemplateMessage' => 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=ACCESS_TOKEN',
    //创建菜单
    'createMenu' => 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=ACCESS_TOKEN',
    //查询菜单
    'selectMenu' => 'https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info?access_token=ACCESS_TOKEN',
    //删除菜单
    'deleteMenu' => 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=ACCESS_TOKEN',
];