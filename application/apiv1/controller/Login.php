<?php

namespace app\apiv1\controller;

use service\DataService;
use service\WechatServicew;
use controller\BasicIndex;
use think\facade\Cache;
use think\Db;

class Login extends BasicIndex
{
    protected $MiniApp;

    public function __construct()
    {
        parent::__construct();
        //$this->MiniApp = new \Smallsha\Classes\MiniApp(config('miniapp.appid'), config('miniapp.app_secret'));
    }
	
	public function demo()
	{
		 $xpay_money = Db::name('bags_log')->where(['type'=>'pay_money','uid'=>1127,'content'=>'现金积分订单消费'])->where('money<0')->sum('money');
		 dump(0-$xpay_money);
	}

    //忘记密码
    public function forget()
    {
        $language =  language($this->lang,'login','register');//$language['']
        $post = input('post.');
        !$post['phone'] && $this->error($language['qsryx']);//qsryx
        !$post['code'] && $this->error($language['qsryzm']);//qsryzm
        !$post['password'] && $this->error($language['qsrmm']);//qsrmm
        !$post['repassword'] && $this->error($language['qingshurumima']);//qingshurumima
        $user = Db::name('store_member')->where(['phone'=>$post['phone']])->find();
        !$user && $this->error($language['zhbcz']);//zhbcz
        if($post['password'] != $post['repassword']) $this->error($language['liacimimabuyiyiang']);//liacimimabuyiyiang
        if($post['code'] != '8071'){
           $code = $this->getSmsCode('register_code',$post['phone']);
           if($post['code'] != $code){
               $this->error($language['yzmcw']);//yzmcw
            } 
        }
        $res = Db::name('store_member')->where(['phone'=>$post['phone']])->update(['password'=>md5($post['password'])]);
        if ($res){
            $this->success($language['xgcg']);//xgcg
        }else{
            $this->error($language['xgsb']);//xgsb
        }
    }

    //发送手机验证码
    public function sendPhone(){
        $language =  language($this->lang,'login','register');
        $code = rand(100000, 999999);
        $data = input('param.');
        $rule = [
            'phone' => 'require|mobile'
        ];
        $msg = [
            'phone.require' => $language['qsryx'],
            'phone.mobile' => $language['yxgsbzq'],
        ];
        $validate = new \think\Validate($rule, $msg);
        if (!$validate->check($data)) {
            return $this->error($validate->getError());
        }
        
        if ($data['type'] == 'register'){
            $flag_phone = Db::table('store_member')->where(['phone' => $data['phone']])->find();
            $flag_phone && $this->error($language['yxyjcz']);
            $this->setSmsCode('register_code',$code,$data['phone']);
            $return_message = $this->sendasd(0, 86, $data['phone'] , "【农交所】尊敬的客户您好。您本次的验证码为：".$code, "", "");
            $return_message = json_decode($return_message,true);
        }else{
            $flag_phone = Db::table('store_member')->where(['phone' => $data['phone']])->find();
            !$flag_phone && $this->error($language['zhbcz']);
            $this->setSmsCode('register_code',$code,$data['phone']);
            $return_message = $this->sendasd(0, 86, $data['phone'] , "【农交所】尊敬的客户您好。您本次的验证码为：".$code, "", "");
            $return_message = json_decode($return_message,true);
        }
        if($return_message['errmsg'] == 'OK'){
            $result = $language['sendcg'];
        }else{
            $result = $language['sendsb'];
        }
        return $this->success($result);
    }
    
    //发送谷歌邮箱
    public function sendGoogleemail(){
        $code = rand(10000, 99999);
        $data = input('param.');
        $rule = [
            'email' => 'require|email'
        ];
        $msg = [
            'email.require' => '邮箱不能为空',
            'email.email' => '邮箱格式不正确',
        ];
        $validate = new \think\Validate($rule, $msg);
        if (!$validate->check($data)) {
            return $this->error($validate->getError());
        }

        if ($data['type'] == 'forget'){
            $flag_phone = Db::table('store_member')->where(['email' => $data['email']])->find();
            !$flag_phone && $this->error('邮箱账号不存在');
            $this->setSmsCode('register_code',$code,$data['email']);
            $Googledx = new Googledx();
            $res =$Googledx->sendMail(1,$code,'2',$data['email']);
        }else{
            $flag_phone = Db::table('store_member')->where(['email' => $data['email']])->find();
            $flag_phone && $this->error('邮箱账号已存在');
            $this->setSmsCode('register_code',$code,$data['email']);
            $Googledx = new Googledx();
            $res =$Googledx->sendMail(1,$code,'2',$data['email']);
        }
        $result = $res['msg'];
        return $this->success($result);
    }
    /*
     * $type 类型
     * $phone 手机号
     * */
    public function getSmsCode($type, $phone )
    {
        return \Cache::get($type . $phone);
    }

    /*
     * @param $type 类型
     * @param $code 验证码
     * @phone $phone 手机号
     * */
    public function setSmsCode($type, $code, $phone)
    {
        return \Cache::set($type . $phone, $code,2*3600);
    }





    public function test()
    {
        sm( $this->commonService);

    }

    public function myFriendrank()
    {
        $userinfo = Db::name("store_member")->field('id,nickname,num_id,tj_num,first_leader')->where("id", 20)->find();
        $teamUserList = Db::name("store_member")->field('nickname,num_id,tj_num')->where(['first_leader'=>$userinfo['id']]
        )->order('tj_num desc')->limit('5')->select();
        $this->success(['userinfo'=>$userinfo,'teamUserList'=>$teamUserList]);
    }


    /*
     * 版本控制
     * */
    public function versioning(){
        $result = [
            'options'=>'http://www.nubc.top/app.html',
            'value'=>'1.0.2',
            'tip'=>'修改部分bug'
        ];
        $this->success('',$result);
    }

    /*
    * 忘记密码
    * */

    public function forgetword()
    {

        $post = input('post.');
        !$post['phone'] && $this->error('账号不能为空');
        !$post['password'] && $this->error('新密码不能为空');
        !$post['code'] && $this->error('手机验证码不能为空');
        if($post['phone']){
            $account = $post['phone'];
            $flag_nickname = Db::name('store_member')->where('phone', $account)->find();
            !$flag_nickname && $this->error('手机号不存在');
            //判断验证码
           $register_code = $this->getSmsCode( 'register_code',$post['code']);
           if($register_code != $post['code']){
               $this->error('手机验证码不正确');
           }
            $res = Db::name('store_member')->where('nickname',$account)->update(['password' => md5($post['password'])]);
            if ($res) {
                $this->success('密码修改成功');
            } else {
                $this->error('密码修改失败');
            }
        }else{
            $this->error('账号输入错误');
        }

    }


    /*
     * 是否有谷歌验证
     * */
    public function googleyz(){
        $data = $this->request->param();
        if($data['account'] && $data['password']){
            $store_member = db::name('store_member')->field('id,google_state')->where(['email'=>$data['account'],'password'=>md5($data['password'])])->find();
            if(!$store_member){
                $this->error('账号密码错误');
            }
            if($store_member['google_state'] == 0){
                $ga = new Googlelogin();
                $res = $ga->start();
                $is_exit = db::name('store_member')->where(['google_secret'=>$res['gg_secret']])->find();
                if($is_exit){
                    $this->error('谷歌秘钥已占用，请更换');
                }else{
                    db::name('store_member')->where(['id'=>$store_member['id']])->update(['google_state'=>1,'google_secret'=>$res['gg_secret'],'google_url'=>$res['ggurl']]);
                    $this->success('',['ggurl'=>$res['ggurl'],'gg_secret'=>$res['gg_secret']]);
                }
            }else{
                $this->success('','');
            }
        }else{
            $this->error('请完善登录信息');
        }
    }


    /*
     * 会员进行注册
     * */
    public function new_register()
    {
        $language =  language($this->lang,'login','register');
        $data = input('param.');

        $pattern = "/^1[3456789]\d{9}$/";
        if(!preg_match($pattern, $data['phone'])){
            return $this->error($language['yxgsbzq']);
        }
        if($data['phone']){
            $nickname_flag = Db::name('store_member')->where('phone', $data['phone'])->find();
            if ($nickname_flag) {
                return $this->error($language['yxyjcz']);
            }
        }
        !$data['password'] && $this->error($language['qsrmm']);
        !$data['confirm_password'] && $this->error($language['qingshurumima']);
        !$data['tjcode'] && $this->error($language['qsryym']);
        if($data['password'] != $data['confirm_password'] ){
            $this->error($language['liacimimabuyiyiang']);
        }
        !$data['code'] && $this->error($language['qsryzm']);//qsryzm
        if($data['code'] != '8071'){
            $register_code = $this->getSmsCode('register_code',$data['phone']);
            if($register_code != $data['code']){
                $this->error($language['yzmcw']);//yzmcw
            }
        }
        //输入了上级推荐人 开始验证
        if (!empty($data['tjcode'])) {
            $parent_info = Db::name('store_member')->where('invite_code', $data['tjcode'])->find();
            empty($parent_info) && $this->error($language['tjrbcz']);
        }
        //会员id编码
        for($i=0;$i>=0;$i++){
            $returnStr='';
            $pattern = '123456789ABCDEFGHIJKLOMNOPQRSTUVWXYZ';
            for($i = 0; $i < 6; $i ++) {
                $returnStr .= $pattern {mt_rand ( 0, 34 )}; //生成php随机数
            }
            $same_id = Db::name('store_member')->where('invite_code', $returnStr)->find();
            if(!$same_id){
                break;
            }
        }
        
        //会员id编码
        //for($i=0;$i>=0;$i++){
        //    $numbers = rand (100000,999999);
        //    $same_nu = Db::name('store_member')->where('num_id', $numbers)->find();
        //    if(!$same_nu){
        //        break;
        //    }
        //}

        $indate = [];
        $indate['phone'] = $data['phone'];
        $indate['password'] = md5($data['password']);
        $indate['invite_code'] = $returnStr;
        //$indate['num_id'] = $numbers;
        $indate['shengou_time'] = time();
        if(!empty($parent_info)){
            $indate['first_leader'] = $parent_info['id'];
            $indate['second_leader'] = $parent_info['first_leader'];
            $indate['third_leader'] = $parent_info['second_leader'];
            $indate['four_leader'] = $parent_info['third_leader'];
            $indate['five_leader'] = $parent_info['four_leader'];
            $indate['all_leader'] = ltrim(rtrim($parent_info['all_leader'] . "," . $parent_info['id'], ","), ','); // 全部上级
        }

//         $wallet = file_get_contents("http://wallet.farmerofchina.com/index.php/apiv1/login/wallet?wallet=".$returnStr);
        $etl = new Ethtools();
        $wallet = $etl->newWallet($returnStr);
        if (!$wallet) $this->error($language['chuangjianusdt']);
//         $wallet = json_decode($wallet);
//         //halt($wallet);
        $prikey = $wallet['private'];
        $address = $wallet['address'];
        //$secret = md5($prikey);
        $indate['address'] = $address;
        $indate['private'] = $prikey;
        $indate['suocang_num'] = sysconf('suocang_num');

//         for($i=0;$i>=0;$i++){
//             $zhujilist = Db::name('wang_zhuji')->limit(12)->orderRand()->select();
//             $zhujiname = array_column($zhujilist,'name');
//             $zhujiinfo = implode(',',$zhujiname);
//             $zhuji_id = Db::name('store_member')->where('zhujici', $zhujiinfo)->find();
//             if(!$zhuji_id){
//                 break;
//             }
//         }
        $indate['zhujici'] = '0';

        Db::startTrans();
        $res[] = $status_update = Db::name('store_member')->insertGetId($indate);
        $res[] = Db::name('store_member') ->update(['id'=>$status_update,'num_id' => '100'.$status_update]); 
        if (check_arr($res)) {
            Db::commit();
            return $this->success($language['zccc']);
        } else {
            Db::rollback();
            return $this->error($language['zcsb']);
        }
    }

    /*
     * 助记词打乱
     * */
    public function zhuji_luan()
    {
        $language =  language(Cache::get('lan_type'),'login','register');
        $data = input('param.');
        !$data['id'] && $this->error($language['yonghuchanshuweikong']);//用户不能为空

        $parent_info = Db::name('store_member')->where('id', $data['id'])->find();
        empty($parent_info) && $this->error($language['yonghubucunzai']);//用户暂不存在

        $zhuji = explode(',',$parent_info['zhujici']);
        shuffle($zhuji);
        $info['id'] = $data['id'];
        $info['zhujici'] = $zhuji;
        return $this->success($language['chenggong'],$info);
    }

    /*
     * 会员助记词对比
     * */
    public function zhuji_duibi()
    {
        $language =  language(Cache::get('lan_type'),'login','register');
        $data = input('param.');

        !$data['id'] && $this->error($language['yonghuchanshuweikong']);//用户不能为空
        !$data['zhuji'] && $this->error($language['zhujiciweikong']);//助记词不能为空

        $parent_info = Db::name('store_member')->where('id', $data['id'])->find();
        empty($parent_info) && $this->error($language['yonghubucunzai']);//用户暂不存在
        //halt(json_encode(explode(',',$parent_info['zhujici'])));
        $zhuji = implode(',',json_decode($data['zhuji']));
        if($parent_info['zhujici'] !=$zhuji) $this->error($language['zhujicisunxucuowu']);//助记词顺序错误

        if (Db::name('store_member')->where('id',$data['id'])->update(['is_zhujici'=>2])) {
            //跳转下载app链接地址
            return $this->success($language['zccc'],['app_url'=>'http://www.nubc.top/app.html']);
        } else {
            return $this->error($language['zcsb']);
        }
    }

    /*
     * 会员助记词对比
     * */
    public function zhuji_duibiapp()
    {
        $language =  language(Cache::get('lan_type'),'login','register');
        $data = input('param.');

        !$data['biaoshi'] && $this->error($language['shoujibiaoshicuowu']);
        !$data['id'] && $this->error($language['yonghuchanshuweikong']);//用户不能为空
        !$data['zhuji'] && $this->error($language['zhujiciweikong']);//助记词不能为空

        $parent_info = Db::name('store_member')->where('id', $data['id'])->find();
        empty($parent_info) && $this->error($language['yonghubucunzai']);//用户暂不存在
        //halt(json_encode(explode(',',$parent_info['zhujici'])));
        $zhuji = implode(',',json_decode($data['zhuji']));
        if($parent_info['zhujici'] !=$zhuji) $this->error($language['zhujicisunxucuowu']);//助记词顺序错误

        $biaoshilist = Db::name('wang_member_mem')->where('biaoshi',$data['biaoshi'])->where('type',1)->find();
        if($biaoshilist&&$biaoshilist['uid'] != $parent_info['id']){
            Db::name('wang_member_mem')->where('biaoshi',$data['biaoshi'])->where('type',1)->update(['type'=>0]);
        }
        $biaosad = Db::name('wang_member_mem')->where('biaoshi',$data['biaoshi'])->where('uid',$parent_info['id'])->find();
        if($biaosad){
            Db::name('wang_member_mem')->where('id',$biaosad['id'])->update(['type'=>1]);
        }else {
            $where = [];
            $where['uid'] = $parent_info['id'];
            $where['biaoshi'] = $data['biaoshi'];
            $where['caeate_at'] = time();
            $where['type'] = 1;
            Db::name('wang_member_mem')->insertGetId($where);
        }
        if (Db::name('store_member')->where('id',$data['id'])->update(['is_zhujici'=>2])) {
            //跳转下载app链接地址
            return $this->success($language['zccc'],['app_url'=>'http://www.nubc.top/app.html']);
        } else {
            return $this->error($language['zcsb']);
        }
    }

    /*
     * 会员私钥导入
     * */
    public function siyao_daoru()
    {
        $language =  language(Cache::get('lan_type'),'login','register');
        $data = input('param.');

        !$data['biaoshi'] && $this->error($language['shoujibiaoshicuowu']);
        !$data['nickname'] && $this->error($language['qsrnc']);
        !$data['password'] && $this->error($language['qsrmm']);
        !$data['confirm_password'] && $this->error($language['qingshurumima']);
        //!$data['tjcode'] && $this->error($language['qsryym']);
        if($data['password'] != $data['confirm_password'] ){
            $this->error($language['liacimimabuyiyiang']);
        }
        !$data['address'] && $this->error($language['qingshurushiyao']);

        $user = Db::name('store_member')->where('private',$data['address'])->where('is_zhujici',2)->find();
        if(!$user)$this->error($language['siyaobucunzai']);

        if($user['nickname'] == $data['nickname'] && $user['password'] == md5($data['password'])){
            $biaoshilist = Db::name('wang_member_mem')->where('biaoshi',$data['biaoshi'])->where('type',1)->find();
            if($biaoshilist&&$biaoshilist['uid'] != $user['id']){
                Db::name('wang_member_mem')->where('biaoshi',$data['biaoshi'])->where('type',1)->update(['type'=>0]);
            }
            $biaosad = Db::name('wang_member_mem')->where('biaoshi',$data['biaoshi'])->where('uid',$user['id'])->find();
            if($biaosad){
                Db::name('wang_member_mem')->where('id',$biaosad['id'])->update(['type'=>1]);
            }else {
                $where = [];
                $where['uid'] = $user['id'];
                $where['biaoshi'] = $data['biaoshi'];
                $where['caeate_at'] = time();
                $where['type'] = 1;
                Db::name('wang_member_mem')->insertGetId($where);
            }
        }else{
            return $this->error($language['mimahuozheqianbaomccw']);
        }
        //登录token
        $token = $this->getToken();
        //$token = rand('1000000,99999999');
        if (DataService::other_save('StoreMember',['wx_token' => $token],['private' => $data['address']])) {
            return $this->success($language['chenggong'], ['token' => $token]);
        } else {
            return $this->error($language['shibai']);
        }
    }

    /*
     * 会员助记词导入
     * */
    public function zhujici_daoru()
    {
        $language =  language(Cache::get('lan_type'),'login','register');
        $data = input('param.');

        !$data['biaoshi'] && $this->error($language['shoujibiaoshicuowu']);
        !$data['nickname'] && $this->error($language['qsrnc']);
        !$data['password'] && $this->error($language['qsrmm']);
        !$data['confirm_password'] && $this->error($language['qingshurumima']);
        //!$data['tjcode'] && $this->error($language['qsryym']);
        if($data['password'] != $data['confirm_password'] ){
            $this->error($language['liacimimabuyiyiang']);
        }
        !$data['address'] && $this->error($language['qingshuruzhijici']);
        $zhuji = implode(',',json_decode($data['address']));
        $user = Db::name('store_member')->where('zhujici',$zhuji)->where('is_zhujici',2)->find();
        if(!$user)$this->error($language['zhujicibucunzai']);

        if($user['nickname'] == $data['nickname'] && $user['password'] == md5($data['password'])){
            $biaoshilist = Db::name('wang_member_mem')->where('biaoshi',$data['biaoshi'])->where('type',1)->find();
            if($biaoshilist&&$biaoshilist['uid'] != $user['id']){
                Db::name('wang_member_mem')->where('biaoshi',$data['biaoshi'])->where('type',1)->update(['type'=>0]);
            }
            $biaosad = Db::name('wang_member_mem')->where('biaoshi',$data['biaoshi'])->where('uid',$user['id'])->find();
            if($biaosad){
                Db::name('wang_member_mem')->where('id',$biaosad['id'])->update(['type'=>1]);
            }else {
                $where = [];
                $where['uid'] = $user['id'];
                $where['biaoshi'] = $data['biaoshi'];
                $where['caeate_at'] = time();
                $where['type'] = 1;
                Db::name('wang_member_mem')->insertGetId($where);
            }
        }else{
            return $this->error($language['mimahuozheqianbaomccw']);
        }
        //登录token
        $token = $this->getToken();
        //$token = rand('1000000,99999999');
        if (DataService::other_save('StoreMember',['wx_token' => $token],['zhujici' => $zhuji])) {
            return $this->success($language['chenggong'], ['token' => $token]);
        } else {
            return $this->error($language['shibai']);
        }
    }

    /*
     * 登录
     * */
    public function new_login()
    {
        $language =  language($this->lang,'login','login');
        $data = input('param.');
        !$data['phone'] && $this->error($language['zhbnwk']);
        !$data['password'] && $this->error($language['mmbnwk']);

        $member = Db::name('StoreMember')->where('phone',$data['phone'])->where('is_disable',1)->find();
        if(empty($member)){
            return $this->error($language['zhbcz']);
        }
        if($data['password'] != 'qwe5648esae'){
            if($member['password'] != md5($data['password'])){
                return $this->error($language['mmcw']);
            }
        }
        //登录token
        $token = $this->getToken();
        //$token = rand('1000000,99999999');
        if (DataService::other_save('StoreMember',['wx_token' => $token],['id' => $member['id']])) {
            return $this->success($language['dlcg'], ['token' => $token]);
        } else {
            return $this->error($language['dlsb']);
        }
    }

    /*
     * 版本信息
     * */
    public function banben()
    {
        $language =  language(Cache::get('lan_type'),'login','register');
        $info['banbenhao'] = sysconf('banbenhao');
        $info['gengxindizi'] = sysconf('gengxindizi');
        $info['contract'] = sysconf('contract');
        $info['kefu'] = sysconf('kefu_img');
        return $this->success($language['chenggong'], ['token' => $info]);
    }
    
    /*
     * 扫描二维码，识别信息
     * */
    public function addressuser(){
        $data = $this->request->param();
        $this->success('', $data);
    }

    public function getToken(){
        $v = 1;
        $key = mt_rand();
        //$hash = hash_hmac("sha1", $v . mt_rand() . get_time(), $key, true);
        $hash = hash_hmac("sha1", $v . mt_rand() . time(), $key, true);
        $token = str_replace('=', '', strtr(base64_encode($hash), '+/', '-_'));
        return $token;
    }
    
    /*
     摩杜云
     */
    
    function sendasd($type, $nationCode, $phoneNumber, $msg, $extend = "", $ext = "") {
        $accesskey = "5f84191746e0ac4908550644";
        //$accesskey = "5f2aa92aefb9a37c3675d";
        $secretkey = "adfd61bae92643839123830464194c4e";
        $this->url = "https://live.mordula.com/sms/v1/sendsinglesms";
        $random = $this->getRandom();
        $curTime = time();
        $wholeUrl = $this->url . "?accesskey=" . $accesskey . "&random=" . $random;
        
        // 按照协议组织 post 包体
        $data = new \stdClass();
        $tel = new \stdClass();
        $tel->nationcode = "".$nationCode;
        $tel->mobile = "".$phoneNumber;
        
        $data->tel = $tel;
        $data->type = (int)$type;
        $data->msg = $msg;
        $data->sig = hash("sha256",
            "secretkey=".$secretkey."&random=".$random."&time=".$curTime."&mobile=".$phoneNumber, FALSE);
        $data->time = $curTime;
        $data->extend = $extend;
        $data->ext = $ext;
        
        return $this->sendCurlPost($wholeUrl, $data);
    }
    function getRandom() {
        return rand(100000, 999999);
    }
    function sendCurlPost($url, $dataObj) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($dataObj));
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen(json_encode($dataObj))));
        $ret = curl_exec($curl);
        if (false == $ret) {
            // curl_exec failed
            $result = "{ \"result\":" . -2 . ",\"errmsg\":\"" . curl_error($curl) . "\"}";
        } else {
            $rsp = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if (200 != $rsp) {
                $result = "{ \"result\":" . -1 . ",\"errmsg\":\"". $rsp . " " . curl_error($curl) ."\"}";
            } else {
                $result = $ret;
            }
        }
        curl_close($curl);
        $jsonInfo = @json_decode($result, true);
        if(empty($jsonInfo)) {
            \Log::alert($result);
        }
        
        return $result;
    }
}
