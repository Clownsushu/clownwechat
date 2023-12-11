<?php
/**
 * 获取url地址
 */
return [
    //缓存前缀
    'cache_prefix' => 'wechat:',
    //获取接口调用凭据
    'getAccessToken' => 'https://api.weixin.qq.com/cgi-bin/token',
    //小程序登录
    'code2Session' => 'https://api.weixin.qq.com/sns/jscode2session',
    //获取小程序码
    'getQRCode' => 'https://api.weixin.qq.com/wxa/getwxacode?access_token=ACCESS_TOKEN',
    //获取不限制的小程序码
    'getUnlimitedQRCode' => 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=ACCESS_TOKEN',

];