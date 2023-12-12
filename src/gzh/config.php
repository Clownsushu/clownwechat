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
    //查询rid
    'getRid' => 'https://api.weixin.qq.com/cgi-bin/openapi/rid/get?access_token=ACCESS_TOKEN',
    //创建菜单
    'createMenu' => 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=ACCESS_TOKEN',
    //查询菜单
    'selectMenu' => 'https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info?access_token=ACCESS_TOKEN',
    //删除菜单
    'deleteMenu' => 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=ACCESS_TOKEN',
    //网页授权地址
    'authorize' => 'https://open.weixin.qq.com/connect/oauth2/authorize',
    //根据code获取openid
    'code' => 'https://api.weixin.qq.com/sns/oauth2/access_token',
    //添加临时素材
    'addTemporarily' => 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token=ACCESS_TOKEN&type=TYPE',
    //获取临时素材
    'getTemporarily' => 'https://api.weixin.qq.com/cgi-bin/media/get?access_token=ACCESS_TOKEN&media_id=MEDIA_ID',
    //新增永久素材图片
    'addPermanentImage' => 'https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token=ACCESS_TOKEN',
    //新增永久素材其他类型
    'addPermanentOther' => 'https https://api.weixin.qq.com/cgi-bin/material/add_material?access_token=ACCESS_TOKEN&type=TYPE',
    //获取永久素材
    'getPermanent' => 'https://api.weixin.qq.com/cgi-bin/material/get_material?access_token=ACCESS_TOKEN',
    //删除永久素材
    'delPermanent' => 'https://api.weixin.qq.com/cgi-bin/material/del_material?access_token=ACCESS_TOKEN',
    //获取素材总数
    'getAllPermanent' => 'https://api.weixin.qq.com/cgi-bin/material/get_materialcount?access_token=ACCESS_TOKEN',
    //获取素材列表
    'getPermanentList' => 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=ACCESS_TOKEN',
];