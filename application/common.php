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

use service\DataService;
use service\NodeService;
use think\Db;
use service\WechatService;
use think\facade\Env;

function shuffle_assoc($list)
{
    if (!is_array($list)) return $list;
    $keys = array_keys($list);
    shuffle($keys);
    $random = array();
    foreach ($keys as $key)
        $random[$key] = $list[$key];
    return $random;
}

function getByIdAddress($id)
{
    return Db::name('sys_address')->where(['id' => $id])->value('area_name');
}

/*
 * $uid  用户id
 * $type 分佣类型
 * $money 操作金额
 * $content 分佣说明
 *  $extends  额外的补充
 *  $orderId  orderID
 * */

function replace_img_url($img_url)
{
    $parse_path = parse_url($img_url);
    if (!empty($parse_path['path'])) {
        $new_path = 'http://' . $_SERVER['HTTP_HOST'] . $parse_path['path'];
        return $new_path;
    }
}

/**
 *  * 获取美元人民币汇率
 *  * @return float
 *  */
function getRate()
{

}


function reserved($money){
   return sprintf("%.2f",$money);
}

/*
 * 兑换成usdt个数(单位都为美元)
 * */
function exchangeUsdt($money){
    //一个usdt等于多少美元
    $usdt_money = 1;
    $usdt_num = round($money/$usdt_money,2);
    return $usdt_num;
}

/*
 * 兑换成lxc个数(单位都为美元)
 * */
function exchangeLxc($money){
    //一个lxc等于多少美元
    $lxc_money = sysconf('lxc_dollar');
    $lxc_num = round($money/$lxc_money,2);
    return $lxc_num;
}
//走的是已结算的
function mlog($uid, $type, $money, $content, $extends = '', $orderId = 0, $status = 1,$fromid = 0,$state=1,$sl_num='')
{
    $before = Db::name('store_member')->where(['id' => $uid])->value($type);
    $res[] = Db::name('store_member')->where(['id' => $uid])->setInc($type, $money);
    $res[] = Db::name('bags_log')->insertGetId([
        'uid' => $uid,
        'type' => $type,
        'money' => $money,
        'before' => $before,
        'content' => $content,
        'extends' => $extends,
        'status' => $status,
        'create_time' => time(),
        'orderId' => $orderId,
        'fromid' => $fromid,
        'state' =>$state,//0未结算1已结算
        'sl_num'=>$sl_num
    ]);
    return $res;
}

//动态奖金记录
function dongtaiAwardjl($tjuid,$tjusername,$uid,$username,$type='', $money, $content,$state=0,$posid,$sl_num,$nac_num='')
{
    $res[] = Db::name('store_award')->insert([
        'uid' => $tjuid,
        'username' => $tjusername,
        'from_uid'=>$uid,
        'from_username'=>$username,
        'type' => $type,
        'money' => $money,
        'content' => $content,
        'create_time' => time(),
        'pos_addtime'=>time(),//矿机产生时间
        'state' =>$state,//0未结算1已结算2失效
        'posid'=>$posid,//来源矿机id
        'sl_num'=>$sl_num,//算力值
        'nac_num'=>$nac_num//nac值
    ]);
    return $res;
}

function getHashOrderInfo($orderid){
    return  Db::name('store_order_c2c')->where('id',$orderid)->value('order_no') ?? Db::name('store_order')->where('id',$orderid)->value('order_no');
}
//获取交易订单
function getC2cOrderPayTime($orderid){
    $orderInfo = Db::name('store_order_c2c')->where(['id'=>$orderid])->find();
    if(!$orderInfo){
        return false;
    }
    if($orderInfo['type'] ==1 ){
        //买入订单
       $result = Db::name('store_c2c_relation')->where(['orderid'=>$orderid])->find();
    }elseif($orderInfo['type'] == 2){
        //卖出订单
        $result = Db::name('store_c2c_relation')->where(['orderid'=>$orderInfo['other_order_id']])->find();
    }
    return $result;
}

/*发送短信*/
function sendMobileMessage($mobile, $content, $template_id = 0)
{
    $obj = new service\SmsApi();
    if ($template_id > 0) {
        //变量模板
        if ($template_id == 0) trigger_error('缺少短信模板id');
        return $obj->send($mobile, $content, $template_id);
    } else {
        //全文模板
        return $obj->sendAll($mobile, $content);
    }


}

//调用微信支付  调用页面必须是在微信支付中配置过的
function getWechatIntance($body, $out_trade_no, $fee, $notify_url, $openid)
{
    $obj = WechatService::WeChatPay();
    $pay_res = $obj->createOrder(['body' => $body, 'nonce_str' => date('YmdHis'), 'out_trade_no' => $out_trade_no, 'total_fee' => $fee, 'spbill_create_ip' => $_SERVER['REMOTE_ADDR'], 'trade_type' => 'JSAPI', 'notify_url' => $notify_url, 'sign_type' => 'MD5', 'openid' => $openid]);
    $payjsparam = $obj->createParamsForJsApi($pay_res['prepay_id']);
    return $payjsparam;
}

//获取发放收益实例
function getMemberFree()
{
    try {
        $obj = \member\MemberFreeBonus::getInstance();
        return $obj;
    } catch (Exception $e) {
        trigger_error($e->getMessage());
    }

}

//制作二维码
function makeQrcode($content, $path)
{
    \PHPQRCode\QRcode::png($content, $path, 'L', 4, 0);
    return $path;
}


function level($id)
{
    return Db::name('sys_level')->find($id)['title'] ?? '暂无等级1';
}

function region_level($id)
{
    return Db::name('sys_earnings')->find($id)['title'] ?? '暂无等级2';
}
function address($id)
{
    if ($id) {
        return Db::name('sys_address')->find($id)['area_name'];
    }

}

function getrealname($id)
{
    if ($id) {
        return Db::name('store_member')->find($id)['email'];
    }

}
function userpayment($id)
{
    if ($id) {
        return Db::name('store_member_payment')->where(['uid'=>$id,'type'=>3,'state'=>1])->value('payment');
    }

}
/*
 * 获取用户信息
 * */
function getUserById($id){
    return Db::name('store_member')->find($id);
}
/*
 * 获取用户实名信息
 * */
function getusernamebyid($id){
    $name =  Db::name('store_member_idcard')->where('uid',$id)->value('truename');
    return $name;
}    
/**
 * 打印输出数据到文件
 * @param mixed $data 输出的数据
 * @param bool $force 强制替换
 * @param string|null $file
 */
function p($data, $force = false, $file = null)
{
    is_null($file) && $file = env('runtime_path') . date('Ymd') . ' . txt';
    $str = (is_string($data) ? $data : (is_array($data) || is_object($data)) ? print_r($data, true) : var_export($data, true)) . PHP_EOL;
    $force ? file_put_contents($file, $str) : file_put_contents($file, $str, FILE_APPEND);
}

function email($id)
{
    return Db::name('store_member')->where('id', $id)->value('email');
}

function usernamecc($id)
{
    return Db::name('store_member')->where('id', $id)->value('nickname');
}
function useraddress($id)
{
    return Db::name('store_member')->where('id', $id)->value('address');
}
function account($id)
{
    return Db::name('store_member')->where('id', $id)->value('email');
}
function num_id($id)
{
    return Db::name('store_member')->where('id', $id)->value('num_id');
}
function invite_code($id)
{
    return Db::name('store_member')->where('id', $id)->value('invite_code');
}
function currency($id)
{
   return Config::get("currency.$id");
}
function currencyad($id)
{
    if($id == 2){
        return 'FOC';
    }else{
        return 'FOC';
    }
}
function legal($id)
{
    return Config::get("legal.$id");
}
/**
 * RBAC节点权限验证
 * @param string $node
 * @return bool
 */
function auth($node)
{
    return NodeService::checkAuthNode($node);
}

/**
 * 设备或配置系统参数
 * @param string $name 参数名称
 * @param bool $value 默认是null为获取值，否则为更新
 * @return string|bool
 * @throws \think\Exception
 * @throws \think\exception\PDOException
 */
function sysconf($name, $value = null)
{
    static $config = [];
    if ($value !== null) {
        list($config, $data) = [[], ['name' => $name, 'value' => $value]];
        return DataService::save('SystemConfig', $data, 'name');
    }
    if (empty($config)) {
        $config = Db::name('SystemConfig')->column('name,value');
    }
    return isset($config[$name]) ? $config[$name] : '';
}

/**
 * 日期格式标准输出
 * @param string $datetime 输入日期
 * @param string $format 输出格式
 * @return false|string
 */
function format_datetime($datetime, $format = 'Y年m月d日 H:i:s')
{
    return date($format, strtotime($datetime));
}

/**
 * UTF8字符串加密
 * @param string $string
 * @return string
 */
function encode($string)
{
    list($chars, $length) = ['', strlen($string = iconv('utf - 8', 'gbk', $string))];
    for ($i = 0; $i < $length; $i++) {
        $chars .= str_pad(base_convert(ord($string[$i]), 10, 36), 2, 0, 0);
    }
    return $chars;
}

/**
 * UTF8字符串解密
 * @param string $string
 * @return string
 */
function decode($string)
{
    $chars = '';
    foreach (str_split($string, 2) as $char) {
        $chars .= chr(intval(base_convert($char, 36, 10)));
    }
    return iconv('gbk', 'utf - 8', $chars);
}

/**
 * 下载远程文件到本地
 * @param string $url 远程图片地址
 * @return string
 */
function local_image($url)
{
    return \service\FileService::download($url)['url'];
}

function username($id)
{
    $username = Db::name('store_member')->where(['id' => $id])->value('phone');
    return $username;
}
function nickname($id)
{
    $nickname = Db::name('store_member')->where(['id' => $id])->value('nickname');
    return $nickname;
}
function name($id)
{
    $username = Db::name('store_member')->where(['id' => $id])->value('nickname');
    $username ? $username : '暂未设置';
    return $username;
}

function datetime($msg)
{
    if ($msg) {
        $time = date('Y-m-d H:i:s', $msg);

    } else {
        $time = '暂无';
    }
    return $time;
}

function adminname($msg)
{
    $name = Db::name('system_user')->where(['id' => $msg])->value('username');
    return $name ? $name : '暂无';

}
//条件为分享正式会员数，团队粉丝数量，激活消耗现金积分
function upgradeUserLevel($wx_user)
{
    $flag = true;
    if ($flag) {
        if ($wx_user['member_level'] == 12) {
           // $this->error('不满足条件,升级失败');
        }
        //会员列表的member_level对应升级表里面的id
        if($wx_user['member_level'] == 1){
            $userLevel = Db::name('sys_level')->where(['id' => $wx_user['member_level'] + 1])->find();
        }else{
            $userLevel = Db::name('sys_level')->where(['id' => $wx_user['member_level']])->find();
        }
        //团队粉丝
        $uid = $wx_user['id'];
       
          $team_fans = Db::query("SELECT count(*) as sub_person_count  FROM `store_member` WHERE FIND_IN_SET($uid,all_leader)")[0]['sub_person_count'];
        //直接分享粉丝
        $direct_share_fans = Db::name('store_member')->where(['first_leader' => $wx_user['id']])->count();
        $drive = Db::name('store_member')->where(['first_leader' => $wx_user['id']])->where('member_level', ">=", 2)->count();
//            $shiming = Db::name('store_member')->where(['first_leader' => $this->wx_user_id])->select();
//            foreach ($shiming as $k => $v) {
//                $shiming[$k]['count'] = Db::name('store_member_idcard')->where(['status' => 1, 'uid' => $v['id']])->count();
//                //直推实名
//            }
//            $mining_total = array_sum(array_column($shiming, 'count'));

        $xpay_money = Db::name('bags_log')->where(['type'=>'pay_money','uid'=>$wx_user['id'],'content'=>'现金积分订单消费'])->where('money<0')->sum('money');
        if($xpay_money<0)
        {
        	$xpay_money = 0 - $xpay_money;
        }
        //升级的条件
        if ($drive >= $userLevel['share'] && $team_fans >= $userLevel['fans'] && $xpay_money >= $userLevel['num']) {
            //查询有没有升级的记录
            $upgradeRecordFlag = Db::name('store_member_upgrade_record')->where(['uid' => $wx_user['id'], 'upgradeLevel' => $wx_user['member_level'] + 1])->find();
            Db::startTrans();
            if ($upgradeRecordFlag) {
                //如果有的话，直接更新字段
                $res[] = Db::name('store_member')->where(['id' => $wx_user['id']])->setInc('member_level');
            } else {
                //如果没有的话，添加记录，更新字段
                $upgradeRecord = [
                    'uid' => $wx_user['id'],
                    'addtime' => get_time(),
                    'upgradeLevel' => $wx_user['member_level'] + 1
                ];
                $res[] = Db::name('store_member_upgrade_record')->insertGetId($upgradeRecord);
                $res[] = Db::name('store_member')->where(['id' =>$wx_user['id']])->setInc('member_level');
//                    if ($this->wx_user['account_money'] < $userLevel['num']) $this->error('账户PDR金额不足');
//                    $res[] = mlog($this->wx_user_id, 'account_money', -$userLevel['num'], "升级{$userLevel['title']},消耗PDR{$userLevel['num']}", 'upgrade_consum');
            }
            if (check_arr($res)) {
                Db::commit();
              //  echo "ok";
                //$this->success('升级成功');
            } else {
                Db::rollback();
                //$this->error('升级失败');
               // echo "no1";
            }

        } else {
            //echo "no2";
           // $this->error('不满足条件,升级失败');
//                $this->error("不满足条件,升级失败{$drive}|{$userLevel['share']}+{$team_fans}|{$userLevel['fans']}+{$this->wx_user['hashrate']}|{$userLevel['force']}");
        }
    } else {
       // echo "no3";
       // $this->error('不满足条件,升级失败');
    }


}



/*
 * 加密
 */
function authcode($string, $operation = 'ENCODE', $key = '', $expiry = 0) {
    $ckey_length = 4;
    $key = md5 ( $key ? $key : 'default_key' );
    $keya = md5 ( substr ( $key, 0, 16 ) );
    $keyb = md5 ( substr ( $key, 16, 16 ) );
    $keyc = ($ckey_length ? ($operation == 'DECODE' ? substr ( $string, 0, $ckey_length ) : substr ( md5 ( microtime () ), - $ckey_length )) : '');
    $cryptkey = $keya . md5 ( $keya . $keyc );
    $key_length = strlen ( $cryptkey );
    $string = ($operation == 'DECODE' ? base64_decode ( substr ( $string, $ckey_length ) ) : sprintf ( '%010d', $expiry ? $expiry + time () : 0 ) . substr ( md5 ( $string . $keyb ), 0, 16 ) . $string);
    $string_length = strlen ( $string );
    $result = '';
    $box = range ( 0, 255 );
    $rndkey = array ();

    for($i = 0; $i <= 255; $i ++) {
        $rndkey [$i] = ord ( $cryptkey [$i % $key_length] );
    }

    for($j = $i = 0; $i < 256; $i ++) {
        $j = ($j + $box [$i] + $rndkey [$i]) % 256;
        $tmp = $box [$i];
        $box [$i] = $box [$j];
        $box [$j] = $tmp;
    }

    for($a = $j = $i = 0; $i < $string_length; $i ++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box [$a]) % 256;
        $tmp = $box [$a];
        $box [$a] = $box [$j];
        $box [$j] = $tmp;
        $result .= chr ( ord ( $string [$i] ) ^ $box [($box [$a] + $box [$j]) % 256] );
    }
    if ($operation == 'DECODE') {
        if (((substr ( $result, 0, 10 ) == 0) || (0 < (substr ( $result, 0, 10 ) - time ()))) && (substr ( $result, 10, 16 ) == substr ( md5 ( substr ( $result, 26 ) . $keyb ), 0, 16 ))) {
            return substr ( $result, 26 );
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace ( '=', '', base64_encode ( $result ) );
    }
}

/*
 * 添加提现记录
 * */
function withdrawLog($uid, $type, $block_address='', $alipay_url='',$num, $tax = '', $acc_money = 0,$total_price='')
{
    $store_member = Db::name('store_member')->where(['id' => $uid])->find();
    if($tax){
        $real_num = $num-$tax;
    }else{
        $real_num = $num;
    }
    $res= Db::name('store_withdraw_money')->insert([
        'uid' => $uid,
        'num_id' => $store_member['num_id'],
        'username' => $store_member['email'],
        'block_address' => $block_address,
        'alipay_url' => $alipay_url,
        'num' => $num,
        'tax' => $tax,
        'real_num'=>$real_num,
        'acc_money' => $acc_money,
        'total_price' => $total_price,
        'type'=>$type,
        'addtime'=>time()
    ]);
    return $res;
}

/*
 *添加释放记录
 * */
function addAccrelease($uid,$num){
    $res= Db::name('acc_release')->insert([
        'uid' => $uid,
        'num' => $num,
        'not_sf_num'=>$num
    ]);
    return $res;
}

/*
 * 团队人数
 * */
function getTdnum($uid){
    $new_add_tjnum = Db::Table('store_member')->where('FIND_IN_SET(:id,all_leader)',['id' => $uid])->where('is_zhujici',2)
        ->count();
    return $new_add_tjnum;
}

/*
 * 直推人数
 * */
function getzhitui($uid){
    $new_add_tjnum = Db::Table('store_member')->where('first_leader',$uid)->where('is_zhujici',2)
        ->count();
    return $new_add_tjnum;
}

/*
 * 有效人数
 * */
function getyouxiao($uid){
    $new_add_tjnum = Db::Table('store_member')->where('first_leader',$uid)->where('is_renzheng',2)->where('is_zhujici',2)
        ->count();
    return $new_add_tjnum;
}

/*赛邮云短信*/
function sendDx($code,$time,$to){
    //$apikey = '25ff0d31260ea39a46fcc58e3a60c77d';
    $pwd = '25ff0d31260ea39a46fcc58e3a60c77d';
    $account = 'n834206968';
    $text="你好！{$to}，您的验证码：{$code}。如非本人操作，可不用理会！【nubc】";
    $url="http://api.sms.cn/sms/?";
    $encoded_text = urlencode("$text");
    //http://api.sms.cn/sms/?ac=send&uid=用户账号&pwd=MD532位密码&mobile=号码&content={"key":"内容"}
    $post_string="ac=send&uid=$account&pwd=$pwd&mobile=$to&content=$encoded_text";
    $info = sock_post($url,$post_string);
    $infoary=explode(',',$info);
    if($infoary[0]!='{"code":0'){
        return true;
    }else{
        return false;
    }
}

function sock_post($url, $query) {
        $data = '';
        $info = parse_url ( $url );
        $fp = fsockopen ( $info ['host'], 80, $errno, $errstr, 30 );
        if (! $fp) {
            return $data;
        }
        $head = 'POST ' . $info ['path'] . ' HTTP/1.0' . "\r\n" . '';
        $head .= 'Host: ' . $info ['host'] . "\r\n";
        $head .= 'Referer: http://' . $info ['host'] . $info ['path'] . "\r\n";
        $head .= 'Content-type: application/x-www-form-urlencoded' . "\r\n" . '';
        $head .= 'Content-Length: ' . strlen ( trim ( $query ) ) . "\r\n";
        $head .= "\r\n";
        $head .= trim ( $query );
        $write = fputs ( $fp, $head );
        $header = '';
        while ( $str = trim ( fgets ( $fp, 4096 ) ) ) {
            $header .= $str;
        }
        while ( ! feof ( $fp ) ) {
            $data .= fgets ( $fp, 4096 );
        }
        return $data;
    }

function getExcel($fileName,$headArr,$data){
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
        require_once Env::get('ROOT_PATH').'/extend/util/PHPExcel.php';
        require_once Env::get('ROOT_PATH').'/extend/util/PHPExcel/Writer/Excel5.php';
        require_once Env::get('ROOT_PATH').'/extend/util/PHPExcel/IOFactory.php';
        // Loader::import('Util.PHPExcel');
        // Loader::import('Util.PHPExcel.Writer.Excel5');
        // Loader::import('Util.PHPExcel.IOFactory.php');

        $date = date("Y_m_d",time());
        $fileName .= "_{$date}.xls";

        //创建PHPExcel对象，注意，不能少了\
        $objPHPExcel = new \PHPExcel(); 
        $objProps = $objPHPExcel->getProperties();

        //设置表头
        $key = ord("A");
        //print_r($headArr);exit;
        foreach($headArr as $v){
            $colum = chr($key);
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            $key += 1;
        }

        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();

        //print_r($data);exit;
        foreach($data as $key => $rows){ //行写入
            $span = ord("A");
            foreach($rows as $keyName=>$value){// 列写入
                $j = chr($span);
                $objActSheet->setCellValue($j.$column, $value);
                $span++;
            }
            $column++;
        }

        $fileName = iconv("utf-8", "gb2312", $fileName);
        //重命名表
        //$objPHPExcel->getActiveSheet()->setTitle('test');
        //设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); //文件通过浏览器下载
        exit;
    }
 /*
 * 新增业绩入库
 * */
function addNewperformance($uid,$money){
    $res= Db::name('store_newaddmoney')->insert([
        'uid' => $uid,
        'money' => $money,
        'creat_at'=>time()
    ]);
    return $res;
}


function number_format1($moeny){
  return  number_format($moeny,6);
}

//生成唯一订单号 函数1 默认每天小于9万个订单号
function makeRand( $num = 6 ){
    mt_srand((double)microtime() * 1000000);//用 seed 来给随机数发生器播种。
    $strand = str_pad(mt_rand(1, 99999),$num,"0",STR_PAD_LEFT);
    return date('Ymd').$strand;
}
/*
 * 距今多长时间
 * */
function format_date($time){
    $t=time()-$time;
    $f=array(
        '31536000'=>'年',
        '2592000'=>'个月',
        '604800'=>'星期',
        '86400'=>'天',
        '3600'=>'小时',
        '60'=>'分钟',
        '1'=>'秒'
    );
    foreach ($f as $k=>$v)    {
        if (0 !=$c=floor($t/(int)$k)) {
            return $c.$v.'前';
        }
    }
}

function language($local,$module,$action){
    $language = new \language\Language($local,$module);
    $info = $language->get($action);
    return $info;
}

function gtranslate($text)
{
    $entext = urlencode($text);
    $url = 'http://translate.google.cn/translate_a/single?client=gtx&dt=t&ie=UTF-8&oe=UTF-8&sl=auto&tl=zh-CN&q=' . $entext;
    set_time_limit(0);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 20);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 40);
    curl_setopt($ch, CURLOPT_URL, $url);
    $result = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($result);
    if (!empty($result)) {
        foreach ($result[0] as $k) {
            $v[] = $k[0];
        }
        return implode(" ", $v);
    }
}

//添加语言
function bagslanguage($b_id,$money1='',$money2='',$money3='',$money4='',$fy_state1='',$fy_state2='',$fy_state3='',$fy_state4=''){
    $res[] = Db::name('bags_language')->insertGetId([
        'b_id' => $b_id,
        'money1' => $money1,
        'money2' => $money2,
        'money3' => $money3,
        'money4' => $money4,
        'fy_state1' => $fy_state1,
        'fy_state2' => $fy_state2,
        'fy_state3' => $fy_state3,
        'fy_state4' => $fy_state4,
    ]);
    return $res;
}
function randomFloat($min = 0, $max = 1) {
    return $min + mt_rand() / mt_getrandmax() * ($max - $min);
}

function period($id){
    $period = [
        5 => "1min",
        6 => "5min",
        1 => "15min",
        7 => "30min",
        2 => "60min",
        3 => "4hour",
        4 => "1day",
        8 => "1week",
        9 => "1mon",
        10 => "1year",
    ];
    return $period[$id];
}

function member_level($id){
    $level = array('普通用户','会员','节点','董事','联创','动态股东','预备节点');
    return $level[$id];
}

/**
 * 拼装交易数据
 *
 * @param Sting $type [kline]
 * @param mixed $data
 * @return bool|string
 */
function writeMarketData($type, $data)
{
    // do some thing
    $pushUrl = "http://127.0.0.1:5678";
    $postData = [
        'event' => $type,
        'msg' => is_array($data) ? json_encode($data) : $data
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $pushUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:"));
    $return = curl_exec($ch);
    curl_close($ch);

    return $return;
}

function check_arr( $rs )
{
    {
        foreach ($rs as $v) {
            if (is_array($v)) {
                foreach ($v as $val) {
                    if (!$val) {
                        return false;
                    }
                }
            } else {
                if (!$v) {
                    return false;
                }
            }
        }
        return true;
    }

}
