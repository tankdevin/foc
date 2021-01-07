<?php

namespace member;

use member\CommonException;
use app\common\model\System;
use think\Db;
use app\common\model\User;
use app\common\model\Syslevel;
use Cache;

interface MemberFreeBonusInterface
{
    /*
     * pdr ---> account_money   hashrate-----> 算力挖矿  mining -----> 分享挖矿
     *
     * bag_log  佣金记录表
     * extends  share_dig   分享挖矿收益
     * extends  check_in    签到挖矿收益
     * extends  team_bonus_money   团队挖矿收益
     * extends  static_dig  静态挖矿收益
     * extends  reduce_power   转入到算力挖矿中 减少 pdr 增加 算力挖矿
     * extends  increase_power   取消算力挖矿   减少算力挖矿里面的  增加pdr
     * extends  sell_pdr  c2c卖出pdr;
     * extends  buy_pdr   c2c买PDR;
     * extends  share_dig_spread 推广赠送上级分享算力
     * extends  time_out_transaction 超时订单
     * extends  cancel_C2c_order 取消订单
     * extends  upgrade_consum 升级消耗PDR
     * extends  cancel_dig_account 算力超过10天转出到account
     * extends  cancel_dig_hashrate 算力超过10天减少hashrate
     * extends  merchant_mining_shou 扫码转账(收款方分享挖矿百分点)
     * extends  merchant_pdr_shou 扫码转账(收款方PDR百分点)
     *
     *
     * store_order  算力订单 表
     * status  1默认  挖矿中 2取消挖矿 3满了10天自动退出来
     * type  1转入 2 转出
     *
     *store_order_c2c  c2c交易订单表
     *type  1 买入  2 卖出
     *status 1 等待中 2匹配中  3 完成
     *
     * store_c2c_relation c2c  c2c交易关系表
     * fromid  购买人id
     * toid    购买该笔订单所属人的id
     *
     * buy_mining 买家交易挖矿
     * seller_mining  卖家交易挖矿
     * */
    //静态挖矿
    function stillMining();

    //获取到分享算力订单
    function getShareOrderList();

    //发送团队收益
    function BonusTeamBig( $userid, $bonus_money, $order_id );

    //获取直推人数
    function getDirectPush( $userid );

    //分享挖矿
    function BonusShareDig();

    //获取当前用户的分享算力比例
    function getSharePower();

    //签到
    function checkIn();

    //释放用户积分
    function sendTodayByUserintegral();


}
ignore_user_abort();
set_time_limit(0);
date_default_timezone_set('Asia/Shanghai');

class MemberFreeBonus implements MemberFreeBonusInterface
{

    static $instance;
    protected $uid;
    const NO_OffICIAl = 1;  //非正式
    const OffICIAl = 2; //正式
    const MAXSTATICBONUS = 10; //最大发放天数
    static $PreviousBonus = 0;
    /*
     * 定时任务进行收益方法  freeBonus
     * */
    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    //获取代理升级订单
    public function getUpgradeOrder()
    {
        return $this->OrderList = Db::name('store_upgrade')->where(['id' => $this->tranID])->find();
    }

    //

    /*链式操作获取用户信息*/
    protected function getByIdUserInfo()
    {
        if (!$this->uid) return false;
        $userModel = User::get($this->uid);
        return $userModel;
    }

    //获取用户等级信息
    public function getUserLevel()
    {
        if (!$this->uid) return false;
        $userModel = Db::name('store_member a')
            ->field('a.*,b.title,b.share,b.fans,b.force,b.num,b.teaming,b.coefficient,b.siginmoney,a.shouyi_money')
            ->join('sys_level b', "a.member_level = b.id")
            ->where(['a.id' => $this->uid])
            ->find();
        if (!$userModel) {
            throw new CommonException('用户不存在');
        }
        return $userModel;
    }

    //分割上级用户--暂时不用
    protected function getSplitUser( $user_all_leader )
    {
        if (!$user_all_leader) return false;
        $user_all_leader = array_reverse(explode(',', $user_all_leader));
        return [array_slice($user_all_leader, 0, 10), array_slice($user_all_leader, 10)];
    }

    public function checkIn()
    {
        $userinfo = $this->getUserLevel();
//        $registerTime = strtotime($userinfo['create_at']);

        if (!$userinfo) {
            throw  new \member\CommonException('非法操作');
        }
        //函数用于在一次操作中给一组变量赋值。
        list($shiDay, $erShiDay) = $this->getAgreeTime($userinfo['create_at']);
        //非正式会员
        $insertFlag = FALSE;
        //NO_OffICIAl  OffICIAl 定义的是一个常量，然后进行静态调用
        //当前级别等于1的话，从注册时间在10天之内
        //当前级别等于1的话，从注册时间在20天之内
        if ($userinfo['member_level'] == static::NO_OffICIAl) {
            if (get_time() < $shiDay) {
                $insertFlag = true;
            };
        } elseif ($userinfo['member_level'] >= static::OffICIAl) {
//            //正式会员

            if (get_time() < $erShiDay) {
                $insertFlag = true;
            }
        }
        if ($insertFlag) {
            //store_member_idcard 会员实名认证
            $real_cert = Db::name('store_member_idcard')->where(['uid' => $this->uid, 'status' => 1])->find(); //当前用户是否实名认证
            if (!$real_cert) {
                throw  new \member\CommonException('您还没有实名认证');
            }
            $sign_flag = Db::name("store_member_signin")->whereTime('time', 'today')->where(['uid' => $this->uid])->count() ?? 0;
            if ($sign_flag > 0) {
                throw  new \member\CommonException('今天已经签到过啦');
            }
            //开启事物
            Db::startTrans();
            $res['mlog'] = mlog($this->uid, 'mining', $userinfo['siginmoney'], $userinfo['title'] . '签到赠送分享算力', 'check_in');

            $res['sign_res'] = Db::name('store_member_signin')->insert([
                'time' => time(),
                'money' => $userinfo['siginmoney'],
                'uid' => $this->uid,
                'content' => $userinfo['title'] . '签到赠送分享算力:' . $userinfo['siginmoney'] . '个',
            ]);
            if (check_arr($res)) {
                Db::commit();
                return true;
            } else {
                Db::rollback();
                throw  new \member\CommonException('签到失败,您不112符合新人签到规则222');
            }
        } else {
            throw  new \member\CommonException('签到失败,您不符合新人签到规则111');
        }
    }

    /*******************************静态挖矿开始************************************/
    public function stillMining()
    {
        ignore_user_abort();
        set_time_limit(0);
        date_default_timezone_set('Asia/Shanghai');

        $orderList = $this->BonusShareOrder();
        foreach ($orderList as $k => $v) {
            $order_id = $v['id'];
            $isFlagRepeat = $this->getRepeatDig($order_id); //判断当天是否重复发放过
            if ($isFlagRepeat) {
                $bonus_count = $this->getStaticDigCount($order_id);
                //发放10天 自动转出
                if ($bonus_count > self::MAXSTATICBONUS) {
                    $this->autoMatic($v['id']);
                    //return false; //自动释放算力订单;
                }else{
                    $bonus_rule = $this->getStaticDigRule($bonus_count);

//                sm($bonus_rule);
                    $bonus_money = $v['real_price'] * $bonus_rule['earnings'] / 100;

                    if ($bonus_money > 0) {
//                    sm($k);
                        //发放静态挖矿收益
                        Db::startTrans();
                        $res['static_bonus'] = mlog($v['mid'], 'shouyi_money', $bonus_money, $bonus_rule['title'] . '静态挖矿,产生收益', 'static_bonus', $order_id);
                        $res['team_bonus'] = $this->BonusTeamBig($v['mid'], $bonus_money, $order_id); //发放团队收益

                        if (check_arr($res)) {
                            Db::commit();
                            echo $order_id . '_发放成功';
                        } else {
                            Db::rollback();
                            //TODO 写入收益记录是否发
                        }
                    } else {
                        //TODO  发送金额不足
                    }
                }

            } else {
            	Db::name('store_order')->where(['id' => $order_id])->update(['up_time'=>date('Y-m-d',time())]);
                echo $order_id . '当天发过了';
                //TODO 重复挖矿了
            }
        }
    }

    public function stillMiningad()
    {
        ignore_user_abort();
        set_time_limit(0);
        date_default_timezone_set('Asia/Shanghai');

        $orderList = $this->BonusShareOrder();

        foreach ($orderList as $k => $v) {
            $order_id = $v['id'];
            for ($i=0;$i<=1;$i++){//1573833601
                $new = strtotime('+'.$i.' day','1573833601');
                //$old = strtotime(date('Y-m-d 23:59:59',strtotime('+'.$i.' day','1573833601')));
                //$isFlagRepeat = $this->getRepeatDigad($order_id,$new,$old); //判断当天是否重复发放过
                $hashrateInfo = Db::name('store_order')->where(['id' => $order_id])->find();

                if(strtotime($hashrateInfo['create_at'])<$new){
                    $bonus_count = $this->getStaticDigCount($order_id);

                    //发放10天 自动转出
                    if ($bonus_count > self::MAXSTATICBONUS) {
                        $this->autoMatic($v['id']);
                        return false; //自动释放算力订单;
                    }
                    $bonus_rule = $this->getStaticDigRule($bonus_count);

//                sm($bonus_rule);
                    $bonus_money = $v['real_price'] * $bonus_rule['earnings'] / 100;

                    if ($bonus_money > 0) {
//                    sm($k);
                        //发放静态挖矿收益
                        Db::startTrans();
                        $res['static_bonus'] = mlog($v['mid'], 'shouyi_money', $bonus_money, $bonus_rule['title'] . '静态挖矿,产生收益', 'static_bonus', $order_id);
                        $res['team_bonus'] = $this->BonusTeamBig($v['mid'], $bonus_money, $order_id); //发放团队收益

                        if (check_arr($res)) {
                            Db::commit();
                            echo $order_id . '_发放成功';
                        } else {
                            Db::rollback();
                            //TODO 写入收益记录是否发
                        }
                    } else {
                        //TODO  发送金额不足
                    }
                }
            }
        }
    }

    //发放团队收益
    public function BonusTeamBig( $userid, $bonus_money, $orderid )
    {
        $res = [TRUE];
        $parent_leader = $this->getUser($userid);
        list($TenInside, $TenOuter) = $this->getSplitUser($parent_leader['all_leader']);
        $allParent = count($TenInside) + count($TenOuter);
        if (count($TenInside) > 0) {
            //10代内发放团队收益
            $PreviousBonus = 0;
            foreach ($TenInside as $k => $v) {
                $k++;
                $userinfo = $this->getUser($v);

                if ($userinfo['member_level'] == 1 || $userinfo['is_disable'] == -1) continue;
                $pushUserCount = $this->getDirectPush($v); //获取直推人数
                if ($k > $pushUserCount) continue;   //当前发放人数 大于直推的跳出去
                $HashRateInfo = $this->getHashRate($k); //每代发放详情
//                echo ($k)."|";
//                echo ($v)."|";
//                echo ($pushUserCount)."|";
                $team_bonus_money = $bonus_money * $HashRateInfo['earnings'] / 100;
                $content = "发送第{$HashRateInfo['id']}代团队收益";
                $res[] = mlog($v, 'shouyi_money', $team_bonus_money, $content, 'team_bonus_money', $orderid);
            }

            //10代发送团队收益
            if ($allParent > 10) {
                foreach ($TenOuter as $k => $v) {
                    $userinfo = $this->getUser($v);

                    if ($userinfo['member_level'] <= 2) continue;
                    $levelInfo = $this->getLevelInfo($userinfo['member_level']);  //星级分佣
                    $team_bonus_money = $bonus_money * $levelInfo['teaming'] / 100;

                    if ($team_bonus_money >= $PreviousBonus) {
                        $TenOuterBonus = $team_bonus_money - self::$PreviousBonus;
                        self::$PreviousBonus = $team_bonus_money;
                    }

                    if ($TenOuterBonus > 0) {
                        $content = "用户ID:{$v},发送{$levelInfo['title']},收益金额";
                        mlog($v, 'shouyi_money', $TenOuterBonus, $content, 'team_bonus_money', $orderid);
                    }
                }
            }
            return $res;
        }
        return $res;
    }


    public function getLevelInfo( $key )
    {
        return Db::name('sys_level')->where(['id' => $key])->find();
    }

    //获取每代的收益方法
    public function getHashRate( $key )
    {
        return Db::name('sys_hashrate')->where(['id' => $key])->find();
    }


    //获取直推人数
    public function getDirectPush( $userid )
    {
        return Db::name("store_member")->where(['first_leader' => $userid])->count();
    }


    //自动释放超过10天的订单
    public function autoMatic( $order_id )
    {
        $orderInfo = $this->getOrderInfo($order_id);
        if ($orderInfo) {
            $content = "订单ID:" . $order_id . ',算力订单超过' . self::MAXSTATICBONUS . '天,自动返还到挖矿收益中';
            Db::startTrans();
            $orderUserInfo = Db::name('store_member')->where(['id'=>$orderInfo['mid']])->find(); //该笔订单用户信息
            $buyTotal = $orderUserInfo['hashrate'];
            if($orderUserInfo['member_level'] <= 2){
                //正式用户
                if ($buyTotal - $orderInfo['real_price'] < 100) {
                    $res[] = Db::name("store_member")->where(['id'=>$orderInfo['mid']])->update(['member_level' => 1]) !== FALSE;
                }
            }elseif($orderUserInfo['member_level'] > 3){
                //星级用户
                //$force = Db::name('sys_level')->where('id',$orderUserInfo['member_level'])->value('force');
                $force = Db::name('sys_level')->where('id',$orderUserInfo['member_level']-1)->value('force');
                if ($buyTotal - $orderInfo['real_price'] < $force) {
                    $res[] = Db::name("store_member")->where(['id'=>$orderInfo['mid']])->update(['member_level' => 3]) !== FALSE;
                }
            }
            $res[] = mlog($orderInfo['mid'], 'shouyi_money', $orderInfo['real_price'], $content, 'cancel_dig_account', $order_id);
            $res[] = mlog($orderInfo['mid'], 'hashrate', -$orderInfo['real_price'], $content, 'cancel_dig_hashrate', $order_id);
            $res[] = Db::name('store_order')->where('id', $order_id)->update(['status' => 3, 'type' => 2]);
            if (check_arr($res)) {
                Db::commit();
                return TRUE;
            } else {
                Db::rollback();
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    //是否重复挖矿
    public function getRepeatDig( $orderid )
    {
        if($orderid > 0){
            $recent_bonus_log = Db::name("bags_log")->where(['orderId' => $orderid,'extends'=>'static_bonus'])->order('create_time desc')->find();
            if ($recent_bonus_log == FALSE) {
                $hashrateInfo = Db::name('store_order')->where(['id' => $orderid])->find();
                if (!$hashrateInfo) return FALSE;
                if (time() > strtotime($hashrateInfo['create_at']) + 86400) {
                    return TRUE;
                } else {
                    return FALSE;
                }
            }else{
                //最近一次挖矿时间加1天 大于当前时间的  不能挖矿
                if ($recent_bonus_log['create_time'] + 86400 > get_time()) {
                    return FALSE;
                }else{
                    return TRUE;
                }
            }
        }else{
            return FALSE;
        }
    }

    //获取静态挖矿第几天规则
    public function getStaticDigRule( $id )
    {
        return Db::name('sys_earnings')->where(['id' => $id])->find();
    }

    public function getOrderInfo( $order_id )
    {
        return Db::name('store_order')->where(['id' => $order_id])->find();
    }

    //获取静态挖矿记录
    public function getStaticDigCount( $order_id )
    {
        $order_info = $this->getOrderInfo($order_id);
        $bonus_log_count = Db::name("bags_log")
            ->where(['orderId' => $order_id, 'extends' => 'static_bonus'])
            ->whereTime('create_time', '>', strtotime($order_info['create_at']))
            ->count();
        return $bonus_log_count+1;
    }

    public function BonusShareOrder()
    {
        $dat_page = Cache::get('dat_page')  ?  Cache::get('dat_page') : 1;
        $this->orderList = Db::name('store_order')->where(['type' => 1, 'status' => 1])->page($dat_page,300)->order('id desc')->select();
        if(!empty($this->orderList)){
            $dat_page++;
            Cache::set('dat_page',$dat_page);

        }else{
            Cache::rm('dat_page');
        }
        return $this->orderList;
//        return $this->orderList = Db::name('store_order')->where(['order_no' => '309874581554775636', 'status' => 1])->select();
    }
    
    public function BonusShareOrderad()
    {
        $info = Db::name('store_order')->where(['type' => 1, 'status' => 1])->page(1,300)->order('id desc')->select();
        return $info;
//        return $this->orderList = Db::name('store_order')->where(['order_no' => '309874581554775636', 'status' => 1])->select();->where('up_time','NEQ',date('Y-m-d',time()))
    }

    /*******************************静态挖矿结束************************************/


    /********************************分享算力挖矿********************************************/
    //分享挖矿开始
    public function BonusShareDig()
    {
        $digFlag = $this->getWhetherShareDig();
        if ($digFlag) {
//            $digPro = $this->getSharePower();  //收矿比例
            $userinfo = $this->getUserLevel($this->uid);
            $money = $userinfo['coefficient'] * $userinfo['mining'] / 100; //分享挖矿系数
            if ($money <= 0) {
                throw  new \member\CommonException('收取失败,您没有收益可以收取哦');
            }
            $content = "获取分享挖矿";
            Db::startTrans();
            $res[] = mlog($this->uid, 'shouyi_money', $money, $content, 'share_dig');
            if (check_arr($res)) {
                Db::commit();
                return TRUE;
            } else {
                Db::rollback();
                throw new \member\CommonException('收取失败,您暂时没有收益可以收取哦');
            }
        } else {
            throw  new \member\CommonException('收取失败,您暂时没有收益可以收取哦');
        }
    }

    //判断是否在分享挖矿阶段内
    public function getWhetherShareDig()
    {
        $recent_bonus_log = Db::name("bags_log")->where(['extends' => 'share_dig', 'uid' => $this->uid])->order('create_time desc')->find();
        if ($recent_bonus_log == FALSE) {
            //第一次直接可以挖矿
            return TRUE;
        }

        //最近一次挖矿时间加1天 大于当前时间的  不能挖矿
        if ($recent_bonus_log['create_time'] + 86400 > get_time()) {
            return FALSE;
        }
        return TRUE;
    }

    //获取最近一次分享挖矿的时间
    public function getRecentShareDig()
    {
        $result = $recent_bonus_log = Db::name("bags_log")->where(['extends' => 'share_dig', 'uid' => $this->uid])->order('create_time desc')->find();
        if ($result) {
            return $result['create_time'];
        } else {
            return FALSE;
        }
    }

    //获取分享算力 比列
    public function getSharePower()
    {
        $orderByUidSum = Db::name('store_order')->where(['mid' => $this->uid])->where(['type' => 1])->sum('real_price');
        return $orderByUidSum > 100 ? sysconf('yesmining') / 100 : sysconf('nomining') / 100;
    }


    /*********************************分享算力结束****************************************************/

    //释放用户积分
    public function sendTodayByUserintegral(){
        $memberList = Db::name('store_member')->where('integral','>',0)->select();
        Db::startTrans();
        if(!empty($memberList)){
            $res = [];
            foreach($memberList as $k=>$v){
                $integralRecord = $this->lookIntegralRecord($v['id']);
                if($integralRecord)  continue;
//                echo $v['id']."}";
//                echo $v['integral']."{";
                $res[] = $this->addIntegralRecord($v['id'],$v['integral']*0.002);
            }
            if(check_arr($res)){
                Db::commit();
                echo '释放积分成功';
            }else{
                Db::rollback();
                echo '释放积分失败';
            }
        }
    }

    //增加积分记录
    public function addIntegralRecord($uid,$money){
        $flagIntegral = Db::name('es_integral_bonus')->whereTime('addtime','d')->where(['uid'=>$uid])->find();
        if(!$flagIntegral){
            $arr = [
                'uid'=>$uid,
                'money'=>$money,
                'addtime'=>get_time()
            ];
            return Db::name('es_integral_bonus')->insertGetId($arr);
        }
        return true;
    }


    //查看方法积分记录
    public function lookIntegralRecord($uid){
        return  Db::name('es_integral_bonus')->where(['uid'=>$uid,'status'=>-1])->whereTime('addtime','d')->find();
    }

    public function getShareOrderList()
    {
        // TODO: Implement getShareOrderList() method.

    }


    public function getTeamOrderList()
    {
        // TODO: Implement getTeamOrderList() method.
    }

    public function getTeamCount()
    {
        // TODO: Implement getTeamCount() method.
    }

    //把年月日时分秒的时间格式，全部给拆开
    public function getAgreeTime( $create_at )
    {
        list($beforeTime, $endTime) = explode(' ', $create_at);
        list($year, $month, $day) = explode('-', $beforeTime);
        list($hour, $minute, $second) = explode(':', $endTime);
        //获取今日开始时间戳和结束时间戳
        $shiDay = mktime($hour, $minute, $second, $month, $day + 10, $year); //十天内
        $erShiDay = mktime($hour, $minute, $second, $month, $day + 20, $year); //二十天
        return [$shiDay, $erShiDay];
    }


    /*递归使用的获取用户信息*/
    public function getUser( $userid )
    {
        if (empty($userid)) return false;
        return User::get($userid);
    }

    /*设置交易订单*/
    public function setTranID( $value )
    {
        $this->tranID = $value;
        return $this;
    }

    /*设置交易订单类型*/
    public function setTranType( $value )
    {
        $this->tranType = $value;
        return $this;
    }


    /*设置用户id*/
    public function setUserID( $value )
    {
        $this->uid = $value;
        return $this;

    }


    public function __set( $name, $value )
    {
        $this->$name = $value;
        return true;
    }

    public function __get( $name )
    {
        return $this->$name;
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }
}