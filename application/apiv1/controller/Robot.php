<?php

namespace app\apiv1\controller;

use service\DataService;
use service\WechatServicew;
use controller\BasicIndex;
use think\Facade\Cache;
use think\Db;

class Robot extends BasicIndex
{
    protected $MiniApp;

    public function __construct()
    {
        parent::__construct();
        //$this->MiniApp = new \Smallsha\Classes\MiniApp(config('miniapp.appid'), config('miniapp.app_secret'));
    }

  /*
  * 机器人下单
  * */
    public function handle(){
        $auto = Db::name('auto_robot')
            ->where(['is_start' =>1])
            ->find();
        if(!$auto) {
            $this->error('机器人已关闭');
        }else {
            $price_precision = $auto['price_precision'];//价格精准度
            $num_precision = $auto['num_precision'];//数量精度
            $rand_price = randomFloat($auto['up_price'],$auto['down_price']);
            $rand_price = number_format($rand_price,$price_precision);
            //获取当前价格
            $price_area = $this->getPriceArea($auto['id'],$rand_price);

            //数量
            $num = number_format(randomFloat($auto['min_number'],$auto['max_number']),$num_precision);
            $now = time();
            if (!empty($price_area)){
                $ts_Arr = [
                    'user_id' => $auto['buy_user_id'],
                    'from_user_id'=>$auto['buy_user_id'],
                    'price' => $price_area,
                    'number' =>$num,
                    'create_time' =>$now,
                    'currency' => $auto['currency_id'],
                    'legal' => $auto['legal_id'],
                    'state' =>'1',//机器人跑的
                ];
                $res[] = Db::name('transaction_complete')->insert($ts_Arr);
                //创建时分线
                $this->batchWriteMarketData($auto['currency_id'],$auto['legal_id'],$num,$price_area,'',$now);

            }else{
                $this->error('没有当前价格区间');
            }
            //最小下单频率 最大下单频率
            $need_second=mt_rand($auto['min_need_second'],$auto['max_need_second']);
            //$this->info('机器人沉默:'.$need_second.'秒');
            sleep($need_second);
        }
    }

    /**
     * 批量写入行情数据
     *
     * @param integer $currency_id 币种ID
     * @param integer $legal_id 法币ID
     * @param float $num 成交数量
     * @param float $price 成交价
     * @param integer $sign 来源标记[0.默认,1.交易更新,2.接口,3.后台添加
     * @param integer|null $time 时间戳
     * @param bool $cumulation 是否累计交易量,默认累计
     * @return void
     */
    public function batchWriteMarketData($currency_id, $legal_id, $num, $price, $sign = 0, $time = null, $cumulation = true)
    {
        //$type类型:1.15分钟,2.1小时,3.4小时,4.一天,5.分时,6.5分钟，7.30分钟,8.一周,9.一月,10.一年
        empty($time) && $time = time();
        $types = [5, 1, 6, 7, 2, 4, 8, 9];
        $start = microtime(true);
        //写入行情数据

        foreach ($types as $value) {
            $data = [];
            $new_data = [];
            $times = self::formatTimeline($value, $time);
            //print_r($times);
            
//             $last_time = db::name('market_hour')
//                 ->where('day_time', '=' , $times)
//                 ->where('currency_id', $currency_id)
//                 ->where('legal_id', $legal_id)
//                 ->where('type', $value)
//                 ->order('day_time','desc')
//                 ->find();
            $market_hour = db::name('market_hour')->where(['currency_id'=>$currency_id,'day_time'=>$times,'period'=>period($value)])->find();
            
            $last_time = db::name('market_hour')->where('type', $value)
                ->where('day_time', '<>' , $times)
                ->where('currency_id', $currency_id)
                ->where('legal_id', $legal_id)
                ->order('day_time','desc')
                ->find();
                if(!empty($last_time)){
                    if(bccomp($last_time->start_price, $last_time->end_price)<0){
                        $data['start_price'] = $last_time['end_price'];
                    }else{
                        $data['start_price'] = $last_time['end_price'];
                    }
                }else{
                    $data['start_price'] = $price;
                }
                
                
                //bc_comp($timeline->start_price, 0) <= 0 && $data['start_price'] = $price;
                $data['end_price'] = $price;
                
            //如果是存在的话更新某个字段
            if(!empty($market_hour)){
                
                if($market_hour['highest']< $price){
                     $data['highest'] = $price;
                }
                if ($market_hour['mminimum']>$price) {
                    $data['mminimum'] = $price;
                }
                //最高价和最低价
                if(isset($data['highest']) &&  $market_hour['highest'] < $data['highest']){
                    $new_highest = $data['highest'];
                }else{
                    $new_highest = $market_hour['highest'];
                }
                if(isset($data['mminimum']) && $market_hour['mminimum'] > $data['mminimum']){
                    $new_mminimum = $price;
                }else{
                    $new_mminimum = $market_hour['mminimum'];
                }
                $data['end_price'] = $price;
                db::name('market_hour')->where(['id'=>$market_hour['id']])->update($data);
                //累加交易量
                db::name('market_hour')->where(['id'=>$market_hour['id']])->setInc('number',$num);
                //封装新数据
                $new_data['currency_id'] = $currency_id;
                $new_data['legal_id'] = $legal_id;
                $new_data['start_price'] = $data['start_price'];
                $new_data['end_price'] =  $data['end_price'];
                $new_data['mminimum'] = $new_mminimum;
                $new_data['highest'] = $new_highest;
                $new_data['day_time'] = $times;
                $new_data['type'] = $market_hour['type'];
                $new_data['number'] = $market_hour['number']+$num;
                $new_data['period'] = $market_hour['period'];
            }else{
                //如果是不存在的话，创建
                $data['currency_id'] = $currency_id;
                $data['legal_id'] = $legal_id;
                $data['start_price'] = $price;
                $data['end_price'] = $price;
                $data['mminimum'] = $price;
                $data['highest'] = $price;
                $data['day_time'] = $times;
                $data['type'] = $value;
                $data['number'] = $num;//交易数量
                $data['period'] =period($value);
                Db::name('market_hour')->insert($data);
                //封装新数据
                $new_data['currency_id'] = $currency_id;
                $new_data['legal_id'] = $legal_id;
                $new_data['start_price'] = $price;
                $new_data['end_price'] =  $price;
                $new_data['mminimum'] = $price;
                $new_data['highest'] = $price;
                $new_data['day_time'] = $times;
                $new_data['type'] = $value;
                $new_data['number'] = $num;
                $new_data['period'] = period($value);
            }
            
            if($value == 5){
                //行情
                //该交易对当天0点的交易行情。
                $time_five = strtotime(date("Y-m-d 00:00:00")); //获取今天0点的时间戳
                $day_Data = DB::table('market_hour')->where('currency_id', 1)
                ->where('legal_id', 4)
                ->where('period', '1day')
                ->where('day_time', '<', $time_five)
                ->where('end_price', '>', '0.00000')
                ->order('id', 'DESC')
                ->find();
                //当天0点的成交价
                if (!empty($day_Data)) {
                    $_zero_price = $day_Data['end_price'];
                } else {
                    $day_Data = DB::table('market_hour')->where('currency_id', 1)
                    ->where('legal_id', 4)
                    ->where('period', '1min')
                    ->find();
                    if (!empty($day_Data)) {
                        $_zero_price = $day_Data['start_price'];
                    }else{
                        $_zero_price = 0;
                    }
                }
                
                $coinnum = DB::table('market_hour')->where('day_time', '>', $time_five)
                ->where('currency_id', 1)
                ->where('legal_id',4)
                ->where('period', '1min')
                ->sum('number');
                $coin["price"] = bcsub($new_data["end_price"],0,4);
                if($_zero_price == 0){
                    $coin["change"] = "+0.000";
                }else{
                    $coin["change"] = bcmul(bcdiv(bcsub($data["end_price"],$_zero_price,2),$data["end_price"],6),100,4);
                }
                $foc = Db::name('system_coin')->where('id',1)->find();
                //最高价和最低价
                if($new_data['highest'] < $foc['high']){
                    $new_high = $foc['high'];
                }else{
                    $new_high = $new_data['highest'];
                }
                if($new_data['mminimum'] > $foc['low']){
                    $new_mmin = $foc['low'];
                }else{
                    $new_mmin = $new_data['mminimum'];
                }
                $coin["high"] = bcsub($new_high,0,4);
                $coin["low"] = bcsub($new_mmin,0,4);
                $coin["amount"] = $coinnum;
                Db::name('system_coin')->where('id',1)->update($coin);
                $new_data1['add_time'] = time();
                $new_data1['change'] = $coin["change"]>0?$coin["change"]:-$coin["change"];
                $new_data1['currency_id'] = 1;
                $new_data1['currency_name'] = 'FOC';
                $new_data1['high'] = $coin["high"];
                $new_data1['legal_id'] = 4;
                $new_data1['legal_name'] = 'USDT';
                $new_data1['low'] = $coin["low"];
                $new_data1['now_price'] = $coin["price"];
                $new_data1['symbol'] = 'FOC/USDT';
                $new_data1['type'] = 'daymarket';
                $new_data1['volume'] = $coin["amount"];
                $this->jiaoyiorderlist();
                $this->writeMarketData('daymarket',$new_data1);
            }
            //进行推送
            //$tuisong = new Tuisong();
             $new_data1 = [
                'type' => 'kline',
                'period' => $new_data['period'],
                'currency_id' => $new_data['currency_id'],
                'currency_name' => "FOC",
                'legal_name' => "USDT",
                'symbol' => "FOC/USDT",
                'open' => $new_data['start_price'],
                'close' => $new_data['end_price'],
                'high' => $new_data['highest'],
                'low' => $new_data['mminimum'],
                'volume' => $new_data['number'],
                'time' => $new_data['day_time'] * 1000,
            ];
            //halt($new_data1);
            //$this->writeMarketData($new_data['period'],$new_data1);
            $this->writeMarketData('kline',$new_data1);

        }
    }
    public function testpush()
    {
        $new_data1 = [
            'type' => 'kline',
            'period' => 1,
            'currency_id' => 1,
            'currency_name' => "NAC",
            'legal_name' => "NF",
            'symbol' => "NF/USDT",
            'open' => 20,
            'close' => 20.56,
            'high' => 25,
            'low' => 18,
            'volume' => 25555,
            'time' => 1000 * 1000,
        ];
        $this->writeMarketData('kline',$new_data1);
    }
    
    /*
     * 交易量和交易价格
     * */
    public function jiaoyiorderlist(){
        $status = input('param.status','1');//1 全部 2有效
        $usdt_rmb = sysconf('usdt_rmb');
        $xian_foc = sysconf('jiaoyi_foc');//1FOC价值USDT限价
        $jiaoyi_foc= db::name('system_coin')->where('id',1)->find();
//         $byorder = db::name('jys_buylist')->where('endtime','<>',0)->order('endtime','desc')->find();
//         $sellorder = db::name('jys_selllist')->where('endtime','<>',0)->order('endtime','desc')->find();
        //         if(!$byorder && !$sellorder) {
        //             $price = $jiaoyi_foc;
        //         }elseif(!$byorder){
        //             $price = $sellorder['price'];
        //         }elseif (!$sellorder){
        //             $price = $byorder['price'];
        //         }else{
        //             if($byorder['endtime']>$sellorder['endtime']){
        //                 $price = $byorder['price'];
        //             }else{
        //                 $price = $sellorder['price'];
        //             }
        //         }
        $buylist = db::name('jys_buylist')->where('state','0')->field("price,sum(leavenum) as num")->order('price desc')->group('price')->limit(5)->select();
        $selllist = db::name('jys_selllist')->where('state','0')->field("price,sum(leavenum) as num")->order('price asc')->group('price')->limit(5)->select();
        if(sysconf('open_shijia') == 1){
            $price = $jiaoyi_foc['price'];
        }else{
            $price = $xian_foc;
        }
        $cny = bcmul($usdt_rmb,$price,4);
        //各显示（10条）
//         $buylist = db::name('jys_price')->where(['type'=>0])->where('status',$status)->order('price desc')->limit(5)->select();
//         $selllist = db::name('jys_price')->where(['type'=>1])->where('status',$status)->order('price asc')->limit(5)->select();
        $new_data = ['buylist'=>$buylist,'selllist'=>$selllist,'price'=>$price,'cny_price'=>$cny,'lastprice'=>'0.200000','timeprice'=>'0.100000'];
        $this->writeMarketData('transaction',$new_data);
    }
    
    /**
     * 拼装交易数据
     *
     * @param Sting $type [kline]
     * @param mixed $data
     * @return bool|string
     */
    function writeMarketData($type, $data)
    {
        // do some thing
        $pushUrl = "http://127.0.0.1:5678";
        $postData = [
            'event' => $type,
            'msg' => is_array($data) ? json_encode($data) : $data
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $pushUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:"));
        $return = curl_exec($ch);
        curl_close($ch);

        return $return;
    }

    //获取最新价格
    public function getPriceArea($id,$rand_price){

        $auto = Db::name('auto_robot')->where(['is_start' =>1,'id'=>$id])->find();
        $min_price=$auto['min_price'];//随机数数最小
        $max_price=$auto['max_price'];//随机数数最大
        //控制涨跌
        $up_or_down = 0;
        $w = $auto['up_weight']+$auto['down_weight'];

        $rand_w = mt_rand(1,$w);

        if($rand_w < $auto['up_weight']){
            $up_or_down = 1;//涨
        }else{
            $up_or_down = 0;//跌
        }

        $init_price=0;
        //成交最高价
        $transaction = db::name('transaction_complete')->order('create_time','desc')->find();
        if(!empty($transaction)){
            $init_price=$transaction['price'];
            //init_price初始价格
            //成交的价格小于初始价格
            if($init_price < $auto['init_price']){
                //就按初始价格走
                $init_price=$auto['init_price'];
            }
        }else{
            $init_price=$auto['init_price'];
        }
        if($init_price>0){
            if($up_or_down == 1){
                //涨
                $new_price = $init_price+$rand_price;
            }else{
                //跌
                $new_price = $init_price-$rand_price;
            }
            //var_dump($new_price);

//             if($new_price > $max_price){
//                 //现在算出来的价格大于随机最大值的话，价格进行翻转
//                 $new_price = $init_price-$rand_price;
//             }
//             if($new_price < $min_price){
//                 //现在算出来的价格小于随机最小值的话，价格进行翻转
//                 $new_price = $init_price+$rand_price;
//             }
            if($new_price > $max_price){
                //现在算出来的价格大于随机最大值的话，价格进行翻转
                $new_price = $max_price;
            }
            if($new_price < $min_price){
                //现在算出来的价格小于随机最小值的话，价格进行翻转
                $new_price = $min_price;
            }
//            var_dump($new_price);
//            exit;
        }
        return $new_price;
    }

    /**
     * 取指定类型时间线实例,如果没有自动创建
     *
     * @param integer $type 类型:[1.15分钟,2.1小时,3.4小时,4.一天,5.分时,6.5分钟，7.30分钟,8.一周,9.一月]
     * @param integer $currency_id 币种id
     * @param integer $legal_id 法币id
     * @param integer $sign 来源标记[0.默认,1.交易更新,2.接口,3.后台添加
     * @param integer $day_time 时间戳
     * @return void
     */
    public static function getTimelineInstance($type, $currency_id, $legal_id, $sign = 0, $day_time = null)
    {
        empty($day_time) && $day_time = time();
        $time = self::formatTimeline($type, $day_time);
        $timeline = self::where('type', $type)
            ->where('day_time', $time)
            ->where('currency_id', $currency_id)
            ->where('legal_id', $legal_id)
            ->orderby('day_time','desc')
            ->first();
        if (!$timeline) {
            $timeline = self::makeTimelineData($type, $currency_id, $legal_id, $sign, $day_time);
        } else {
            $timeline->sign = $sign;
        }
        return $timeline;
    }

    /**
     * 按类型格式化时间线
     *
     * @param integer $type 类型:1.15分钟,2.1小时,3.一年,4.一天,5.分时,6.5分钟，7.30分钟,8.一周,9.一月,10.4小时
     * @param integer $day_time 时间戳,不传将默认采用当前时间
     * @return void
     * 根据类型，把时间戳进行一个1分钟之内，5分钟之内等
     */

    public function formatTimeline($type=10, $day_time ='1597825382')
    {
        empty($day_time) && $day_time = time();
        switch ($type) {
            //15分钟
            case 1:
                $start_time = strtotime(date('Y-m-d H:00:00', $day_time));
                $minute = intval(date('i', $day_time));
                $multiple = floor($minute / 15);
                $minute = $multiple * 15;
                $time = $start_time + $minute * 60;
                break;
            //1小时
            case 2:
                $time = strtotime(date('Y-m-d H:00:00', $day_time));
                break;
            //4小时
            case 3:
                $start_time = strtotime(date('Y-m-d', $day_time));
                $hours = intval(date('H', $day_time));
                $multiple = floor($hours / 4);
                $hours = $multiple * 4;
                $time = $start_time + $hours * 3600;
                break;
            //一天
            case 4:
                $time = strtotime(date('Y-m-d', $day_time));
                break;
            //分时
            case 5:
                $time_string = date('Y-m-d H:i', $day_time);
                $time = strtotime($time_string);
                break;
            //5分钟
            case 6:
                $start_time = strtotime(date('Y-m-d H:00:00', $day_time));
                $minute = intval(date('i', $day_time));
                $multiple = floor($minute / 5);
                $minute = $multiple * 5;
                $time = $start_time + $minute * 60;
                break;
            //30分钟
            case 7:
                $start_time = strtotime(date('Y-m-d H:00:00', $day_time));
                $minute = intval(date('i', $day_time));
                $multiple = floor($minute / 30);
                $minute = $multiple * 30;
                $time = $start_time + $minute * 60;
                break;
            //一周
            case 8:
                $start_time = strtotime(date('Y-m-d', $day_time));
                $week = intval(date('w', $day_time));
                $diff_day = $week;
                $time = $start_time - $diff_day * 86400;
                break;
            //一月
            case 9:
                $time_string = date('Y-m', $day_time);
                $time = strtotime($time_string);
                break;
            //一年
            case 10:
                $time = strtotime(date('Y-01-01', $day_time));
                break;
            default:
                $time = $day_time;
                break;
        }
        return $time;
    }
}