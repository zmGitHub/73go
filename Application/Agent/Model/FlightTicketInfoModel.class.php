<?php
namespace Agent\Model;

use Think\Model;

/**
 * 机票订单详情
 * Enter description here ...
 * @author xiaogan
 *
 */
class FlightTicketInfoModel extends OrderPartModel {
	
	/**
	 * 根据订单id查询机票订单详情
	 * @param $orderId（订单id）
	 * 创建者：甘世凤
	 * 2014-12-21下午03:26:04
	 */
	public function getFlightByOrderId($orderId){
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
			//机票使用者详情
			$this->doDeleteRelated('flight_userinfo', 'h_id', $ids);
			//航空险保单详情
			$this->doDeleteRelated('air_insur_info', 'a_id', $ids);
		}		
	}

	/**
	 * 删除自身之前，先删除关联的机票订单使用者详情和航空险保单详情。
	 * @param $id
	 */
	protected function deleteSelfRelated($id) {
		//机票使用者详情
		$this->doDeleteRelated('flight_userinfo', 'h_id', $id);
		//航空险保单详情
		$this->doDeleteRelated('air_insur_info', 'a_id', $id);
	}

}