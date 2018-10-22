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

use app\portal\model\ProductQueryModel;
use cmf\controller\HomeBaseController;
use app\portal\model\ProductModel;


class ListController extends HomeBaseController
{
    /**
     * 获取列表数据
     * author Fox
     */
    public function index()
    {
        //$ProductModel = new ProductModel();
        $result = ProductModel::where('set_hot','=','1')
                    ->where('product_status','=','1')
                    ->order('view_num','desc')
                    ->field('product_id,product_name,product_image,loan_use_dor,loan_time_limit,audit_cycle,view_num')
                    ->select()->toArray();

        foreach ($result as &$value) {

            $product_query = ProductQueryModel::where('product_id',$value['product_id'])->select()->toArray();

            foreach ($product_query as $row){
                $value[$row['pt_en_name']] = $row['pv_value'];
            }
        }

        return $this->apisucces('Index api',$result);
    }
}
