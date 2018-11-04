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
namespace app\api\controller;

header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:*');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');

use app\portal\model\MemberModel;
use cmf\controller\HomeBaseController;
use think\Db;

class UserController extends HomeBaseController
{
    /**
     * 获取用户信息
     * author Fox
     */
    public function info()
    {
        $wxtoken = $_REQUEST['wxtoken'];

        $info = MemberModel::get(array('member_wx_token'=>$wxtoken));

        if(!$info){
            $this->apifailed('用户不存在');
        }else{
            $side_info = Db::table('ddq_option')->where('option_name','=','site_info')->find();

            $info['side_setting'] = json_decode($side_info['option_value']);
            $info['side_setting']->site_customer_service_phone = '111111';

            $this->apisucces('用户基本信息',$info);
        }
    }
}
