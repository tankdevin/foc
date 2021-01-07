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
 * 微信粉丝管理
 * Class Fans
 * @package app\wechat\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/03/27 14:43
 */
class Member extends BasicAdmin
{

    /**
     * 定义当前默认数据表
     * @var string
     */
    public $table = 'store_member';

    /**
     * 显示粉丝列表
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function index()
    {
        $this->title = '用户中心';
        $get = $this->request->get();
        $db = Db::name($this->table);
        $levellist = Db::name('sys_level')->select();
        $this->assign('level',$levellist);
        foreach (['phone', 'member_level'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->whereLike($key, "%{$get[$key]}%");
        }
        return parent::_list($db->order('id desc'));
    }

    /**
     * 列表数据处理
     * @param array $list
     */
    public function add()
    {
        $levellist = Db::name('sys_level')->select();
        $this->assign('level',$levellist);
        return $this->_form($this->table, 'form');
    }


    //执行文章状态的操作
    public function memberstatus()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $id = $data['id'];
            if (empty($id))
                $this->error('你好,请选择要操作的数据');

            $where['id'] = array('in', $id);
            $method = $data['field'];
            switch (strtolower($method)) {
                case 'resume':
                    $data = array('status' => 0);
                    break;
                case  'forbid':
                    $data = array('status' => 1);
                    break;
                case  'delete':
                    $res = Db::name('es_leave')->where('id','in',$id)->delete();
                    if ($res) {
                        $this->success('删除成功', '');
                    } else {
                        $this->error('删除失败');
                    }
                    break;
                default:
                    $this->error('参数非法');
            }
            if (Db::name('es_article')->where($where)->update($data)) {
                $this->success('操作成功', '');
            } else {
                $this->error('操作失败');
            }
        }
    }

    public function edit()
    {
        $levellist = Db::name('sys_level')->select();
        $this->assign('level',$levellist);
        return $this->_form($this->table, 'form');
    }

    /**
     * 用户禁用
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        if (in_array('10000', explode(',', $this->request->post('id')))) {
            $this->error('系统超级账号禁止操作！');
        }
        if (DataService::update($this->table)) {
            $this->success("用户禁用成功！", '');
        }
        $this->error("用户禁用失败，请稍候再试！");
    }

    /**
     * 用户禁用
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        if (DataService::update($this->table)) {
            $this->success("用户启用成功！", '');
        }
        $this->error("用户启用失败，请稍候再试！");
    }

    public function open(){
        $id = input('get.id');
    }

    public function openlist($id){

    }

    public function lxwm()
    {
        $this->title = '留言管理';
        $get = $this->request->get();
        $db = Db::name("es_leave");
        $where = array();
        if (isset($get['type']) && $get['type'] != '') {
            $where['status'] = $get['type'];
            $db->where($where);
        }
        if (isset($get['name']) && $get['name'] != '') {
            $where['uid'] = Db::name('store_member')->where(array('nickname' => $get['name']))->value('id');
            $db->where($where);
        }
//
//        if (isset($get['time']) && $get['time'] != '') {
//            $date = $get['date'];
//            list($start, $end) = explode(' - ', $date);
//            if ($get['time'] == 'end') {
//                $db->where('endtime', 'between', [strtotime($start), strtotime($end)]);
//            } else {
//                $db->where('addtime', 'between', [strtotime($start), strtotime($end)]);
//            }
//        }
        $db->order('id desc');
        return parent::_list($db);
    }
    public function _lxwm_data_filter(&$data){
        foreach($data as $k=>$v){
            $data[$k]['user_info'] = Db::name('store_member')->where(array('id' => $v['uid']))->find();
        }
    }

    public function lxwmedit()
    {
        $get = $this->request->get();
        if (request()->post()){
            $post = $this->request->post();
            if ($post['reply'] == ''){
                $this->error('回复内容不能为空');
            }
            $where= [];
            $where['reply'] = $post['reply'];
            $where['create_time'] = session('user')['id'];
            $where['reply_time'] = time();
            $where['status'] = 1;
            $db = Db::name('es_leave')->where(['id'=>$get['id']])->update($where);
            if ($db){
                $this->success('回复留言成功','');
            }else{
                $this->error('回复留言失败');
            }
        }
        $info = Db::name('es_leave')->where(['id'=>$get['id']])->find();
        $this->assign('info',$info);
        return view();
    }
    //会员族谱
    public function member_org(){
        $member_id = input('param.member_id',0);
        $user_data = Db::name('store_member a')
            ->field('a.id,a.create_at,a.phone,a.account_money,a.create_at,a.address,a.wallet_six,a.level')
            ->where(['a.id'=>$member_id])
            ->find();
        $user_data['level_name'] = member_level($user_data['level']);
        $user_data['wallet_six'] = intval($user_data['wallet_six']);
       
        $this->assign('user_org',$user_data);

        $this->assign('datascource',json_encode($this->getTreeMember($user_data['id'])));

        return view();
    }


    public function getTreeMember($member_id =0){
        $list = [];
        $users =    Db::name('store_member a')
            ->field('a.id,a.create_at,a.phone,a.account_money,a.create_at,a.address,a.wallet_six,a.level')
            ->where(['a.first_leader'=>$member_id])->select();
        if (!empty($users)) {
            foreach ($users as $k => &$v) {
                $v['children'] = $this->getTreeMember($v['id']);
                $v['level_name'] = member_level($v['level']);
                $v['wallet_six'] = intval($v['wallet_six']);
                $list[] = $v;
            }
        }
        return $list;
    }

    //身份认证审核
    public function idaudit()
    {
        $this->title = '身份认证管理';
        $get = $this->request->get();
        $db = Db::name("store_member_idcard");
        $where = array();
        if (isset($get['type']) && $get['type'] != '') {
            $where['status'] = $get['type'];
            $db->where($where);
        }
//        sm($get);
        if (isset($get['name']) && $get['name'] != '') {
            $where['uid'] = Db::name('store_member')->where(array('phone' => $get['name']))->value('id');
            $db->where($where);
        }
        $db->order('id desc');
       return parent::_list($db);
    }
    
    //身份认证操作

    public function idpass()
    {
        $param = input('param.');
        Db::startTrans();
        if($param['status'] == 1){
            $uid = Db::name('store_member_idcard')->where(['id'=>$param['id']])->value('uid');
            $db[] = Db::name('store_member_idcard')->where(['id'=>$param['id']])->update(['status'=>$param['status']]);
            $db[] = Db::name('store_member')->where(['id'=>$uid])->update(['is_renzheng'=>2]);
            $db[] = Db::name('store_member')->where(['id'=>$uid,'level'=>0])->update(['level'=>1]);
        }elseif($param['status'] == 2){
            $db[] = Db::name('store_member_idcard')->where(['id'=>$param['id']])->update(['status'=>$param['status']]);
        }
        if (check_arr($db)){
            Db::commit();
//             $store_member_idcard = Db::name('store_member_idcard')->where(['id'=>$param['id']])->find();
//             //查看实名认证的会员信息
//             //后台审核通过
//            $first_leader = Db::name('store_member')->where(['id'=>$store_member_idcard['uid']])->value('first_leader');
//            mlog($store_member_idcard['uid'], 'dsf_acc',sysconf('shtg_dsf_score'), '实名认证后台审核赠送待释放acc'.sysconf('shtg_dsf_score'), '', '', '11', $store_member_idcard['uid']);
//            //添加释放记录
//            $res= Db::name('acc_release')->insert(['uid' => $store_member_idcard['uid'],'num' => sysconf('shtg_dsf_score'),'not_sf_num'=>sysconf('shtg_dsf_score')]);
//            if($first_leader){
//                 mlog($first_leader, 'dsf_acc',sysconf('shtg_dsf__tj_score'), '推荐人获得(实名认证审核成功)待释放acc'.sysconf('shtg_dsf__tj_score'), '', '', '11', $store_member_idcard['uid']);
//                  //添加推荐人释放记录
//                  $res= Db::name('acc_release')->insert(['uid' => $first_leader,'num' => sysconf('shtg_dsf__tj_score'),'not_sf_num'=>sysconf('shtg_dsf__tj_score')]);
//            }
            $this->success('操作成功');
        } else{
            Db::rollback();
            $this->error('操作失败');
        }
    }
    
}
