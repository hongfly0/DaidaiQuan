<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\portal\controller;

use cmf\controller\HomeBaseController;
use think\Db;

class InstitutionsController extends HomeBaseController
{
    public function index()
    {
        return $this->fetch(':ins_register');
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
            return $this->apisucces('提交申请成功');
        }else{
            return $this->apisucces('提交申请失败');
        }
    }
}
