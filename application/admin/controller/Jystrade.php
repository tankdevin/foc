<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

namespace app\admin\controller;

use controller\BasicAdmin;
use service\DataService;
use think\Db;

/**
 * 系统日志管理
 * Class Log
 * @package app\admin\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/02/15 18:12
 */
class Jystrade extends BasicAdmin
{

    /**
     * 指定当前数据表11
     * @var string
     */
    public $table1 = 'jys_buylist';
    public $table2 = 'jys_selllist';
    public function buylist()
    {
        // 日志行为类别
        $actions = Db::name($this->table1);
        $this->assign('actions', $actions);
        // 日志数据库对象
        list($this->title, $get) = ['交易所买单', $this->request->get()];
        $db = Db::name($this->table1)->order('state asc,id desc');
        foreach (['action', 'content', 'username'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->whereLike($key, "%{$get[$key]}%");
        }
        if (isset($get['addtime']) && $get['addtime'] !== '') {
            list($start, $end) = explode(' - ', $get['addtime']);
            $db->whereBetween('create_at', ["{$start} 00:00:00", "{$end} 23:59:59"]);
        }
        if (isset($get['ordersn']) && $get['ordersn'] != '') {
            $where['ordersn'] = $get['ordersn'];
            $db->where($where);
        }
        if (isset($get['address']) && $get['address'] != '') {
            $where['uid'] = Db::name('store_member')->where('address',$get['address'])->value('id');
            $db->where($where);
        }
        if (isset($get['uid']) && $get['uid'] != '') {
            $where['uid'] = $get['uid'];
            $db->where($where);
        }
        return parent::_list($db);
    }

    /*
     * 卖单
     * */
    public function selllist()
    {
        // 日志行为类别
        $actions = Db::name($this->table2);
        $this->assign('actions', $actions);
        // 日志数据库对象
        list($this->title, $get) = ['交易所卖单', $this->request->get()];
        $db = Db::name($this->table2)->order('state asc,id desc');
        foreach (['action', 'content', 'username'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->whereLike($key, "%{$get[$key]}%");
        }
        if (isset($get['addtime']) && $get['addtime'] !== '') {
            list($start, $end) = explode(' - ', $get['addtime']);
            $db->whereBetween('create_at', ["{$start} 00:00:00", "{$end} 23:59:59"]);
        }
        if (isset($get['ordersn']) && $get['ordersn'] != '') {
            $where['ordersn'] = $get['ordersn'];
            $db->where($where);
        }
        if (isset($get['address']) && $get['address'] != '') {
            $where['uid'] = Db::name('store_member')->where('address',$get['address'])->value('id');
            $db->where($where);
        }
        if (isset($get['uid']) && $get['uid'] != '') {
            $where['uid'] = $get['uid'];
            $db->where($where);
        }
        return parent::_list($db);
    }
    
    /**
     * 撤单买单
     */
    public function delbuy()
    {
        $orderId = input('param.id');
        //买单
        $orderInfo = Db::name('jys_buylist')->where(['id' => $orderId,'state'=>0])->find();
        if(!$orderInfo){
            $this->error('订单不存在或不能撤销');
        }
        Db::startTrans();
        //撤回的是usdt数量
        $rate_usdt = $orderInfo['leavenum']* $orderInfo['price']*sysconf('jys_rate')*0.01;
        $usdt =  $orderInfo['leavenum']* $orderInfo['price'];
        $res_id2 = $res[] = mlog($orderInfo['uid'], 'account_money', $usdt, '取消买单'.$orderInfo['ordersn'].'实际退回usdt'.$usdt, 'cancel_C2c_order', $orderId,'13');
        //bagslanguage($res_id2['1'],$orderInfo['ordersn'],$rate_usdt,$usdt,'',31,29,32);
        $res[] = Db::name('jys_buylist')->where(['id' => $orderId])->update(['state' => 2,'endtime'=>time()]);
        if (check_arr($res)) {
            Db::commit();
            $this->success('撤单成功');
        } else {
            Db::rollback();
            $this->error('撤单失败');
        }
    }
    
    /**
     * 撤单卖单
     */
    public function delsell()
    {
        $orderId = input('param.id');
        //卖单
        $orderInfo = Db::name('jys_selllist')->where(['id' => $orderId,'state'=>0])->find();
        if(!$orderInfo){
            $this->error('订单不存在或不能撤销');
        }
        Db::startTrans();
        //撤销返回的是nac
        $rate_nac = $orderInfo['leavenum']*sysconf('jys_rate')*0.01;
        $nac = $orderInfo['leavenum']+$rate_nac;
        if($orderInfo['name'] == 'FOC'){
            $moneyname = 'account_foc';
            $res_id1 = $res[] = mlog($orderInfo['uid'], $moneyname,$nac, '取消卖单'.$orderInfo['ordersn'].'手续费' . $rate_nac."实际退回".$nac, 'cancel_C2c_order', $orderId,'13');
        }else{
            $res[] = Db::name('store_member')->where(['id' => $orderInfo['uid']])->setInc('gz_foc',$orderInfo['leavenum']/2);
            $moneyname = 'account_score';
            $foc_tui = ($orderInfo['leavenum']/2)+$rate_nac;
            $res_id1 = $res[] = mlog($orderInfo['uid'], 'account_foc',$foc_tui, '取消卖单'.$orderInfo['ordersn'].'手续费' . $rate_nac."实际退回".$foc_tui, 'cancel_C2c_order', $orderId,'13');
            $res_id1 = $res[] = mlog($orderInfo['uid'], $moneyname,$orderInfo['leavenum']/2, '取消卖单'.$orderInfo['ordersn']."实际退回".($orderInfo['leavenum']/2), 'cancel_C2c_order', $orderId,'13');
        }
        //bagslanguage($res_id1['1'],$orderInfo['ordersn'],$rate_nac,$nac,'',28,29,30);
        $res[] = Db::name('jys_selllist')->where(['id' => $orderId,'state'=>0])->update(['state' => 2,'endtime'=>time()]);
        if (check_arr($res)) {
            Db::commit();
            $this->success('撤单成功');
        } else {
            Db::rollback();
            $this->error('撤单失败');
        }
    }
}
