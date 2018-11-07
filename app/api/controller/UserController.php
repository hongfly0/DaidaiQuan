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
use app\portal\model\ProductModel;
use app\portal\model\ProductQueryModel;
use cmf\controller\HomeBaseController;
use think\Db;

class UserController extends HomeBaseController
{
    protected  static $page=1;
    protected  static $limit=25;

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

    /**
     * 个人收藏列表
     */
    public function collect_list(){

        $member_id = $_REQUEST['member_id'];

        $collect_ids = Db::table('ddq_collect_list');

        $collect_ids=$collect_ids->where('member_id','=',$member_id);

        $collect_ids = $collect_ids->field('product_id')->order('cp_id','desc')->page($this::$page,$this::$limit)->select()->toArray();

        $result = Db::table('ddq_product')
            ->where('product_status','1')
            ->where('product_id','IN',array_column($collect_ids,'product_id'))
            ->field('product_id,product_name,product_image,loan_use_dor,loan_time_limit,audit_cycle,view_num')
            ->select()
            ->toArray();

        foreach ($result as &$value) {
            $product_query = ProductQueryModel::where('product_id',$value['product_id'])->select()->toArray();

            foreach ($product_query as $row){
                $value[$row['pt_en_name']] = $row['pv_value'];
            }
        }

        return $this->apisucces('用户收藏列表',$result);

    }

    /**
     * 个人查看历史
     */
    public function view_list(){

        $member_id = $_REQUEST['member_id'];

        $view_ids = Db::table('ddq_view_list');

        $view_ids=$view_ids->where('member_id','=',$member_id);

        $view_ids = $view_ids->field('product_id')->order('vl_id','desc')->page($this::$page,$this::$limit)->select()->toArray();

        $result = Db::table('ddq_product')
            ->where('product_status','1')
            ->where('product_id','IN',array_column($view_ids,'product_id'))
            ->field('product_id,product_name,product_image,loan_use_dor,loan_time_limit,audit_cycle,view_num')
            ->select()
            ->toArray();

        foreach ($result as &$value) {
            $product_query = ProductQueryModel::where('product_id',$value['product_id'])->select()->toArray();

            foreach ($product_query as $row){
                $value[$row['pt_en_name']] = $row['pv_value'];
            }
        }

        return $this->apisucces('用户查看历史',$result);
    }
}
