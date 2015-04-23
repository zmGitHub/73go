<?php

namespace Mfront\Controller;

use Think\Controller;

/**
 * 微信响应系统
 * 董发勇
 * 语音请求，文本请求，网页请求，事件推送
 */
class IndexController extends Controller {
	
	/**
	 * 微信调用程序的入口
	 */
	public function index() {			
		define ( "TOKEN", "weixin" );
		$wechatObj = new WScheduleinfoController ();
		$echost = $_GET ['echostr'];
		if (! isset ( $echost )) {
			$wechatObj->responseMsg ();
		} else {
			$this->valid ();
		}
	}
	
//创建菜单
	public function createmenu(){
		$config = C('THINK_WECHAT');

		$publi=new WPublicController();
		$access_token=$publi->initAccessToken();
		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
		$result = $this->https_request($url, $config['JSON_MENU']);
		var_dump($result);

	}
	
	public function deletemenu(){
		$publi=new WPublicController();
		$access_token=$publi->initAccessToken();
		$url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=".$access_token;
		$result = $this-> https_request($url);
		var_dump($result);	
	}


	/**
	 * 输出$echoStr的值,验证入口
	 */
	public function valid() {
		$echoStr = $_GET ["echostr"];
		if ($this->checkSignature ()) {
			echo $echoStr;
			exit ();
		}
	}
	/**
	 * 校验签名的值
	 */
	public function checkSignature() {
		$signature = $_GET ["signature"];
		$timestamp = $_GET ["timestamp"];
		$nonce = $_GET ["nonce"];
		$token = TOKEN;
		$tmpArr = array (
				$token,
				$timestamp,
				$nonce 
		);
		sort ( $tmpArr );
		$tmpStr = implode ( $tmpArr );
		$tmpStr = sha1 ( $tmpStr );
		if ($tmpStr == $signature) {
			return true;
		} else {
			return false;
		}
	}
	public function https_request($url,$data = null){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if (!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}
	public function text(){
		echo 123333;
	}
}
?>
