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
class Resource extends Base
{
    public function index()
    {
        //获取美元的价格
        $dollar = db::name('system_coin')->where(['name'=>'usdt'])->value('price');
        $acc_price = db::name('system_coin')->where(['name'=>'acc'])->value('price');
        $acc_dollar = number_format($acc_price/$dollar, 4);
        //总资产（我的账户余额+我的积分+待释放acc+可用acc）
       $total_meiyuan = $this->wx_user['account_money'] +  $this->wx_user['account_score'] + $this->wx_user['dsf_acc'] * $acc_dollar + $this->wx_user['ky_acc']*$acc_dollar;
       //1个usdt的价格
        $price = db::name('system_coin')->where(['name'=>'usdt'])->value('price');
        $total_renminbi = $this->wx_user['account_money']*$dollar +  $this->wx_user['account_score']*$dollar + ($this->wx_user['dsf_acc'] * sysconf('pdr_money')) + ($this->wx_user['ky_acc']* sysconf('pdr_money'));
        $data['total_meiyuan'] = number_format($total_meiyuan,4);
        $data['total_renminbi'] = number_format($total_renminbi,4);
        $this->success('',$data);
    }

      //实时价格
    public function realtimePrice(){
        $system = db::name('system_coin')->select();
        $dollar = db::name('system_coin')->where(['name'=>'usdt'])->value('price');
        foreach($system as $key=>$value){
          
            $system[$key]['dollar'] = number_format($value['price']/$dollar, 4);
            
           
            //var_dump($value['price']);
            if($value['name'] == 'ACC'){
           
                if($value['price'] != sysconf('pdr_money')){
                   
                    db::name('acc_price')->data(['price'=>sysconf('pdr_money')])->insert();
                    
                    $system[$key]['price'] = sysconf('pdr_money');
                    $acc_money = db::name('acc_price')->order('id desc')->find();
                    $daoshuo_er_id = $acc_money['id'] -1;
                    $daoshu_er = db::name('acc_price')->where(['id'=>$daoshuo_er_id])->value('price');
                    $daoshu_yi = $acc_money['price'];
                    $cha = $daoshu_yi-$daoshu_er;
                    
                    $change = $cha/$daoshu_er;
                    $system[$key]['change'] = number_format($change,2);
                    //var_dump($change);
                    //更改价格

                     $user = Db::name('system_coin')->where(['name' =>'acc'])->update(['price'=>sysconf('pdr_money'),'change'=>$change]);
                }
            }
        }
       
        $this->success('',$system);
    }





}
