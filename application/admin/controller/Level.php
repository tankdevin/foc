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

/**earnings
 * 系统日志管理
 * Class Log
 * @package app\admin\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/02/15 18:12
 */
class Level extends BasicAdmin
{

    /**
     * 指定当前数据表
     * @var string
     */
    public $table = 'sys_level';
    public $table_address = 'sys_address';


    public function lists()
    {
        // 日志行为类别
        $actions = Db::name($this->table);
        $this->assign('actions', $actions);
        // 日志数据库对象
        list($this->title, $get) = ['等级列表', $this->request->get()];
        $db = Db::name($this->table)->order('id');
        foreach (['action', 'content', 'username'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->whereLike($key, "%{$get[$key]}%");
        }
        if (isset($get['create_at']) && $get['create_at'] !== '') {
            list($start, $end) = explode(' - ', $get['create_at']);
            $db->whereBetween('create_at', ["{$start} 00:00:00", "{$end} 23:59:59"]);
        }
        return parent::_list($db);
    }


    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success("等级删除成功!", '');
        }
        $this->error("等级删除失败, 请稍候再试!");
    }


    public function add()
    {

        return $this->_form($this->table, 'form', '', '', ['add_at' => time()]);
    }
    // 非POST请求, 获取数据并显示表单页面
    // POST请求, 数据自动存库
    //$this->table指的是数据库查询的对象
    //form 显示模板名字
    public function edit()
    {
        if($this->request->post('daishu')){
            $daishu = $this->request->post('daishu');
            $daishu_str = strstr($daishu, '-');
            if($daishu_str){
                $min_num = min(explode('-',$daishu));
                $max_num = max(explode('-',$daishu));
                for($i= $min_num;$i<=$max_num;$i++){
                    $arr_ds[]=$i;
                }
                $str_ds = implode(',',$arr_ds);
            }

        }else{
            $str_ds ='';
        }

        return $this->_form($this->table, 'form','','',['daishu_arr' => $str_ds]);
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
        if ($id) {
            $data = Db::name('store_member')->where('phone', $name)->find();
            if ($data) {
                $num1 = substr($id, -1, 1);
                $num2 = substr($id, -2, 1);
                if (!$num1 && !$num2) {
                    //市
                    $member_level = 6;
                } else {
                    //区
                    $member_level = 5;
                }
                $res = Db::name('store_member')->where('phone', $name)->update(['member_level' => $member_level, 'addr_id' => $id]);
                if ($res) {
                    $this->success('添加成功', '');
                } else {
                    $this->error('添加失败,请稍后重试');
                }
            } else {
                $this->error('用户不存在');
            }
        }
        return $this->_form($this->table_address, 'add_add');
    }

    public function del_add()
    {
        $id = input('param.id');
        $res = Db::name('store_member')->where('addr_id', $id)->update(['member_level' => 1, 'addr_id' => 0]);
        if ($res) {
            $this->success('解除成功', '');
        } else {
            $this->error('解除失败,请稍后重试');
        }
    }

    //幸运奖列表
    public function earnings()
    {
        $list = Db::name('sys_earnings')->select();
        return view('', ['list' => $list, 'title' => '实体店服务列表']);
    }

    //编辑静态收益
    public function editear()
    {
        $post = input('param.');
        if (request()->post()) {
            $tjnum = $post['tjnum'];
            $info = Db::name('sys_earnings')->where(['id' => $post['id']])->update(['luck_rate'=>$post['luck_rate'],'addtime' => time()]);
            if ($info) {
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }
        }
        $info = Db::name('sys_earnings')->where(['id' => $post['id']])->find();
        return view('', ['info' => $info]);
    }


    //推广团队算力列表
    public function hashrate()
    {
        $this->title = '推广团队算力列表';
        $list = Db::name('sys_hashrate');
        return parent::_list($list);

    }

    //编辑推广团队算力列表
    public function edithash()
    {
        $post = input('param.');
        if (request()->post()) {
            $info = Db::name('sys_hashrate')->where(['id' => $post['id']])->update(['hashrate' => $post['hashrate'], 'earnings' => $post['earnings']]);
            if ($info) {
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }
        }
        $info = Db::name('sys_hashrate')->where(['id' => $post['id']])->find();
        return view('', ['info' => $info]);
    }


    /*
     * 积分兑换分类
     * */
    public function scorefl()
    {
        $list = Db::name('sys_scorefl')->select();
        return view('', ['list' => $list, 'title' => '积分兑换分类']);
    }

    //编辑积分兑换分类
    public function editscorefl()
    {
        $post = input('param.');
        if (request()->post()) {
            $info = Db::name('sys_scorefl')->where(['id' => $post['id']])->update(['min_score' => $post['min_score'],'max_score'=>$post['max_score'],'multiple'=>$post['multiple']]);
            if ($info) {
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }
        }
        $info = Db::name('sys_scorefl')->where(['id' => $post['id']])->find();
        return view('', ['info' => $info]);
    }
}
