<?php

namespace app\apiv1\controller;

use service\DataService;
use think\Error;
use think\Validate;
use think\Db;
use controller\BasicIndex;

/**
 * 应用入口控制器
 * @author Anyon <zoujingli@qq.com>
 */
class Kline extends BasicIndex
{
    /*
      * 交易量和交易价格
      * */
    public function jysPrice(){
        //各显示（10条）
        $buylist = db::name('jys_price')->where(['type'=>0])->order('num desc')->limit(10)->select();
        $selllist = db::name('jys_price')->where(['type'=>1])->order('num desc')->limit(10)->select();
        $this->success('',['buylist'=>$buylist,'selllist'=>$selllist,'lastprice'=>'0.200000','timeprice'=>'0.100000']);
    }

    /*
     *1min=2hour，5min,15min,60min,4hour,1天，1周，1月，1年
     * @from
     * @to
     * &symbol=NAC/USDT
     * &period=5min
     * */
    public function newTime(){
        $data = $this->request->param();
        $market_hour = db::name('market_hour')->where(['period'=>$data['period']])->whereTime('day_time', '>=', $data['from'])->whereTime('day_time', '<=',$data['to'])->select();
       
    

        foreach($market_hour as $k=>$val){
            $market_hour[$k]['day_time'] = $val['day_time']*1000;
        }
        $this->success('',['market_hour'=>$market_hour]);
    }
    
    /*
    *1min=2hour，5min,15min,60min,4hour,1天，1周，1月，1年
    * @from
    * @to
    * &symbol=NAC/USDT
    * &period=5min
    * */
    public function newTimeshar(){
        $data = $this->request->param();
        $fasc = db::name('market_hour')->where(['currency_id'=>$data['currency_id'],'period'=>$data['period']])->limit(1000)->order('day_time desc')->select();
        $asdfc = array();
        foreach($fasc as $k=>$val){
            $asdfc[$k]['time'] = $val['day_time']*1000;
            $asdfc[$k]['volume'] = $val['number'];
            $asdfc[$k]['close'] = $val['end_price'];
            $asdfc[$k]['high'] = $val['highest'];
            $asdfc[$k]['low'] = $val['mminimum'];
            $asdfc[$k]['open'] = $val['start_price'];
        }
        $this->success('',array_reverse($asdfc));
    }

}
