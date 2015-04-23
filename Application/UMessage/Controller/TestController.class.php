<?php
namespace UMessage\Controller;

use UMessage\Logic\UnifyMessage;

use UMessage\Logic\UnifyMessageSender;

use Think\Controller;

/**** Unify Message Test Controller ****/
class TestController extends Controller {


	/**
	 *
     */
	public function index() {
		echo "OK";
	}


	/**
	 *
     */
	public  function sendTest(){
		$run = new \UMessage\Logic\UnifyMessageDispatcher();
		$sender = $run->sendPendingMessages();
		var_dump($sender);
		//UnifyMessageDispatcher::sendPendingMessages();
	}

	/**
	 *
     */
	public function testUMsg() {
		$sender = new UnifyMessageSender();
		//尝试发送短信
		$sender->sendUMessage(UnifyMessage::NewSMS('13500000217', 'Hello, 浩哥!'));
		echo '发送短信...'."OK<br/>\r";
		$sender->sendUMessage(UnifyMessage::NewEMail('5511620@qq.com', '轻松行网站消息', "
<html>
	<head>
	</head>
	<body>轻松行科技向您问好！<br/>
	</body>
</html>"));
		echo '发送邮件...'."OK<br/>\r";
		
		$sender->sendUMessage(UnifyMessage::NewUnionMessage(array(
			C('73GO_UM_SMS'), C('73GO_UM_WECHAT')
		), array (
			C('73GO_UM_SMS')=>'18127893680',
			C('73GO_UM_WECHAT')=>'18127893680'
		), '', '客人你好！轻松行网站向你问好！', "
<html>
	<head>
	</head>
	<body>轻松行科技向您问好！<br/>
	</body>
</html>"));		
		echo '发送集成消息...'."OK<br/>\r";
		
		$this->success("发送测试成功！", U('Test/index'), 3);
	}
	
	
}