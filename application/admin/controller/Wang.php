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
use service\DataService;
use think\Db;

/**
 * 系统日志管理
 * Class Log
 * @package app\admin\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/02/15 18:12
 */
class Wang extends BasicAdmin
{

    /**
     * 指定当前数据表11
     * @var string
     */
    //public $table1 = 'wang_shengou';
    //public $table2 = 'jys_selllist';
    public function shengou()
    {
        // 日志行为类别
        $actions = Db::name("wang_shengou");
        $this->assign('actions', $actions);
        // 日志数据库对象
        list($this->title, $get) = ['申购', $this->request->get()];
        $db = Db::name("wang_shengou")->order('id desc');
        foreach (['action', 'content', 'username'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->whereLike($key, "%{$get[$key]}%");
        }
        if (isset($get['create_at']) && $get['create_at'] !== '') {
            list($start, $end) = explode(' - ', $get['create_at']);
            $db->whereBetween('create_at', ["{$start} 00:00:00", "{$end} 23:59:59"]);
        }
        if (isset($get['address']) && $get['address'] != '') {
            $user = Db::name('store_member')->where('address',$get['address'])->value('id');
            $where['uid'] = $user;
            $db->where($where);
        }
        return parent::_list($db);
    }

    public function kuangchi()
    {
        // 日志行为类别
        $actions = Db::name("wang_kuangchi");
        $this->assign('actions', $actions);
        // 日志数据库对象
        list($this->title, $get) = ['申购', $this->request->get()];
        $db = Db::name("wang_kuangchi")->order('id desc');
        foreach (['action', 'content', 'username'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->whereLike($key, "%{$get[$key]}%");
        }
        if (isset($get['create_at']) && $get['create_at'] !== '') {
            list($start, $end) = explode(' - ', $get['create_at']);
            $db->whereBetween('create_at', ["{$start} 00:00:00", "{$end} 23:59:59"]);
        }
        if (isset($get['address']) && $get['address'] != '') {
            $user = Db::name('store_member')->where('address',$get['address'])->value('id');
            $where['uid'] = $user;
            $db->where($where);
        }
        return parent::_list($db);
    }


}
