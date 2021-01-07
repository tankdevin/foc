<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2019 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://demo.thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | gitee 代码仓库：https://gitee.com/zoujingli/ThinkAdmin
// | github 代码仓库：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

namespace app\apiv1\controller;

use library\Controller;

/**
 * 应用入口
 * Class Index
 * @package app\index\controller
 */
class Update extends Base
{

    /**
     * 上传身份证认证
     * @return [type] [description]
     */
    public function merber_upcard()
    {
        $language =  language($this->lang,'common','fileImage');
        $image = request()->post('image');
        if ($image) {
            $res = $this->base64Image($image,'cardimagse');
        } else {
            $this->error($language['scsb']);
        }
        if($res['code'] == 1){
            $this->success($language['sccg'], ['imgname'=>$res['imageName'],'imgurl'=>$res['image_url']]);
        }else{
            $this->error($res['msg']);
        }
        $this->error($language['scsb']);
    }

    /**
     * 上传企业
     * @return [type] [description]
     */
    public function merber_upqiye()
    {
        $language =  language($this->lang,'common','fileImage');
        $image = request()->post('image');
        if ($image) {
            $res = $this->base64Image($image,'qiyeimagse');
        } else {
            $this->error($language['scsb']);
        }
        if($res['code'] == 1){
            $this->success($language['sccg'], ['imgname'=>$res['imageName'],'imgurl'=>$res['image_url']]);
        }else{
            $this->error($res['msg']);
        }
        $this->error($language['scsb']);
    }
    
    /**
     * 上传工单图片
     * @return [type] [description]
     */
    public function merber_upwork()
    {
        $language =  language($this->lang,'common','fileImage');
        $image = request()->post('image');
        if ($image) {
            $res = $this->base64Image($image,'workimagse');
        } else {
            $this->error($language['scsb']);
        }
        if($res['code'] == 1){
            $this->success($language['sccg'], ['imgname'=>$res['imageName'],'imgurl'=>$res['image_url']]);
        }else{
            $this->error($res['msg']);
        }
        $this->error($language['scsb']);
    }

    function base64Image($base64_image_content,$upad = 'userimagse')
    {
        $language =  language($this->lang,'common','saveBase64Image');
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
            //图片后缀
            $type = $result[2];
            if ($type == 'jpeg')$type = 'jpg';
            $ext = strtolower('png,jpg,rar,doc,icon,mp4,jpeg');
            if (is_string($ext)) {$ext = explode(',', $ext);}
            if (!in_array($type, $ext)) {
                $data['code'] = 0;
                $data['imgageName'] = '';
                $data['image_url'] = '';
                $data['type'] = '';
                $data['msg'] = $language['byxsc'];
                return $data;
            }
            //保存位置--图片名
            $image_name = date('His') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT) . "." . $type;
            $iamge_dir = 'upload/idcard/'.$upad . date('Ymd');
            $image_url = $iamge_dir . $image_name;
            if (!is_dir(dirname('./' . $iamge_dir))) {
                mkdir(dirname('./' . $iamge_dir));
                chmod(dirname('./' . $iamge_dir), 0777);
            }
            
            //解码
            $decode = base64_decode(str_replace($result[1], '', $base64_image_content));
            if (file_put_contents('./' . $image_url, $decode)) {
                $appRoot = request()->root(true);  // 去掉参数 true 将获得相对地址
                $uriRoot = preg_match('/\.php$/', $appRoot) ? dirname($appRoot) : $appRoot;
                $uriRoot = in_array($uriRoot, ['/', '\\']) ? '' : $uriRoot;
                $data['code'] = 1;
                $data['imageName'] = $image_name;
                $data['image_url'] = $uriRoot . '/' . $image_url;
                $data['type'] = $type;
                $data['msg'] = $language['bccg'];
            } else {
                $data['code'] = 0;
                $data['imgageName'] = '';
                $data['image_url'] = '';
                $data['type'] = '';
                $data['msg'] = $language['bcsb'];
            }
        } else {
            $data['code'] = 0;
            $data['imgageName'] = '';
            $data['image_url'] = '';
            $data['type'] = '';
            $data['msg'] = $language['gscw'];
        }
        return $data;
    }
}
