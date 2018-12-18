<?php
/**
 * 配置文件
 */

return [
//    'appKey'=>'CFFD4234D8C54E23CEE45C500F131367',
//    'appSecret' => "40783baacbfade86cf36c05c0f724a9a",
//    'accessToken' => "df42927b-6994-4ad3-8e25-e8fd0bb543b7",
    'IMG_URL_PREFIX' => 'http://web.daidaiquan.cn/upload/',

    // 小程序app_id
    'app_id' => 'wxee670ee05c9d509e',
    // 小程序app_secret
    'app_secret' => '62e5cb47ea1b99d31763570aa5acf402',

    // 微信使用code换取用户openid及session_key的url地址
    'login_url' => "https://api.weixin.qq.com/sns/jscode2session?" ."appid=%s&secret=%s&js_code=%s&grant_type=authorization_code",

    // 微信获取access_token的url地址
    'access_token_url' => "https://api.weixin.qq.com/cgi-bin/token?" ."grant_type=client_credential&appid=%s&secret=%s",

    'site_customer_service_phone' => '0775-7767564',
    'base_url' => 'https://web.daidaiquan.cn/'
];