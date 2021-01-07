<?php

namespace app\apiv1\controller;
use think\Db;


/**
 * 应用入口控制器
 * @author Anyon <zoujingli@qq.com>
 */
class Award extends Base
{

    /*
     * 推荐奖(得到的是算力)
     * $levelid 指的是sys_level的id
     * $money 指的是usdt（需要换算成wt）
     * $posid 指的是pos的id
     * */
    public function tjAward($levelid,$uid,$money,$posid){
        $user = db::table('store_member')->where(array('id'=>$uid))->find();
        $tj_user_id = $user['first_leader'];
        if($tj_user_id){
            //推荐人信息
            $tjuser = db::table('store_member')->where(array('id'=>$tj_user_id))->find();
            $sys_level = db::name('sys_level')->field('jt_rate,sl_rate')->where(['id'=>$levelid])->find();
            //比较推荐人的个数sl_rate
            $add_money = $sys_level['jt_rate']*$money*0.01*$sys_level['sl_rate']*0.01;
            if($add_money > 0) {
                dongtaiAwardjl($tjuser['id'],$tjuser['email'],$uid,$user['email'], $type=0, $add_money, '获得推荐奖来源人为'.$user['email'].',总金额' . $money . '$ 算力比率'.$sys_level['sl_rate'].'%,总算力为'.$money*0.01*$sys_level['sl_rate'].'，推荐比率' . $sys_level['jt_rate'] . '%推荐算力为'.$add_money,'',$posid,$add_money);
            }
        }
    }

    /*
    * pot的见点奖
    *
    * */
    public function jdaward($uid,$money,$posid){
        $user = db::table('store_member')->where(array('id'=>$uid))->find();
        $all_leader = array_reverse(explode(',',$user['all_leader']));
        $leader_num = count($all_leader);
        for($i=0;$i<$leader_num;$i++) {
            $ii = $i+1;
            $tjuser = db::table('store_member')->where(['id' => $all_leader[$i],'is_disable'=>1])->find();;
            if ($tjuser) {
                //见点人的买单
                $tj_buylist = db::name('ty_buylist')
                    ->where(['uid'=>$tjuser['id']])
                    ->where('state','neq',6)
                    ->order('id desc')->find();
                if($tj_buylist){
                    //奖金烧伤
                    if($tj_buylist['totalnum'] <= $money){
                        $money = $tj_buylist['totalnum'];
                    }else{
                        $money = $tj_buylist['totalnum'];
                    }
                    $ceng = $i+1;
                    $sys_earnings = db::name('sys_earnings')->field('ceng_num,luck_rate')->where(['ceng_num'=>$ceng])->find();
                    //查询无限代的比率
                    $last_sys_earnings = db::name('sys_earnings')->field('ceng_num,luck_rate')->order('id desc')->find();
                    if($ceng >= $last_sys_earnings['ceng_num']){
                        $rate = $last_sys_earnings['luck_rate'];
                        $addmoney = $money * $last_sys_earnings['luck_rate'] *0.01;
                    }else{
                        $rate = $sys_earnings['luck_rate'];
                        $addmoney = $money * $sys_earnings['luck_rate'] *0.01;
                    }
                    if($addmoney > 0) {
                        dongtaiAwardjl($tjuser['id'],$tjuser['email'],$uid,$user['email'], $type=1, $addmoney, '获得 '.$ii.' 代见点奖来源人为'.$user['email'].',总金额' . $money . '$，见点比率' . $rate . '%见点算力为'.$addmoney,'',$posid,$addmoney);
                    }
                }
            }
        }
    }
}
