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

namespace app\miniapp\controller;

use think\Controller;
use service\DataService;
use think\Db;
use think\Exception;
use think\validate;
use service\WechatServicew;
use GatewayClient\Gateway;

/**
 * 应用入口控制器
 * @author Anyon <zoujingli@qq.com>
 */
class Http extends Base
{

    const DATA_TEXT = 'text';
    const DATA_IMAGE = 'image';
    const DATA_SERVER = 'https://6topia.com/';

    public function bindUser()
    {
        $data = input('param.');
        $validate = Validate::make([
            'client_id' => 'require',
            'room_id' => 'require',
        ], [
            'client_id.require' => '当前用户的client_id',
            'room_id.require' => '当前用户的room_id',
        ]);
        $validate->check($data) || $this->error($validate->getError());
        Gateway::joinGroup($data['client_id'], $data['room_id']);
    }

    public function sendUserMessage()
    {
        $data = input('param.');
        $validate = Validate::make([
            'room_id' => 'require',
            'message' => 'require'
        ], [
            'room_id.require' => '房间号不存在！',
            'message.require' => '发送内容不能为空',
        ]);
        $validate->check($data) || $this->error($validate->getError());
        Db::startTrans();
        $res[] = $this->recordMessage($this->wx_user_id, $data['to_id'], $data['message'], self::DATA_IMAGE, $data['room_id']);
        if (check_arr($res)) {
            Db::commit();
        } else {
            Db::rollback();
            error('发送信息失败');
        }
        Gateway::sendToGroup($data['room_id'], json_encode(['type' => 'send', 'contentType' => 'text', 'content' => $data['message'], 'userInfo' => $this->wx_user]));
    }


    public function sendUserImg()
    {
        $data = input('param.');
        $validate = Validate::make([
            'room_id' => 'require',
            'to_id' => 'require',
        ], [
            'room_id.require' => '房间号不存在！',
            'to_id.require' => '接收人不存在！',
        ]);
        $validate->check($data) || error($validate->getError());
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if ($file) {
            $info = $file->move(ROOT_PATH . '/upload');
            if ($info) {
                Db::startTrans();
                $res[] = $this->recordMessage($this->wx_user_id, $data['to_id'], '/upload/' . $info->getSaveName(), self::DATA_TEXT, $data['room_id']);
                if (check_arr($res)) {
                    Db::commit();
                } else {
                    Db::rollback();
                    error('发送信息失败');
                }
                Gateway::sendToGroup($data['room_id'], json_encode(['type' => 'send', 'contentType' => 'image', 'content' => self::DATA_SERVER .'/upload/' . $info->getSaveName(), 'userInfo' => $this->wx_user]));
            } else {
                error($file->getError());

            }
        }
    }


    /*
     * @param $uid 用户id
     * @param $message 发送的消息
     * @param $message 发送的数据类型
     * @param $room_id 房间号码
     * */
    public function recordMessage( $uid, $toid, $message, $messageType, $room_id )
    {
        return Db::name("es_chat_record")->insertGetId(
            [
                'uid' => $uid,
                'toid' => $toid,
                'message' => $message,
                'type' => $messageType,
                'room_id' => $room_id
            ]
        );
    }


    public function fileUploader()
    {

    }


    public function getUserFlagBind()
    {
//        sm(Gateway::getAllClientSessions('200')); //获取放假里面的人
        sm(Gateway::sendToGroup('200', '我是组里面的人')); //获取放假里面的人
//        sm(Gateway::closeClient(Gateway::getClientIdByUid('200')[0],'end'));  //踢掉某人
    }


}
