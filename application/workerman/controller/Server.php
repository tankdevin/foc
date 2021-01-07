<?php

namespace app\workerman\controller;
use workerman\Worker;
use service\DataService;
use service\WechatServicew;
use controller\BasicIndex;
use think\Facade\Cache;
use think\Db;

class Server extends BasicIndex
{
	
	public function __construct()
	{
        // 创建一个Worker监听2345端口，使用http协议通讯
        $http_worker = new Worker("http://0.0.0.0:2345");

// 启动4个进程对外提供服务
        $http_worker->count = 4;

// 接收到浏览器发送的数据时回复hello world给浏览器
        $http_worker->onMessage = function($connection, $data)
        {
            // $data数组格式，里面有uid，表示向那个uid的页面推送数据
            $data = json_decode($buffer, true);
            $uid = $data['uid'];
            var_dump($uid);
            // 通过workerman，向uid的页面推送数据
            $ret = sendMessageByUid($uid, $buffer);
            // 返回推送结果
            $connection->send($ret ? 'ok' : 'fail');

        };
        // ## 执行监听 ##
        $inner_text_worker->listen();


// 运行worker
        Worker::runAll();
	}



}
