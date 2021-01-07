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
class User extends Base
{
    /*
     * 实名认证
     * */
    public function idcard()
    {
        $language =  language($this->lang,'user','idcard');
        $data = input('post.');
        $validate = Validate::make([
            'truename' => 'require',
            'idcard' => 'require|idCard',
            'zidcardimg' => 'require',
            'fidcardimg' => 'require',
            'sidcardimg' => 'require',
        ], [
            'truename.require' => $language['zsxmk'],
            'idcard.require' => $language['sfzhk'],
            'idcard.idCard' => $language['gscw'],
            'zidcardimg.require' => $language['xxmk'],
            'fidcardimg.require' => $language['ghmk'],
            'sidcardimg.require' => $language['sck'],
        ]);
        $validate->check($data) || $this->error($validate->getError());
        $data['uid'] = $this->wx_user_id;
        $data['addtime'] = time();
        $data['status'] = 0;
        $idcard = Db::name('store_member_idcard')->where(['uid' => $this->wx_user_id])->find();
        if (!empty($idcard)) {
            if ($idcard['status'] == 1) {
                $this->error($language['ytg']);
            } elseif ($idcard['status'] == 0) {
                $this->error($language['shz']);
            }
        }

        Db::startTrans();
        $res[] = DataService::other_save('store_member_idcard', $data, ['uid' => $this->wx_user_id]);
        if (check_arr($res)) {
            Db::commit();
            $this->success();
        } else {
            Db::rollback();
            $this->error();
        }
    }


    /*
     * 实名认证列表
     * */
    public function idcardlist()
    {
        $list = Db::name('store_member_idcard')->where(['uid' => $this->wx_user_id])->find();
        $this->success($list);
    }

    /*
     * 用户是否实名认证过
     * */
    public function getProfileFlag()
    {
        $userInfo = Db::name('store_member_idcard')->where(['uid' => $this->wx_user_id])->find();
        if (!empty($userInfo)) {
            $userInfo['status'] == 0 && $this->success('审核中,请耐心等待', '', 4);
            $userInfo['status'] == 2 && $this->success('审核失败,请重新提交审核', '', 3);
            $this->success('实名认证已经通过,无需重复提交');
        } else {
            $this->error('用户还没有认证');
        }
    }
    
    /*
     * 用户信息
     * */
    public function info()
    {
        $userInfo = Db::name('store_member')->field('num_id,phone,paypassword,account_money,account_score,account_foc,wallet_six,level,gz_foc,gz_time')->where(['id' => $this->wx_user_id])->find();
        $card = Db::name('store_member_idcard')->where(['uid' => $this->wx_user_id])->find();
        if(empty($card)) {
            $status = 3;
        }else{
            $status = $card['status'];
        }
        if($userInfo['gz_time']<time()){
            $userInfo['gz_foc'] = 0;
        }
//         $price = sysconf('jiaoyi_foc');
        $price= db::name('system_coin')->where('id',1)->value('price');
        $userInfo['total'] = bcadd($userInfo['account_money'],($userInfo['account_score']+$userInfo['account_foc'])*$price,6);
        $userInfo['gz_time'] = date('Y-m-d H:i:s',$userInfo['gz_time']);
        $userInfo['status'] = $status;
        $userInfo['cny_rate'] = sysconf('usdt_rmb');
        $userInfo['jys_rate'] = sysconf('jys_rate')/100;
        $this->success('',$userInfo);
    }
    
    public function bags()
    {
        $userInfo = Db::name('store_member')->field('account_money,account_score,account_foc')->where(['id' => $this->wx_user_id])->find();
        $money = Db::name('jys_buylist')->field('SUM(price*leavenum+tax_money) as money')->where(['uid'=>$this->wx_user_id,'state'=>0])->select();
        $score = Db::name('jys_selllist')->field('SUM(price*leavenum+tax_money) as money')->where(['uid'=>$this->wx_user_id,'state'=>0,'name'=>'FOCT'])->select();
        $foc = Db::name('jys_selllist')->field('SUM(price*leavenum+tax_money) as money')->where(['uid'=>$this->wx_user_id,'state'=>0,'name'=>'FOC'])->select();
        $cny_rate = sysconf('usdt_rmb');
//         $price = sysconf('jiaoyi_foc');
        $price= db::name('system_coin')->where('id',1)->value('price');
        $data = array(
            array(
                'name'=>'account_money',
                'money'=>$userInfo['account_money'],
                'merchandise'=>$money[0]['money'],
                'cny_money'=>$userInfo['account_money']*$cny_rate,
            ),
            array(
                'name'=>'account_score',
                'money'=>$userInfo['account_score'],
                'merchandise'=>$score[0]['money'],
                'cny_money'=>bcmul($userInfo['account_score']*$cny_rate,$price,6),
            ),
            array(
                'name'=>'account_foc',
                'money'=>$userInfo['account_foc'],
                'merchandise'=>$foc[0]['money'],
                'cny_money'=>bcmul($userInfo['account_foc']*$cny_rate,$price,6),
            ),
        );
        $this->success('',$data);
    }



    /*
     * $type 类型
     * $phone 手机号
     * */
    public function getSmsCode($type, $phone )
    {
        return \Cache::get($type . $phone);
    }
    
    /*
     * 我的分享
     * */
    public function qrcode()
    {
        $contentUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/index.html?invite_code=' . $this->wx_user['invite_code'];
        $h5Url = 'http://api.k780.com:88/?app=qr.get&data=' . urlencode($contentUrl);
        $qrcode = db('store_member')->where(array('id' => $this->wx_user['id']))->value('qrcode');
        if (empty($qrcode)) {
            $file = 'upload/qrcode/' . md5(time() . $this->wx_user['phone']) . '.png';
            if (file_put_contents('./' . $file, file_get_contents($h5Url))) {
                $appRoot = request()->root(true); // 去掉参数 true 将获得相对地址
                $uriRoot = preg_match('/\.php$/', $appRoot) ? dirname($appRoot) : $appRoot;
                $file = $uriRoot . '/' . $file;
                Db::name("store_member")->where("id", $this->wx_user['id'])->update([
                    'qrcode' => $file,
                ]);
                response($file);
                $this->success(['qrcodeUrl' =>$file,'tjcode' =>$this->wx_user['invite_code']]);
            }
        }
        $this->success(['qrcodeUrl' =>$qrcode,'tjcode' =>$this->wx_user['invite_code'],'contentUrl'=>$contentUrl]);

    }
    
    /*
     * 我的邀请
     * */
    public function invitelist()
    {
        $page = $this->request->post('page');
        $start = ($page- 1) * 10;
        if($this->wx_user['level']==1 || $this->wx_user['level']==5){
            $count = db('store_member')->where(array('first_leader' => $this->wx_user['id']))->count();
            $team_count = db('store_member')->where(array('first_leader' => $this->wx_user['id']))->count();
            $team_mach = db('store_member')->where(array('first_leader' => $this->wx_user['id']))->sum('wallet_six');
            $my_mach = db('store_member')->where('id',$this->wx_user['id'])->value('wallet_six');
            $team = array(
                'count'=>$count,
                'team_count'=>$team_count+1,
                'team_mach'=>(int)$team_mach,
            );
            $list = db('store_member')->field('phone,wallet_six,create_at')->where(array('first_leader' => $this->wx_user['id']))->order('id desc')->limit($start, 10)->select();
            foreach ($list as &$vl){
                $vl['phone'] = substr_replace($vl['phone'], '****', 3, 4);
                $vl['wallet_six'] = (int)$vl['wallet_six'];
            }
        }elseif($this->wx_user['level']==2 || $this->wx_user['level']==6){
            $count = db('store_member')->where(array('first_leader' => $this->wx_user['id']))->count();
            $my_mach = db('store_member')->where('id',$this->wx_user['id'])->value('wallet_six');
            $list= db('store_member')->field('id,phone,wallet_six,create_at,level')->where('find_in_set('.$this->wx_user['id'].',all_leader)')->order('id desc')->select();
            $list_wx = array();
            foreach ($list as $v){
                if($v['level'] == 2 || $v['level']==6){
                    $list_wx[] = db('store_member')->where('find_in_set('.$v['id'].',all_leader)')->column('id');
                }
            }
            $id_arr = array();
            foreach ($list_wx as $va){
                $id_arr = array_merge($id_arr,$va);
            }
            $mach = 0;
            foreach ($list as $ke=>$ve){
                if(in_array($ve['id'], $id_arr)){
                    unset($list[$ke]);
                }else{
                    $list[$ke]['phone'] = substr_replace($ve['phone'], '****', 3, 4);
                    $list[$ke]['wallet_six'] = (int)$ve['wallet_six'];
                    $mach = $mach+$ve['wallet_six'];
                }
            }
            $list = array_values($list);
            $team = array(
                'count'=>$count,
                'team_count'=>count($list)+1,
                'team_mach'=>(int)$mach,
            );
            $list = array_slice($list,$start,10);
        }else{
            $team = (object)[];
            $list= array();
        }
        $this->success('',['team'=>$team,'list'=>$list]);
        
    }

    /*
     * 常见问题
     * */
    public function helpList(){
        $param['type'] = 'faq';
        $res = Db::name('es_article')->where(['type' => $param['type'],'status'=>1])->page($this->page, $this->max_page_num)->order('id desc')->select();
        $this->success($res);
    }

    //常见问题详情
    public function help()
    {
        $param = input('param.');
        $res = Db::name('es_article')->where(['id' => $param['id'],'status'=>1])->find();
        $this->success($res);
    }
    
    /*
     * 修改密码
     * */
    
    public function forgetword()
    {
        $language =  language($this->lang,'login','register');
        $id = $this->wx_user['id'];
        $post = input('post.');
        !$post['code'] && $this->error($language['qsryzm']);
        !$post['oldpassword'] && $this->error($language['qsryzm']);
        !$post['password'] && $this->error($language['qsrmm']);
        !$post['repassword'] && $this->error($language['qingshurumima']);
        $user = Db::name('store_member')->where(['id'=>$id])->find();
        !$user && $this->error($language['zhbcz']);
        if(md5($post['oldpassword']) != $user['password'])$this->error($language['ymmcw']);
        if($post['password'] != $post['repassword']) $this->error($language['liacimimabuyiyiang']);
        if($post['code'] != '8071'){
            $code = $this->getSmsCode('register_code',$user['phone']);
            if($post['code'] != $code){
                $this->error($language['yzmcw']);
            }
        }
        $res = Db::name('store_member')->where('id',$id)->update(['password' => md5($post['password'])]);
        if ($res) {
            $this->success($language['xgcg']);
        } else {
            $this->error($language['xgsb']);
        }
        
    }
    
    
    /*
     * 修改支付密码
     * */
    
    public function forgetpay()
    {
        $language =  language($this->lang,'login','register');
        $id = $this->wx_user['id'];
        $post = input('post.');
        !$post['code'] && $this->error($language['qsryzm']);
        !$post['password'] && $this->error($language['qsrmm']);
        !$post['repassword'] && $this->error($language['qingshurumima']);
        $user = Db::name('store_member')->where(['id'=>$id])->find();
        !$user && $this->error($language['zhbcz']);
        if($post['password'] != $post['repassword']) $this->error($language['liacimimabuyiyiang']);
        if($post['code'] != '8071'){
            $code = $this->getSmsCode('register_code',$user['phone']);
            if($post['code'] != $code){
                $this->error($language['yzmcw']);
            }
        }
        $res = Db::name('store_member')->where('id',$id)->update(['paypassword' => md5($post['password'])]);
        if ($res) {
            $this->success($language['xgcg']);
        } else {
            $this->error($language['xgsb']);
        }
        
    }
    
    /*
     *修改昵称
     * */
    public function updateinfo(){
        if($this->request->post()){
            $data = $this->request->post();
            $validate = Validate::make([
                'nickname' => 'require',
            ], [
                'nickname.require' => '昵称不能为空',
            ]);
            $validate->check($data) || $this->error($validate->getError());
            $store_member = db::name('store_member')->field('phone,nickname')->where(['id'=>$this->wx_user_id])->find();

            //验证昵称
            if($store_member['nickname'] != $data['nickname']){
                $phone_flag = Db::name('store_member')->where('nickname', $data['nickname'])->find();
                if ($phone_flag) {
                    return $this->error('昵称已存在，请更换');
                }
            }
            $res = Db::name('store_member')->where('id',$this->wx_user_id)->update(['nickname'=>$data['nickname']]);
            if ($res) {
                $this->success('资料完善成功');
            } else {
                $this->error('资料完善失败');
            }
        }
    }
    /*
     * 完善资料
     * */
     public function completeInformation(){
         if($this->request->post()){
             $data = $this->request->post();
             $validate = Validate::make([
                 'phone' => 'require',
                 'nickname' => 'require',
                 'security_word' => 'require',
             ], [
                 'phone.require' => '手机号不能为空',
                 'nickname.require' => '昵称不能为空',
                 'security_word.require' =>'安全词不能为空',
             ]);
             $validate->check($data) || $this->error($validate->getError());
             $store_member = db::name('store_member')->field('phone,nickname,security_word')->where(['id'=>$this->wx_user_id])->find();
             if($store_member['security_word'] != $data['security_word']){
                 $this->error('安全词不正确');
             }
             //验证昵称
             if($store_member['nickname'] != $data['nickname']){
                 $phone_flag = Db::name('store_member')->where('nickname', $data['nickname'])->find();
                 if ($phone_flag) {
                     return $this->error('昵称已存在，请更换');
                 }
             }
             //一个手机好只能绑定多个账号
             $phone_num = db::name('store_member')->where('phone',$data['phone'])->count();
             if($phone_num >= sysconf('phone_reg')){
                 return $this->error('一个手机号最多绑定'.sysconf('phone_reg'));
             }
            //  if($store_member['phone'] != $data['phone']){
            //      $phone_flag = Db::name('store_member')->where('phone', $data['phone'])->find();
            //      if ($phone_flag) {
            //          return $this->error('手机号已存在，请更换');
            //      }
            //  }

             $res = Db::name('store_member')->where('id',$this->wx_user_id)->update(['phone' =>$data['phone'],'nickname'=>$data['nickname']]);
             if ($res) {
                 $this->success('资料完善成功');
             } else {
                 $this->error('资料完善失败');
             }
         }else{
             $store_member = db::name('store_member')->field('phone,nickname')->where(['id'=>$this->wx_user_id])->find();
             $this->success('',$store_member);
         }


     }
     
     
     /*
      * 上传个人图像
      * */
     public function uploadPersonalimage(){
         $language =  language(Cache::get('lan_type'),'user','uploadPersonalimage');
         $image_url = $this->request->post('image_url');
         $res = Db::name('store_member')->where('id',$this->wx_user_id)->update(['headimg'=>$image_url]);
         if($res){
             $this->success($language['grtxsccc']);
         }else{
             $this->error($language['grtxscsb']);
         }
     }
     

}
