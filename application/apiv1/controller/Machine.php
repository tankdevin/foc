<?php

namespace app\apiv1\controller;

use service\DataService;
use think\Error;
use think\Validate;
use think\Db;
use think\facade\Cache;

/**
 * 应用入口控制器
 * @author Anyon <zoujingli@qq.com>
 */
class Machine extends Base
{
    /**
     * 矿机信息
     */
    public function index()
    {
        $total = Db::name('store_machine')->where(['mid' =>$this->wx_user_id,'kid'=>1])->sum('num');
        $num = 8-$total;
        $mach = Db::name('machine')->where(['status'=>1,'id'=>1])->find();
        if($mach['foc'] == 0){
            $mach['usdt'] = 500;
            $mach['foc'] = 200;
        }
        $machs = array();
        if($mach['stock'] > 0){
            for ($i=0;$i<$num;$i++){
                $machs[] = $mach;
            }
        }
//         $two = Db::name('store_machine')->where(['mid' =>$this->wx_user_id,'kid'=>1])->where('end_time','<', date('Y-m-d'))->find();
//         if(!empty($two)){
            $two_nu = Db::name('store_machine')->where(['mid' =>$this->wx_user_id,'kid'=>2])->sum('num');
            $num = 4-$two_nu;
            $mach_two = Db::name('machine')->where(['status'=>1,'id'=>2])->find();
            if($mach_two['stock'] >= 1){
                for ($i=0;$i<$num;$i++){
                    $machs[] = $mach_two;
                }
            }
//             $trhe = Db::name('store_machine')->where(['mid' =>$this->wx_user_id,'kid'=>2])->where('end_time','<', date('Y-m-d'))->find();
//             if(!empty($trhe)){
                $trhe_nu = Db::name('store_machine')->where(['mid' =>$this->wx_user_id,'kid'=>3])->sum('num');
                $num = 2-$trhe_nu;
                $mach_tre = Db::name('machine')->where(['status'=>1,'id'=>3])->find();
                if($mach_tre['stock'] >= 1){
                    for ($i=0;$i<$num;$i++){
                        $machs[] = $mach_tre;
                    }
                }
//                 $four = Db::name('store_machine')->where(['mid' =>$this->wx_user_id,'kid'=>3])->where('end_time','<', date('Y-m-d'))->find();
//                 if(!empty($four)){
                    $two = Db::name('store_machine')->where(['mid' =>$this->wx_user_id,'kid'=>4])->sum('num');
                    $num = 1-$two;
                    $mach_four = Db::name('machine')->where(['status'=>1,'id'=>4])->find();
                    if($mach_four['stock'] >= 1){
                        for ($i=0;$i<$num;$i++){
                            $machs[] = $mach_four;
                        }
                    }
//                 }
//             }
//         }
        $this->success('',$machs);
    }
    
    /**
     * 购买矿机
     */
    public function buy()
    {
        $language =  language($this->lang,'user','machine');
        $data = input('post.');
        $validate = Validate::make([
            'num' => 'require',
            'id' => 'require',
            'paypassword'=>'require',
        ], [
            'num.require' => $language['gmsl'],
            'paypassword.require' => $language['srzfmm'],
        ]);
        $validate->check($data) || $this->error($validate->getError());
        $merchatInfo = Db::name('store_member')->where(['id' =>$this->wx_user_id ])->find();
        if($merchatInfo['dongjie'] == 1) $this->error($language['dongjie']);
        $mach = Db::name('machine')->where('status',1)->where('id',$data['id'])->find();
        if(md5($data['paypassword']) != $merchatInfo['paypassword']) $this->error($language['zfmmcw']);
        if(!$mach) $this->error($language['myzskj']);
        $total = Db::name('store_machine')->where(['mid' =>$this->wx_user_id,'kid'=>$data['id']])->sum('num');
        if($data['id'] == 1){
            $my_top = 8;
        }elseif($data['id'] == 2){
            $my_top = 4;
        }elseif($data['id'] == 3){
            $my_top = 2;
        }elseif($data['id'] == 4){
            $my_top = 1;
        }
        if(($total+$data['num'])>$my_top) $this->error($language['kgkj'].($my_top-$total));
        if($mach['stock']<$data['num']) $this->error($language['kgkjkc']);
        $stock = $mach['stock']-$data['num'];
        $usdt = $data['num']*($mach['usdt']+$mach['foc']);
        $jiaoyi_foc= db::name('system_coin')->where('id',1)->find();
        if(sysconf('openjin_wei') == 1){//open_shijia
            $foc = bcmul($data['num'],bcdiv($mach['foc'],$jiaoyi_foc['price'],6),6);
        }else{
            $foc = $data['num']*$mach['foc_num'];
        }
        if($merchatInfo['account_money']<$usdt) $this->error('usdt'.$language['bz']);
//         if($merchatInfo['account_foc']<$foc) $this->error('foc'.$language['bz']);//setDec减少
        Db::startTrans();
        $time = time();
        $start = date('Y-m-d H:i:s',$time);
        $daynum = $mach['day_num']-1;
        $end = date('Y-m-d 23:59:59',strtotime('+ '.$daynum.' day'));
        $insert = array(
                        'mid'=>$this->wx_user_id,
                        'kid'=>$mach['id'],
                        'num'=>$data['num'],
                        'usdt'=>$usdt,
                        'foc'=>$foc,
                        'day_num'=>$mach['day_num'],
                        'start_time'=>$start,
                        'end_time'=>$end,
                        'status'=>1,
                        'create_at'=>date('Y-m-d H:i:s'),
                    );
        $res[] = Db::name('store_machine')->insert($insert);
        $res[] = Db::name('store_member')->where(['id' => $this->wx_user_id])->setInc('wallet_six', $data['num']);
        $res[] = Db::name('machine')->where('id',$mach['id'])->where('ver',$mach['ver'])->update(array('stock'=>$stock,'ver'=>time()));
        if($mach['usdt']>0){
            $res[] = mlog($this->wx_user_id, 'account_money', -$usdt, "usdt购买矿机",'buy_kjusdt','',8);
        }
//         if($mach['foc']>0){
//             $res[] = mlog($this->wx_user_id, 'account_foc', -$foc, "foc购买矿机",'buy_kjfoc','',8);
//         }
        //处理推荐奖
        $first_leader = Db::name('store_member')->where(['id' =>$merchatInfo['first_leader']])->value('level');
        if($first_leader == 2){
            $res[] = mlog($merchatInfo['first_leader'], 'account_money', 100*$data['num'], "直推奖",'tuijian','',5,$this->wx_user_id);
            $res[] = mlog($merchatInfo['first_leader'], 'usdt_suo', 100*$data['num'], "直推奖",'tuijian','',5,$this->wx_user_id);
        }elseif($first_leader == 1 || $first_leader == 5 || $first_leader == 6){
            $leader_mach = Db::name('store_machine')->where('mid',$merchatInfo['first_leader'])->find();
            $all_leaders = array_reverse(explode(',', $merchatInfo['all_leader']));//反转数组
            if(!empty($leader_mach)){
                $res[] = mlog($merchatInfo['first_leader'], 'account_money', 100*$data['num'], "直推奖",'tuijian','',5,$this->wx_user_id);
            }
            foreach ($all_leaders as $v)
            {
                $leaders = Db::name('store_member')->where(['id' =>$v])->value('level');
                if($leaders == 2){
                    $res[] = mlog($v, 'usdt_suo', 100*$data['num'], "间推奖",'tuijian','',5,$this->wx_user_id);
                    break;
                }
            }
        }
        if (check_arr($res)) {
            Db::commit();
            $this->success($language['gmcg']);
        } else {
            Db::rollback();
            $this->error($language['gmsb']);
        }
    }
    
    /**
     * 购买记录
     */
    public function record()
    {
        $list = Db::name('store_machine')->where('mid',$this->wx_user_id)->order('id',desc)->select();
        $this->success('',$list);
    }
    
    /**
     * 我的矿机
     */
    public function my_mach()
    {
        $list = Db::name('store_machine')->where('mid',$this->wx_user_id)->where('status','<>',3)->order('id',desc)->select();
//         $mach = Db::name('machine')->where('status',1)->find();
        $machs = array();
        foreach ($list as &$vl){
            if($vl['status'] == 0){
                $vl['status'] = 1;
            }
            $num = $vl['num']-$vl['syn_num'];
            for ($i=0;$i<$num;$i++){
                $machs[] = $vl;
            }
        }
        $this->success('',$machs);
    }
    
    /**
     * 矿机详情
     */
    public function mach_details()
    {
        $language =  language($this->lang,'user','machine');
        $data = input('post.');
        $validate = Validate::make([
            'id' => 'require',
        ], [
            'id.require' => $language['csyc'],
        ]);
        $machs = Db::name('store_machine')->where('id',$data['id'])->find();
        $mach = Db::name('machine')->where('id',$machs['kid'])->find();
//         $shouyi = Db::name('bags_log')->where(['uid'=>$machs['mid'],'orderId'=>$machs['kid'],'status'=>9,'type'=>'account_score'])->sum('money');
//         $num = Db::name('store_machine')->where(['mid'=>$machs['mid'],'kid'=>$machs['kid']])->sum('num');
        $machs['shouyi'] = bcdiv($machs['income'],$machs['num'],6);
        if($machs['status'] == 0) $machs['status'] = 1;
        $machs['endtime'] = $machs['end_time'];
        $machs['usdt'] = $mach['usdt'];
        $machs['foc_num'] = $machs['foc']/$machs['num'];
        $machs['foc'] = $mach['foc'];
        if($machs['foc'] == 0){
            $machs['usdt'] = 500;
            $machs['foc'] = 200;
        }
        $this->success('',$machs);
    }
    
    /**
     * 赎回
     */
    public function redeem()
    {
        $language =  language($this->lang,'user','machine');
        $data = input('post.');
        $validate = Validate::make([
            'id' => 'require',
        ], [
            'id.require' => $language['csyc'],
        ]);
        $list = Db::name('store_machine')->where(['mid'=>$this->wx_user_id,'kid'=>$data['id'],'status'=>2])->select();
        foreach ($list as $key => $vl){
            if($vl['num']<=$vl['syn_num']){
                array_splice($list,$key,1);
            }
        }
        Db::startTrans();
        if(($list[0]['num']-$list[0]['syn_num']) >= 1){
            $res[] = Db::name('store_machine')->where(['id' => $list[0]['id']])->setInc('syn_num', 1);
            $old_u = $list[0]['usdt']/$list[0]['num'];
        }else{
            $this->error($language['bksh']);
        }
        $mach = Db::name('machine')->where('id',$data['id'])->find();
//         if(($data['id']) == 1){
//             $my_top = 200;
//         }elseif(($data['id']) == 2){
//             $my_top = 400;
//         }elseif(($data['id']) == 3){
//             $my_top = 800;
//         }elseif(($data['id']) == 4){
//             $my_top = 1600;
//         }
        $res[] = mlog($this->wx_user_id, 'account_money', $old_u, "赎回返还USDT",'shuhui','',7,$list[0]['kid']);
        if (check_arr($res)) {
            Db::commit();
            $this->success($language['shcg']);
        } else {
            Db::rollback();
            $this->error($language['shsb']);
        }
    }
    
    /**
     * 提币
     */
    public function mention()
    {
        $language =  language($this->lang,'user','machine');
        $data = input('post.');
        $validate = Validate::make([
            'id' => 'require',
        ], [
            'id.require' => $language['csyc'],
        ]);
        $list = Db::name('store_machine')->where(['mid'=>$this->wx_user_id,'kid'=>$data['id'],'status'=>2])->select();
        foreach ($list as $key => $vl){
            if($vl['num']<=$vl['syn_num']){
                array_splice($list,$key,1);
            }
        }
        $mach = Db::name('machine')->where('id',$data['id'])->find();
        Db::startTrans();
        if(($list[0]['num']-$list[0]['syn_num']) >= 1){
            $res[] = Db::name('store_machine')->where(['id' => $list[0]['id']])->setInc('syn_num', 1);
            if($list[0]['foc'] == 0) $this->error($language['zksh']);
            $old_f = $list[0]['foc']/$list[0]['num'];
            $usdt = $mach['usdt'];
        }else{
            $this->error($language['kjyc']);
        }
        $res[] = mlog($this->wx_user_id, 'account_score', $old_f, "赎回返还foc",'shuhui','',7,$list[0]['kid']);
        $res[] = mlog($this->wx_user_id, 'account_money', $usdt, "赎回返还usdt",'shuhui','',7,$list[0]['kid']);
        if (check_arr($res)) {
            Db::commit();
            $this->success($language['tbcg']);
        } else {
            Db::rollback();
            $this->error($language['tbsb']);
        }
    }
    
    /**
     * 合成
     */
    public function synthesis()
    {
        $language =  language($this->lang,'user','machine');
        $data = input('post.');
        $validate = Validate::make([
            'id' => 'require',
        ], [
            'id.require' => $language['csyc'],
        ]);
        if($data['id'] == 4) $this->error($language['wfxf']);
        $list = Db::name('store_machine')->where(['mid'=>$this->wx_user_id,'kid'=>$data['id'],'status'=>2])->select();
        foreach ($list as $key => $vl){
            if($vl['num']<=$vl['syn_num']){
                array_splice($list,$key,1);
            }
        }
        $mach = Db::name('machine')->where('status',1)->where('id',$data['id']+1)->find();
        if(!$mach) $this->error($language['kjyc']);
        $total = Db::name('store_machine')->where(['mid' =>$this->wx_user_id,'kid'=>$data['id']+1])->sum('num');
        if(($data['id']+1) == 1){
            $my_top = 8;
        }elseif(($data['id']+1) == 2){
            $my_top = 4;
        }elseif(($data['id']+1) == 3){
            $my_top = 2;
        }elseif(($data['id']+1) == 4){
            $my_top = 1;
        }
        if(($total+1)>$my_top) $this->error($language['cyym']);
        Db::startTrans();
        $time = time();
        $start = date('Y-m-d H:i:s',$time);
        $daynum = $mach['day_num']-1;
        $end = date('Y-m-d 23:59:59',strtotime('+ '.$daynum.' day'));
        if(($list[0]['num']-$list[0]['syn_num']) >= 2){
            $res[] = Db::name('store_machine')->where(['id' => $list[0]['id']])->setInc('syn_num', 2);
            $old_foc = ($list[0]['foc']/$list[0]['num'])*2;
        }else{
            if(count($list) < 2){
                $this->error($language['jckjbz']);
            }else{
                $res[] = Db::name('store_machine')->where(['id' => $list[0]['id']])->setInc('syn_num', 1);
                $res[] = Db::name('store_machine')->where(['id' => $list[1]['id']])->setInc('syn_num', 1);
                $old_foc = ($list[0]['foc']/$list[0]['num'])+($list[1]['foc']/$list[1]['num']);
            }
        }
        $jiaoyi_foc= db::name('system_coin')->where('id',1)->find();
        if(sysconf('openjin_wei') == 1){
            $foc_p = bcdiv($mach['foc'],$jiaoyi_foc['price'],6);
        }else{
            $foc_p = $mach['foc_num'];
        }
        if($old_foc>$foc_p){
            $res[] = mlog($this->wx_user_id, 'account_score', $old_foc-$foc_p, "续费返回差价",'chajia','',9,$list[0]['kid']);
        }
        $insert = array(
            'mid'=>$this->wx_user_id,
            'kid'=>$mach['id'],
            'num'=>1,
            'usdt'=>$mach['usdt'],
            'foc'=>$foc_p,
            'day_num'=>$mach['day_num'],
            'start_time'=>$start,
            'end_time'=>$end,
            'status'=>1,
            'create_at'=>date('Y-m-d H:i:s'),
        );
        $res[] = Db::name('store_machine')->insert($insert);
        $res[] = Db::name('store_member')->where(['id' => $this->wx_user_id])->setInc('wallet_six', 1);
        //处理推荐奖
        $merchatInfo = Db::name('store_member')->where(['id' =>$this->wx_user_id ])->find();
        $first_leader = Db::name('store_member')->where(['id' =>$merchatInfo['first_leader']])->value('level');
        if($first_leader == 2){
            $res[] = mlog($merchatInfo['first_leader'], 'account_money', 100, "直推奖",'tuijian','',5,$this->wx_user_id);
            $res[] = mlog($merchatInfo['first_leader'], 'usdt_suo', 100, "直推奖",'tuijian','',5,$this->wx_user_id);
        }elseif($first_leader == 1 || $first_leader == 5 || $first_leader == 6){
            $leader_mach = Db::name('store_machine')->where('mid',$merchatInfo['first_leader'])->find();
            $all_leaders = array_reverse(explode(',', $merchatInfo['all_leader']));//反转数组
            if(!empty($leader_mach)){
                $res[] = mlog($merchatInfo['first_leader'], 'account_money', 100, "直推奖",'tuijian','',5,$this->wx_user_id);
            }
            foreach ($all_leaders as $v)
            {
                $leaders = Db::name('store_member')->where(['id' =>$v])->value('level');
                if($leaders == 2){
                    $res[] = mlog($v, 'usdt_suo', 100, "间推奖",'tuijian','',5,$this->wx_user_id);
                    break;
                }
            }
        }
        if (check_arr($res)) {
            Db::commit();
            $this->success($language['xfcg']);
        } else {
            Db::rollback();
            $this->error($language['xfsb']);
        }
    }
}