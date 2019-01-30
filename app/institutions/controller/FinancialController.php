<?php
/**
 *
 */

namespace app\institutions\controller;

use cmf\controller\BaseController;
use think\Db;

class FinancialController extends BaseController
{
    public function _initialize()
    {
        // 监听admin_init
        hook('admin_init');
        parent::_initialize();
    }

    public function register(){
        return $this->fetch();
    }

    public function post_add_financial(){
        $data = $_POST;

        if(empty($data['ins_mobile'])){
            return $this->apifailed('缺少必要参数:手机号');
        }

        //检查手机是否已被占用
        $check_is_exit = Db::name('institutions')->where('ins_mobile',$data['ins_mobile'])->count();

        if($check_is_exit){
            return $this->apifailed('手机号码已被占用');
        }

        $data['ins_login_passwod'] = cmf_password($data['ins_login_passwod']);
        $data['create_at'] = date('Y-m-d H:i:s');
        $data['create_at'] = date('Y-m-d H:i:s');

        $res = Db::name('institutions')->insert($data);

        if($res){
            return $this->apisucces('提交申请成功,请耐心等待平台审核');
        }else{
            return $this->apifailed('添加失败');
        }
    }

    /**
     * 返回成功
     * author Fox
     */
    public function apisucces($msg='',$result=array()){
        $return = array();
        $msg = empty($msg)?'success':$msg;

        $return['code'] = '1';
        $return['msg']  = $msg;
        $return['data'] = $result;

        echo json_encode($return);
    }

    /**
     * 返回失败
     * author Fox
     */
    public function apifailed($msg='',$result=array(),$code='-1'){
        $return = array();
        $msg = empty($msg)?'failed':$msg;

        $return['code'] = $code;
        $return['msg']  = $msg;
        $return['data'] = $result;

        echo json_encode($return);
    }

    public function _initializeView()
    {
        $cmfAdminThemePath    = config('cmf_admin_theme_path');
        $cmfAdminDefaultTheme = cmf_get_current_admin_theme();

        $themePath = "{$cmfAdminThemePath}{$cmfAdminDefaultTheme}";

        $root = cmf_get_root();

        //使cdn设置生效
        $cdnSettings = cmf_get_option('cdn_settings');
        if (empty($cdnSettings['cdn_static_root'])) {
            $viewReplaceStr = [
                '__ROOT__'     => $root,
                '__TMPL__'     => "{$root}/{$themePath}",
                '__STATIC__'   => "{$root}/static",
                '__WEB_ROOT__' => $root
            ];
        } else {
            $cdnStaticRoot  = rtrim($cdnSettings['cdn_static_root'], '/');
            $viewReplaceStr = [
                '__ROOT__'     => $root,
                '__TMPL__'     => "{$cdnStaticRoot}/{$themePath}",
                '__STATIC__'   => "{$cdnStaticRoot}/static",
                '__WEB_ROOT__' => $cdnStaticRoot
            ];
        }

        $viewReplaceStr = array_merge(config('view_replace_str'), $viewReplaceStr);
        config('template.view_base', "$themePath/");
        config('view_replace_str', $viewReplaceStr);
    }
}