<?php

namespace app\admin\controller;

use controller\BasicAdmin;
use service\LogService;
use service\WechatService;

use think\DB;

/**
 * 后台参数配置控制器
 * Class Config
 * @package app\admin\controller
 */
class Article extends BasicAdmin
{

    //执行文章管理的操作
    public function index()
    {
        $this->title = '文章管理';
        $get = $this->request->get();
        $db = Db::name("es_article");
        $where = array();
        if (isset($get['status']) && $get['status'] != '') {
            $where['status'] = $get['status'];
            $db->where($where);
        }
        if (isset($get['type']) && $get['type'] != '') {
            $where['type'] = $get['type'];
            $db->where($where);
        }
        if (isset($get['field']) && $get['field'] != '') {
            if ($get['field'] == 'username') {
                $where['adminid'] = Db::name('SystemUser')->where(array('username' => $get['name']))->value('id');
            } else {
                $where[$get['field']] = $get['name'];
            }
            $db->where($where);
        }
        $db->order('id desc');
        return parent::_list($db);
    }

    //执行文章编辑的操作
    public function edit()
    {
        $id = input('id');
        if (request()->isPost()) {
            $where = array();
            $data = request()->post();
            //执行判断标签的操作
            if ($id) {
                $text = '修改';
                $where['content'] = $data['content'];
                $where['title'] = $data['title'];
                $where['status'] = $data['status'];
                $where['type'] = $data['type'];
                $where['endtime'] = time();
                $where['img'] = $data['img'];
                $where['adminid'] = session('user')['id'];
                $res = Db::name("es_article")->where(array(
                    'id' => $id
                ))->update($where);
                //执行入操作员操作日志表
                $content = '文章编辑    操作ID: ' . $id;
                LogService::write('系统管理', $content);
            } else {
                $text = '添加';
                $where['content'] = $data['content'];
                $where['title'] = $data['title'];
                $where['addtime'] = time();
                $where['status'] = $data['status'];
                $where['type'] = $data['type'];
                $where['img'] = $data['img'];
                $where['adminid'] = session('user')['id'];
                $res = Db::name("es_article")->insertGetId($where);
                //执行入操作员操作日志表
                $content = '文章添加    操作ID: ' . $res;
                LogService::write('系统管理', $content);
            }
            if ($res) {
                $this->success("{$text}成功", '');
            } else {
                $this->error("{$text}失败", '');
            }
        }
        $info = [];
        if ($id) {
            $info = Db::name("es_article")->where(array(
                'id' => $id
            ))->find();
        }
        $this->assign('info', $info);
        return view();
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
                    $res = Db::name('es_article')->where($where)->delete();
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

    //执行广告管理的操作
    public function adver()
    {
        $this->title = '广告管理';
        $get = $this->request->get();
        $db = Db::name("Adver");
        $where = array();
        if (isset($get['status']) && $get['status'] != '') {
            $where['status'] = $get['status'];
            $db->where($where);
        }
        if (isset($get['type']) && $get['type'] != '') {
            $where['type'] = $get['type'];
            $db->where($where);
        }
        if (isset($get['field']) && $get['field'] != '') {
            if ($get['field'] == 'username') {
                $where['adminid'] = Db::name('SystemUser')->where(array('username' => $get['name']))->value('id');
            } else {
                $where[$get['field']] = $get['name'];
            }
            $db->where($where);
        }
        $db->order('id desc');
        return parent::_list($db);
    }

    //执行广告管理图片的操作
    public function adveredit()
    {
        $id = input('id');
        if (request()->isPost()) {
            $where = array();
            $data = request()->post();
            //执行判断标签的操作
            if ($id) {
                $text = '修改';
                $where['title'] = $data['title'];
                $where['status'] = $data['status'];
                $where['endtime'] = time();
                $where['url'] = $data['url'];
                $where['img'] = $data['img'];
                $where['adminid'] = session('user')['id'];
                $res = Db::name("Adver")->where(array(
                    'id' => $id
                ))->update($where);
                //执行入操作员操作日志表
                $content = '广告编辑    操作ID: ' . $id;
                LogService::write('系统管理', $content);
            } else {
                $text = '添加';
                $where['title'] = $data['title'];
                $where['addtime'] = time();
                $where['status'] = $data['status'];
                $where['url'] = $data['url'];
                $where['img'] = $data['img'];
                $where['adminid'] = session('user')['id'];
                $res = Db::name("Adver")->insertGetId($where);
                //执行入操作员操作日志表
                $content = '广告添加    操作ID: ' . $res;
                LogService::write('系统管理', $content);
            }
            if ($res) {
                $this->success("{$text}成功", '');
            } else {
                $this->error("{$text}失败", '');
            }
        }
        $info = [];
        if ($id) {
            $info = Db::name("Adver")->where(array(
                'id' => $id
            ))->find();
        }
        $this->assign('info', $info);
        return view();
    }

    //执行广告管理状态值的操作
    public function adverstatus()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $id = $data['id'];
            if (empty($id)) {
                $this->error('你好,请选择要操作的数据');
            }
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
                    $res = Db::name('Adver')->where($where)->delete();
                    if ($res) {
                        $this->success('删除成功', '');
                    } else {
                        $this->error('删除失败');
                    }
                    break;
                default:
                    $this->error('参数非法');
            }
            if (Db::name('Adver')->where($where)->update($data)) {
                $this->success('操作成功', '');
            } else {
                $this->error('操作失败');
            }
        }
    }

    //执行留言管理的操作
    public function message()
    {
        $this->title = '留言管理';
        $get = $this->request->get();
        $db = Db::name("message");
        $where = array();
        if (isset($get['hstatus']) && $get['hstatus'] != '') {
            $where['hstatus'] = $get['hstatus'];
            $db->where($where);
        }
        if (isset($get['type']) && $get['type'] != '') {
            $where['type'] = $get['type'];
            $db->where($where);
        }
        if (isset($get['field']) && $get['field'] != '') {
            if ($get['field'] == 'username') {
                $where['adminid'] = Db::name('SystemUser')->where(array('username' => $get['name']))->value('id');
            } elseif ($get['field'] == 'truename') {
                $where['userid'] = Db::name('User')->where(array('username' => $get['name']))->value('id');
            } else {
                $where[$get['field']] = $get['name'];
            }
            $db->where($where);
        }
        $db->order('id desc');
        return parent::_list($db);

    }

    //执行留言回复的操作
    public function messagedit()
    {
        $id = input('id');
        if (request()->isPost()) {
            $where = array();
            $data = request()->post();
            //执行判断标签的操作
            $text = '回复';
            $where['adminnote'] = $data['adminnote'];
            $where['hftime'] = time();
            $where['hstatus'] = 1;
            $where['adminid'] = session('user')['id'];
            $res = Db::name("Message")->where(array('id' => $id))->update($where);
            //执行入操作员操作日志表
            $content = '留言回复    操作ID: ' . $id;
            LogService::write('系统管理', $content);
            if ($res) {
                $this->success("{$text}成功", '');
            } else {
                $this->error("{$text}失败", '');
            }
        }
        $info = [];
        if ($id) {
            $info = Db::name("Message")->where(array('id' => $id))->find();
        }
        $this->assign('info', $info);
        return view();
    }

    //执行留言详情的操作
    public function messageinfo()
    {
        $id = input('id');
        $info = '';
        if ($id) {
            $info = Db::name("Message")->where(array('id' => $id))->find();
        }
        $this->assign('info', $info);
        return view();
    }
}
