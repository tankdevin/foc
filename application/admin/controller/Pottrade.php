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
class pottrade extends BasicAdmin
{

    /**
     * 指定当前数据表11
     * @var string
     */
    public $table1 = 'ty_buylist';
    public $table2 = 'ty_selllist';
    public $table3 = 'ty_match';
    public function buylist()
    {
        // 日志行为类别
        $actions = Db::name($this->table1);
        $this->assign('actions', $actions);
        //总订单数，总冻结USDT数，待匹配数，已完成数，异常单数
        $buylist_num = db::name('ty_buylist')->where('state','neq','6')->count();//总订单数
        $this->assign('buylist_num', $buylist_num);
        $buylist_leavenum = db::name('ty_buylist')->where(['state'=>1])->sum('leavenum');//待匹配数
        $dpp_num = db::name('ty_buylist')->where(['state'=>1])->count();//待匹配笔数
        $this->assign('buylist_leavenum', $buylist_leavenum);
        $this->assign('dpp_num', $dpp_num);
        $ycd_num = db::name('ty_buylist')->where(['state'=>3])->count();//异常单笔数
        $ycd_leavenum = db::name('ty_buylist')->where(['state'=>3])->sum('leavenum');//异常单数量
        $this->assign('ycd_num', $ycd_num);
        $this->assign('ycd_leavenum', $ycd_leavenum);
        $finsh_num = db::name('ty_buylist')->where(['state'=>5])->count();//异常单笔数
        $finsh_leavenum = db::name('ty_buylist')->where(['state'=>5])->sum('totalnum');//异常单笔数
        $this->assign('finsh_num', $finsh_num);
        $this->assign('finsh_leavenum', $finsh_leavenum);

        // 日志数据库对象
        list($this->title, $get) = ['pot买单', $this->request->get()];
        $db = Db::name($this->table1)->order(['is_topping'=>'desc','state'=>'asc','id'=>'desc']);
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

        if (isset($get['uname']) && $get['uname'] != '') {
            $where['uname'] = $get['uname'];
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
        list($this->title, $get) = ['pot卖单', $this->request->get()];
        $db = Db::name($this->table2)->order(['is_topping'=>'desc','state'=>'asc','id'=>'desc']);
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

        if (isset($get['uname']) && $get['uname'] != '') {
            $where['uname'] = $get['uname'];
            $db->where($where);
        }
        return parent::_list($db);
    }


    /*
     * 匹配列表
     * */
    public function matchlist()
    {
        // 日志行为类别
        $actions = Db::name($this->table3);
        //查询有没有预匹配的单子
        $is_exit = db::name('ty_match')->where(['state'=>7])->find();
        if($is_exit){
            $is_state = 1;
        }else{
            $is_state = 0;
        }
        $this->assign('is_state', $is_state);
        $this->assign('actions', $actions);
        // 日志数据库对象
        list($this->title, $get) = ['匹配列表', $this->request->get()];
        $db = Db::name($this->table3)->order('state asc,id desc');
        foreach (['action', 'content', 'username'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->whereLike($key, "%{$get[$key]}%");
        }
        if (isset($get['oid']) && $get['oid'] !== '') {
            $where['b_id'] = $get['oid'];
            $db->where($where);
        }
        if (isset($get['addtime']) && $get['addtime'] !== '') {
            list($start, $end) = explode(' - ', $get['addtime']);
            $db->whereBetween('create_at', ["{$start} 00:00:00", "{$end} 23:59:59"]);
        }
        if (isset($get['ordersn']) && $get['ordersn'] != '') {
            $where['ordersn'] = $get['ordersn'];
            $db->where($where);
        }
        if (isset($get['b_uname']) && $get['b_uname'] != '') {
            $where['b_uname'] = $get['b_uname'];
            $db->where($where);
        }
        if (isset($get['s_uname']) && $get['s_uname'] != '') {
            $where['s_uname'] = $get['s_uname'];
            $db->where($where);
        }
        if (isset($get['uid']) && $get['uid'] != '') {
            $where['uid'] = $get['uid'];
            $db->where($where);
        }
        return parent::_list($db);
    }

    public function selllistsd(){
        $bid = $this->request->param('id');
        $uid_arr = db::name('ty_buylist')->whereIn('id',$bid)->column('uid');
        if($bid){
            session('bid',$bid);
        }
        if(!session('?bid')){
            $this->error('未提交数据');
        }else{
            $bid = session('bid');
        }
        // 日志行为类别
        $actions = Db::name($this->table2);
        $this->assign('actions', $actions);
        $this->assign('bid', $bid);
        // 日志数据库对象
        list($this->title, $get) = ['pot卖单', $this->request->get()];
        $db = Db::name($this->table2)->order('state asc,id desc');
        foreach (['action', 'content', 'username'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->whereLike($key, "%{$get[$key]}%");
        }
        if($bid){
            $db->where('uid','not in',$uid_arr);
        }
        if (isset($get['addtime']) && $get['addtime'] !== '') {
            list($start, $end) = explode(' - ', $get['addtime']);
            $db->whereBetween('create_at', ["{$start} 00:00:00", "{$end} 23:59:59"]);
        }
        if (isset($get['ordersn']) && $get['ordersn'] != '') {
            $where['ordersn'] = $get['ordersn'];
            $db->where($where);
        }

        if (isset($get['uid']) && $get['uid'] != '') {
            $where['uid'] = $get['uid'];
            $db->where($where);
        }
        return parent::_list($db);
    }
    /*
     *自动匹配
     * */
    public function automatching(){
        $buylist = db::name('ty_buylist')->where(['state'=>1])->order(['is_topping'=>'desc','id'=>'asc'])->select();

        foreach($buylist as $k=>$v){
            $this->matchPartner($v['id']);

        }

        $this->success('执行成功','');
    }

    public function matchPartner($b_id){
        $buyInfo = db::name('ty_buylist')->where(array('id'=>$b_id))->find();

        $buy_num = $buyInfo['leavenum'];
        $sell_num = db::name('ty_selllist')->where(array('state'=>1))->where('uid','<>',$buyInfo['uid'])->sum('leavenum');
        //如果总的提供的小玉需求的不能匹配
        if($buy_num > $sell_num){
            return true;
        }
        //匹配开始
        //按照时间数序获得满足的订单
        $sell_all =  db::name('ty_selllist')->where(array('state'=>1))->where('uid','<>',$buyInfo['uid'])->select();

        if($sell_all == false){
            return;
        }
        $sell_match_id = array();
        $sell_vs_num = 0;
        //获得所有的提供id
        foreach($sell_all as $k=>$v){
            $sell_match_id[] = $v['id'];
            $sell_vs_num += $v['leavenum'];
            if($sell_vs_num >=$buy_num){
                breack;
            }
        }
        //1首次匹配 8再次匹配
        if($buyInfo['state']==1){
            $res[] = db::name('ty_buylist')->where(['id'=>$b_id])->update(['leavenum' => '0','state'=>2,'matchtime1'=>time()]);
        }
        //更该状态添加数据
        $sell_match_id = implode(',',$sell_match_id);
        $sell_match =  db::name('ty_selllist')->where('id','in',"$sell_match_id")->select();
        foreach($sell_match as $k=>$v){
            if($buy_num >$v['leavenum']){
                //需要大于提供   全部匹配后继续匹配
                db::name('ty_selllist')->where(array('id'=>$v['id']))->setDec('leavenum',$v['leavenum']);
                $updata = array(
                    'state'=>2,
                    'endtime'=>time()
                );
                db::name('ty_selllist')->where(array('id'=>$v['id']))->setField($updata);
                $this->addMatch($buyInfo['id'],$v['id'],$buyInfo['uid'],$v['uid'],$v['leavenum'],2,'');
            }elseif($buy_num ==$v['leavenum']){
                //需要等于提供   全部匹配后跳出
                db::name('ty_selllist')->where(array('id'=>$v['id']))->setDec('leavenum',$v['leavenum']);
                $updata = array(
                    'state'=>2,
                    'endtime'=>time()
                );
                db::name('ty_selllist')->where(array('id'=>$v['id']))->setField($updata);
                $this->addMatch($buyInfo['id'],$v['id'],$buyInfo['uid'],$v['uid'],$v['leavenum'],2,'');
                $res = array(
                    'state'=>1,
                    'msg'=>'匹配成功'
                );
                return $res;
            }elseif($buy_num <$v['leavenum']){
                //需要小提供     部分匹配后跳出
                db::name('ty_selllist')->where(array('id'=>$v['id']))->setDec('leavenum',$buy_num);
                $this->addMatch($buyInfo['id'],$v['id'],$buyInfo['uid'],$v['uid'],$buy_num,2,'');
                $res = array(
                    'state'=>1,
                    'msg'=>'匹配成功'
                );
                return $res;
            }
            $buy_num -=$v['leavenum'];
        }

    }

    /*
  手动进行匹配
  */
    public function matchsd(){
        //卖单
        $sid = $this->request->param('id');
        //买单
        $bid = session('bid');
        if($bid==false){
            $this->error('请选择购买订单',url('pottrade/buylist'));exit;
        }
        if($sid==false){
            $this->error('请选择出售订单');exit;
        }
        $num = count(explode(',',$bid));
        //必须得保证每个买单的状态都为1
        $buy_number = db::name('ty_buylist')->whereIn('id',"$bid")->where(['state'=>1])->count();

        if($buy_number != $num){
            $this->error('请选择正确的买单进行匹配');exit;
        }
        //var_dump($_POST['sid']);exit;
        $buy_num = db::name('ty_buylist')->whereIn('id',"$bid")->where(['state'=>1])->sum('totalnum');
        $buy_uid = db::name('ty_buylist')->whereIn('id',"$bid")->where(['state'=>1])->column('uid');

        //4的话是显示出不来的
        $sell_num = db::name('ty_selllist')->where('state','1')->whereIn('id',$sid)->whereNotIn('uid',$buy_uid)->sum('leavenum');

        $num = min($buy_num,$sell_num);

//        if($num == $sell_num){
//            $this->success("提供金额不足",'');
//        }
        $bid = explode(',',$bid);
        for($i=0;$i<$num;$i++){
           $this->matchhand($bid[$i],$sid);
        }
        session('bid',null);
        $this->success('匹配成功','');
    }
    /*
     * 进行匹配
     * */
    public function matchhand($b_id=1,$sell_match_id=array(3)){
        $buyInfo = db::name('ty_buylist')->where(['id'=>$b_id,'state'=>1])->find();
        if($buyInfo){
            $buy_num = $buyInfo['totalnum'];
            $sell_match = db::name('ty_selllist')->where(['state'=>1])->whereIn('id',$sell_match_id)->where('uid','<>',$buyInfo['uid'])->order('id asc')->select();
            if($buyInfo['state']==1){
                $res[] = db::name('ty_buylist')->where(['id'=>$b_id])->update(['leavenum' => '0','state'=>2,'matchtime1'=>time()]);
            }
            foreach($sell_match as $k=>$v){
                if($buy_num >$v['leavenum']){
                    //需要大于提供   全部匹配后继续匹配
                    db::name('ty_selllist')->where(array('id'=>$v['id']))->setDec('leavenum',$v['leavenum']);
                    $updata = array(
                        'state'=>2,
                        'endtime'=>time()
                    );
                    db::name('ty_selllist')->where(array('id'=>$v['id']))->setField($updata);
                    $this->addMatch($buyInfo['id'],$v['id'],$buyInfo['uid'],$v['uid'],$v['leavenum'],2,'');
                }elseif($buy_num ==$v['leavenum']){
                    //需要等于提供   全部匹配后跳出
                    db::name('ty_selllist')->where(array('id'=>$v['id']))->setDec('leavenum',$v['leavenum']);
                    $updata = array(
                        'state'=>2,
                        'endtime'=>time()
                    );
                    db::name('ty_selllist')->where(array('id'=>$v['id']))->setField($updata);
                    $this->addMatch($buyInfo['id'],$v['id'],$buyInfo['uid'],$v['uid'],$v['leavenum'],2,'');
                    $res = array(
                        'state'=>1,
                        'msg'=>'匹配成功'
                    );
                    return $res;
                }elseif($buy_num <$v['leavenum']){
                    //需要小提供     部分匹配后跳出
                    db::name('ty_selllist')->where(array('id'=>$v['id']))->setDec('leavenum',$buy_num);
                    $this->addMatch($buyInfo['id'],$v['id'],$buyInfo['uid'],$v['uid'],$buy_num,2,'');
                    $res = array(
                        'state'=>1,
                        'msg'=>'匹配成功'
                    );
                    return $res;
                }
                $buy_num -=$v['leavenum'];
            }
        }
    }


    /**
     * 添加匹配表记录
     * @param $bid
     * @param $sid
     * @param $buid
     * @param $suid
     */
    public function addMatch($bid,$sid,$buid,$suid,$money,$type,$shunxu=''){
        $ordersn = 'M'.time().rand(1,99);
        //获得购买列表的信息
        $buyInfo = db::name('ty_buylist')->where(array('id'=>$bid))->find();
        $sellInfo = db::name('ty_selllist')->where(array('id'=>$sid))->find();
        //收款地址
        $payment = db::name('store_member_payment')->where(array('uid'=>$sellInfo['uid']))->value('payment');
        $data = array(
            'ordersn'=>$ordersn,
            'b_id'=>$bid,
            's_id'=>$sid,
            'b_uid'=>$buid,
            'b_uname'=>$buyInfo['uname'],
            's_uid'=>$suid,
            's_uname'=>$sellInfo['uname'],
            'num'=>$money,
            'type'=>$type,//1自动2手动
            'shunxu'=>$shunxu,//1购买订单前期2购买订单后期
            'begintime'=>$buyInfo['addtime'],
            'state'=>7,//预匹配
            'addtime7'=>time(),
            'payment'=>$payment
        );
        db::name('ty_match')->insert($data);
        return $ordersn;
    }


    /**
     * 卖单
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function add()
    {
        if ($this->request->isGet()) {
            $this->title = '添加卖单';
            //$this->_form_assign();
            return $this->_form($this->table2, 'form');
        }
        $data = $this->_form_build_data();
        $money = $data['main']['money'];
        $username = $data['main']['username'];
        $user = db::name('store_member')->where(['email'=>$username])->find();
        if($user){
            $payment = db::name('store_member_payment')->where(['uid'=>$user['id']])->find();
            if(!$payment){
                $this->error('此会员，没有添加收币地址');
            }
            //添加记录，进行扣除
            Db::startTrans();
            $order_id= Db::name('ty_selllist')->insertGetId([
                'uid'=>$user['id'],
                'ordersn'=>'S'.substr(time(), -9),
                'uname'=>$username,
                'totalnum'=>$money,
                'leavenum'=>$money,
//            'pdnum'=>$pdnum,
                'state'=>1,//1待匹配2完成//4预匹配
                'addtime'=>time(),
                'admin_state'=>1
            ]);
            $res[] = $order_id;
            if (check_arr($res)) {
                Db::commit();
                list($base, $spm, $url) = [url('@admin'), $this->request->get('spm'), url('admin/pottrade/selllist')];
                $this->success('卖单添加成功',"{$base}#{$url}?spm={$spm}");
            } else {
                Db::rollback();
                $this->error('卖单添加失败');
            }
        }else{
            $this->error('会员账号错误！');
        }

    }

    /*
   从表单中获取数据
   */
    protected function _form_build_data()
    {


//        empty($post['username']) && $this->error('会员账号不能为空！');
//        empty($post['money']) && $this->error('金额不能为空！');
        // 商品主数据组装
        $main['username'] = $this->request->post('username', '');
        $main['money'] = $this->request->post('money', '');
        return ['main' => $main];
    }
    /**
     * 表单数据处理
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _form_assign()
    {
        $financial_licai = Db::name('financial_licai')->where(['status'=>1,'is_deleted'=>0])->select();
        array_unshift($specs, ['spec_title' => ' - 不使用规格模板 -', 'spec_param' => '[]', 'id' => '0']);
        $this->assign([
            'financial_licai' => $financial_licai,
        ]);
    }
    /*
     * 后台操作(超时未打款)
     * */
    public function admincaozuo()
    {
        if (!$this->request->isPost()) {
            $oid = input('param.oid',0);
            if($oid){
                $info = Db::name('ty_match')->where(['id'=>$oid])->find();
            }
            $this->assign('info',$info);
        } else {

            $data = $this->request->post();
            unset($data['s_uname']);
            unset($data['payment']);
            $match = Db::name('ty_match')->where(['id'=>$data['id']])->find();
            $buyinfo = db::name('ty_buylist')->where(array('id'=>$match['b_id']))->find();
            $result = Db::name('ty_match')->where(['id'=>$data['id']])->update(['payimg'=>$data['img'],'addtime6'=>time(),'state'=>6]);
            //经过后台处理过后，强制对方确认收款
            $checkMatch = db::name('ty_match')->where(array('b_uid'=>$buyinfo['uid'],'b_id'=>$buyinfo['id']))->where('state','lt',2)->count();
            if($checkMatch<=0){
                if($buyinfo['state']==4){
                    $uparry = array(
                        'state'=>5,
                        'endtime'=>time()
                    );
                    $res[] = db::name('ty_buylist')->where(array('id'=>$match['b_id']))->setField($uparry);
                }
            }
            $this->setSellOver($match['s_id']);
            if ($result !== FALSE) {
                $this->success('操作成功','');
            } else {
                $this->error('操作失败','');
            }
        }

        return view();
    }
    /**
     * 判断出售表是否全部结束
     */
    public function setSellOver($s_id){

        $re = db::name('ty_match')->where(array('s_id'=>$s_id))->where('state','lt',2)->find();
        $leavenum =  db::name('ty_selllist')->where(array('id'=>$s_id))->value('leavenum');
        if(!$re && $leavenum==0){
            $updata = array(
                'state'=>3,
                'oktime'=>time()
            );
            $res[] = db::name('ty_selllist')->where(array('id'=>$s_id))->setField($updata);
        }
    }
    /*
    * 超时未收款
    * */
    public function adminsellok(){
        Db::startTrans();
        try {
            $m_id = input('param.oid',0);
            if($m_id){
                $info = Db::name('ty_match')->where(['id'=>$m_id])->find();
                $uid = $info['b_uid'];
                $check = db::name('ty_match')->where(array('s_uid'=>$uid,'id'=>$m_id,'state'=>1))->find();
                if($check == false){
                    $this->error('无效订单');exit;
                }
                $buyinfo = db::name('ty_buylist')->where(array('id'=>$check['b_id']))->find();
                $uparry = array(
                    'state'=>2,
                    'addtime2'=>time()
                );
                Db::startTrans();
                //更改状态
                $res[] = db::name('ty_match')->where(array('s_uid'=>$uid,'id'=>$m_id,'state'=>1))->setField($uparry);
                //更改购买列表的状态
                //判断购买者的匹配表是否全部完成才能进行下次匹配
                $checkMatch = db::name('ty_match')->where(array('b_uid'=>$buyinfo['uid'],'b_id'=>$buyinfo['id']))->where('state','lt',2)->count();
                if($checkMatch<=0){
                    if($buyinfo['state']==4){
                        $uparry = array(
                            'state'=>5,
                            'endtime'=>time()
                        );
                        $res[] = db::name('ty_buylist')->where(array('id'=>$check['b_id']))->setField($uparry);
                        //结束之后（相当于产生一个pot矿机）
                        $order_id= Db::name('financial_order')->insertGetId([
                            'ordersn'=> substr(time(),5) . mt_rand(100, 999),
                            //'lc_id' => 'pot矿机',
                            'uid' => $buyinfo['uid'],
                            'uname' => $buyinfo['uname'],
                            'title' =>  'pot矿机',
                            'day' => sysconf('pot_day'),
                            'create_at' => time(),
                            'market_price' => $buyinfo['totalnum'],
                            'sl_rate'=>'',
                            'type'=>1
                        ]);
                        if($order_id){
                            mlog($buyinfo['uid'], 'wallet_four', $buyinfo['totalnum'], "购买pot矿机,增加算力{$buyinfo['totalnum']}个", '','','4',$this->wx_user_id);
                            //推荐奖
                            $award = new Award;
                            $award->jdaward($buyinfo['uid'],$buyinfo['totalnum'],$order_id);
                        }

                    }
                }
                $this->setSellOver($check['s_id']);
                Db::commit();
                $this->success('确认成功');
            }

        } catch (\Exception $e) {
            Db::rollback();
            $this->error("操作失败，请稍候再试！");
        }
    }
    /*
     * pot列表
     * */
    public function list()
    {
        $this->title = '理财订单';
        $get = $this->request->get();
        $db = Db::name("financial_order")->where('type','1')->order('id desc');
        $where = array();
        if (isset($get['phone']) && $get['phone'] != '') {
            $where['phone'] = $get['phone'];
            $db->where($where);
        }
        if (isset($get['order_no']) && $get['order_no'] != '') {
            $where['ordersn'] = $get['order_no'] ;
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
            if($get['extends'] != 'awardUsdt'){
                $where['type'] = $get['extends'];
            }else{
                $where['extends'] = $get['extends'];
            }
            $db->where($where);
        }
        if (isset($get['create_at']) && $get['create_at'] != '') {
            $date = $get['create_at'];
            list($start, $end) = explode(' - ', $date);
            $db->where('create_at', 'between', [strtotime($start), strtotime($end)+86400]);
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
        return parent::_list($db->order('id desc'));
    }

    /*
    * pot列表
    * */
    public function naclist()
    {
        $this->title = '算力产生NAC记录';
        $get = $this->request->get();
        $db = Db::name("pot_daysy")->order('id desc');
        $where = array();
        if (isset($get['uname']) && $get['uname'] != '') {
            $where['uname'] = $get['uname'];
            $db->where($where);
        }
        if (isset($get['create_at']) && $get['create_at'] != '') {
            $date = $get['create_at'];
            list($start, $end) = explode(' - ', $date);
            $db->where('create_at', 'between', [strtotime($start), strtotime($end)+86400]);
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
        return parent::_list($db->order('id desc'));
    }


    /*
    * 与匹配成为已匹配
    * */
    public function pre_matching(){
        $res1 = db::name('ty_buylist')->where(array('state'=>2))->update(['state'=>4,'matchtime1'=>time()]);
        //挂匹配单
        $res2 = db::name('ty_match')->where(array('state'=>7))->update(['state'=>0,'addtime'=>time()]);


        //挂卖单
//        $data3 = array(
//            'ypp_state'=>1,
//        );
//        $res3 = m('selllist')->where(array('ypp_state'=>0))->setField($data3);
        $this->success('执行成功','');

    }

    /*
     * 置顶/解除置顶
     * */
    public function zhiding()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $id = $data['id'];
            if (empty($id))
                $this->error('请选择要操作的数据');
            $method = $data['field'];
            unset( $data['field']);
            switch (strtolower($method)) {
                case 'zhiding':
                    $data = array('is_topping' => 1);
                    break;
                case  'jczhiding':
                    $data = array('is_topping' =>0);
                    break;
                case  'jzpp':
                    $data = array('state' =>7);
                    break;
                case  'jcjzpp':
                    $data = array('state' =>1);
                    break;
                default:
                    $this->error('参数非法');
            }
            if (Db::name('ty_buylist')->where('id','in',$id)->update($data)) {
                $this->success('操作成功', '');
            } else {
                $this->error('操作失败');
            }
        }
    }

    /*
   * 卖单置顶/解除置顶
   * */
    public function sellzhiding()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $id = $data['id'];
            if (empty($id))
                $this->error('请选择要操作的数据');
            $method = $data['field'];
            unset( $data['field']);
            switch (strtolower($method)) {
                case 'zhiding':
                    $data = array('is_topping' => 1);
                    break;
                case  'jczhiding':
                    $data = array('is_topping' =>0);
                    break;
                case  'jzpp':
                    $data = array('state' =>4);
                    break;
                case  'jcjzpp':
                    $data = array('state' =>1);
                    break;
                default:
                    $this->error('参数非法');
            }
            if (Db::name('ty_selllist')->where('id','in',$id)->update($data)) {
                $this->success('操作成功', '');
            } else {
                $this->error('操作失败');
            }
        }
    }
    /**
     * 买单设置置顶
     */
    public function setZd()
    {

        try {
            $is_topping = Db::name($this->table1)->where('id', input('param.id'))->value('is_topping');
            $is_topping = $is_topping == 1 ? 0 : 1;
            Db::name($this->table1)->where('id', input('param.id'))->setField('is_topping', $is_topping);
        } catch (\Exception $e) {
            $this->error("操作失败，请稍候再试！");
        }
        $this->success('操作成功！', '');
    }



    /**
     * 买单设置禁止
     */
    public function setJz()
    {

        try {
            $state = Db::name($this->table1)->where('id', input('param.id'))->value('state');
            $state = $state == 1 ? 7 : 1;
            Db::name($this->table1)->where('id', input('param.id'))->setField('state', $state);
        } catch (\Exception $e) {
            $this->error("操作失败，请稍候再试！");
        }
        $this->success('操作成功！', '');
    }
    /**
     * 卖单设置置顶
     */
    public function sellsetZd()
    {

        try {
            $is_topping = Db::name($this->table2)->where('id', input('param.id'))->value('is_topping');
            $is_topping = $is_topping == 1 ? 0 : 1;
            Db::name($this->table2)->where('id', input('param.id'))->setField('is_topping', $is_topping);
        } catch (\Exception $e) {
            $this->error("操作失败，请稍候再试！");
        }
        $this->success('操作成功！', '');
    }



    /**
     * 卖单设置禁止
     */
    public function sellsetJz()
    {

        try {
            $state = Db::name($this->table2)->where('id', input('param.id'))->value('state');
            $state = $state == 1 ? 4 : 1;
            Db::name($this->table2)->where('id', input('param.id'))->setField('state', $state);
        } catch (\Exception $e) {
            $this->error("操作失败，请稍候再试！");
        }
        $this->success('操作成功！', '');
    }

    /*
     * 预匹配的时候还能进行撤回
     * */
    public function matchrecallwith(){
        $id = $this->request->param('id');
        if($id){
            $match = db::name('ty_match')->where(['id'=>$id])->find();
            if($match['state'] != 7){
                $this->error("该匹配单无效");
            }
            Db::startTrans();
            try {
                $buyinfo = db::name('ty_buylist')->field('totalnum,id')->where(['id'=>$match['b_id']])->find();
                //买单
                //查询出有关联的匹配单和卖单
                $ty_match = db::name('ty_match')->field('id,s_id,num')->where(['b_id'=>$buyinfo['id'],'state'=>7])->select();
                //var_dump($match);
                foreach($ty_match as $k=>$val){
                    db::name('ty_selllist')->where(['id'=>$val['s_id']])->setInc('leavenum',$val['num']);
                    db::name('ty_selllist')->where(['id'=>$val['s_id']])->update(['state'=>1]);
                    db::name('ty_match')->where(['id'=>$val['id']])->update(['state'=>9]);
                }
                db::name('ty_buylist')->where(['id'=>$match['b_id']])->update(['state'=>1,'leavenum'=>$buyinfo['totalnum']]);


                Db::commit();
                $this->success('撤回成功');
            }catch(Exception $e){
                Log::error($e->getMessage()."||".$e->getLine());
                Db::rollback();
                $this->success('撤回失败');
            }
        }
    }


}
