<?php
use \Workerman\Worker;
use \Workerman\Lib\Timer;
require __DIR__ . '/thinkphp/base.php';
require_once __DIR__ . '/vendor/autoload.php';
 
$task = new Worker();
// 开启多少个进程运行定时任务，注意多进程并发问题
$task->count = 1;
$task->onWorkerStart = function($task)
{
    // 每2.5秒执行一次
//     $time_interval = 3;
//     Timer::add($time_interval, function()
//     {
//         file_get_contents("http://www.foc.tk/index.php/apiv1/getkline/handle");
//     });
//     $five_interval = 10;
//     Timer::add($five_interval, function()
//     {
//         file_get_contents("http://www.foc.tk/index.php/apiv1/getkline/five_min?min=6");
//         file_get_contents("http://www.foc.tk/index.php/apiv1/getkline/five_min?min=1");
//         file_get_contents("http://www.foc.tk/index.php/apiv1/getkline/five_min?min=7");
//         file_get_contents("http://www.foc.tk/index.php/apiv1/getkline/five_min?min=2");
//         file_get_contents("http://www.foc.tk/index.php/apiv1/getkline/five_min?min=4");
//         file_get_contents("http://www.foc.tk/index.php/apiv1/getkline/five_min?min=8");
//         file_get_contents("http://www.foc.tk/index.php/apiv1/getkline/five_min?min=9");
//     });
//     $my_interval = 5;
//     Timer::add($my_interval, function()
//     {
//         file_get_contents("http://www.foc.tk/index.php/apiv1/robot/handle");
//     });
    $jy_interval = 5;
    Timer::add($jy_interval, function()
    {
        file_get_contents("http://www.foc.tk/index.php/apiv1/robot/jiaoyiorderlist");
    });
};
 
// 运行worker
Worker::runAll();