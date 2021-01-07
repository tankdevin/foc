<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

namespace app\miniapp\controller;

use think\Controller;
use service\DataService;
use think\Db;
use think\validate;
use service\WechatServicew;

/**
 * 应用入口控制器
 * @author Anyon <zoujingli@qq.com>
 */
class Index extends Base
{

    //首页筛选
    public function index()
    {
        $condition = [];
        $get = input('get.');
        $db = Db::name('store_member a')
            ->join("store_member_info b", "a.id = b.uid");
        //地区
        if (isset($get['address']) && $get['address'] != '') {
            $condition['b.address'] = ['like', "%{$get['address']}%"];
        }
        //行业id
        if (isset($get['industry']) && $get['industry'] != '') {
            $condition['b.industry'] = ['=', $get['industry']];
        }
        //内推机会
        if (isset($get['Intrapolation']) && $get['Intrapolation'] != '') {
            $condition['b.Intrapolation'] = ['=', $get['Intrapolation']];
        }
        if (isset($get['sex']) && $get['sex'] != '') {
            $condition['a.sex'] = ['=', $get['sex']];
        }
        $condition['a.member_level'] = \member\Member::BIGCOW;
        $dataList = $db->where($condition)->page($this->page, $this->max_page_num)->select();
        foreach ($dataList as $k => $v) {
            $dataList[$k]['is_friends'] = Db::name('es_order_share_relationship')->where(['uid' => $this->wx_user_id, 'add_uid' => $v['id'], 'status' => 1])->find() ?? 0;
        }
        return $this->success($dataList);
    }

    //信息完善
    public function perfectInformation()
    {
        $data = input('post.');
        Db::startTrans();
        $res = [];
        if ($this->wx_user['member_level'] == \member\Member::GENERAL) {
            //普通用户
            $member_info = [
                'realName' => $data['realName'],
                'intention_country' => $data['intention_country'],
                'industry' => $data['industry'],
                'admission_time' => $data['admission_time'],
                'profess_study' => $data['profess_study'],
                'go_country_express' => $data['go_country_express'],
                'school' => $data['school'],
                'study_cert' => $data['study_cert'],
                'personal_desc' => $data['personal_desc'],
                'experience_desc' => $data['experience_desc'],
            ];
        } elseif ($this->wx_user['member_level'] == \member\Member::BIGCOW) {
            //大牛
            $member_info = [
                'realName' => $data['realName'],
                'isCountry' => $data['isCountry'],
                'study_cert' => $data['study_cert'],
                'personal_desc' => $data['personal_desc'],
                'experience_desc' => $data['experience_desc'],
            ];

            $res[] = DataService::wechatSave('StoreMember', ['sex' => $data['sex']], 'id', ['id' => $this->wx_user_id]);
            $education = $data['education'];  //更多教育经历
            if (!empty($education)) {
                $education = json_decode($education, true);
                foreach ($education as $k => $v) {
                    $v = [
                        'school' => $v['school'],
                        'uid' => $data['uid'],
                        'industry' => $v['industry'],
                        'startStudy' => $v['startStudy'],
                        'endStudy' => $v['endStudy'],
                    ];
                    $res[] = DataService::wechatSave('UserBigcowInfo', $v);
                }

            }
        }
        $member_info['uid'] = $this->wx_user_id;
        $res[] = DataService::wechatSave('StoreMemberInfo', $member_info, 'id', ['uid' => $this->wx_user_id]);
        if (check_arr($res)) {
            Db::commit();
            return $this->success('完善信息成功');
        } else {
            Db::rollback();
            return $this->error('网络故障');
        }

    }


    public function getUserInfo()
    {
        $userInfo = Db::name("store_member a")
            ->join("store_member_info b","a.id = b.uid")
            ->where(['a.id'=>$this->wx_user_id])
            ->find();
        return $this->success($userInfo);
    }

    //用户提现
    public function addWithdraw()
    {
        $data = input('post.');
        $rule = [
            'amount' => 'require|number',
        ];
        $msg = [
            'amount.require' => 'amount必传',
            'amount.number' => '请填写正确金额!',
        ];
        $validate = new \think\Validate($rule, $msg);
        if (!$validate->check($data)) {
            return $this->error($validate->getError());
        }
        $data['uid'] = $this->wx_user_id;
        if ($data['amount'] > $this->wx_user['account_money']) {
            return $this->error('账户金额不足');
        }
        if (DataService::save('EsWithdrawList', $data, 'id')) {
            return $this->success('提现提交成功,请耐心等待审核通过:)');
        } else {
            return $this->error('网络故障,请稍后重试:(');
        }
    }

    //获取用户提现列表
    public function getByUserWithdraw()
    {
        $withdraw_list = Db::table('es_withdraw_list')->where(['uid' => $this->wx_user_id])->page($this->page, $this->max_page_num)->select();
        return $this->success($withdraw_list);
    }


    //小程序进行支付
    public function goPay()
    {
        $data = input('post.');
        $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/miniapp/wechat/notify_wechat_pay/';
        $out_trade_no = date('YmdHis');
        $rule = [
            'type' => 'require',
            'cow_id' => 'require'
        ];
        $msg = [
            'type.require' => 'type必传',   //type 1 支付  2 分享
            'cow_id.require' => 'cow_id必传',
        ];
        $validate = new \think\Validate($rule, $msg);
        if (!$validate->check($data)) {
            return $this->error($validate->getError());
        }
        $arr = [
            'mid' => $this->wx_user_id,
            'type' => $data['type'],
            'order_no' => $out_trade_no,
            'goods_price' => sysconf('miniapp_money'),
            'pay_type' => 'wechat'
        ];

        $res = [TRUE];
        Db::startTrans();
//        if ($data['type'] == 2) {
//            $shareFlag = Db::name('es_order_share_relationship')->where(['uid' => $this->wx_user_id, 'add_uid' => $data['cow_id']])->value('order_id');
//            if ($shareFlag) {
//                //分享过的话 就进行统计叠加
//                Db::name("store_order")->where(['id' => $shareFlag])->setInc('share_num');
//                return $this->success('分享成功');
//            }
//
//        }
        $res['order_id'] = Db::table('store_order')->insertGetId($arr); //插入订单
        $share_data = [
            'uid' => $this->wx_user_id,
            'order_id' => $res['order_id'],
            'add_uid' => $data['cow_id'],
        ];
        $res['share_relationship'] = Db::table('es_order_share_relationship')->insertGetId($share_data);
        if (check_arr($res)) {
            Db::commit();
            if ($data['type'] == 2) {
                //TODO  分享第一次
                return $this->success(['relation_id'=>$res['share_relationship']]);
            }
            $payjsparam = getWechatIntance('结交新朋友', $out_trade_no, sysconf('miniapp_money') * 100, $notify_url, $this->wx_user['openid']);
            return $this->success($payjsparam);
        } else {
            Db::rollback();
            return $this->error('网络故障');
        }
    }



    //获取用户支付过朋友
    public function getFriendsList()
    {
        $dataList = Db::name('es_order_share_relationship a')
            ->join("store_member_info b", "a.uid = b.uid")
            ->join("store_order c","c.id = a.order_id")
            ->where(['uid' => $this->wx_user_id])
            ->page($this->page, $this->max_page_num)
            ->select();
        $this->success($dataList);
    }


    public function getByUserMessage()
    {
        $dataList = Db::name('es_message')
            ->where(['uid' => $this->wx_user_id])
            ->page($this->page, $this->max_page_num)
            ->select();
        if (!empty($dataList)) {
            $this->success($dataList);
        } else {
            $this->error('没有更多数据!');
        }

    }

    public function sendTemplate()
    {
        $data = input('param.');
        $validate = Validate::make([
            'template_id' => 'require',
        ], [
            'template_id.require' => '模板id必传！',
        ]);
        $validate->check($data) || $this->error($validate->getError());
        $arr = [
            'keyword1' => 'smallsha',
            'keyword2' => date('Y-m-d H:i:s'),
            'keyword3' => '请及时处理',
        ];

        sm(sendWechatTemplate('oAyFp5LmSoRpq8Mu2oEiv-mjt21Q', 'euNb4KhyvVw5soeSx6e-V74TuNyTAE6bAS4yLAXTlgw', $arr));
    }

}
