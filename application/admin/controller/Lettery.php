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
class Lettery extends BasicAdmin
{

    /**
     * 指定当前数据表
     * @var string
     */
    public $table = 'sys_lettery';


    public function index()
    {
        $actions = Db::name($this->table);
        $this->assign('actions', $actions);
        // 日志数据库对象
        list($this->title, $get) = ['抽奖设置', $this->request->get()];
        $db = Db::name($this->table)->order('id');
        if (isset($get['create_at']) && $get['create_at'] !== '') {
            list($start, $end) = explode(' - ', $get['create_at']);
            $db->whereBetween('create_at', ["{$start} 00:00:00", "{$end} 23:59:59"]);
        }

        return parent::_list($db);
    }


    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success("抽奖设置删除成功!", '');
        }
        $this->error("抽奖设置删除失败, 请稍候再试!");
    }


    public function add()
    {
        return $this->_form($this->table, 'form', '', '');
    }

    public function edit()
    {
        return $this->_form($this->table, 'form');
    }


    //

    public function address()
    {
        $id = input('param.id', '0');
        $name = input('param.area_name', '');
        if ($name) {
            $list = Db::name($this->table_address)->where('area_name', 'like', "%" . $name . "%")->select();
        } else {
            $list = Db::name($this->table_address)->where('area_parent_id', $id)->select();
        }
        foreach ($list as $k => $v) {
            $name = Db::table('store_member')->where('addr_id', $v['id'])->find();
            if ($name) {
                $list[$k]['uid'] = $name['id'];
                $list[$k]['name'] = $name['phone'];
                $list[$k]['status'] = 1;
            } else {
                $list[$k]['status'] = 0;
            }
        }
        $this->assign('list', $list);
        return view();
    }

    public function add_add()
    {
        $name = input('post.phone');
        $id = input('post.id');
        if($id){
            $data = Db::name('store_member')->where('phone',$name)->find();
            if ($data){
                $num1 = substr($id,-1,1);
                $num2 = substr($id,-2,1);
                if (!$num1 && !$num2){
                    //市
                    $member_level = 6;
                }else{
                    //区
                    $member_level = 5;
                }
                $res = Db::name('store_member')->where('phone',$name)->update(['member_level'=>$member_level,'addr_id'=>$id]);
                if ($res) {
                    $this->success('添加成功','');
                } else {
                    $this->error('添加失败,请稍后重试');
                }
            }else{
                $this->error('用户不存在');
            }
        }
        return $this->_form($this->table_address, 'add_add');
    }

    public function del_add()
    {
        $id = input('param.id');
        $res = Db::name('store_member')->where('addr_id',$id)->update(['member_level'=>1,'addr_id'=>0]);
        if ($res) {
            $this->success('解除成功','');
        } else {
            $this->error('解除失败,请稍后重试');
        }
    }


    //执行文章状态的操作
    public function articlestatus()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $id = $data['id'];
            if (empty($id))
                $this->error('你好,请选择要操作的数据');

            $where['id'] = array('in', $id);
            $method = $data['field'];
            switch (strtolower($method)) {
                case 'resume':
                    $data = array('status' => 0);
                    break;
                case  'forbid':
                    $data = array('status' => 1);
                    break;
                case  'delete':
                    $res = Db::name($this->table)->where('id','in',$id)->delete();
                    if ($res) {
                        $this->success('删除成功', '');
                    } else {
                        $this->error('删除失败');
                    }
                    break;
                default:
                    $this->error('参数非法');
            }
            if (Db::name('es_article')->where($where)->update($data)) {
                $this->success('操作成功', '');
            } else {
                $this->error('操作失败');
            }
        }
    }
}
