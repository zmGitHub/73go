<?php

namespace Mfront\Widget;

use Think\Controller;

/**
 * 微信响应系统
 * 董发勇
 * 语音请求，文本请求，网页请求，事件推送
 */
class CateWidget extends Controller {

	//底部菜单
	public function footer(){
		$config = C('THINK_WECHAT');
		$footermenu = json_decode($config['JSON_MENU'],true);

		$this->assign("footermenu", $footermenu);
		$this->theme('default')->display("footer");

	}



}
?>