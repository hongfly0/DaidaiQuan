<?php
/**
 *  代办人商家
 */

namespace app\portal\controller;

use app\portal\model\AgentModel;
use cmf\controller\AdminBaseController;
use cmf\controller\HomeBaseController;
use think\Db;

class AgentController extends HomeBaseController
{

    public function _initialize()
    {

    }

    //代办商家申请
    public function index()
    {
        return $this->fetch(':agent_register');
    }

    /**
     * 处理添加代办商家
     * author Fox
     */
    public function post_add_agent_store(){

        $data = $_POST;

        $check_is_exits =  AgentModel::find(function($query) use ($data){
            $query->where('agent_mobile','=',$data['agent_mobile']);
        });

        if($check_is_exits){
            return  $this->apifailed('手机号码已被占用',array(),'-2');
        }

        $data['create_at'] = date('Y-m-d H:i:s');
        $agent = Db::table('ddq_agent')->insert($data);

        if($agent){
            return $this->apisucces('操作成功');
        }else{
            return $this->apifailed('操作失败');
        }
    }
}