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

/**
 * 矿机管理
 * Class Fans
 * @package app\wechat\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/03/27 14:43
 */
class Machine extends BasicAdmin
{
    /**
     * 定义当前默认数据表
     * @var string
     */
    public $table = 'machine';
    
    /**
     * 列表
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function index()
    {
        $this->title = '矿机';
        $get = $this->request->get();
        
        $db = Db::table('machine');
        
        $db->field('*');
        
        return parent::_list($db->order('id desc'));
    }
    
    /**
     * 修改矿机
     */
    public function edit()
    {
        if (!$this->request->isPost()) {
            $member_id = input('param.id',0);
            
            if($member_id){
                $info = Db::name('machine')->where(['id'=>$member_id])->find();
                $this->assign('info',$info);
            }
        } else {
            $data = $this->request->post();
            $user = Db::name('machine')->where(['id'=>$data['id']])->find();
            if(isset($data['id'])){
                if($user['status'] == 1){
                    $result = Db::name('machine')->where(['id'=>$data['id']])->update($data);
                }
            }else{
//                 $mach = Db::name('machine')->where('status','<>',3)->find();
//                 if($mach) $this->error('存在可用矿机','');
                $data['stock'] = 0;
                $data['status'] = 1;
                $data['ver'] = time();
                $result = Db::name('machine')->insert($data);
            }
            if ($result !== FALSE) {
                $this->success('操作成功','');
            } else {
                $this->error('操作失败','');
            }
        }
        return view();
    }
    
    /**
     * 矿机状态
     */
    public function status()
    {
        $data = $this->request->post();
        $status = $this->request->get('status');
        $user = Db::name('machine')->where(['id'=>$data['id']])->find();
        if(isset($data['id'])){
            if($user['status'] == 1){
                if($status == 2 && $user['stock']!=0) $this->error('未售完','');
            }elseif($user['status'] == 0){
                if($status == 2) $this->error('禁用中','');
            }else{
                $this->error('该状态不可操作','');
            }
            Db::startTrans();
            if($status==2){
//                 $start_time = date('Y-m-d').' 00:00:01';
//                 $end_time = date('Y-m-d',strtotime('+ '.$user['day_num'].' day')).' 23:59:59';
//                 $res[] = Db::name('machine')->where(['id'=>$data['id']])->update(array('start_time'=>$start_time,'end_time'=>$end_time,'status'=>$status));
//                 $res[] = Db::name('store_machine')->where(['kid'=>$data['id'],'status'=>0])->update(array('start_time'=>$start_time,'end_time'=>$end_time,'status'=>1));
            }else{
                $res[] = Db::name('machine')->where(['id'=>$data['id']])->update(array('status'=>$status));
//                 if($status == 2){
//                     $member = Db::name('store_member')->field('id','usdt_suo')->where('usdt_suo','>',0)->select();
//                     foreach ($member as $vl){
//                         $res[] = Db::name('store_member')->where('id',$vl['id'])->update(array('usdt_suo'=>0));
//                         $res[] = mlog($vl['id'], 'account_money', $vl['usdt_suo'], "开矿释放冻结USDT",'kaikuang','',6,'');
//                     }
//                 }
            }
        }else{
            $this->error('操作失败','');
        }
        if (check_arr($res)) {
            Db::commit();
            $this->success('操作成功');
        } else {
            Db::rollback();
            $this->error('操作失败');
        }
    }
    
    /**
     * 列表
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function order_list()
    {
        $this->title = '购买记录';
        $get = $this->request->get();
        
        $db = Db::table('store_machine');
        
        $db->field('*');
        
        return parent::_list($db->order('id desc'));
    }
}