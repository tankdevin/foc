<?php

namespace app\apiv1\controller;

use service\DataService;
use think\Error;
use think\Validate;
use think\Db;
use think\facade\Cache;
use controller\BasicIndex;

/**
 * 应用入口控制器
 * @author Anyon <zoujingli@qq.com>
 */
class Task extends BasicIndex
{
    /*
     * 矿池结算
     * */
    public function machine()
    {
        set_time_limit(0);
        if(date('H') == 23){
        
            $recode = Db::name('store_machine')->where(['status'=>1])->select();
            $machine = Db::name('machine')->column('total,income,start_time,end_time','id');
            $time = time();
            $today_s = date('Y-m-d').' 00:00:00';
            $today_e = date('Y-m-d').' 23:59:59';
            $coins = sysconf('amount_of_coins');
            foreach ($recode as $key=>$val){
                $res = [];
                if($val['kid'] == 4){
                    $gain_i = 8*sysconf('mlin_foc_num');
                }elseif($val['kid'] == 3){
                    $gain_i = 4*sysconf('mlin_foc_num');
                }elseif($val['kid'] == 2){
                    $gain_i = 2*sysconf('mlin_foc_num');
                }else{
                    $gain_i = sysconf('mlin_foc_num');
                }
//                 $gain_i = bcdiv(bcdiv($coins,2),$machine[$val['kid']]['total'],2);
                if($time>=strtotime($val['start_time']) && $time<=strtotime($val['end_time'])){
                    $baglog = Db::name('bags_log')->where(['uid' => $val['mid'], 'status' => 9, 'extends'=>'account_score_income','orderId'=>$val['id']])->whereBetween('create_time', strtotime($today_s).",".strtotime($today_e))->find();
                    if ($baglog) continue;
                    Db::startTrans();
                    try {
                        $gain = $gain_i*$val['num'];
                        $res[] = mlog($val['mid'],'account_score',$gain,"矿池收益",'account_score_income',$val['id'],9);
                        $res[] = Db::name('store_machine')->where('id',$val['id'])->setInc('income',$gain);
                        //处理动态奖
                        $merchatInfo = Db::name('store_member')->where('id',$val['mid'])->find();
                        $first_leader = Db::name('store_member')->where(['id' =>$merchatInfo['first_leader']])->value('level');
                        if($first_leader == 2){
                            $res[] = mlog($merchatInfo['first_leader'], 'account_score', 0.4*$gain, "直推动态奖",'tuijian','',5,$val['mid']);
                        }elseif($first_leader == 1 || $first_leader == 5  || $first_leader == 6){
                            $leader_mach = Db::name('store_machine')->where('mid',$merchatInfo['first_leader'])->find();
                            $all_leaders = array_reverse(explode(',', $merchatInfo['all_leader']));//反转数组
                            if(!empty($leader_mach)){
                                $res[] = mlog($merchatInfo['first_leader'], 'account_score', 0.2*$gain, "直推动态奖",'tuijian','',5,$val['mid']);
                            }
                            foreach ($all_leaders as $v)
                            {
                                $leaders = Db::name('store_member')->where(['id' =>$v])->value('level');
                                if($leaders == 2){
                                    $res[] = mlog($v, 'account_score', 0.2*$gain, "间推动态奖",'tuijian','',5,$val['mid']);
                                    break;
                                }
                            }
                        }
                        Db::commit();
                        echo '成功';
                    }catch(\Exception $e){
                        Db::rollback();
                    }
                }
            }
         }else{
             echo '时间不到';
         }
    }
    
    /*
     * 矿池结束
     * */
    public function machine_end()
    {
        set_time_limit(0);
         if(date('H') == 23){
        
            $recode = Db::name('store_machine')->where(['status'=>1])->select();
            $time = time();
            foreach ($recode as $key=>$val){
                if($time>strtotime($val['end_time'])){
                    $res = Db::name('store_machine')->where(['id' =>$val['id'],'status'=>1])->update(['update_at'=>date('Y-m-d H:i:s'),'status'=>2]);
                    if ($res) {
                        echo '成功';
                    } else {
                        echo 1;
                    }
                }
            }
         }else{
             echo '时间不到';
         }
    }
    
    /*
     * 释放
     * */
    public function release()
    {
        set_time_limit(0);
        if(date('H') == 1){
            
            $recode = Db::name('store_machine')->where(['status'=>2])->select();
            foreach ($recode as $key=>$val){
                $res = [];
                Db::startTrans();
                if(time() > strtotime('+ 1 day',strtotime($val['end_time']))){
                    $res[] = Db::name('store_machine')->where(['id' =>$val['id'],'status'=>2])->update(['update_at'=>date('Y-m-d H:i:s'),'status'=>3]);
                    if($val['num'] > $val['syn_num']){
                        $num = ($val['num']-$val['syn_num'])/2;
                        $res[] = Db::name('machine')->where('id',$val['kid']+1)->setInc('stock',$num);
                        $usdt = $val['usdt']/$val['num']*($val['num']-$val['syn_num']);
                        $foc = $val['foc']/$val['num']*($val['num']-$val['syn_num']);
                        if($usdt>0){
                            $res[] = mlog($val['mid'], 'account_money', $usdt, "矿机本金返还",'kuangji','',7,$val['kid']);
                        }
                        if($foc>0){
                            $res[] = mlog($val['mid'], 'account_foc', $foc, "矿机本金返还",'kuangji','',7,$val['kid']);
                        }
                    }
                }
                if ($this->check_arr($res)) {
                    Db::commit();
                    echo '成功';
                } else {
                    Db::rollback();
                    echo 1;
                }
            }
        }else{
            echo '时间不到';
        }
    }
    
    /*
     * 超时订单释放24H
     * */
    public function order_release()
    {
        set_time_limit(0);
        $order_buy = Db::name('jys_buylist')
        ->where('state', 0)
        ->select();
        foreach ($order_buy as $vl){
            if($vl['uid'] == 361){
                $checktime = $vl['addtime']+24*60*60;
            }else{
                $checktime = $vl['addtime']+1*60*60;
            }
            if(time() < $checktime) continue;
            Db::startTrans();
            //撤回的是usdt数量
            $rate_usdt = $vl['leavenum']* $vl['price']*sysconf('jys_rate')*0.01;
            $usdt =  $vl['leavenum']* $vl['price'];
            $res_id2 = $res[] = mlog($vl['uid'], 'account_money', $usdt, '取消买单'.$vl['ordersn'].'实际退回usdt'.$usdt, 'cancel_C2c_order', $vl['id'],'13');
            $res[] = Db::name('jys_buylist')->where(['id' => $vl['id'],'state'=>0])->update(['state' => 2,'endtime'=>time()]);
            if (check_arr($res)) {
                Db::commit();
                echo '成功';
            } else {
                Db::rollback();
                echo 2;
            }
        }
        $order_sell = Db::name('jys_selllist')
        ->where('state', 0)
        ->select();
        foreach ($order_sell as $val){
            $checktime = $val['addtime']+4*60*60;
            if(time() < $checktime) continue;
            Db::startTrans();
            //撤销返回的是nac
            $rate_nac = $val['leavenum']*sysconf('jys_rate')*0.01;
            $nac = $val['leavenum']+$rate_nac;
            if($val['name'] == 'FOC'){
                $moneyname = 'account_foc';
                $res_id1 = $res2[] = mlog($val['uid'], $moneyname,$nac, '取消卖单'.$val['ordersn'].'手续费' . $rate_nac."实际退回".$nac, 'cancel_C2c_order', $val['id'],'13');
            }else{
                $res2[] = Db::name('store_member')->where(['id' => $val['uid']])->setInc('gz_foc',$val['leavenum']/2);
                $moneyname = 'account_score';
                $foc_tui = ($val['leavenum']/2)+$rate_nac;
                $res_id1 = $res[] = mlog($val['uid'], 'account_foc',$foc_tui, '取消卖单'.$val['ordersn'].'手续费' . $rate_nac."实际退回".$foc_tui, 'cancel_C2c_order', $val['id'],'13');
                $res_id1 = $res[] = mlog($val['uid'], $moneyname,$val['leavenum']/2, '取消卖单'.$val['ordersn']."实际退回".($val['leavenum']/2), 'cancel_C2c_order', $val['id'],'13');
            }
            //bagslanguage($res_id1['1'],$orderInfo['ordersn'],$rate_nac,$nac,'',28,29,30);
            $res2[] = Db::name('jys_selllist')->where(['id' => $val['id'],'state'=>0])->update(['state' => 2,'endtime'=>time()]);
            if (check_arr($res2)) {
                Db::commit();
                echo '成功';
            } else {
                Db::rollback();
                echo 3;
            }
        }
    }
    
    /*
     * 买卖进行匹配
     * */
    public function match(){
        $buyinfo  = db::name('jys_buylist')->where('leavenum','gt','0')->where('state',0)->order(['price'=>'asc','addtime'=>'asc'])->select();
        $hour = sysconf('gz_delete_time');
        foreach($buyinfo as $k=>$v){
            $res = [];//->whereIn('uid', [361,367,394])
            $sellinfo = db::name('jys_selllist')->where('leavenum','gt','0')->where('uid','<>',$v['uid'])->where('price','<=',$v['price'])->where('state',0)->order(['price'=>'asc','addtime'=>'asc'])->find();
            if($v['price'] >= $sellinfo['price'] && $v['uid'] != $sellinfo['uid']){
                    $moneyname = 'account_foc';
                    $num = min($v['leavenum'],$sellinfo['leavenum']);
                Db::startTrans();
                    //买单
                    $res[] = db::name('jys_buylist')->where(['id'=>$v['id']])->setDec('leavenum',$num);
                    //共振额度
                    $gz = Db::name('store_member')->field('id,gz_time,gz_foc')->where(['id' => $v['uid']])->find();
                    if(time()>$gz['gz_time']){
                        $res[] = Db::name('store_member')->where(['id' => $v['uid']])->update(array('gz_time'=>strtotime('+ '.$hour.' hour'),'gz_foc'=>$num));
                    }else{
                        $res[] = Db::name('store_member')->where(['id' => $v['uid']])->update(array('gz_time'=>strtotime('+ '.$hour.' hour'),'gz_foc'=>$num+$gz['gz_foc']));
                    }
                    $ra_price = $num*sysconf('jys_rate')*0.01;
                    $dz_foc = $num-$ra_price;
                    $res[] = mlog($v['uid'], $moneyname, $dz_foc, "交易所买单{$v['ordersn']}匹配卖单{$sellinfo['ordersn']},单价{$sellinfo['price']}数量{$num}，扣手续费{$ra_price}{$v['name']}增加{$v['name']}{$dz_foc}", 'buylist','','11',$sellinfo['id']);
                    if($v['price']>$sellinfo['price']){
                        $tui = $num*($v['price']-$sellinfo['price']);
                        $res[] = mlog($v['uid'], 'account_money', $tui, "交易所买单{$v['ordersn']}匹配卖单{$sellinfo['ordersn']},单价{$sellinfo['price']}数量{$num}，退回差价{$tui}", 'buylist','','11',$sellinfo['id']);
                    }
                    //bagslanguage($res_id1['1'],$v['ordersn'],$sellinfo['ordersn'],$v['price'],$num,21,22,23,24);
                    if($v['leavenum'] == $num){
                        //全部挂买数量完成
                        $res[] = db::name('jys_buylist')->where(['id'=>$v['id']])->update(['state'=>1,'endtime'=>time()]);
                    }
                    //卖单
                    $res[] = db::name('jys_selllist')->where(['id'=>$sellinfo['id']])->setDec('leavenum',$num);
                    //卖出应获得usdt个数
                    $money = $num*$sellinfo['price'];
                    $res[] = mlog($sellinfo['uid'], 'account_money', $money, "交易所卖单{$sellinfo['ordersn']}匹配买单{$v['ordersn']},单价{$sellinfo['price']}数量{$num}，增加usdt{$money}", 'buylist','','11',$v['id']);
                    //bagslanguage($res_id2['1'],$sellinfo['ordersn'],$v['ordersn'],$v['price'],$money,25,26,23,27);
                    if($sellinfo['leavenum'] == $num){
                        //全部挂买数量完成
                        $res[] = db::name('jys_selllist')->where(['id'=>$sellinfo['id']])->update(['state'=>1,'endtime'=>time()]);
                    }
                    if ($this->check_arr($res)) {
                        Db::commit(); //$v['price']
                        if(!empty($num) && !empty($v['price'])){
                            $this->batchWriteMarketData(1, 4, $num, $sellinfo['price']);
                        }
                    } else {
                        Db::rollback();
                    }
            }
        }
        
        return '执行成功';
        
    }
    
    /*
     * 测试
     * */
    public function match1(){
        $hour = 240;
        var_dump(strtotime('+ '.$hour.' hour'));
//         $this->batchWriteMarketData(1, 4, $num, $sellinfo['price']);
//         return '执行成功';
    }
    
    private function batchWriteMarketData($currency_id, $legal_id, $num, $price, $sign = 0, $time = null, $cumulation = true)
    {
        //$type类型:1.15分钟,2.1小时,3.4小时,4.一天,5.分时,6.5分钟，7.30分钟,8.一周,9.一月,10.一年
        file_put_contents("log.txt", '数量'.$num.'价格'.$price. PHP_EOL, FILE_APPEND);
        empty($time) && $time = time();
        $types = [5, 1, 6, 7, 2, 4, 8, 9];
        $start = microtime(true);
        
        //写入行情数据
        
        foreach ($types as $value) {
            $data = [];
            $new_data = [];
            $times = self::formatTimeline($value, $time);
            $market_hour = db::name('market_hour')->where(['currency_id'=>$currency_id,'day_time'=>$times,'period'=>period($value)])->find();
            
            $last_time = db::name('market_hour')->where('type', $value)
            ->where('day_time', '<>' , $times)
            ->where('currency_id', $currency_id)
            ->where('legal_id', $legal_id)
            ->order('day_time','desc')
            ->find();
            if(!empty($last_time)){
                if(bccomp($last_time->start_price, $last_time->end_price)<0){
                    $data['start_price'] = $last_time['end_price'];
                }else{
                    $data['start_price'] = $last_time['end_price'];
                }
            }else{
                $data['start_price'] = $price;
            }
            $new_start_price = $data['start_price'];
            
            //bc_comp($timeline->start_price, 0) <= 0 && $data['start_price'] = $price;
            $data['end_price'] = $price;
            
            //如果是存在的话更新某个字段
            if(!empty($market_hour)){
                
                if($market_hour['highest']< $price){
                    $data['highest'] = $price;
                }
                if ($market_hour['mminimum']>$price) {
                    $data['mminimum'] = $price;
                }
                //最高价和最低价
                if(isset($data['highest']) &&  $market_hour['highest'] < $data['highest']){
                    $new_highest = $data['highest'];
                }else{
                    $new_highest = $market_hour['highest'];
                }
                if(isset($data['mminimum']) && $market_hour['mminimum'] > $data['mminimum']){
                    $new_mminimum = $price;
                }else{
                    $new_mminimum = $market_hour['mminimum'];
                }
                $data['end_price'] = $price;
                unset($data['start_price']);
                db::name('market_hour')->where(['id'=>$market_hour['id']])->update($data);
                //累加交易量
                db::name('market_hour')->where(['id'=>$market_hour['id']])->setInc('number',$num);
                //封装新数据
                $new_data['currency_id'] = $currency_id;
                $new_data['legal_id'] = $legal_id;
                $new_data['start_price'] = $new_start_price;
                $new_data['end_price'] =  $data['end_price'];
                $new_data['mminimum'] = $new_mminimum;
                $new_data['highest'] = $new_highest;
                $new_data['day_time'] = $times;
                $new_data['type'] = $market_hour['type'];
                $new_data['number'] = $market_hour['number']+$num;
                $new_data['period'] = $market_hour['period'];
            }else{
                //如果是不存在的话，创建
                $data['currency_id'] = $currency_id;
                $data['legal_id'] = $legal_id;
                $data['start_price'] = $price;
                $data['end_price'] = $price;
                $data['mminimum'] = $price;
                $data['highest'] = $price;
                $data['day_time'] = $times;
                $data['type'] = $value;
                $data['number'] = $num;//交易数量
                $data['period'] =period($value);
                Db::name('market_hour')->insert($data);
                //封装新数据
                $new_data['currency_id'] = $currency_id;
                $new_data['legal_id'] = $legal_id;
                $new_data['start_price'] = $price;
                $new_data['end_price'] =  $price;
                $new_data['mminimum'] = $price;
                $new_data['highest'] = $price;
                $new_data['day_time'] = $times;
                $new_data['type'] = $value;
                $new_data['number'] = $num;
                $new_data['period'] = period($value);
            }
            
            if($value == 5){
                //行情
                //该交易对当天0点的交易行情。
                $time_five = strtotime(date("Y-m-d 00:00:00")); //获取今天0点的时间戳
                $day_Data = DB::table('market_hour')->where('currency_id', 1)
                ->where('legal_id', 4)
                ->where('period', '1day')
                ->where('day_time', '<', $time_five)
                ->where('end_price', '>', '0.00000')
                ->order('id', 'DESC')
                ->find();
                //当天0点的成交价
                if (!empty($day_Data)) {
                    $_zero_price = $day_Data['end_price'];
                } else {
                    $day_Data = DB::table('market_hour')->where('currency_id', 1)
                    ->where('legal_id', 4)
                    ->where('period', '1min')
                    ->find();
                    if (!empty($day_Data)) {
                        $_zero_price = $day_Data['start_price'];
                    }else{
                        $_zero_price = 0;
                    }
                }
                $coinnum = DB::table('market_hour')->where('day_time', '>', $time_five)
                ->where('currency_id', 1)
                ->where('legal_id',4)
                ->where('period', '1min')
                ->sum('number');
                $coin["price"] = bcsub($new_data["end_price"],0,4);
                if($_zero_price == 0){
                    $coin["change"] = "+0.000";
                }else{
                    $coin["change"] = bcmul(bcdiv(bcsub($data["end_price"],$_zero_price,2),$_zero_price,6),100,4);
                }
                $foc = Db::name('system_coin')->where('id',1)->find();
                $day_to = DB::table('market_hour')->where('currency_id', 1)
                ->where('legal_id', 4)
                ->where('period', '1day')
                ->order('id', 'DESC')
                ->find();
                if(!empty($foc)){
                    //最高价和最低价
                    if($new_data['highest'] < $foc['high']){
                        $new_high = $foc['high'];
                    }else{
                        $new_high = $new_data['highest'];
                    }
                    if($new_data['mminimum'] > $foc['low']){
                        $new_mmin = $foc['low'];
                    }else{
                        $new_mmin = $new_data['mminimum'];
                    }
                    $coin["high"] = bcsub($new_high,0,4);
                    $coin["low"] = bcsub($new_mmin,0,4);
                }
                $coin["amount"] = $coinnum;
                Db::name('system_coin')->where('id',1)->update($coin);
                $new_data1['add_time'] = time();
                $new_data1['change'] = $coin["change"]>0?'+'.$coin["change"]:$coin["change"];
                $new_data1['currency_id'] = 1;
                $new_data1['currency_name'] = 'FOC';
                $new_data1['high'] = $coin["high"];
                $new_data1['legal_id'] = 4;
                $new_data1['legal_name'] = 'USDT';
                $new_data1['low'] = $coin["low"];
                $new_data1['now_price'] = $coin["price"];
                $new_data1['symbol'] = 'FOC/USDT';
                $new_data1['type'] = 'daymarket';
                $new_data1['volume'] = $coin["amount"];
                $this->writeMarketData('daymarket',$new_data1);
            }
            //进行推送
            //$tuisong = new Tuisong();
            $new_data1 = [
                'type' => 'kline',
                'period' => $new_data['period'],
                'currency_id' => $new_data['currency_id'],
                'currency_name' => "FOC",
                'legal_name' => "USDT",
                'symbol' => "FOC/USDT",
                'open' => $new_data['start_price'],
                'close' => $new_data['end_price'],
                'high' => $new_data['highest'],
                'low' => $new_data['mminimum'],
                'volume' => $new_data['number'],
                'time' => $new_data['day_time'] * 1000,
            ];
            //halt($new_data1);
            //$this->writeMarketData($new_data['period'],$new_data1);
            $this->writeMarketData('kline',$new_data1);
            
        }
    }
    
    /**
     * 拼装交易数据
     *
     * @param Sting $type [kline]
     * @param mixed $data
     * @return bool|string
     */
    function writeMarketData($type, $data)
    {
        // do some thing
        $pushUrl = "http://127.0.0.1:5678";
        $postData = [
            'event' => $type,
            'msg' => is_array($data) ? json_encode($data) : $data
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $pushUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:"));
        $return = curl_exec($ch);
        curl_close($ch);
        
        return $return;
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
            $num = db::name('jys_buylist')->where(['price'=>$v['price']])->where('name',$v['name'])->sum('leavenum');
            $status = 1;
            $res['orderid'] = Db::name('jys_price')->insertGetId([
                'type'=>0,
                'price'=>$v['price'],
                'num'=>$num,
                'status'=>$status,
                'addtime'=>time()
            ]);
            db::name('jys_buylist')->where(['id'=>$v['id']])->setField('is_tongji',1);
        }
        $sellinfo = db::name('jys_selllist')
        ->where('state','neq','2')
        ->where('is_tongji',0)
        ->order('id asc')->select();
        foreach($sellinfo as $k=>$v){
            $num = db::name('jys_selllist')->where(['price'=>$v['price']])->where('name',$v['name'])->sum('leavenum');
            $status = 1;
            $res['orderid'] = Db::name('jys_price')->insertGetId([
                'type'=>1,
                'price'=>$v['price'],
                'num'=>$num,
                'status'=>$status,
                'addtime'=>time()
            ]);
            db::name('jys_selllist')->where(['id'=>$v['id']])->setField('is_tongji',1);
        }
        $this->success("执行成功");
    }
    
    /**
     * 会员和动态股东每日持币
     */
    public function dynamicData(){
        set_time_limit(0);
        if(date('H') == 23 && sysconf('amount_of_coins') == 50000){
            $member = Db::name('store_member')->field('id,account_score,account_foc')->where('level',1)->whereOr('level',5)->select();
            foreach ($member as $vl){
                $res = [];
                $one = Db::name('store_dynamic')->where(['mid' => $vl['id']])->where('created_at','like',date('Y-m-d').'%')->find();
                Db::startTrans();
                if (!empty($one)){
                    $update['money'] = $vl['account_score']+$vl['account_foc'];
                    $res[] = Db::name('store_dynamic')->where('id',$one['id'])->update($update);
                }else{
                    $insert = array();
                    $insert['mid'] = $vl['id'];
                    $insert['money'] = $vl['account_score']+$vl['account_foc'];
                    $insert['created_at'] = date('Y-m-d H:i:s');
                    $res[] = Db::name('store_dynamic')->insert($insert);
                }
                if ($this->check_arr($res)) {
                    Db::commit();
                    echo '成功';
                } else {
                    Db::rollback();
                    echo 1;
                }
            }
        }else{
            echo '时间不到';
        }
    }
    
    /**
     * 动态股东考核分红
     */
    public function dynamic(){
        set_time_limit(0);
        if(date('H') == 1){
            $data = Db::name('store_dynamic')->field("sum(money) as money,DATE_FORMAT(created_at,'%Y-%m-%d') as date")->where(['status' => 0])->group('date')->select();
            if(count($data)==15){
                
                $is_lx = 1; //默认数组是连续的
                foreach($data as $key=>$val){
                    if($key>0){ //从第二条开始记录判断bai
                        if(strtotime($data[$key-1]['date'])+86400 != strtotime($val)){ //如果前一个du日期加一天不zhi等于当前日期
                            $is_lx = 0; //改变连续状态
                        }
                    }
                }
                if($is_lx){
                    $mid_y = Db::name('store_dynamic')->where('money','>=',200)->where('status', 0)->column('mid');
                    $mid_y = array_unique($mid_y);
                    $mid_n = Db::name('store_dynamic')->where('money','<',200)->where('status', 0)->column('mid');
                    $mid_n = array_unique($mid_n);
                    $midarr = array_diff($mid_y,$mid_n);
                    Db::startTrans();
                    Db::name('store_member')->where('level',5)->update(array('level'=>1));
                    $dynamic = Db::name('store_dynamic')->field("AVG(money)+MIN(money) as t_money,mid")->where(['status' => 0])->where('mid','IN',$midarr)->order('t_money','desc')->group('mid')->limit(100)->select();
                    $res[] = Db::name('store_dynamic')->where('status',0)->update(array('status'=>1));
                    foreach ($dynamic as $vl){
                        $score = 1875*7;
                        $res[] = mlog($vl['mid'], 'account_score', $score, "动态股东分红",'gudongfenhong','',14,'');
                        $res[] = Db::name('store_member')->where('level',1)->where('id',$vl['mid'])->update(array('level'=>5));
                    }
                    if ($this->check_arr($res)) {
                        Db::commit();
                        echo '成功';
                    } else {
                        Db::rollback();
                        echo 1;
                    }
                }
            }
        }else{
            echo '时间不到';
        }
    }
    
    /**
     * 会员每日持币
     */
    public function dayData(){
        set_time_limit(0);
        $member = Db::name('store_member')->field('id,account_score,account_foc')->select();
        foreach ($member as $vl){
            $res = [];
            $one = Db::name('store_daymoney')->where(['mid' => $vl['id']])->where('created_at','like',date('Y-m-d').'%')->find();
            Db::startTrans();
            if (!empty($one)){
                $update['money'] = $vl['account_score']+$vl['account_foc'];
                $res[] = Db::name('store_daymoney')->where('id',$one['id'])->update($update);
            }else{
                $insert = array();
                $insert['mid'] = $vl['id'];
                $insert['money'] = $vl['account_score']+$vl['account_foc'];
                $insert['created_at'] = date('Y-m-d H:i:s');
                $res[] = Db::name('store_daymoney')->insert($insert);
            }
            if ($this->check_arr($res)) {
                Db::commit();
                echo '成功';
            } else {
                Db::rollback();
                echo 1;
            }
        }
    }
    
    /**
     * 持币生息
     */
    public function interest(){
        set_time_limit(0);
        if(date('H') == 10){
            $member = Db::name('store_member')->field('id,(account_score+account_foc) as money')->whereNotIn('level',[3,4])->select();
//             $total = Db::name('store_member')->field('sum(account_score)+sum(account_foc) as money')->where('(account_score+account_foc)>200')->find();
            $time = time();
            $today_s = date('Y-m-d').' 00:00:00';
            $today_e = date('Y-m-d').' 23:59:59';
            $coins = sysconf('amount_of_coins');
            $res[] = 1;
            Db::startTrans();
            foreach ($member as $vl){
                $today_data = Db::name('store_daymoney')->where('mid',$vl['id'])->where('created_at','like',date('Y-m-d').'%')->find();
                $yestoday_data = Db::name('store_daymoney')->where('mid',$vl['id'])->where('created_at','like',date('Y-m-d',strtotime('-1 day')).'%')->find();
                if(empty($today_data) || empty($yestoday_data)) continue;
                if($today_data['money']<200 && $yestoday_data['money']<200) continue;
                $baglog = Db::name('bags_log')->where(['uid' => $vl['id'], 'status' => 15, 'extends'=>'account_score_interest'])->whereBetween('create_time', strtotime($today_s).",".strtotime($today_e))->find();
                if ($baglog) continue;
                if($today_data['money']>$yestoday_data['money'] && $yestoday_data['money']>=200){
                    $newmoney = $yestoday_data['money'];
                }else{
                    $newmoney = $today_data['money'];
                }
                if($today_data['money']<$yestoday_data['money'] && $today_data['money']>=200){
                    $newmoney = $today_data['money'];
                }else{
                    $newmoney = $yestoday_data['money'];
                }
//                 $score = bcmul(bcdiv($vl['money'],$total['money'],2),bcdiv($coins,4,2),2);
                $score = bcmul($newmoney,sysconf('hold_interest_coe'),2);
                if($score > 0){
                    $res[] = mlog($vl['id'], 'account_score', $score, "持币生息",'account_score_interest','',15,'');
                    //处理动态奖
                    $merchatInfo = Db::name('store_member')->where('id',$vl['id'])->find();
                    $first_leader = Db::name('store_member')->where(['id' =>$merchatInfo['first_leader']])->value('level');
                    if($first_leader == 2){
                        $res[] = mlog($merchatInfo['first_leader'], 'account_score', 0.4*$score, "直推持币生息奖",'account_score_interest','',5,$vl['id']);
                    }elseif($first_leader == 1 || $first_leader == 5  || $first_leader == 6){
                        $all_leaders = array_reverse(explode(',', $merchatInfo['all_leader']));//反转数组
                        $res[] = mlog($merchatInfo['first_leader'], 'account_score', 0.2*$score, "直推持币生息奖",'account_score_interest','',5,$vl['id']);
                        foreach ($all_leaders as $v)
                        {
                            $leaders = Db::name('store_member')->where(['id' =>$v])->value('level');
                            if($leaders == 2){
                                $res[] = mlog($v, 'account_score', 0.2*$score, "间推持币生息奖",'account_score_interest','',5,$vl['id']);
                                break;
                            }
                        }
                    }
                }
            }
            if ($this->check_arr($res)) {
                Db::commit();
                echo '成功';
            } else {
                Db::rollback();
                echo 1;
            }
        }else{
            echo '时间不到';
        }
    }
    
    public function coin(){
        if(date('H') == 0){
            $time = strtotime(date("Y-m-d 00:00:00")); //获取今天0点的时间戳
            $day_Data = DB::table('market_hour')->where('currency_id', 1)
            ->where('legal_id', 4)
            ->where('period', '1day')
            ->where('day_time', '>=', $time)
            ->order('id', 'DESC')
            ->find();
            if(empty($day_Data)){
                $foc = Db::name('system_coin')->where('id',1)->find();
                $coin["high"] = $foc['price'];
                $coin["low"] = $foc['price'];
                $coin["amount"] = 0;
                $coin["change"] = 0;
                Db::name('system_coin')->where('id',1)->update($coin);
            }else{
                $coin["high"] = $day_Data['highest'];
                $coin["low"] = $day_Data['mminimum'];
                Db::name('system_coin')->where('id',1)->update($coin);
            }
        }else{
            echo '时间不到';
        }
    }
    
    function check_arr( $rs )
    {
        {
            foreach ($rs as $v) {
                if (is_array($v)) {
                    foreach ($v as $val) {
                        if (!$val) {
                            return false;
                        }
                    }
                } else {
                    if (!$v) {
                        return false;
                    }
                }
            }
            return true;
        }
        
    }
    
    /**
     * 按类型格式化时间线
     *
     * @param integer $type 类型:1.15分钟,2.1小时,3.一年,4.一天,5.分时,6.5分钟，7.30分钟,8.一周,9.一月,10.4小时
     * @param integer $day_time 时间戳,不传将默认采用当前时间
     * @return void
     * 根据类型，把时间戳进行一个1分钟之内，5分钟之内等
     */
    
    public function formatTimeline($type=10, $day_time ='1597825382')
    {
        empty($day_time) && $day_time = time();
        switch ($type) {
            //15分钟
            case 1:
                $start_time = strtotime(date('Y-m-d H:00:00', $day_time));
                $minute = intval(date('i', $day_time));
                $multiple = floor($minute / 15);
                $minute = $multiple * 15;
                $time = $start_time + $minute * 60;
                break;
                //1小时
            case 2:
                $time = strtotime(date('Y-m-d H:00:00', $day_time));
                break;
                //4小时
            case 3:
                $start_time = strtotime(date('Y-m-d', $day_time));
                $hours = intval(date('H', $day_time));
                $multiple = floor($hours / 4);
                $hours = $multiple * 4;
                $time = $start_time + $hours * 3600;
                break;
                //一天
            case 4:
                $time = strtotime(date('Y-m-d', $day_time));
                break;
                //分时
            case 5:
                $time_string = date('Y-m-d H:i', $day_time);
                $time = strtotime($time_string);
                break;
                //5分钟
            case 6:
                $start_time = strtotime(date('Y-m-d H:00:00', $day_time));
                $minute = intval(date('i', $day_time));
                $multiple = floor($minute / 5);
                $minute = $multiple * 5;
                $time = $start_time + $minute * 60;
                break;
                //30分钟
            case 7:
                $start_time = strtotime(date('Y-m-d H:00:00', $day_time));
                $minute = intval(date('i', $day_time));
                $multiple = floor($minute / 30);
                $minute = $multiple * 30;
                $time = $start_time + $minute * 60;
                break;
                //一周
            case 8:
                $start_time = strtotime(date('Y-m-d', $day_time));
                $week = intval(date('w', $day_time));
                $diff_day = $week;
                $time = $start_time - $diff_day * 86400;
                break;
                //一月
            case 9:
                $time_string = date('Y-m', $day_time);
                $time = strtotime($time_string);
                break;
                //一年
            case 10:
                $time = strtotime(date('Y-01-01', $day_time));
                break;
            default:
                $time = $day_time;
                break;
        }
        return $time;
    }
}