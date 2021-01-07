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
class Wang extends Base
{
    const TSDJS = 900;//投诉倒计时

    protected $MiniApp;

    public function __construct()
    {
        parent::__construct();
        //$this->MiniApp = new \Smallsha\Classes\MiniApp(config('miniapp.appid'), config('miniapp.app_secret'));
    }

    //钱包列表
    public function userlist()
    {
        $language =  language(Cache::get('lan_type'),'login','register');
        $data = input('param.');
        !$data['biaoshi'] && $this->error($language['shoujibiaoshicuowu']);

        $list = Db::name('wang_member_mem')->where('biaoshi',$data['biaoshi'])->select();
        foreach ($list as $key=>$val){
            $user = Db::name('store_member')->where('id',$val['uid'])->where('is_zhujici',2)->find();
            if(!empty($user)){
                $list[$key]['nickname'] = $user['nickname'];
                $list[$key]['address'] = $user['address'];
            }else{
                unset($list[$key]);
            }
        }
        $this->success($language['chenggong'],$list);
    }

    //切换钱包
    public function userqiehuan()
    {
        $language =  language(Cache::get('lan_type'),'login','register');
        $data = input('param.');
        !$data['biaoshi'] && $this->error($language['shoujibiaoshicuowu']);
        !$data['id'] && $this->error($language['yonghuchanshuweikong']);
        !$data['password'] && $this->error($language['qsrmm']);

        $list = Db::name('store_member')->where('id',$data['id'])->where('is_zhujici',2)->find();
        if(md5($data['password'])!=$list['password'])$this->error($language['mimahuozheqianbaomccw']);
        if($list['is_disable'] == '-1')return $this->error($language['shibai']);

        $biaoshilist = Db::name('wang_member_mem')->where('biaoshi',$data['biaoshi'])->where('type',1)->find();
        if($biaoshilist&&$biaoshilist['uid'] != $list['id']){
            Db::name('wang_member_mem')->where('biaoshi',$data['biaoshi'])->where('type',1)->update(['type'=>0]);
        }
        $biaosad = Db::name('wang_member_mem')->where('biaoshi',$data['biaoshi'])->where('uid',$list['id'])->find();
        if($biaosad){
            Db::name('wang_member_mem')->where('id',$biaosad['id'])->update(['type'=>1]);
        }
        $token = $this->getToken();
        //$token = rand('1000000,99999999');
        if (DataService::other_save('StoreMember',['wx_token' => $token],['id' => $list['id']])) {
            return $this->success($language['chenggong'], ['token' => $token]);
        } else {
            return $this->error($language['shibai']);
        }
    }

    //导出私钥
    public function usershiyao()
    {
        $language =  language(Cache::get('lan_type'),'login','register');
        $data = input('param.');
        !$data['id'] && $this->error($language['yonghuchanshuweikong']);
        !$data['password'] && $this->error($language['qsrmm']);

        $list = Db::name('store_member')->where('id',$data['id'])->where('is_zhujici',2)->find();
        if(md5($data['password'])!=$list['password'])$this->error($language['shibai']);
        $info['address'] = $list['address'];
        $info['private'] = $list['private'];
        return $this->success($language['chenggong'],$info);
    }

    //首页-发行申请
    public function faixingadd()
    {
        $language =  language(Cache::get('lan_type'),'login','register');
        $data = input('param.');

        $where = [];
        $where['uid'] = $this->wx_user_id;
        $where['name'] = $data['name'];
        $where['logo'] = $data['logo'];
        $where['faxingjia'] = $data['faxingjia'];
        $where['zliang'] = $data['zliang'];
        $where['zhouqi'] = $data['zhouqi'];
        $where['faxingfang'] = $data['faxingfang'];
        $where['wangzhi'] = $data['wangzhi'];
        $where['lianjie'] = $data['lianjie'];
        $where['lianxi'] = $data['lianxi'];
        $where['address'] = $data['address'];
        $where['content'] = $data['content'];
        $where['jianjie'] = $data['jianjie'];
        $where['caeate_at'] = time();

        if(Db::name('wang_faxing')->insertGetId($where)){
            return $this->success($language['chenggong']);
        }else{
            return $this->error($language['shibai']);
        }
    }

    //申购-页面数据
    public function faxingindex()
    {
        $language =  language(Cache::get('lan_type'),'login','register');
        $info['shengou_nums'] = sysconf('shengou_nums');    //申购总发行量
        $info['shengou_dayxuni'] = sysconf('shengou_dayxuni');    //每天虚拟数量
        $info['shengou_dayshifa'] = sysconf('shengou_dayshifa');    //每天实发数量
        $info['shengou_newjiage'] = sysconf('shengou_newjiage');    //实际价格
        $info['shengou_junjia'] = sysconf('shengou_junjia');    //市场均价
        $info['shengou_zuijia'] = sysconf('shengou_zuijia');    //最佳持币
        $info['shengou_xianzi'] = sysconf('shengou_xianzi');    //子账号申购限制
        $info['shengou_zhekou'] = 100;    //申购折扣
        if((strtotime(date('Y-m-d '.sysconf('time_shengouold').':0:0')) - time())>0){
            $info['time'] = strtotime(date('Y-m-d '.sysconf('time_shengouold').':0:0')) - time();    //当前时间戳
        }else{
            $info['time'] = 0;
        }
        $orderinfo = Db::name('wang_shengou')->where('uid',$this->wx_user_id)->where('status',1)->where('create_at','>',strtotime(date('Y-m-d 0:0:1')))->find();
        if(!empty($orderinfo)){
            $info['shengou_is'] = 1;
        }else{
            $info['shengou_is'] = 2;
        }
        $info['shengou_suanli'] = $this->wx_user['account_money'];    //申购算力
        $info['shengou_tuiguang'] = Db::name('store_member')->where('find_in_set('.$this->wx_user_id.',all_leader)')->where('is_zhujici',2)->where('account_money','>=',100)->count();    //推广算力
        
        $info['shougou_yujikeshengou'] = 0; //预计可申购
        /*if(!empty($this->wx_user['first_leader'])&&$this->wx_user['account_money']>=100){
            $info['shougou_yujikeshengou'] = '100'; //预计可申购
        }else{
            if($this->wx_user['account_money']>=500){
                $info['shougou_yujikeshengou'] = '100'; //预计可申购
            }
        }*/
        $info['shougou_yujikeshengou'] = $this->wx_user['day_shengou']; //预计可申购
       
        return $this->success($language['chenggong'],$info);
    }

    //申购-申请申购
    public function shengou_add()
    {
        //halt(Cache::get('lan_type'));
        $language =  language(Cache::get('lan_type'),'login','register');
        $num = input('param.num');
        $password = input('param.password');
        !$password && $this->error($language['qsrmm']);
        //halt($this->wx_user_id);
        if($this->wx_user['is_renzheng'] == 1)$this->error($language['zhanghaoshangwujihuo']);

        $shengou_nums = sysconf('shengou_nums');    //申购总发行量
        $shengou_dayxuni = sysconf('shengou_dayxuni');    //每天虚拟数量
        $shengou_dayshifa = sysconf('shengou_dayshifa');    //每天实发数量
        $shengou_newjiage = sysconf('shengou_newjiage');    //实际价格
        $shengou_junjia = sysconf('shengou_junjia');    //市场均价
        $shengou_zuijia = sysconf('shengou_zuijia');    //最佳持币
        $shengou_xianzi = sysconf('shengou_xianzi');    //子账号申购限制
        $shengou_zhekou = 100;    //申购折扣
        if($this->wx_user['is_shengou'] == 1){
            $this->error($language['zigebuzhu']);
        }
        $timeadd = sysconf('time_shengouadd');
        $timeold = sysconf('time_shengouold');
        $newtime = date('H');
        if($newtime<$timeadd||$newtime>=$timeold){
            $this->error($language['buzaishengoushijianduan']);
        }
        /*if(!empty($this->wx_user['first_leader'])){
            if($num>$shengou_xianzi||$num<=0)$this->error($language['chaochukeshengoushuliang']);
        }*/
        if($num < $shengou_zhekou){
            $this->error($language['chaochukeshengoushuliang']);
        }
        if($this->wx_user['day_shengou'] >500){
            if($num>500||$num<=0)$this->error($language['chaochukeshengoushuliang']);
        }else{
            if($num>$this->wx_user['day_shengou']||$num<=0)$this->error($language['chaochukeshengoushuliang']);
        }
        if(md5($password)!=$this->wx_user['password'])$this->error($language['shibai']);

        $orderinfo = Db::name('wang_shengou')->where('uid',$this->wx_user_id)->where('status',1)->where('create_at','>',strtotime(date('Y-m-d 0:0:1')))->find();
        //if(!empty($orderinfo))$this->error($language['yijingshengou']);

        $usdt = bcdiv($num,$shengou_newjiage,6);
        if($num>$this->wx_user['account_money'])$this->error($language['usdtbuzhu']);

        $where = [];
        $where['uid'] = $this->wx_user_id;
        $where['num'] = $usdt;
        $where['jiage'] = $shengou_newjiage;
        $where['usdt'] = $num;
        $where['create_at'] = time();

        Db::startTrans();
        $res[] = $orderid = Db::name('wang_shengou')->insertGetId($where);
        $res[] = mlog($this->wx_user_id,'account_money',-$num,"申购,扣除".$num.'个usdt','touziUsdt',$orderid,5);
        if($this->wx_user['first_leader']>0&&$this->wx_user['is_xiaoshou']==1){
            if(!empty($this->wx_user['all_leader'])){
                $asdf = explode(',',$this->wx_user['all_leader']);
                if(!empty($asdf)){
                    $us = DB::table('store_member')->where('id' , $asdf[0])->find();
                    if(!empty($us)&&empty($us['all_leader'])&&$us['suocang_num']>$us['suocang_fafang']){
                        $shifa = bcdiv(bcmul($us['suocang_num'],sysconf('suocang_bili'),6),100,2);
                        $res[] = Db::name('store_member')->where('id',$us['id'])->setInc('suocang_fafang',$shifa);
                        $res[] = Db::name('store_member')->where('id',$this->wx_user['id'])->update(['is_xiaoshou'=>2]);
                        $res[]= mlog($us['id'],'account_score',$shifa,'用户'.$this->wx_user['address'].'激活，锁仓代币发放','rechanglxc','','14');
                    }
                }
            }
        }
        if (check_arr($res)) {
            Db::commit();
            return $this->success($language['chenggong']);
        } else {
            Db::rollback();
            return $this->error($language['shibai']);
        }
    }

    //矿池-页面数据
    public function kuangchiindex()
    {
        $language =  language(Cache::get('lan_type'),'login','register');
        $info['kuangchi_body'] = sysconf('kuangchi_body');    //矿池每天申购人数
        $info['kuangchi_qishi'] = sysconf('kuangchi_qishi');    //起始投币

        $kuangchi_peizi = sysconf('kuangchi_peizi');    //产币配置
        $kuangchi_peizi = explode(';',$kuangchi_peizi);
        $peizi = [];
        foreach ($kuangchi_peizi as $key=>$value){
            list($infoad,$feilv) = explode(':',$value);
            list($qujian1,$qujian2) = explode('-',$infoad);
            $peizi[$key]['feilv'] =$feilv;
            $peizi[$key]['qujian1'] =$qujian1;
            $peizi[$key]['qujian2'] =$qujian2;
        }
        $info['peizi'] = $peizi;

        $info['rishouyi'] = Db::name('wang_kuangchi')->where('uid',$this->wx_user_id)->where('status',1)->where('create_at','>',strtotime(date('Y-m-d 0:0:1')))->sum('good_num');
        $info['lijishouyi'] = Db::name('wang_kuangchi')->where('uid',$this->wx_user_id)->where('status',2)->sum('good_num');
        $info['time'] = strtotime(date('Y-m-d 23:59:59')) - time();    //当前时间戳
        return $this->success($language['chenggong'],$info);
    }

    //矿池-申请
    public function kuangchi_add()
    {
        $language =  language(Cache::get('lan_type'),'login','register');
        $num = input('param.num');
        $password = input('param.password');
        !$password && $this->error($language['qsrmm']);
        if(md5($password)!=$this->wx_user['password'])$this->error($language['shibai']);
        if($this->wx_user['is_renzheng'] == 1)$this->error($language['zhanghaoshangwujihuo']);

        $kuangchi_body = sysconf('kuangchi_body');    //矿池每天申购人数
        $kuangchi_qishi = sysconf('kuangchi_qishi');    //起始投币

        $orderinfo = Db::name('wang_kuangchi')->where('uid',$this->wx_user_id)->where('status',1)->where('create_at','>',strtotime(date('Y-m-d 0:0:1')))->find();
        //if(!empty($orderinfo))$this->error($language['wakuangjinxz']);
        if($num>$this->wx_user['account_score'])$this->error($language['nfbuzhu']);
        if($num<$kuangchi_qishi)$this->error($language['chaochukeshengoushuliang']);
        if(empty($orderinfo)){
            $ordernum = Db::name('wang_kuangchi')->where('status',1)->where('create_at','>',strtotime(date('Y-m-d 0:0:1')))->group('uid')->count();
            if($ordernum>=$kuangchi_body)$this->error($language['kuangchixiangou']);
        }
        
        $kuangchi_peizi = sysconf('kuangchi_peizi');    //产币配置
        $kuangchi_peizi = explode(';',$kuangchi_peizi);
        foreach ($kuangchi_peizi as $key=>$value){
            list($infoad,$feilv) = explode(':',$value);
            list($qujian1,$qujian2) = explode('-',$infoad);
            if($num>=$qujian1&&$num<=$qujian2){
                $peizi = $feilv;
                break;
            }
        }
        $where = [];
        $where['uid'] = $this->wx_user_id;
        $where['num'] = $num;
        $where['beilv'] = $peizi;
        $where['create_at'] = time();
        $where['good_num'] = bcdiv(bcmul($num,$peizi,6),100,6);

        Db::startTrans();
        $res[] = $orderid = Db::name('wang_kuangchi')->insertGetId($where);
        $res[] = mlog($this->wx_user_id,'account_score',-$num,"矿池,扣除".$num.'个NF','account_score',$orderid,8);
        if (check_arr($res)) {
            Db::commit();
            return $this->success($language['chenggong']);
        } else {
            Db::rollback();
            return $this->error($language['shibai']);
        }
    }

    /*
         * 收款码
         * */
    public function neibuerweiba()
    {
        //如果存在的话，就能进行生成
        $userinfo = Db::name('store_member')->where(['id' => $this->wx_user_id])->find();
        //$contentUrl = 'http://'.$_SERVER['HTTP_HOST'].'/index.php/apiv1/login/addressuser?address='. $userinfo['address'];
        $contentUrl = $userinfo['address'];
        $h5Url = 'http://api.k780.com:88/?app=qr.get&data=' . urlencode($contentUrl);
        if (empty($userinfo['qukuai_img'])) {
            $file = 'upload/usdt/' . md5(time() . $userinfo['num_id']) . '.png';
            if (file_put_contents('./' . $file, file_get_contents($h5Url))) {
                $appRoot = request()->root(true); // 去掉参数 true 将获得相对地址
                $uriRoot = preg_match('/\.php$/', $appRoot) ? dirname($appRoot) : $appRoot;
                $file = $uriRoot . '/' . $file;
                Db::name("store_member")->where(['id' => $this->wx_user_id])->update([
                    'qukuai_img' => $file,
                ]);
                response($file);
                $this->success('',['qrcodeUrl' => $file, 'linkUrl' => $contentUrl]);
            }
        }

        $this->success('',['qrcodeUrl' => $userinfo['qukuai_img'], 'linkUrl' => $userinfo['address'],'linkUrlad' => $contentUrl]);

    }

    /*
     * 扫描二维码，识别信息
     * */
    public function addressuser(){
        $data = $this->request->param();
        $this->success('', $data);
    }

    //转账
    public function zhuanzhang(){
        $language =  language(Cache::get('lan_type'),'login','register');
        $num = input('param.num');//数量
        $type = input('param.type','2');//1 USDT 2NF 3NFC
        $address = input('param.address');//1 USDT 2NF 3NFC
        $password = input('param.password');

        !$address && $this->error($language['qingshurudizhi']);
        !$num && $this->error($language['qingshurusuliang']);
        !$password && $this->error($language['qsrmm']);
        if(md5($password)!=$this->wx_user['password'])$this->error($language['shibai']);

        switch ($type)
        {
            case 1:
                $bizhong = 'account_money';
                break;
            case 2:
                $bizhong = 'account_score';
                break;
            default:
                $bizhong = 'account_nfc';
        }
        if($num>$this->wx_user[$bizhong])$this->error($language['yuebuzhu']);

        $userinfo = Db::name('store_member')->where('address',$address)->where('is_zhujici',2)->find();
        if(empty($userinfo))$this->error($language['yonghubucunzai']);
        if($userinfo['id'] == $this->wx_user_id)$this->error($language['zhuangzhangziji']);

        $shouxufei = 0;//手续费
        if($type ==2){
            $shouxufei = sysconf('shouxuf_neibu');//手续费NF
            if(($num)> $this->wx_user['account_score']){
                $this->error($language['nfbuzhu']);
            }
            if($shouxufei>$num){
                $this->error($language['nfbuzhu']);
            }
        }elseif($type ==1){
            $shouxufei = sysconf('shouxuf_neibuusdt');//手续费USDT
            if($num > $this->wx_user['account_money']){
                $this->error($language['usdtbuzhu']);
            }
            if($shouxufei>$num){
                $this->error($language['usdtbuzhu']);
            }
        }
        Db::startTrans();
            if($userinfo['is_renzheng'] == 1&&$num>=sysconf('renzheng_money')){
                $res[] = Db::name('store_member')->where('id',$userinfo['id'])->update(['is_renzheng'=>2]);
                /*if(!empty($userinfo['all_leader'])){
                    $asdf = explode(',',$userinfo['all_leader']);
                    if(!empty($asdf)){
                        $us = DB::table('store_member')->where('id' , $asdf[0])->find();
                        if(!empty($us)&&empty($us['all_leader'])&&$us['suocang_num']>$us['suocang_fafang']){
                            $shifa = bcdiv(bcmul($us['suocang_num'],sysconf('suocang_bili'),6),100,2);
                            $res[] = Db::name('store_member')->where('id',$us['id'])->setInc('suocang_fafang',$shifa);
                            $res[]= mlog($us['id'],'account_score',$shifa,'用户激活，锁仓代币发放','rechanglxc','','14');
                        }
                    }
                }*/
            }
            $res[] = mlog($this->wx_user_id,$bizhong,-$num,"内部转出",$bizhong,'',3,$userinfo['id']);
            $res[] = mlog($userinfo['id'],$bizhong,$num-$shouxufei,"内部转入",$bizhong,'',4,$this->wx_user_id);
        if (check_arr($res)) {
            Db::commit();
            return $this->success($language['chenggong']);
        } else {
            Db::rollback();
            return $this->error($language['shibai']);
        }
    }

    //用户余额
    public function useryue(){
        $language =  language(Cache::get('lan_type'),'login','register');
        $type = input('param.type','2');//1 USDT 2NF 3NFC

        switch ($type)
        {
            case 1:
                $bizhong = 'account_money';
                break;
            case 2:
                $bizhong = 'account_score';
                break;
            default:
                $bizhong = 'account_nfc';
        }
        return $this->success($language['chenggong'],$this->wx_user[$bizhong]);
    }

    //log
    public function userlog(){
        $language =  language(Cache::get('lan_type'),'login','register');
        $type = input('param.type');//1 USDT 2FOC
        $page = request()->param("pageNo", 1);
        $pnum = request()->param("pnum", 10);
        $status = request()->param("status", '');
        $start = ($page - 1) * $pnum;
        $db = Db::name('bags_log')->field('id,type,money,content,create_time,status')->where('uid',$this->wx_user_id);
        if(!empty($type)){
            switch ($type)
            {
                case 1:
                    $db->where('type','account_money');
                    break;
                case 2:
                    $db->where('type','account_score');
                    break;
                default:
                    $db->where('type','account_foc');
            }
        }else{
            $db->whereIn('type',['account_money','account_foc','account_score']);
        }
        if($status == 1){
            $db->where('money','>',0);
        }elseif($status == 2){
            $db->where('money','<',0);
        }
        $info = $db->order('id desc')->page($page, $pnum)->select();
        $model = array(1=>'充值',2=>'提现',3=>'内部转出',4=>'内部转入',5=>'推荐所得',6=>'冻结usdt释放',7=>'矿机本金',8=>'矿池支付',9=>'矿池返还',10=>'交易支付',11=>'交易所得',12=>'后台调整',13=>'撤单返还',14=>'股东分红',15=>'持币生息');
        foreach ($info as &$vl){
            if($vl['type'] == 'account_money'){
                $vl['type'] = 'USDT';
            }elseif($vl['type'] == 'account_foc'){
                $vl['type'] = 'FOC交易';
            }elseif($vl['type'] == 'account_score'){
                $vl['type'] = 'FOC矿池';
            }
            $vl['status'] = $model[$vl['status']];
            $vl['create_time'] = date('Y-m-d H:i:s',$vl['create_time']);
        }
        return $this->success('',$info);
    }

    //我的信息
    public function userinfo(){
        $language =  language(Cache::get('lan_type'),'login','register');
        $info['nickname'] = $this->wx_user['nickname'];
        $info['address'] = $this->wx_user['address'];
        $info['is_renzheng'] = $this->wx_user['is_renzheng'];
        return $this->success($language['chenggong'],$info);
    }

    //申购记录
    public function shengoulog(){
        $language =  language(Cache::get('lan_type'),'login','register');
        $page = request()->param("pageNo", 1);
        $pnum = request()->param("pnum", 10);
        $start = ($page - 1) * $pnum;
        $db = Db::name('wang_shengou')->where('uid',$this->wx_user_id);
        $info = $db->order('id desc')->page($page, $pnum)->select();
        return $this->success($language['chenggong'],$info);
    }

    //矿池记录
    public function kuangchilog(){
        $language =  language(Cache::get('lan_type'),'login','register');
        $page = request()->param("pageNo", 1);
        $pnum = request()->param("pnum", 10);
        $start = ($page - 1) * $pnum;
        $db = Db::name('wang_kuangchi')->where('uid',$this->wx_user_id);
        $info = $db->order('id desc')->page($page, $pnum)->select();
        return $this->success($language['chenggong'],$info);
    }

    //会员数量
    public function usernum(){
        $language =  language(Cache::get('lan_type'),'login','register');
        $type = input('param.type','1');//1 全部 2有效
        $page = request()->param("pageNo", 1);
        $pnum = request()->param("pnum", 10);
        $start = ($page - 1) * $pnum;
        $info['zhitui'] = Db::name('store_member')->where('first_leader',$this->wx_user_id)->where('is_zhujici',2)->count();
        $info['youxiao'] = Db::name('store_member')->where('first_leader',$this->wx_user_id)->where('is_zhujici',2)->where('is_renzheng',2)->count();
        $info['suocang_yifa'] = $this->wx_user['suocang_fafang'];
        $info['suocang_num'] = $this->wx_user['suocang_num'];
        if($this->wx_user['suocang_num']>$this->wx_user['suocang_fafang']){
            $info['suocang_sengyu'] = bcsub($this->wx_user['suocang_num'],$this->wx_user['suocang_fafang'],2);
        }else{
            $info['suocang_sengyu'] = 0;
        }
        $db = Db::name('store_member')->where('first_leader',$this->wx_user_id)->where('is_zhujici',2);
        if($type == 2){
            $db->where('is_renzheng',2);
        }
        $info['list'] = $db->field('id,nickname,address')->page($page, $pnum)->select();
        return $this->success($language['chenggong'],$info);
    }

    //参数配置
    public function canshupeizhi(){
        $language =  language(Cache::get('lan_type'),'login','register');

        $info['kaifazhebuluo'] = sysconf('kaifazhebuluo');
        $info['qukuaillqi'] = sysconf('qukuaillqi');
        $info['kaiyuandizhi'] = sysconf('kaiyuandizhi');

        return $this->success($language['chenggong'],$info);
    }

    //资产
    public function userzican(){
        $language =  language(Cache::get('lan_type'),'login','register');

        $usdt_rmb = sysconf('usdt_rmb');//db::name('system_coin')->where(['name'=>'USDT'])->value('price');//usdt兑换人民币
        $jiaoyi_nf = sysconf('jiaoyi_nf');//1NF价值USDT限价
        $jiaoyi_nfc = sysconf('jiaoyi_nfc');//1NFC价值USDT限价

        $user['usdt'] = $this->wx_user['account_money'];
        $user['nf'] = $this->wx_user['account_score'];
        $user['nfc'] = $this->wx_user['account_nfc'];

        $user['usdt_rmb'] = bcmul($this->wx_user['account_money']*$usdt_rmb,1,6);
        $user['nf_rmb'] = bcmul($this->wx_user['account_score']*$jiaoyi_nf*$usdt_rmb,1,6);
        $user['nfc_rmb'] = bcmul($this->wx_user['account_nfc']*$jiaoyi_nfc*$usdt_rmb,1,6);

        $user['num_rmb'] = bcmul($user['usdt_rmb']+$user['nf_rmb']+$user['nfc_rmb'],1,6);
        return $this->success($language['chenggong'],$user);
    }

    //实时价格
    public function realtimePrice(){
        $system = db::name('system_coin')->where('status',1)->select();
//         if($this->wx_user_id != 367){
//             array_splice($system, 0, 1); 
//         }
        $dollar = sysconf('usdt_rmb');//db::name('system_coin')->where(['name'=>'usdt'])->value('price');
        foreach($system as $key=>$value){
            $system[$key]['cny_price'] = bcmul($value['price'],$dollar, 4);
//             if($value['name'] == 'FOC'){
//                 $system[$key]['price'] = sysconf('jiaoyi_foc');
//                 $system[$key]['amount'] = Db::name('jys_price')->where('type',0)->where('status',1)->where('addtime','>=',strtotime('-24 hour',time()))->sum('num');

//                 $numpr = Db::name('jys_price')->where('type',0)->where('status',1)->where('addtime','>=',strtotime('-24 hour',time()))->sum('price');
//                 $counpr = Db::name('jys_price')->where('type',0)->where('status',1)->where('addtime','>=',strtotime('-24 hour',time()))->count();
//                 $system[$key]['change'] = bcmul(bcdiv(bcsub(sysconf('jiaoyi_foc'),bcdiv($numpr,$counpr,6),6),sysconf('jiaoyi_foc'),6),100,4);
//                 $system[$key]['cny_price'] = bcmul(sysconf('jiaoyi_foc'),$dollar, 4);
//                 $system[$key]['img'] = sysconf('nf_img');
//                 $high = Db::name('jys_price')->where('type',0)->where('status',2)->where('addtime','>=',strtotime('-24 hour',time()))->order('price desc')->value('price');
//                 $low = Db::name('jys_price')->where('type',0)->where('status',2)->where('addtime','>=',strtotime('-24 hour',time()))->order('price asc')->value('price');
//                 if(!empty($high)){
//                     $system[$key]['high'] = $high;
//                 }else{
//                     $system[$key]['high'] = sysconf('jiaoyi_foc');
//                 }
//                 if(!empty($high)){
//                     $system[$key]['low'] = $low;
//                 }else{
//                     $system[$key]['low'] = sysconf('jiaoyi_foc');
//                 }
//             }
        }
        $this->success('',$system);
    }
    
    //单个实时价格
    public function onePrice(){
        $id = input('param.id',1);
        $system = db::name('system_coin')->where('id',$id)->find();
        $dollar = sysconf('usdt_rmb');//db::name('system_coin')->where(['name'=>'usdt'])->value('price');
        $system['cny_price'] = bcmul($system['price'],$dollar, 4);
//         $time = strtotime(date("Y-m-d 00:00:00")); //获取今天0点的时间戳
//         $system['low'] = DB::table('market_hour')->where('day_time', '>', $time)
//                 ->where('currency_id', 1)
//                 ->where('legal_id',4)
//                 ->where('period', '1min')
//                 ->order('mminimum asc')
//                 ->value('mminimum',0);
        $this->success('',$system);
    }

    //实时价格
    public function jiaoyiindex(){
        $jys_rate = sysconf('jys_rate');//交易手续费
        $usdt_rmb = sysconf('usdt_rmb');//db::name('system_coin')->where(['name'=>'USDT'])->value('price');//usdt兑换人民币
        $jiaoyi_nf = sysconf('jiaoyi_nf');//1NF价值USDT限价
        $jiaoyi_nfc = sysconf('jiaoyi_nfc');//1NFC价值USDT限价

        $info['jys_rate'] = $jys_rate;////交易手续费
        $info['usdt_rmb'] = $usdt_rmb;//usdt兑换人民币

        $info['jiaoyi_nf'] = $jiaoyi_nf;//1NF价值USDT限价
        //$info['nf_rmb'] = bcmul($usdt_rmb,$jiaoyi_nf,4);//当前价格价值rmb
        $info['nf_rmb'] = $usdt_rmb;//当前价格价值rmb
        $buyinfo = Db::name('jys_buylist')->where('name','NF')->order('price desc')->find();
        $info['buynf_rmb'] = bcmul($info['nf_rmb'],$buyinfo['price'],4);//当前买单价格价值rmb
        $info['buynf'] = $buyinfo['price'];

        $info['jiaoyi_nfc'] = $jiaoyi_nfc;//1NF价值USDT限价
        //$info['nfc_rmb'] = bcmul($usdt_rmb,$jiaoyi_nfc,4);//当前价格价值rmb
        $info['nfc_rmb'] = bcmul($usdt_rmb,$jiaoyi_nfc,4);//当前价格价值rmb
        $buyinfoc = Db::name('jys_buylist')->where('name','NTF')->order('price desc')->find();
        $info['buynfc_rmb'] = bcmul($info['nfc_rmb'],$buyinfoc['price'],4);//当前买单价格价值rmb
        $info['buynfc'] = $buyinfoc['price'];

        $this->success('',$info);
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
        if(sysconf('open_shijia') == 1){
            $price = $jiaoyi_foc['price'];
        }else{
            $price = $xian_foc;
        }
        $cny = bcmul($usdt_rmb,$price,4);
        //各显示（10条）
//         $buylist = db::name('jys_price')->where(['type'=>0])->where('status',$status)->order('price desc')->limit(5)->select();
//         $selllist = db::name('jys_price')->where(['type'=>1])->where('status',$status)->order('price asc')->limit(5)->select();
        $buylist = db::name('jys_buylist')->where('state','0')->field("price,sum(leavenum) as num")->order('price desc')->group('price')->limit(5)->select();
        $selllist = db::name('jys_selllist')->where('state','0')->field("price,sum(leavenum) as num")->order('price asc')->group('price')->limit(5)->select();
        $this->success('',['buylist'=>$buylist,'selllist'=>$selllist,'price'=>$price,'cny_price'=>$cny,'lastprice'=>'0.200000','timeprice'=>'0.100000']);
    }

    //挂买单pot
    public function buyOrderOutOrInto()
    {
        $language =  language($this->lang,'jystrade','buyOrderOutOrInto');
        $isOpen = sysconf('openC2c');
        $foc_low = sysconf('jiaoyi_foc_low');
        $foc_high = sysconf('jiaoyi_foc_high');
        if($isOpen == 0){
          $this->error($language['isCanbuy']); 
        }
        $data = input('post.');
        $validate = Validate::make([
            'type'=>'require',//0买入1卖出
            //'type1'=>'require',//0市价交易1限价交易
            'price'=>'require',//单价
            'totalnum'=>'require',//总数量
//             'paypassword' =>'require',
        ], [
            'price.require' => $language['jgbnwk'],
            'totalnum.require' =>$language['jyslbnwk'],
//             'paypassword.require' => $language['zfmmk'],
        ]);
        $validate->check($data) || $this->error($validate->getError());
//         date('Y-m-d 23:23:59',strtotime('-' .$i.' day'))
        if($this->wx_user['is_renzheng'] == 1)$this->error($language['zhanghaoshangwujihuo']);
        if($this->wx_user['dongjie'] == 1)$this->error($language['dongjie']);
//         if($this->wx_user['paypassword'] != md5($data['paypassword']))$this->error($language['mimacuowu']);

        if( $data['price']<=0){
            $this->error($language['jgyw']);
        }
        if( $data['price']<$foc_low){
            $this->error($language['jgbdy'].$foc_low);
        }
        if( $data['price']>$foc_high){
            $this->error($language['jgbgy'].$foc_high);
        }
        if( $data['totalnum']<=0){
            $this->error($language['jyslyy']);
        }
        if( $data['status'] == 2){
            $name = 'FOC';
        }else{
            $name = 'FOC';
        }
        if($data['type'] == 0){
            //var_dump( $this->error($language['slbzxy']));
            $money = $data['price']*$data['totalnum'];
//             $rate_money = sysconf('jys_rate')*$money*0.01;//手续费这个不扣，撮合是扣到账foc
            $aturnover = $money;
            if($this->wx_user['account_money'] <  $aturnover){
                $this->error($language['slbzxy'].$aturnover.$language['g']);
            }
            //进行挂买单
            $arr = [
                'ordersn'=>'B'.makeRand(),
                'name'=>$name,
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
            $res_id1 = $res[] = mlog($this->wx_user_id, 'account_money', -$aturnover, "交易所挂买单{$name}{$data['totalnum']},单价{$data['price']}，扣除usdt{$aturnover}", 'buylist','','10',$res['orderid']);
            if (check_arr($res)) {
                Db::commit();
                action('apiv1/robot/jiaoyiorderlist');
                action('apiv1/task/match');
                $this->success($language['mrcgddpp']);
            } else {
                Db::rollback();
                $this->error($language['mrsb']);
            }
        }else{
            if( $data['status'] == 2){
                $moneyname = 'account_foc';
            }else{
                $moneyname = 'account_score';
            }
            Db::startTrans();
            //进行挂买单
            $aturnover = $data['price']*$data['totalnum'];
            $rate_money = sysconf('jys_rate')*$data['totalnum']*0.01;
            $total = $data['totalnum'] + $rate_money;//总共需要扣除数量
            if($moneyname == 'account_score'){
                if($this->wx_user[$moneyname] <  $data['totalnum']){
                    $this->error($language['slbzxy'].$data['totalnum'].$language['gnac']);
                }
                $total = $data['totalnum'] + 2*$rate_money;
                //进行挂买单
                if($this->wx_user['account_foc'] <  $total){
                    $this->error($language['xyjy']);
                }
                if($this->wx_user['gz_time'] < time()){
                    $this->error($language['gzbz']);
                }
                if($this->wx_user['gz_foc'] < $data['totalnum']){
                    $this->error($language['gzbz']);
                }
                $arr = [
                    'ordersn'=>'S'.makeRand(),
                    'name'=>$name.'T',
                    'uid' => $this->wx_user_id,
                    'uname'=> $this->wx_user['email'],
                    'price'=> $data['price'],
                    'totalnum' => $data['totalnum']*2,
                    'leavenum' => $data['totalnum']*2,
                    'aturnover'=> $aturnover,//usdt为单位
                    'addtime' => time(),
                    'tax_rate'=>sysconf('jys_rate'),
                    'tax_money'=>$rate_money*2
                ];
                $res['orderid'] = Db::name('jys_selllist')->insertGetId($arr);
                //共振额度
                $res[] = Db::name('store_member')->where(['id' => $this->wx_user_id])->setDec('gz_foc',$data['totalnum']);
                $res_id2 = $res[] = mlog($this->wx_user_id, 'account_foc', -$total, "交易所挂卖单扣除{$name}T{$data['totalnum']},手续费{$name}T{$arr['tax_money']},单价{$data['price']}，usdt{$aturnover}", 'selllist','','10',$res['orderid']);
                $res[] = mlog($this->wx_user_id, $moneyname, -$data['totalnum'], "交易所挂卖单扣除{$name}{$data['totalnum']},单价{$data['price']}，usdt{$aturnover}", 'selllist','','10',$res['orderid']);
            }else{
                if($this->wx_user[$moneyname] <  $total){
                    $this->error($language['slbzxy'].$total.$language['gnac']);
                }
                $arr = [
                    'ordersn'=>'S'.makeRand(),
                    'name'=>$name,
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
                $res['orderid'] = Db::name('jys_selllist')->insertGetId($arr);
                $res_id2 = $res[] = mlog($this->wx_user_id, $moneyname, -$total, "交易所挂卖单扣除{$name}{$data['totalnum']},手续费{$name}{$rate_money},单价{$data['price']}，usdt{$aturnover}", 'selllist','','10',$res['orderid']);
            }
            if (check_arr($res)) {
                Db::commit();
                action('apiv1/robot/jiaoyiorderlist');
                action('apiv1/task/match');
                $this->success($language['mcccddpp']);
            } else {
                Db::rollback();
                $this->error($language['mcsb']);
            }
        }
    }

    /*
     * 撤销订单
     * */
    public function revokeorder(){
        $language =  language($this->lang,'jystrade','revokeorder');
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
            if($orderInfo['name'] == 'FOC'){
                $moneyname = 'account_foc';
                $res_id1 = $res[] = mlog($orderInfo['uid'], $moneyname,$nac, '取消卖单'.$orderInfo['ordersn'].'手续费' . $rate_nac."实际退回".$nac, 'cancel_C2c_order', $orderId,'13');
            }else{
                $res[] = Db::name('store_member')->where(['id' => $orderInfo['uid']])->setInc('gz_foc',$orderInfo['leavenum']/2);
                $moneyname = 'account_score';
                $foc_tui = ($orderInfo['leavenum']/2)+$rate_nac;
                $res_id1 = $res[] = mlog($orderInfo['uid'], 'account_foc',$foc_tui, '取消卖单'.$orderInfo['ordersn'].'手续费' . $rate_nac."实际退回".$foc_tui, 'cancel_C2c_order', $orderId,'13');
                $res_id1 = $res[] = mlog($orderInfo['uid'], $moneyname,$orderInfo['leavenum']/2, '取消卖单'.$orderInfo['ordersn']."实际退回".($orderInfo['leavenum']/2), 'cancel_C2c_order', $orderId,'13');
            }
            //bagslanguage($res_id1['1'],$orderInfo['ordersn'],$rate_nac,$nac,'',28,29,30);
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
            $usdt =  $orderInfo['leavenum']* $orderInfo['price'];
            $res_id2 = $res[] = mlog($orderInfo['uid'], 'account_money', $usdt, '取消买单'.$orderInfo['ordersn'].'实际退回usdt'.$usdt, 'cancel_C2c_order', $orderId,'13');
            //bagslanguage($res_id2['1'],$orderInfo['ordersn'],$rate_usdt,$usdt,'',31,29,32);
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
     * 我的订单
     * */
    public function myorder(){
        $page = request()->param("pageNo", 1);
        $pnum = request()->param("pnum", 10);
        $start = ($page - 1) * $pnum;
        $status = request()->param("status", 0);
        if($status == 0){
            $order_buy = db::name('jys_buylist')->field('id,ordersn,name,price,totalnum,leavenum,state,tax_money,addtime')->where(['uid'=>$this->wx_user_id,'state'=>0])->select();
            $order_sell = db::name('jys_selllist')->field('id,ordersn,name,price,totalnum,leavenum,state,tax_money,addtime')->where(['uid'=>$this->wx_user_id,'state'=>0])->select();
        }else{
            $order_buy = db::name('jys_buylist')->field('id,ordersn,name,price,totalnum,leavenum,state,tax_money,addtime')->where(['uid'=>$this->wx_user_id,])->where('state','<>',0)->select();
            $order_sell = db::name('jys_selllist')->field('id,ordersn,name,price,totalnum,leavenum,state,tax_money,addtime')->where(['uid'=>$this->wx_user_id])->where('state','<>',0)->select();
        }
        $order = array_merge($order_buy,$order_sell);
        // 调用php内置array_multisort函数
        array_multisort(array_column($order,'addtime'),SORT_DESC,$order);
        $order= array_slice($order,$start,10);
        foreach ($order as &$vl){
            $vl['name'] = 'FOC/USDT';
            if($status == 0){
                $vl['totalnum'] = $vl['leavenum'];
            }
            $vl['addtime'] = date('Y-m-d H:i:s',$vl['addtime']);
            if(strstr($vl['ordersn'], 'S')){
                $vl['buy_sell'] = 1;
            }else{
                $vl['buy_sell'] = 0;
            }
        }
        $this->success('',['order'=>$order]);
    }
    
    //K线头部
    public function kxiantup(){
        $type = input('param.id',1);//1NF 2NFC
        $dollar = sysconf('usdt_rmb');//db::name('system_coin')->where(['name'=>'usdt'])->value('price');
        if($type == 2){
            $info['price'] = sysconf('jiaoyi_nfc');
            $info['amount'] = Db::name('jys_price')->where('type',0)->where('status',2)->where('addtime','>=',strtotime('-24 hour',time()))->sum('num');

            $numpr = Db::name('jys_price')->where('type',0)->where('status',2)->where('addtime','>=',strtotime('-24 hour',time()))->sum('price');
            $counpr = Db::name('jys_price')->where('type',0)->where('status',2)->where('addtime','>=',strtotime('-24 hour',time()))->count();
            $info['change'] = bcmul(bcdiv(bcsub(sysconf('jiaoyi_nfc'),bcdiv($numpr,$counpr,6),6),sysconf('jiaoyi_nfc'),6),100,4);
            $info['dollar'] = bcmul (sysconf('jiaoyi_nfc'),$dollar, 4);
            $info['img'] = sysconf('nfc_img');
            $high = Db::name('jys_price')->where('type',0)->where('status',2)->where('addtime','>=',strtotime('-24 hour',time()))->order('price desc')->value('price');
            $low = Db::name('jys_price')->where('type',0)->where('status',2)->where('addtime','>=',strtotime('-24 hour',time()))->order('price asc')->value('price');
            if(!empty($high)){
                $info['high'] = $high;
            }else{
                $info['high'] = sysconf('jiaoyi_nf');
            }
            if(!empty($high)){
                $info['low'] = $low;
            }else{
                $info['low'] = sysconf('jiaoyi_nf');
            }
        }else{
            $info['price'] = sysconf('jiaoyi_nf');
            $info['amount'] = Db::name('jys_price')->where('type',0)->where('status',1)->where('addtime','>=',strtotime('-24 hour',time()))->sum('num');

            $numpr = Db::name('jys_price')->where('type',0)->where('status',1)->where('addtime','>=',strtotime('-24 hour',time()))->sum('price');
            $counpr = Db::name('jys_price')->where('type',0)->where('status',1)->where('addtime','>=',strtotime('-24 hour',time()))->count();
            $info['change'] = bcmul(bcdiv(bcsub(sysconf('jiaoyi_nf'),bcdiv($numpr,$counpr,6),6),sysconf('jiaoyi_nf'),6),100,4);
            $info['dollar'] = bcmul(sysconf('jiaoyi_nf'),$dollar, 4);
            $info['img'] = sysconf('nf_img');
            $high = Db::name('jys_price')->where('type',0)->where('status',1)->where('addtime','>=',strtotime('-24 hour',time()))->order('price desc')->value('price');
            $low = Db::name('jys_price')->where('type',0)->where('status',1)->where('addtime','>=',strtotime('-24 hour',time()))->order('price asc')->value('price');
            if(!empty($high)){
                $info['high'] = $high;
            }else{
                $info['high'] = sysconf('jiaoyi_nf');
            }
            if(!empty($high)){
                $info['low'] = $low;
            }else{
                $info['low'] = sysconf('jiaoyi_nf');
            }
        }
        $this->success('',$info);
    }
    
    /*
     * 激活旷工
     * */
    public function mykuanggong(){
        $language =  language(Cache::get('lan_type'),'login','register');
        $biaoshi = input('param.biaoshi');
        !$biaoshi && $this->error($language['shoujibiaoshicuowu']);

        $num = Db::name('wang_member_mem')->where('biaoshi',$biaoshi)->count();//总数量

        $useridarray = Db::name('wang_member_mem')->where('biaoshi',$biaoshi)->field('id')->select();
        $userid = array_column($useridarray,'uid');
        $userid[] = $this->wx_user_id;
        $useryouxiao = Db::name('store_member')->where('is_renzheng',2)->whereIn('id',$userid)->count();//有效数量

        $info['numuser'] = $num;
        $info['usernum'] = $useryouxiao;

        $info['numchuangshi'] = 0;
        $info['chuangshi'] = 0;
        $info['nf'] = $this->wx_user['account_score'];
        $info['address'] = $this->wx_user['address'];
        $info['time'] = date('Y-m-d H:i:s');

        $info['jihuonum'] = Db::name('store_member')->where('is_renzheng',2)->where('id',$this->wx_user_id)->count();//有效数量;;
        $info['sebeinum'] = 1;
        $this->success('',['order'=>$info]);
    }
    
    public function getToken(){
        $v = 1;
        $key = mt_rand();
        $hash = hash_hmac("sha1", $v . mt_rand() . time(), $key, true);
        $token = str_replace('=', '', strtr(base64_encode($hash), '+/', '-_'));
        return $token;
    }

    public function prf($param,$path='debug/')
	{
		$style = is_bool($param) ? 1 : 0;
		if($style){
			$outStr = "\r\n";
			$outStr .='<------------------------------------------------------------------------';
			$outStr .= "\r\n";
			$outStr .= date('Y-m-d H:i:s',time());
			$outStr .= "\r\n";
			$outStr .= $param == TRUE ? 'bool:TRUE' : 'bool:FALSE';
			$outStr .= "\r\n";
		}else{
			$outStr = "\r\n";
			$outStr .='<------------------------------------------------------------------------';
			$outStr .= "\r\n";
			$outStr .= date('Y-m-d H:i:s',time());
			$outStr .= "\r\n";
			$outStr .= print_r($param,1);
			$outStr .= "\r\n";
		}

		$backTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		unset($backTrace[0]['args']);
		$outStr .= print_r($backTrace[0],1);
	    $outStr .='------------------------------------------------------------------------>';
		$outStr .= "\r\n";
		$path .= date('Y-m-d',time());
		file_put_contents($path.'-log.txt',$outStr,FILE_APPEND);
	}
}
