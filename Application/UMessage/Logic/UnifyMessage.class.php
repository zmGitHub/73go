<?php
namespace UMessage\Logic;

/**
 * 消息内容封装类。
 * @author Lanny Lee
 */
class UnifyMessage {

	/**
	 * 消息类型
	 * @var mixed 若为array，则每个项是一个类型；若为string，则是单一类型
	 * @see "73GO 消息类型"@config.php 
	 */
	public $msgType = '';
	/**
	 * 消息目标（手机号码/电子邮箱/微信号）
	 * @var mixed 若为数组，则'1'=>手机号, '2'=>电子邮箱, '3'=>微信号；否则则为string, 单一消息目标
	 * @see "73GO 消息类型"@config.php
	 */
	public $msgTarget = '';
	/**
	 * 消息标题（邮件）
	 * @var string
	 */
	public $msgTitle = '';
	/**
	 * 消息纯文本内容
	 * @var string
	 */
	public $msgText = '';
	/**
	 * 消息HTML内容
	 * @var string
	 */
	public $msgHtml = '';
	/**
	 * 消息是否定时发送。
	 * @var string 'Y-m-d H:i:s'格式，为空则是即时发送
	 */
	public $msgTime = '';
	/**
	 * 邮件的回复地址。
	 */
	public $replyTo = '';
	
	/**
	 * 创建一个短信内容封装
	 * @param string $target  电话号码
	 * @param string $text    文本内容
	 * @param string $time    定时发送时间，参考$msgTime的定义
	 */
	public static function NewSMS($target, $text, $time='') {
		$um = new UnifyMessage();
		$um->msgType = C('73GO_UM_SMS');
		$um->msgTarget = $target;
		$um->msgText = $text;
		if ($time != '') $um->msgTime = $time;
		return $um;
	}

	/**
	 * 创建一个邮件内容封装，只支持HTML内容。
	 * 若发纯文本邮件，请自行创建。
	 * @param string $target  电子邮箱
	 * @param string $title   邮件标题
	 * @param string $html    邮件内容
	 * @param string $time    定时发送时间，参考$msgTime的定义
	 * @param string $replyTo 邮件的回复地址
	 */
	public static function NewEMail($target, $title, $html, $time='', $replayTo='') {
		$um = new UnifyMessage();
		$um->msgType = C('73GO_UM_EMAIL');
		$um->msgTarget = $target;
		$um->msgTitle = $title;
		$um->msgHtml = $html;
		if ($time != '') $um->msgTime = $time;
		if ($replayTo != '') $um->replayTo = $replayTo;
		return $um;
	}
	
	/**
	 * 创建一个微信内容封装，只支持HTML内容。
	 * 若发纯文本内容，请自行创建。
	 * @param string $target  微信号
	 * @param string $html    邮件内容
	 * @param string $time    定时发送时间，参考$msgTime的定义
	 */
	public static function NewWechat($target, $html, $time='') {
		$um = new UnifyMessage();
		$um->msgType = C('73GO_UM_WECHAT');
		$um->msgTarget = $target;
		$um->msgHtml = $html;
		if ($time != '') $um->msgTime = $time;
		return $um;
	}
	
	/**
	 * 创建一个集成消息封装。
	 * @param array $types    类型数组，
	 * @param array $targets  目标数组，'1'=>手机号, '2'=>电子邮箱, '3'=>微信号
	 * @param string $title 标题
	 * @param string $text  文本内容
	 * @param string $html  HTML内容
	 * @param string $time  定时发送时间，格式"Y-m-d H:i:s"。为空则是立即发送
	 * @param string $replyTo 邮件的回复地址
	 */
	public static function NewUnionMessage($types, $targets, $title, $text, $html, 
		$time='', $replayTo='') {
		$um = new UnifyMessage();
		$um->msgType = $types;
		$um->msgTarget = $targets;
		$um->msgTitle = $title;
		$um->msgText = $text;
		$um->msgHtml = $html;
		if ($time != '') $um->msgTime = $time;
		if ($replayTo != '') $um->replayTo = $replayTo;
		return $um;
	}
	
}