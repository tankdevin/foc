<?php

namespace app\apiv1\controller;
use controller\BasicIndex;

use think\Db;
use phpmailer\PHPMailer;

class Googledx extends BasicIndex
{
 
	
/**
 * 发送邮箱
 * @param type $data 邮箱队列数据 包含邮箱地址 内容
 * @param $to_address 到哪个邮箱
 * @param $expiration_time 过期时间
 */
public function sendMail($type,$code,$expiration_time,$to_address) {
    
    $mail = new PHPMailer();//实例化
    
    $mail->CharSet='UTF-8';//设置字符集
    //var_dump($toarr);exit();
    $mail->IsHTML(true);//是否使用HTML格式
    //使用smtp鉴权方式发送邮件
    $mail->isSMTP();
    //smtp需要鉴权 这个必须是true
    $mail->SMTPAuth = true;
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
  /*
   // qq 邮箱的 smtp服务器地址，这里当然也可以写其他的 smtp服务器地址
    $mail->Host = 'smtp.qq.com';
     //smtp登录的账号 这里填入字符串格式的qq号即可
    $mail->Username = "6023xxx02@qq.com";
     // 这个就是之前得到的授权码，一共16位
    $mail->Password = "hilmigjuhqxibdif";
    $mail->setFrom("6023xxx02@qq.com", "zzz");
    // $to 为收件人的邮箱地址，如果想一次性发送向多个邮箱地址，则只需要将下面这个方法多次调用即可
    $mail->addAddress("zhangyouwu1018@gmail.com"); */ 
     
    $mail->Host = 'smtp.gmail.com';
    $mail->Username = "support@noahsark.work";
    $mail->Password = "noahsark888";
    $mail->setFrom("support@noahsark.work", "noahsark");
    //发送邮箱给谁
    $mail->addAddress($to_address);  
 
  
    $name="lee";
    // $money = "500USDT";
    //邮箱验证码
    $hashAddress =$code;
    // 该邮件的主题
    $mail->Subject = "Noah's Ark System Verification Code";
    if($type == 1){
        // 该邮件的正文内容
        $mail->Body = "<div style='width:100%;height:180px;'><img src='https://app.noahsark.work/logo.png' style='float:left;width:150px;height:150px;'></div>";
        $mail->Body .= "<br>--------------------------------------------------<br><br>";
        $mail->Body .= "Dear   ";
        $mail->Body .= $name;
        $mail->Body .= "!<br><br>";
        $mail->Body .= "Transaction batch is ";
        $mail->Body .= "<br><br>";
        $mail->Body .=$hashAddress;
        $mail->Body .= "<br><br>---------------------------------------------------------<br>";
        $mail->Body .= "Your faithfully,<br>Noah’s Ark System<br>https://www.noahsark.work"; 
    }else{
         // 该邮件的正文内容
        $mail->Body = "<div style='width:100%;height:180px;'><img src='https://app.noahsark.work/logo.png' style='float:left;width:150px;height:150px;'></div>";
        $mail->Body .= "<br>--------------------------------------------------<br><br>";
        $mail->Body .= "Dear   ";
        $mail->Body .= $name;
        $mail->Body .= "!<br><br>";
        // $mail->Body .= $money;
        // $mail->Body .= "    has been successfully sent to your account .";
        $mail->Body .= "Transaction batch is ";
        $mail->Body .= "<br><br>";
        $mail->Body .=$hashAddress;
        $mail->Body .= "<br><br>---------------------------------------------------------<br>";
        $mail->Body .= "Your faithfully,<br>Noah’s Ark System<br>https://www.noahsark.work"; 
    }
   
    $mail->SMTPSecure = 'ssl';
    // 设置ssl连接smtp服务器的远程服务器端口号
    $mail->Port = 465;
 
    // 使用 send() 方法发送邮件
    if(!$mail->send()) {
        //return "1";
        $result = [
            'status'=>0,
            'msg'=>'发送失败: ' . $mail->ErrorInfo,
        ];
        
    } else {
        $result = [
            'status'=>1,
            'msg'=>'发送成功，请查看邮箱',
        ];
    }
    return $result;
    
}


}
