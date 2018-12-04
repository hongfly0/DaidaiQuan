<?php
namespace app\api\Service;


class Token {
    /**
     * 生成随机字符串 作为 token
     */
    public function generateToken() {
        $randChar = getRandChar(32);
        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        return md5($randChar . $timestamp);
    }
    /**
     * 根据用户携带的 token ，从缓存中读取用户信息
     */
    public static function getCurrentIdentity() {
        $token = Request::header('token');
        if (!$token) {
            throw new \app\common\exception\BaseException(['msg' => '请先登录']);
        }
        $identity = cache($token);
        if ($identity) {
            return $identity;
        } else {
            throw new \app\common\exception\BaseException(['msg' => '身份已过期，请重新登录']);
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
}
use think\Request;