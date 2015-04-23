<?php
namespace Agent\Model;

use Think\Exception;
use Think\Model;

/**
 * 机票保险订单
 * Enter description here ...
 * @author xiaogan
 *
 */
class AirInsurInfoModel extends Model {
	
	/**
	 * 根据订单id及机票查询机票保险订单
	 * @param $orderId（订单id）,$a_id(机票订单id)
	 * 创建者：甘世凤
	 * 2014-12-21下午03:26:04
	 */
	public function getAirInsurByCode($orderId,$a_id){
		$cond['o_id']=$orderId;
		$cond['a_id']=$a_id;
		return $this->where($cond)->select();
	}

	/**
	 * 根据关联的飞机票详情信息id提取保险信息
	 * @param $a_id 机票详情信息id
	 * @return mixed 找到的数据
	 */
	public function getByFlightTicketInfoId($a_id) {
		$cond['a_id']=$a_id;
		return $this->where($cond)->find();
	}


}