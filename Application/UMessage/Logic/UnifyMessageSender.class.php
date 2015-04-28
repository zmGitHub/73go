<?php
/**
 * 消息发送接口。
 * 业务代码调用此类的方法进行消息发送。
 * 此类只将消息内容写到数据表中，不负责消息的真正发送。
 */


namespace UMessage\Logic;

use UMessage\Model\UnifyMessageModel;

class UnifyMessageSender extends UnifyMessageModel {
	
	/**
	 * 发送消息。
	 * 统一的发送方法。需要发送的内容，若独立内容通过调用
	 * UnifyMessage::NewSMS或者UnifyMessage::NewEMail或者UnifyMessage::NewWechat获得；
	 * 集成内容通过调用UnifyMessage::NewUnionMessage方法获得。
	 * @param UnifyMessage $um
	 */	
	public function sendUMessage($um) {
		//判断是否集成消息
		$multiMsg = is_array($um->msgType);
		
		if ($multiMsg) {
			//是集成消息，则保存多条记录
			foreach ($um->msgType as $type) {
				
				$data['msg_type'] = $type;
				$data['msg_target'] = $um->msgTarget[$type];
				if ($data['msg_target'] != '') { //确实有这个类型的目标
					$data['msg_title'] = $um->msgTitle;
					if ($um->msgText === '') unset($data['msg_text']);
					else $data['msg_text'] = $um->msgText;
					if ($um->msgHtml === '') unset($data['msg_html']);
					else $data['msg_html'] = $um->msgHtml;
					if ($um->msgTime === '') unset($data['msg_time']);
					else $data['msg_time'] = $um->msgTime;
					if ($um->replyTo === '') unset($data['reply_to']);
					else $data['reply_to'] = $um->replyTo;
					$data['op_time'] = date("Y-m-d H:i:s");
					$data['status'] = C('73GO_UM_STATUS_NEW');
					//$m_message = D('unify_message');
					//$result = $m_message->add($data);
					$result=$this->data($data)->add();
					//return $result;
				}
			}
		} else {
			//独立消息，保存一条记录
			$data['msg_type'] = $um->msgType;
			$data['msg_target'] = $um->msgTarget;
			$data['msg_title'] = $um->msgTitle;
			if ($um->msgText === '') unset($data['msg_text']);
			else $data['msg_text'] = $um->msgText;
			if ($um->msgHtml === '') unset($data['msg_html']);
			else $data['msg_html'] = $um->msgHtml;
			if ($um->msgTime === '') unset($data['msg_time']);
			else $data['msg_time'] = $um->msgTime;
			if ($um->replyTo === '') unset($data['reply_to']);
			else $data['reply_to'] = $um->replyTo;
			$data['op_time'] = date("Y-m-d H:i:s");
			$data['status'] = C('73GO_UM_STATUS_NEW');
			$this->data($data)->add();
		}

		//添加实时触发发送消息 2015-3-11 david law
		$dipatcher = new UnifyMessageDispatcher();
		$dipatcher->sendPendingMessages();
	}
	
}