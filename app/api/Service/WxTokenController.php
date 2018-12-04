<?php
namespace app\api\Service;
use app\api\Service\Token;
use think\Exception;

class WxToken extends Token {
    protected $code;
    protected $Appid;
    protected $AppSecret;
    protected $LoginUrl;

    public function __construct($code) {
        $this->code = $code;

        //从服务器换取 openid 需要传递三个参数
        // Appid、Appsecret、Code
        $this->Appid = config('wx.app_id');
        $this->AppSecret = config('wx.app_secret');

        //sprintf的作用是将字符串中占位符用特定值按顺序替换
        $this->LoginUrl = sprintf(config('wx.login_url'), $this->Appid, $this->AppSecret, $this->code);
    }
    /**
     * 根据用户传递 code 去微信服务器换取 openid
     */
    public function get() {
        $result = curl_get($this->LoginUrl);
        $wxResult = json_decode($result, true);
        if (empty($wxResult)) {
            throw new Exception("获取session_key和open_id失败，微信内部错误");
        }
        //验证获取令牌是否成功
        if (array_key_exists('errcode', $wxResult)) {
            throw new Exception([
                'errorCode' => $wxResult['errcode'],
                'msg' => $wxResult['errmsg'],
            ]);
        } else {
            return $this->grantToken($wxResult['openid']);
        }
    }
    /**
     * 颁发令牌 并将用户信息序列化成json，已token为键保存在本地缓存
     * 作用是 当用户不存在时创建用户   存在时返回用户 id
     */
    private function grantToken($openid) {
        $user = User::where('openid', $openid)->find();
        if (!$user) {
            $uid = User::create([
                'openid' => $openid,
            ]);
        } else {
            $uid = $user->id;
        }
        //存入缓存 key：生成返回客户端的令牌 value：openid + uid
        $key = $this->generateToken();
        $cache_value['openid'] = $openid;
        $cache_value['uid'] = $uid;
        $expire = config('token.expire');
        if (!cache($key, $cache_value, $expire)) {
            throw new Exception("缓存客户令牌时出现错误");
        } else {
            return $key;
        }
    }
}