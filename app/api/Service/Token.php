<?php
namespace app\api\Service;


class Token {
    /**
     * 生成随机字符串 作为 token
     */
    public function generateToken($id) {
        $randChar = $this->getRandChar(32);
        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        return md5($randChar . $timestamp).$id;
    }
    /**
     * 根据用户携带的 token ，从缓存中读取用户信息
     */
    public static function getCurrentIdentity() {
        $token = Request::header('token');
        if (!$token) {
            throw new Exception(['msg' => '请先登录']);
        }
        $identity = cache($token);
        if ($identity) {
            return $identity;
        } else {
            throw new Exception(['msg' => '身份已过期，请重新登录']);
        }
    }
    /**
     * 获得保存在缓存指定键的值
     */
    public static function getCurrentTokenVar($var) {
        $indentity = self::getCurrentIdentity();
        if (!$indentity[$var]) {
            throw new Exception(['msg' => '尝试获取的Token变量并不存在']);
        } else {
            return $indentity[$var];
        }
    }

    function getRandChar($length){
        $str = null;
        $strPol = "0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;

        for($i=0;$i<$length;$i++){
            $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }

        return $str;
    }
}
use think\Exception;
use think\Request;