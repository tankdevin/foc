<?php

namespace app\apiv1\controller;

use service\DataService;
use service\WechatServicew;
use controller\BasicIndex;
use think\facade\Cache;
use think\Db;

class Queue extends BasicIndex
{
    protected $MiniApp;

    public function __construct()
    {
        parent::__construct();
        $this->MiniApp = new \Smallsha\Classes\MiniApp(config('miniapp.appid'), config('miniapp.app_secret'));
    }

    public function login()
    {
        $data = input('post.');
        $rule = [
            'phone' => 'require',
            'password' => 'require',
            'yzm' => 'require',
        ];
        $msg = [
            'phone.require' => 'phone必传',
            'password.require' => 'password必传',
            'yzm.require' => 'yzm必传',
        ];
        $validate = new \think\Validate($rule, $msg);
        if (!$validate->check($data)) {
            return $this->error($validate->getError());
        }
        $flag_phone = Db::table('store_member')->where(['phone' => $data['phone']])->find();
        !$flag_phone && $this->error('账号不存在');
        $user_flag = Db::table('store_member')->where(['phone' => $data['phone'], 'password' => md5($data['password'])])->find();
        !$user_flag && $this->error('密码错误');
        //验证短信
        $data['yzm'] != \Cache::pull(request()->ip() . 'lyzm') && $this->error('验证码错误');
        $token = $this->MiniApp->getToken();
        if (DataService::other_save('StoreMember', ['wx_token' => $token], ['phone' => $data['phone']])) {
            return $this->success('登录成功', ['token' => $token]);
        } else {
            return $this->error('登录失败');
        }
    }

    /*
自动匹配
匹配条件
1.以买家价格为准 必须大于等于买家价格
2.按照时间先后顺序进行匹配
3.价格按照由小到大的顺序进行匹配
*/
    public function matching()
    {
        $isAutoMatch = sysconf('is_autoMatch');
        if ($isAutoMatch == -1) return false;

        $start_today_one = strtotime(date('Y-m-d 9:00:00', time()));
        $start_today_two = strtotime(date('Y-m-d 22:00:00', time()));


        if (time() > $start_today_one && time() < $start_today_two) {
            $data = Db::name('store_order_c2c')
                ->where(['type' => 1, 'status' => 1])
                ->order('interval_id ACS,create_at ASC')
                ->select();
            Db::startTrans();
            $res[] = 1;
            foreach ($data as $k => $v) {//查询买入订单，根绝买入价格查询正在卖出的订单，如果数量不相等自动拆分，拆分后等待下一轮匹配
                // echo "<br>";
                //1.数量相等
                if ($buyinfo = Db::name('store_order_c2c')->where(['type' => 2, 'status' => 1])->where("price<=" . $v['price'])->find()) {
                    $flag_phone1 = Db::table('store_member')->where(['id' => $v['mid']])->find();
                    $flag_phone2 = Db::table('store_member')->where(['id' => $buyinfo['mid']])->find();
                    //1.数量相等
                    if ($buyinfo['num'] == $v['num']) {
                        $res[] = Db::name('store_order_c2c')->where(['id' => $v['id']])->update(['other_order_id' => $buyinfo['id'], 'ism'=> 1 ,'status' => 2,'price' => $buyinfo['price'], 'real_price' => $buyinfo['real_price']]);
                        $res[] = Db::name('store_order_c2c')->where(['id' => $buyinfo['id']])->update(['other_order_id' => $v['id'],  'status' => 2]);
                        $res[] = sendMobileMessage($flag_phone1['phone'], ['code' => $v['order_no']], '499820');
                        $res[] = sendMobileMessage($flag_phone2['phone'], ['code' => $buyinfo['order_no']], '499820');

                        $relation_order_c2c = [
                            'fromid' => $v['mid'], //买的人id
                            'toid' => $buyinfo['mid'], //买出人
                            'status' => 2,
                            'create_at' => get_time(),
                            'orderid' => $v['id']
                        ];
                        $res['insert_c2c_relation'] = DataService::save('StoreC2cRelation', $relation_order_c2c);
                        // $res['order_update_status'] = Db::name('store_order_c2c')->where(['id' => $res['orderid']])->update(['status' => 2]);

                    }

                    //2.数量不等
                    if ($buyinfo['num'] > $v['num']) {
                        $res[] = Db::name('store_order_c2c')->where(['id' => $v['id']])->update(['other_order_id' => $buyinfo['id'], 'status' => 2, 'ism'=> 1 , 'price' => $buyinfo['price'], 'real_price' => $buyinfo['price'] * $v['num']]);
                        $res[] = Db::name('store_order_c2c')->where(['id' => $buyinfo['id']])->update(['other_order_id' => $v['id'], 'status' => 2, 'num' => $v['num'], 'real_price' => $buyinfo['price'] * $v['num']]);
                        $arr = [
                            'type' => $buyinfo['type'],
                            'mid' => $buyinfo['mid'],
                            'interval_id' => $buyinfo['interval_id'],
                            'order_no' => $buyinfo['mid'] . rand(100000, 999999) . time(),
                            'num' => $buyinfo['num'] - $v['num'],
                            'price' => $buyinfo['price'],
                            'real_price' => $buyinfo['price'] * ($buyinfo['num'] - $v['num'])

                        ];
                        $res['orderId'] = Db::name('store_order_c2c')->insertGetId($arr);
                        $res[] = sendMobileMessage($flag_phone1['phone'], ['code' => $v['order_no']], '499820');
                        $res[] = sendMobileMessage($flag_phone2['phone'], ['code' => $buyinfo['order_no']], '499820');
                        $relation_order_c2c = [
                            'fromid' => $v['mid'], //买的人id
                            'toid' => $buyinfo['mid'], //买出人
                            'status' => 2,
                            'create_at' => get_time(),
                            'orderid' => $v['id']
                        ];
                        $res['insert_c2c_relation'] = DataService::save('StoreC2cRelation', $relation_order_c2c);


                    }

                    if ($buyinfo['num'] < $v['num']) {
                        $res[] = Db::name('store_order_c2c')->where(['id' => $v['id']])->update(['other_order_id' => $buyinfo['id'], 'status' => 2,'ism'=> 1,'num' => $buyinfo['num'], 'real_price' => $buyinfo['price'] * $buyinfo['num']]);
                        $res[] = Db::name('store_order_c2c')->where(['id' => $buyinfo['id']])->update(['other_order_id' => $v['id'], 'status' => 2]);

                        $arr = [
                            'type' => $v['type'],
                            'mid' => $v['mid'],
                            'interval_id' => $v['interval_id'],
                            'order_no' => $v['mid'] . rand(100000, 999999) . time(),
                            'num' => $v['num'] - $buyinfo['num'],
                            'price' => $v['price'],
                            'real_price' => $v['price'] * ($v['num'] - $buyinfo['num'])

                        ];
                        $res[] = Db::name('store_order_c2c')->insertGetId($arr);
                        $res[] = sendMobileMessage($flag_phone1['phone'], ['code' => $v['order_no']], '499820');
                        $res[] = sendMobileMessage($flag_phone2['phone'], ['code' => $buyinfo['order_no']], '499820');
                        $relation_order_c2c = [
                            'fromid' => $v['mid'], //买的人id
                            'toid' => $buyinfo['mid'], //买出人
                            'status' => 2,
                            'create_at' => get_time(),
                            'orderid' => $v['id']
                        ];
                        $res['insert_c2c_relation'] = DataService::save('StoreC2cRelation', $relation_order_c2c);

                    }
                }
            }
            if (check_arr($res)) {
                Db::commit();
                $this->success();
            } else {
                Db::rollback();
                $this->error();
            }
        } else {
            echo '不在匹配时间内';
        }

    }


    public function test()
    {
        sm($this->commonService);

    }

    public function givebtt()
    {
         $data = Db::name('store_order_c2c')
               // ->where(['type' => 1, 'status' => 3 , ' isf'=>0])
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
