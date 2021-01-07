<?php

namespace app\apiv1\controller;

use service\DataService;
use service\WechatService;
use controller\BasicIndex;
use think\facade\Cache;
use think\Db;

class Common extends BasicIndex
{
    const Wallet = [
        'BTT比特流' => 'https://data.gateio.co/api2/1/ticker/btt_usdt',
        'BTC比特币' => 'https://data.gateio.co/api2/1/ticker/btc_cnyx',
        'ETH以太坊' => 'https://data.gateio.co/api2/1/ticker/eth_cnyx',
        'EOS柚子' => 'https://data.gateio.co/api2/1/ticker/eos_cnyx'
    ];
    const DOLLAR_RATE = 6.7145;
    public $user;
    public function __construct()
    {
        parent::__construct();
        $token = request()->header('token','');
        $this->user = Db::name('store_member')->where(['wx_token'=>$token])->find();
    }
    /*
     * 进行图片上传
     * */
    public function fileImage()
    {
        $language =  language(Cache::get('lan_type'),'common','fileImage');
        $image = request()->post('image');
        if ($image) {
            $res = $this->saveBase64Image($image);
            return $this->success('',$res);
        } else {
            return $this->error($language['scsb']);
        }
    }

    function saveBase64Image( $base64_image_content )
    {
        
        $language =  language(Cache::get('lan_type'),'common','saveBase64Image');

        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {

            //图片后缀
            $type = $result[2];
            if ($type == 'jpeg')
                $type = 'jpg';
            if ($type == 'png')
                $type = 'png';
            $ext = strtolower(sysconf('storage_local_exts'));

            if (is_string($ext)) {
                $ext = explode(',', $ext);
            }

            if (!in_array($type, $ext)) {
                $data['code'] = 0;
                $data['imgageName'] = '';
                $data['image_url'] = '';
                $data['type'] = '';
                $data['msg'] = '不允许上传的文件类型';
                return $data;
            }

            //保存位置--图片名
            $image_name = date('His') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT) . "." . $type;
            $iamge_dir = 'upload/' . date('Ymd');
            $image_url = $iamge_dir . $image_name;
            if (!is_dir(dirname('./' . $iamge_dir))) {
                mkdir(dirname('./' . $iamge_dir));
                chmod(dirname('./' . $iamge_dir), 0777);
            }

            //解码
            $decode = base64_decode(str_replace($result[1], '', $base64_image_content));
            if (file_put_contents('./' . $image_url, $decode)) {
                $appRoot = request()->root();  // 去掉参数 true 将获得相对地址
                $uriRoot = preg_match('/\.php$/', $appRoot) ? dirname($appRoot) : $appRoot;
                $uriRoot = in_array($uriRoot, ['/', '\\']) ? '' : $uriRoot;
                $url = $_SERVER['HTTP_HOST'];
                $data['code'] = 1;
                $data['imageName'] = $image_name;
                $data['image_url'] = 'http://'.$url.$uriRoot . '/' . $image_url;
                $data['type'] = $type;
                $data['msg'] = '保存成功！';
            } else {
                $data['code'] = 0;
                $data['imgageName'] = '';
                $data['image_url'] = '';
                $data['type'] = '';
                $data['msg'] = '图片保存失败！';
            }
        } else {
            $data['code'] = 0;
            $data['imgageName'] = '';
            $data['image_url'] = '';
            $data['type'] = '';
            $data['msg'] = 'base64图片格式有误！';
        }
        return $data;
    }


    public function fileUploader()
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');

        // 移动到框架应用根目录/public/uploads/ 目录下
        if ($file) {
            $info = $file->move(ROOT_PATH . '/upload');
            if ($info) {
                return $this->success('/upload/' . $info->getSaveName());
            } else {
                return $this->error($file->getError());

            }
        }
    }

    public function getWalletList()
    {
        $result = [];
        $kk = 0;


        foreach (self::Wallet as $k => $v) {
            $arr = json_decode(sm_request($v), true);
            if($kk == 0){
                $arr['name'] = $k;
                $arr['dollar_money'] = sprintf("%.6f", $arr['last']);
                $arr['last'] =  sprintf("%.6f",$arr['last']  * self::DOLLAR_RATE);
                $arr['image'] = 'http://'.$_SERVER['HTTP_HOST'].'/static/btb/'.$k.'.png';
                $arr['hash_money'] = $this->user['btt'] ?? 0;
            }else{
                $arr['name'] = $k;
                $arr['dollar_money'] = sprintf("%.2f", $arr['last'] / self::DOLLAR_RATE);
                $arr['image'] = 'http://'.$_SERVER['HTTP_HOST'].'/static/btb/'.$k.'.png';
                $arr['hash_money'] = '0';
            }
    $kk++;
            $result[] = $arr;
        }
        $this->success('', $result);
        if (!\Cache::get('result_btc')) {
            foreach (self::Wallet as $k => $v) {
                $arr = json_decode(sm_request($v), true);
                $arr['name'] = $k;
                $arr['dollar_money'] = sprintf("%.2f", $arr['last'] / self::DOLLAR_RATE);
                $result[] = $arr;
            }
            \Cache::set('result_btc', $result, 120);
            $this->success('', $result);
        } else {
            $this->success('', \Cache::get('result_btc'));
        }

    }
    //发送静态收益 定时发送
    public function sendTodayByUserintegral()
    {
        set_time_limit(0);
        date_default_timezone_set('Asia/Shanghai');
        $this->commonService->sendTodayByUserintegral();
    }



    //发送静态收益 定时发送
    public function sendStaticBonus()
    {
        set_time_limit(0);
        date_default_timezone_set('Asia/Shanghai');
        $this->commonService->stillMining();
    }
    
    //发送静态收益 出错时间发送
    public function sendStaticBonusad()
    {
        set_time_limit(0);
        date_default_timezone_set('Asia/Shanghai');
        $this->commonService->stillMiningad();
    }


    //查看订单交易是否超时
    public function checkOrderTimeOut()
    {
        set_time_limit(0);
        date_default_timezone_set('Asia/Shanghai');
        $relationList = Db::name('store_c2c_relation')->where('status','in','2,4')->select();   //买家订单
        $buyOrders = [];   //买家的oderid
        $sellOrders = [];
        //获取卖家的订单
        Db::startTrans();
        $res = [];
        $sellOrders = [];
        foreach ($relationList as $k => $v) {
            if (get_time() > $v['create_at'] + 3600*24) {
                $buyOrderInfo = Db::name('store_order_c2c')->where(['id' => $v['orderid']])->find();
                $sellOrderInfo = Db::name('store_order_c2c')->where(['id'=>$buyOrderInfo['other_order_id']])->find();
                if($buyOrderInfo['status'] == 4){
                    //买家已经打款
                    $res[] = mlog($buyOrderInfo['mid'],'account_money',$buyOrderInfo['real_price'],"订单超时,自动到账金额{$buyOrderInfo['real_price']}",'time_out_transaction_money_arrival',$buyOrderInfo['id']);
                    $res[] = mlog($sellOrderInfo['mid'],'credit',-1,'卖家没有确认收款,扣除信用积分1','time_out_transaction',$v['id']);
                    $userInfo = Db::name('store_member')->where(['id'=>$sellOrderInfo['mid']])->find();
                    if($userInfo['credit'] <=4){
                       $res['test1'][] = Db::name('store_member')->where(['id'=>$sellOrderInfo['mid']])->update(['is_disable'=>-1]) !==FALSE;
                    }
                }elseif($buyOrderInfo['status'] == 2){
                    //还在撮合中
                    $res[] = mlog($sellOrderInfo['mid'],'account_money',$sellOrderInfo['real_price'],"订单超时买家没有打款,返回金额{$sellOrderInfo['real_price']}",'time_out_transaction_money_return',$sellOrderInfo['id']);
                    $res[] = mlog($buyOrderInfo['mid'],'credit',-1,'买家没有打款,扣除信用积分1','time_out_transaction',$v['id']);
                    $userInfo = Db::name('store_member')->where(['id'=>$buyOrderInfo['mid']])->find();
                    if($userInfo['credit'] <=4){
                        $res['test1'][] = Db::name('store_member')->where(['id'=>$buyOrderInfo['mid']])->update(['is_disable'=>-1]) !==FALSE;
                    }
                }
                if (!$buyOrderInfo['other_order_id']) continue;
                array_push($sellOrders, $buyOrderInfo['other_order_id']);  //对应的卖家
                array_push($buyOrders, $v['orderid']);  //对应的卖家
            }
        }
        $allOutTimeOrder = array_unique(array_merge($buyOrders, $sellOrders));
        if(count($sellOrders) == 0){
            exit('暂时没有超时订单');
        }else{
            if($sellOrders == []){
                echo '暂时没有超时订单12';die;
            }
            $res[] = Db::name('store_order_c2c')->where('id', 'in', $allOutTimeOrder)->update(['status' => 10]) !== FALSE;  //超时
            $res[] = Db::name('store_c2c_relation')->where('orderid', 'in', $buyOrders)->update(['status' => 10]) !== FALSE;
          
            if (check_arr($res)) {
                Db::commit();
                echo date('Y-m-d H:i:s').'交易超时检查完成买家订单'.implode(',',$buyOrders).',卖家订单'.implode(',',$sellOrders);
            } else {
                Db::rollback();
                echo date('Y-m-d H:i:s').'交易超时检查失败';
            }
        }
    }


    public function sendMobileMessage()
    {
        $code = rand(10000, 99999);
        $data = input('param.');
        if (empty($data['phone'])) {
            $data['phone'] = Db::name('store_member')->where(['id' => $this->wx_user_id])->value('phone');
            !$data['phone'] && $this->error('账号不存在');
            $this->setSmsCode('code', $code, $data['phone']);
            $result = sendMobileMessage($data['phone'], ['code' => $code], '499532');
            $this->success($result);
        }
    }
    public function hot_update()
    {
        $this->success('',['hot_update'=>0]);
    }



    public function givebtt()
    {
        $data = Db::name('store_order_c2c')
            // ->where(['type' => 2, 'status' => 3 , ' isf'=>0])
            ->where('end_time > 1558800000 and  type = 1 and status = 3 and isf = 0')
            ->order('create_at ASC')
            ->select();
        Db::startTrans();
        $res[] = 1;
        foreach ($data as $k => $v) {//

            $res[] = Db::name('store_order_c2c')->where(['id' => $v['id']])->update(['isf' => 1, 'ftime' => time() ]);

            $res[] = mlog($v['mid'],'btt',$v['num'] ,'交易获得'.$v['num'].'个 ID:'.$v['order_no'],'give_btt');
        }
        if (check_arr($res)) {
            Db::commit();
            $this->success();
        } else {
            Db::rollback();
            $this->error();
        }

    }





}
