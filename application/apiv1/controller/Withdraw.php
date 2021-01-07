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
class Withdraw extends Base
{
    //usdt提现
    public function moneyTx()//
    {
        $pageNo = request()->param("page", 1);
        $pageSize = input('param.pageSize', 10);
        $start = ($pageNo - 1) * $pageSize;
        $user = Db::name('store_member')->where(['id' => $this->wx_user_id])->find();
        $orderList = Db::name('store_withdraw_money')->where(['uid' => $this->wx_user_id,'type'=>1])->order('id desc')->limit($start, $pageSize)->select();
        $this->success('',['orderlist'=>$orderList,'user'=>$user,'tx_tax'=>sysconf('tx_rate')]);
    }
     /*
     * 余额进行提现
     * */
    public function domoneyTx()
    {
        $language =  language($this->lang,'withdraw','domoneyTx');
        $data = input('post.');
        if($data['num'] && $data['paypassword']){

            $validate = Validate::make([
                'num' => 'require',
                'paypassword'=>'require',
                'safe_num'=>'require',
                'code'=>'require',
            ], [
                'num.require' => $language['txslbnwk'],
                'paypassword.require' => $language['zfmmbnwk'],
                'safe_num.require' => $language['aqmbnwk'],
            ]);
            $validate->check($data) || $this->error($validate->getError());
            $merchatInfo = Db::name('store_member')->where(['id' =>$this->wx_user_id ])->find();
            if($merchatInfo['dongjie'] == 1) $this->error($language['dongjie']);
            if ($merchatInfo) {
                if( $this->wx_user['account_money'] < $data['num']){
                    $this->error($language['slbz']);
                }
                if(sysconf('tx_min')>1){
                    if( $data['num'] % sysconf('tx_min') !=0){
                         $this->error(sysconf('tx_min').$language['bsqt']);
                    }
                }
                if($data['code'] != '8071'){
                    $register_code = $this->getSmsCode('register_code',$this->wx_user['phone']);
                    if($register_code != $data['code']){
                        $this->error($language['yzmcw']);
                    }
                }
                //每天最多可提
//                 $lingcheng = strtotime(date('Y-m-d'));
//                 $withdraw_money = db::name('store_withdraw_money')->where('addtime','gt',$lingcheng)->where(['uid'=>$this->wx_user_id])->where('state','lt',2)->sum('num');
//                 if(!$withdraw_money){
//                     $withdraw_money = 0;
//                 }
//                 $cha_money = sysconf('day_lc_fd')-$withdraw_money;
//                 if($data['num'] > $cha_money){
//                     $this->error($language['mtzdktx'].$cha_money.$language['usdtsl']);
//                 }
                $paypassword = db::name('store_member')->where(['paypassword'=>md5($data['paypassword']),'id'=>$this->wx_user_id])->find();
                if(!$paypassword){
                    $this->error($language['zfmmyw']);
                }
               /* $store_member_payment = db::name('store_member_payment')->where(['uid'=>$this->wx_user_id,'type'=>3,'state'=>1])->find();
                if(!$store_member_payment){
                    $this->error($language['qkddbcz']);
                }*/
                //安全码验证

                Db::startTrans();
                $res_id1 =  $res[] = mlog($this->wx_user_id, 'account_money', -$data['num'], "余额提现到钱包，usdt数量减少{$data['num']}", 'tx_usdt','',2,$this->wx_user_id);
                bagslanguage($res_id1['1'], $data['num'],'','','',38);
                //$tax = $data['num']*sysconf('tx_rate')*0.01;
                $tax = 10;//手续费
                $res[] = withdrawLog($this->wx_user_id, 1, $block_address=$data['safe_num'], $alipay_url='',$data['num'], $tax, $acc_money ='');
                if (check_arr($res)) {
                    Db::commit();
                    $this->success($language['txcgqddsh']);
                } else {
                    Db::rollback();
                    $this->error($language['txsb']);
                }
            } else {
                $this->error($language['hybcz']);
            }
        }else{
            $this->error($language['zfmmyw']);
            //$store_member_payment = db::name('store_member_payment')->where(['uid'=>$this->wx_user_id,'type'=>3,'state'=>1])->find();
            //$this->success("",['payment'=>$store_member_payment['payment']]);
        }
    }
    
    //lxc提现
    public function moneyTxusdt()//
    {
        $pageNo = request()->param("page", 1);
        $pageSize = input('param.pageSize', 10);
        $start = ($pageNo - 1) * $pageSize;
        $orderList = Db::name('store_withdraw_money')->where(['uid' => $this->wx_user_id,'type'=>1])->order('id desc')->limit($start, $pageSize)->select();
        foreach ($orderList as &$val){
            $val['addtime'] = date('Y-m-d H:i:s',$val['addtime']);
        }
        $this->success('',['orderlist'=>$orderList]);
    }
    
    //lxc提现
    public function moneyTxone()//
    {
        $id = request()->param("id", 0);
        $orderList = Db::name('store_withdraw_money')->where(['id'=> $id])->find();
        if($orderList){
            $orderList['addtime'] = date('Y-m-d H:i:s',$orderList['addtime']);
        }
        $this->success('',['orderlist'=>$orderList]);
    }
    
    
    /*
     * 余额进行提现
     * */
    public function domoneyTxlxc()//
    {
        $data = input('post.');
        $validate = Validate::make([
            'num' => 'require',
            //'tx_usdt_address'=>'require',
            'code'=>'require',
        ], [
            'num.require' => '提现数量不能为空',
            //'tx_usdt_address.require' => '提现地址不能为空',
            'num.require' => '验证码不能为空',
        ]);
        $validate->check($data) || $this->error($validate->getError());
         $merchatInfo = Db::name('store_member')->where(['id' =>$this->wx_user_id ])->find();
        if ($merchatInfo) {
            if( $this->wx_user['account_score'] < $data['num']){
                $this->error('nubc数量不足');
            }
            if( $data['num'] % sysconf('tx_min_lxc') !=0){
                $this->error(sysconf('tx_min_lxc').'倍数起提');
            }
            //每天最多可提
            $lingcheng = strtotime(date('Y-m-d'));
            $withdraw_money = db::name('store_withdraw_money')->where('addtime','gt',$lingcheng)->where(['uid'=>$this->wx_user_id,'type'=>2])->where('state','lt',2)->sum('num');
            if(!$withdraw_money){
                $withdraw_money = 0;
            }
            $cha_money = sysconf('day_lc_fd_lxc')-$withdraw_money;
            if($data['num'] > $cha_money){
                $this->error("每天最多可提".$cha_money."nubc数量");
            }
            $store_member_payment = db::name('store_member_payment')->where(['uid'=>$this->wx_user_id,'type'=>3,'state'=>1])->find();
            if(!$store_member_payment){
                $this->error("区块地址不存在，请添加区块地址");
            }
            Db::startTrans();
            $res[] = mlog($this->wx_user_id, 'account_score', -$data['num'], "nubc提现到钱包，nubc数量减少{$data['num']}个", 'tx_lxc','',10,$this->wx_user_id);
            $tax = $data['num']*sysconf('tx_rate_lxc')*0.01;
            $res[] = withdrawLog($this->wx_user_id, 2, $block_address=$store_member_payment['payment'], $alipay_url='',$data['num'], $tax, $acc_money ='');
            if (check_arr($res)) {
                Db::commit();
                $this->success('提现成功，请等待审核');
            } else {
                Db::rollback();
                $this->error('提现失败');
            }
        } else {
            $this->error('商户不存在');
        }
    }

    /*
     * $type 类型
     * $phone 手机号
     * */
    public function getSmsCode($type, $phone )
    {
        return \Cache::get($type . $phone);
    }
}
