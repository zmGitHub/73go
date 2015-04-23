<?php
namespace UMessage\Model;

use Think\Model;

class UnifyMessageModel extends Model {
	protected $tableName = "unify_message";
	protected $tablePrefix = "73go_";
	protected $trueTableName = "73go_unify_message";

	/**
	 * 按状态获得消息记录列表。 
	 * @param string $status    状态
	 * @param boolean $timedesc 是否按时间倒序
	 */
	public function selectByStatus($status, $timedesc=true) {
		$cond['status'] = $status;
		$orderBy = 'op_time'.($timedesc ? ' DESC' : ''); 
		return $this->where($cond)->order($orderBy)->select();
	}

	/**
	 * 采用标识查找记录。
	 * @param int $id
	 */
	public function findById($id) {
		$cond['id'] = $id;
		return $this->where($cond)->find();
	}

	/**
	 * 按类型以及状态查找消息列表。
	 * @param string $msgType   消息类型
	 * @param string $status    状态
	 * @param boolean $timedesc 是否按时间倒序
	 */
	public function selectByType($msgType, $status='', $timedesc=true) {
		//若状态为空，则查找未发的消息
		if ($status === '') $status = C('73GO_UM_STATUS_NEW');
		if ($msgType != '')
			$cond['msg_type'] = $msgType;
		$cond['status'] = $status;
		$orderBy = 'op_time'.($timedesc ? ' DESC' : ''); 
		return $this->where($cond)->order($orderBy)->select();
	}

	/**
	 * 获取所有未发送的消息。
	 * 过滤条件：
	 * 1、不指定消息时间的，或当前时间已经超过了指定时间；
	 * 2、状态是“新”或者“发送中”,其中“发送中”的状态是由于已经分发给具体的发送接口，
	 *    但接口并没有返回正确发送反馈而导致，此时需要重发。
	 */
	public function getPendingMessages() {
		$cond['msg_time'] = array(
			array('exp', 'IS NULL'),
			array('exp', '< now()'),
			'OR'
		);
		$cond['status'] = array('exp', 'in ('.C('73GO_UM_STATUS_NEW').','.C('73GO_UM_STATUS_SENDING').')');
		return $this->where($cond)->select();
	}

	/**
	 * 发送中
	 * @param $msg
	 */
	public function messageSending($msg) {
		$data['status'] = C("73GO_UM_STATUS_SENDING");
		$cond['id'] = $msg['id'];
		$this->where($cond)->data($data)->save();
	}

	/**
	 * 发送成功
	 * @param $msg
	 */
	public function messageSent($msg) {
		$data['status'] = C("73GO_UM_STATUS_SENT");
		$data['sent_time'] = date('Y-m-d H:i:s');
		$cond['id'] = $msg['id'];
		$this->where($cond)->data($data)->save();
	}

}