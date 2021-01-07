<?php

namespace app\apiv1\controller;

use service\DataService;
use service\WechatServicew;
use controller\BasicIndex;
use think\Facade\Cache;
use think\Db;

class Guazai extends BasicIndex
{
    protected $MiniApp;

    public function __construct()
    {
        parent::__construct();
        $this->MiniApp = new \Smallsha\Classes\MiniApp(config('miniapp.appid'), config('miniapp.app_secret'));
    }

    /*
  * 每天释放矿机（pos）
  * */
    public function ReleasePos(){
        $result = Db::name('financial_order')
            ->where(['status' =>0,'type'=>0])
            ->select();
        Db::startTrans();
        try {
            foreach($result as $key=>$value){
                //已结算的天数
                $settlement_days = $value['settlement_days']+1;
                //每天收益
                $day_shouyi = number_format($value['market_price']/$value['day'],4);
                $earned =   $day_shouyi+$value['earned'];
                $res[] = db::name('financial_order')->where(['id'=>$value['id']])->update(['settlement_days'=>$settlement_days,'earned'=>$earned]);
                $financial_order = db::name('financial_order')->where(['id'=>$value['id']])->find();
                if($financial_order['settlement_days'] >= $financial_order['day']){
                    //投资理财结束
                    $res[] = db::name('financial_order')->where(['id'=>$value['id']])->update(['status' => '1','profit_end'=>time()]);
                    //投资结束，把相应的算力去掉
                    $res_id1 = mlog($value['uid'], 'wallet_one',-$value['sl_num'], 'pos矿机' . $value['market_price'] . 'nac,结束扣除算力'.$value['sl_num'], 'shouyiUsdt', $value['id'], '1');
                    bagslanguage($res_id1['1'], $value['market_price'],$value['sl_num'],'','',10,11);
                }
                //添加记录
                $res_id2= mlog($value['uid'], 'account_score', $day_shouyi, 'pos矿机' . $value['market_price'] . 'nac每天释放nac' .$day_shouyi, 'shouyiUsdt', $value['id'], '1');
                bagslanguage($res_id2['1'], $value['market_price'],$day_shouyi,'','',10,12);
            }
            //奖金未领取沉淀或者退回
            $this->awardslPrecipitate();
            Db::commit();
            $this->success('执行成功');
        }catch(Exception $e){
            Log::error($e->getMessage()."||".$e->getLine());
            Db::rollback();
        }
    }
    /*
     * pos每日进行收益(总币量除以总算力，总算力指的是系统会员所有购买算力)
     * */
    public function posdayProfit(){
        //每个玩家的算力
        Db::startTrans();
        try {
            $wallet_one = db::name('store_member')->where(['is_disable'=>1])->sum('wallet_one');
            //直推算力
            $wallet_two = db::name('store_member')->where(['is_disable'=>1])->sum('wallet_two');
            $total_suanli = $wallet_one + $wallet_two +sysconf('pos_adjust_power');
            $avg_sl = sysconf('pos_total_currency')/$total_suanli;//平均算力
            $user = db::name('store_member')->where(['is_disable'=>1])->where('wallet_one','gt',0)->whereOr('wallet_two','gt','0')->select();
            foreach($user as $key=>$val){
                //pos算力兑换的nac币
                $financial_order = db::name('financial_order')->field('sl_num,id')->where(['uid'=>$val['id'],'status'=>'0','type'=>0])->select();
                //pos的算力，挖出来的nac币
                foreach($financial_order as $k=>$v){
                    $power1 = $avg_sl*($v['sl_num'])*(1-sysconf('absence_expenses')*0.01);

                    if($power1){
                        db::name('financial_order')->where(['id'=>$v['id'],'status'=>'0'])->setInc('nac_num',$power1);
                    }
                }
                $store_award = db::name('store_award')->field('sl_num,id')->where(['uid'=>$val['id'],'state'=>'1','type'=>0])->select();
                //推荐奖的算力，挖出来的nac币
                foreach($store_award as $kk=>$vv){

                    $power2 = $avg_sl*($vv['sl_num'])*(1-sysconf('absence_expenses')*0.01);

                    if($power2){
                        db::name('store_award')->where(['id'=>$vv['id'],'state'=>'1','type'=>0])->setInc('nac_num',$power2);
                    }
                }
                $power = $avg_sl*($val['wallet_one']+$val['wallet_two'])*(1-sysconf('absence_expenses')*0.01);
                $power =  number_format($power,4);
                if($power > 0){
                    $res_id =  mlog($val['id'], 'account_score', $power, 'pos算力'.$val['wallet_one']+$val['wallet_two'].'挖矿出，nac' .$power , 'wallet_three', $val['id'], '1','','',$val['wallet_one']+$val['wallet_two']);
                    bagslanguage($res_id['1'], $val['wallet_one']+$val['wallet_two'],$power,'','',13,14);
                    db::name('store_member')->where(['id'=>$val['id']])->setInc('wallet_three',$power);
                }
            }
            Db::commit();
            $this->success('执行成功');
        }catch(Exception $e){
            Log::error($e->getMessage()."||".$e->getLine());
            Db::rollback();
        }


    }





    /*
     * pot订单结束算力
     * */

    public function ReleasePot(){
        $result = Db::name('financial_order')
            ->where(['status' =>0,'type'=>1])
            ->select();
        Db::startTrans();
        try {
            foreach($result as $key=>$value){
                //已结算的天数
                $settlement_days = $value['settlement_days']+1;
                $res[] = db::name('financial_order')->where(['id'=>$value['id']])->update(['settlement_days'=>$settlement_days]);
                $financial_order = db::name('financial_order')->where(['id'=>$value['id']])->find();
                if($financial_order['settlement_days'] >= $financial_order['day']){
                    //投资理财结束
                    $res[] = db::name('financial_order')->where(['id'=>$value['id']])->update(['status' => '1','profit_end'=>time()]);
                    //投资结束，把相应的算力去掉
                    $res_id1 = mlog($value['uid'], 'wallet_four',-$value['sl_num'], 'poe矿机' . $value['market_price'] . '$结束，扣除算力'.$value['sl_num'], 'shouyiUsdt', $value['id'], '1');
                    bagslanguage($res_id1['1'], $value['market_price'],$value['sl_num'],'','',15,16);
                    $order = db::name('financial_order')->where(['id'=>$value['id'],'type'=>1,'buy_state'=>0])->find();
                    if($order['status'] == 1){
                        //pot矿机，15天之后自动会生成一个卖单，质押币返回，每日收益领取
                        $this->potBecomeorder($order['uid'],$order['email'],$order['market_price']);
                        //锁定质押币
                        $nac_num = $order['market_price'] * sysconf('zyb_rate')*0.01/sysconf('lxc_dollar');
                        $res_id2 = mlog($order['uid'], 'account_score', $nac_num, "poe矿机{$order['market_price']}usdt，返回nac{$nac_num}", 'account_score','','4',$order['uid']);
                        bagslanguage($res_id2['1'],$order['market_price'],$nac_num,'','',15,17);
                        $pot_daysy_num =  db::name('pot_daysy')->where(['state'=>0])->sum('num');
                        $pot_daysy_sl_num =  db::name('pot_daysy')->where(['state'=>0])->sum('sl_num');
                        $res = db::name('pot_daysy')->where(['state'=>0])->setField(['state'=>1,'end_time'=>time()]);
                        if($res){
                            $res_id3 = mlog($order['uid'], 'account_score', $pot_daysy_num, "poe算力挖矿nac{$pot_daysy_num}", 'wallet_six','','4',$order['id'],'',$pot_daysy_sl_num);
                            bagslanguage($res_id3['1'],$pot_daysy_num,'','','',18);
                        }
                    }
                }
            }
            //奖金未领取沉淀或者退回
            $this->awardslPrecipitate();
            Db::commit();
            $this->success('执行成功');
        }catch(Exception $e){
            Log::error($e->getMessage()."||".$e->getLine());
            Db::rollback();
        }



    }
    /*
   * pot矿机，15天之后自动会生成一个卖单，质押币返回，每日收益领取
   * */
    public function potBecomeorder($uid,$uname,$num){
        $data = array(
            'uid'=>$uid,
            'ordersn'=>'S'.substr(time(), -9),
            'uname'=>$uname,
            'totalnum'=>$num,
            'leavenum'=>$num,
//            'pdnum'=>$pdnum,
            'state'=>1,//1待匹配2完成//4预匹配
            'addtime'=>time()
        );
        $res = db::name('ty_selllist')->insert($data);
    }

    /*
   * pot每日进行收益(总币量除以总算力，总算力指的是系统会员所有购买算力)
   * */
    public function potsdayProfit(){
        Db::startTrans();
        try {
            //每个玩家的算力
            $wallet_four = db::name('store_member')->where(['is_disable'=>1])->sum('wallet_four');
            //直推算力
            $wallet_five = db::name('store_member')->where(['is_disable'=>1])->sum('wallet_five');
            $total_suanli = $wallet_four + $wallet_five +sysconf('pot_adjust_power');
            $avg_sl = sysconf('pot_total_currency')/$total_suanli;//平均算力
            $user = db::name('store_member')->where(['is_disable'=>1])->where('wallet_four','gt',0)->select();
            foreach($user as $key=>$val){
                //pot算力兑换的nac币
                $power = $avg_sl*($val['wallet_four']+$val['wallet_five'])*(1-sysconf('pot_absence_expenses')*0.01);
                $power =  number_format($power,4);
                if($power > 0){
                    $content = $val['wallet_four']+$val['wallet_five'].'pot算力挖矿' .$power . 'nac币';
                    $sl_num = $val['wallet_four']+$val['wallet_five'];
                    $this->pot_daysy($val['id'],$power,$content,$sl_num);
                }
            }
            Db::commit();
            $this->success('执行成功');
        }catch(Exception $e){
            Log::error($e->getMessage()."||".$e->getLine());
            Db::rollback();
        }
    }

    //每天释放的是pot
    public function pot_daysy($uid, $num, $content,$sl_num)
    {
        $uname = db::name('store_member')->where(['id'=>$uid])->value('email');
        $res[] = Db::name('pot_daysy')->insert([
            'uid' => $uid,
            'uname' => $uname,
            'num' => $num,
            'content'=>$content,
            'addtime' => time(),
            'sl_num'=>$sl_num
        ]);
    }

    /*
    * 奖金算力（如果超过时间不领取的话，会自动沉淀）
    * */
    public function awardslPrecipitate(){
        //查看自己未领取的推荐算力
        $financial_order = db::name('financial_order')->field('id')->where(['status'=>1,'award_sate'=>0])->select();
        foreach($financial_order as $k=>$val){
            Db::name('store_award')->where(['state' =>0,'posid'=>$val['id']])->update(['state'=>2,'endtime'=>time()]);
            //矿机结束之后，扣除相应的直推算力
            $store_award = Db::name('store_award')->field('sl_num,uid,type')->where(['state' =>1,'posid'=>$val['id']])->find();
            if($store_award){
                if($store_award['type'] == 0){
                    $type = "wallet_two";
                    $content = 'pos矿机结束，直推算力扣除'. $store_award['sl_num'];
                }else{
                    $type = "wallet_five";
                    $content = 'poe矿机结束，见点算力扣除'. $store_award['sl_num'];
                }
                $res_id = mlog($store_award['uid'], $type, -$store_award['sl_num'], $content , 'shouyiUsdt', '', '1');
                if($store_award['type'] == 0){
                    bagslanguage($res_id['1'],$store_award['sl_num'],'','','',19);
                }else{
                    bagslanguage($res_id['1'],$store_award['sl_num'],'','','',20);
                }

            }
            db::name('financial_order')->where(['status'=>1,'award_sate'=>0,'id'=>$val['id']])->update(['award_sate'=>1]);
        }
    }


    /*
     * pot超时未打款
     * */
    public function djUser(){
        //24小时不打款冻结
        $limit1 = sysconf('payhours') * 3600;
        $list1 =db::name('ty_match')->where(array('state'=>0))->select();
        foreach($list1 as $k=>$v){
            if(time() - $v['addtime'] > $limit1){
                //冻结人
                db::name('store_member')->where(array('id'=>$v['b_uid']))->setField('is_disable',-1);
                $updata = array(
                    'state'=>4,//4未打款冻结
                    'addtime4'=>time()
                );
               db::name('ty_match')->where(array('id'=>$v['id']))->setField($updata);
               //变成疑问单
               db::name('ty_buylist')->where(array('id'=>$v['b_id']))->setField('state',3);
            }
        }
        //24小时不受款冻结
        $limit2 = 3600 * sysconf('collectionhours');
        $list2 = db::name('ty_match')->where(array('state'=>1))->select();
        var_dump( sysconf('collectionhours'));
        foreach($list2 as $k=>$v){
            if(time() - $v['addtime1'] > $limit2){

                //冻结人
                db::name('store_member')->where(array('id'=>$v['s_uid']))->setField('is_disable',-1);
                $updata = array(
                    'state'=>5,//5未确认收款
                    'addtime5'=>time()
                );
                db::name('ty_match')->where(array('id'=>$v['id']))->setField($updata);
            }
        }
    }

    /*
    * 买卖进行匹配
    * */
    public function match(){
        $buyinfo  = db::name('jys_buylist')->where('leavenum','gt','0')->order('id asc')->select();
        Db::startTrans();
        try {
            foreach($buyinfo as $k=>$v){
                    //只有价格相等的话，才会进行匹配
                $sellinfo = db::name('jys_selllist')->where('leavenum','gt','0')->where('price',$v['price'])->order('id asc')->find();

                    if($v['price'] == $sellinfo['price'] && $v['uid'] != $sellinfo['uid']){
                        $num = min($v['leavenum'],$sellinfo['leavenum']);
                        //买单
                        db::name('jys_buylist')->where(['id'=>$v['id']])->setDec('leavenum',$num);
                        $res_id1 = mlog($v['uid'], 'account_score', $num, "交易所买单{$v['ordersn']}匹配卖单{$sellinfo['ordersn']},单价{$v['price']}，增加nac{$num}", 'buylist','','4',$sellinfo['id']);
                        bagslanguage($res_id1['1'],$v['ordersn'],$sellinfo['ordersn'],$v['price'],$num,21,22,23,24);
                        if($v['leavenum'] == $num){
                            //全部挂买数量完成
                            db::name('jys_buylist')->where(['id'=>$v['id']])->update(['state'=>1,'endtime'=>time()]);
                        }
                        //卖单
                        db::name('jys_selllist')->where(['id'=>$sellinfo['id']])->setDec('leavenum',$num);
                        //卖出应获得usdt个数
                        $money = $num*$v['price'];
                        $res_id2 =mlog($sellinfo['uid'], 'account_money', $money, "交易所卖单{$sellinfo['ordersn']}匹配买单{$v['ordersn']},单价{$v['price']}，增加usdt{$money}", 'buylist','','4',$v['id']);
                        bagslanguage($res_id2['1'],$sellinfo['ordersn'],$v['ordersn'],$v['price'],$money,25,26,23,27);
                        if($sellinfo['leavenum'] == $num){
                            //全部挂买数量完成
                            db::name('jys_selllist')->where(['id'=>$sellinfo['id']])->update(['state'=>1,'endtime'=>time()]);
                        }
                    }
            }
            Db::commit();
        }catch(Exception $e){
            Log::error($e->getMessage()."||".$e->getLine());
            Db::rollback();
        }

        $this->success("执行成功");

    }
    /*
     * 收集交易所的价格和数量
     * */
    public function jysPrice(){
        $buyinfo = db::name('jys_buylist')
            ->where('state','neq','2')
            ->where('is_tongji',0)
            ->order('id asc')->select();
        foreach($buyinfo as $k=>$v){
            $num = db::name('jys_buylist')->where(['price'=>$v['price']])->sum('leavenum');
            $res['orderid'] = Db::name('jys_price')->insertGetId([
                'type'=>0,
                'price'=>$v['price'],
                'num'=>$num,
                'addtime'=>time()
            ]);
            db::name('jys_buylist')->where(['id'=>$v['id']])->setField('is_tongji',1);
        }
        $sellinfo = db::name('jys_selllist')
            ->where('state','neq','2')
            ->where('is_tongji',0)
            ->order('id asc')->select();
        foreach($sellinfo as $k=>$v){
            $num = db::name('jys_selllist')->where(['price'=>$v['price']])->sum('leavenum');
            $res['orderid'] = Db::name('jys_price')->insertGetId([
                'type'=>1,
                'price'=>$v['price'],
                'num'=>$num,
                'addtime'=>time()
            ]);
            db::name('jys_selllist')->where(['id'=>$v['id']])->setField('is_tongji',1);
        }

        $this->success("执行成功");

    }

}