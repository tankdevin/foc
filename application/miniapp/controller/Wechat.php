<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/25
 * Time: 14:41
 */
namespace app\miniapp\controller;


use think\Controller;
use think\facade\Log;
use service\WechatService;
use think\Db;
class Wechat extends Controller{

    public function notify_wechat_pay(){

        $obj = WechatService::WeChatPay();
        $data = $obj->getNotify();
        Log::record($data);
//        $data = [
//            'appid' => 'wx52be3a61fe897934',
//            'bank_type' => 'CFT',
//            'cash_fee' => '1',
//            'fee_type' => 'CNY',
//            'is_subscribe' => 'Y',
//            'mch_id' => '1527156081',
//            'nonce_str' => '20190304162941',
//            'openid' => 'oAyFp5LmSoRpq8Mu2oEiv-mjt21Q',
//            'out_trade_no' => '20190304162941',
//            'return_code' => 'SUCCESS',
//            'result_code' => 'SUCCESS',
//            'sign' => '21641AEEB23B2FEF79E174152FC3A201',
//            'total_fee' => '1',
//            'trade_type' => 'JSAPI',
//            'transaction_id' => '4200000247201903044833680469',
//            ];
        if(!empty($data)){
            if($data['result_code'] == 'SUCCESS'){
                /*
                 * 更改订单状态
                 * */
                Db::startTrans();
                 $orderData = Db::table('store_order')->where(['order_no'=>$data['out_trade_no']])->find();
                 $shareData = Db::name('es_order_share_relationship')->where(['order_id'=>$orderData['id']])->find();
//                sm($orderData);
//                sm($shareData);
                 if($orderData['status'] == 1){
                     $res[] = Db::name('store_order')->where(['order_no'=>$data['out_trade_no']])->update(['status'=>2,'is_pay'=>1,'pay_no'=>$data['transaction_id'],'pay_price'=>$data['total_fee']/100]);
                     $res[] = Db::name('es_order_share_relationship')->where(['order_id'=>$orderData['id']])->update(['status'=>1]);
                     $res[] = Db::name('store_member')->where(['id'=>$shareData['add_uid']])->setInc('add_friends_num');
                     if(check_arr($res)){
                         Db::commit();
                         Log::record('支付失败smlls_success');
                     }else{
                         Db::rollback();
                         Log::record('支付失败smlls_fail');
                     }
                 }

            }else{
                Log::record('支付失败'.$data['result_code']);
            }
        }
    }

      public function notify_wechat_agency_pay(){
        $obj = WechatService::WeChatPay();
        $data = $obj->getNotify();
        if($data){
            if($data['result_code'] == 'SUCCESS'){
                Log::record('支付成功');
                /*
                 * 更改订单状态
                 * */
                Db::startTrans();
                $orderData = Db::table('store_upgrade')->where(['out_trade_no'=>$data['out_trade_no']])->find();
                $res[] = Db::table('store_upgrade')->where(['out_trade_no'=>$data['out_trade_no']])->update(['pay_status'=>1,'pay_no'=>$data['transaction_id'],'pay_price'=>$data['total_fee']/100]);
                $res[] = \think\facade\Hook::listen('FreeBonus', ['bonus_type' =>2, 'orderID' => $orderData['id']]);
                $res[] = \think\facade\Hook::listen('UserChangeLevel', ['orderid' => $orderData['id']]);
                if(check_arr($res)){
                    Db::commit();
                    Log::record('代理分佣成功');
                }else{
                    Db::rollback();
                    Log::record('代理分佣失败');
                }
            }
            Log::record('有数据');
        }else{
            Log::record('sm______pay_config_fail');
        }
    }


}