<?php
/**
 *
 */

namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;

class ZoneController extends AdminBaseController
{
    public function _initialize()
    {

    }

    /**
     * 区域列表
     * @return mixed
     * author Fox
     */
    public function index()
    {
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

        $this->assign("zones", $zone_p1);
        return $this->fetch();
    }

    /**
     * author Fox
     * 显示添加类型界面
     */
    public function add_zone(){
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

        $this->assign("zones", $zone_p1);
        return $this->fetch();
    }

    public function post_add_zone(){
        $data = $_POST;

        $data['create_at'] = date('Y-m-d H:i:s');
        $data['update_at'] = date('Y-m-d H:i:s');

        $res = Db::name('zone')->insert($data);

        if($res){
            return  $this->apisucces('添加成功');
        }else{
            return  $this->apisucces('添加失敗');
        }

    }

    /**
     * 编辑类型界面
     */
    public function edit_zone(){
        $z_id = $_REQUEST['z_id'];

        $info = Db::name('zone')->where('z_id',$z_id)->find();

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

        $this->assign("zones", $zone_p1);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * 提交编辑类型
     */
    public function post_edit_zone(){
        $data = $_POST;

        $z_id = $data['z_id'];
        unset($data['z_id']);

        $res = Db::name('zone')->where('z_id',$z_id)->update($data);

        if($res){
            return $this->apisucces('更新成功');
        }else{
            return $this->apifailed('更新失败');
        }
    }

    public function del_zone(){
        $z_id= $_REQUEST['z_id'];

        $res = Db::name('zone')->where('z_id',$z_id)->delete();

        if($res){
            return $this->apisucces('刪除成功');
        }else{
            return $this->apifailed('刪除失败');
        }
    }
}