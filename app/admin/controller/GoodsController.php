<?php
/**
 *
 */

namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;

class GoodsController extends AdminBaseController{

    public function _initialize()
    {

    }

    public function goodsList()
    {
        $content = hook_one('admin_user_index_view');

        if (!empty($content)) {
            return $content;
        }

        $where = '1=1';

        /**搜索条件**/
        $key_word = $this->request->param('key_word');

        if ($key_word) {
            $where .= " and `product_name` like '%". $key_word ."%'";
        }

        $products = Db::name('product')
            ->where($where)
            ->order("product_id DESC")
            ->paginate(20);
        // 获取分页显示
        $page = $products->render();

        $result= array();

        foreach ($products as &$val){
            $product_query = Db::name('product_query')->where('product_id',$val['product_id'])->select()->toArray();

            foreach ($product_query as $row){
                $val[$row['pt_en_name']] = $row['pv_value'];
            }
            $result[]= $val;
        }

        $this->assign("page", $page);
        $this->assign("products", $result);
        return $this->fetch();
    }

    public function addGoods()
    {
        $content = hook_one('admin_setting_site_view');

        if (!empty($content)) {
            return $content;
        }

        $noNeedDirs     = [".", "..", ".svn", 'fonts'];
        $adminThemesDir = config('cmf_admin_theme_path') . config('cmf_admin_default_theme') . '/public/assets/themes/';
        $adminStyles    = cmf_scan_dir($adminThemesDir . '*', GLOB_ONLYDIR);
        $adminStyles    = array_diff($adminStyles, $noNeedDirs);
        $cdnSettings    = cmf_get_option('cdn_settings');
        $cmfSettings    = cmf_get_option('cmf_settings');
        $adminSettings  = cmf_get_option('admin_settings');


        $this->assign('site_info', cmf_get_option('site_info'));
        $this->assign("admin_styles", $adminStyles);
        $this->assign("templates", []);
        $this->assign("cdn_settings", $cdnSettings);
        $this->assign("admin_settings", $adminSettings);
        $this->assign("cmf_settings", $cmfSettings);


        //获取需要填的项目
        $types = Db::name('query_type')->order('qt_sort')->select()->toArray();

        $values = Db::name('query_value')->order('qv_sort')->select()->toArray();

        $new_values = array();

        foreach ($values as $row){
            $new_values[$row['qt_id']][] = $row;
        }

        $new_types = array();

        foreach ($types as $trows){
            $trows['values'] = $new_values[$trows['qt_id']];

            $new_types[] = $trows;
        }

        $this->assign("types", $new_types);

        return $this->fetch();
    }

    public function post_add_goods(){
        $data = file_get_contents("php://input");
        $request_data = \Qiniu\json_decode($data,true);

        $insert_data = array();
        $insert_query = array();

        foreach ($request_data as $value){

            if(strstr($value['name'],'query_array')){
                $insert_query = array_merge($insert_query,$value['values']);
            }else{
                $insert_data[$value['name']] = empty($value['value'])?"":$value['value'];
            }
        }


        $query_type_values = Db::name('query_value')->alias('qv')->join('ddq_query_type qt','qv.qt_id=qt.qt_id')
                            ->field('qv.qv_id,qv.qv_value,qv.qt_id,qt.qt_en_name,qt.qt_name')
                            ->whereIn('qv.qv_id',$insert_query)->select()->toArray();

        Db::startTrans();

        $insert_data['create_user_id'] = 1;

        $insert_res = Db::name('product')->insertGetId($insert_data);


        $insert_query_data = array();

        foreach ($query_type_values as $row){
            $insert_query_data[] = array(
                "product_id" => $insert_res,
                "pt_id" => $row['qt_id'],
                "pt_en_name" => $row['qt_en_name'],
                "pt_name" => $row['qt_name'],
                "pv_id" => $row['qv_id'],
                "pv_value" => $row['qv_value']
            );
        }

        $insert_query_res = Db::name('product_query')->insertAll($insert_query_data);

        if($insert_res && $insert_query_res){
            Db::commit();

            return $this->apisucces('添加成功');
        }else{
            Db::rollback();

            return $this->apifailed('添加失败');
        }
    }

    public function updateGoods()
    {

    }

    public function delGoods()
    {

    }

    public function updateGoodsBrowse()
    {

    }

    public function selectGoodsCollectionList()
    {

    }

    public function selectGoodsBrowseList()
    {

    }
}