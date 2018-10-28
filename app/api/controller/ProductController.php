<?php
namespace app\api\controller;

header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:*');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');

use app\portal\model\ProductQueryModel;
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

    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        $this::$page = empty($_REQUEST['page'])?1:$_REQUEST['page'];
        $this::$limit = empty($_REQUEST['limit'])?25:$_REQUEST['limt'];
        $this::$qv_ids = empty($_REQUEST['qv_ids'])?array():implode(',',$_REQUEST['qv_ids']);
        $this::$zone_ids = empty($_REQUEST['zone_ids'])?array():$_REQUEST['zone_ids'];
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
            ->where('product_status','1')
            ->where('product_id','IN',array_column($product_ids,'product_id'))
            ->select()->toArray();

        foreach ($result as &$value) {
            $product_query = ProductQueryModel::where('product_id',$value['product_id'])->select()->toArray();

            foreach ($product_query as $row){
                $value[$row['pt_en_name']] = $row['pv_value'];
            }
        }

        return $this->apisucces('product list',$result);
    }


    /**
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

        $result = $product_info;

        return $this->apisucces('产品详情',$result);
    }
}
