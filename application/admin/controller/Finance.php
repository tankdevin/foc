<?php

namespace app\admin\controller;

use controller\BasicAdmin;
use service\LogService;
use think\App;
use think\Db;
use think\facade\Cache;

/**
 * 后台参数配置控制器
 * Class Config
 * @package app\admin\controller
 */
class Finance extends BasicAdmin
{
    public function __construct( App $app = null )
    {
        parent::__construct($app);
        //进行匹配

    }

    public function exception()
    {
        $u = Db::name('user')->find(1);
        $user = Db::name("user")->select();

        foreach ($user as $k => $v) {
            foreach (coin() as $key => $val) {
                if ($key != 'cpf_calc') {
                    $res[$v['username']][$key] = [
                        'current' => round($v[$key], 4),
                        'calc' => round(Db::name('log')->where([
                            'uid' => $v['id'],
                            'type' => $key,
                        ])->sum('money'), 4),
                    ];
                }
            }
        }

        return view('', [
            'list' => $res,
        ]);
    }

    //执行金币赠送记录汇总的操作
    public function gives()
    {
        $this->title = '余额赠送';
        $get = $this->request->get();
        $db = Db::name("Log");
        $where = array();
        $where['status'] = 5;
        if (isset($get['type']) && $get['type'] != '') {
            $where['type'] = $get['type'];
            $db->where($where);
        }
        if (isset($get['field']) && $get['field'] != '') {
            if ($get['field'] == 'username') {
                if (!empty($get['name'])) {
                    $where['uid'] = Db::name('User')->where(array('username' => $get['name']))->value('id');
                }

            } else {
                $where[$get['field']] = $get['name'];
            }
            $db->where($where);
        }
        $db->where($where);
        $db->order('create_time desc,id desc');
        return parent::_list($db);
    }

    //执行金币赠送的操作的效果
    public function givedit()
    {
        if (request()->post()) {
            if (!session('user')) {
                $this->error('无效的后台管理用户');
            }

            if (session('user')['status'] != 1) {
                $this->error('该管理用户已冻结，不能进行操作');
            }

            $post = request()->post();
            $user = Db::name('User')->where(array('username' => $post['username']))->find();
            if (!$user) {
                $this->error('该会员账户不存在');
            }

            if ($user['status'] != 1 && $user['status'] != 3) {
                $this->error('该用户已锁定冻结');
            }

            if ($user[$post['coin']] < 0) {
                $this->error('无效的账户财产');
            }

            if (empty($post['num'])) {
                $this->error('请输入充值金额');
            }

            if (!check($post['num'], 'double')) {
                $this->error('充值金额格式错误');
            }

            if ($post['type'] == 'mykk') {
                if (round($post['num'], 8) > $user[$post['coin']]) {
                    $this->error('用户财产余额不足');
                }

                Db::startTrans();

                $res = array();
                $map = array();
                $where = array();
                $res[] = Db::name('User')->where(array('id' => $user['id']))->SetDec($post['coin'], $post['num']);
                $zuser = Db::name('User')->where(array('id' => $user['id']))->find();
                $map['uid'] = $user['id'];
                $map['type'] = $post['coin'];
                $map['status'] = 5;
                $map['money'] = '-' . $post['num'];
                $map['before'] = $user[$post['coin']] * 1;
                $map['content'] = '扣款:' . $post['num'];
                $map['create_time'] = time();
                $res[] = Db::name('Log')->insert($map);
                //执行入资金变动记录表
                $content = '余额赠送(扣款) 操作会员ID: ' . $user['id'];
                LogService::write('系统管理', $content);

                if (check_arr($res)) {
                    //提交事务
                    Db::commit();
                    $this->success('余额赠送成功(扣款)', '');
                } else {
                    //回滚事务
                    Db::rollback();
                    $this->error('金余额赠送失败(扣款)');
                }
            } else {
                //执行事务的操作
                Db::startTrans();
                $res = array();
                $map = array();
                $where = array();
                $res[] = Db::name('User')->where(array('id' => $user['id']))->SetInc($post['coin'], $post['num']);
                $zuser = Db::name('User')->where(array('id' => $user['id']))->find();
                $map['uid'] = $user['id'];
                $map['type'] = $post['coin'];
                $map['status'] = 5;
                $map['money'] = '+' . $post['num'];
                $map['before'] = $user[$post['coin']] * 1;
                $map['content'] = '充值:' . $post['num'];
                $map['create_time'] = time();
                $res[] = Db::name('Log')->insert($map);
                //执行入资金变动记录表
                $content = '余额赠送(充值)    操作会员ID: ' . $user['id'];
                LogService::write('系统管理', $content);

                if (check_arr($res)) {
                    //提交事务
                    Db::commit();
                    $this->success('余额赠送成功(充值)', '');
                } else {
                    //回滚事务
                    Db::rollback();
                    $this->error('余额赠送失败(充值)');
                }
            }
        }
        return view();
    }

    public function demo()
    {
        echo "<br><br><br><br>,<h3>不显示用户名的属于已经删除用户</h3><br><p style='padding:20px'>";
        $demodb = Db::name("Log")->where('money<0')->select();
        foreach ($demodb as $var) {


            if ($var['before'] + $var['money'] < 0) {
                $user = Db::name('User')->where(array('id' => $var['uid']))->find();
                echo $var['id'] . "-----" . $user['username'] . "---" . $var['uid'] . "<br>";
            }
        }
        echo "</p>";
    }
    /*
    导出
    */
         public function sqlExcel()
    {
        if(Cache::get('finance_index')){
            $map = [];
            $data = Cache::get('finance_index');
            $map['uid'] = $data['uid'];
            if(isset($data['type'])){
                 $map['type'] = $data['type'];
            }
            if(isset($data['beg']) && isset($data['end'])){
                 $map['create_time'] = ['between',[$data['beg'],$data['end']]];
            }
           
           
           
            $list=Db::name('bags_log')->field('id,uid,type,money,before,content,create_time')->where($map)->select();
        }else{
            $list=Db::name('bags_log')->field('id,uid,type,money,before,content,create_time')->select();
        }
        
        $data=array();
        $filename='会员列表';
        foreach ($list as $key => $value) {
          $data[$key]['id']=$value['id'].' ';
          //账号
          $data[$key]['phone']=Db::name('store_member')->where('id',$value['uid'])->value('phone').' ';
          //类型
          if($value['type'] == 'account_money'){
              $data[$key]['type']='usdt'.' ';
          }elseif($value['type'] == 'account_score'){
              $data[$key]['type']='lxc'.' ';
          }elseif($value['type'] == 'dj_usdt'){
              $data[$key]['type']='冻结usdt'.' ';
          }elseif($value['type'] == 'dj_lxc'){
              $data[$key]['type']='冻结lxc'.' ';
          }
          
          $data[$key]['money']=$value['money'].' ';
          $data[$key]['before']=$value['before'].' ';
          $data[$key]['content']=$value['content'].' ';
          //$data[$key]['is_sell']=$value['is_sell']?'是':'否';
          $data[$key]['create_time']=date('Y-m-d H:i:s',$value['create_time']);
       }
        $headArr[]="ID";
        $headArr[]="账号";
        $headArr[]="类型";
        $headArr[]="金额";
        $headArr[]="之前的金额";
        $headArr[]="备注";
        //$headArr[]="之前的金额";
        $headArr[]="添加时间";
       
        getExcel($filename,$headArr,$data);
       
    }
    //执行资金变动记录汇总的操作
    public function index()
    {
        $this->title = '资金明细';
        $get = $this->request->get();
        $db = Db::name("bags_log")->order('id desc');
        $where = array();
        $where1 = array();
        if (isset($get['address']) && $get['address'] != '') {
             $where['uid'] = Db::name('store_member')->where('address',$get['address'])->value('id');
            $db->where($where);
        }

        if (isset($get['member_id']) && $get['member_id'] != '') {
            $where['uid'] = $get['member_id'];
            $db->where($where);
        }
        if (isset($get['extends']) && $get['extends'] != '') {
            $where['type'] = $get['extends'];
            $db->where($where);

        }
        if (isset($get['status']) && $get['status'] != '') {
            $where['status'] = $get['status'];
            $db->where($where);
        }

       /* if (isset($get['extends']) && $get['extends'] != '') {
            if($get['extends'] != 'award'){
                $where['type'] = $get['extends'];
            }else{
                $where['extends'] = $get['extends'];
            }
            $where1['type'] = $get['extends'];
            $db->where($where);
        }*/
        if (isset($get['date']) && $get['date'] != '') {
            $date = $get['date'];
            list($start, $end) = explode(' - ', $date);
            $end = strtotime($end)+86400;
            //根据搜索进行导出
            $where1['beg'] = strtotime($start) ;
            $where1['end'] = $end ;
            $db->where('create_time', 'between', [strtotime($start),$end]);
        }
        /*if (isset($get['field']) && $get['field'] != '') {
            if ($get['field'] == 'email') {
                $get['name'] && $where['uid'] = Db::name('store_member')->where(array('email' => $get['name']))->value('id');
                 //根据搜索进行导出
                if(Db::name('store_member')->where(array('email' => $get['name']))->value('id')){
                    $where1['uid'] = $where['uid'];
                }
              

            } elseif($get['field'] == 'username') {
                $get['name'] && $where['uid'] = Db::name('store_member')->where(array('nickname' => $get['name']))->value('id');
            }else{
                $where[$get['field']] = $get['name'];
            }

            $db->where($where);
          
            
        }*/
       
        if($where1){
            
            Cache::set('finance_index',$where1,3600);
        }
        return parent::_list($db);
    }
    
    public function test(){
        dump(Cache::get('finance_index'));
        //Cache::rm('finance_index'); 
    }

    //执行资金变动记录汇总的操作
    public function yeji()
    {
        $this->title = '业绩结算查询';
        $get = $this->request->get();
        $db = Db::name("yejiLogs");
        $where = array();
        if (isset($get['date']) && $get['date'] != '') {
            $date = $get['date'];
            list($start, $end) = explode(' - ', $date);
            $db->where('create_time', 'between', [strtotime($start), strtotime($end)]);
        }
        if (isset($get['field']) && $get['field'] != '') {
            if ($get['field'] == 'username') {
                $get['name'] && $where['uid'] = Db::name('User')->where(array('username' => $get['name']))->value('id');
            } else {
                $where[$get['field']] = $get['name'];
            }
            $db->where($where);
        }
        $db->order('create_time desc,id desc');
        return parent::_list($db);
    }

    //执行资金变动记录汇总的操作
    public function nodeIncome()
    {
        $this->title = '节点收益结算明细';
        $get = $this->request->get();
        $db = Db::name("node_income_runtime_log");
        $where = array();
        $db->order('id desc');
        return parent::_list($db);
    }

    //执行资金变动记录汇总的操作
    public function usdt()
    {
        $this->title = '余额充值';
        $get = $this->request->get();
        $db = Db::name("recharge_usdt");
        $where = array();
        if (isset($get['field']) && $get['field'] != '') {
            if ($get['field'] == 'username') {
                if (!empty($get['name'])) {
                    $where['uid'] = Db::name('User')->where(array('username' => $get['name']))->value('id');
                }

            } else {
                $where[$get['field']] = $get['name'];
            }
            $db->where($where);
        }
        if (isset($get['date']) && $get['date'] != '') {
            $date = $get['date'];
            list($start, $end) = explode(' - ', $date);
            $db->where('create_time', 'between', [strtotime($start), strtotime($end)]);
        }
        $db->order('id desc');
        return parent::_list($db);
    }

    public function usdtedit()
    {
        $id = request()->get('id');
        if (request()->isPost()) {
            $post = request()->post();
            $zxcz = Db::name('recharge_usdt')->where(array('id' => $id))->find();
            $huser = Db::name('user')->where(array('id' => $zxcz['uid']))->find();
            if (!isset($post['typemm'])) {
                $this->error('请选择操作类型');
            }

            if (!$zxcz) {
                $this->error('无效的请求类型');
            }

            if ($zxcz['status'] == 1) {
                $this->error('该订单是充值完成订单,无需操作');
            }

            if ($zxcz['status'] == 2) {
                $this->error('该订单是拒绝订单,无需操作');
            }

            if (!$huser) {
                $this->error('平台中暂无该用户');
            }

            if (empty($huser['username'])) {
                $this->error('该用户名不存在');
            }

            if ($huser['status'] != 1) {
                $this->error('该用户已冻结,请先进行解封，在进行操作');
            }

            $where = array();
            if ($post['typemm'] == 1) {

                //执行事务的操作
                Db::startTrans();

                $where = array();
                $where['audit_time'] = time();
                $where['status'] = 1;
                $where['adminid'] = session('user')['id'];
                $res[] = Db::name('recharge_usdt')->where(array('id' => $zxcz['id']))->update($where);
                $res[] = mlog($zxcz['uid'], 'res_money', $zxcz['money'], '在线充值');

                //执行入操作员操作日志表
                $content = '在线充值(充值成功) 操作ID: ' . $id;
                LogService::write('系统管理', $content);
                if (check_arr($res)) {
                    //提交事务
                    Db::commit();
                    $this->success('充值成功', '');
                } else {
                    //事务回滚
                    Db::rollback();
                    $this->error('充值失败');
                }
            } elseif ($post['typemm'] == 2) {
                if (empty($post['content'])) {
                    $this->error('请输入拒绝原因');
                }

                //执行事务的操作
                Db::startTrans();
                $res = array();
                $where = array();
                $where['content'] = $post['content'];
                $where['audit_time'] = time();
                $where['status'] = 2;
                $where['adminid'] = session('user')['id'];
                $res[] = Db::name('recharge_usdt')->where(array('id' => $zxcz['id']))->update($where);
                //执行入操作员操作日志表
                $content = '在线充值(拒绝)    操作ID: ' . $id;
                LogService::write('系统管理', $content);
                if (check_arr($res)) {
                    //提交事务
                    Db::commit();
                    $this->success('拒绝成功', '');
                } else {
                    //事务回滚
                    Db::rollback();
                    $this->error('拒绝失败');
                }
            }
        }

        $info = Db::name('recharge_usdt')->where(array('id' => $id))->find();
        $this->assign('info', $info);
        return view();

    }

    public function usdtdetail()
    {
        $id = input('id');
        $info = Db::name('recharge_usdt')->where(array('id' => $id))->find();
        $this->assign('info', $info);
        return view();
    }

    //价格的操作
    public function pricels()
    {
        $this->title = '价格的操作';
        $get = $this->request->get();
        $db = Db::name("kline");
        $where = array();
        if (isset($get['time']) && $get['time'] != '') {
            $time = $get['time'];
            $time = explode(' - ', $time);
            //开始时间
            $starttime = strtotime($time[0]);
            //结束时间
            $endtime = strtotime($time[1]);
            $db->where('date', 'between', [$starttime, $endtime]);
        }
        $db->order('id desc');
        return parent::_list($db);
    }

    //添加价格
    public function edit()
    {
        $ltime = strtotime(date('Y-m-d'));
        if (request()->isPost()) {
            $post = request()->post();
            $price = $post['price'];
            $nowtime = time();
            $count = Db::name("kline")->where("date", "gt", $ltime)->count();
            if ($count < 1) {
                $res = Db::name("kline")->insert([
                    'price' => $price,
                    'date' => $nowtime,
                ]);
                if ($res) {
                    $this->success("添加成功", "/#/finance/pricels.html?spm=m-23-44");
                } else {
                    $this->error("添加失败");
                }
            } else {
                $this->error("今天已添加过了");
            }

        }
        return $this->fetch();
    }

    //添加价格
    public function editpri()
    {
        $id = request()->get('id');
        $info = Db::name("kline")->where("id", $id)->find();
        $ltime = strtotime(date('Y-m-d'));
        if (request()->isPost()) {
            $post = request()->post();
            $id = $post['id'];
            $price = $post['price'];
            $nowtime = time();
            $res = Db::name("kline")->where("id", $id)->update([
                'price' => $price,
            ]);
            if ($res) {
                $this->success("修改成功", "");
            } else {
                $this->error("修改失败");
            }

        }
        $this->assign("info", $info);
        return $this->fetch();
    }

    //执行用户状态值的操作
    public function deletepri()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $id = $data['id'];
            if (empty($id)) {
                $this->error('你好,请选择要操作的数据');
            } else {
                if (Db::name('kline')->where([
                    ['id', 'in', $id],
                ])->delete()) {
                    $this->success('操作成功', '');
                } else {
                    $this->error('操作失败');
                }
            }
        } else {
            $this->error('无效的请求方式');
        }
    }

    public function awardPool()
    {
        $this->title = '奖池管理';
        $get = $this->request->get();
        $db = Db::name("awardPool");
        $where = array();
        if (isset($get['date']) && $get['date'] != '') {
            $date = $get['date'];
            list($start, $end) = explode(' - ', $date);
            if ($get['time'] == 'end') {
                $db->where('endtime', 'between', [strtotime($start), strtotime($end)]);
            } else {
                $db->where('addtime', 'between', [strtotime($start), strtotime($end)]);
            }

        }
        $db->order('id desc');
        return parent::_list($db);
    }

    public function awardEdit()
    {
        $id = request()->get('id');
        if (request()->isPost()) {
            $num = request()->post('num');
            $res[] = Db::name('awardPool')->where(array('id' => $id))->update([
                'num' => $num
            ]);
            //执行入操作员操作日志表
            $content = '修改奖池成功为' . $num;
            LogService::write('系统管理', $content);
            if (check_arr($res)) {
                //提交事务
                Db::commit();
                $this->success('修改成功', '');
            } else {
                //事务回滚
                Db::rollback();
                $this->error('修改失败');
            }
        }
        $info = Db::name('awardPool')->where(array('id' => $id))->find();
        $this->assign('info', $info);
        return view();
    }

    public function bbcz()
    {
        $this->title = '余额提现';
        $get = $this->request->get();
        $db = Db::name("bbcz");
        $where = array();
        if (isset($get['field']) && $get['field'] != '') {
            if ($get['field'] == 'username') {
                if (!empty($get['name'])) {
                    $where['uid'] = Db::name('User')->where(array('username' => $get['name']))->value('id');
                }

            } else {
                $where[$get['field']] = $get['name'];
            }
            $db->where($where);
        }
        if (isset($get['date']) && $get['date'] != '') {
            $date = $get['date'];
            list($start, $end) = explode(' - ', $date);
            if ($get['time'] == 'end') {
                $db->where('endtime', 'between', [strtotime($start), strtotime($end)]);
            } else {
                $db->where('addtime', 'between', [strtotime($start), strtotime($end)]);
            }

        }
        $db->order('id desc');
        return parent::_list($db);
    }

    /**
     * [bbczedit description] USDT提现的处理
     * @return [type] [description]
     */
    public function bbczedit()
    {
        $id = request()->get('id');
        if (request()->isPost()) {
            $post = request()->post();
            $zxcz = Db::name('bbcz')->where(array('id' => $id))->find();
            $huser = Db::name('user')->where(array('id' => $zxcz['uid']))->find();
            if (!isset($post['typemm'])) {
                $this->error('请选择操作类型');
            }

            if (!$zxcz) {
                $this->error('无效的请求类型');
            }

            if ($zxcz['status'] == 1) {
                $this->error('该订单是提现完成订单,无需操作');
            }

            if ($zxcz['status'] == 2) {
                $this->error('该订单是提现拒绝订单,无需操作');
            }

            if (!$huser) {
                $this->error('平台中暂无该用户');
            }

            if (empty($huser['username'])) {
                $this->error('该用户名不存在');
            }

            if ($huser['status'] != 1) {
                $this->error('该用户已冻结,请先进行解封，在进行操作');
            }

            $where = array();
            if ($post['typemm'] == 1) {

                //执行事务的操作
                Db::startTrans();

                $where = array();
                $where['endtime'] = time();
                $where['status'] = 1;
                $where['adminid'] = session('user')['id'];
                $where['endmoney'] = $huser['res_money'];
                $res[] = Db::name('bbcz')->where(array('id' => $zxcz['id']))->update($where);
                //执行入操作员操作日志表
                $content = '提现余额(提现成功) 操作ID: ' . $id;
                LogService::write('系统管理', $content);
                if (check_arr($res)) {
                    //提交事务
                    Db::commit();
                    $this->success('提现成功', '');
                } else {
                    //事务回滚
                    Db::rollback();
                    $this->error('提现失败');
                }
            } elseif ($post['typemm'] == 2) {
                if (empty($post['content'])) {
                    $this->error('请输入拒绝原因');
                }
                //返还提现数量
                $money = $zxcz['money'];
                //执行事务的操作
                Db::startTrans();
                $res = array();
                $where = array();
                $where['content'] = $post['content'];
                $where['endtime'] = time();
                $where['status'] = 2;
                $where['endmoney'] = $huser['res_money'] + $money;
                $where['adminid'] = session('user')['id'];
                $res[] = Db::name('bbcz')->where(array('id' => $zxcz['id']))->update($where);
                $res[] = mlog($zxcz['uid'], 'res_money', $money, '余额提现失败返还');
                //执行入操作员操作日志表
                $content = '提现余额(拒绝)    操作ID: ' . $id;
                LogService::write('系统管理', $content);
                if (check_arr($res)) {
                    //提交事务
                    Db::commit();
                    $this->success('拒绝成功', '');
                } else {
                    //事务回滚
                    Db::rollback();
                    $this->error('拒绝失败');
                }
            }
        }

        $info = Db::name('bbcz')->where(array('id' => $id))->find();
        $this->assign('info', $info);
        return view();
    }
    
    function paymoney()
    {
        $this->title = '充值列表';
        $get = $this->request->get();
        $db = Db::name("store_pay_list");
        $where = array();
    
        if (isset($get['orderid']) && $get['orderid'] != '') {
            $where['orderid'] = $get['orderid'];
            $db->where($where);
        }

        if (isset($get['member_id']) && $get['member_id'] != '') {
            $where['uid'] = $get['member_id'] ;
            $db->where($where);
        }

        if (isset($get['type']) && $get['type'] != '') {
            $where['type'] = $get['type'];
            $db->where($where);
        }

        if (isset($get['extends']) && $get['extends'] != '') {
            $where['extends'] = $get['extends'];
            $db->where($where);
        }
        if (isset($get['date']) && $get['date'] != '') {
            $date = $get['date'];
            list($start, $end) = explode(' - ', $date);
            $db->where('create_time', 'between', [strtotime($start), strtotime($end)]);
        }
        if (isset($get['field']) && $get['field'] != '') {
            if ($get['field'] == 'phone') {
                $get['name'] && $where['uid'] = Db::name('store_member')->where(array('phone' => $get['name']))->value('id');
            } elseif($get['field'] == 'username') {
                $get['name'] && $where['uid'] = Db::name('store_member')->where(array('nickname' => $get['name']))->value('id');
            }else{
                $where[$get['field']] = $get['name'];
            }

            $db->where($where);
        }
        
        
        $db->order('create_time desc,id desc');
        return parent::_list($db);
    }

    public function stock()
    {
        if (!$this->request->post()) {
            $goods_id = $this->request->get('id');
            $goods = Db::name('store_pay_list')->where(['id' => $goods_id])->find();
            empty($goods) && $this->error('无法操作入库操作！');
            $where = ['goods_id' => $goods_id, 'status' => '1', 'is_deleted' => '0'];
            $goods['list'] = Db::name('StoreGoodsList')->where($where)->select();
            return $this->fetch('', ['vo' => $goods]);
        }
        // 入库保存
        $goods_id = $this->request->post('id');
        list($post, $data) = [$this->request->post(), []];
        $goods = Db::name('store_pay_list')->where(['id' => $post['id']])->find();
        Db::startTrans();
        if($goods){
            if($post['member_level'] == 1){
                $res[] = mlog($goods['uid'], 'pay_money', $goods['money'], '线上充值', '');
            }
            $res[] = Db::name('store_pay_list')->where(['id' => $post['id']])->update(['content'=>$post['desc'],'status'=>$post['member_level']]);
        }
        if (check_arr($res)) {
            Db::commit();
            $this->success('操作成功');
        } else {
            Db::rollback();
            $this->error('操作失败');
        }
    }
    
    
     //动态奖记录
    public function dtlist()
    {
        $this->title = '动态奖金明细';
        $get = $this->request->get();
        $db = Db::name("store_award")->order('id desc');
        $where = array();

        if (isset($get['orderid']) && $get['orderid'] != '') {
            $where['orderid'] = $get['orderid'];
            $db->where($where);
        }

        if (isset($get['type']) && $get['type'] != '') {
            $where['type'] = $get['type']-1;
            $db->where($where);
        }

        if (isset($get['date']) && $get['date'] != '') {
            $date = $get['date'];
            list($start, $end) = explode(' - ', $date);
            $db->where('create_time', 'between', [strtotime($start), strtotime($end)]);
        }
        if (isset($get['field']) && $get['field'] != '') {
            if ($get['field'] == 'email') {
                $get['name'] && $where['uid'] = Db::name('store_member')->where(array('email' => $get['name']))->value('id');
            } elseif($get['field'] == 'username') {
                $get['name'] && $where['uid'] = Db::name('store_member')->where(array('nickname' => $get['name']))->value('id');
            }else{
                $where[$get['field']] = $get['name'];
            }

            $db->where($where);
        }
        return parent::_list($db);
    }
}