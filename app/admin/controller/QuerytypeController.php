<?php
/**
 *
 */

namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;

class QuerytypeController extends AdminBaseController
{
    public function _initialize()
    {

    }

    public function querytypeList()
    {
        $where = '1=1';

        /**搜索条件**/
        $key_word = $this->request->param('key_word');

        if ($key_word) {
            $where .= " and `ins_mobile` like '%". $key_word ."%'";
        }

        $query_type = Db::name('query_type')
            ->where($where)
            ->order("qt_sort ASC")
            ->select()->toArray();

        foreach ($query_type as &$value){
            $value['child_values'] = Db::name('query_value')->where('qt_id',$value['qt_id'])->select()->toArray();
        }

        $this->assign("infos", $query_type);
        return $this->fetch();
    }

    /**
     * author Fox
     * 显示添加类型界面
     */
    public function add_type(){
        $qt_id = empty($_REQUEST['qt_id'])?'':$_REQUEST['qt_id'];

        $this->assign('qt_id',$qt_id);
        return $this->fetch();
    }

    public function post_add_type(){
        $data = $_POST;

        $check_is_exist = Db::name('query_type')->where('qt_en_name',$data['qt_en_name'])->find();

        if($check_is_exist){
            return $this->apifailed('分类英文名已存在');
        }

        $data['create_at'] = date('Y-m-d H:i:s');
        $data['update_at'] = date('Y-m-d H:i:s');

        $res = Db::name('query_type')->insert($data);

        if($res){
            return  $this->apisucces('添加成功');
        }else{
            return  $this->apisucces('添加失敗');
        }

    }

    /**
     * 编辑类型界面
     */
    public function edit_type(){
        $qt_id = $_REQUEST['qt_id'];

        $info = Db::name('query_type')->where('qt_id',$qt_id)->find();

        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * 提交编辑类型
     */
    public function post_edit_type(){
        $data = $_POST;

        $qt_id = $data['qt_id'];
        unset($data['qt_id']);

        $res = Db::name('query_type')->where('qt_id',$qt_id)->update($data);

        if($res){
            return $this->apisucces('更新成功');
        }else{
            return $this->apifailed('更新失败');
        }
    }

    public function del_type(){
        $qt_id= $_REQUEST['qt_id'];

        $res = Db::name('query_type')->where('qt_id',$qt_id)->delete();

        if($res){
            return $this->apisucces('刪除成功');
        }else{
            return $this->apifailed('刪除失败');
        }
    }


    public function add_value(){
        $qt_id = empty($_REQUEST['qt_id'])?'':$_REQUEST['qt_id'];

        $query_types = Db::name('query_type')->order("qt_sort ASC")->select()->toArray();

        $this->assign('types',$query_types);
        $this->assign('qt_id',$qt_id);
        return $this->fetch();
    }

    public function post_add_value(){
        $data = $_POST;

        $data['create_at'] = date('Y-m-d H:i:s');
        $data['update_at'] = date('Y-m-d H:i:s');

        $res = Db::name('query_value')->insert($data);

        if($res){
            return  $this->apisucces('添加成功');
        }else{
            return  $this->apisucces('添加失败');
        }
    }

    /**
     * author Fox
     * 显示编辑value界面
     */
    public function edit_value(){
        $qv_id = $_REQUEST['qv_id'];

        $info = Db::name('query_value')->where('qv_id',$qv_id)->find();
        $query_types = Db::name('query_type')->order("qt_sort ASC")->select()->toArray();

        $this->assign('types',$query_types);
        $this->assign('info',$info);
        return $this->fetch();

    }


    public function post_edit_value(){
        $data = $_POST;
        $qv_id = $data['qv_id'];
        unset($data['qv_id']);

        $data['update_at'] = date('Y-m-d H:i:s');

        $res = Db::name('query_value')->where('qv_id',$qv_id)->update($data);

        if($res){
            return  $this->apisucces('修改成功');
        }else{
            return  $this->apisucces('修改失败');
        }
    }


    public function del_value(){
        $qt_id= $_REQUEST['qv_id'];

        $res = Db::name('query_value')->where('qv_id',$qt_id)->delete();

        if($res){
            return $this->apisucces('刪除成功');
        }else{
            return $this->apifailed('刪除成功');
        }
    }



}