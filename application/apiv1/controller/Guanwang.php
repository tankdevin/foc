<?php

namespace app\apiv1\controller;

use service\DataService;
use think\Error;
use think\Validate;
use think\Db;
use controller\BasicIndex;
use think\facade\Cache;

/**
 * 应用入口控制器
 * @author Anyon <zoujingli@qq.com>
 */
class guanwang extends BasicIndex
{
    //公告   faq 常见问题   fingerpost 新手指南  about 关于我们
    public function notice()
    {
        //$param = input('param.');
        $param['type'] = 'noticle';
        $res = Db::name('es_article')->where(['type' => $param['type'],'status'=>1])->page($this->page, $this->max_page_num)->order('id desc')->select();
        $this->success('',$res);
    }
    //公告   about 关于我们  posabout pos挖矿  poeabout挖矿
    public function about()
    {
        //$param = input('param.');
        $type = $this->request->param('type');
        $res = Db::name('es_article')->where(['type' =>$type,'status'=>1])->order('id desc')->select();
        $this->success('',$res);
    }

    /*
     * 帮助中心  如何造作 rhcz 如何挖矿 rhwk  充值手册 czsc  交易规则 jygz
     * */
    public function help()
    {
        $type = $this->request->param('type');
        $res = Db::name('es_article')->where(['type' =>$type,'status'=>1])->order('id desc')->find();
        $this->success('',$res);
    }

    /*
  * 新闻中心
  * */
    public function shouyeNew()
    {
        //$param = input('param.');
        $param['type'] = 'faq';
        $res = Db::name('es_article')->where(['type' => $param['type'],'status'=>1])->page($this->page, $this->max_page_num)->order('id desc')->select();
        $this->success('',$res);
    }

    /*
     * 国家
     * */
    public function country(){
        $res = Db::name('sys_country')->select();
        $this->success('',$res);
    }

    /*
     * 详情
     * */
    public function detail()
    {
        $param = input('param.');
        $res = Db::name('es_article')->where(['id' => $param['id']])->find();
        $this->success('',$res);
    }

    /*
     * 友情链接
     * */
    public function friendlink(){
       Cache::rm('lan_type');
       if(!Cache::get('lan_type','')){
            Cache::set('lan_type','zh-cn');
        }
        
        $this->success('',[
            'kfweixin'=>sysconf('kfweixin'),
            'facebook'=>sysconf('facebook'),
            'twitter'=>sysconf('twitter'),
            'youtube'=>sysconf('youtube'),
            'instagram'=>sysconf('instagram'),
        ]);
    }

    /*
     * 用户协议
     * */
    public function userpact(){
        $param['type'] = 'yhxy';
        $res = Db::name('es_article')->where(['type' => $param['type'],'status'=>1])->order('id desc')->find();
        $this->success($res);
    }
    
    /*
     * 获得语言种类(默认是英文)
     * */
    public function Languagetypes(){
        $type = $this->request->param('lan_type');
        if($type != Cache::get('lan_type','')){
            Cache::set('lan_type',$type);
        }
    }


}
