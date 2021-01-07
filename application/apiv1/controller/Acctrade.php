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
class Acctrade extends Base
{

    /*
    * 收款码
    * */
    public function blockReceiptCode()
    {

        //如果存在的话，就能进行生成
        $userinfo = Db::name('store_member')->where(['id' => $this->wx_user_id])->find();

        $contentUrl = 'http://'.$_SERVER['HTTP_HOST'].'/index.php/apiv1/acctrade/otherUsdtnumid?num_id='. $userinfo['num_id'].'&type=1';
        //  $h5Url2 = "http://h5ip.cn/index/api?format=json&url=" . urlencode($contentUrl);
        $h5Url = 'http://qr.liantu.com/api.php?text=' . urlencode($contentUrl);
        
        if (empty($userinfo['acc_img'])) {
            $file = 'upload/acc/' . md5(time() . $userinfo['num_id']) . '.png';
            if (file_put_contents('./' . $file, file_get_contents($h5Url))) {
                $appRoot = request()->root(true); // 去掉参数 true 将获得相对地址
                $uriRoot = preg_match('/\.php$/', $appRoot) ? dirname($appRoot) : $appRoot;
                $file = $uriRoot . '/' . $file;
                Db::name("store_member")->where(['id' => $this->wx_user_id])->update([
                    'acc_img' => $file,
                ]);
                response($file);
                $this->success('',['qrcodeUrl' => $file, 'linkUrl' => $contentUrl]);
            }
        }

        $this->success('',['qrcodeUrl' => $userinfo['acc_img'], 'linkUrl' => $contentUrl]);

    }
    
    
    /*
     * 扫描二维码，识别信息
     * */
    public function otherUsdtnumid(){
        $data = $this->request->param();
        if($data['num_id']){
            $this->success('', $data);
        }else{
            $this->error('参数错误');
        }

    }
    
    
     /*
     *可用acc互转记录
     * */
    public function index(){
        $ky_acc = $this->wx_user['ky_acc'];
        //记录
        $result = Db::name('bags_log')
            ->where(['uid' => $this->wx_user_id])
            //->where('extends', 'in', 'static_bonus,team_bonus_money,share_dig,lettery,pay_mining')
            ->where(['type'=>'ky_acc'])
            //->whereTime('create_time', 'today')
            ->order('id desc')
            ->page($this->page, $this->max_page_num)
            ->select();
        $this->success('',['ky_acc' => $ky_acc, 'result' => $result]);
    }
    
    
       /*
    * 会员之间进行互转(acc)
    * */
    public function transferMerchatad()
    {
        $data = input('post.');
        $validate = Validate::make([
            'num_id' => 'require',
            'num' => 'require',
            'paypassword'=> 'require',
        ], [
            'num_id.require' => '对方id不能为空',
            'num.require' => 'num不能为空',
            'paypassword.require' =>'支付密码不能为空',
        ]);
        $validate->check($data) || $this->error($validate->getError());
        $merchatInfo = Db::name('store_member')->where(['num_id' => $data['num_id']])->find();
        if ($merchatInfo) {
            if($this->wx_user['isjh'] != 1){
                $this->error("您未激活，无法进行转账");
            }
            if($merchatInfo['id']  == $this->wx_user_id){
                $this->error("自己不能给自己转");
            }
            $this->wx_user['ky_acc'] < $data['num'] && $this->error('acc数量不足');
            if(!is_numeric($data['paypassword']) || strlen($data['paypassword']) !='6'){
                $this->error("交易密码必须是6位数字");
            }
            $store_member = Db::name("store_member")->where(['paypassword'=>md5($data['paypassword']),'id'=>$this->wx_user_id])->find();
            if(!$store_member){
                $this->error("交易密码错误");
            }
            Db::startTrans();
            //处理打款方
            $res[] = mlog($this->wx_user_id, 'ky_acc', -$data['num'], "给商户({$merchatInfo['num_id']}),转账acc减少{$data['num']}个", 'merchant_pdr_shou_reduce','',8,$merchatInfo['id']);
            //处理收款方
            $res[] = mlog($merchatInfo['id'], 'ky_acc', $data['num'], "收到商户({$this->wx_user['num_id']}),转账acc{$data['num']}个", 'merchant_pdr_shou', '', '8', $this->wx_user_id);
            if (check_arr($res)) {
                Db::commit();
                $this->success('转账成功');
            } else {
                Db::rollback();
                $this->error('网络故障,转账失败');
            }
        } else {
            $this->error('商户不存在');
        }
    }
    
    
      /*
     * 支付宝扫码接口
     * */
    public function alipaySaoma(){
        $url = $this->request->param('url');
        //$url = "HTTPS://QR.ALIPAY.COM/FKX08300Y15RHTAWH2PK6F?t=1591082385805";
        $h5Url = 'http://qr.liantu.com/api.php?text=' . urlencode($url);
        $userinfo = Db::name('store_member')->where(['id' => $this->wx_user_id])->find();
        $file = 'upload/alipay' . md5(time() . $userinfo['num_id']) . '.png';
        if (file_put_contents('./' . $file, file_get_contents($h5Url))) {
            $appRoot = request()->root(true); // 去掉参数 true 将获得相对地址
            $uriRoot = preg_match('/\.php$/', $appRoot) ? dirname($appRoot) : $appRoot;
            $file = $uriRoot . '/' . $file;
            response($file);
            $this->success(['qrcodeUrl' => $file,'acc_money'=>sysconf('pdr_money')]);
        }
    }
    
    /*
     * acc释放记录
     * */
    public function accReleasejl(){
        $acc_release = db::name('acc_release')->where(['uid'=>$this->wx_user_id])->page($this->page, $this->max_page_num)->order('id desc')->select();
        $this->success('',$acc_release);

    }
    
    /*
     * 通过支付宝转账
     * */
    public function alipayTrade(){
        $data = input('post.');
        $validate = Validate::make([
            'qrcodeUrl' => 'require',
            'num' => 'require',
            'total_price' => 'require',
            'paypassword'=> 'require',
        ], [
            'qrcodeUrl.require' => '支付宝二维码不能为空',
            'num.require' => '数量不能为空',
            'total_price.require' =>'金额不能为空',
            'paypassword.require' =>'支付密码不能为空',
        ]);
        //判断是否未支付宝的二维码
        $alipay_img = substr($data['qrcodeUrl'],11,6);
        if($alipay_img != "ALIPAY"){
            $this->error("你扫描的二维码不是支付宝，请更换为支付宝扫码");
        }
        $validate->check($data) || $this->error($validate->getError());

        $this->wx_user['ky_acc'] < $data['num'] && $this->error('acc数量不足');
        if(!is_numeric($data['paypassword']) || strlen($data['paypassword']) !='6'){
            $this->error("交易密码必须是6位数字");
        }
        $store_member = Db::name("store_member")->where(['paypassword'=>md5($data['paypassword']),'id'=>$this->wx_user_id])->find();
        if(!$store_member){
            $this->error("交易密码错误");
        }
        $alipay_url = $this->alipaySaoma1($alipay_img);
        Db::startTrans();
        //处理打款方
        $res[] = mlog($this->wx_user_id, 'ky_acc', -$data['num'], "acc扫码到支付宝，acc数量减少{$data['num']}个", 'merchant_pdr_shou_reduce','',9,$this->wx_user_id);
        if (check_arr($res)) {
            Db::commit();
            withdrawLog($this->wx_user_id, 2, $block_address='', $alipay_url,$data['num'], $tax = '', $acc_money =sysconf('pdr_money'),$data['total_price']);
            $this->success('支付成功，请等待审核');
        } else {
            Db::rollback();
            $this->error('网络故障,转账失败');
        }
    }
    
       /*
     * 支付宝扫码接口(提交记录的时候)
     * */
    public function alipaySaoma1($url){
        $h5Url = 'http://qr.liantu.com/api.php?text=' . urlencode($url);
        $userinfo = Db::name('store_member')->where(['id' => $this->wx_user_id])->find();
        $file = 'upload/alipay' . md5(time() . $userinfo['num_id']) . '.png';
        if (file_put_contents('./' . $file, file_get_contents($h5Url))) {
            $appRoot = request()->root(true); // 去掉参数 true 将获得相对地址
            $uriRoot = preg_match('/\.php$/', $appRoot) ? dirname($appRoot) : $appRoot;
            $file = $uriRoot . '/' . $file;
            response($file);
            return $file;
        }
    }
    
     /*
     * 支付宝提现记录
     * */
    public function alipayTradejl(){
        $pageNo = request()->param("page", 1);
        $pageSize = input('param.pageSize', 10);
        $start = ($pageNo - 1) * $pageSize;
        $orderList = Db::name('store_withdraw_money')->where(['uid' => $this->wx_user_id,'type'=>2])->order('id desc')->limit($start, $pageSize)->select();
        $this->success('',['orderlist'=>$orderList]);
    }
    
    
    /*
    * acc来源记录
    * */
    public function accSourcerecord()
    {
        $result = Db::name('bags_log')
            ->where(['uid' => $this->wx_user_id])
            ->where(['type'=>'dsf_acc'])
            /* ->where('extends', 'in', 'sell_pdr,buy_pdr,reduce_power,increase_power,seller_mining,buy_mining,time_out_transaction_money_return,time_out_transaction_money_arrival,cancel_dig_account,increase_pdr_collar,merchant_pdr_shou,merchant_pdr_shou_reduce')*/
            ->page($this->page, $this->max_page_num)
            ->order('id desc')
            ->select();
        $this->success('',$result);
    }
    
    //acc单价
    public function accPrice(){

        $this->success('', sysconf('pdr_money'));
    }
}
