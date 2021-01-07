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
class Wdingshi extends BasicIndex
{
        /*
  * 删除未注册完成账号
  * */
    public function userdel()
    {
        $shengounum = Db::name('store_member')->where(['is_zhujici'=>1])->where('create_at','<',(date('Y-m-d 0:0:1')))->select();
        foreach ($shengounum as $value){
            Db::name('store_member')->where('id',$value['id'])->delete();
        }
    }
    
    /*
   * 申购结算
   * */
    public function shengou()
    {      
        set_time_limit(0);
        $timeold = sysconf('time_shengouold');
        //halt($timeold);
        $newtime = date('H');
        if($newtime!=$timeold){
            echo 6;die;
        }
        //$shengounum = Db::name('wang_shengou')->where(['status'=>1])->where('create_at','<',strtotime(date('Y-m-d 0:0:1')))->sum('num');
        $shengounum = Db::name('wang_shengou')->where(['status'=>1])->sum('usdt');
        $shengou_dayshifa = sysconf('shengou_dayshifa');    //每天实发数量
        $shengou_newjiage = sysconf('shengou_newjiage');    //实际价格
        //$shengou = Db::name('wang_shengou')->where(['status'=>1])->where('create_at','<',strtotime(date('Y-m-d 0:0:1')))->select();

        $shengou = Db::name('wang_shengou')->where(['status'=>1])->select();
        //$shengou = Db::name('wang_shengou')->where(['id'=>73])->select();
        foreach ($shengou as $key=>$val){
            $res = [];
            //$addnum = bcmul(bcmul(bcdiv($shengou_dayshifa,$shengounum,6),$val['num'],6),$shengou_newjiage,6);
            $usernum = Db::name('store_member')->where(['first_leader'=>$val['uid'],'is_renzheng'=>2])->count();//认证人数
            if($val['usdt'] >=300){
                $jisuanusdt = 300;
            }else{
                $jisuanusdt = 100;
            }
            //$useradd = bcadd(bcmul($usernum,0.05,6),$jisuanusdt,6);
            //$newnum = bcmul(bcdiv($useradd,$shengounum,6),$shengou_dayshifa,6);
            $useradd = bcmul($usernum,0.05,6);
            $newnum = bcmul(bcdiv($jisuanusdt,$shengounum,6),$shengou_dayshifa,6);
            $addnum = bcadd(bcmul($newnum,$shengou_newjiage,6),$useradd,6);
            $newnum = bcdiv($addnum,$shengou_newjiage,6);
            Db::startTrans();
            if($addnum>=$val['usdt']){
                $res[] = Db::name('wang_shengou')->where('id',$val['id'])->update(['end_time'=>time(),'good_num'=>$val['num'],'good_usdt'=>$val['usdt'],'status'=>2]);
                $res[] = mlog($val['uid'],'account_score',$val['num'],"申购",'account_score','',7);
            }else{
                $usdttuihuan = $val['usdt'] - $addnum;
                $res[] = Db::name('wang_shengou')->where('id',$val['id'])->update(['end_time'=>time(),'good_num'=>$newnum,'good_usdt'=>$addnum,'status'=>2]);
                $res[] = mlog($val['uid'],'account_score',$newnum,"申购",'account_score',$val['id'],7);
                $res[] = mlog($val['uid'],'account_money',$usdttuihuan,"申购退还",'account_money',$val['id'],6);
            }
            if ($this->check_arr($res)) {
                Db::commit();
            } else {
                Db::rollback();
                echo 1;
            }
        }
    }

    /*
   * 矿池结算
   * */
    public function kuangchi()
    {
        set_time_limit(0);

        $shengou = Db::name('wang_kuangchi')->where(['status'=>1])->where('create_at','<',strtotime(date('Y-m-d 0:0:1')))->select();
        
        $jiaoyi_nf = sysconf('jiaoyi_nf');//nf交易单间
        $jiaoyi_nfc = sysconf('jiaoyi_nfc');//NTF交易单间
        foreach ($shengou as $key=>$val){
            $num = bcdiv(bcmul($val['num'],$jiaoyi_nf,6),$jiaoyi_nfc);
            $num = bcadd($num,$val['good_num'],6);//var_dump($num);die;
            Db::startTrans();
                $res[] = Db::name('wang_kuangchi')->where('id',$val['id'])->update(['end_time'=>time(),'status'=>2]);
                //$res[] = mlog($val['uid'],'account_score',$val['num'],"矿池成功",'account_score',$val['id'],9);
                $res[] = mlog($val['uid'],'account_nfc',$num,"矿池成功",'account_nfc',$val['id'],9);
            if ($this->check_arr($res)) {
                Db::commit();
            } else {
                Db::rollback();
                echo 1;
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
                $sellinfo = db::name('jys_selllist')->where('leavenum','gt','0')->where('name',$v['name'])->where('price',$v['price'])->order('id asc')->find();

                if($v['price'] == $sellinfo['price'] && $v['uid'] != $sellinfo['uid']){
                    $moneyname = 'account_foc';
                    $num = min($v['leavenum'],$sellinfo['leavenum']);
                    //买单
                    db::name('jys_buylist')->where(['id'=>$v['id']])->setDec('leavenum',$num);
                    $res_id1 = mlog($v['uid'], $moneyname, $num, "交易所买单{$v['ordersn']}匹配卖单{$sellinfo['ordersn']},单价{$v['price']}，增加{$v['name']}{$num}", 'buylist','','11',$sellinfo['id']);
                    //bagslanguage($res_id1['1'],$v['ordersn'],$sellinfo['ordersn'],$v['price'],$num,21,22,23,24);
                    if($v['leavenum'] == $num){
                        //全部挂买数量完成
                        db::name('jys_buylist')->where(['id'=>$v['id']])->update(['state'=>1,'endtime'=>time()]);
                    }
                    //卖单
                    db::name('jys_selllist')->where(['id'=>$sellinfo['id']])->setDec('leavenum',$num);
                    //卖出应获得usdt个数
                    $money = $num*$v['price'];
                    $res_id2 =mlog($sellinfo['uid'], 'account_money', $money, "交易所卖单{$sellinfo['ordersn']}匹配买单{$v['ordersn']},单价{$v['price']}，增加usdt{$money}", 'buylist','','11',$v['id']);
                    //bagslanguage($res_id2['1'],$sellinfo['ordersn'],$v['ordersn'],$v['price'],$money,25,26,23,27);
                    if($sellinfo['leavenum'] == $num){
                        //全部挂买数量完成
                        db::name('jys_selllist')->where(['id'=>$sellinfo['id']])->update(['state'=>1,'endtime'=>time()]);
                    }
                }
            }
            Db::commit();
        }catch(\Exception $e){
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

    /*
     * 行情收集
     * */
    public function getCEOticker(){
        $qc_data= ["btc","eth","eos","ltc","xrp","bch"];

        //$url = "https://api.ceoex.com/api/market/ticker?market=";
        $url = "https://api.huobi.br.com/market/detail?symbol=";
        foreach ($qc_data as $k=>$v){
            $data_url=$url.$v.'usdt';
            // $result=$this->http('https://api.huobi.pro/market/detail/merged?symbol=ethusdt');
            //print_r($data_url);
            //print_r($result);
            //$result=$this->curl($data_url);
            //print_r($result);
            //halt(123);
            //$s = file_get_contents($data_url);
            //$s = json_decode($s, true);
            //halt($s);
            $result=$this->http($data_url);
            $result=json_decode($result,true);
            
            if($result["status"] == "ok"){
                $info = Db::name('system_coin')->where("name",$v)->where('status',1)->find();
                $data= [];
                $new_price = bcdiv($result["tick"]["low"]+$result["tick"]["high"],2,6);
                
                if(empty($info)){
                    $data= [];
                    $data["name"] = $v;
                    $data["price"] = $result["tick"]["close"];
                    $data["change"] = bcmul(bcdiv($result["tick"]["close"]-$new_price,$new_price,6),100,4);
                    $data["status"] = 1;
                    $data["high"] = $result["tick"]["high"];
                    $data["low"] = $result["tick"]["low"];
                    $data["amount"] = $result["tick"]["amount"];
                    Db::name('system_coin')->where('status',1)->insert($data);
                }else{
                    $jicha = $result["tick"]["close"]-$new_price;
                    $data["price"] = $result["tick"]["close"];
                    $data["change"] = bcmul(bcdiv($jicha,$result["tick"]["close"],6),100,4)-1.5;
                    $data["amount"] = $result["tick"]["amount"];
                    $data["high"] = $result["tick"]["high"];
                    $data["low"] = $result["tick"]["low"];
                    Db::name('system_coin')->where('id',$info["id"])->update($data);
                }
            }
        }
        return json(['code' => 1,'message' => "执行成功"]);
    }

    public function curl($url, $type = 'GET', $postdata = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($type == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
        ]);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        $output = curl_exec($ch);
        $info   = curl_getinfo($ch);
        curl_close($ch);
        return @json_decode($output, true);
    }


    /**
     * http请求
     * @param  string  $url    请求地址
     * @param  boolean|string|array $params 请求数据
     * @param  integer $ispost 0/1，是否post
     * @param  array  $header
     * @param  $verify 是否验证ssl
     * return string|boolean          出错时返回false
     */
    public function http($url, $params = false, $ispost = 0, $header = [], $verify = false) {
        $httpInfo = array();
        $ch = curl_init();
        if(!empty($header)){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        //忽略ssl证书
        if($verify === true){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if (is_array($params)) {
                $params = http_build_query($params);
            }
            if ($params) {
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        $response = curl_exec($ch);
        if ($response === FALSE) {
            trace("cURL Error: " . curl_errno($ch) . ',' . curl_error($ch), 'error');
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
            trace($httpInfo, 'error');
            return false;
        }
        curl_close($ch);
        return $response;
    }
    
     /*
  * 用户达标 矿池释放
  * */
    public function usershouyi()
    {
        set_time_limit(0);
        $shengou = Db::name('store_member')->where(['is_xiaoshou'=>1])->where('first_leader','>',0)->where('account_money','>=',100)->select();
        foreach ($shengou as $key=>$val){
            Db::startTrans();
            $res= [];
            if(!empty($val['all_leader'])){
                $asdf = explode(',',$val['all_leader']);
                if(!empty($asdf)){
                    $us = DB::table('store_member')->where('id' , $asdf[0])->find();
                    if(!empty($us)&&empty($us['all_leader'])&&$us['suocang_num']>$us['suocang_fafang']){
                        $shifa = bcdiv(bcmul($us['suocang_num'],sysconf('suocang_bili'),6),100,2);
                        $res[] = Db::name('store_member')->where('id',$us['id'])->setInc('suocang_fafang',$shifa);
                        $res[] = Db::name('store_member')->where('id',$val['id'])->update(['is_xiaoshou'=>2]);
                        $res[]= mlog($us['id'],'account_score',$shifa,'用户'.$val['address'].'激活，锁仓代币发放','rechanglxc','','14');
                    }
                }
            }
            //halt($res);
            if ($this->check_arr($res)) {
                Db::commit();
            } else {
                Db::rollback();
                echo 1;
            }
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
    
     /*
  * 发放用户申购资格
  * */
    public function shengoufafang()
    {
        set_time_limit(0);
        $timeadd = sysconf('time_kuaizhao');
        $newtime = date('H');
        //halt($timeadd);
        if($newtime == $timeadd){
            //$shengou = Db::name('store_member')->where('shengou_time','<',strtotime(date('Y-m-d 0:0:1')))->order('id desc')->select();
            $shengou = Db::name('store_member')->order('id desc')->select();
            $usdtnum = Db::name('store_member')->order('id desc')->sum('account_money');
            foreach ($shengou as $key=>$val){
                $dayshengou = 0;
                if($val['account_money'] >= 500){
                    $dayshengou = 500;
                }elseif ($val['account_money']>=100) {
                    $dayshengou = 100;
                }else{
                    $dayshengou = 0;
                }
                if($dayshengou > 0){
                    Db::name('store_member')->where(['id'=>$val['id']])->update(['is_shengou'=>2,'shengou_time'=>time(),'day_shengou'=>$dayshengou]);
                }else{
                    Db::name('store_member')->where(['id'=>$val['id']])->update(['is_shengou'=>1,'shengou_time'=>time(),'day_shengou'=>$dayshengou]);
                }
                /*$dayxuni = sysconf('shengou_dayxuni');
                $shengou_newjiage = sysconf('shengou_newjiage');    //实际价格
                $day_usdt = bcdiv($dayxuni,$shengou_newjiage,6);
                $dayshengou = 0;
                if($val['first_leader']>0){
                    if($val['account_money']>=100){
                        $usernum = Db::name('store_member')->where(['first_leader'=>$val['id'],'is_renzheng'=>2])->count();
                        $useradd = bcmul($usernum,0.05,6);
                        $dayshengou = bcadd(bcmul(bcdiv($val['account_money'],$usdtnum,6),$day_usdt,6),$useradd,6);
                        Db::name('store_member')->where(['id'=>$val['id']])->update(['is_shengou'=>2,'shengou_time'=>time(),'day_shengou'=>$dayshengou]);
                    }else{
                        Db::name('store_member')->where(['id'=>$val['id']])->update(['is_shengou'=>1,'shengou_time'=>time(),'day_shengou'=>$dayshengou]);
                    }
                }else{
                    if($val['account_money']>=500){
                        $usernum = Db::name('store_member')->where(['first_leader'=>$val['id'],'is_renzheng'=>2])->count();
                        $useradd = bcmul($usernum,0.05,6);
                        $dayshengou = bcadd(bcmul(bcdiv($val['account_money'],$usdtnum,6),$day_usdt,6),$useradd,6);
                        Db::name('store_member')->where(['id'=>$val['id']])->update(['is_shengou'=>2,'shengou_time'=>time(),'day_shengou'=>$dayshengou]);
                    }else{
                        Db::name('store_member')->where(['id'=>$val['id']])->update(['is_shengou'=>1,'shengou_time'=>time(),'day_shengou'=>$dayshengou]);
                    }
                }*/
            }
        }else{
            echo 6;
        }
    }


}
