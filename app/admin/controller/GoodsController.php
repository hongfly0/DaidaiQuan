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
            ->join('ddq_institutions i','i.ins_id = p.create_user_id')
            ->join('ddq_product_query pq', 'pq.product_id=p.product_id', 'LEFT')
            ->where($where)
            ->field('p.*,i.ins_name,i.ins_mobile')
            ->order("p.product_id DESC")
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

        $zones = $this->zonelist();

        $this->assign('zones',$zones);
        $this->assign("types", $new_types);
        return $this->fetch();
    }

    public function editGoods()
    {
        $content = hook_one('admin_setting_site_view');

        $product_id = $_REQUEST['product_id'];

        if (!empty($content)) {
            return $content;
        }

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

        $product_info = Db::name('product')->where('product_id',$product_id)->find();
        $product_query = Db::name('product_query')->where('product_id',$product_id)->select()->toArray();

        $this->assign('product',$product_info);
        $this->assign('product_query',json_encode($product_query));
        $this->assign("types", $new_types);

        $zones = $this->zonelist();
        $this->assign('zones',$zones);

        return $this->fetch();
    }

    public function post_add_goods(){
        $data = file_get_contents("php://input");
        $request_data = \Qiniu\json_decode($data,true);

        $insert_data = array();
        $insert_query = array();

        foreach ($request_data as $value){
            if($value['name']=='zones'){
                continue;
            }

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
        $insert_data['product_status'] = 4;
        $insert_data['create_at'] = date('Y-m-d H:i:s');
        $insert_data['update_at'] = date('Y-m-d H:i:s');
        
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

    /*
     * 刷新产品
     */
    public function refresh_product(){
        if(empty($_REQUEST['product_id'])){
            return  $this->apifailed('产品id不能为空');
        }
        $product_id = $_REQUEST['product_id'];

        $res = Db::name('product')->where('product_id',$product_id)->update(array('refresh_time'=>date('Y-m-d H:i:s')));

        if($res){
            return $this->apisucces('刷新成功');
        }else{
            return $this->apifailed('刷新失败');
        }
    }

    /*
     * 暂停
     */
    public function pause_product(){
        if(empty($_REQUEST['product_id'])){
            return  $this->apifailed('产品id不能为空');
        }
        $product_id = $_REQUEST['product_id'];

        $product_status  = Db::name('product')->where('product_id',$product_id)->value('product_status');

        if($product_status==2){
            $update_product_status = 1;
        }elseif ($product_status == 1){
            $update_product_status = 2;
        }else{
            $this->apifailed('当前状态不允许修改');
            return  false;
        }

        $res = Db::name('product')->where('product_id',$product_id)->update(array('product_status'=>$update_product_status));

        if($res){
            return $this->apisucces('更改成功',array('product_status'=>$update_product_status));
        }else{
            return $this->apifailed('更改失败');
        }
    }

    /**
     * 产品详情
     */
    public function product_detail(){
        $product_id = $_REQUEST['product_id'];

        $info = Db::name('product')->where('product_id',$product_id)->find();

        $product_type = Db::name('query_type')->field('qt_id,qt_en_name,qt_name')->select()->toArray();

        $product_query = Db::name('product_query')->where('product_id',$product_id)->select()->toArray();

        foreach ($product_type as &$value){
            $value['query_values'] = array();
            foreach ($product_query as $row){
                if($value['qt_id'] == $row['pt_id']){
                    $value['query_values'][] = array('pv_id'=>$row['pv_id'],'pv_value'=>$row['pv_value']);
                }
            }
        }

        $info['product_query'] = $product_type;

        //获取地区字符
        $zone = Db::name('zone')->whereIn('z_id',$info['use_zone_ids'])->field('z_name')->select()->toArray();

        $info['use_zone_title'] = '';
        if($zone){
            $info['use_zone_title'] = implode(',',array_column($zone,'z_name'));
        }

        $this->assign("info", $info);
        return $this->fetch();
    }



    public function updateGoods()
    {

    }

    public function delGoods()
    {
        if(empty($_REQUEST['product_id'])){
            return $this->apifailed('缺少必要参数');
        }

        $product_id = $_REQUEST['product_id'];

        Db::startTrans();

        $del_pro_res = Db::name('product')->where('product_id',$product_id)->delete();

        $del_pq_res = Db::name('product_query')->where('product_id',$product_id)->delete();

        if($del_pq_res && $del_pro_res){
            Db::commit();
            return $this->apisucces('删除成功');
        }else{
            Db::rollback();
            return $this->apifailed('删除失败');
        }
    }

    /**
     * 提交修改保存
     */
    public function post_save_goods(){
        $data = file_get_contents("php://input");
        $request_data = \Qiniu\json_decode($data,true);

        $update_data = array();
        $insert_query = array();

        foreach ($request_data as $value){
            if($value['name']=='zones'){
                continue;
            }

            if(strstr($value['name'],'query_array')){
                $insert_query = array_merge($insert_query,$value['values']);
            }else{
                $update_data[$value['name']] = empty($value['value'])?"":$value['value'];
            }
        }

        $query_type_values = Db::name('query_value')->alias('qv')->join('ddq_query_type qt','qv.qt_id=qt.qt_id')
            ->field('qv.qv_id,qv.qv_value,qv.qt_id,qt.qt_en_name,qt.qt_name')
            ->whereIn('qv.qv_id',$insert_query)->select()->toArray();

        $product_id = $update_data['product_id'];

        unset($update_data['product_id']);

        Db::startTrans();

        $update_data['create_user_id'] = 1;
        $update_data['create_at'] = date('Y-m-d H:i:s');
        $update_data['update_at'] = date('Y-m-d H:i:s');

        $update_res = Db::name('product')->where('product_id',$product_id)->update($update_data);

        $insert_query_data = array();

        foreach ($query_type_values as $row){
            $insert_query_data[] = array(
                "product_id" => $product_id,
                "pt_id" => $row['qt_id'],
                "pt_en_name" => $row['qt_en_name'],
                "pt_name" => $row['qt_name'],
                "pv_id" => $row['qv_id'],
                "pv_value" => $row['qv_value']
            );
        }

        $del_query_res = Db::name('product_query')->where('product_id',$product_id)->delete();

        $insert_query_res = Db::name('product_query')->insertAll($insert_query_data);

        if($update_res && $insert_query_res && $del_query_res){
            Db::commit();
            return $this->apisucces('更新成功');
        }else{
            Db::rollback();
            return $this->apifailed('更新失败');
        }
    }


    public function review()
    {
        $where = '1=1';

        /**搜索条件**/
        $key_word = $this->request->param('key_word');

        if ($key_word) {
            $where .= " and `product_name` like '%". $key_word ."%'";
        }

        $products = Db::name('product')
            ->where($where)
            ->whereIn('product_status',array('4'))
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

    public function review_show()
    {
        $content = hook_one('admin_setting_site_view');

        $product_id = $_REQUEST['product_id'];

        if (!empty($content)) {
            return $content;
        }

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

        $product_info = Db::name('product')->where('product_id',$product_id)->find();
        $product_query = Db::name('product_query')->where('product_id',$product_id)->select()->toArray();

        $this->assign('product',$product_info);
        $this->assign('product_query',json_encode($product_query));
        $this->assign("types", $new_types);

        return $this->fetch();
    }

    /**
     * 保存金融机构审核信息
     */
    public function post_review_goods(){
        $data = $_POST;

        $product_id = $data['product_id'];
        unset($data['product_id']);

        $check_no = Db::name('product')->where('product_no',$data['product_no'])->find();

        if($check_no){
            return  $this->apifailed($data['product_no'].'，产品编号已存在');
        }

        $data['update_at'] = date('Y-m-d H:i:s');
        $data['audit_user_id'] = session('ADMIN_ID');
        $data['audit_user_name'] = session('name');

        $res = Db::name('product')->where('product_id',$product_id)->update($data);

        if($res){
            return $this->apisucces('操作成功');
        }else{
            return  $this->apisucces('操作失败');
        }
    }

    /**
     * 获取zone
     */
    public function zonelist(){
        $where = '1=1 and z_pid=0';

        $zone_p1 = Db::name('zone')
            ->where($where)
            ->order("z_id ASC")
            ->select()->toArray();

        foreach ($zone_p1 as &$value){
            $zone_p2  = Db::name('zone')->where('z_pid',$value['z_id'])->select()->toArray();

            foreach ($zone_p2 as &$value2){
                $zone_p3  = Db::name('zone')->where('z_pid',$value2['z_id'])->select()->toArray();
                $value2['child_values'] = $zone_p3;
            }

            $value['child_values'] = $zone_p2;
        }

        return $zone_p1;
    }
}