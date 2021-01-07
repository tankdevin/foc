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
class Trade extends Base
{

    /*
     * 支付密码
     * */
    public function paypassword(){
        $paypassword = $this->request->param('paypassword');

        if(!$paypassword){
            $this->error("支付密码校验成功".$this->wx_user_id);
        }
        if(!is_numeric($paypassword) || strlen($paypassword) !='6'){
            $this->error("交易密码必须是6位数字");
        }
        $store_member = Db::name("store_member")->where(['paypassword'=>md5($paypassword),'id'=>$this->wx_user_id])->find();
        if($store_member){
            $this->success("");
        }else{
            $this->error("支付密码校验失败");
        }
    }

    /*
     * 购买理财产品
     * */
    public function buyFinancialorder()
    {
        $data = input('post.');
        $validate = Validate::make([
            'id' => 'require',
            'paypassword'=> 'require',
        ], [
            'id.require' => '参数有误',
            'paypassword.require' =>'支付密码不能为空',
        ]);
        $validate->check($data) || $this->error($validate->getError());
        if ($data['id']) {
            $licai = Db::name("financial_licai")->where(['id' => $data['id'], 'status' => 1, 'is_deleted' => 0])->find();
            //var_dump($licai);
            if ($licai) {
                //以美元的方式购买换算成数量
                $usdt_num = exchangeUsdt($licai['market_price'] * sysconf('pay_type1') * 0.01);
                $lxc_num = exchangeLxc($licai['market_price'] * sysconf('pay_type2') * 0.01);
//                var_dump($this->wx_user['account_money']);
//                var_dump($usdt_num);
                if(!$this->wx_user['paypassword']){
                    $this->error("请完善支付密码");
                }
                if(!is_numeric($data['paypassword']) || strlen($data['paypassword']) !='6'){
                    $this->error("交易密码必须是6位数字");
                }
                if ($this->wx_user['account_money'] < $usdt_num && $usdt_num>0) {
                    $this->error("usdt数量不足，需要支付".$usdt_num.'个usdt');
                }
                if ($this->wx_user['account_score'] < $lxc_num && $lxc_num >0) {
                    $this->error("数量不足，需要支付".$lxc_num.'个nubc');
                }
                if($this->wx_user['total_performance'] >= sysconf('xd_lc_fd')){
                    $this->error("理财最大封顶值单账户限定数量".sysconf('xd_lc_fd').'$');
                }
                
                if(md5($data['paypassword']) != $this->wx_user['paypassword']){
                    $this->error("支付密码错误，请重新输入");
                }

                //添加记录，进行扣除
                Db::startTrans();
                $order_id= Db::name('financial_order')->insertGetId([
                    'ordersn'=> substr(time(),5) . mt_rand(100, 999),
                    'lc_id' => $data['id'],
                    'uid' => $this->wx_user_id,
                    'phone' => $this->wx_user['phone'],
                    'title' => $licai['title'],
                    'day' => $licai['day'],
                    'estimated_income' =>$licai['market_price']*$licai['sy_rate'] ,//sy_rate指的倍数
                    'create_at' => time(),
                    'market_price' => $licai['market_price'],
                    'sy_rate' =>  $licai['sy_rate'],
                    'pay_usdt_num'=>$usdt_num,
                    'pay_lxc_num'=>$lxc_num
                ]);
                $res[] = $order_id;
                if($usdt_num > 0){
                    $res[] = mlog($this->wx_user_id,'account_money',-$usdt_num,"购买理财产品".$licai['market_price'].",扣除".$usdt_num.'个usdt','touziUsdt','',6,$res);
                }
                if($lxc_num > 0){
                    $res[] = mlog($this->wx_user_id,'account_score',-$lxc_num,"购买理财产品".$licai['market_price'].",扣除".$lxc_num.'个nubc','touzilxc','',6,$res);
                }

                $res[] = Db::name('store_member')->where(['id'=>$this->wx_user_id])->setInc('total_performance',$licai['market_price']);
                //增加个人业绩和团队业绩
                if (check_arr($res)) {
                    Db::commit();
                    //添加团队业绩
                    $this->addTeamperformance($this->wx_user_id,$licai['market_price']);
                    //冻结金额
                    $addmoney = $licai['market_price']*$licai['sy_rate'];
                    //添加冻结钱包和冻结lxc
                    $res[] = mlog($this->wx_user_id, 'dj_usdt', $addmoney, "购买理财产品".$licai['market_price'].",冻结钱包增加".$addmoney.'个usdt', 'dj_usdt','',6,$res);
                    //添加直推奖奖金
                    $this->tjAward($this->wx_user_id,$licai['market_price']);
                    //添加实体店奖金
                    $this->storesBonus($this->wx_user_id,$licai['market_price']);
                    $this->success('购买理财产品成功');
                } else {
                    Db::rollback();
                    $this->error('购买理财产品失败');
                }
            }
        }
    }


    /*
     * 团队业绩（根据团队业绩来判断级别）是以usdt的个数来判断
     * @param $team_performance  团队业绩
     *
     * */
    public function upLevel($uid){
        $user = db::table('store_member')->where(array('id'=>$uid))->find();
        $sys_level = db::name('sys_level')->order('id desc')->select();
        $ulevel = 0;
        foreach($sys_level as $key=>$value){
            if($user['team_performance'] > $value['min_teaming'] && $user['team_performance'] <= $value['teaming']){
                $ulevel = $value['id'];
                break;
            }
        }
        if($user['level'] < $ulevel){
            //进行升级(添加升级记录)
            Db::startTrans();
            $upgradeRecord = [
                'uid' => $user['id'],
                'phone'=>$user['phone'],
                'addtime' => get_time(),
                'oldulevel' => $user['level'],
                'upgradeLevel' => $ulevel,
                'type'=>1
            ];
            $res[] = Db::name('store_member_upgrade_record')->insertGetId($upgradeRecord);
            $res[] = Db::name('store_member')->where(['id' =>$uid])->setField('level',$ulevel);
            if (check_arr($res)) {
                Db::commit();
                //  echo "ok";
                //$this->success('升级成功');
            } else {
                Db::rollback();
                //$this->error('升级失败');
                // echo "no1";
            }
        }

    }


    

    
     /*
     * 扫描二维码，识别信息
     * */
    public function otherUsdtnumid(){
        $data = $this->request->param();
        if($data['num_id'] && $data['type'] == 2){
            $this->success('', $data);
        }else{
            $this->error('参数错误');
        }

    }
    /*直推奖励*/
    public function tjAward($uid,$money){
        $user = db::table('store_member')->where(array('id'=>$uid))->find();
        $tj_user_id = $user['first_leader'];
        if($tj_user_id){
            //推荐人信息
            $tjuser = db::table('store_member')->where(array('id'=>$tj_user_id))->find();
            $addmoney = $money*sysconf('zt_rate')*0.01;
            if($tjuser['dj_usdt'] <= $addmoney){
                $addmoney = $tjuser['dj_usdt'];
            }
            if($tjuser['dj_usdt'] > 0){
                mlog($tjuser['id'], 'account_money', $addmoney, '获得推荐奖来源人为'.$user['phone'].',总金额' . $money . '$ 比率为' . sysconf('zt_rate') . '% 实际金额为'.$addmoney.'usdt', 'award', '', '11');
                dongtaiAwardjl($tjuser['id'], $type=2, $addmoney, '获得推荐奖来源人为'.$user['phone'].',总金额' . $money . '$ 比率为' . sysconf('zt_rate') . '% 实际金额为' . $addmoney."usdt",'');
                mlog($tjuser['id'], 'dj_usdt', -$addmoney, '冻结钱包减少'.$addmoney.'usdt', 'award', '', '11');
                db::table('store_member')->where(array('id'=>$tj_user_id))->setInc('released_money',$addmoney);//增加已释放钱包
                averageDynamic($tjuser['id'],$addmoney);//把所有的动态收益平分到静态释放里面
            }
        }
    }

    /*
     * 产品列表
     * */
    public function productList(){
        $result = Db::name('financial_licai')
            ->where(['status' => 1,'is_deleted'=>0])
            ->page($this->page, $this->max_page_num)
            ->select();
        foreach($result as $k=>$v){
            $result[$k]['pay_type1'] = sysconf('pay_type1');
            $result[$k]['pay_type2'] = sysconf('pay_type2');
        }
        $this->success('',$result);
    }

    /*
     * 投资记录
     * */
    public function InvestmentRecord(){
        $result = Db::name('financial_order')
            ->where(['uid' => $this->wx_user_id])
            ->page($this->page, $this->max_page_num)->order('id desc')
            ->select();
        $this->success($result);
    }

    /*
     *实体店列表
     *  */
    public function storesList(){
        $sys_earnings = Db::name("sys_earnings")->select();
        $this->success('',['sys_earnings'=>$sys_earnings]);
    }
    /*
     * 实体店奖金
     * */

    public function buyStores(){
        $data = input('post.');
        $validate = Validate::make([
            'id' => 'require',
            'paypassword'=> 'require',
        ], [
            'id.require' => '参数有误',
            'paypassword.require' =>'支付密码不能为空',
        ]);
        $validate->check($data) || $this->error($validate->getError());
        if ($data['id']) {
            $licai = Db::name("sys_earnings")->where(['id' => $data['id']])->find();
            if ($licai) {
                if(!$this->wx_user['paypassword']){
                    $this->error("请完善支付密码");
                }
                if(!is_numeric($data['paypassword']) || strlen($data['paypassword']) !='6'){
                    $this->error("交易密码必须是6位数字");
                }
                if($this->wx_user['region_level'] >= $data['id']){
                    $this->error("只能升级比自己大的服务商");
                }
                if ($this->wx_user['account_money'] < $licai['tjnum']) {
                    $this->error("usdt数量不足，需要支付".$licai['tjnum'].'个usdt');
                }
                if(md5($data['paypassword']) != $this->wx_user['paypassword']){
                    $this->error("支付密码错误，请重新输入");
                }

                if($this->wx_user['region_level'] < $data['id']){
                    //进行升级(添加升级记录)
                    Db::startTrans();
                    $order_id= Db::name('financial_order')->insertGetId([
                        'ordersn'=> substr(time(),5) . mt_rand(100, 999),
                        'lc_id' => $data['id'],
                        'uid' => $this->wx_user_id,
                        'phone' => $this->wx_user['phone'],
                        'title' => $licai['title'],
                        'estimated_income' =>$licai['tjnum']*$licai['sy_rate'] ,//sy_rate指的倍数
                        'create_at' => time(),
                        'market_price' => $licai['tjnum'],
                        'sy_rate' =>  $licai['sy_rate'],
                        'rate'=>$licai['luck_rate'],
                        'pay_usdt_num'=>$licai['tjnum'],
                        'type'=>1//省市县
                    ]);
                    $res[] = $order_id;
                    $upgradeRecord = [
                        'uid' => $this->wx_user['id'],
                        'phone'=>$this->wx_user['phone'],
                        'addtime' => get_time(),
                        'oldulevel' => $this->wx_user['region_level'],
                        'upgradeLevel' =>  $data['id'],
                        'money'=>$licai['tjnum'],
                        'type'=>2//区域代理
                    ];
                    $res[] = mlog($this->wx_user_id,'account_money',-$licai['tjnum'],"升级服务商".",扣除".$licai['tjnum'].'个usdt','touziUsdt','',6,'');
                    $res[] = Db::name('store_member_upgrade_record')->insertGetId($upgradeRecord);
                    $res[] = Db::name('store_member')->where(['id' =>$this->wx_user_id])->setField('region_level',$data['id']);
                    //添加个人记录
                    $res[] = Db::name('store_member')->where(['id'=>$this->wx_user_id])->setInc('total_performance',$licai['tjnum']);

                    //增加个人业绩和团队业绩
                    if (check_arr($res)) {
                        Db::commit();
                        //添加团队业绩
                        $this->addTeamperformance($this->wx_user_id,$licai['tjnum']);
                        $addmoney = $licai['tjnum']*$licai['sy_rate'];
                        //添加冻结钱包和冻结lxc
                        $res[] = mlog($this->wx_user_id, 'dj_usdt', $addmoney, "购买理财产品".$licai['market_price'].",冻结钱包增加".$addmoney.'个usdt', 'dj_usdt','',6,$res);
                        //添加直推奖奖金
                        $this->tjAward($this->wx_user_id,$licai['tjnum']);
                        //添加实体店奖金
                        $this->storesBonus($this->wx_user_id,$licai['tjnum']);
                        $this->success('升级服务商成功');
                    } else {
                        Db::rollback();
                        $this->error('升级服务商失败');
                    }
                }
            }
        }
    }

    /*
 * 实体店奖金
 * */

    public function storesBonus($uid,$money){
        $user = db::table('store_member')->where(array('id'=>$uid))->find();
        $all_leader = array_reverse(explode(',',$user['all_leader']));
        $leader_num = count($all_leader);
        $dt_award_sum = 0;
        for($i=0;$i<$leader_num;$i++) {
            $tjuser = db::table('store_member')->where(['id' => $all_leader[$i],'is_disable'=>1])->find();;
            if ($tjuser) {
                //只用平级或大于的话，才拿相对应的奖励
                if($tjuser['region_level'] >= $user['region_level'] && $tjuser['region_level'] !=0){
                    //会员等级相对应的概率
                    $luck_rate = db::name('sys_earnings')->where(['id'=>$tjuser['region_level']])->value('luck_rate');
                    $cha_rate = $luck_rate - $dt_award_sum;
                    if($cha_rate > 0 && $tjuser['dj_usdt'] > 0){
                        //进行发放奖金
                        $td_award = $money * $cha_rate * 0.01;
                        if($tjuser['dj_usdt'] < $td_award){
                            $addmoney = $tjuser['dj_usdt'];
                        }else{
                            $addmoney = $td_award;
                        }
                        mlog($tjuser['id'], 'account_money', $addmoney, '获得服务奖，总金额' . $money . '$ 比率为' . $cha_rate . '% 实际金额为' . $addmoney."usdt,来源人".$user['phone'], 'award', '', '44');
                        mlog($tjuser['id'], 'dj_usdt', -$addmoney, '冻结钱包减少'.$addmoney.'usdt', 'award', '', '44');
                        db::table('store_member')->where(array('id'=>$tjuser['id']))->setInc('released_money',$addmoney);//增加已释放钱包
                        averageDynamic($tjuser['id'],$addmoney);//把所有的动态收益平分到静态释放里面
                        $dt_award_sum += $cha_rate;
                    }
                }
            }
        }
    }
}
