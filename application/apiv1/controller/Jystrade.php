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
class Jystrade extends Base
{
    /*
     * pot首页
     * */
    public function potIndex(){

        $this->success('',['pot_total_currency'=>sysconf('pot_total_currency'),'sl_num'=>$this->wx_user['wallet_four']+$this->wx_user['wallet_five'],'total_pot_nac'=>$this->wx_user['total_pot_nac']]);
    }
    //挂买单pot
    public function buyOrderOutOrInto()
    {
        $language =  language(Cache::get('lan_type'),'jystrade','buyOrderOutOrInto');
        $data = input('post.');
        $validate = Validate::make([
            'type'=>'require',//0买入1卖出
            'type1'=>'require',//0市价交易1限价交易
            'price'=>'require',//单价
            'totalnum'=>'require',//总数量
            //'paypassword' =>'require',
        ], [
            'price.require' => $language['jgbnwk'],
            'totalnum.require' =>$language['jyslbnwk'],
            //'paypassword.require' => '支付密码不能为空',
        ]);
        $validate->check($data) || $this->error($validate->getError());
    
        if( $data['price']<=0){
            $this->error($language['jgyw']);
        }
        if( $data['totalnum']<=0){
            $this->error($language['jyslyy']);
        }
        if($data['type'] == 0){
            //var_dump( $this->error($language['slbzxy']));
            if($this->wx_user['account_money'] <  $data['price']*$data['totalnum']){
                $this->error($language['slbzxy'].$data['price']*$data['totalnum'].$language['g']);
             
            }
            
            //进行挂买单
            $money = $data['price']*$data['totalnum'];
            $rate_money = sysconf('jys_rate')*$money*0.01;
            $aturnover = $money + $rate_money;
            $arr = [
                'ordersn'=>'B'.makeRand(),
                'name'=>'NAC/USDT',
                'uid' => $this->wx_user_id,
                'uname'=> $this->wx_user['email'],
                'price'=> $data['price'],
                'totalnum' => $data['totalnum'],
                'leavenum' => $data['totalnum'],
                'aturnover'=> $aturnover,//usdt为单位
                'addtime' => time(),
                'tax_rate'=>sysconf('jys_rate'),
                'tax_money'=>$rate_money
            ];
            Db::startTrans();
            $res['orderid'] = Db::name('jys_buylist')->insertGetId($arr);
            $res_id1 = $res[] = mlog($this->wx_user_id, 'account_money', -$aturnover, "交易所挂买单nac{$data['totalnum']},单价{$data['price']}，手续费{$rate_money}扣除usdt{$aturnover}", 'buylist','','4',$res['orderid']);
            bagslanguage($res_id1['1'],$data['totalnum'],$data['price'],$rate_money,$aturnover,33,23.,29,34);
            if (check_arr($res)) {
                Db::commit();
                $this->success($language['mrcgddpp']);
            } else {
                Db::rollback();
                $this->error($language['mrsb']);
            }
        }else{

            //进行挂买单
            $aturnover = $data['price']*$data['totalnum'];
            $rate_money = sysconf('jys_rate')*$data['totalnum']*0.01;
            $total = $data['totalnum'] + $rate_money;//总共需要扣除nac的数量
            if($this->wx_user['account_score'] <  $total){
                $this->error($language['slbzxy'].$total.$language['gnac']);
            }
            $arr = [
                'ordersn'=>'S'.makeRand(),
                'name'=>'NAC/USDT',
                'uid' => $this->wx_user_id,
                'uname'=> $this->wx_user['email'],
                'price'=> $data['price'],
                'totalnum' => $data['totalnum'],
                'leavenum' => $data['totalnum'],
                'aturnover'=> $aturnover,//usdt为单位
                'addtime' => time(),
                'tax_rate'=>sysconf('jys_rate'),
                'tax_money'=>$rate_money
            ];
            Db::startTrans();
            $res['orderid'] = Db::name('jys_selllist')->insertGetId($arr);
            $res_id2 = $res[] = mlog($this->wx_user_id, 'account_score', -$total, "交易所挂卖单扣除nac{$data['totalnum']},手续费nac{$rate_money},单价{$data['price']}，usdt{$aturnover}", 'selllist','','4',$res['orderid']);
            bagslanguage($res_id2['1'],$data['totalnum'],$rate_money,$data['price'],$aturnover,35.,36,23,37);
            if (check_arr($res)) {
                Db::commit();
                $this->success($language['mcccddpp']);
            } else {
                Db::rollback();
                $this->error($language['mcsb']);
            }
        }
    }

    /*
     * 交易量和交易价格
     * */
    public function jysPrice(){
        //各显示（10条）
        $buylist = db::name('jys_price')->where(['type'=>0])->order('num desc')->limit(10)->select();
        $selllist = db::name('jys_price')->where(['type'=>1])->order('num desc')->limit(10)->select();
        $this->success('',['buylist'=>$buylist,'selllist'=>$selllist]);
    }

    /*
     * 我的订单
     * */
    public function myorder(){
        $type = $this->request->param('type');
        if($type == 1){
           $order = db::name('jys_buylist')->where(['uid'=>$this->wx_user_id])->page($this->page, $this->max_page_num)->order('id desc')->select();
        }elseif($type == 2){
            $buyorder = db::name('jys_buylist')->page($this->page, $this->max_page_num)->order('id desc')->select();
            $sellorder = db::name('jys_selllist')->page($this->page, $this->max_page_num)->order('id desc')->select();
        }else{
            $order = db::name('jys_selllist')->where(['uid'=>$this->wx_user_id])->page($this->page, $this->max_page_num)->order('id desc')->select();
        }
        $this->success('',['order'=>$order,'buyorder'=>$buyorder,'sellorder'=>$sellorder]);
    }


    /*
     * 撤销订单
     * */
    public function revokeorder(){
         $language =  language(Cache::get('lan_type'),'jystrade','revokeorder');
        $orderId = input('param.orderId');
        $type = input('param.type');//0买单1卖单
        !$orderId && $this->error($language['ddbnwk']);
        //卖单
        if($type == 1){
            $orderInfo = Db::name('jys_selllist')->where(['id' => $orderId,'state'=>0])->find();
            if(!$orderInfo){
                $this->error($language['gmddwx']);
            }
            Db::startTrans();
            //撤销返回的是nac
            $rate_nac = $orderInfo['leavenum']*sysconf('jys_rate')*0.01;
            $nac = $orderInfo['leavenum']+$rate_nac;
            $res_id1 = $res[] = mlog($orderInfo['uid'], 'account_score',$nac, '取消卖单'.$orderInfo['ordersn'].'手续费' . $rate_nac."实际退回nac".$nac, 'cancel_C2c_order', $orderId);
            bagslanguage($res_id1['1'],$orderInfo['ordersn'],$rate_nac,$nac,'',28,29,30);
            $res[] = Db::name('jys_selllist')->where(['id' => $orderId,'state'=>0])->update(['state' => 2,'endtime'=>time()]);
            if (check_arr($res)) {
                Db::commit();
                $this->success($language['cxcg']);
            } else {
                Db::rollback();
                $this->error($language['cxsb']);
            }
        }else {
            //买单
            $orderInfo = Db::name('jys_buylist')->where(['id' => $orderId,'state'=>0])->find();
            if(!$orderInfo){
                $this->error($language['gmdddwx']);
            }
            Db::startTrans();
            //撤回的是usdt数量
            $rate_usdt = $orderInfo['leavenum']* $orderInfo['price']*sysconf('jys_rate')*0.01;
            $usdt =  $orderInfo['leavenum']* $orderInfo['price']+ $rate_usdt;
            $res_id2 = $res[] = mlog($orderInfo['uid'], 'account_money', $usdt, '取消买单'.$orderInfo['ordersn'].'手续费'.$rate_usdt.'实际退回usdt'.$usdt, 'cancel_C2c_order', $orderId);
            bagslanguage($res_id2['1'],$orderInfo['ordersn'],$rate_usdt,$usdt,'',31,29,32);
            $res[] = Db::name('jys_buylist')->where(['id' => $orderId])->update(['state' => 2,'endtime'=>time()]);
            if (check_arr($res)) {
                Db::commit();
                $this->success($language['cxcg']);
            } else {
                Db::rollback();
                $this->error($language['cxsb']);
            }
        }

    }
    /*
     * 获取最优市价
     * */
    public function goodprice(){
        $type = $this->request->param('type');
        $price = db::name('jys_price')->where(['type'=>$type])->order('num desc')->value('price');
        $this->success('',['price'=>$price]);
    }

}
