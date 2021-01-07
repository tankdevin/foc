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
class Index extends Base
{
    const TSDJS = 900;//投诉倒计时
    const TSJZ = 3600;//投诉截止时间

    /*
     * 个人信息
     * */
    public function userinfo(){
        $this->success('',['email'=>$this->wx_user['email'],'id'=>$this->wx_user['num_id'],'headimg'=>$this->wx_user['headimg'],'nickname'=>$this->wx_user['nickname'],'account_money'=>$this->wx_user['account_money'],'account_score'=>$this->wx_user['account_score'],'jys_rate'=>sysconf('jys_rate')]);
    }
    /*
     * 我的钱包
     * */
    public function profile()
    {
        $language =  language(Cache::get('lan_type'),'index','profile');
        $data = input('post.');

        if(!empty($data['payment']) || !empty($data['paypassword'])) {

            $validate = Validate::make([
                'payment' => 'require',
                'paypassword' =>'require',
            ], [
                'payment.require' => $language['qbzdbnwk'],
                'paypassword.require' => $language['zfmmbnwk'],
                //'safe_num.require' => $language['aqmbnwk'],
            ]);
            $validate->check($data) || $this->error($validate->getError());
            //支付密码验证
            //安全码验证
            $store_member_payment = Db::name('store_member_payment')->where(['uid'=>$this->wx_user_id])->find();
            if(!$store_member_payment){
                $add_list = Db::name('store_member_payment')->insert([
                    'uid' => $this->wx_user_id,
                    'type' => 3,
                    'payment' => $data['payment'],
                    'mark'=>'',
                    'addtime' => time(),
                    'state'=>1
                ]);
            }else{
                $add_list = Db::name('store_member_payment')->where(['uid'=>$this->wx_user_id])->update(['payment'=>$data['payment']]);
            }
            if($add_list){
                $this->success($language['bccc']);
            }else{
                $this->error($language['bcsb']);
            }
        }else{
            //查看提币地址
            $store_member_payment = db::name('store_member_payment')->field('payment')->where(['uid'=>$this->wx_user_id,'type'=>3,'state'=>1])->find();
            $this->success('',['account_money'=>$this->wx_user['account_money'],'account_score'=>$this->wx_user['account_score'],'payment'=>$store_member_payment['payment']]);
        }
    }

    /*
     * usdt信息
     * */
    public function userUsdt(){
       $language =  language(Cache::get('lan_type'),'userUsdt','userUsdt');
        //var_dump($language);
        $bags_log = db::name('bags_log')->alias('a')->leftJoin('bags_language b', 'a.id=b.b_id')
            ->where(['uid'=>$this->wx_user_id,'type'=>'account_money'])
            ->page($this->page, $this->max_page_num)
            ->order('a.id desc')->select();
        foreach($bags_log as $k=>$val){
            $bags_log[$k]['language'] =  $language[$val['fy_state1']].$val['money1'].$language[$val['fy_state2']].$val['money2'].$language[$val['fy_state3']].$val['money3'].$language[$val['fy_state4']].$val['money4'];
        }
        $this->success('',['account_money'=>$this->wx_user['account_money'],'bags_log'=>$bags_log]);
    }
    /*
    * nac信息
    * */
    public function userNac(){
        $language =  language(Cache::get('lan_type'),'userUsdt','userUsdt');
        $bags_log = db::name('bags_log')->alias('a')->leftJoin('bags_language b', 'a.id=b.b_id')
            ->where(['uid'=>$this->wx_user_id])
            ->where('type', 'account_score')
            ->page($this->page, $this->max_page_num)
            ->order('a.id desc')->select();
        foreach($bags_log as $k=>$val){
            $bags_log[$k]['language'] =  $language[$val['fy_state1']].$val['money1'].$language[$val['fy_state2']].$val['money2'].$language[$val['fy_state3']].$val['money3'].$language[$val['fy_state4']].$val['money4'];
        }
        $this->success('',['account_score'=>$this->wx_user['account_score'],'bags_log'=>$bags_log]);
    }

    /*
     * 我的团队人数
     * */
    public function teamNum(){
        //有效玩家值的是只要在pot里面排过一单都算上
        $this->success('',['tj_num'=>$this->wx_user['tj_num'],'xy_tj_num'=>$this->wx_user['xy_tj_num']]);
    }


    //实名认证列表
    public function idcardlist()
    {
        $list = Db::name('store_member_idcard')->where(['uid' => $this->wx_user_id])->find();
        $this->success($list);
    }


//    轮播图
    public function banner()
    {
        $type =  $this->request->get('type');
        if(!$type){
            $type = 'business';
        }else{
            $type = 'news_banner';
        }
        $list = Db::name('es_article')->field('img')->where(['type' =>$type, 'status' => 1])->select();
        foreach ($list as $k => $v) {

            $v[$k]['img'] = str_replace('http://' . $_SERVER['HTTP_HOST'], '', $v['img']);
        }
        $this->success('',$list);
    }
    //新闻轮播图
    public function business()
    {
        $list = Db::name('es_article')->where(['type' => 'news_banner', 'status' => 1])->select();
        foreach ($list as $k => &$v) {
            $v['img'] = str_replace('http://' . $_SERVER['HTTP_HOST'], '', $v['img']);
        }
        $this->success($list);
    }

    //公告   faq 常见问题   fingerpost 新手指南  about 关于我们
    public function notice()
    {
        //$param = input('param.');
        $param['type'] = 'noticle';
        $res = Db::name('es_article')->where(['type' => $param['type'],'status'=>1])->page($this->page, $this->max_page_num)->order('id desc')->select();
        $this->success('',$res);
    }
    //公告   faq 常见问题   fingerpost 新手指南  about 关于我们
    public function about()
    {
        //$param = input('param.');
        $param['type'] = 'about';
        $res = Db::name('es_article')->where(['type' => $param['type'],'status'=>1])->order('id desc')->value('content');
        $this->success('',$res);
    }


    /*qrcode
   * 我的分享
   * */
    public function qrcode()
    {
        
        $contentUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/index.html#/createAddress?tjcode=' . $this->wx_user['num_id'];
        $h5Url = 'http://api.k780.com:88/?app=qr.get&data=' . urlencode($contentUrl);
        //$h5Url = 'http://qr.liantu.com/api.php?text=' . urlencode($contentUrl);
        $qrcode = db('store_member')->where(array('id' => $this->wx_user['id']))->value('qrcode');
        if (empty($qrcode)) {
            $file = 'upload/' . md5(time() . $this->wx_user['phone']) . '.png';
            if (file_put_contents('./' . $file, file_get_contents($h5Url))) {
                $appRoot = request()->root(true); // 去掉参数 true 将获得相对地址
                $uriRoot = preg_match('/\.php$/', $appRoot) ? dirname($appRoot) : $appRoot;
                $file = $uriRoot . '/' . $file;
                Db::name("store_member")->where("id", $this->wx_user['id'])->update([
                    'qrcode' => $file,
                ]);
                response($file);
                $this->success('',['qrcodeUrl' =>$file,'linkUrl' =>$contentUrl]);
            }
        }
        $this->success('',['qrcodeUrl' =>$qrcode,'linkUrl' =>$contentUrl]);

    }
    /*
    pc版的推广链接
    */
     public function qrcode1()
    {
        //https://www.noahsark.work/#/register/?tjcode=11111
        $contentUrl = 'http://www.noahsark.work/#/register/?tjcode=' . $this->wx_user['num_id'];

        $h5Url = 'http://qr.liantu.com/api.php?text=' . urlencode($contentUrl);
        $qrcode = db('store_member')->where(array('id' => $this->wx_user['id']))->value('qrcode1');
        if (empty($qrcode)) {
            $file = 'upload/' . md5(time() . $this->wx_user['phone']) . '.png';
            if (file_put_contents('./' . $file, file_get_contents($h5Url))) {
                $appRoot = request()->root(true); // 去掉参数 true 将获得相对地址
                $uriRoot = preg_match('/\.php$/', $appRoot) ? dirname($appRoot) : $appRoot;
                $file = $uriRoot . '/' . $file;
                Db::name("store_member")->where("id", $this->wx_user['id'])->update([
                    'qrcode1' => $file,
                ]);
                response($file);
                $this->success('',['qrcodeUrl' =>$file,'linkUrl' =>$contentUrl]);
            }
        }
        $this->success('',['qrcodeUrl' =>$qrcode,'linkUrl' =>$contentUrl]);

    }

    //公告详情
    public function detail()
    {
        $param = input('param.');
        $res = Db::name('es_article')->where(['id' => $param['id']])->find();
        $this->success('',$res);
    }

    /*
     * 绑定银行卡
     * */
    public function bank()
    {
        $data = input('post.');
        $validate = Validate::make([
            'truename' => 'require',
            'bankname' => 'require',
            'bankcard' => 'require',
            'banksite' => 'require',
            'security_word' => 'require',
        ], [
            'truename.require' => '真实姓名不能为空！',
            'bankname.require' => '银行卡名不能为空！',
            'bankcard.require' => '银行卡号不能为空！',
            'banksite.require' => '银行开户地址不能为空!',
            'security_word.require' => '安全词不能为空!',
        ]);
        $validate->check($data) || $this->error($validate->getError());

        if (!preg_match('/^([1-9]{1})(\d{14}|\d{18}|\d{15}|\d{17})$/', $data['bankcard'])) {
            $this->error('银行卡号格式不正确!!');
        }
        $bantrue = Db::name('store_member_idcard')->where(['uid' => $this->wx_user_id])->value('truename');
        $data['truename'] != $bantrue && $this->error('银行卡真实姓名需要与实名认证姓名相同');
        $data['uid'] = $this->wx_user_id;
        $data['addtime'] = time();
        //判断安全词
        $store_member = db::name('store_member')->where(['id'=>$this->wx_user_id,'security_word'=>$data['security_word']])->find();
        if(!$store_member){
            $this->error('安全词错误');
        }
        Db::startTrans();
        unset($data['security_word']);
        $res[] = DataService::other_save('store_member_bank', $data, ['uid' => $this->wx_user_id]);
        if (check_arr($res)) {
            Db::commit();
            $this->success();
        } else {
            Db::rollback();
            $this->error();
        }
    }

    //银行卡列表
    public function banklist()
    {
        $list = Db::name('store_member_bank')->where(['uid' => $this->wx_user_id])->find();
        $this->success($list);
    }

    //更改密码

    public function password()
    {
        $language =  language(Cache::get('lan_type'),'index','password');
        $post = input('post.');
        !$post['password'] && $this->error($language['dlmmbnwk']);
        !$post['newpassword'] && $this->error($language['xmmbnwk']);
        !$post['repassword'] && $this->error($language['qrxmmbnwk']);
        //!$post['code'] && $this->error('验证码不能为空');
        if ($post['newpassword'] != $post['repassword'])
            $this->error($language['lcddmmbyz']);
        //$post['code'] != $this->getSmsCode('code', $this->wx_user['phone']) && $this->error('短信验证码错误:(');
        $store_member = Db::name('store_member')->where(['id' => $this->wx_user_id,'password'=>md5($post['password'])])->find();
        if(!$store_member){
            $this->error($language['dlmmywqsr']);
        }
        if(md5($post['newpassword']) == $this->wx_user['password']){
            $this->error($language['ymmhxmmbnyyy']);
        }
        $res = Db::name('store_member')->where(['id' => $this->wx_user_id])->update(['password' => md5($post['newpassword'])]);
        if ($res) {
            $this->success($language['mmxgcc']);
        } else {
            $this->error($language['mmxgsb']);
        }
    }

    /*
       * 修改支付密码
       * */

    public function paypassword()
    {
        $language =  language(Cache::get('lan_type'),'index','paypassword');
        $data = input('post.');
        if($data['paypassword'] || $data['newpaypassword'] || $data['repaypassword']){
            if(!$this->wx_user['paypassword']){
                !$data['newpaypassword'] && $this->error($language['xzfmmbnwk']);
                !$data['repaypassword'] && $this->error($language['qrzfmmbnwk']);
            }else{
                !$data['paypassword'] && $this->error($language['zfmmbnwk']);
                $store_member = Db::name('store_member')->where(['id' => $this->wx_user_id,'paypassword'=>md5($data['paypassword'])])->find();
                if(!$store_member){
                    $this->error($language['zfmmbzq']);
                }
            }
            if ($data['newpaypassword'] != $data['repaypassword']){
                $this->error($language['lczfmmbyz']);
            }
            if(md5($data['newpaypassword']) == $this->wx_user['paypassword']){
                $this->error($language['yzfmmhxzfmmbnyy']);
            }
            $res = Db::name('store_member')->where(['id' => $this->wx_user_id])->update(['paypassword' => md5($data['newpaypassword'])]);
            if ($res) {
                $this->success($language['zfmmxgcg']);
            } else {
                $this->error($language['zfmmxgsb']);
            }
        }else{
            if(!$this->wx_user['paypassword']){
                //不显示
                $state = 1;
            }else{
                //显示
                $state = 0;
            }
            $this->success('',['state'=>$state]);
        }

    }
    /*
     * 绑定收款码
     * */
    public function payment()
    {
        $post = input('post.');
        !$post['payment'] && $this->error('不能为空');
        !$post['paymentimg'] && $this->error('二维码图片不能为空');
        !$post['security_word'] && $this->error('安全词不能为空');
        $post['addtime'] = time();
        !$post['type'] && $this->error('请传类型');
        $post['uid'] = $this->wx_user_id;
        $store_member = db::name('store_member')->where(['id'=>$this->wx_user_id,'security_word'=>$post['security_word']])->find();
        if(!$store_member){
            $this->error('安全词错误');
        }
        $res = DataService::other_save('store_member_payment', $post, ['uid' => $this->wx_user_id, 'type' => $post['type']]);

        if ($res) {
            $this->success();
        } else {
            $this->error();
        }
    }

    public function paymentlist()
    {
        $param = input('param.');
        if ($param['type'] == 1) {
            $res = Db::name('store_member_payment')->where(['uid' => $this->wx_user_id, 'type' => 1])->find();
        } else {
            $res = Db::name('store_member_payment')->where(['uid' => $this->wx_user_id, 'type' => 2])->find();
        }
        $this->success($res);
    }




    //参数
    public function parameter()
    {
        //团队粉丝
        $par['fensi'] = Db::query("SELECT count(*) as sub_person_count  FROM `store_member` WHERE FIND_IN_SET($this->wx_user_id,all_leader)")[0]['sub_person_count'];
        //直接分享粉丝
        $par['zfensi'] = Db::name('store_member')->where(['first_leader' => $this->wx_user_id])->count();
        $drive = Db::name('store_member')->where(['first_leader' => $this->wx_user_id])->where('member_level', '>=', 2)->count();
//        $zhitui = 0;
//        foreach ($drive as $k => $v) {
//            $tempUserTotal = Db::name("store_order")->where(['mid' => $v['id'], 'type' => 1])->sum('real_price');
//            if ($tempUserTotal > 100) {
//                $zhitui++;
//            }
//        }

        $par['zhitui'] = $drive;
//
//        //直推正式
//        $par['zhitui'] = array_sum(array_column($drive, 'count'));


        $shiming = Db::name('store_member')->where(['first_leader' => $this->wx_user_id])->select();
        foreach ($shiming as $k => $v) {
            $shiming[$k]['count'] = Db::name('store_member_idcard')->where(['status' => 1, 'uid' => $v['id']])->count();
            //直推实名
        }
        $par['shi'] = array_sum(array_column($shiming, 'count'));

        $this->success('', $par);
    }


    //收益明细
    public function detailed()
    {
        $teamUserList = Db::name("store_member a")
            ->field('a.*,c.title')
            ->join("sys_level c", "c.id = a.member_level")
            ->where("a.first_leader", $this->wx_user_id)
            ->page($this->page, $this->max_page_num)
            ->order('a.member_level desc')
            ->select();
        foreach ($teamUserList as &$v) {
            $v['create_at'] = explode(' ', $v['create_at'])[0];
            $v['is_identity'] = Db::name('store_member_idcard')->where(['uid' => $v['id']])->find() ? 1 : 0;
        }
        $this->success('', $teamUserList);
    }

    //留言
    public function message()
    {
        $data = input('param.');
        !$data['content'] && $this->error('请输入您要留言的内容');
        !$data['image'] && $this->error('请上传图片');

        $res = Db::name('es_leave')->insert([
            'uid' => $this->wx_user_id,
            'image' => $data['image'],
            'status' => 0,
            'content' => $data['content'],
            'create_time' => time(),
        ]);

        if ($res) {
            $this->success('留言成功');
        } else {
            $this->error('留言失败');
        }
    }

    /***************************************C2C开始*******************************************************/




    //余额明细
    public function getPdrRecord()
    {
        $result = Db::name('bags_log')
            ->where(['uid' => $this->wx_user_id])
            ->where('type','account_money')
            ->where('state','1')
            /* ->where('extends', 'in', 'sell_pdr,buy_pdr,reduce_power,increase_power,seller_mining,buy_mining,time_out_transaction_money_return,time_out_transaction_money_arrival,cancel_dig_account,increase_pdr_collar,merchant_pdr_shou,merchant_pdr_shou_reduce')*/
            ->page($this->page, $this->max_page_num)
            ->order('id desc')
            ->select();
        $this->success('',$result);
    }

    //获取今日收益
    public function getTodayBonus()
    {
        $result = Db::name('bags_log')
            ->where(['uid' => $this->wx_user_id])
            ->where('money','>',0)
            /*->where('extends', 'in', 'static_bonus,team_bonus_money,share_dig,lettery,pay_mining')*/
            ->whereTime('create_time', 'today')
            ->page($this->page, $this->max_page_num)
            ->select();
        $this->success($result);
    }


    //获取今日收益
    public function getHashDayBonus()
    {
        $orderId = input('post.orderId');
        !$orderId && $this->error('订单ID不能为空');
        $result = Db::name('bags_log')
            ->where(['uid' => $this->wx_user_id,'orderId'=>$orderId])
            ->where('extends', 'in', 'static_bonus')
            ->page($this->page, $this->max_page_num)
            ->select();
        $this->success($result);
    }

    /***************************************C2C结束********************************************************/

    /***************************************交易相关*******************************************************/

    //发送验证码
    public function sendMobileMessage()
    {
        $code = rand(10000, 99999);
        $data = input('param.');
        if (empty($data['phone'])) {
            $data['phone'] = Db::name('store_member')->where(['id' => $this->wx_user_id])->value('phone');
            !$data['phone'] && $this->error('手机号不存在,请先去绑定手机');
            $this->setSmsCode('code', $code, $data['phone']);
            $result = sendMobileMessage($data['phone'], ['code' => $code], '499532');
            $this->success($result);
        } else {
            $this->setSmsCode('code', $code, $data['phone']);
            $result = sendMobileMessage($data['phone'], ['code' => $code], '499532');
            $this->success($result);
        }
    }




    /*****************************扫码转账功能结束*********************************/
    /*
     * $type 类型
     * $phone 手机号
     * */
    public
    function getSmsCode( $type, $phone )
    {
        return \Cache::pull($type . getips() . $phone);
    }

    /*
     * @param $type 类型
     * @param $code 验证码
     * @phone $phone 手机号
     * */
    public
    function setSmsCode( $type, $code, $phone )
    {
        return \Cache::set($type . getips() . $phone, $code);
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
                $arr['hash_money'] = 223121;
            }else{
                $arr['name'] = $k;
                $arr['dollar_money'] = sprintf("%.2f", $arr['last'] / self::DOLLAR_RATE);
                $arr['image'] = 'http://'.$_SERVER['HTTP_HOST'].'/static/btb/'.$k.'.png';
                $arr['hash_money'] = '0.000000';
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


    //银行卡信息
    public function banklistad()
    {
        $list['name'] = sysconf('usename');
        $list['yhname'] = sysconf('yhname');
        $list['yhkahao'] = sysconf('yhkh');
        $this->success('',$list);
    }

    //微信支付宝信息
    public function paymentlistad()
    {
        $param = input('param.');
        if ($param['type'] == 2) {
            $res['name']  =  sysconf('weixinname');
            $res['phone']  =  sysconf('weixin');
            $res['img']  =  sysconf('weixinsk');
            //$res = Db::name('store_member_payment')->where(['uid' => $this->wx_user_id, 'type' => 1])->find();
        } if ($param['type'] == 1) {
        $res['name']  =  sysconf('zhifubaoname');
        $res['phone']  =  sysconf('zhifubao');
        $res['img']  =  sysconf('zhifubaosk');
    }else{
        $res['name'] = sysconf('usename');
        $res['yhname'] = sysconf('yhname');
        $res['yhkahao'] = sysconf('yhkh');
    }
        $this->success('',$res);
    }

    //线下支付
    public function payadd()
    {
        $money = input('code');
        $payimg = input('paymentimg');
        $type = input('type');

        $where = [];
        $where['uid'] = $this->wx_user_id;
        $where['money'] = $money;
        $where['payimg'] =$payimg;
        $where['type'] = $type;
        $where['create_time'] = time();

        if(Db::name('store_pay_list')->insertGetId($where)){
            $this->success('提交成功');
        }else{
            $this->success('提交失败');
        }
    }



    //检查用户
    public function transPhone()
    {
        $phone = input('post.merchatname');
        if($phone){
            $merchatInfo = Db::name('store_member')->alias('a')->leftJoin('store_member_bank b','a.id=b.uid')
                ->where(['a.phone' => $phone])->field('a.nickname,b.truename')->find();
            if($merchatInfo){
                $this->success('',$merchatInfo);
            }else{
                $this->success('无此用户');
            }
        }else{
            $this->error('手机号不能为空');
        }
    }

    //获取收益列表
    public function getBonusList()
    {
        $result = Db::name('bags_log')
            ->where(['uid' => $this->wx_user_id])
            //->where('extends', 'in', 'static_bonus,team_bonus_money,share_dig,lettery,pay_mining')
            ->where('type','account_score')
            ->where('state','1')
            //->whereTime('create_time', 'today')
            ->order('id desc')
            ->page($this->page, $this->max_page_num)
            ->select();
        $this->success($result);

        /* $data = Db::name('store_order a')
             ->field('a.*')
             ->join('store_member b', "a.mid = b.id")
             ->where(['a.mid' => $this->wx_user_id])
             ->page($this->page, $this->max_page_num)
             ->order('a.create_at desc')
             ->select();
         foreach ($data as $k => $v) {
             $data[$k]['bonus_total'] = Db::name("bags_log")->where(['orderId' => $v['id'], 'uid' => $this->wx_user_id])->where('extends', 'in', 'static_bonus')->sum('money'); //获取当前订单的十天内的算力挖矿收益统计
             $data[$k]['create_at'] = str_replace(date('Y') . '-', '', $v['create_at']);
         }
         $this->success('', $data);*/
    }

    /*
     * 保证金明细
     * */
    public function getbzjList()
    {
        $result = Db::name('bags_log')
            ->where(['uid' => $this->wx_user_id])
            //->where('extends', 'in', 'static_bonus,team_bonus_money,share_dig,lettery,pay_mining')
            ->where('type','guarantee_money')
            ->where('state','1')
            //->whereTime('create_time', 'today')
            ->order('id desc')
            ->page($this->page, $this->max_page_num)
            ->select();
        $this->success($result);

        /* $data = Db::name('store_order a')
             ->field('a.*')
             ->join('store_member b', "a.mid = b.id")
             ->where(['a.mid' => $this->wx_user_id])
             ->page($this->page, $this->max_page_num)
             ->order('a.create_at desc')
             ->select();
         foreach ($data as $k => $v) {
             $data[$k]['bonus_total'] = Db::name("bags_log")->where(['orderId' => $v['id'], 'uid' => $this->wx_user_id])->where('extends', 'in', 'static_bonus')->sum('money'); //获取当前订单的十天内的算力挖矿收益统计
             $data[$k]['create_at'] = str_replace(date('Y') . '-', '', $v['create_at']);
         }
         $this->success('', $data);*/
    }

    /*转账记录*/
    public function zhuanzhang()
    {
        $money = $this->request->post('money');
        $username = $this->request->post('username');
        $paypassword = $this->request->post('paypassword');
        if (md5($paypassword) != $this->wx_user['paypassword']) {
            $this->error('交易密码不正确');
        }
        $duifuser = Db::name('store_member')->where(['phone' => $username])->find();
        if(!$duifuser){
            $this->error('用户不存在');
        }
        $money % 10 != 0 && $this->error('操作数量必须是10的倍数');
        $userinfo = Db::name('store_member')->where(['id' => $this->wx_user_id])->find();
        if(empty($userinfo) ||$userinfo['pay_money']<$money){
            $this->error('余额不足');
        }
        Db::startTrans();
        $res[] = mlog($duifuser['id'], 'pay_money', $money, '用户'.$userinfo['phone'].'线上转与', '');
        $res[] = mlog($userinfo['id'], 'pay_money', -$money, '线上转与用户'.$duifuser['phone'].'', '');
        if (check_arr($res)) {
            Db::commit();
            $this->success('操作成功');
        } else {
            Db::rollback();
            $this->error('操作失败');
        }
    }
    /*
     * 激活会员
     *
     * */
    public function jhuser($uid){
        $store_member = Db::name('store_member')->where(['id' =>$uid])->find();
        if($store_member['isjh'] == 0){
            $res = Db::name('store_member')->where('id', $uid)->update(['isjh' => '1','create_jh'=>time()]);
            if($res){
                //添加正式的推荐人数
                db::name('store_member')->where(['id'=>$store_member['first_leader']])->setInc('tj_num',1);

            }
        }
    }


    /*
     * usdt 充值信息
     * */
    public function paymentqukuai()
    {
         //如果存在的话，就能进行生成
            $userinfo = Db::name('store_member')->where(['id' => $this->wx_user_id])->find();
            $contentUrl = $userinfo['address'];
            $h5Url = 'http://api.k780.com:88/?app=qr.get&data=' . urlencode($contentUrl);
            if (empty($userinfo['usdt_online_img'])) {
                $file = 'upload/onlineusdt/' . md5(time() . $userinfo['num_id']) . '.png';
                if (file_put_contents('./' . $file, file_get_contents($h5Url))) {
                    $appRoot = request()->root(true); // 去掉参数 true 将获得相对地址
                    $uriRoot = preg_match('/\.php$/', $appRoot) ? dirname($appRoot) : $appRoot;
                    $file = $uriRoot . '/' . $file;
                    Db::name("store_member")->where(['id' => $this->wx_user_id])->update([
                        'usdt_online_img' => $file,
                    ]);
                    response($file);
                    $this->success('',['img' => $file, 'name' => $contentUrl]);
                }
            }
            $this->success('',['img' => $userinfo['address'], 'name' => $userinfo['address']]);//前端自己生成了二维码
            // $this->success('',['img' => $userinfo['usdt_online_img'], 'name' => $userinfo['address']]);
    }
    


    /*
     * 我的好友（排行榜）
     * */
    public function myFriend()
    {
       $userinfo = Db::name("store_member")->field('id,nickname,num_id,tj_num,first_leader,total_performance,headimg,team_performance,isjh,account_score')->where("id", $this->wx_user_id)->find();
        $tj_uid = $this->request->get('tj_uid');
        if(!$tj_uid){
            $tj_uid = $this->wx_user_id;
        }
        $teamUserList = Db::name("store_member")->field('id,isjh,nickname,num_id,team_performance,tj_num,phone')->where(['first_leader'=>$tj_uid]
        )->order('id desc')->page($this->page, $this->max_page_num)->select();
        foreach($teamUserList as $k=>$v){
            $teamUserList[$k]['tj_num'] = Db::Table('store_member')->where('FIND_IN_SET(:id,all_leader)',['id' => $v['id']])
         ->count();
        }
        //查询总的业绩和新增业绩和今日新增人数
         $now_lincheng = strtotime(date('Y-m-d'));
         $arr_uid = Db::name('store_member')->where('FIND_IN_SET(:id,all_leader)',['id' => $this->wx_user_id])->column('id');
         $arr_uid = implode(',',$arr_uid);
         $new_add_totalmoney = db::name('financial_order')
         ->where('create_at','>',$now_lincheng)
         ->where('uid','in',$arr_uid)
         ->sum('market_price');
         //个人新增（今日）业绩
         $gr_new_add_totalmoney = db::name('financial_order')->where('create_at','>',$now_lincheng)->where(['uid'=>$this->wx_user_id])->sum('market_price');
         $new_add_totalmoney = $new_add_totalmoney + $gr_new_add_totalmoney;
         //团队人数
         $new_add_tjnum = Db::Table('store_member')->where('FIND_IN_SET(:id,all_leader)',['id' => $this->wx_user_id])
         ->count();

         $this->success(['userinfo'=>$userinfo,'teamUserList'=>$teamUserList,'total_performance'=>$userinfo['team_performance'],'new_add_totalmoney'=>$new_add_totalmoney,'new_add_tjnum'=>$new_add_tjnum]);
    }


    /*
    * pos机进行挖矿(usdt进行购买，产生币)
    * */
    public function scoreExchange()
    {
        //$this->success('转账成功');
         $language =  language(Cache::get('lan_type'),'index','scoreExchange');
        $data = input('post.');
        if($data){
            $validate = Validate::make([
                'num' => 'require|gt:0',
                'fl_id'=>'require',
                //'paypassword' => 'require'
            ], [
                'num.require' => $language['slbnwk'],
                'num.gt' => $language['slbxdy0'],
                'fl_id.require' => $language['csyw'],
                //'paypassword.require' => 'paypassword不能为空',
            ]);
            $validate->check($data) || $this->error($validate->getError());
            //首次投资和复投
            $userinfo = Db::name('store_member')->where(['id' => $this->wx_user_id])->find();
            //限制规则
            $sys_scorefl = db::name('sys_level')->where(['id'=>$data['fl_id']])->find();
            if($data['num'] < $sys_scorefl['min_teaming'] || $data['num'] > $sys_scorefl['teaming']){
                $this->error($language['gmslbxz'].$sys_scorefl['min_teaming'].'--'.$sys_scorefl['teaming'].$language['qj']);
            }
            if($userinfo['account_score'] < $data['num']){
                $this->error($language['slbnwk']);
            }
            if($data['num'] % sysconf('pos_beishu') != 0){
                $this->error($language['gmsls'].sysconf('pos_beishu').$language['bs']);
            }
            if ($userinfo) {
                //$this->wx_user['paypassword'] != md5($data['paypassword']) && $this->error('支付密码不正确');
                $this->wx_user['account_score'] < $data['num'] && $this->error($language['slbz']);
                //10的背时起投
                //算力数量
                $sl_num = $data['num'] * $sys_scorefl['sl_rate']*0.01;
                Db::startTrans();
                $order_id= Db::name('financial_order')->insertGetId([
                    'ordersn'=> substr(time(),5) . mt_rand(100, 999),
                    'lc_id' => $data['fl_id'],
                    'uid' => $this->wx_user_id,
                    'uname' => $this->wx_user['email'],
                    'title' => $sys_scorefl['title'],
                    'day' => $sys_scorefl['num'],
                    'create_at' => time(),
                    'market_price' => $data['num'],
                    'sl_rate'=>$sys_scorefl['sl_rate'],
                    'sl_num'=>$sl_num,
                    'tj_rate'=>$sys_scorefl['jt_rate']
                ]);
                $res[] = $order_id;
                //处理打款方
                $res[] = $res_id1 = mlog($this->wx_user_id, 'account_score', -$data['num'], "购买pos矿机,扣除nac{$data['num']}", '','','4',$this->wx_user_id);
                bagslanguage($res_id1['1'],$data['num'],'','','',1);
                $res[] = $res_id2  = mlog($this->wx_user_id, 'wallet_one', $sl_num, "购买pos矿机,增加算力数量{$sl_num}", '','','4',$this->wx_user_id);//算力数量
                bagslanguage($res_id2['1'],$sl_num,'','','',2);
                if (check_arr($res)) {
                    Db::commit();
                    //添加总的业绩
                    $res = Db::name('store_member')->where(['id'=>$this->wx_user_id])->setInc('total_performance',$data['num']);
                    //添加团队业绩
                    $this->addTeamperformance($this->wx_user_id,$data['num']);
                    //推荐奖
                    $award = new Award;
                    $award->tjAward($data['fl_id'],$this->wx_user_id,$data['num'],$order_id);
                    $this->success($language['gmcg']);
                } else {
                    Db::rollback();
                    $this->error($language['gmsb']);
                }
            } else {
                $this->error($language['ffcz']);
            }
        }else{

            //理财产品
            $sys_level = Db::name('sys_level')->select();
            $this->success('',['sys_level' =>$sys_level]);
        }

    }
    /*
     * pos首页
     * */
    public function posindex(){
        $status = $this->request->get('status');
        $map['uid'] = $this->wx_user_id;
        if(!empty($status)){
            if($status == 1){
                $status =0;
            }elseif($status == 2){
                $status =1;
            }
            $map['status'] = $status;
        }
        $map['type'] = 0;
        $financial_order = db::name('financial_order')->where($map)->page($this->page, $this->max_page_num) ->order('id desc')->select();
        //累计收益
        $money = db::name('bags_log')->where(['uid'=>$this->wx_user_id,'type'=>'account_score','extends'=>'wallet_three'])->sum('money');
        //pos总算力
        $wallet_one = Db::name('store_member')->where(['is_disable'=>'1'])->sum('wallet_one');//算力(购买pos的算力)
        $wallet_two = Db::name('store_member')->where(['is_disable'=>'1'])->sum('wallet_two');//直推投入算力（pos有效）
        //poe总算力
        $this->success('',['pos_total_currency'=>sysconf('pos_total_currency'),'sl_num'=>$this->wx_user['wallet_one']+$this->wx_user['wallet_two'],'total_pos_nac'=>$money,'pos_sl'=>$wallet_one+$wallet_two,'financial_order'=>$financial_order]);
    }

    /*
     * pos订单详情
     * */
    public function posOrderinfo(){
        $oid = $this->request->get('oid');
        $financial_order = db::name('financial_order')->where(['id'=>$oid,'uid'=>$this->wx_user_id])->find();
        $this->success('',$financial_order);
    }

    /*
     * 矿池算力详情
     * */
    public function posslDetail(){
        //推荐算力值
        $language =  language(Cache::get('lan_type'),'userUsdt','userUsdt');
        $tj_store_award = db::name('store_award')->where(['type'=>0,'uid'=>$this->wx_user_id,'state'=>0])->sum('money');
        //算力列表
        $bags_log = db::name('bags_log')
            ->alias('a')->leftJoin('bags_language b', 'a.id=b.b_id')
            ->where(['uid'=>$this->wx_user_id,'type'=>'account_money'])
            ->where('uid',$this->wx_user_id)
            ->where('type','in','wallet_one,wallet_two')
            ->page($this->page, $this->max_page_num) ->order('a.id desc')->select();
        foreach($bags_log as $k=>$val){
            $bags_log[$k]['language'] =  $language[$val['fy_state1']].$val['money1'].$language[$val['fy_state2']].$val['money2'].$language[$val['fy_state3']].$val['money3'].$language[$val['fy_state4']].$val['money4'];
        }
        $this->success('',['tj_store_award'=>$tj_store_award,'bags_log'=>$bags_log]);


    }
    /*
     *pos收益
     * type：0  推荐算力列表
     * type：1  见点算力列表
     * */
    public function posprofit(){
        //昨日收益（nac）
        $yesterday = strtotime(date('Y-m-d'));
        $beforeyesterday = strtotime(date('Y-m-d'))-86400;
        $yt_num = db::name('bags_log')->where(['uid'=>$this->wx_user_id,'type'=>'account_score','extends'=>'wallet_three'])->whereBetween('create_time',"$beforeyesterday,$yesterday")->sum('money');
        //挖矿投资（算力）
        $slnum = db::name('bags_log')->where(['uid'=>$this->wx_user_id,'type'=>'account_score','extends'=>'wallet_three'])->sum('sl_num');
        //累计收益（nac）
        $money = db::name('bags_log')->where(['uid'=>$this->wx_user_id,'type'=>'account_score','extends'=>'wallet_three'])->sum('money');
        $bags_log = db::name('bags_log')->where(['uid'=>$this->wx_user_id,'type'=>'account_score','extends'=>'wallet_three'])->page($this->page, $this->max_page_num) ->order('id desc')->select();
        $this->success('',['yt_num'=>$yt_num,'slnum'=>$slnum,'money'=>$money,'bags_log'=>$bags_log]);
    }

    /*
     *推荐挖矿算力(挖矿)
     * */
    public function tjcalculationWk(){
        $language =  language(Cache::get('lan_type'),'index','tjcalculationWk');
        $order = Db::name('store_award')->where(['state' =>0,'type'=>0,'uid'=>$this->wx_user_id])->find();
        if(!$order){
            $this->error($language['cczwx']);
        }
        $sl_num = Db::name('store_award')->where(['type' =>0,'uid'=>$this->wx_user_id,'state'=>0])->sum('sl_num');
        $res = Db::name('store_award')->where(['type' =>0,'uid'=>$this->wx_user_id,'state'=>0])->update(['state'=>1]);

        if($res){
            //增加推荐人的推荐算力
            $res_id =  mlog($this->wx_user_id, 'wallet_two',$sl_num, '推荐算力增加'.$sl_num, 'wallet_two', '', '3');
            bagslanguage($res_id['1'],$sl_num,'','','',3);
            $this->success($language['wkcg']);
        }else{
            $this->error($language['wksb']);
        }
    }


    /*
      * 添加团队业绩(这里的团队业绩不包含个人)
      * */
    public function addTeamperformance($uid,$money){
        $user = db::table('store_member')->where(array('id'=>$uid))->find();
        $all_leader = explode(',',$user['all_leader']);
        array_push($all_leader,$uid);
        $all_leader = array_reverse($all_leader);
        $leader_num = count($all_leader);
        for($i=0;$i<$leader_num;$i++){
            db::name('store_member')->where(['id'=>$all_leader[$i]])->setInc('team_performance',$money);
            //这个时候进行会员升级
            //$this->upLevel($all_leader[$i]);
        }
        //添加团队业绩记录
        //addNewperformance($uid,$money);
    }
    /*
     * 积分兑换(分类)
     * */
    public function scoreExchangeclassify()
    {

        $userInfo = Db::name('store_member')->field('account_money,nickname,headimg')->where(['id' => $this->wx_user_id])->find();
        //可兑换积分
        $userInfo['duihuan_score'] = number_format($userInfo['account_money']*sysconf('beishusy'),2);
        $sys_scorefl = db::name('sys_scorefl')->select();
        $this->success(['userInfo' =>$userInfo,'sys_scorefl'=>$sys_scorefl]);
        }


        /*
         * 首页
         * */
        public function index(){
            //关于级别
            $userInfo = Db::name('store_member')->field('account_money,account_score,guarantee_money,nickname,num_id,headimg,level,phone,address,private,lxcaddress,lxcprivate,region_level')->where(['id' => $this->wx_user_id])->find();
            $userInfo['level_name'] = level($userInfo['level']);
            $userInfo['region_level'] = region_level($userInfo['region_level']);
            $userInfo['lxc_usdt'] = $userInfo['account_score']*sysconf('lxc_dollar');
            $userInfo['usdt_usdt'] = $userInfo['account_money'];
            
            //获取用户的秘钥和地址
            if(!$userInfo['address'] || !$userInfo['private'] || !$userInfo['lxcaddress'] || !$userInfo['lxcprivate']){
                $ethtools = new Ethtools();
                $ethtools->getAddress($uid = $this->wx_user_id);
            }
           
            $this->success($userInfo);
        }

        /*
         * 客服
         * */
        public function kefu(){
            $data = [
              'kfweixin'=>sysconf('kfweixin'),
              'kefutime'=>sysconf('kefutime'),
              'kefuphoto'=>sysconf('kefuphoto'),
            ];
            $this->success('',$data);
        }

    /*
     * usdt充值
     * xianshangRechangUsdt   连上充值
     * rechangUsdt     (usdt充值)
     * touziUsdt       usdt投资
     * shouyiUsdt      usdt收益
     * usdtAward       usdt奖金
     * */

    public function usdtRecord(){
        $extends = $this->request->get('extend');
        if($extends){
            $result = Db::name('bags_log')
                ->where(['uid' => $this->wx_user_id])
                ->where('type','account_money')
                ->where('extends',$extends)
                /* ->where('extends', 'in', 'sell_pdr,buy_pdr,reduce_power,increase_power,seller_mining,buy_mining,time_out_transaction_money_return,time_out_transaction_money_arrival,cancel_dig_account,increase_pdr_collar,merchant_pdr_shou,merchant_pdr_shou_reduce')*/
                ->page($this->page, $this->max_page_num)
                ->order('id desc')
                ->select();
        }else{
            $result = Db::name('bags_log')
                ->where(['uid' => $this->wx_user_id])
                ->where('type','account_money')
                /* ->where('extends', 'in', 'sell_pdr,buy_pdr,reduce_power,increase_power,seller_mining,buy_mining,time_out_transaction_money_return,time_out_transaction_money_arrival,cancel_dig_account,increase_pdr_collar,merchant_pdr_shou,merchant_pdr_shou_reduce')*/
                ->page($this->page, $this->max_page_num)
                ->order('id desc')
                ->select();
        }

        $this->success('',$result);
    }

    /*
    * usdt充值
    * xianshangRechanglxc   连上充值
    * rechanglxc     (usdt充值)
    * touzilxc       usdt投资
    * */

    public function lxcRecord(){
        $extends = $this->request->get('extend');
        if($extends){
            $result = Db::name('bags_log')
                ->where(['uid' => $this->wx_user_id])
                ->where('type','account_score')
                ->where(['extends'=>$extends])
                /* ->where('extends', 'in', 'sell_pdr,buy_pdr,reduce_power,increase_power,seller_mining,buy_mining,time_out_transaction_money_return,time_out_transaction_money_arrival,cancel_dig_account,increase_pdr_collar,merchant_pdr_shou,merchant_pdr_shou_reduce')*/
                ->page($this->page, $this->max_page_num)
                ->order('id desc')
                ->select();
        }else{
            $result = Db::name('bags_log')
                ->where(['uid' => $this->wx_user_id])
                ->where('type','account_score')
                /* ->where('extends', 'in', 'sell_pdr,buy_pdr,reduce_power,increase_power,seller_mining,buy_mining,time_out_transaction_money_return,time_out_transaction_money_arrival,cancel_dig_account,increase_pdr_collar,merchant_pdr_shou,merchant_pdr_shou_reduce')*/
                ->page($this->page, $this->max_page_num)
                ->order('id desc')
                ->select();
        }

        $this->success('',$result);
    }
    /*
     * usdt 账号
     * */
    public function usdtAccount(){
        $userInfo = Db::name('store_member')->field('account_money,dj_usdt,num_id,headimg,level,dj_usdt')->where(['id' => $this->wx_user_id])->find();
        //账户余额
        $userInfo['account_usdt'] = number_format($userInfo['account_money']+$userInfo['dj_usdt'],2);
        $this->success('',$userInfo);
    }

    /*
     * lxc 账号
     * */
    public function lxcAccount(){
        $userInfo = Db::name('store_member')->field('account_score,dj_usdt,num_id,headimg,level,dj_lxc,dj_nubc')->where(['id' => $this->wx_user_id])->find();
        //账户余额
        $userInfo['account_nubc'] = number_format($userInfo['account_score']+$userInfo['dj_nubc'],2);
        $this->success('',$userInfo);
    }

    //lxc区块地址
    public function lxcAddress()
    {
        //如果存在的话，就能进行生成
        $userinfo = Db::name('store_member')->where(['id' => $this->wx_user_id])->find();
        $contentUrl = $userinfo['lxcaddress'];
        $h5Url = 'http://qr.liantu.com/api.php?text=' . urlencode($contentUrl);
        if (empty($userinfo['lxc_online_img'])) {
            $file = 'upload/onlinelxc/' . md5(time() . $userinfo['num_id']) . '.png';
            if (file_put_contents('./' . $file, file_get_contents($h5Url))) {
                $appRoot = request()->root(true); // 去掉参数 true 将获得相对地址
                $uriRoot = preg_match('/\.php$/', $appRoot) ? dirname($appRoot) : $appRoot;
                $file = $uriRoot . '/' . $file;
                Db::name("store_member")->where(['id' => $this->wx_user_id])->update([
                    'lxc_online_img' => $file,
                    'lxcaddress'=>$contentUrl
                ]);
                response($file);
                $this->success('',['img' => $file, 'name' => $contentUrl]);
            }
        }

        $this->success('',['img' => $userinfo['lxc_online_img'], 'name' => $userinfo['lxcaddress']]);
    }

    //线上充值记录
    public function lxcOnlinejl(){
        $result = Db::name('bags_log')
            ->where(['uid' => $this->wx_user_id])
            ->where('type','account_money')
            ->where('state','13')
            /* ->where('extends', 'in', 'sell_pdr,buy_pdr,reduce_power,increase_power,seller_mining,buy_mining,time_out_transaction_money_return,time_out_transaction_money_arrival,cancel_dig_account,increase_pdr_collar,merchant_pdr_shou,merchant_pdr_shou_reduce')*/
            ->page($this->page, $this->max_page_num)
            ->order('id desc')
            ->select();
        $this->success('',$result);
    }

    //提交工单
    public function complaint()
    {
        $language =  language($this->lang,'index','complaint');
        $content = input('post.content');
        $title = input('post.title');
        $imgurl = input('post.image_path');
        if(!$content || !$title){
            $this->error($language['fkxxbnwk']);
        }
        $ts_Arr = [
            'uid' => $this->wx_user_id,
            'username'=>$this->wx_user['phone'],
            'order' => '',//投诉订单ID
            'buid' => '',
            'border' => '', //被投诉订单ID
            'caeate_at' => time(),
            'status' => 0, //0未处理  1已处理
            'title' =>$title,
            'image_path' =>$imgurl,
            'content' => $content,
        ];
        Db::startTrans();
        $res[] = Db::name('store_order_c2c_ts')->insert($ts_Arr);
        if (check_arr($res)) {
            Db::commit();
            $this->success($language['fkcc']);
        } else {
            Db::rollback();
            $this->error($language['fksb']);
        }
    }

    /*
     * 反馈记录
     * */
    public function complaintjl(){
        $page = $this->request->post('page',1);
        $status = $this->request->post('status',0);
        $start = ($page- 1) * 10;
        $store_order_c2c_ts = Db::name('store_order_c2c_ts')->field('id,title,content,status,image_path,reply_content')->where(['uid'=>$this->wx_user_id,'status'=>$status])->order('id desc')->limit($start, 10)->select();
        foreach ($store_order_c2c_ts as &$vl){
            if($vl['image_path']) $vl['image_path'] = json_decode($vl['image_path']);
        }
        $this->success('',$store_order_c2c_ts);
    }
}
