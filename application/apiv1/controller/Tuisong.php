<?php

namespace app\apiv1\controller;

use service\DataService;
use service\WechatServicew;
use controller\BasicIndex;
use think\Facade\Cache;
use think\Db;

class Tuisong extends BasicIndex
{
    protected $MiniApp;
    /*
  * 每天释放矿机（pos）
  * */
    public function index($new_data)
    {

        // 推送的url地址，使用自己的服务器地址
        $push_api_url = "http://173.214.170.78:5678";

        $post_data=$new_data;
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $push_api_url );
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_HEADER, 0 );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array("Expect:"));
        $return = curl_exec ( $ch );
        curl_close ( $ch );
        
        return $return;
    }



}