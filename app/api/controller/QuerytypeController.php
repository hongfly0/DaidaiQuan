<?php
/**
 * User: Fable
 */
namespace app\api\controller;

use app\portal\model\QueryTypeModel;
use app\portal\model\QueryValueModel;
use cmf\controller\HomeBaseController;
use think\Db;

class QuerytypeController extends HomeBaseController
{
    public function index(){

        $type_list = QueryTypeModel::field('qt_id,qt_en_name,qt_name')->order('qt_sort','asc')->select()->toArray();

        $value_list = QueryValueModel::field('qv_id,qt_id,qv_value')->order('qv_sort','asc')->select()->toArray();


        foreach ($type_list as &$type){
            for ($i=0; $i < count($value_list);$i++){
                if($type['qt_id'] == $value_list[$i]['qt_id']){
                    $type['values'][] =$value_list[$i];
                }
            }
        }

        $this->apisucces('筛选条件列表',$type_list);
    }
}
