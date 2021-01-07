<?php

namespace app\behavior;

use think\Db;

class FreeBonus
{
    public $order;
    public $memberStoreService;
    public $bonus_type;
    public $orderID;

    //释放用户佣金
    public function run( $param = [] )
    {
        $this->bonus_type = $param['bonus_type'];  //释放佣金类型
        $this->orderID = $param['orderID'];        //订单号
        if(!$this->bonus_type || !$this->orderID) return false;
        $this->memberStoreService = getMemberFree()->setTranID($this->orderID)->setTranType($this->bonus_type);
        $this->memberStoreService->freeByoneBonus();  //订单分佣
        return true;
    }
}