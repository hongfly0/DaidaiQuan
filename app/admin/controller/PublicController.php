<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use cmf\lib\Upload;
use think\Db;

class PublicController extends AdminBaseController
{
    public function _initialize()
    {
    }

    /**
     * 后台登陆界面
     */
    public function login()
    {
        $loginAllowed = session("__LOGIN_BY_CMF_ADMIN_PW__");
        if (empty($loginAllowed)) {
            //$this->error('非法登录!', cmf_get_root() . '/');
            return redirect(cmf_get_root() . "/");
        }

        $admin_id = session('ADMIN_ID');
        if (!empty($admin_id)) {//已经登录
            return redirect(url("admin/Index/index"));
        } else {
            $site_admin_url_password = config("cmf_SITE_ADMIN_URL_PASSWORD");
            $upw = session("__CMF_UPW__");
            if (!empty($site_admin_url_password) && $upw != $site_admin_url_password) {
                return redirect(cmf_get_root() . "/");
            } else {
                session("__SP_ADMIN_LOGIN_PAGE_SHOWED_SUCCESS__", true);
                $result = hook_one('admin_login');
                if (!empty($result)) {
                    return $result;
                }
                return $this->fetch(":login");
            }
        }
    }

    /**
     * 登录验证
     */
    public function doLogin()
    {
        if (hook_one('admin_custom_login_open')) {
            $this->error('您已经通过插件自定义后台登录！');
        }

        $loginAllowed = session("__LOGIN_BY_CMF_ADMIN_PW__");
        if (empty($loginAllowed)) {
            $this->error('非法登录!', cmf_get_root() . '/');
        }

        $captcha = $this->request->param('captcha');
        if (empty($captcha)) {
            $this->error(lang('CAPTCHA_REQUIRED'));
        }
        //验证码
        if (!cmf_captcha_check($captcha)) {
            $this->error(lang('CAPTCHA_NOT_RIGHT'));
        }

        $name = $this->request->param("username");
        if (empty($name)) {
            $this->error(lang('USERNAME_OR_EMAIL_EMPTY'));
        }
        $pass = $this->request->param("password");
        if (empty($pass)) {
            $this->error(lang('PASSWORD_REQUIRED'));
        }
        if (strpos($name, "@") > 0) {//邮箱登陆
            $where['user_email'] = $name;
        } else {
            $where['user_login'] = $name;
        }

        $result = Db::name('user')->where($where)->find();

        if (!empty($result) && $result['user_type'] == 1) {
            if (cmf_compare_password($pass, $result['user_pass'])) {
                $groups = Db::name('RoleUser')
                    ->alias("a")
                    ->join('__ROLE__ b', 'a.role_id =b.id')
                    ->where(["user_id" => $result["id"], "status" => 1])
                    ->value("role_id");
                if ($result["id"] != 1 && (empty($groups) || empty($result['user_status']))) {
                    $this->error(lang('USE_DISABLED'));
                }
                //登入成功页面跳转
                session('ADMIN_ID', $result["id"]);
                session('name', $result["user_login"]);
                $result['last_login_ip'] = get_client_ip(0, true);
                $result['last_login_time'] = time();
                $token = cmf_generate_user_token($result["id"], 'web');
                if (!empty($token)) {
                    session('token', $token);
                }
                Db::name('user')->where('id',$result['id'])->update($result);
                cookie("admin_username", $name, 3600 * 24 * 30);
                session("__LOGIN_BY_CMF_ADMIN_PW__", null);
                $this->success('登录成功', url("admin/Index/index"));
            } else {
                $this->error('密码不正确');
            }
        } else {
            $this->error(lang('USERNAME_NOT_EXIST'));
        }
    }

    /**
     * 后台管理员退出
     */
    public function logout()
    {
        session('ADMIN_ID', null);
        return redirect('http://www.daidaiquan.cn/#/index');
    }


    /**
     * 图片上传公用方法
     */
    public function upload_img()
    {
        $upload = new Upload();// 实例化上传类
        $upload->maxSize = 3145728;// 设置附件上传大小
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath = './Uploads/'; // 设置附件上传根目录
        $upload->savePath = ''; // 设置附件上传（子）目录
        // 上传文件
        $info = $upload->upload();

        if (!$info) {
            echo  json_encode(array('code'=>-1,'msg'=>$upload->getError()));
        } else {
            echo json_encode(array('code'=>1,'msg'=>'success','data'=>$info));
        }
    }

    /**
     * 富文本编辑器图片上传公用方法
     */
    public function Editupload_img()
    {
        $upload = new Upload();// 实例化上传类
        $upload->maxSize = 3145728;// 设置附件上传大小
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath = './Uploads/'; // 设置附件上传根目录
        $upload->savePath = ''; // 设置附件上传（子）目录
        // 上传文件
        $info = $upload->upload();

        if (!$info) {
            echo  json_encode(array('code'=>-1,'msg'=>$upload->getError()));
        } else {
            echo json_encode(array('code'=>0,'msg'=>'success','data'=>array('src'=>config('base_url').$info['url'])));
        }
    }
}