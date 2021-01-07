<?php

namespace app\miniapp\controller;

use service\DataService;
use service\WechatServicew;
use controller\BasicApi;
use Db;
class Login extends BasicApi
{
    public $wxuser;
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */

    protected $MiniApp;

    public function __construct()
    {

        parent::__construct();
        $this->MiniApp = new \Smallsha\Classes\MiniApp(config('miniapp.appid'), config('miniapp.app_secret'));
    }


    public function index()
    {
        $data = input('post.');
        $rule = [
            'code'  => 'require',
            'iv'   => 'require',
            'encryptedData' => 'require',
            'member_level' => 'require'
        ];
        $msg = [
            'code.require' => 'code必传',
            'iv.require'     => 'iv必传',
            'encryptedData.require'   => 'encryptedData必传',
            'member_level.require'   => 'member_level必传',
        ];
        $validate = new \think\Validate($rule, $msg);
        if(!$validate->check($data)){
            return $this->error($validate->getError());
        }
        extract($data);
        $this->wxuser = WechatServicew::WeMiniCrypt()->userInfo($code,$iv,$encryptedData);
        if (isset($this->wxuser['session_key'])) {
            $token = $this->MiniApp->getToken();
            //写入用户
            $data = [
                'openid' => $this->wxuser['openid'],
                'sessionkey' => $this->wxuser['session_key'],
                'wx_token' => $token,
                'headimg' => $this->wxuser['avatarUrl'],
                'nickname' => $this->wxuser['nickName'],
                'sex' => $this->wxuser['gender'],
                'member_level' => $data['member_level']
            ];

            if (DataService::wechatSave('StoreMember', $data, 'id', ['openid' => $this->wxuser['openid']])) {
                return $this->success('授权成功',['token'=>$token]);
            } else {
                return $this->error('授权失败');
            }

        } else {
            return $this->error('授权失败');
        }
    }


    public function phoneLogin(){
        $data = input('post.');
        $rule = [
            'phone'  => 'require',
            'yzm'   => 'require',
            'member_level' => 'require'
        ];
        $msg = [
            'phone.require' => 'phone必传',
            'yzm.require'     => 'yzm必传',
            'member_level.require'   => 'member_level必传',
        ];
        $validate = new \think\Validate($rule, $msg);
        if(!$validate->check($data)){
            return $this->error($validate->getError());
        }
        $flag_phone = Db::table('store_member')->where(['phone' => $data['phone']])->find();
        $flag_phone && $this->error('账号已存在');
        //验证短信
        $data['yzm'] != $this->getSmsCode('register_code',$data['phone']) && $this->error('短信验证码错误:(');
        $token = $this->MiniApp->getToken();
        $arr = [
            'phone'=>$data['phone'],
            'wx_token' => $token,
            'member_level' => $data['member_level'],
        ];
        if (DataService::save('StoreMember', $arr, 'id', ['phone' => $data['phone']])) {
            return $this->success('登录成功',['token'=>$token]);
        } else {
            return $this->error('登录失败');
        }
    }



    //发送验证码
    public function sendMobileMessage()
    {
        $code = rand(10000,99999);
        $data = input('param.');
        $rule = [
            'phone'  => 'require|mobile'
        ];
        $msg = [
            'phone.require' => '手机号不能为空:(',
            'phone.mobile' => '手机格式不正确:(',
        ];
        $validate = new \think\Validate($rule, $msg);
        if(!$validate->check($data)){
            return $this->error($validate->getError());
        }
        $flag_phone = Db::table('store_member')->where(['phone' => $data['phone']])->find();
        $flag_phone && $this->error('账号已存在');
        $this->setSmsCode('register_code',$code,$data['phone']);
        $result = sendMobileMessage($data['phone'], ['code' => $code], '496845');
        return $this->success($result);
    }

    /*
     * $type 类型
     * $phone 手机号
     * */
    public function getSmsCode($type,$phone){
        return \Cache::pull($type.getips().$phone);
    }

    /*
     * @param $type 类型
     * @param $code 验证码
     * @phone $phone 手机号
     * */
    public function setSmsCode($type,$code,$phone){
        return \Cache::set($type.getips().$phone,$code);
    }


    public function test(){

    }
}
