<?php

namespace app\miniapp\controller;

use service\DataService;
use service\WechatService;
use controller\BasicApi;
use Db;

class Common extends BasicApi
{

    //获取行业
    public function getIndustry(){
        $parent_industry = Db::table('system_menu_industry')->where(['status'=>1,'pid'=>0])->order('sort asc')->select();
        foreach($parent_industry as $k=>$v){
            $parent_industry[$k]['children'] = Db::table('system_menu_industry')->where(['status'=>1,'pid'=>$v['id']])->order('sort asc')->select();
        }
        response($parent_industry);
    }

    public function getSysAddress(){
        $area_parent_id = input('param.area_parent_id',0);
        $data = Db::table('sys_address')->where('area_parent_id',$area_parent_id)->select();
        foreach($data as $k=>&$v){
            $v['title'] = $v['area_name'];
        }
        response($data);
    }

    //获取预约时间
    public function getReservation(){
        $res = [];
        $res['morning'] = Db::table('sys_reservation')->where('type',1)->select();
        $res['afternoon'] = Db::table('sys_reservation')->where('type',2)->select();
        $res['night'] = Db::table('sys_reservation')->where('type',3)->select();
        response($res);
    }



    public function getCountry(){
        $data = Db::table('sys_country')->order('sort asc')->select();
        response($data);
    }

    public function fileImage()
    {
        $image = request()->post('image');
        if ($image) {
            $res = $this->saveBase64Image($image);
            return $this->success($res);
        } else {
            return $this->error('上传失败');
        }
    }

    function saveBase64Image( $base64_image_content )
    {

        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {

            //图片后缀
            $type = $result[2];
            if ($type == 'jpeg')
                $type = 'jpg';

            $ext = strtolower(sysconf('storage_local_exts'));

            if (is_string($ext)) {
                $ext = explode(',', $ext);
            }

            if (!in_array($type, $ext)) {
                $data['code'] = 0;
                $data['imgageName'] = '';
                $data['image_url'] = '';
                $data['type'] = '';
                $data['msg'] = '不允许上传的文件类型';
                return $data;
            }

            //保存位置--图片名
            $image_name = date('His') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT) . "." . $type;
            $iamge_dir = 'upload/' . date('Ymd');
            $image_url = $iamge_dir . $image_name;
            if (!is_dir(dirname('./' . $iamge_dir))) {
                mkdir(dirname('./' . $iamge_dir));
                chmod(dirname('./' . $iamge_dir), 0777);
            }

            //解码
            $decode = base64_decode(str_replace($result[1], '', $base64_image_content));
            if (file_put_contents('./' . $image_url, $decode)) {
                $appRoot = request()->root();  // 去掉参数 true 将获得相对地址
                $uriRoot = preg_match('/\.php$/', $appRoot) ? dirname($appRoot)   : $appRoot;
                $uriRoot = in_array($uriRoot, ['/', '\\']) ? '' : $uriRoot;

                $data['code'] = 1;
                $data['imageName'] = $image_name;
                $data['image_url'] = $uriRoot . '/' . $image_url;
                $data['type'] = $type;
                $data['msg'] = '保存成功！';
            } else {
                $data['code'] = 0;
                $data['imgageName'] = '';
                $data['image_url'] = '';
                $data['type'] = '';
                $data['msg'] = '图片保存失败！';
            }
        } else {
            $data['code'] = 0;
            $data['imgageName'] = '';
            $data['image_url'] = '';
            $data['type'] = '';
            $data['msg'] = 'base64图片格式有误！';
        }
        return $data;
    }


    public function fileUploader()
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if ($file) {
            $info = $file->move(ROOT_PATH . '/upload');
            if ($info) {
                return $this->success('/upload/' . $info->getSaveName());
            } else {
                return $this->error($file->getError());

            }
        }
    }
    //获取地址
    public function getAllAddress(){
//        Db::name()
    }




}
