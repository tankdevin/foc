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
class Block extends Base
{
        /*
         * 区块绑定(应用的是提现)
         * */
        public function blockBinding(){
            if($this->request->param('payment')){
                $payment = $this->request->param('payment');
                $mark = $this->request->param('mark');
                $res = Db::name('store_member_payment')->where(['type' =>3,'payment'=>$payment])->find();
                if($res){
                   $this->error("请勿添加相同的区块地址");
                }
                //查询添加的区块地址是否为第一个
                $is_exit = Db::name('store_member_payment')->where(['type' =>3,'uid'=>$this->wx_user_id])->find();
                if($is_exit){
                    $state = 0;
                }else{
                    $state = 1;
                }
                $add_list = Db::name('store_member_payment')->insert([
                    'uid' => $this->wx_user_id,
                    'type' => 3,
                    'payment' => $payment,
                    'mark'=>$mark,
                    'addtime' => time(),
                    'state'=>$state
                ]);
                if($add_list){
                    $this->success('区块绑定成功');
                }else{
                    $this->error('区块绑定失败');
                }
            }
            $qukuai = Db::name('store_member_payment')->where(['type' =>3,'uid'=>$this->wx_user_id])->select();
            $this->success($qukuai);
        }
        /*
         * 删除区块绑定
         * */
        public function delBlock(){
            $id = $this->request->param('id');
            $order = Db::name('store_member_payment')->where(['type' =>3,'uid'=>$this->wx_user_id,'id'=>$id])->find();
            if(!$order){
                $this->error("此区块地址无效");
            }
            $res = Db::name('store_member_payment')->where(['type' =>3,'uid'=>$this->wx_user_id,'id'=>$id])->delete();
            if($res){
                //如果删除的是选中的话，默认第一个是选中的
                if($order['state'] == 1){
                    $is_cz = Db::name('store_member_payment')->where(['type' =>3,'uid'=>$this->wx_user_id])->order('id asc')->find();
                    if($is_cz){
                        Db::name('store_member_payment')->where(['id'=>$is_cz['id']])->setField('state',1);
                    }
                }
                $this->success("删除成功");
            }else{
                $this->error("删除失败");
            }
        }
        /*
         *指定使用区块
         *  */
        public function  appointBlock(){

            $id = input('id');
            $order = Db::name('store_member_payment')->where(['type' =>3,'uid'=>$this->wx_user_id,'id'=>$id])->find();
            if(!$order){
                $this->error("此区块地址无效");
            }
            $res = Db::name('store_member_payment')->where(['type' =>3,'uid'=>$this->wx_user_id,'id'=>$id,'state'=>0])->setField('state',1);
            if($res){
                Db::name('store_member_payment')->where(['type' =>3,'uid'=>$this->wx_user_id])->where('id', '<>', $id)
                    ->setField('state',0);
                $this->success("选中成功");
            }else{
                $this->error("选中失败");
            }
        }

        /*
         * 收款码
         * */
        public function blockReceiptCode()
        {

            //如果存在的话，就能进行生成
            $userinfo = Db::name('store_member')->where(['id' => $this->wx_user_id])->find();

            $contentUrl = 'http://'.$_SERVER['HTTP_HOST'].'/index.php/apiv1/trade/otherUsdtnumid?num_id='. $userinfo['num_id'].'&type=2';
            //$contentUrl = 'http://192.168.0.190:8080/index.php/apiv1/trade/otherUsdtnumid?num_id=' . $userinfo['num_id'].'&type=1';
            //  $h5Url2 = "http://h5ip.cn/index/api?format=json&url=" . urlencode($contentUrl);
            $h5Url = 'http://qr.liantu.com/api.php?text=' . urlencode($contentUrl);
            if (empty($userinfo['qukuai_img'])) {
                $file = 'upload/usdt/' . md5(time() . $userinfo['num_id']) . '.png';
                if (file_put_contents('./' . $file, file_get_contents($h5Url))) {
                    $appRoot = request()->root(true); // 去掉参数 true 将获得相对地址
                    $uriRoot = preg_match('/\.php$/', $appRoot) ? dirname($appRoot) : $appRoot;
                    $file = $uriRoot . '/' . $file;
                    Db::name("store_member")->where(['id' => $this->wx_user_id])->update([
                        'qukuai_img' => $file,
                    ]);
                    response($file);
                    $this->success('',['qrcodeUrl' => $file, 'linkUrl' => $contentUrl]);
                }
            }

            $this->success('',['qrcodeUrl' => $userinfo['qukuai_img'], 'linkUrl' => $contentUrl]);

        }

}
