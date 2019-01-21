<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\api\controller;

header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:*');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');

use app\api\Service\WxToken;
use app\portal\model\MemberModel;
use app\portal\model\ProductModel;
use app\portal\model\ProductQueryModel;
use cmf\controller\HomeBaseController;
use think\Db;
use wxapp\aes\WXBizDataCrypt;

class UserController extends HomeBaseController
{
    protected  static $page=1;
    protected  static $limit=25;

    /**
     *
     */
    public function getUserInfo() {
        $code = $this->request->param('code');
        $iv = $this->request->param('iv');
        $encryptedData = $this->request->param('encryptedData');

        if(empty($code)){
            return  $this->apifailed('缺少必要參數code');
        }

        if(empty($iv)){
            return  $this->apifailed('缺少必要參數iv');
        }

        if(empty($encryptedData)){
            return  $this->apifailed('缺少必要參數encryptedData');
        }

        $WxToken = new WxToken($code);

        $memger_info = $WxToken->get();

        $WXBizDataCrypt = New WXBizDataCrypt(config('app_id'),$memger_info->session_key);

        $wx_user_info = $WXBizDataCrypt->decryptData($encryptedData,$iv,$data);

        $wx_user_data = json_decode($wx_user_info,true);

        if(empty($memger_info->member_avatar_url)){
            $memger_info->member_name = empty($wx_user_data['nickName'])?date('YmdHis'):$wx_user_data['nickName'];
            $memger_info->member_avatar_url = $wx_user_data['avatarUrl'];
        }
        $memger_info->updated_at = date('Y-m-d H:i:s');
        $memger_info->save();

        $side_info = Db::table('ddq_option')->where('option_name','=','site_info')->find();
        $memger_info->side_setting = json_decode($side_info['option_value']);
        $memger_info->side_setting->site_customer_service_phone = config('site_customer_service_phone');

        return $this->apisucces('用戶基本信息' ,$memger_info);
    }


    /**
     * 获取用户信息
     * author Fox
     */
    public function info()
    {
        if(empty($_REQUEST['openid'])){
            return $this->apifailed('缺少必要参数:用戶openid');
        }

        $nickname = empty($_REQUEST['nickName'])?'':$_REQUEST['nickName'];
        $avatar = empty($_REQUEST['member_avatar_url'])?'':$_REQUEST['member_avatar_url'];
        $openid = $_REQUEST['openid'];

        $info = MemberModel::get(array('openid'=>$openid));

        if(!$info){
            $uid = MemberModel::create([
                'member_name' => $nickname,
                'member_avatar_url' => $avatar,
                'openid' => $openid,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $info = MemberModel::get(array('openid'=>$openid));
        }

        $side_info = Db::table('ddq_option')->where('option_name','=','site_info')->find();

        $info['side_setting'] = json_decode($side_info['option_value']);
        $info['side_setting']->site_customer_service_phone = config('site_customer_service_phone');

        $this->apisucces('用户基本信息',$info);
    }

    /**
     * 个人收藏列表
     */
    public function collect_list(){

        $member_id = $_REQUEST['member_id'];

        $collect_ids = Db::name('collect_list');

        $collect_ids=$collect_ids->where('member_id','=',$member_id);

        $collect_ids = $collect_ids->field('product_id')->order('cp_id','desc')->page($this::$page,$this::$limit)->select()->toArray();

        $msg_array = array();

        $total = Db::name('collect_list')->where('member_id','=',$member_id)->distinct('product_id')->count(1);

        $msg_array['total_page'] = ceil ($total/$this::$limit);

        $result = Db::name('product')
            ->where('product_status','1')
            ->where('product_id','IN',array_column($collect_ids,'product_id'))
            ->field('product_id,product_name,product_image,loan_use_dor,loan_time_limit,audit_cycle,view_num')
            ->select()
            ->toArray();

        foreach ($result as &$value) {
            $product_query = ProductQueryModel::where('product_id',$value['product_id'])->select()->toArray();

            if(!empty($_REQUEST['member_id'])){
                $value['collect_status'] = $this->checkCollect($_REQUEST['member_id'],$value['product_id']);
            }else{
                $value['collect_status'] = 0;
            }

            foreach ($product_query as $row){
                $value[$row['pt_en_name']] = $row['pv_value'];
            }
        }

        return $this->apisucces('用户收藏列表',$result,$msg_array);

    }

    /**
     * 个人查看历史
     */
    public function view_list(){

        $member_id = $_REQUEST['member_id'];

        $view_ids = Db::name('view_list');

        $view_ids=$view_ids->where('member_id','=',$member_id);

        $msg_array = array();

        $total = Db::name('view_list')->where('member_id','=',$member_id)->distinct('product_id')->count(1);

        $msg_array['total_page'] = ceil ($total/$this::$limit);

        $view_ids = $view_ids->field('product_id')->order('vl_id','desc')->page($this::$page,$this::$limit)->select()->toArray();

        $result = Db::table('ddq_product')
            ->where('product_status','1')
            ->where('product_id','IN',array_column($view_ids,'product_id'))
            ->field('product_id,product_name,product_image,loan_use_dor,loan_time_limit,audit_cycle,view_num')
            ->select()
            ->toArray();

        foreach ($result as &$value) {
            $product_query = ProductQueryModel::where('product_id',$value['product_id'])->select()->toArray();

            if(!empty($_REQUEST['member_id'])){
                $value['collect_status'] = $this->checkCollect($_REQUEST['member_id'],$value['product_id']);
            }else{
                $value['collect_status'] = 0;
            }

            foreach ($product_query as $row){
                $value[$row['pt_en_name']] = $row['pv_value'];
            }
        }

        return $this->apisucces('用户查看历史',$result,$msg_array);
    }

    /**
     * 获取手机
     */
    public function getUserPhone(){
        $member_id = $this->request->param('member_id');
        $session_key= $this->request->param('session_key');
        $iv = $this->request->param('iv');
        $encryptedData = $this->request->param('encryptedData');

        $WXBizDataCrypt = New WXBizDataCrypt(config('app_id'),$session_key);

        $wx_user_info = $WXBizDataCrypt->decryptData($encryptedData,$iv,$data);

        $wx_user_data = json_decode($wx_user_info,true);

        $update['member_mobile'] = $wx_user_data['phoneNumber'];

        $res = Db::name('member')->where('member_id',$member_id)->update($update);

        if($res){
            return $this->apisucces('获取手机号码成功');
        }else{
            return $this->apifailed('获取手机号码失败');
        }
    }

    /**
     *网页端获取用户信息
     */
    public function getWebUserInfo()
    {
        $code = $this->request->param('code');

        $WxToken = new WxToken($code,'web');

        $memger_info = $WxToken->get();

        $get_user_url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$memger_info->access_token."&openid=".$memger_info->openid;

        $user_info = cmf_curl_get($get_user_url);

        $user_info = json_decode($user_info,true);

        $update_user_data = array();

        if(empty($memger_info->name)){
            $memger_info->member_name = $user_info['nickname'];
            $memger_info->member_avatar_url = $user_info['headimgurl'];

            $update_user_data['member_name'] = $user_info['nickname'];
            $update_user_data['member_avatar_url'] = $user_info['headimgurl'];
            $res = MemberModel::where('openid', $memger_info->openid)->save($update_user_data);
        }

        $info = json_decode(json_encode($memger_info),true);

        $side_info = Db::table('ddq_option')->where('option_name','=','site_info')->find();
        $info['side_setting'] = json_decode($side_info['option_value']);
        $info['side_setting']->site_customer_service_phone = config('site_customer_service_phone');

        return  $this->apisucces('网页端用户信息',$info);
    }
}
