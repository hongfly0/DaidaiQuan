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

    }

    public function updateFinancial()
    {

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
           if (!empty($info) && $info['ins_login_passwod'] != md5('ddq123456'))
           {
               $res = Db::name('institutions')->where('ins_id = '.$post['id'])->data(array('ins_login_passwod' => md5('ddq123456')))->update();
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