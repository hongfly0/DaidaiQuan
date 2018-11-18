<?php
/**
 *  代办人商家
 */

namespace app\admin\controller;

use app\portal\model\AgentModel;
use cmf\controller\AdminBaseController;
use think\Db;

class AgentController extends AdminBaseController
{

    public function _initialize()
    {

    }

    //添加代办商家
    public function add_agent_store()
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

    /**
     * 处理添加代办商家
     * author Fox
     */
    public function post_add_agent_store(){

        $data = $_POST;

        $product_nums = $data['product_nums'];

        $product_infos = array();

        //检测产品是否存在
        if($product_nums){
            $product_infos = Db::table('ddq_product')->whereIn('product_no',$product_nums)->field('product_id,product_no')->select()->toArray();

            $product_array = array_column($product_infos,'product_no');

            $missing_msg = "";

            foreach ($product_nums as $val){
                if(!in_array($val,$product_array)){
                    $missing_msg .= " 产品编号:".$val."不存在;<br>";
                }
            }

            if(!empty($missing_msg)){
                return  $this->apifailed($missing_msg,array(),'-1');
            }
        }

        unset($data['product_nums']);

        $check_is_exits =  AgentModel::find(function($query) use ($data){
            $query->where('agent_mobile','=',$data['agent_mobile']);
        });

        if($check_is_exits){
            return  $this->apifailed('账户已存在',array(),'-2');
        }

        $data['create_at'] = date('Y-m-d H:i:s');
        $agent = Db::table('ddq_agent')->insert($data);
        $agentId = Db::name('ddq_agent')->getLastInsID();

        if($agent){
            foreach ($product_infos as $row) {
                $insert_array = array();

                $insert_array['product_id']  = $row['product_id'];
                $insert_array['product_no'] = $row['product_no'] ;
                $insert_array['create_at'] = date('Y-m-d H:i:s');
                $insert_array['update_at'] = date('Y-m-d H:i:s');
                $insert_array['agent_id']  = $agentId;

                Db::table('ddq_agent_product')->insert($insert_array);
            }

            return $this->apisucces('操作成功');
        }else{
            return $this->apifailed('操作失敗');
        }

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


}