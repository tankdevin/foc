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
class Pottrade extends Base
{


    /*
     * pot首页
     * */
    public function potIndex(){
        $money = db::name('bags_log')->where(['uid'=>$this->wx_user_id,'type'=>'account_score','extends'=>'wallet_six'])->sum('money');
        //pot总算力
        $wallet_four = Db::name('store_member')->where(['is_disable'=>'1'])->sum('wallet_four');//算力（购买pot的算力）
        $wallet_five = Db::name('store_member')->where(['is_disable'=>'1'])->sum('wallet_five');//直推投入算力（pot有效）
        $this->success('',['pot_total_currency'=>sysconf('pot_total_currency'),'sl_num'=>$this->wx_user['wallet_four']+$this->wx_user['wallet_five'],'total_pot_nac'=>$money,'pot_sl'=>$wallet_four+$wallet_five]);
    }
    //挂买单pot
    public function buyOrderOutOrInto()
    {

       $language =  language(Cache::get('lan_type'),'pottrade','buyOrderOutOrInto');
        $data = input('post.');
        $orderId = input('post.orderid', 0);
        $validate = Validate::make([
            'num' => 'require',
            //'real_price' => 'require',
            'paypassword' =>'require',
        ], [
            'num.require' => $language['data']['mrslbuwk'],
            'paypassword.require' => $language['data']['zfmmbnwk'],
        ]);
        if( $data['num']<=0){
            $this->error($language['data']['mcslyw']);
        }
        if($data['num'] % sysconf('pot_tx_bonus') != 0){
            $this->error($language['data']['pdjew'].sysconf('pot_tx_bonus').$language['data']['dbs']);
        }
        $validate->check($data) || $this->error($validate->getError());
        $res = [TRUE];
        //锁定质押币
        $nac_num = $data['num'] * sysconf('zyb_rate')*0.01/sysconf('lxc_dollar');
        if($this->wx_user['account_score'] < $nac_num){
            $this->error($language['data']['nacslbz'].$nac_num.$language['data']['ge']);
        }
        $payment_info = Db::name("store_member_payment")->where(['uid' => $this->wx_user_id,'type'=>3])->find();
        !$payment_info && $this->error($language['data']['qwsddxx']);
        //排单次序
        $lastCount = db::name('ty_buylist')->where(array('uid'=>$this->wx_user_id))->count();
        $pdnum = $lastCount + 1;
        $arr = [
            'uid' => $this->wx_user_id,
            'ordersn' =>'B'.time().rand(1,99),
            'uname'=>$this->wx_user['email'],
            'totalnum' => $data['num'],
            'leavenum' => $data['num'],
            'addtime' => time(),
            'nac_num' => $nac_num,
            'pdnum'=>$pdnum,
            'state'=>1,
            'sl_num'=>$data['num']
        ];
        Db::startTrans();
        $res['orderid'] = Db::name('ty_buylist')->insertGetId($arr);
        $orderid = $res['orderid'];
        $res[] = $res_id= mlog($this->wx_user_id, 'account_score', -$nac_num, "poe排单数量为{$data['num']}usdt，扣除nac{$nac_num}", 'buylist','','4',$this->wx_user_id);
        bagslanguage($res_id['1'],$data['num'],$nac_num,'','',4,5);
        //增加有效会员数量
        if($this->wx_user['is_yx'] == 0){
            //推荐人
            $tjid = db::name('store_member')->where(['id'=>$this->wx_user_id])->value('first_leader');
            db::name('store_member')->where(['id'=>$tjid])->setInc('xy_tj_num',1);
            db::name('store_member')->where(['id'=>$this->wx_user_id])->setField('is_yx',1);
        }
        if (check_arr($res)) {
            Db::commit();
            if ($res['orderid']) {
                $this->success($language['data']['gmcg'],['orderid'=>$orderid]);
            }else{
                $this->error($language['data']['gmsb']);
            }
        } else {
            Db::rollback();
            $this->error($language['data']['gmsb']);
        }
    }

    /*
     * 挂买列表
     * @param state 1待匹配3疑问单4进行中5已完成
     * */
    public function buyList(){
       $state = $this->request->get('state');
       if($state){
           if($state == 4 || $state == 2){
               $buylist =  db::name('ty_buylist')->where(['uid'=>$this->wx_user_id])->where('state','in','2,4')->page($this->page, $this->max_page_num)->order('id desc')->select();
           }else{
               $buylist =  db::name('ty_buylist')->where(['uid'=>$this->wx_user_id,'state'=>$state])->page($this->page, $this->max_page_num)->order('id desc')->select();
           }

       }else{
           $buylist =  db::name('ty_buylist')->where(['uid'=>$this->wx_user_id])->page($this->page, $this->max_page_num)->order('id desc')->select();
       }
       $this->success('',['buylist'=>$buylist]);
    }

    /*
     * 点击买单，出现买单和匹配单
     * */
    public function buydetail(){
        $language =  language(Cache::get('lan_type'),'pottrade','buydetail');
        $bid = $this->request->get('b_id');
        $uid =  $this->wx_user_id;
        $limit1 = sysconf('payhours') * 3600;
        //如果买单的状态为预匹配的时候，订单详情是无法看到的
        $buylist = db::name('ty_buylist')->where(array('id'=>$bid,'uid'=>$uid))->find();
        if($buylist['state'] == 1){
            $this->error($language['dppzwfckddxq'],['state'=>1,'buylist'=>$buylist],'1');exit;
        }
        $matchInfo = db::name('ty_match')->where(array('b_id'=>$bid,'b_uid'=>$uid))->order('id desc')->field('id,ordersn,state,s_uid,num,addtime,paytype')->select();
        if($matchInfo ==false){
            $this->error($language['wzddydppdd'],0,'');exit;
        }
        //获取对方的地址

        foreach( $matchInfo as $k=>$v){
            if($v['state'] ==0){
                $matchInfo[$k]['leavetime'] = $v['addtime'] + $limit1-time();

            }
            $matchInfo[$k]['other_uname'] = getrealname($v['s_uid']);
            $matchInfo[$k]['other_address'] = userpayment($v['s_uid']);
        }
        $buyInfo = db::name('ty_buylist')->where(array('id'=>$bid,'uid'=>$uid))->field('ordersn,totalnum,leavenum,addtime,trading_status,nac_num,endtime,sy_num,state')->find();
        $this->success('',['buyInfo'=>$buyInfo,'matchInfo'=>$matchInfo]);
    }


    /*
     * 匹配单详情
     * */
    public function buypayone(){
        $language =  language(Cache::get('lan_type'),'pottrade','buypayone');
        $mid = $this->request->get('m_id');
        $uid =  $this->wx_user_id;
        $matchInfo = db::name('ty_match')->where(array('id'=>$mid,'b_uid'=>$uid))->find();
        if($matchInfo==false){
            $this->error($language['wzddydjl'],0,'');exit;
        }
        if($matchInfo['state']!=0){
            $this->error($language['tpyysc'],0,'');exit;
        }
        //获得对方人信息
        $other= getUserById($matchInfo['s_uid']);
        $limit1 = sysconf('payhours') * 3600;
        $matchInfo['leavetime'] = $matchInfo['addtime'] + $limit1-time();
        $matchInfo['other_uname'] = $other['email'];
        $matchInfo['other_nickname'] = $other['nickname'];
        $matchInfo['other_address'] = userpayment($other['id']);
        $this->success('',$matchInfo);exit;
    }

    /*
     * 确认打款
     * */
    public function upPayimg(){
        $language =  language(Cache::get('lan_type'),'pottrade','upPayimg');
        $uid = $this->wx_user_id;
        $m_id = $this->request->post('m_id');
        $image = $this->request->post('image');
        !$m_id && $this->error($language['ddbnwk']);
        !$image && $this->error($language['scdkjtbnwk']);
        $now = time();
        // 匹配出图片的格式
        $uparry = array(
            'state'=>1,
            'payimg'=>$image,
            'addtime1'=>$now,
        );
        $res[] = db::name('ty_match')->where(array('b_uid'=>$uid,'id'=>$m_id,'state'=>0))->setField($uparry);
        if (check_arr($res)) {
            Db::commit();
            $this->success($language['qrfkcg']);
        } else {
            Db::rollback();
            $this->error($language['qrfksb']);
        }

    }

    /*
     * 卖单列表
     * @param state 1 待匹配 2进行中 3已完成
     * */
    public function sellList(){
        $state = $this->request->get('state');
        if($state){
            $selllist =  db::name('ty_selllist')->where(['uid'=>$this->wx_user_id,'state'=>$state])->page($this->page, $this->max_page_num)->order('id desc')->select();
        }else{
            $selllist =  db::name('ty_selllist')->where(['uid'=>$this->wx_user_id])->page($this->page, $this->max_page_num)->order('id desc')->select();
        }
        $this->success('',['selllist'=>$selllist]);
    }

    //出售详情
    public function selldetail(){
        $language =  language(Cache::get('lan_type'),'pottrade','selldetail');
        $sid = $this->request->get('s_id');
        $uid =  $this->wx_user_id;
        $selllist = db::name('ty_selllist')->where(array('id'=>$sid,'uid'=>$uid))->find();
        if($selllist){
            $matchInfo = db::name('ty_match')->where(array('s_id'=>$sid,'s_uid'=>$uid))->order('id desc')->select();
            if($matchInfo ==false){
                $this->error($language['wzddydppdd']);exit;
            }
            foreach( $matchInfo as $k=>$v){
                if($v['state'] ==1){
                    $matchInfo[$k]['leavetime'] = $v['addtime1'] + sysconf('collectionhours')*3600 -time();
                }else{
                    $matchInfo[$k]['leavetime'] = 0;
                }
                $matchInfo[$k]['other_uname'] = getrealname($v['b_uid']);
            }
            $sellInfo = db::name('ty_selllist')->where(array('id'=>$sid,'uid'=>$uid))->order('id desc')->find();
        }
        $this->success('',['sellInfo'=>$sellInfo,'matchInfo'=>$matchInfo]);
    }

    /*
     * 出售一条匹配详情
     * */
    public function sellmatchone(){
        $language =  language(Cache::get('lan_type'),'pottrade','sellmatchone');
        $mid = $this->request->get('m_id');
        $uid =  $this->wx_user_id;
        $limit2 = 3600 * sysconf('collectionhours');
        $matchInfo = db::name('ty_match')->where(array('id'=>$mid,'s_uid'=>$uid))->find();
        if($matchInfo ==false){
            $this->error($language['wzddydppdd'],0,'');exit;
        }
        //获得对方人信息
        $other= getUserById($matchInfo['b_uid']);
        $matchInfo['other_uname'] = $other['email'];
        $matchInfo['other_nickname'] = $other['nickname'];
        if($matchInfo['state'] ==0){
            $matchInfo['addtime'] = $matchInfo['addtime1'];
        }
        if($matchInfo['state'] ==1){
            $matchInfo['leavetime'] = $matchInfo['addtime1'] + $limit2 -time();
        }
        $this->success('',['matchInfo'=>$matchInfo]);exit;
    }

    /*
     * 确认收款
     * */
    public function sellok(){
        $language =  language(Cache::get('lan_type'),'pottrade','sellok');
        $uid = $this->wx_user_id;
        $m_id = $this->request->post('m_id');
        $check = db::name('ty_match')->where(array('s_uid'=>$uid,'id'=>$m_id,'state'=>1))->find();
        if($check == false){
            $this->error($language['wxdd']);exit;
        }
        $buyinfo = db::name('ty_buylist')->where(array('id'=>$check['b_id']))->find();
        $uparry = array(
            'state'=>2,
            'addtime2'=>time()
        );
        Db::startTrans();
        //更改状态
        $res[] = db::name('ty_match')->where(array('s_uid'=>$uid,'id'=>$m_id,'state'=>1))->setField($uparry);
        //更改购买列表的状态
        //判断购买者的匹配表是否全部完成才能进行下次匹配
        $checkMatch = db::name('ty_match')->where(array('b_uid'=>$buyinfo['uid'],'b_id'=>$buyinfo['id']))->where('state','lt',2)->count();
        //买单的剩余数量为0
        if($checkMatch<=0 && $buyinfo['leavenum'] == 0){
            if($buyinfo['state']==4){
                $uparry = array(
                    'state'=>5,
                    'endtime'=>time()
                );
                $res[] = db::name('ty_buylist')->where(array('id'=>$check['b_id']))->setField($uparry);
                //结束之后（相当于产生一个pot矿机）
                $order_id= Db::name('financial_order')->insertGetId([
                    'ordersn'=> substr(time(),5) . mt_rand(100, 999),
                    //'lc_id' => 'pot矿机',
                    'uid' => $buyinfo['uid'],
                    'uname' => $buyinfo['uname'],
                    'title' =>  'poe矿机',
                    'day' => sysconf('pot_day'),
                    'create_at' => time(),
                    'market_price' => $buyinfo['totalnum'],
                    'sl_rate'=>$buyinfo['totalnum'],
                    'sl_num'=>$buyinfo['totalnum'],
                    'type'=>1
                ]);
                if($order_id){
                    $res_id =  mlog($buyinfo['uid'], 'wallet_four', $buyinfo['totalnum'], "购买poe矿机,增加算力{$buyinfo['totalnum']}", '','','4',$this->wx_user_id);
                    bagslanguage($res_id['1'],$buyinfo['totalnum'],'','','',6);
                    //见点奖
                    $award = new Award;
                    $award->jdaward($buyinfo['uid'],$buyinfo['totalnum'],$order_id);
                }

            }
        }
        $this->setSellOver($check['s_id']);
        if (check_arr($res)) {
            Db::commit();
            $this->success($language['qrcg']);
        }else{
            Db::rollback();
            $this->error($language['qrsb']);
        }

    }
    /**
     * 判断出售表是否全部结束
     */
    public function setSellOver($s_id){
        $re = db::name('ty_match')->where(array('s_id'=>$s_id))->where('state','lt',2)->find();
        $leavenum =  db::name('ty_selllist')->where(array('id'=>$s_id))->value('leavenum');
        if(!$re && $leavenum==0){
            $updata = array(
                'state'=>3,
                'oktime'=>time()
            );
           $res[] = db::name('ty_selllist')->where(array('id'=>$s_id))->setField($updata);
        }
    }

    //投诉处理
    public function complaint()
    {
        $orderId = input('post.orderId');
        $imagePath = input('post.imagePath');
        $content = input('post.content');
        if (empty($imagePath))
            $this->error('请上传投诉截图');
        if (empty($content))
            $this->error('请填写投诉内容');
        //$relationOrderInfo = \getC2cOrderPayTime($orderId);
        $orderinfo = Db::name('ty_match')->where(['id' => $orderId])->find();
        if (!$orderinfo)
            $this->error('订单不存在');
        if ($orderinfo['state'] == 8)
            $this->error('请勿重复操作');
//        //查看是否倒计时结束
//        $endtime = strtotime($relationOrderInfo['create_at']) + self::TSDJS;
//        if ($endtime > time())
//            $this->error('投诉时间还未到,请耐心等待');

//        //在有效时间内投诉有效
//        $jztime = strtotime($orderinfo['create_at']) + self::TSJZ;
//        if ($jztime < time()) {
//            $this->error('订单交易15分钟内不能进行投诉');
//        }
        $ts_Arr = [
            'uid' => $orderinfo['s_uid'],
            'order' =>$orderId ,//投诉订单ID
            'username'=>$this->wx_user['email'],
            'buid' =>$orderinfo['b_uid'],
            'border' => '', //被投诉订单ID
            'caeate_at' => time(),
            'status' => 0, //0未处理  1已处理
            'image_path' => $imagePath,
            'content' => $content,
        ];
        Db::startTrans();
        $res[] = Db::name('ty_match')->where(['id' => $orderId])->update(['state' => 8]);
        $res[] = Db::name('store_order_c2c_ts')->insert($ts_Arr);
        if (check_arr($res)) {
            Db::commit();
            $this->success('投诉成功');
        } else {
            Db::rollback();
            $this->error('投诉失败');
        }
    }


    /*
     * 取消订单
     * */
    public function cancelC2cOrder()
    {
        $language =  language(Cache::get('lan_type'),'pottrade','cancelC2cOrder');
        $orderId = input('param.orderId');
        !$orderId && $this->error($language['ddidbnwk']);
        Db::startTrans();
        $orderInfo = Db::name('ty_buylist')->where(['id' => $orderId,'state'=>1])->find();
        if($orderInfo){
            //        var_dump($orderInfo);
            //$orderInfo['state'] != 1 && $this->error('当前订单不能取消,尝试刷新网页');
             $res[] = $res_id= mlog($orderInfo['uid'], 'account_score', $orderInfo['nac_num'], '取消买单'.$orderInfo['totalnum'].'返回nac'.$orderInfo['nac_num'], 'cancel_C2c_order', $orderId);
             bagslanguage($res_id['1'],$orderInfo['totalnum'],'','','',7,8);
            $res[] = Db::name('ty_buylist')->where(['id' => $orderId])->update(['state' => 6]);
            if($this->wx_user['is_yx'] == 1){
                //推荐人
                $tjid = db::name('store_member')->where(['id'=>$this->wx_user_id])->value('first_leader');
                db::name('store_member')->where(['id'=>$tjid])->setDec('xy_tj_num',1);
                db::name('store_member')->where(['id'=>$this->wx_user_id])->setField('is_yx',0);
            }
            if (check_arr($res)) {
                Db::commit();
                $this->success($language['qxcg']);
            } else {
                Db::rollback();
                $this->error($language['qxsb']);
            }
        }
    }

    /*
     * 矿池算力
     * */
    public function potOrepoolsl(){
       $language =  language(Cache::get('lan_type'),'userUsdt','userUsdt');
        //推荐算力值
        $jd_store_award = db::name('store_award')->where(['type'=>1,'uid'=>$this->wx_user_id,'state'=>0])->sum('money');
        //算力列表
        $bags_log = db::name('bags_log')->where(['uid'=>$this->wx_user_id])
            ->alias('a')->leftJoin('bags_language b', 'a.id=b.b_id')
            ->where('type','in', 'wallet_four,wallet_five')
            ->page($this->page, $this->max_page_num)
            ->order('a.id desc')->select();
        foreach($bags_log as $k=>$val){
            $bags_log[$k]['language'] =  $language[$val['fy_state1']].$val['money1'].$language[$val['fy_state2']].$val['money2'].$language[$val['fy_state3']].$val['money3'].$language[$val['fy_state4']].$val['money4'];
        }
        $this->success('',['jd_store_award'=>$jd_store_award,'bags_log'=>$bags_log]);
    }

    /*
     *见点挖矿算力(挖矿)
     * */
    public function jdalculationWk(){
        $language =  language(Cache::get('lan_type'),'pottrade','jdalculationWk');
        $order = Db::name('store_award')->where(['state' =>0,'type'=>1,'uid'=>$this->wx_user_id])->find();
        if(!$order){
            $this->error($language['cczwx']);
        }
        $sl_num = Db::name('store_award')->where(['type' =>1,'uid'=>$this->wx_user_id,'state'=>0])->sum('sl_num');
        $res = Db::name('store_award')->where(['type' =>1,'uid'=>$this->wx_user_id,'state'=>0])->update(['state'=>1]);

        if($res){
            //见点人的见点算力
            $res_id = mlog($this->wx_user_id, 'wallet_five', $sl_num, '见点奖算力增加'.$sl_num, 'wallet_five', '', '3');
            bagslanguage($res_id['1'],$sl_num,'','','',9);
            $this->success($language['wkcg']);
        }else{
            $this->error($language['wksb']);
        }
    }
    /*
     *pos收益
     * type：0  推荐算力列表
     * type：1  见点算力列表
     * */
    public function potprofit(){
        //昨日收益（nac）
        $yesterday = strtotime(date('Y-m-d'));
        $beforeyesterday = strtotime(date('Y-m-d'))-86400;
        $yt_num = db::name('bags_log')->where(['uid'=>$this->wx_user_id,'type'=>'account_score','extends'=>'wallet_six'])->whereBetween('create_time',"$beforeyesterday,$yesterday")->sum('money');
        //挖矿投资（算力）
        $slnum = db::name('bags_log')->where(['uid'=>$this->wx_user_id,'type'=>'account_score','extends'=>'wallet_six'])->sum('sl_num');
        //累计收益（nac）
        $money = db::name('bags_log')->where(['uid'=>$this->wx_user_id,'type'=>'account_score','extends'=>'wallet_six'])->sum('money');
        $bags_log = db::name('bags_log')->where(['uid'=>$this->wx_user_id,'type'=>'account_score','extends'=>'wallet_six'])->page($this->page, $this->max_page_num) ->order('id desc')->select();
        $this->success('',['yt_num'=>$yt_num,'slnum'=>$slnum,'money'=>$money,'bags_log'=>$bags_log]);
    }

    /*
     *poe挂买单成功之后的记录
     * */
    public function pledge(){
        $orderid = $this->request->param('orderid');
        $ty_buylist = db::name('ty_buylist')->where(['id'=>$orderid])->find();
        $this->success('',['ty_buylist'=>$ty_buylist]);
    }

    /*
     * 质押币公式
     * */
    public function nacgongshi(){
        $num = $this->request->param('num');
        $nac_num = $num * sysconf('zyb_rate')*0.01/sysconf('lxc_dollar');
        $this->success('',['nac_num'=>$nac_num]);
    }

    /*
     * poe矿机记录
     * */
   public function poemachinerecord(){
//       $status = $this->request->get('status');
//       $map['uid'] = $this->wx_user_id;
//       if(!empty($status)){
//           if($status == 1){
//               $status =0;
//           }elseif($status == 2){
//               $status =1;
//           }
//           $map['status'] = $status;
//       }
       $map['type'] =1;
       $financial_order = db::name('financial_order')->where($map)->page($this->page, $this->max_page_num) ->order('id desc')->select();
       //累计收益
       $this->success('',['financial_order'=>$financial_order]);
   }

    /*
    * poe订单详情
    * */
    public function poeOrderinfo(){
        $oid = $this->request->get('oid');
        $financial_order = db::name('financial_order')->where(['id'=>$oid,'uid'=>$this->wx_user_id])->find();
        $this->success('',$financial_order);
    }

    /*
     * pot 算力展示
     * */
    public function sldisplay(){
        $pot_daysy = db::name('pot_daysy')->where(['uid'=>$this->wx_user_id])->page($this->page, $this->max_page_num)->select();
        $this->success('',$pot_daysy);
    }

    /*
     * poe内容
     * */
    public function poecontent(){
        $this->success('',['poecontent'=>sysconf('poe_content')]);
    }
}
