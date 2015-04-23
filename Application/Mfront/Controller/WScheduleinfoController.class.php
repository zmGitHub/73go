<?php

namespace Mfront\Controller;

use Think\Controller;

/**
 * 微信响应系统
 * 董发勇
 * 语音请求，文本请求，网页请求，事件推送
 */
class WScheduleinfoController extends Controller {
	/**
	 * 判断微信用户请求的类型
	 */
	public function responseMsg() {
		/*
		 * $HTTP_RAW_POST_DATA 变量包含有原始的 POST 数据，想当于post请求只是有时候post请求 不能处理的数据格式（如xml，text）可以使用$GLOBALS ["HTTP_RAW_POST_DATA"]来处理
		 */
		$postStr = $GLOBALS ["HTTP_RAW_POST_DATA"];
		if (! empty ( $postStr )) {
			/*
			 * 将$postStrxml数据保存到$postObj对象中去 $postStr xml格式数据 SimpleXMLElement php解析xml的一种方式 LIBXML_NOCDATA 吧CDATA设置为文本节点
			 */
			$postObj = simplexml_load_string ( $postStr, 'SimpleXMLElement', LIBXML_NOCDATA );
			$RX_TYPE = trim ( $postObj->MsgType );
			switch ($RX_TYPE) {
				case "text" ://文本
					$result = $this->receiveText ( $postObj );
					break;
				case "voice" ://语音
					$result = $this->receiveVoice ( $postObj );
					break;
				case "event" ://事件
					$result = $this->receiveEvent ( $postObj );
					break;
				default :
					$result = "unknow msg type: " . $RX_TYPE;
					break;
			}
			echo $result;
		} else {
			echo "";
			exit ();
		}
	}
	/**
	 * 回复文本消息
	 */
	public function receiveText($object) {
		$keyword = trim ( $object->Content );
		$content = $object->Content;
		$result = $this->transmitText ( $object, $content );
		return $result;
	}
	/**
	 * 回复语音订票消息
	 */
	public function receiveVoice($objectvoice) {
		$public = new WPublicController ();
		if (isset ( $objectvoice->Recognition ) && ! empty ( $objectvoice->Recognition )) {
			/*
			 * 封装语音信息数据 语音订单基本信息 语音订单扩展信息
			 */
			$wx_fromusername = $objectvoice->FromUserName; // 微信用户标识
			$wx_createtime = $objectvoice->CreateTime; // 消息创建时间
			$wx_msgID = $objectvoice->MsgId; // 消息id
			$wx_msgtype = $objectvoice->MsgType; // 语音类型
			$wx_mediaID = $objectvoice->MediaID; // 语音消息媒体id
			$wx_formart = $objectvoice->Format; // 语音格式
			$wx_recognition = $objectvoice->Recognition; // 语音识别结果
			$curlPost = 'fromusername=' . urlencode ( "$wx_fromusername" ) . '&createtime=' . urlencode ( "$wx_createtime" ) . '&msgID=' . urlencode ( "$wx_msgID" ) . '&msgtype=' . urlencode ( "$wx_msgtype" ) . '&mediaID=' . urlencode ( "$wx_mediaID" ) . '&formart=' . urlencode ( "$wx_formart" ) . '&recognition=' . urlencode ( "$wx_recognition" );
			$urlvoice = 'http://devwww.73go.cn/index.php/Mfront/WScheduleInterface/addVoiceQsxReq';
			$public->http_request ( $curlPost, $urlvoice );
			$contentStr ="您的需求已收到,我们将安排多位资深的出行顾问为您服务."."\n"."30分钟内出行顾问将提供多套方案供您选择,请耐心等待,谢谢配合！";
			//$contentStr ="<a href=\"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxe88f8959823e92a2&redirect_uri=http%3a%2f%2fweixin.73go.cn%2fMfront%2fWScheduleInterface%2fselectOrder&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect\">ddd</a>";
		} else {
			$contentStr = "未开启语音识别功能或者识别内容为空";
		}
		
		if (is_array ( $contentStr )) {
			$resultStr = $public->transmitNews ( $objectvoice, $contentStr );
		} else {
			$resultStr = $public->transmitText ( $objectvoice, $contentStr );
		}
		return $resultStr;
	}
	/**
	 * 回复关注事件
	 */
	public function receiveEvent($object) {
		$public = new WPublicController ();
		$content = "";
		$access_token = $public->initAccessToken ();
		$openid = $object->FromUserName;
		$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access_token&openid=$openid&lang=zh_CN";
		$output = $public->https_request ( $url );
		$jsoninfo = json_decode ( $output, true );
		switch ($object->Event) {
			case "subscribe" : // 关注事件
				/*
				 * 封装微信关注用户信息
				 */
				$wx_subscribe = 1; // 判断用户是否为关注用户，1为关注用户，0为不是关注用户\
				$wx_openid = $jsoninfo ["openid"]; // 用户唯一标示
				$wx_nickname = $jsoninfo ["nickname"]; // 用户昵称
				$wx_sex = (($jsoninfo ["sex"] == 1) ? "M" : "F"); // 用户性别
				$wx_city = $jsoninfo ["city"]; // 用户所在城市
				$wx_country = $jsoninfo ["country"]; // 用户所在国家
				$wx_province = $jsoninfo ["province"]; // 用户所在省份
				$wx_language = (($jsoninfo ["language"] == "zh_CN") ? "简体中文" : "非简体中文"); // 用户所使用的语言
				$wx_headimgurl = $jsoninfo ["headimgurl"]; // 用户头像
				$wx_subscribe_time = date ( 'Y年m月d日', $jsoninfo ["subscribe_time"] ); // 用户关注时间
				$wx_unionid = $jsoninfo ["unionid"]; // 用户机制，关注用户后可以得到
				/*
				 * 调用保存微信用户关注信息接口，将微信关注用户信息保存数据库
				 */
				$curlPost = 'subscribe=' . urlencode ( "$wx_subscribe" ) . '&openid=' . urlencode ( "$wx_openid" ) . '&nickname=' . urlencode ( "$wx_nickname" ) . '&sex=' . urlencode ( "$wx_sex" ) . '&city=' . urlencode ( "$wx_city" ) . '&country=' . urlencode ( "$wx_country" ) . '&province=' . urlencode ( "$wx_province" ) . '&language=' . urlencode ( "$wx_language" ) . '&headimgurl=' . urlencode ( "$wx_headimgurl" ) . '&subscribe_time=' . urlencode ( "$wx_subscribe_time" ) . '&unionid=' . urlencode ( "$wx_unionid" );
				$urlvoice = 'http://devwww.73go.cn/index.php/Mfront/WScheduleInterface/addSubscribeEvent';
				$public->http_request ( $curlPost, $urlvoice );
				$content ="欢迎您关注轻松行旅游";
				break;
			case "unsubscribe" : // 取消关注事件
				$wx_openid = $jsoninfo ["openid"]; // 用户昵称
				$user = M('user');
				$userdata['wx_openid']="$wx_openid";
				$userdata['status']=0;
				$result = $user->where($userdata)->find();//查询验证用户
				$id=$result["id"];
				$wxdata['status']=99;
				$user->where('id='.$id)->save($wxdata);
				break;
			case "CLICK":
				$content ='欢迎使用'.'"语音预订"功能,请切换到微信语音输入模式，您可以发送一条微信语音信息来说明您的预订需求'."\n"."\n".'为了更好地为您提供服务，请尽量使用标准的普通话来描述您的出行需求，语音示例：'."\n".'我要预订9月20号上午10点左右从深圳去北京的航班，入住建国门附近的北京万豪酒店,并于10月1日返回深圳.';
				break;
		}
		$result = $public->transmitText ( $object, $content );
		return $result;
	}
	/*
	 * 判断语音的合法性
	 */
	public function checkvoice() {
}
}
?>