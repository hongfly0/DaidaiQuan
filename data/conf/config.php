<?php
/**
 * 配置文件
 */

return [
    'appKey'=>'CFFD4234D8C54E23CEE45C500F131367',
    'appSecret' => "ccc2e26a38e0473ab0e30c637c0d90bb",
    'accessToken' => "df42927b-6994-4ad3-8e25-e8fd0bb543b7",
    'IMG_URL_PREFIX' => 'http://47.106.37.216/upload/',

    // 小程序app_id
    'app_id' => 'wx0a1d95f443204af2',
    // 小程序app_secret
    'app_secret' => 'a29462308699ae469d5fb6cc54a9a95a',

    // 微信使用code换取用户openid及session_key的url地址
    'login_url' => "https://api.weixin.qq.com/sns/jscode2session?" .
"appid=%s&secret=%s&js_code=%s&grant_type=authorization_code",

    // 微信获取access_token的url地址
    'access_token_url' => "https://api.weixin.qq.com/cgi-bin/token?" .
"grant_type=client_credential&appid=%s&secret=%s",
];