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