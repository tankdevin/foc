<?php
namespace app\behavior;

use think\Db;

class AssocUserParent
{
    public $userModel;
    static $level_set = ['second_leader','third_leader','four_leader','five_leader'];
    //新用户生成后,关联上下级关系1
    public function run( $param = [] )
    {
        $user_id = $param['user_id'];
        $parent_id = $param['parent_id'];
        if($parent_id > 0 && isset($user_id)){
            $this->userModel = new \app\common\model\User();
            $userinfo = $this->userModel->field('nickname,id,first_leader')->where(['id'=>$user_id])->find();
            $parentinfo = $this->userModel->field('first_leader,second_leader,third_leader,four_leader,five_leader,all_leader')->where(['id'=>$parent_id])->find()->toArray();
            if(empty($parentinfo)) return false;  //上级用户信息不存在
            if($userinfo['first_leader'] == 0){
                $res = [TRUE];
                $this->userModel->startTrans();
                $i = 0;
                foreach(array_slice($parentinfo,0,4) as $k => $v){
                    $temp = self::$level_set[$i];
                    $i++;
                    $res[$temp] = $this->userModel->save([$temp => $v],['id'=>$user_id]);  //插入后五个上级
                }
                $all_leader = array_pop($parentinfo);
                if(strlen($all_leader) > 0){
                    $all_leader = $all_leader.','.$parent_id;
                }else{
                    $all_leader = $parent_id;
                }
                $res['first_leader'] = $this->userModel->isUpdate()->save(['first_leader'=>$parent_id],['id'=>$user_id]);  //第一个上级
                $res['all_leader'] = $this->userModel->isUpdate()->save(['all_leader'=>$all_leader],['id'=>$user_id]);  //所有的上级
                /*判断上级是否满足外汇经纪人条件*/
//                $sub_person_count = Db::query("SELECT count(*) as sub_person_count  FROM `es_user` WHERE FIND_IN_SET($parent_id,all_leader)")[0]['sub_person_count'];  //客户体系
//                $direct_person_count = Db::name('user')->where('first_leader','=',$parent_id)->count('id');  //直推人数
//                if($direct_person_count >= sysconf('junior_direct_condition')){
//                    //初级20条件
//                    if($sub_person_count>=sysconf('junior_direct_person')){
//                        //客户体系500人
//                        $res['junior_upgrade'] = $this->userModel->isUpdate()->save(['user_role'=>1],['id'=>$parent_id]);
//                    }
//                }elseif($direct_person_count >= sysconf('advance_direct_condition')){
//                    //高级50条件
//                    if($sub_person_count>=sysconf('advance_direct_person')){
//                        //客户体系满1000人
//                        $res['advance_upgrade'] = $this->userModel->isUpdate()->save(['user_role'=>2],['id'=>$parent_id]);
//                    }
//                }
                if(check_arr($res)){
                    $this->userModel->commit();
                    return true;
                }else{
                    $this->userModel->rollback();
                    return false;
                }
                return false;
            }
            return false;  //已经有上级了
        }
        return false;
    }
}