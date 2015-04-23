<?php

namespace Mfront\Controller;

use Think\Controller;

/**
 * 微信消息回复格式方法
 * 微信access_token获取
 * 微信cul方法封装
 * 
 * @author dfy
 *        
 */
class WPublicController extends Controller {
	//分发接口调用
	public function opfen($reqId){
		$tmc=M('tmc');
		$tmc_qsx_rec = M('tmc_qsx_rec');
		$info=$tmc->field('id')->select();
			foreach($info as $key=>$vo){
					$map['req_id'] = $reqId;
					$map['tmc_id']= $vo['id'];
					$map['distr_time']=date('Y-m-d H:i:s',time());
					$map['status']= 0;
					$res = $tmc_qsx_rec->data($map)->add();	
				}	
				return $res;
				
	}
	/**
	 * 处理微信高级接口参数返回接口处理
	 */
	public function https_request($url) {
		$curl = curl_init ();
		curl_setopt ( $curl, CURLOPT_URL, $url );
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
		$data = curl_exec ( $curl );
		if (curl_errno ( $curl )) {
			return 'ERROR ' . curl_error ( $curl );
		}
		curl_close ( $curl );
		return $data;
	}


	/**
	 * 获取access_token
	 */
	public function initAccessToken() {
		$data = F("wechat/data");

		if(time() >= $data['expire']){
			$config = C('THINK_WECHAT');
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$config['APP_ID']}&secret={$config['APP_SECRET']}";

			$data['time'] = time();
			$curl = new \Org\Util\Curl();
			$result = $curl->request($url);
			$result = json_decode ( $result, true );

			$data['access_token'] = $result["access_token"];
			$data['expires_in'] = $result["expires_in"];
			$data['expire'] = $data['time'] + $result["expires_in"];

			F("wechat/data", $data);
		}
		return  $data['access_token'];
	}


	/**
	 * 获取处理openid
	 */
	public function initOpenid($return = false) {
		$openid = li('userOpenid');

		if(!isset($openid) || empty($openid) || $openid == false ){
			$code = I("get.code");
			$config = C('THINK_WECHAT');
			$url =  "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$config['APP_ID']}&secret={$config['APP_SECRET']}&code={$code}&grant_type=authorization_code";

			$curl = new \Org\Util\Curl();
			$result = $curl->request($url);
			$result = json_decode ( $result, true );
		}else{
			$result['openid'] = $openid;
		}
		$user = M('user');
		$cond = array("status" => 0,"wx_openid"=>$result['openid']);
		$data = $user->where($cond)->find();

		if($data){
			LI('userId', $data['id']);
			LI('userName', $data['username']);
			LI('userType', $data['user_type']);
			LI('userOpenid', $data['wx_openid']);

			$employee =M('employee');
			$employeecond= array("u_id" => $data['id']); ;
			$employeedata = $employee->where($employeecond)->find();
			LI('comId',$employeedata['co_id']);
			LI('empId',$employeedata['id']);

			return $data['wx_openid'];

		}else{
			unset($_SESSION['LoginInfo']);
			if($return){
				return $result['openid'];
			}else{
				$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$config['APP_ID']}&redirect_uri={$config['ACCOUNT_BINDING_URL']}";
				header("Location: {$url}");
			}
		}

	}
	/**
	 * 发送文本消息
	 */
	public function sendText($object, $content) {
		$access_token = $this->initAccessToken();
		$json_test  = '{"touser": "'.$object.'","msgtype": "text",	"text": {"content": "'.$content.'"}}';
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
		$result = $this->http_requestscheme($url,$json_test);
		$result = json_decode ( $result, true );

		return $result['errcode'] == 0 ? true : false;
	}


	/**
	 * 当对象不是数据时
	 * 回复文本消息格式
	 */
	public function transmitNews($object, $content) {
	}
	/**
	 * 处理curl封装的语音值
	 */
	public function http_request($curlPost, $url) {
		$ch = curl_init (); // 初始化curl
		curl_setopt ( $ch, CURLOPT_URL, $url ); // 抓取指定网页
		curl_setopt ( $ch, CURLOPT_HEADER, 0 ); // 设置header
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 ); // 要求结果为字符串且输出到屏幕上
		curl_setopt ( $ch, CURLOPT_POST, 1 ); // post提交方式
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $curlPost );
		$data = curl_exec ( $ch ); // 运行curl
		curl_close ( $ch );
		return $data;
	}
	/**
	 * 当对象是数据时
	 * 回复文本消息格式
	 */
	public function transmitText($object, $content) {
		$textTpl = "
        <xml>
			<ToUserName><![CDATA[%s]]></ToUserName>
			<FromUserName><![CDATA[%s]]></FromUserName>
			<CreateTime>%s</CreateTime>
			<MsgType><![CDATA[text]]></MsgType>
			<Content><![CDATA[%s]]></Content>
		</xml>";
		$result = sprintf ( $textTpl, $object->FromUserName, $object->ToUserName, time (), $content );
		return $result;
	}
	/**
	 * 处理客服接口cul封装的数据
	 */
	public function http_requestscheme($url,$data){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($curl);
		if (curl_errno($curl)) {
			return 'Errno'.curl_error($curl);
		}
		curl_close($curl);
		return $result;
	}
}
?>