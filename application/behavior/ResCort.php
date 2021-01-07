<?php
namespace app\behavior;
use think\Db;
class ResCort
{
    //新用户生成后,关联上下级关系
    public function run( $param = [] )
    {

        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:*');
        header('Access-Control-Allow-Headers:*');
        header('Access-Control-Allow-Credentials:true');
        if (\think\facade\Request::instance()->isOptions()) {
            echo '';
            die();
        }
    }
}