<?php
namespace app\api\Service;
use app\api\Service\Token;
use app\portal\model\MemberModel;
use think\Exception;

class WxToken extends Token {
    protected $code;
    protected $Appid;
    protected $AppSecret;
    protected $LoginUrl;

    public function __construct($code,$type='app') {
        $this->code = $code;

        //从服务器换取 openid 需要传递三个参数
        // Appid、Appsecret、Code
        if($type=='web'){
            $this->Appid = config('web_appid');
            $this->AppSecret = config('web_appsecret');
            $url = config('web_get_access_token');
        }else{
            $this->Appid = config('app_id');
            $this->AppSecret = config('app_secret');
            $url = config('login_url');
        }

        //sprintf的作用是将字符串中占位符用特定值按顺序替换
        $this->LoginUrl = sprintf($url, $this->Appid, $this->AppSecret, $this->code);
    }
    /**
     * 根据用户传递 code 去微信服务器换取 openid
     */
    public function get() {
        $result = cmf_curl_get($this->LoginUrl);
        $wxResult = json_decode($result, true);

        if (empty($wxResult)) {
            throw new Exception("获取session_key和open_id失败，微信内部错误");
        }
        //验证获取令牌是否成功
        if (array_key_exists('errcode', $wxResult)) {
            return (array(
                'errorCode' => $wxResult['errcode'],
                'msg' => $wxResult['errmsg'],
            ));
        } else {
            $member_info = $this->grantToken($wxResult['openid']);
            $member_info->session_key = empty($wxResult['session_key'])?'':$wxResult['session_key'];
            $member_info->access_token = empty($wxResult['access_token'])?'':$wxResult['access_token'];
            return $member_info;
        }
    }
    /**
     * 颁发令牌 并将用户信息序列化成json，已token为键保存在本地缓存
     * 作用是 当用户不存在时创建用户   存在时返回用户 id
     */
    private function grantToken($openid) {
        $user = MemberModel::where('openid', $openid)->find();
        if (!$user) {
            $uid = MemberModel::create([
                'openid' => $openid,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            $uid = $user->member_id;
        }
        //存入缓存 key：生成返回客户端的令牌 value：openid + uid
        $key = $this->generateToken($uid);
        $user->wx_key = $key;
        $user->updated_at = date('Y-m-d H:i:s');
        $user->save();

        return $user;
    }
}