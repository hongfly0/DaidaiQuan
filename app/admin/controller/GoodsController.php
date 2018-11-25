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