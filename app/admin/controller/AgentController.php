<?php
/**
 *  代办人商家
 */

namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;

class AgentController extends AdminBaseController
{

    public function _initialize()
    {

    }

    //添加代办商家
    public function addAgentStore()
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

        return $this->fetch();
    }


    // 代办商家列表
    public function agentStoreList()
    {
        $content = hook_one('admin_user_index_view');

        if (!empty($content)) {
            return $content;
        }

        $where = '1=1';

        /**搜索条件**/
        $key_word = $this->request->param('key_word');

        if ($key_word) {
            $where .= " and `agent_mobile` like '%". $key_word ."%'";
        }

        $agent = Db::name('agent')
            ->where($where)
            ->order("agent_id DESC")
            ->paginate(20);
        // 获取分页显示
        $page = $agent->render();

        $this->assign("page", $page);
        $this->assign("agent", $agent);
        return $this->fetch();
    }


    public function upload_pic()
    {
        var_dump($_FILES);
    }
}