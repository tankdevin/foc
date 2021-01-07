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

namespace app\admin\controller;

use controller\BasicAdmin;
use service\DataService;
use think\Db;
use think\facade\Config;

/**
 * 系统日志管理
 * Class Log
 * @package app\admin\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/02/15 18:12
 */
class Robot extends BasicAdmin
{
    /*
     * 卖单
     * */
    public function index()
    {
        // 日志行为类别
        $actions = Db::name('auto_robot');
        $this->assign('actions', $actions);
        // 日志数据库对象
        list($this->title, $get) = ['机器人列表', $this->request->get()];
        $db = Db::name('auto_robot')->order(['id'=>'desc']);
        foreach (['action', 'content', 'username'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->whereLike($key, "%{$get[$key]}%");
        }
        if (isset($get['addtime']) && $get['addtime'] !== '') {
            list($start, $end) = explode(' - ', $get['addtime']);
            $db->whereBetween('create_at', ["{$start} 00:00:00", "{$end} 23:59:59"]);
        }
        if (isset($get['ordersn']) && $get['ordersn'] != '') {
            $where['ordersn'] = $get['ordersn'];
            $db->where($where);
        }

        if (isset($get['uname']) && $get['uname'] != '') {
            $where['uname'] = $get['uname'];
            $db->where($where);
        }
        if (isset($get['state']) && $get['state'] != '') {
            $where['state'] = $get['state'];
            $db->where($where);
        }
        return parent::_list($db);
    }



    //执行文章编辑的操作
    public function edit()
    {
        $id = input('id');
        if (request()->isPost()) {
            $where = array();
            $data = request()->post();
            //执行判断标签的操作
            if ($id) {
                $text = '修改';
                $buy_user_id = db::name('store_member')->where(['address'=>$data['buy_user_id']])->value('id');
                if(!$buy_user_id){
                    $this->error("买方账号不存在，请重新填写", '');
                }
                $sell_user_id = db::name('store_member')->where(['address'=>$data['sell_user_id']])->value('id');
                if(!$sell_user_id){
                    $this->error("买方账号不存在，请重新填写", '');
                }
                $where['buy_user_id'] = $buy_user_id;
                $where['sell_user_id'] = $sell_user_id;
                $where['currency_id'] = $data['currency_id'];
                $where['legal_id'] = $data['legal_id'];
                $where['up_weight'] = $data['up_weight'];
                $where['down_weight'] = $data['down_weight'];
                $where['init_price'] = $data['init_price'];
                $where['min_number'] = $data['min_number'];
                $where['max_number'] =  $data['max_number'];
                $where['up_price'] = $data['up_price'];
                $where['down_price'] =  $data['down_price'];
                $where['min_number'] = $data['min_number'];
                $where['max_number'] =  $data['max_number'];
                $where['min_price'] = $data['min_price'];
                $where['max_price'] =  $data['max_price'];
                $where['price_precision'] = $data['price_precision'];
                $where['num_precision'] =  $data['num_precision'];
                $where['min_need_second'] = $data['min_need_second'];
                $where['max_need_second'] =$data['max_need_second'];
                $res = Db::name("auto_robot")->where(array(
                    'id' => $id
                ))->update($where);
            } else {
                $text = '添加';
                $buy_user_id = db::name('store_member')->where(['address'=>$data['buy_user_id']])->value('id');
                if(!$buy_user_id){
                    $this->error("买方账号不存在，请重新填写", '');
                }
                $sell_user_id = db::name('store_member')->where(['address'=>$data['sell_user_id']])->value('id');
                if(!$sell_user_id){
                    $this->error("买方账号不存在，请重新填写", '');
                }
                $auto_robot = db::name('auto_robot')->where(['currency_id'=>$data['currency_id']])->find();
                if($auto_robot){
                    $this->error("该交易对已经有机器人了", '');
                }
                $where['buy_user_id'] = $buy_user_id;
                $where['sell_user_id'] = $sell_user_id;
                $where['currency_id'] = $data['currency_id'];
                $where['legal_id'] = $data['legal_id'];
                $where['up_weight'] = $data['up_weight'];
                $where['down_weight'] = $data['down_weight'];
                $where['init_price'] = $data['init_price'];
                $where['min_number'] = $data['min_number'];
                $where['max_number'] =  $data['max_number'];
                $where['up_price'] = $data['up_price'];
                $where['down_price'] =  $data['down_price'];
                $where['min_number'] = $data['min_number'];
                $where['max_number'] =  $data['max_number'];
                $where['min_price'] = $data['min_price'];
                $where['max_price'] =  $data['max_price'];
                $where['price_precision'] = $data['price_precision'];
                $where['num_precision'] =  $data['num_precision'];
                $where['min_need_second'] = $data['min_need_second'];
                $where['max_need_second'] =$data['max_need_second'];
                $where['create_time'] =time();
                $res = Db::name("auto_robot")->insertGetId($where);
            }
            if ($res) {
                $this->success("{$text}成功", '');
            } else {
                $this->error("{$text}失败", '');
            }
        }
        $info = [];
        if ($id) {
            $info = Db::name("auto_robot")->where(array(
                'id' => $id
            ))->find();
        }
        $this->assign('currency_id', Config::get("currency"));
        $this->assign('legal_id',  Config::get("legal"));
        $this->assign('info', $info);
        return view();
    }

    /*
     * 启用/禁止
     * */
    /**
     * 卖单设置禁止
     */
    public function qiyong()
    {

        try {
            $state = Db::name("auto_robot")->where('id', input('param.id'))->value('is_start');
            $state = $state == 1 ? 0 : 1;
            Db::name("auto_robot")->where('id', input('param.id'))->setField('is_start', $state);
        } catch (\Exception $e) {
            $this->error("操作失败，请稍候再试！");
        }
        $this->success('操作成功！', '');
    }

}
