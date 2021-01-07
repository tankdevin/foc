<?php

namespace app\apiv1\controller;

use googleauthenticator;
use controller\BasicIndex;
use Exception;
use InvalidArgumentException;
use think\Db;
use think\Controller;

require_once('./vendor/googleauthenticator/phpgangsta/GoogleAuthenticator.php');

/**
 * 应用入口控制器
 */
class Googlelogin extends BasicIndex
{

    public function start()
    {

            $ga = new GoogleAuthenticator();
            $secret = $ga->createSecret();
            $contentUrl = 'http://' . $_SERVER['HTTP_HOST'].'/';
            //echo "安全密匙SecretKey: " . $secret . "\n\n";
            //第一个参数是"标识",第二个参数为"安全密匙SecretKey" 生成二维码信息
            if($secret){
                $qrCodeUrl = $ga->getQRCodeGoogleUrl("$contentUrl", $secret);
                return ['ggurl'=>$qrCodeUrl,'gg_secret'=>$secret];
            }
        //Google Charts接口 生成的二维码图片,方便手机端扫描绑定安全密匙SecretKey
    }

    /*
    谷歌验证
    */
    public function verification(){
        $data = $this->request->param();
        if($data['oneCode']){
            $ga = new GoogleAuthenticator();
            $secret = $this->wx_user['google_secret'];
            $oneCode = $data['oneCode'];
            $checkResult = $ga->verifyCode($secret, $oneCode, 2);
            if (!$checkResult) {
                //这里添加自定义逻辑
                $this->error("谷歌验证失败");
            }
        }else{
            $this->error("请输入谷歌验证码");
        }
    }


    /**
     * 谷歌验证内容
     */
    public function content(){
        $this->success('',['content'=>sysconf('contract')]);
    }




}