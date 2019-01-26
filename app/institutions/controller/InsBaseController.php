<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\institutions\controller;

use cmf\controller\BaseController;
use think\Db;

class InsBaseController extends BaseController
{

    public function _initialize()
    {
        // 监听admin_init
        hook('admin_init');
        parent::_initialize();
        $INS_ID = session('INS_ID');

        if (!empty($INS_ID)) {
            $user = Db::name('institutions')->where(['ins_id' => $INS_ID])->find();

            $this->assign("institutions", $user);
        } else {
            if ($this->request->isPost()) {
                $this->error("您还没有登录！", url("institutions/public/login"));
            } else {
                header("Location:" . url("institutions/public/login"));
                exit();
            }
        }
    }

    public function _initializeView()
    {
        $cmfAdminThemePath    = config('cmf_admin_theme_path');
        $cmfAdminDefaultTheme = cmf_get_current_admin_theme();

        $themePath = "{$cmfAdminThemePath}{$cmfAdminDefaultTheme}";

        $root = cmf_get_root();

        //使cdn设置生效
        $cdnSettings = cmf_get_option('cdn_settings');
        if (empty($cdnSettings['cdn_static_root'])) {
            $viewReplaceStr = [
                '__ROOT__'     => $root,
                '__TMPL__'     => "{$root}/{$themePath}",
                '__STATIC__'   => "{$root}/static",
                '__WEB_ROOT__' => $root
            ];
        } else {
            $cdnStaticRoot  = rtrim($cdnSettings['cdn_static_root'], '/');
            $viewReplaceStr = [
                '__ROOT__'     => $root,
                '__TMPL__'     => "{$cdnStaticRoot}/{$themePath}",
                '__STATIC__'   => "{$cdnStaticRoot}/static",
                '__WEB_ROOT__' => $cdnStaticRoot
            ];
        }

        $viewReplaceStr = array_merge(config('view_replace_str'), $viewReplaceStr);
        config('template.view_base', "$themePath/");
        config('view_replace_str', $viewReplaceStr);
    }
}