<?php

namespace controller;

use service\ToolsService;
use think\Db;

/**
 * 基础接口类1
 * Class BasicApi
 * @package controller
 */
class BasicIndex extends BasicApi
{
    protected $id;
    protected $lang;

    protected $user;
    protected $commonService;
    public function __construct()
    {
        parent::__construct();
        $this->commonService = getMemberFree();
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET, POST, OPTIONS, DELETE");
        header("Access-Control-Allow-Headers:DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type, Accept-Language, Origin, Accept-Encoding");
        $lang = request()->header('lang');
        $this->lang = $lang??'zh-cn';


//        if ($this->request->isGet())

//            error('404 Not Found');
//        $not_allow_controller = [
//            "login",
//            "index"
//        ];
//        if (!in_array(strtolower($this->request->controller()), $not_allow_controller))
//            if (empty($token = $this->request->post('token', request()->header('token', '')))) {
//                error('token is required param', [], 999);
//            } else {
//                $user = Db::name('store_member')->where([
//                    'wx_token' => $token
//                ])->find();

//                if (!$user)
//                    error("user don't exists", [], 999);
//                if ($user['status'] == 2) {
//                    error("账户异常,请联系管理员", [], 999);
//                }
//                $this->id = $user['id'];
//                $this->user = $user;
//            }
    }
}