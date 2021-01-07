<?php

// +----------------------------------------------------------------------
// | Think.Admin
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/Think.Admin
// +----------------------------------------------------------------------

namespace app\store\controller;

use app\store\service\OrderService;
use controller\BasicAdmin;
use service\DataService;
use think\Db;

/**
 * 商店订单管理
 * Class Order
 * @package app\store\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/03/27 14:43
 */
class Order extends BasicAdmin
{

    /**
     * 定义当前操作表名
     * @var string
     */
    public $table = 'StoreOrder';

    /**
     * 订单列表
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
       $this->title = '订单管理';
        $db = Db::name('store_order_goods');
		$get = $this->request->get();
        // 会员信息查询过滤
        $memberWhere = [];
        foreach (['phone', 'nickname'] as $field) {
            if (isset($get[$field]) && $get[$field] !== '') {
                $memberWhere[] = [$field, 'like', "%{$get[$field]}%"];
            }
        }
        if (!empty($memberWhere)) {
           
            $sql = Db::name('store_member')->field('id')->where($memberWhere)->buildSql(true);
            $db->where("mid in {$sql}");
        }
        // =============== 商品信息查询过滤 ===============
        $goodsWhere = [];
        foreach (['goods_title'] as $field) {
            if (isset($get[$field]) && $get[$field] !== '') {
                $goodsWhere[] = [$field, 'like', "%{$get[$field]}%"];
            }
        }
        if (!empty($goodsWhere)) {
            $sql = Db::name('store_order_goods')->field('order_no')->where($goodsWhere)->buildSql(true);
            $db->where("order_no in {$sql}");
        }
       
        // =============== 主订单过滤 ===============
        foreach (['order_no', 'desc'] as $field) {
            (isset($get[$field]) && $get[$field] !== '') && $db->whereLike($field, "%{$get[$field]}%");
        }
        (isset($get['send_status']) && $get['send_status'] !== '') && $db->where('status', $get['send_status']);
        // 订单是否包邮状态检索
        if (isset($get['express_zero']) && $get['express_zero'] !== '') {
            empty($get['express_zero']) ? $db->where('freight_price', '>', '0') : $db->where('freight_price', '0');
        }
        // 订单时间过滤
        foreach (['create_at', 'pay_at'] as $field) {
            if (isset($get[$field]) && $get[$field] !== '') {
                list($start, $end) = explode(' - ', $get[$field]);
                $db->whereBetween($field, ["{$start} 00:00:00", "{$end} 23:59:59"]);
            }
        }
        return parent::_list($db->order('id desc'));
    }

    /**
     * 订单列表数据处理
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _data_filter(&$data)
    {
        OrderService::buildOrderList($data);
    }

    /**
     * 订单地址修改
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function address()
    {
        $order_no = $this->request->get('order_no');
        if ($this->request->isGet()) {
            $order = Db::name('StoreOrder')->where(['order_no' => $order_no])->find();
            empty($order) && $this->error('该订单无法进行地址修改，订单数据不存在！');
            $orderExpress = Db::name('StoreOrderExpress')->where(['order_no' => $order_no])->find();
            empty($orderExpress) && $this->error('该订单无法进行地址修改！');
            return $this->fetch('', $orderExpress);
        }
        $data = [
            'order_no' => $order_no,
            'username' => $this->request->post('express_username'),
            'send_no' => $this->request->post('send_no'),
            'phone'    => $this->request->post('express_phone'),
            'province' => $this->request->post('form_express_province'),
            'city'     => $this->request->post('form_express_city'),
            'area'     => $this->request->post('form_express_area'),
            'address'  => $this->request->post('express_address'),
            'desc'     => $this->request->post('express_desc'),
        ];
        if (DataService::save('StoreOrderExpress', $data, 'order_no')) {
            $this->success('收货地址修改成功！', '');
        }
        $this->error('收货地址修改失败，请稍候再试！');
    }
	public function forbid()
    {
       $id = $this->request->post('id');
       $orderExpress = Db::name('store_order_goods')->where(['id' => $id])->update(['status'=>2]);
       if($orderExpress){
       	 $this->success('发货成功！', '');
       }else {
       	$this->error('发货失败');
       }
    }

}
