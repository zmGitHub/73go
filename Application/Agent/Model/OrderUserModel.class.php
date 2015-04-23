<?php
namespace Agent\Model;

use Think\Model;

class OrderUserModel extends Model {
	/**
	 * 根据订单id查询订单使用者
	 * @param $orderId（订单id）
	 * 创建者：甘世凤
	 * 2014-12-21下午03:26:04
	 */
	public function getOrderUserByCode($orderId){
		$cond['o_id']=$orderId;
		return $this->where($cond)->select();
	}

	/**
	 * 删除关联指定订单id的记录
	 * 创建者：Lanny Lee
	 * 2014-12-25上午02:12:32
	 */
	public function deleteByOrderId($odrId) {
		$cond['o_id'] = $odrId;
		$this->where($cond)->delete();
	}
}