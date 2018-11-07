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

    }



}