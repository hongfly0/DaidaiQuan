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

            if (!$this->checkAccess($INS_ID)) {
                $this->error("您没有访问权限！");
            }
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
}