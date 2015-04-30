<?php
namespace Home\Logic;
use Think\Model;
use UMessage\Logic\UnifyMessage;
use UMessage\Logic\UnifyMessageSender;
/**
 * 制作发送短信和邮箱的模板文件
 * 创建者：郭攀
 * 创建时间：2014-03-05
 *
 */
	
	//定义SendMessageLogic继承底层的model类
	class SendMessagelogic extends Model{
		
		//定义SendDetail方法
		public function SendDetails($case,$data){
			
			//判断预定是通过主网站预定，还是TMC旗舰店网站判断
			//如果是主网站的时候TMC公司显示轻松行科技，如果是TMC旗舰店网站的时候显示TMC旗舰店的名字
			if(getHostedTmcInfo('name')){
				$show_name=	getHostedTmcInfo('name');
			}else{
				$show_name="duangduang打飞机";
			}
			//获取发送的时间；
			$data['time'] = date ( 'Y-m-d H:i:s', time () );
			
			switch ($case)
			{
				case "EmailGetNewPassword":
					$types = array(2);
					$targets = array (	2 => $data['user_email']);
					$title = '账户使用邮箱获得新的密码';
					$html =  '亲爱的用户：您的新密码为:'.$data ['newpassword'].'，请及时修改密码！'."<此为系统发送邮件，请勿回复>";
				break;

				case "PhoneGetNewPassword":
					$types=array(1);
					$targets = array (	1 => $data['user_phone']);
					$title = '账户使用短信获得新的密码';
					$text = '亲爱的'.$data['username'].':您在duangduang打飞机的新密码为 '.$data['newpassword'].'，请及时修改密码！';
				break;

				case "PhoneGetVerifyCode":
					$types=array(1);
					$targets = array (	1 => $data['user_phone']);
					$title = '账户使用短信获得验证码';
					$text = '亲爱的用户:您的动态验证码为:'.$data['newpassword'].'，请输入以进行身份认证，该动态密码3分钟有效。';
					break;




			}
		
		$sender = new UnifyMessageSender ();
		$um = UnifyMessage::NewUnionMessage ( $types,$targets,$title,$text, $html );
		$sender->sendUMessage ($um);
		
		
		}
			


	}