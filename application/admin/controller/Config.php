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

namespace app\admin\controller;

use controller\BasicAdmin;
use service\LogService;
use service\WechatService;
use think\Db;

/**
 * 后台参数配置控制器
 * Class Config
 * @package app\admin\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/02/15 18:05
 */
class Config extends BasicAdmin
{

    /**
     * 当前默认数据模型
     * @var string
     */
    public $table = 'SystemConfig';

    /**
     * 当前页面标题
     * @var string
     */
    public $title = '系统参数配置';

    /**
     * 显示系统常规配置
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        if ($this->request->isGet()) {
            return $this->fetch('', ['title' => $this->title]);
        }
        if ($this->request->isPost()) {
            foreach ($this->request->post() as $key => $vo) {
                  //清空数据库(开启)
                if($key== 'is_clearSql' && $vo=='-1'){

                    $this->clearSql();
                    sysconf('is_clearSql', 1);
                }else{
                    sysconf($key, $vo);
                }
            }
            LogService::write('系统管理', '系统参数配置成功');
            $this->success('系统参数配置成功！', '');
        }
    }

    /**
     * 文件存储配置
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function file()
    {
        $this->title = '文件存储配置';
        return $this->index();
    }
    
       /*
     * 清空数据库
     * is_clearSql  是-1  否1
     * */
    public function clearSql(){
        Db::execute('truncate table bags_log');
        Db::execute('truncate table financial_order');
        Db::execute('truncate table jys_buylist');
        Db::execute('truncate table jys_selllist');
        Db::execute('truncate table pot_daysy');
        Db::execute('truncate table store_award');

        //  $res = db::name('store_member')->where('id','>','1')->delete();
        //  if($res){
        //      $data = ['level' => '1', 'account_money' => '0','account_score'=>'0','tj_num'=>'0','xy_tj_num'=>0,'td_num'=>0,'team_performance'=>0,'total_performance'=>0,'wallet_one'=>0,'wallet_two'=>0,'wallet_three'=>0,'wallet_four'=>0,'wallet_five'=>0,'wallet_six'=>0,'is_yx'=>0];
        //      Db::name('store_member')
        //          ->where('id', 1)
        //          ->data($data)
        //          ->update();

        //  }
        Db::execute('truncate table store_member_payment');
        Db::execute('truncate store_order_c2c_ts');
        Db::execute('truncate store_withdraw_money');
        Db::execute('truncate ty_buylist');
        Db::execute('truncate ty_match');
        Db::execute('truncate ty_selllist');
        Db::execute('truncate system_log');
//        Db::execute('truncate store_order_c2c_ts');//清空建议表
//        Db::execute('truncate store_withdraw_money');//链上充值记录
        return true;
    }
    
}
