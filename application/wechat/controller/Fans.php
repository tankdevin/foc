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

namespace app\wechat\controller;

use app\wechat\service\FansService;
use app\wechat\service\TagsService;
use controller\BasicAdmin;
use service\LogService;
use service\ToolsService;
use service\WechatService;
use think\Db;

/**
 * 微信粉丝管理
 * Class Fans
 * @package app\wechat\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/03/27 14:43
 */
class Fans extends BasicAdmin
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
        $this->title = '用户列表';
        $get = $this->request->get();

        $db = Db::table('store_member');

        if (isset($get['create_at']) && $get['create_at'] !== '') {
            list($start, $end) = explode(' - ', $get['create_at']);
            $db->whereBetween('create_at', ["{$start} 00:00:00", "{$end} 23:59:59"]);
        }
          if (isset($get['phone']) && $get['phone'] !== '') {
            $db->where('phone',$get['phone']);
            //var_dump($get['nickname']);
        }

        if (isset($get['address']) && $get['address'] !== '') {
            $db->where('address',$get['address']);
            //var_dump($get['nickname']);
        }
        
        if (isset($get['num_id']) && $get['num_id'] !== '') {
            $db->where('invite_code', "{$get['num_id']}");
        }
        if (isset($get['fir_num_id']) && $get['fir_num_id'] !== '') {
            $firnum = Db::name('store_member')->where('invite_code',$get['fir_num_id'])->value('id');
            $db->where('first_leader', $firnum);
        }

        if (isset($get['is_renzheng']) && $get['is_renzheng'] !== '') {
            $db->where('is_renzheng', '=',$get['is_renzheng']);
        }
        if (isset($get['level']) && $get['level'] !== '') {
            $db->where('level', '=',$get['level']);
        }

        $db->field('*,(SELECT SUM(real_price) FROM store_order where mid = store_member.id and type =1 and status =1 ) as num');

        return parent::_list($db->where('is_zhujici',1)->order('id desc'));
    }

    public function _index_data_filter( &$data )
    {
        foreach($data as &$v){
            $v['member_level_info'] = Db::name('sys_level')->where(['id'=>$v['member_level']])->value('title');

        }
        $sys_level = Db::name('sys_level')->select();
        $sys_earnings = Db::name('sys_earnings')->select();
        $this->assign('sys_level',$sys_level);
        $this->assign('sys_earnings',$sys_earnings);
    }


    /**
     * 设置黑名单
     */
    public function backadd()
    {

        try {
            $is_disable = Db::name($this->table)->where('id', input('param.id'))->value('is_disable');
            $is_disable = $is_disable == 1 ? -1 : 1;
            Db::name($this->table)->where('id', input('param.id'))->setField('is_disable', $is_disable);
        } catch (\Exception $e) {
            $this->error("操作失败，请稍候再试！");
        }
        $this->success('操作成功！', '');
    }
    
    /**
     * 删除
     */
    public function delete()
    {
        $meber = Db::name($this->table)->where('first_leader',input('param.id'))->find();
        if($meber) $this->error("该用户不能删除！");
        try {
            Db::name($this->table)->where('id', input('param.id'))->delete();
        } catch (\Exception $e) {
            $this->error("删除失败，请稍候再试！");
        }
        $this->success('删除成功！', '');
    }
    
    /**
     * 设置冻结
     */
    public function dongjie()
    {
        try {
            $dongjie = Db::name($this->table)->where('id', input('param.id'))->value('dongjie');
            $dongjie = $dongjie == 1 ? 0 : 1;
            Db::name($this->table)->where('id', input('param.id'))->setField('dongjie', $dongjie);
        } catch (\Exception $e) {
            $this->error("操作失败，请稍候再试！");
        }
        $this->success('操作成功！', '');
    }
    /**
     * 激活
     */
    public function backaddad()
    {

        try {
            $is_disable = Db::name($this->table)->where('id', input('param.id'))->value('is_renzheng');
            $is_disable = $is_disable == 1 ? -1 : 1;
            Db::name($this->table)->where('id', input('param.id'))->setField('is_renzheng', 2);
        } catch (\Exception $e) {
            $this->error("操作失败，请稍候再试！");
        }
        $this->success('操作成功！', '');
    }



    /**
     * 谷歌秘钥重置
     */
    public function googlecz()
    {

        try {
            $is_disable = Db::name($this->table)->where('id', input('param.id'))->value('google_state');
            $is_disable = $is_disable == 1 ? 0 : 1;
            Db::name($this->table)->where('id', input('param.id'))->setField('google_state', $is_disable);
        } catch (\Exception $e) {
            $this->error("操作失败，请稍候再试！");
        }
        $this->success('操作成功！', '');
    }
    /**
     * 标签选择
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function tagset()
    {
        $tags = $this->request->post('tags', '');
        $fans_id = $this->request->post('fans_id', '');
        $fans = Db::name('WechatFans')->where(['id' => $fans_id])->find();
        empty($fans) && $this->error('需要操作的数据不存在!');
        try {
            $wechat = WechatService::WeChatTags();
            foreach (explode(',', $fans['tagid_list']) as $tagid) {
                is_numeric($tagid) && $wechat->batchUntagging([$fans['openid']], $tagid);
            }
            foreach (explode(',', $tags) as $tagid) {
                is_numeric($tagid) && $wechat->batchTagging([$fans['openid']], $tagid);
            }
            Db::name('WechatFans')->where(['id' => $fans_id])->setField('tagid_list', $tags);
        } catch (\Exception $e) {
            $this->error('粉丝标签设置失败, 请稍候再试!');
        }
        $this->success('粉丝标签成功!', '');
    }

    /**
     * 给粉丝增加标签
     */
    public function tagadd()
    {
        $tagid = $this->request->post('tag_id', 0);
        empty($tagid) && $this->error('没有可能操作的标签ID');
        try {
            $openids = $this->_getActionOpenids();
            WechatService::WeChatTags()->batchTagging($openids, $tagid);
        } catch (\Exception $e) {
            $this->error("设置粉丝标签失败, 请稍候再试! " . $e->getMessage());
        }
        $this->success('设置粉丝标签成功!', '');
    }

    /**
     * 移除粉丝标签
     */
    public function tagdel()
    {
        $tagid = $this->request->post('tag_id', 0);
        empty($tagid) && $this->error('没有可能操作的标签ID');
        try {
            $openids = $this->_getActionOpenids();
            WechatService::WeChatTags()->batchUntagging($openids, $tagid);
        } catch (\Exception $e) {
            $this->error("删除粉丝标签失败, 请稍候再试! ");
        }
        $this->success('删除粉丝标签成功!', '');
    }

    /**
     * 获取当前操作用户openid数组
     * @return array
     */
    private function _getActionOpenids()
    {
        $ids = $this->request->post('id', '');
        empty($ids) && $this->error('没有需要操作的数据!');
        $openids = Db::name($this->table)->whereIn('id', explode(',', $ids))->column('openid');
        empty($openids) && $this->error('没有需要操作的数据!');
        return $openids;
    }

    /**
     * 同步粉丝列表
     */
    public function sync()
    {
        try {
            Db::name($this->table)->where('1=1')->delete();
            [FansService::sync(), TagsService::sync()];
            LogService::write('微信管理', '同步全部微信粉丝成功');
        } catch (\Exception $e) {
            $this->error('同步粉丝记录失败，请稍候再试！' . $e->getMessage());
        }
        $this->success('同步获取所有粉丝成功！', '');
    }


    public function user_add()
    {
        if (!$this->request->isPost()) {
//             0用户1会员2节点3董事4联创5动态股东6预备节点
            $sys_level = array(array('id'=>0,'title'=>'普通用户'),array('id'=>1,'title'=>'会员'),array('id'=>2,'title'=>'节点'),array('id'=>6,'title'=>'预备节点'),array('id'=>3,'title'=>'董事'),array('id'=>4,'title'=>'联创'),array('id'=>5,'title'=>'动态股东'));
            $member_id = input('param.member_id',0);

            if($member_id){
                $info = Db::name('store_member')->where(['id'=>$member_id])->find();

                $info['first_leader'] = Db::name('store_member')->where(['id'=>$info['first_leader']])->value('address');
                $this->assign('info',$info);
            }
            $this->assign('sys_level', $sys_level);
        } else {

            $data = $this->request->post();
            $parentid = 0;
            $user = Db::name('store_member')->where(['id'=>$data['id']])->find();
            unset($data['parent_phone']);
            if(isset($data['id'])){
                if(!empty($data['password'])&&!empty($data['paypassword'])&&$data['password'] == $data['paypassword']){
                    $where['password'] = md5($data['password']);
                }
                if($user['level'] != $data['level']) {
                    if(in_array($data['level'], array(0,5))){
                        $this->error('该等级不能手动设置','');
                    }else{
                        $where['level'] = $data['level'];
                        if($user['level'] == 6 && $data['level'] == 1 && $user['usdt_suo']>0){
                            $res[] = mlog($user['id'],'usdt_suo',-$user['usdt_suo'],'撤销预备节点冻结USDT清除','suoUsdt','','12');
                        }
                    }
                }
                if(!empty($data['wallet_six'])){
                    $where['wallet_six'] = ($data['wallet_six']);
                }
                if(!empty($data['suocang_num'])){
                    $where['suocang_num'] = ($data['suocang_num']);
                }
                $res[] = Db::name('store_member')->where(['id'=>$data['id']])->update($where);
            }else{
                $this->error('操作失败','');
            }
            
            if(check_arr($res)){
                Db::commit();
                return $this->success('操作成功');
            }else{
                Db::rollback();
                return $this->error('操作失败');
            }
        }
        return view();
    }

    public function user_balance()
    {
        if($this->request->post()){
            $data = $this->request->post();
            if($data['member_id']){
                $store_member = db::name('store_member')->where(['id'=>$data['member_id']])->find();
                $content_str = '';
                if($data['recharge_type'] ==1){
                    if($store_member['account_money']<$data['account_money']){
                        return $this->error('余额不足');
                    }
                    $content_str = "减少用户:{$store_member['address']},金额:{$data['account_money']}";
                    $data['account_money'] = '-'.$data['account_money'];
                }else{
                    $content_str = "增加用户:{$store_member['address']},金额:{$data['account_money']}";
                    $data['account_money'] = $data['account_money'];
                }
                Db::startTrans();
                $res_id = $res[] = mlog($data['member_id'],'account_money',$data['account_money'],$content_str,'rechangUsdt','','12');
                /*if($data['recharge_type'] ==1){
                    bagslanguage($res_id['1'],$data['account_money'],'','','',39);
                }else{
                    bagslanguage($res_id['1'],$data['account_money'],'','','',40);
                }*/
                if(check_arr($res)){
                    Db::commit();
                    return $this->success('操作成功');
                }else{
                    Db::rollback();
                    return $this->error('操作失败');
                }
            }else{
                return $this->error('账户不存在！');
            }
        }
        return view();
    }
    /*
     * 可用NF
     * */
    public function user_kyacc()
    {
        if($this->request->post()){
            $data = $this->request->post();

            if($data['member_id']){
                $store_member = db::name('store_member')->where(['id'=>$data['member_id']])->find();
                $content_str = '';
                Db::startTrans();
                if($data['recharge_type'] ==1){
                    if($store_member['account_score']<$data['account_score']){
                        return $this->error('余额不足');
                    }
                    $content_str = "减少用户:{$store_member['address']},金额:{$data['account_score']}";
                    $data['account_score'] = '-'.$data['account_score'];
                }else{
                    $content_str = "增加用户:{$store_member['address']},金额:{$data['account_score']}";
                    $data['account_score'] = $data['account_score'];
                }
                $res_id = $res[]= mlog($data['member_id'],'account_score',$data['account_score'],$content_str,'rechangfoc','','12');
                if(check_arr($res)){
                    Db::commit();
                    return $this->success('操作成功');
                }else{
                    Db::rollback();
                    return $this->error('操作失败');
                }
            }else{
                return $this->error('账户不存在！');
            }
        }
        return view();
    }
    /*
     * 可用FOC
     * */
    public function user_foc()
    {
        if($this->request->post()){
            $data = $this->request->post();

            if($data['member_id']){
                $store_member = db::name('store_member')->where(['id'=>$data['member_id']])->find();
                $content_str = '';
                if($data['recharge_type'] ==1){
                    if($store_member['account_foc']<$data['account_foc']){
                        return $this->error('余额不足');
                    }
                    $content_str = "减少用户:{$store_member['address']},金额:{$data['account_foc']}";
                    $data['account_foc'] = '-'.$data['account_foc'];
                }else{
                    $content_str = "增加用户:{$store_member['address']},金额:{$data['account_foc']}";
                    $data['account_foc'] = $data['account_foc'];
                }
                Db::startTrans();
                $res_id = $res= mlog($data['member_id'],'account_foc',$data['account_foc'],$content_str,'rechangfoc','','12');
                if(check_arr($res)){
                    Db::commit();
                    return $this->success('操作成功');
                }else{
                    Db::rollback();
                    return $this->error('操作失败');
                }
            }else{
                return $this->error('账户不存在！');
            }
        }
        return view();
    }
    public function user_wakuang()
    {
        if($this->request->post()){
            $data = $this->request->post();
            if($data['member_id']){
                $content_str = '';
                if($data['recharge_type'] ==1){
                    $content_str = "减少用户:{$data['member_id']},挖矿收益:{$data['shouyi_money']}";
                    $data['shouyi_money'] = '-'.$data['shouyi_money'];
                }else{
                    $content_str = "增加用户:{$data['member_id']},挖矿收益:{$data['shouyi_money']}";
                    $data['shouyi_money'] = $data['shouyi_money'];
                }
                Db::startTrans();
                $res = mlog($data['member_id'],'shouyi_money',$data['shouyi_money'],$content_str,'20');
                if(check_arr($res)){
                    Db::commit();
                    return $this->success('操作成功');
                }else{
                    Db::rollback();
                    return $this->error('操作失败');
                }
            }else{
                return $this->error('账户不存在！');
            }
        }
        return view();
    }

    public function user_btt()
    {
        if($this->request->post()){
            $data = $this->request->post();
            if($data['member_id']){
                $content_str = '';
                if($data['recharge_type'] ==1){
                    $content_str = "减少用户:{$data['member_id']},金额:{$data['btt']}";
                    $data['btt'] = '-'.$data['btt'];
                }else{
                    $content_str = "增加用户:{$data['member_id']},金额:{$data['btt']}";
                    $data['btt'] = $data['btt'];
                }
                Db::startTrans();
                $res = mlog($data['member_id'],'pay_money',$data['btt'],$content_str,'20');
                if(check_arr($res)){
                    Db::commit();
                    return $this->success('操作成功');
                }else{
                    Db::rollback();
                    return $this->error('操作失败');
                }
            }else{
                return $this->error('账户不存在！');
            }
        }
        return view();
    }
    
    
       public function user_hashrate()
    {
        if($this->request->post()){
            $data = $this->request->post();
            if($data['member_id']){
                $content_str = '';
                if($data['recharge_type'] ==1){
                    $content_str = "减少用户:{$data['member_id']},金额:{$data['btt']}";
                    $data['btt'] = '-'.$data['btt'];
                }else{
                    $content_str = "增加用户:{$data['member_id']},金额:{$data['btt']}";
                    $data['btt'] = $data['btt'];
                }
                Db::startTrans();
                $res = mlog($data['member_id'],'mining',$data['btt'],$content_str,'20');
                if(check_arr($res)){
                    Db::commit();
                    return $this->success('操作成功');
                }else{
                    Db::rollback();
                    return $this->error('操作失败');
                }
            }else{
                return $this->error('账户不存在！');
            }
        }
        return view('user_btt');
    }
    
    
       public function user_hashrate2()
    {
        if($this->request->post()){
            $data = $this->request->post();
            if($data['member_id']){
                $content_str = '';
                if($data['recharge_type'] ==1){
                    $content_str = "减少用户:{$data['member_id']},金额:{$data['btt']}";
                    $data['btt'] = '-'.$data['btt'];
                }else{
                    $content_str = "增加用户:{$data['member_id']},金额:{$data['btt']}";
                    $data['btt'] = $data['btt'];
                }
                Db::startTrans();
                $res = mlog($data['member_id'],'hashrate',$data['btt'],$content_str,'20');
                if(check_arr($res)){
                    Db::commit();
                    return $this->success('操作成功');
                }else{
                    Db::rollback();
                    return $this->error('操作失败');
                }
            }else{
                return $this->error('账户不存在！');
            }
        }
        return view('user_btt');
    }
    
    /*
    显示会员等级
    */
    public function userLevel()
    {
        $this->title = '会员升级列表';
        $get = $this->request->get();
        $db = Db::table('store_member_upgrade_record');
        if (isset($get['addtime']) && $get['addtime'] != '') {
            $date = $get['addtime'];
            list($start, $end) = explode(' - ', $date);
            $db->where('addtime', 'between', [strtotime($start), strtotime($end)+86400]);
            
        }
        if (isset($get['phone']) && $get['phone'] !== '') {
            $db->where('phone', 'like',"%{$get['phone']}%");
        }
        //类型
        if (isset($get['type']) && $get['type'] != '') {
            //var_dump(111);
            $where['type'] = $get['type'];
            $db->where($where);
        }
        return parent::_list($db->order('id desc'));
    }
}
