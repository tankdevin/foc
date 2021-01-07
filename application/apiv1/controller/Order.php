<?php

namespace app\apiv1\controller;

use controller\BasicIndex;
use function GuzzleHttp\Psr7\str;
use think\console\command\Lists;
use think\Db;
use service\LogService;
use think\Controller;
use think\db\Where;



/**xlqg
 * 应用入口控制器
 */
class Order extends Base
{
    const MS = 0.7;
    const XL = 0.3;
    public $uid;

    static $allOrderMoney;

    public function __construct()
    {
        parent::__construct();
        $this->uid = $this->id;
    }

    //团购更多
    public function tuangroupList()
    {
    	
        $page = request()->param("pageNo", 1);
        $num = request()->param("pageNo", 10);
        $start = ($page - 1) * $num;
        $list = Db::name('store_goods')->field('id,goods_title,goods_logo,market_price,is_deleted,status')->where('is_deleted', 0)->where('status', 1)->order('sort asc')->page($page, 20)->select();

        /*foreach ($list as $k => $v) {
            $list[$k]['money'] = $v['selling_price'] * sysconf('shop_fpfq') / 100; //(购买商品返批发卷%)
            $list[$k]['groupName'] = '团购专区';
            $list[$k]['goods_title'] = $v['goods_title'];//、、substr($v['goods_title'], 0, 15);
        }*/
        $this->success('',$list);
    }
    
    public function goodsdetailed()
    {
        $goods = input('param.goods_id');
    
        $num = input('param.num');
        
        $list = Db::name('store_goods')->where('is_deleted', 0)->find($goods);
        $list['goods_image'] = explode("|", $list['goods_image']);
        //$list['xfg'] = $list['selling_price'] * sysconf('xsmschj') / 100;
        $list['goods_content'] = str_replace("<img", "<img style = 'width:100%'", $list['goods_content']);
        //$list['xianjin'] = $list['market_price']*$num * $list['bili_money']/100;
        $list['xianjin'] = $list['bili_money']*$num;
        $list['opf'] = ($list['market_price']*$num - $list['xianjin'])/sysconf('pdr_money');
		$this->success('',$list);
    }
    
    



   /**
     * 用户收货地址
     *
     * http://api.chtx.com/order/address
     * 必须登陆状态才可以查询
     */
    public function address()
    {
        $addr = Db::name('store_member_address')->where([
            'mid' => $this->wx_user_id, 'is_default' => '1'
        ])->find();

        if (!$addr) {
            $addr = Db::name('store_member_address')->where([
                'mid' => $this->wx_user_id
            ])->find();
            if ($addr) {
                Db::name('store_member_address')->where('id', $addr['id'])->update(['default' => 1]);
            } else {
                $this->success('暂无收货地址');
            }
        }
        //$addr['province_id'] = Db::name('sys_address')->find($addr['province_id'])['province_name'];
		$addr['province'] = Db::name('sys_address')->find($addr['province'])['area_name'];
        $addr['city'] = Db::name('sys_address')->find($addr['city'])['area_name'];
        $addr['area'] = Db::name('sys_address')->find($addr['area'])['area_name'];
        $this->success('',$addr);
    }
    
    
    /**
     * 收货地址列表
     * */

    public function delivery()
    {
        $num = request()->param("pageSize", 10);
        $page = request()->param("pageNo", 1);
        $start = ($page - 1) * $num;
        $list = Db::name('store_member_address')->where([
            'mid' => $this->wx_user_id
        ])->order('is_default desc,id desc')->limit($start, $num)->select();
       $this->success('',$list);
    }
    
    public function getProvince()
    {
        $list = (Db::name('sys_address')->where('area_parent_id',0)->select());//省
        $this->success('',$list);
    }

    public function getCity()
    {
        $province_id = input('param.province_id');
        $list = (Db::name('sys_address')->where(['area_parent_id' => $province_id])->select());//市
        $this->success('',$list);
    }

    public function getDistrict()
    {
        $city_id = input('param.city_id');
        $list = (Db::name('sys_address')->where(['area_parent_id' => $city_id])->select());//区
        $this->success('',$list);
    }
    
    /**
     * 添加收货地址
     */
    public function adddelivery()
    {
        $post = request()->post();
        if (empty($post['name'])) {
            $this->error('收货人姓名不能为空');
        }

        if (empty($post['phone'])) {
            $this->error('收货人手机号不能为空');
        }

        list($provice_id, $city_id, $district_id) = explode('|', $post['provinces']); //地址

        if (empty($post['addr'])) {
            $this->error('收货人详细地址不能为空');
        }

        $where = [];
        $where['mid'] = $this->wx_user_id;
        $where['username'] = $post['name'];
        $where['phone'] = $post['phone'];
        $where['province'] = $provice_id;
        $where['city'] = $city_id;
        $where['area'] = $district_id;
        $where['address'] = $post['addr'];
//            $where['default'] = $post['default'];

        $db = Db::name('store_member_address')->insertGetId($where);
        $ww = Db::name('store_member_address')->where(['id' => $db])->find();
        if ($ww['is_default'] == 1) {
            Db::name('store_member_address')->where(['mid' => $this->wx_user_id])->where('id', 'neq', $db)->update(['is_default' => 0]);
        }
        if ($db) {
            $this->success('新增收货地址成功');
        } else {
            $this->error('新增收货地址失败');
        }
    }
    
    public function moren() //默认地址
    {
        $id = request()->param('id');

        $db = Db::name('store_member_address')->where(['id' => $id])->update(['is_default' => 1]);
        
        if (Db::name('store_member_address')->where(['mid' => $this->wx_user_id])->where('id', 'neq', $id)->update(['is_default' => 0])) {
            $this->success('设为默认地址成功');
        } else {
            $this->error('设为默认地址失败');
        }
    }
    
    public function deldelivery()
    {
        $id = input('param.id');
        if (!$id) {
            $this->error('收货地址不存在');
        }
        $count = Db::name('store_member_address')->where(['mid' => $this->wx_user_id])->count();
        if ($count <= 1) {
            $this->error('不能删除最后一个收货地址');
        }
        $db = Db::name('store_member_address')->where(['id' => $id])->delete();
        if ($db) {
            $this->success('删除收获地址成功');
        } else {
            $this->error('删除地址失败');
        }
    }
    
    public function addorder()
    {

        $addr_id = input('post.addr_id');
        $goods_id = input('post.goods_id');
        $num = input('post.num');
        $info = input('post.info');
        
        //选填参数
        if (!$addr_id || !$address = Db::name('store_member_address')->find($addr_id)) {
             $this->success('地址信息不存在');
        }
        if (!$goods_id || !$goods = Db::name('store_goods')->find($goods_id)) {
             $this->success('商品信息不存在');
        }

        $order = time() . mt_rand(10000, 99999);

        $user = Db::name('store_member')->find($this->wx_user_id);
        //$selling_price = round($goods['market_price']*$num*sysconf('pay_money')/100,2);
        //$market_price = $goods['market_price']*$num-$selling_price;
        $selling_price = round($goods['bili_money']*$num,2);
        $market_price = ($goods['market_price']*$num-$selling_price)/sysconf('pdr_money');
        
        if($num>($goods['package_stock']-$goods['package_sale'])){
        	 $this->success('库存不足');
        }

        if($market_price > $user['account_money'] || $selling_price > $user['pay_money'])
        {
             $this->success('可用余额不足');
        }
        Db::startTrans();

            $goods_arr = [
                'mid' => $this->wx_user_id,
                'order_no' => $order,
                'goods_id' => $goods_id,
                'goods_title' =>$goods['goods_title'],
                'goods_logo' =>$goods['goods_logo'],
                'goods_image' =>$goods['goods_image'],
                'selling_price' => $selling_price,
                'market_price' => $market_price,	
                'number' => $num,
                'address_id' => $addr_id,
            ];
           
            $res[] = Db::name('store_goods')->where('id', $goods_id)->setInc('package_sale', $num);
            //$res[] = Db::name('store_goods')->where('id', $goods_id)->setDec('account_money', $num);
            $res[] = $lastid=  Db::name('store_order_goods')->insertGetId($goods_arr);
            $res[] = mlog($this->wx_user_id, 'account_money', -$market_price, '订单消费,减少opf'.$market_price, 'pay_opf',$lastid);
             $res[] = mlog($this->wx_user_id, 'pay_money', -$selling_price, '现金积分订单消费', '',$lastid);
            $arr = [
	            'type' => 1,
	            'mid' => $this->wx_user_id,
	            'order_no' => $this->wx_user_id . rand(100000, 999999) . time(),
	            'real_price' => $market_price,
	        ];
	        $res[] = db::name('StoreOrder')->insertGetId($arr);
	        $res[] = Db::name('store_member')->where(['id' => $this->wx_user_id])->setInc('hashrate', $market_price);

       //$res[] =Db::name('SystemConfig')->where(['name'=>'ys_buynum'])->setInc('value', $goods['selling_price'] * $num);
     // dump($res);
        if (check_arr($res)) {
            Db::commit();
             $this->success('提交订单成功');
            //success('提交订单成功', ['order_number' => $order, 'pfMoney' => $pfMoney]);
        } else {
            Db::rollback();
            $this->success('提交订单失败');
            //error('提交订单失败');
        }
    }
    
    public function orderType()//
    {
        $page = request()->param("pageNo", 1);
        $pageSize = input('param.pageSize', 10);
        $start = ($page - 1) * $pageSize;
        //$status = input('param.status');
        
        $type = input('post.status');
      //  $this->sevenShou();
		$wehre = [];
		if($type){
			$wehre['status'] = $type;	
		}else{
			$wehre['status'] = 1;	
		}

        $orderList = Db::name('store_order_goods')->where($wehre)->where(['mid' => $this->wx_user_id])->order('id desc')->limit($start, $pageSize)->select();

        !$orderList && $this->success('暂无订单');
        foreach ($orderList as $k => $v) {
            $orderList[$k]['goodslist'] = Db::name('store_goods')->field('id,goods_title,goods_logo,selling_price,market_price')->find($v['goods_id']);
            $orderList[$k]['danjia'] = ($v['market_price']+$v['selling_price'])/$v['number'];
        }
        $this->success('',$orderList);
    }
    
    public function paylist()//
    {
        $pageNo = request()->param("page", 1);
        $pageSize = input('param.pageSize', 10);
        $start = ($pageNo - 1) * $pageSize;
   
        $orderList = Db::name('store_pay_list')->where(['uid' => $this->wx_user_id])->order('id desc')->limit($start, $pageSize)->select();

        !$orderList && $this->success('暂无记录');
        $this->success('',$orderList);
    }
    
    
    //收益记录
    public function getTransRecordad()
    {
        $data = Db::name('bags_log')->where(['uid' => $this->wx_user_id])->where('type','pay_money')->page($this->page, $this->max_page_num)->order('create_time desc')->select();

        $this->success('',$data);
    }
    
    //确认收货
    public function affirmShou()
    {
        $orderId = input('param.id');
        $orderList = Db::name('store_order_goods')->where('mid', $this->wx_user_id)->where('id', $orderId)->where(['is_deleted' => 0])->find();
        !$orderList && error('暂无订单');

        if ($orderList['status'] == 2) {
            Db::startTrans();
            $res[] = Db::name('store_order_goods')->where('mid',  $this->wx_user_id)->where('id', $orderId)->where(['is_deleted' => 0])->update(['status' => 3]);
            if ($res) {
                Db::commit();
                $this->success('确认收货成功');
            } else {
                Db::rollback();
                $this->error('操作异常');
            }
        } else {
            $this->error('您的订单状态异常');
        }
    }
    
    //确认收货
    public function addressad()
    {
        $orderId = input('param.id');
        $orderList = Db::name('store_member_address')->where('mid', $this->wx_user_id)->where('id', $orderId)->find();
       if($orderList){
       	$this->success('',$orderList);
       }else{
       	$this->success('暂无地址');
       } 
    }

    //七天自动收货
    public function sevenShou()
    {

        $where_ = [];
        $where_['mid'] = $this->uid;
        $where_['is_deleted'] = 0;
        $where_['status'] = 3;
        $orderList = Db::name('store_order')->where($where_)->select();

        $sevenDay = sysconf('timeTake') * 86400;
//            $countDown = strtotime($orderList['express_time']) + $sevenDay - time(); //7天时间;
        foreach ($orderList as $k => $v) {
            if (time() > strtotime($v['express_time']) + $sevenDay) {
                Db::startTrans();
                $res = Db::name('store_order')->where(['id' => $v['id']])->update(['status' => 4]);
                if ($res) {
                    Db::commit();
                }
                Db::rollback();
            }
        }

    }

    /*
     *
     * */

    public function paylist1()//
    {
        $pageNo = request()->param("page", 1);
        $pageSize = input('param.pageSize', 10);
        $start = ($pageNo - 1) * $pageSize;

        $orderList = Db::name('store_pay_list')->where(['uid' => $this->wx_user_id])->order('id desc')->limit($start, $pageSize)->select();

        !$orderList && $this->success('暂无记录');
        $this->success('',$orderList);
    }



   
}