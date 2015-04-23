<?php
namespace Agent\Model;

use Think\Model;

/**
 * 联合订单
 * 数据定义：
名称			代码			数据类型			长度		强制		注释
============================================================
联合订单ID	id			int						TRUE	
订单号		order_num	varchar(30)		30		TRUE	
金额	    	amount		float(18,2)		18		TRUE	
 * 
 * @author Lanny Lee
 *
 */
class OrderUnionModel extends Model {
	
	/**
	 * 使用数据ID获得数据。
	 * 创建者：Lanny Lee
	 * 2014-12-24上午11:32:36
	 */	
	public function getById($id) {
		$cond['id'] = $id;
		return $this->where($cond)->find();
	}
	
	/**
	 * 使用订单号获得数据。
	 * 创建者：Lanny Lee
	 * 2014-12-24上午11:45:18
	 */
	public function getByOrderNumber($orderNum) {
		$cond['order_num'] = $orderNum;
		return $this->where($cond)->find();
	}

	/**
	 * 判断给定的订单号是否联合订单
	 * 
	 * 返回：bool
	 * 创建者：Lanny Lee
	 * 2014-12-24上午11:46:02
	 */
	public function isUnionOrder($orderNum) {
		$cond['order_num'] = $orderNum;
		$data = $this->where($cond)->field('1')->select();
		if ($data) 
			return true;
		else 
			return false;
	}

	public function deleteById($id) {
		$cond['id'] = $id;
		$this->where($cond)->delete();
	}
	
}