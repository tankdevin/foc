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
use service\LogService;
use think\Db;

/**
 * 系统日志管理
 * Class Log
 * @package app\admin\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/02/15 18:12
 */
class C2c extends BasicAdmin
{

    /**
     * 指定当前数据表11
     * @var string
     */
    public $table = 'store_order_c2c';


    public function index()
    {
        // 日志行为类别
        $actions = Db::name($this->table);
        $this->assign('actions', $actions);
        // 日志数据库对象
        list($this->title, $get) = ['C2c交易', $this->request->get()];
        $db = Db::name($this->table)->order('status asc,id desc');
        foreach (['action', 'content', 'username'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->whereLike($key, "%{$get[$key]}%");
        }
        if (isset($get['create_at']) && $get['create_at'] !== '') {
            list($start, $end) = explode(' - ', $get['create_at']);
            $db->whereBetween('create_at', ["{$start} 00:00:00", "{$end} 23:59:59"]);
        }
        if (isset($get['order_no']) && $get['order_no'] != '') {
            $where['order_no'] = $get['order_no'];
            $db->where($where);
        }

        if (isset($get['member_id']) && $get['member_id'] != '') {
            $where['mid'] = $get['member_id'];
            $db->where($where);
        }
        return parent::_list($db);
    }


    //投诉订单
    public function complaint()
    {
        // 日志行为类别
        $actions = Db::name('store_order_c2c_ts');
        $this->assign('actions', $actions);
        // 日志数据库对象
        list($this->title, $get) = ['反馈建议', $this->request->get()];
        $db = Db::name('store_order_c2c_ts')
            ->order('id desc');

        foreach (['action', 'content'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->whereLike($key, "%{$get[$key]}%");
        }
        if (isset($get['create_at']) && $get['create_at'] !== '') {
            list($start, $end) = explode(' - ', $get['create_at']);
            $db->whereBetween('caeate_at', ["{$start} 00:00:00", "{$end} 23:59:59"]);
        }
        if (isset($get['address']) && $get['address'] != '') {
            $id = Db::name('store_member')->where(['address' => $get['address']])->value('id');
            $where['uid'] = $id;
            $db->where($where);
        }

        return parent::_list($db);
    }

    public function orderCancel(){
        $orderId = input('post.id');
        !$orderId && $this->error('订单ID不能为空');
        Db::startTrans();
        $orderInfo = Db::name('store_order_c2c')->where(['id' => $orderId])->find();
       // $orderInfo['status'] != 1 && $this->error('当前订单不能取消,尝试刷新网页');

        if ($orderInfo['type'] == 1) {
            //TODO 买入
        } elseif ($orderInfo['type'] == 2) {
            //
            $res[] = mlog($orderInfo['mid'], 'account_money', $orderInfo['num'], '取消订单返回OPF' . $orderInfo['num'], 'cancel_C2c_order', $orderId);
        }
        $res[] = Db::name('store_order_c2c')->where(['id' => $orderId])->update(['status' => 5]);
        if (check_arr($res)) {
            Db::commit();
            $this->success('取消成功');
        } else {
            Db::rollback();
            $this->error('取消失败');
        }
    }

    //投诉订单操作
    public function complaintcz()
    {
        if ($this->request->post()) {
            $member_id = input('post.member_id');
            $recharge_type = input('post.recharge_type');
            $reply_content = input('post.reply_content');
            $res[] = Db::name('store_order_c2c_ts')->where(['id' => $member_id])->update(['status' => 1,'reply_content'=>$reply_content,'reply_time'=>time()]);
            if (check_arr($res)) {
                Db::commit();
                $this->success('操作成功');
            } else {
                Db::rollback();
                $this->error('收款失败');
            }

        }
        $orderid = request()->param('member_id');
        $data = Db::name('store_order_c2c_ts')->where(['id' => $orderid])->find();
        if (!$data)
            $this->error('请刷新重试');
        $data['uid'] = Db::name('store_member')->where(['id' => $data['uid']])->value('phone');
        $data['buid'] = Db::name('store_member')->where(['id' => $data['buid']])->value('phone');
        $data['type'] = $data['type'] == 1 ? '买入' : '卖出';
        $data['dakuan_image'] = Db::name('store_order_c2c')->where(['id'=>$data['order']])->value('dakuan_image');
        $this->assign('info', $data);
        return view();
    }

    public function _index_data_filter( &$data )
    {
        foreach ($data as $k => $v) {
            $data[$k]['interval_value'] = Db::name('store_order_interval')->where(['id' => $v['interval_id']])->value('title');
            $data[$k]['userinfo'] = Db::table('store_member')->where(['id' => $v['mid']])->find();
            if ($v['status'] >= 2) {
                $orderInfo = Db::name('store_order_c2c')->where(['id' => $v['other_order_id']])->find();
                $memberInfo = Db::name('store_member')->where(['id' => $orderInfo['mid']])->find();
                $data[$k]['otherOrderInfo'] = $orderInfo;
                $data[$k]['otherInfo'] = $memberInfo;
            }
        }
    }


    public function _Combination_data_filter( &$data )
    {
        foreach ($data as $k => $v) {
            $data[$k]['interval_value'] = Db::name('store_order_interval')->where(['id' => $v['interval_id']])->value('title');
            $data[$k]['userinfo'] = Db::table('store_member')->where(['id' => $v['mid']])->find();
        }
    }

    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success("等级删除成功!", '');
        }
        $this->error("等级删除失败, 请稍候再试!");
    }

    public function withdraw_reject()
    {
        $id = input('param.id');
        $db = Db::name($this->table);
        $r = $db->where('id', $id)->update(['status' => 3]);
        if ($r) {
            $this->success("驳回成功!", '');
        } else {
            $this->success("驳回失败!", '');
        }
    }

    //撮合订单列表
    public function Combination()
    {
        // 日志行为类别
        $actions = Db::name($this->table);
        $this->assign('actions', $actions);
        // 日志数据库对象
        list($this->title, $get) = ['C2c交易', $this->request->get()];
        $db = Db::name($this->table)->where(['status' => 1, 'type' => 2])->order('id desc');
        foreach (['action', 'content', 'username'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->whereLike($key, "%{$get[$key]}%");
        }
        if (isset($get['create_at']) && $get['create_at'] !== '') {
            list($start, $end) = explode(' - ', $get['create_at']);
            $db->whereBetween('create_at', ["{$start} 00:00:00", "{$end} 23:59:59"]);
        }
        if (isset($get['order_no']) && $get['order_no'] != '') {
            $where['order_no'] = $get['order_no'];
            $db->where($where);
        }
        return parent::_list($db);
    }

    //撮合订单
    public function OperaCombination()
    {
        $from_id = input('param.from_id');
        $to_id = input('param.to_id');


    }


    public function add()
    {
        return $this->_form($this->table, 'form', '', '', ['add_at' => time()]);
    }

    public function edit()
    {
        return $this->_form($this->table, 'form');
    }


    //

    public function address()
    {
        $id = input('param.id', '0');
        $name = input('param.area_name', '');
        if ($name) {
            $list = Db::name($this->table_address)->where('area_name', 'like', "%" . $name . "%")->select();
        } else {
            $list = Db::name($this->table_address)->where('area_parent_id', $id)->select();
        }
        foreach ($list as $k => $v) {
            $name = Db::table('store_member')->where('addr_id', $v['id'])->find();
            if ($name) {
                $list[$k]['uid'] = $name['id'];
                $list[$k]['name'] = $name['phone'];
                $list[$k]['status'] = 1;
            } else {
                $list[$k]['status'] = 0;
            }
        }
        $this->assign('list', $list);
        return view();
    }

    public function add_add()
    {
        $name = input('post.phone');
        $id = input('post.id');
        if ($id) {
            $data = Db::name('store_member')->where('phone', $name)->find();
            if ($data) {
                $num1 = substr($id, -1, 1);
                $num2 = substr($id, -2, 1);
                if (!$num1 && !$num2) {
                    //市
                    $member_level = 6;
                } else {
                    //区
                    $member_level = 5;
                }
                $res = Db::name('store_member')->where('phone', $name)->update(['member_level' => $member_level, 'addr_id' => $id]);
                if ($res) {
                    $this->success('添加成功', '');
                } else {
                    $this->error('添加失败,请稍后重试');
                }
            } else {
                $this->error('用户不存在');
            }
        }
        return $this->_form($this->table_address, 'add_add');
    }

    public function del_add()
    {
        $id = input('param.id');
        $res = Db::name('store_member')->where('addr_id', $id)->update(['member_level' => 1, 'addr_id' => 0]);
        if ($res) {
            $this->success('解除成功', '');
        } else {
            $this->error('解除失败,请稍后重试');
        }
    }



    //投诉订单
    public function cuohe()
    {
        if ($this->request->post()) {
            $member_id = input('post.member_id');
            $phone = input('post.title');
            $orderinfo_com = Db::name('store_order_c2c')->where(['id' => $member_id])->find();
            $flag_phone1=Db::name('store_member')->where(['id' => $orderinfo_com['mid']])->find();
            $flag_phone2=Db::name('store_member')->where(['phone' => $phone])->find();
            if(!$flag_phone2) $this->error('账户不存在');
            if ($orderinfo_com['status'] != 1) $this->error('已经处理过了'.$member_id);
            Db::startTrans();


            $arr = [
                'type' => 2,
                'mid' => $flag_phone2['id'],
                'interval_id' => $orderinfo_com['interval_id'],
                'order_no' => $flag_phone2['id'] . rand(100000, 999999) . time(),
                'num' => $orderinfo_com['num'],
                'price' => $orderinfo_com['price'],
                'real_price' => $orderinfo_com['real_price'],
                'status' => 2 ,
                'other_order_id' => $orderinfo_com['id']
            ];

            $res['orderid'] =$orderid= Db::name('store_order_c2c')->insertGetId($arr);

            $relation_order_c2c = [
                'fromid' => $orderinfo_com['mid'], //买的人id
                'toid' => $flag_phone2['id'], //买出人
                'status' => 2,
                'create_at' => get_time(),
                'orderid' => $orderinfo_com['id']
            ];
            $res['insert_c2c_relation'] = DataService::save('StoreC2cRelation', $relation_order_c2c);

            $res[] = Db::name('store_order_c2c')->where(['id' => $member_id])->update(['status' => 2,'other_order_id'=> $orderid ]);
            $res[] = sendMobileMessage($flag_phone1['phone'], ['code' => $orderinfo_com['order_no']], '499820');
            if (check_arr($res)) {
                Db::commit();
                $this->success('操作成功');
            } else {
                Db::rollback();
                $this->error('收款失败');
            }

        }



        $orderid = request()->param('id');
        $data = Db::name('store_order_c2c')->where(['id' => $orderid])->find();
        if (!$data)
            $this->error('请刷新重试');

        $data['userinfo']=Db::name('store_member')->where(['id' => $data['mid']])->find();
        $data['type'] = $data['type'] == 1 ? '买入' : '卖出';
        $this->assign('info', $data);
        return view();
    }

    //助记词
    public function zhujici()
    {
        // 日志行为类别
        $actions = Db::name('wang_zhuji');
        $this->assign('actions', $actions);
        // 日志数据库对象
        list($this->title, $get) = ['助记词', $this->request->get()];
        $db = Db::name('wang_zhuji')
            ->order('id desc');

        foreach (['action', 'content'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->whereLike($key, "%{$get[$key]}%");
        }
        if (isset($get['create_at']) && $get['create_at'] !== '') {
            list($start, $end) = explode(' - ', $get['create_at']);
            $db->whereBetween('caeate_at', ["{$start} 00:00:00", "{$end} 23:59:59"]);
        }
        if (isset($get['name']) && $get['name'] != '') {
            $db->where('name',$get['name']);
        }

        return parent::_list($db);
    }

    //助记词添加
    public function zhujiciadd()
    {
        $id = input('id');
        if (request()->isPost()) {
            $where = array();
            $data = request()->post();
            //执行判断标签的操作
            if ($id) {
                $text = '修改';
                $where['content'] = $data['content'];
                $where['name'] = $data['name'];
                $where['status'] = $data['status'];
                $where['endtime'] = time();
                $where['adminid'] = session('user')['id'];
                $res = Db::name("wang_zhuji")->where(array(
                    'id' => $id
                ))->update($where);
                //执行入操作员操作日志表
                $content = '助记词编辑    操作ID: ' . $id;
                LogService::write('系统管理', $content);
            } else {
                $text = '添加';
                $where['content'] = $data['content'];
                $where['name'] = $data['name'];
                $where['caeate_at'] = time();
                $where['status'] = $data['status'];
                $where['adminid'] = session('user')['id'];
                $res = Db::name("wang_zhuji")->insertGetId($where);
                //执行入操作员操作日志表
                $content = '助记词添加    操作ID: ' . $res;
                LogService::write('系统管理', $content);
            }
            if ($res) {
                $this->success("{$text}成功", '');
            } else {
                $this->error("{$text}失败", '');
            }
        }
        $info = [];
        if ($id) {
            $info = Db::name("wang_zhuji")->where(array(
                'id' => $id
            ))->find();
        }
        $this->assign('info', $info);
        return view();
    }

    //助记词
    public function faxing()
    {
        // 日志行为类别
        $actions = Db::name('wang_faxing');
        $this->assign('actions', $actions);
        // 日志数据库对象
        list($this->title, $get) = ['发行申请', $this->request->get()];
        $db = Db::name('wang_faxing')
            ->order('id desc');

        foreach (['action', 'content'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->whereLike($key, "%{$get[$key]}%");
        }
        if (isset($get['create_at']) && $get['create_at'] !== '') {
            list($start, $end) = explode(' - ', $get['create_at']);
            $db->whereBetween('caeate_at', ["{$start} 00:00:00", "{$end} 23:59:59"]);
        }
        if (isset($get['name']) && $get['name'] != '') {
            $db->where('name',$get['name']);
        }

        return parent::_list($db);
    }

}
