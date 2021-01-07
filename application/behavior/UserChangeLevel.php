<?php
namespace app\behavior;
use think\Db;

class UserChangeLevel
{
    public $orderid;
    const AREA_LEVEL_LT = 4;
    const AREA_AGENCY = 5;
    const CITY_AGENCY = 6;
    //用户购买成功后进行升级操作、
    public function run( $param = [] )
    {
        $this->orderid = $param['orderid'];
        $order_info = Db::name('store_upgrade')->where(['id'=>$this->orderid])->find();
        if($order_info['pay_status'] == 1){
            Db::name('store_member')->where(['id'=>$order_info['uid']])->update(['member_level'=>$order_info['level']]);
            if($order_info['level'] > self::AREA_LEVEL_LT){
                $address_info = explode(',',$order_info['city_val']);
                if($order_info['level'] == self::AREA_AGENCY){
                    $add_id = $address_info[2];
                }elseif($order_info['level'] == self::CITY_AGENCY){
                    $add_id = $address_info[1];
                }
                Db::name('store_member')->where(['id'=>$order_info['uid']])->update(['addr_id'=>$add_id]);
            }
        }
        return TRUE;
    }
}