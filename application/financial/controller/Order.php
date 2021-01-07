<?php

// +----------------------------------------------------------------------
// | Think.Admin
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/Think.Admin
// +----------------------------------------------------------------------

namespace app\financial\controller;
use controller\BasicAdmin;
use service\DataService;
use think\Db;

/**
 * 商店订单管理
 * Class Order
 * @package app\store\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/03/27 14:43
 */
class Order extends BasicAdmin
{

    /**
     * 定义当前操作表名
     * @var string
     */
    public $table = 'financialOrder';

    /**
     * 订单列表
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
       $this->title = '理财订单';
        $get = $this->request->get();
        $db = Db::name("financial_order")->where('type','0')->order('id desc');
        $where = array();
        if (isset($get['phone']) && $get['phone'] != '') {
            $where['phone'] = $get['phone'];
            $db->where($where);
        }
        if (isset($get['order_no']) && $get['order_no'] != '') {
            $where['ordersn'] = $get['order_no'] ;
            $db->where($where);
        }
        if (isset($get['member_id']) && $get['member_id'] != '') {
            $where['uid'] = $get['member_id'] ;
            $db->where($where);
        }
        if (isset($get['uname']) && $get['uname'] != '') {
            $where['uname'] = $get['uname'] ;
            $db->where($where);
        }
        if (isset($get['type']) && $get['type'] != '') {
            $where['type'] = $get['type'];
            $db->where($where);
        }

        if (isset($get['extends']) && $get['extends'] != '') {
//            if($get['extends'] == 'dongtaiaward'){
//            	$where['extends'] = $get['extends'];
//            }
//             if($get['extends'] == 'luckyaward'){
//            	$where['extends'] = $get['extends'];
//            }
            if($get['extends'] != 'awardUsdt'){
                $where['type'] = $get['extends'];
            }else{
                $where['extends'] = $get['extends'];
            }
            $db->where($where);
        }
        if (isset($get['create_at']) && $get['create_at'] != '') {
            $date = $get['create_at'];
            list($start, $end) = explode(' - ', $date);
            $db->where('create_at', 'between', [strtotime($start), strtotime($end)+86400]);
        }
        if (isset($get['field']) && $get['field'] != '') {
            if ($get['field'] == 'phone') {
                $get['name'] && $where['uid'] = Db::name('store_member')->where(array('phone' => $get['name']))->value('id');
            } elseif($get['field'] == 'username') {
                $get['name'] && $where['uid'] = Db::name('store_member')->where(array('nickname' => $get['name']))->value('id');
            }else{
                $where[$get['field']] = $get['name'];
            }

            $db->where($where);
        }
        return parent::_list($db->order('id desc'));
    }
    
    /**
     * 添加商品
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function add()
    {
        if ($this->request->isGet()) {
            $this->title = '添加理财订单';
            $this->_form_assign();
            return $this->_form($this->table, 'form');
        }
        $data = $this->_form_build_data();
        $lc_id = $data['main']['lc_id'];
        $username = $data['main']['username'];
        $user = db::name('store_member')->where(['phone'=>$username])->find();
        $licai = Db::name("financial_licai")->where(['id' => $lc_id, 'status' => 1, 'is_deleted' => 0])->find();
        if($user && $licai){
            $usdt_num = exchangeUsdt($licai['market_price'] * sysconf('pay_type1') * 0.01);
            $lxc_num = exchangeLxc($licai['market_price'] * sysconf('pay_type2') * 0.01);
            if ($user['account_money'] < $usdt_num && $usdt_num>0) {
                $this->error("usdt数量不足，需要支付".$usdt_num.'个usdt');
            }
            if ($user['account_score'] < $lxc_num && $lxc_num >0) {
                $this->error("数量不足，需要支付".$lxc_num.'个nubc');
            }
            //添加记录，进行扣除
            Db::startTrans();
            $order_id= Db::name('financial_order')->insertGetId([
                    'ordersn'=> substr(time(),5) . mt_rand(100, 999),
                    'lc_id' => $lc_id,
                    'uid' => $user['id'],
                    'phone' => $user['phone'],
                    'title' => $licai['title'],
                    'day' => $licai['day'],
                    'estimated_income' =>$licai['market_price']*$licai['sy_rate'] ,//sy_rate指的倍数
                    'create_at' => time(),
                    'market_price' => $licai['market_price'],
                    'sy_rate' =>  $licai['sy_rate'],
                    'pay_usdt_num'=>$usdt_num,
                    'pay_lxc_num'=>$lxc_num,
                    'state'=>$data['main']['state'],
                ]);
                $res[] = $order_id;
                if($usdt_num > 0){
                    $res[] = mlog($user['id'],'account_money',-$usdt_num,"购买理财产品".$licai['market_price'].",扣除".$usdt_num.'个usdt','touziUsdt','',6,$res);
                }
                if($lxc_num > 0){
                    $res[] = mlog($user['id'],'account_score',-$lxc_num,"购买理财产品".$licai['market_price'].",扣除".$lxc_num.'个nubc','touzilxc','',6,$res);
                }
                $res[] = Db::name('store_member')->where(['id'=>$user['id']])->setInc('total_performance',$licai['market_price']);
                if (check_arr($res)) {
                    Db::commit();
                    //添加团队业绩
                    $this->addTeamperformance($user['id'],$licai['market_price']);
                    //冻结金额
                    $addmoney = $licai['market_price']*$licai['sy_rate'];
                    //添加冻结钱包和冻结lxc
                    $res[] = mlog($user['id'], 'dj_usdt', $addmoney, "购买理财产品".$licai['market_price'].",冻结钱包增加".$addmoney.'个usdt', 'dj_usdt','',6,$res);
                    //添加直推奖奖金
                    $this->tjAward($user['id'],$licai['market_price']);
                    //添加实体店奖金
                    $this->storesBonus($user['id'],$licai['market_price']);
                    list($base, $spm, $url) = [url('@admin'), $this->request->get('spm'), url('financial/order/index')];
                    $this->success('购买理财产品成功',"{$base}#{$url}?spm={$spm}");
                } else {
                    Db::rollback();
                    $this->error('购买理财产品失败');
                }
        }else{
            $this->error('会员账号错误！');
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
            $this->upLevel($all_leader[$i]);
        }
        //添加团队业绩记录
        addNewperformance($uid,$money);
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
    /*
    从表单中获取数据
    */
    protected function _form_build_data()
    {

     
        // empty($post['username']) && $this->error('会员账号不能为空！');
        // 商品主数据组装
        $main['username'] = $this->request->post('username', '');
        $main['lc_id'] = $this->request->post('lc_id', '');
        $main['state'] = $this->request->post('state', '');
        return ['main' => $main];
    }
    
     /**
     * 表单数据处理
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _form_assign()
    {
        $financial_licai = Db::name('financial_licai')->where(['status'=>1,'is_deleted'=>0])->select();
        array_unshift($specs, ['spec_title' => ' - 不使用规格模板 -', 'spec_param' => '[]', 'id' => '0']);
        $this->assign([
            'financial_licai' => $financial_licai,
        ]);
    }

}
