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

namespace app\index\controller;


use think\App;
use think\Db;
use think\Controller;
use controller\BasicWechat;
use service\WechatService;

class Base extends Controller
{
    protected $wechat_info;
    protected $wechat_fans;
    protected $uid;

    protected $memberService;

    public function __construct( App $app = null )
    {
        parent::__construct($app);
        /*
         * 推广码过来的
         * */
        $first_leader = input('param.first_leader', 0);
//        $first_leader = mb_substr($first_leader,strpos($first_leader,'R')+1);
        if ($first_leader > 0) {
            $leader_info = Db::name('store_member')->where('phone',$first_leader)->find();
            if(isset($leader_info['phone'])){
                $this->assign('first_tuiguang_leader',$leader_info['phone']);
            }
        }
    }


}
