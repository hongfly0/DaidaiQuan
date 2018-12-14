<?php
namespace app\api\controller;

header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:*');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');

use app\portal\model\CollectListModel;
use app\portal\model\ProductQueryModel;
use app\portal\model\ViewListModel;
use cmf\controller\HomeBaseController;
use app\portal\model\ProductModel;
use think\Db;
use think\Request;

class ProductController extends HomeBaseController
{
    protected  static $page=1;
    protected  static $limit=25;
    protected  static $qv_ids=array();
    protected  static $zone_ids=array();
    protected  static $search_key = "";

    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        $this::$page = empty($_REQUEST['page'])?1:$_REQUEST['page'];
        $this::$limit = empty($_REQUEST['limit'])?25:$_REQUEST['limt'];
        $this::$qv_ids = empty($_REQUEST['qv_ids'])?array():implode(',',$_REQUEST['qv_ids']);
        $this::$zone_ids = empty($_REQUEST['zone_ids'])?array():$_REQUEST['zone_ids'];
        $this::$search_key = empty($_REQUEST['search_key'])?"":$_REQUEST['search_key'];
    }

    /**
     * 小程序筛选条件
     */
    public function query_value(){

    }

    /**
     * 產品列表
     * author Fox
     */
    public function index()
    {
        $product_ids = DB::table('ddq_product_query');

        if(!empty($this::$qv_ids)){
            $product_ids=$product_ids->where('pv_id','in',$this::$qv_ids);
        }

        $product_ids = $product_ids->distinct(true)->field('product_id')->page($this::$page,$this::$limit)->select()->toArray();

        $result = Db::table('ddq_product')
            ->where('product_status','1');

        if($product_ids){
            $result = $result->where('product_id','IN',array_column($product_ids,'product_id'));
        }

        if(!empty($this::$search_key)){
            $search_key = $this::$search_key;
            $result = $result->where(function ($query) use ($search_key){
                $query->where('product_name','LIKE',"%$search_key%")->whereor('product_detail','LIKE',"%$search_key%");
            });
        }

        $result = $result->select()->toArray();

        foreach ($result as &$value) {
            $product_query = ProductQueryModel::where('product_id',$value['product_id'])->select()->toArray();

            foreach ($product_query as $row){
                $value[$row['pt_en_name']] = $row['pv_value'];
            }

            if(!empty($_REQUEST['member_id'])){
                $value['collect_status'] = $this->checkCollect($_REQUEST['member_id'],$value['product_id']);
            }else{
                $value['collect_status'] = 0;
            }

        }

        return $this->apisucces('product list',$result);
    }


    /**=
     * 产品详情
     * author Fox
     */
    public function info()
    {
        $product_id = $_REQUEST['product_id'];

        if(empty($product_id)){
            return $this->apifailed('请输入产品id');
        }

        $productModel = new ProductModel();

        $product_info = $productModel::where('product_id',$product_id)->find()->toArray();

        if(!$product_info){
            return $this->apifailed('产品不存在');
        }

        $product_type = Db::name('query_type')->field('qt_id,qt_en_name,qt_name')->select()->toArray();

        $product_query = Db::name('product_query')->where('product_id',$product_id)->select()->toArray();

        foreach ($product_type as &$value){
            foreach ($product_query as $row){
                if($value['qt_id'] == $row['pt_id']){
                    $value['query_values'][] = array('pv_id'=>$row['pv_id'],'pv_value'=>$row['pv_value']);
                }
            }
        }

        if(!empty($_REQUEST['member_id'])){
            //检测是否已收藏
            $product_info['collect_status'] = $this->checkCollect($_REQUEST['member_id'],$_REQUEST['product_id']);
        }else{
            $product_info['collect_status'] = 0;
        }

        $product_info['product_query'] = $product_type;

        //获取地区字符
        $zone = Db::name('zone')->whereIn('z_id',$product_info['use_zone_ids'])->field('z_name')->select()->toArray();

        $product_info['use_zone_title'] = '';
        if($zone){
            $product_info['use_zone_title'] = implode(',',array_column($zone,'z_name'));
        }

        $result = $product_info;

        return $this->apisucces('产品详情',$result);
    }

    /**
     * 代办机构列表
     */
    public function agentList(){
        $product_id = $_REQUEST['product_id'];

        $result = Db::table('ddq_agent')->alias('a')
            ->join('ddq_agent_product ap','ap.agent_id=a.agent_id')
            ->where('ap.product_id','=',$product_id)
            ->where('a.agent_status','=',1)
            ->field('a.agent_id,a.agent_avatar,a.agent_name,a.agent_company,a.agent_company_address,a.agent_position,a.agent_company_owner,a.agent_company_phone,a.agent_mobile,a.agent_business_scope,a.agent_zode,a.agent_status')
            ->select()->toArray();

        $this->apisucces('代办人列表',$result);
    }

    /**
     * 产品查看
     */
    public function view(){
        $product_id = $_REQUEST['product_id'];
        $member_id = $_REQUEST['member_id'];

        Db::startTrans();

        $result = ViewListModel::create(array(
            'member_id' => $member_id,
            'product_id' => $product_id,
            'create_at'  => date('Y-m-d H:i:s')
        ));

        $result2 = Db::table('ddq_product')->where('product_id','=',$product_id)->setInc('view_num');

        if($result && $result2){
            Db::commit();
            $this->apisucces('查看成功');
        }else{
            Db::rollback();
            $this->apifailed('查看失败');
        }
    }

    /**
     * 收藏信息
     */
    public function collect(){
        $product_id = $_REQUEST['product_id'];
        $member_id = $_REQUEST['member_id'];
        $status = $_REQUEST['status'];

        if(empty($product_id) || empty($member_id) || empty($status)){
            return $this->apifailed('缺少必要参数');
        }

        $info = CollectListModel::get(array('member_id'=>$member_id,'product_id'=>$product_id));

        if($status == 1 && !empty($info)){
                return  $this->apifailed('已经收藏了');
        }elseif ($status==2 && empty($info)){
            return  $this->apifailed('你还没有收藏');
        }

        Db::startTrans();

        if($status==1){
           $result = CollectListModel::create(array(
               'member_id' => $member_id,
               'product_id' => $product_id,
               'create_at'  => date('Y-m-d H:i:s')
           ));
            $result2 = Db::table('ddq_product')->where('product_id','=',$product_id)->setInc('collect_num');
        }elseif ($status == 2){
            $result = CollectListModel::destroy(array('member_id'=>$member_id,'product_id'=>$product_id));

            $result2 = Db::table('ddq_product')->where('product_id','=',$product_id)->setDec('collect_num');
        }

        if($result && $result2){
            Db::commit();
            return  $this->apisucces('成功');
        }else{
            Db::rollback();
            return $this->apifailed('失败');
        }
    }

}
