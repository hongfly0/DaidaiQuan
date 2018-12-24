<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;


class MemberController extends AdminBaseController
{

    public function index()
    {

        $where = '1=1';

        $member_name = empty($_REQUEST['member_name'])?'':$_REQUEST['member_name'];

        if($member_name){
            $where .= " and member_name LIKE '%$member_name%' or member_mobile LIKE '%$member_name%' ";
        }

        $members = Db::name('member')
            ->where($where)
            ->order("member_id DESC")
            ->paginate(20);

        $result = array();

        foreach ($members as $val){

            $val['view_num'] = Db::name('view_list')->where('member_id',$val['member_id'])->count();
            $val['collect_num'] = Db::name('collect_list')->where('member_id',$val['member_id'])->count();

            $result[] = $val;
        }

        // 获取分页显示
        $page = $members->render();

        $this->assign('member_name',$member_name);
        $this->assign('members',$result);
        $this->assign('page',$page);
        return $this->fetch();
    }

    /**
     * 显示收藏产品列表
     */
    public function collect_list(){
        if (!empty($content)) {
            return $content;
        }
        $member_id = $this->request->param('member_id');

        $where = " 1=1 and cl.member_id = '$member_id' ";

        /**搜索条件**/
        $key_word = empty($this->request->param('key_word'))?'':$this->request->param('key_word');
        $ins_mobile = empty($this->request->param('ins_mobile'))?'':$this->request->param('ins_mobile');
        $service_object = empty($this->request->param('service_object'))?'':$this->request->param('service_object');
        $product_type = empty($this->request->param('product_type'))?'':$this->request->param('product_type');
        $product_status = empty($this->request->param('product_status'))?'':$this->request->param('product_status');
        $refund_type = empty($this->request->param('refund_type'))?'':$this->request->param('refund_type');
        $zone = empty($this->request->param('zones'))?'':$this->request->param('zones');


        if ($key_word) {
            $where .= " and p.product_name like '%". $key_word ."%'";
        }

        if($ins_mobile){
            $where .= " and i.ins_mobile like '%". $ins_mobile ."%'";
        }

        $query_arr =array();

        if($product_type){
            $query_arr[] = $product_type;
        }

        if($refund_type){
            $query_arr[] = $refund_type;
        }

        if($zone){
            $where .= " and p.use_zone_ids like '%".$zone."%'";
        }

        if($product_status) {
            $where .= " and p.product_status = '$product_status'";
        }else{
            $where .= " and p.product_status in (1,2)";
        }

        if($service_object) {
            $where .= " and p.service_object = '$service_object'";
        }

        if($query_arr){
            $query_str = implode(',',$query_arr);

            $where .= " and  pq.pv_id in ($query_str)";
        }

        $products = Db::name('product')
            ->alias('p')
            ->join('ddq_collect_list cl','p.product_id = cl.product_id')
            ->join('ddq_institutions i','i.ins_id = p.create_user_id')
            ->join('ddq_product_query pq', 'pq.product_id=p.product_id', 'LEFT')
            ->where($where)
            ->field('p.*,i.ins_name,i.ins_mobile')
            ->order("cl.create_at DESC")
            ->group('p.product_id')
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


        //获取产品类型
        $product_types=Db::name('query_value')->where('qt_id',1)->select()->toArray();
        //还款方式
        $refund_types= Db::name('query_value')->where('qt_id',2)->select()->toArray();
        //地区
        $zones = $this->getZone();

        $this->assign('zones',$zones);
        $this->assign('product_type',$product_types);
        $this->assign('refund_type',$refund_types);
        $this->assign("page", $page);
        $this->assign("products", $result);

        //搜索条件
        $this->assign('key_refund_type',$refund_type);
        $this->assign('key_ins_mobile',$ins_mobile);
        $this->assign('key_product_type',$product_type);
        $this->assign('key_zone',$zone);
        $this->assign('key_service_object',$service_object);
        $this->assign('key_product_status',$product_status);
        $this->assign('key_word',$key_word);

        return $this->fetch();
    }

    /**
     * 显示收藏产品列表
     */
    public function view_list(){
        if (!empty($content)) {
            return $content;
        }
        $member_id = $this->request->param('member_id');

        $where = " 1=1 and cl.member_id = '$member_id' ";

        /**搜索条件**/
        $key_word = empty($this->request->param('key_word'))?'':$this->request->param('key_word');
        $ins_mobile = empty($this->request->param('ins_mobile'))?'':$this->request->param('ins_mobile');
        $service_object = empty($this->request->param('service_object'))?'':$this->request->param('service_object');
        $product_type = empty($this->request->param('product_type'))?'':$this->request->param('product_type');
        $product_status = empty($this->request->param('product_status'))?'':$this->request->param('product_status');
        $refund_type = empty($this->request->param('refund_type'))?'':$this->request->param('refund_type');
        $zone = empty($this->request->param('zones'))?'':$this->request->param('zones');


        if ($key_word) {
            $where .= " and p.product_name like '%". $key_word ."%'";
        }

        if($ins_mobile){
            $where .= " and i.ins_mobile like '%". $ins_mobile ."%'";
        }

        $query_arr =array();

        if($product_type){
            $query_arr[] = $product_type;
        }

        if($refund_type){
            $query_arr[] = $refund_type;
        }

        if($zone){
            $where .= " and p.use_zone_ids like '%".$zone."%'";
        }

        if($product_status) {
            $where .= " and p.product_status = '$product_status'";
        }else{
            $where .= " and p.product_status in (1,2)";
        }

        if($service_object) {
            $where .= " and p.service_object = '$service_object'";
        }

        if($query_arr){
            $query_str = implode(',',$query_arr);

            $where .= " and  pq.pv_id in ($query_str)";
        }

        $products = Db::name('product')
            ->alias('p')
            ->join('ddq_view_list cl','p.product_id = cl.product_id')
            ->join('ddq_institutions i','i.ins_id = p.create_user_id')
            ->join('ddq_product_query pq', 'pq.product_id=p.product_id', 'LEFT')
            ->where($where)
            ->field('p.*,i.ins_name,i.ins_mobile')
            ->order("cl.create_at DESC")
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


        //获取产品类型
        $product_types=Db::name('query_value')->where('qt_id',1)->select()->toArray();
        //还款方式
        $refund_types= Db::name('query_value')->where('qt_id',2)->select()->toArray();
        //地区
        $zones = $this->getZone();

        $this->assign('zones',$zones);
        $this->assign('product_type',$product_types);
        $this->assign('refund_type',$refund_types);
        $this->assign("page", $page);
        $this->assign("products", $result);

        //搜索条件
        $this->assign('key_refund_type',$refund_type);
        $this->assign('key_ins_mobile',$ins_mobile);
        $this->assign('key_product_type',$product_type);
        $this->assign('key_zone',$zone);
        $this->assign('key_service_object',$service_object);
        $this->assign('key_product_status',$product_status);
        $this->assign('key_word',$key_word);

        return $this->fetch();
    }

}