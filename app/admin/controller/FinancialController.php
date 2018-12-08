<?php
/**
 *
 */

namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;
class FinancialController extends AdminBaseController
{
    public function _initialize()
    {

    }

    public function financialList()
    {
        $content = hook_one('admin_user_index_view');

        if (!empty($content)) {
            return $content;
        }

        $where = '1=1';

        /**搜索条件**/
        $key_word = $this->request->param('key_word');

        if ($key_word) {
            $where .= " and `ins_mobile` like '%". $key_word ."%'";
        }

        $agent = Db::name('institutions')
            ->where($where)
            ->order("ins_id DESC")
            ->paginate(20);
        // 获取分页显示
        $page = $agent->render();

        $this->assign("page", $page);
        $this->assign("ins", $agent);
        return $this->fetch();
    }

    public function addFinancial()
    {
        return $this->fetch();
    }

    public function post_add_financial(){
        $data = $_POST;

        if(empty($data['ins_mobile'])){
            return $this->apifailed('缺少必要参数:手机号');
        }

        //检查手机是否已被占用
        $check_is_exit = Db::name('institutions')->where('ins_mobile',$data['ins_mobile'])->count();

        $data['ins_login_passwod'] = cmf_password($data['ins_login_passwod']);
        $data['create_at'] = date('Y-m-d H:i:s');
        $data['create_at'] = date('Y-m-d H:i:s');

        $res = Db::name('institutions')->insert($data);

        if($res){
            return $this->apisucces('添加成功');
        }else{
            return $this->apisucces('添加失败');
        }
    }

    public function editFinancial()
    {
        $ins_id  = $_REQUEST['ins_id'];

        $financial = Db::name('institutions')->where('ins_id',$ins_id)->find();

        $this->assign('info',$financial);
        return $this->fetch();
    }

    /**
     * 保存金融机构信息
     */
    public function post_edit_financial(){
        $data = $_POST;

        $ins_id = $data['ins_id'];
        unset($data['ins_id']);

        $res = Db::name('institutions')->where('ins_id',$ins_id)->update($data);

        if($res){
            return $this->apisucces('更新成功');
        }else{
            return  $this->apisucces('更新成功');
        }
    }

    public function delFinancial()
    {
        $post = $this->request->param();
        if(!empty($post))
        {
            $info = Db::name('institutions')->where('ins_id = '.$post['id'])->find();
            if (!empty($info))
            {
                $res = Db::name('institutions')->where('ins_id = '.$post['id'])->delete();
                if ($res)
                {
                    $this->success("删除成功！");
                } else {
                    $this->error("删除失败，请刷新重试！");
                }
            }
        }
    }

    public function resetFinancialPassWord()
    {
        $post = $this->request->param();
        if(!empty($post))
        {
            $info = Db::name('institutions')->where('ins_id = '.$post['id'])->find();
           if (!empty($info) && $info['ins_login_passwod'] != cmf_password('ddq123456'))
           {
               $res = Db::name('institutions')->where('ins_id = '.$post['id'])->data(array('ins_login_passwod' => cmf_password('ddq123456')))->update();
               if ($res)
               {
                   $this->success("重置成功！", url("Financial/financialList"));
               } else {
                   $this->error("重置失败，请刷新重试！");
               }
           } else {
               $this->error("已重置，无需再次重置！");
           }
        }
    }

}