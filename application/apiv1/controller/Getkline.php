<?php

namespace app\apiv1\controller;

use think\Db;
use controller\BasicIndex;

// 定义参数
defined('ACCOUNT_ID') or define('ACCOUNT_ID', '128103598'); // 你的账户ID
defined('ACCESS_KEY') or define('ACCESS_KEY', '33a05b7f-ez2xc4vb6n-75910d51-a9fb6'); // 你的ACCESS_KEY
defined('SECRET_KEY') or define('SECRET_KEY', 'c245c84d-87a654ab-f056ffb0-0eaf7'); // 你的SECRET_KEY


class Getkline extends BasicIndex
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get_kline_data';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取实时K线图数据';
    
    // 定义参数
    //    const ACCOUNT_ID = 50154012; // 你的账户ID
    //    const ACCESS_KEY = 'c96392eb-b7c57373-f646c2ef-25a14'; // 你的ACCESS_KEY
    //    const SECRET_KEY = ''; // 你的SECRET_KEY
    
    private $url = 'https://api.huobi.br.com'; //'https://api.huobi.pro';
    private $api = '';
    public $api_method = '';
    public $req_method = '';
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        // header('Content-Type: text/html; charset=utf-8');
        parent::__construct();
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        echo ("开始推送\n\r");
        $all_arr= array(
            '2'=>array('id'=>2,'name'=>'BTC'),
            '3'=>array('id'=>3,'name'=>'ETH'),
            '5'=>array('id'=>5,'name'=>'EOS'),
            '6'=>array('id'=>6,'name'=>'LTC'),
            '7'=>array('id'=>7,'name'=>'XRP'),
            '8'=>array('id'=>8,'name'=>'BCH')
        );//,
        $legal_arr = array('4'=>array('id'=>4,'name'=>'USDT'));
        
        //拼接所有的交易对
        $ar = [];
        foreach ($legal_arr as $legal) {
            foreach ($all_arr as $item) {
                if ($legal['id'] != $item['id']) {
                    echo ("begin2");
                    $ar_a = [];
                    $ar_a['name'] = strtolower($item['name']) . strtolower($legal['name']);
                    $ar_a['currency_id'] = $item['id'];
                    $ar_a['legal_id'] = $legal['id'];
                    $ar[] = $ar_a;
                }
            }
        }
        //获取火币交易平台上面的有数据的交易对
        $kko = json_decode($this->curl('https://api.huobi.br.com/v1/common/symbols'), true);
        if ($kko['status'] != 'ok') {
            echo ("begin3");
            $this->error('请求出错');
            return false;
        }
        
        $trade = array_column($kko['data'], 'symbol');
        //file_put_contents("ar_new.txt", json_encode($ar_new) . PHP_EOL, FILE_APPEND);
        foreach ($ar as $it) {
            
            echo ("遍历币种开始\n\r");
            //不在火币交易对中直接跳过
            if (!in_array($it['name'], $trade)) {
                echo ("begin5");
                $this->error('不在火币交易对中直接跳过-' . $it['name']);
                continue;
            }
            $data = array();
            echo ("开始请求\n\r");
            $data = $this->get_history_kline($it['name'], '1min', 1);
            if ($data) {
                
            } else {
                echo ("重新采集\n\r");
                // sleep(5);
                continue;
            }
            echo ("请求结束\n\r");
            //请求失败直接跳过
            if ($data['status'] != 'ok') {
                echo ("begin6");
                $this->error('请求失败');
                continue;
            }
            $info = $data['data'][0];
            if(empty($info['open'])&&empty($info['close'])&&empty($info['high'])&&empty($info['high'])){
                //如果指行情价格为空,直接跳过
                echo ("begin000");
                continue;
            }
            //每分钟时间戳
            
            $insert_instance = DB::table('market_hour')->where('currency_id', $it['currency_id'])
            ->where('legal_id', $it['legal_id'])
            ->where('day_time', '=', $info['id'])
            ->where('type', 5)
            ->find();
            $update_Data = array();
            $update_Data['start_price'] = $this->sctonum($info['open']);
            $update_Data['end_price'] = $this->sctonum($info['close']);
            $update_Data['mminimum'] = $this->sctonum($info['low']);
            $update_Data['highest'] = $this->sctonum($info['high']);
            $update_Data['number'] = bcmul($info['amount'], 1, 5);
            $insert_Data = array();
            $insert_Data['currency_id'] = $it['currency_id'];
            $insert_Data['legal_id'] = $it['legal_id'];
            $insert_Data['start_price'] = $this->sctonum($info['open']);
            $insert_Data['end_price'] = $this->sctonum($info['close']);
            $insert_Data['mminimum'] = $this->sctonum($info['low']);
            $insert_Data['highest'] = $this->sctonum($info['high']);
            $insert_Data['type'] = 5;
            $insert_Data['sign'] = 2;
            $insert_Data['day_time'] = $info['id'];
            $insert_Data['period'] = '1min';
            $insert_Data['number'] = bcmul($info['amount'], 1, 5);
            $insert_Data['mar_id'] = $info['id'];
            if ($insert_instance) {
                $id=$insert_instance['id'];
                //如果指定时间行情已存在,直接跳过
                echo ("begin7");
                try {
                    DB::table('market_hour')->where('id',$id)->update($update_Data);
                } catch (\Exception $e) {
                    continue;
                }
                //                 $this->error('指定时间行情已存在,直接跳过');
                //                 continue;
            }else{
                
                try {
                    echo 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
                    DB::table('market_hour')->insert($insert_Data);
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            $update_Data = []; //每分钟获取到的最新的交易行情
            $update_Data['currency_id'] = $it['currency_id'];
            $update_Data['legal_id'] = $it['legal_id'];
            $update_Data['now_price'] = $this->sctonum($info['close']);
            $update_Data['add_time'] = time();
            $update_Data['volume'] = '0.00000';
            $update_Data['change'] = '+0.00';
            
            //该交易对当天0点的交易行情。
            $time = strtotime(date("Y-m-d")); //获取今天0点的时间戳
            $day_Data = DB::table('market_hour')->where('currency_id', $it['currency_id'])
            ->where('legal_id', $it['legal_id'])
            ->where('period', '1day')
            ->where('sign', 2)
            ->where('day_time', '<=', $time)
            ->where('end_price', '>', '0.00000')
            ->order('id', 'DESC')
            ->find();
            //当天0点的成交价
            if (!empty($day_Data)) {
                $_zero_price = $day_Data['end_price'];
            } else {
                $_zero_price = 0;
            }
            
            $update_Data['volume'] = DB::table('market_hour')->where('day_time', '>', $time)
            ->where('currency_id', $it['currency_id'])
            ->where('legal_id', $it['legal_id'])
            ->where('period', '1min')
            ->where('sign', 2)
            ->sum('number');
            
            switch (bccomp($update_Data['now_price'], $_zero_price, 5)) {
                case 1:
                    if ($_zero_price === 0) {
                        $update_Data['change'] = '+0.000';
                    } else {
                        $a = bcsub($update_Data['now_price'], $_zero_price, 5);
                        $_pencet_num = bcdiv($a, $_zero_price, 5);
                        $update_Data['change'] = '+' . bcmul($_pencet_num, 100, 3);
                    }
                    break;
                    
                case 0:
                    $update_Data['change'] = '+0.000';
                    break;
                    
                case -1:
                    if ($_zero_price === 0) {
                        $update_Data['change'] = '+0.000';
                    } else {
                        $a = bcsub($_zero_price, $update_Data['now_price'], 5);
                        $_pencet_num = bcdiv($a, $_zero_price, 5);
                        $update_Data['change'] = '-' . bcmul($_pencet_num, 100, 3);
                    }
                    break;
                    
                default:
                    $update_Data['change'] = '+0.000';
            }
            
            //             $que_data = DB::table('currency_quotation')
            //             ->where('currency_id', $it['currency_id'])
            //             ->where('legal_id', $it['legal_id'])
            //             ->orderby('id', 'DESC')->first();
            //             if (!empty($que_data)) {
            //                 DB::table('currency_quotation')->where('id', $que_data->id)->update($update_Data);
            //             } else {
            //                 try {
            //                     DB::table('currency_quotation')->insert($update_Data);
            //                 } catch (\Exception $e) {
            //                     continue;
            //                 }
            
            //             }
            $currency = $all_arr[$it['currency_id']];
            $legal = $legal_arr[$it['legal_id']];
            $update_Data['currency_name'] = $currency['name'];
            $update_Data['legal_name'] = $legal['name'];
            $update_Data['type'] = 'daymarket';
            $update_Data['high'] = $insert_Data['highest'];
            $update_Data['low'] = $this->sctonum($info['low']);
            $update_Data['symbol'] = $currency['name'] . '/' . $legal['name'];
            
            //推送K线行情
            echo ("begin8");
            $new_data = [
                'type' => 'kline',
                'period' => $insert_Data['period'],
                'currency_id' => $insert_Data['currency_id'],
                'currency_name' => $currency['name'],
                'legal_id' => $insert_Data['legal_id'],
                'legal_name' => $legal['name'],
                'symbol' => $currency['name'] . '/' . $legal['name'],
                'open' => $insert_Data['start_price'],
                'close' => $insert_Data['end_price'],
                'high' => $insert_Data['highest'],
                'low' => $insert_Data['mminimum'],
                'volume' => $insert_Data['number'],
                'time' => $insert_Data['day_time'] * 1000,
            ];
            echo ("开始推送\n\r");
            $this->writeMarketData('daymarket',$update_Data);
            $this->writeMarketData('kline',$new_data);
            unset($currency);
            unset($legal);
            echo ("遍历币种结束\n\r");
        }
    }
    
    public function five_min()
    {
        echo ("600_begin\n\r");
        $type = input('get.min');
        $period = [
            5 => "1min",
            6 => "5min",
            1 => "15min",
            7 => "30min",
            2 => "60min",
            3 => "4hour",
            4 => "1day",
            8 => "1week",
            9 => "1mon",
            10 => "1year",
        ];
        $min = $period[$type];
        $all_arr= array(
            '2'=>array('id'=>2,'name'=>'BTC'),
            '3'=>array('id'=>3,'name'=>'ETH'),
            '5'=>array('id'=>5,'name'=>'EOS'),
            '6'=>array('id'=>6,'name'=>'LTC'),
            '7'=>array('id'=>7,'name'=>'XRP'),
            '8'=>array('id'=>8,'name'=>'BCH')
        );//,
        $legal_arr = array('4'=>array('id'=>4,'name'=>'USDT'));
        //拼接所有的交易对
        $ar = [];
        foreach ($legal_arr as $legal) {
            foreach ($all_arr as $item) {
                if ($legal['id'] != $item['id']) {
                    $ar_a = [];
                    $ar_a['name'] = strtolower($item['name']) . strtolower($legal['name']);
                    $ar_a['currency_id'] = $item['id'];
                    $ar_a['legal_id'] = $legal['id'];
                    $ar[] = $ar_a;
                }
            }
        }
        echo ("600_ready\n\r");
        //获取火币交易平台上面的有数据的交易对
        $kko = json_decode($this->curl('https://api.huobi.br.com/v1/common/symbols'), true);
        
        if ($kko['status'] == 'ok') {
            
            $trade = [];
            foreach ($kko['data'] as $key => $value) {
                $trade[] = $value['symbol'];
            }
            foreach ($ar as $it) {
                if (in_array($it['name'], $trade)) {
                    $data = array();
                    $data = $this->get_history_kline($it['name'], $min, 1);
                    if ($data['status'] != 'ok') {
                        echo ("600_fail\n\r");
                        continue;
                    }
                    $info = $data['data'][0];
                    if(empty($info['open'])&&empty($info['close'])&&empty($info['high'])&&empty($info['high'])){
                        //如果指行情价格为空,直接跳过
                        echo ("600_empty\n\r");
                        continue;
                    }
                    $insert_instance = DB::table('market_hour')->where('currency_id', $it['currency_id'])
                    ->where('legal_id', $it['legal_id'])
                    ->where('day_time', '=', $info['id'])
                    ->where('type', $type)
                    ->find();
                    
                    $update_Data = array();
                    $update_Data['start_price'] = $this->sctonum($info['open']);
                    $update_Data['end_price'] = $this->sctonum($info['close']);
                    $update_Data['mminimum'] = $this->sctonum($info['low']);
                    $update_Data['highest'] = $this->sctonum($info['high']);
                    $update_Data['number'] = bcmul($info['amount'], 1, 5);
                    
                    $insert_Data = array();
                    $insert_Data['currency_id'] = $it['currency_id'];
                    $insert_Data['legal_id'] = $it['legal_id'];
                    $insert_Data['start_price'] = $this->sctonum($info['open']);
                    $insert_Data['end_price'] = $this->sctonum($info['close']);
                    $insert_Data['mminimum'] = $this->sctonum($info['low']);
                    $insert_Data['highest'] = $this->sctonum($info['high']);
                    $insert_Data['type'] = $type;
                    $insert_Data['sign'] = 2;
                    $insert_Data['day_time'] = $info['id'];
                    $insert_Data['period'] = $min;
                    $insert_Data['number'] = bcmul($info['amount'], 1, 5);
                    $insert_Data['mar_id'] = $info['id'];
                    if (!empty($insert_instance)) {
                        $id=$insert_instance['id'];
                        //如果指定时间行情已存在,直接跳过
                        echo ("600_update\n\r");
                        try {
                            DB::table('market_hour')->where('id',$id)->update($update_Data);
                        } catch (\Exception $e) {
                            continue;
                        }
                    }else{
                        echo ("600_insert\n\r");
                        //print_r($insert_Data);
                        try {
                            DB::table('market_hour')->insert($insert_Data);
                        } catch (\Exception $e) {
                            continue;
                        }
                        
                    }
                    $currency = $all_arr[$it['currency_id']];
                    $legal = $legal_arr[$it['legal_id']];
                    
                    //推送K线行情
                    echo ("begin600");
                    $new_data = [
                        'type' => 'kline',
                        'period' => $insert_Data['period'],
                        'currency_id' => $insert_Data['currency_id'],
                        'currency_name' => $currency['name'],
                        'legal_id' => $insert_Data['legal_id'],
                        'legal_name' => $legal['name'],
                        'symbol' => $currency['name'] . '/' . $legal['name'],
                        'open' => $insert_Data['start_price'],
                        'close' => $insert_Data['end_price'],
                        'high' => $insert_Data['highest'],
                        'low' => $insert_Data['mminimum'],
                        'volume' => $insert_Data['number'],
                        'time' => $insert_Data['day_time'] * 1000,
                    ];
                    echo ("开始推送\n\r");
                    $this->writeMarketData('kline',$new_data);
                }
            }
            echo ("600_finish\n\r");
        }
        echo ("600_end\n\r");
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
    
    /**对象转数组
     * @param $obj
     * @return mixed
     */
    public function object2array($obj)
    {
        return json_decode(json_encode($obj), true);
    }
    
    //科学计算发转字符串
    public function sctonum($num, $double = 8)
    {
        if (false !== stripos($num, "e")) {
            $a = explode("e", strtolower($num));
            return bcmul($a[0], bcpow(10, $a[1], $double), $double);
        } else {
            return $num;
        }
    }
    
    //
    //    /**
    //     * 行情类API
    //     */
    //    // 获取K线数据
    public function get_history_kline($symbol = '', $period = '', $size = 0)
    {
        echo ("获取K线数据\n\r");
        $this->api_method = "/market/history/kline";
        $this->req_method = 'GET';
        $param = [
            'symbol' => $symbol,
            'period' => $period,
        ];
        if ($size) {
            $param['size'] = $size;
        }
        
        $url = $this->create_sign_url($param);
        //echo($url);
//         file_put_contents("log.txt", $url . PHP_EOL, FILE_APPEND);
        echo ("获取K线数据结束\n\r");
        return json_decode($this->curl($url), true);
        //return json_decode(file_get_contents($url), true);
    }
    //    /**
    //     * 类库方法
    //     */
    //    // 生成验签URL
    public function create_sign_url($append_param = [])
    {
        // 验签参数
        $param = [
            'AccessKeyId' => ACCESS_KEY,
            'SignatureMethod' => 'HmacSHA256',
            'SignatureVersion' => 2,
            'Timestamp' => date('Y-m-d\TH:i:s', time()),
        ];
        if ($append_param) {
            foreach ($append_param as $k => $ap) {
                $param[$k] = $ap;
            }
        }
        return $this->url . $this->api_method . '?' . $this->bind_param($param);
    }
    //    // 组合参数
    public function bind_param($param)
    {
        $u = [];
        $sort_rank = [];
        foreach ($param as $k => $v) {
            $u[] = $k . "=" . urlencode($v);
            $sort_rank[] = ord($k);
        }
        asort($u);
        $u[] = "Signature=" . urlencode($this->create_sig($u));
        return implode('&', $u);
    }
    //    // 生成签名
    public function create_sig($param)
    {
        $sign_param_1 = $this->req_method . "\n" . $this->api . "\n" . $this->api_method . "\n" . implode('&', $param);
        $signature = hash_hmac('sha256', $sign_param_1, SECRET_KEY, true);
        return base64_encode($signature);
    }
    public function curl($url, $postdata = [])
    {
        
        echo ("curl开始\n\r");
        $start = microtime(true);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($this->req_method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        // curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:'.$this->Rand_IP(), 'CLIENT-IP:'.$this->Rand_IP()));
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
        ]);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        
        if (empty($output)) {
            echo ("curl没有采集到\n\r");
        };
        echo ("curl结束\n\r");
        $end = microtime(true);
        file_put_contents("haoshi.txt", ($end - $start) . PHP_EOL, FILE_APPEND);
        // echo "\t" . date('Y-m-d H:i:s') . $value->symbol . '处理完成,耗时' .($end - $start) . '秒' . PHP_EOL;
        //  print_r($output);
        //  echo("\n\r");
        // print_r($info);
        return $output;
    }
    
}
