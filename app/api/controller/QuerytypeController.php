<?php
/**
 * User: Fable
 */
namespace app\api\controller;

header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:*');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');


use app\portal\model\QueryTypeModel;
use app\portal\model\QueryValueModel;
use app\portal\model\ZoneModel;
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
                    $type['values'][] = $value_list[$i];
                }
            }
        }

        $zone_list = ZoneModel::field('z_id,z_name,z_pid')->select()->toArray();

        array_unshift($type_list,array('qt_id'=>0,'qt_en_name'=>'service_object','qt_name'=>'服务对象','values'=>array(array('qv_id'=>'service_object_1','qt_id'=>0,'qv_value'=>'对公'),array('qv_id'=>'service_object_2','qt_id'=>0,'qv_value'=>'对私'))));


        $this->apisucces('筛选条件列表',array('query_list'=>$type_list,'zone_list'=>$zone_list));
    }
}
