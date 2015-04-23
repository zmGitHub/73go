<?php
namespace Agent\Model;

use Think\Model;

class OtherProduInfoModel extends OrderPartModel {
	/**
	 * 根据订单id查询其他订单
	 * @param $orderId（订单id）
	 * 创建者：甘世凤
	 * 2014-12-21下午03:26:04
	 */
	public function getOtherByCode($orderId){
		$cond['o_id']=$orderId;
		return $this->where($cond)->select();
	}

	/**
	 * 在删除自己之前，先删除相关记录。
	 * 创建者：Lanny Lee
	 * 2014-12-25上午03:21:52
	 */
	protected function deleteParentRelated($orderId) {
		$ids = $this->getIdsByOrderId($orderId);
		if (!empty($ids)) {
			$ids = array_values($ids);
			//其他订单使用者详情
			$this->doDeleteRelated('other_userinfo', 't_id', $ids);
		}		
	}

	/**
	 * 删除自身之前，先删除关联的其他订单用户详情信息。
	 * @param $id
	 */
	protected function deleteSelfRelated($id) {
		$this->doDeleteRelated('other_userinfo', 't_id', $id);
	}
}