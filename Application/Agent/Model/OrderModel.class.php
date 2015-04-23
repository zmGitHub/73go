<?php
namespace Agent\Model;

use Think\Model;

/**
 * 订单Model。
 * 
 * 数据定义：
名称			代码			    数据类型		长度		强制		注释
订单ID		id				int					TRUE	
联合订单ID	uodr_id			int					FALSE	可为空。
														若不为空，则表示该订单属于这个ID下的联合订单。
订单号		order_num		varchar(30)	30		TRUE	
订单来源ID	src_id			int					FALSE	自助预订=> 空  
														轻松行需求方案=> “方案”表的“方案ID” 
														OP添加=>“TMC企业员工”表的“员工ID”
订单来源		src				varchar(2)	2		TRUE	字典表：order_src
出差申请号	tr_num			varchar(30)	30		FALSE	TR号
订单时间		time			datetime			TRUE	
预订企业ID	co_id			int					FALSE	
预订人ID		u_id			int					FALSE	
金额			amount			float(18,2)	18		TRUE	
服务费		service_price	float(18,2)	18		FALSE	
结算类型		pay_type		varchar(2)	2		TRUE	机票=>1 酒店=>2 火车票=>3  其他=>待定，
														需要能支持扩展
TMC ID		tmc_id			int					TRUE	
TMC 员工ID	tmc_uid			int					TRUE	需要是tmc员工
TMC员工姓名	tmc_uname		varchar(30)	30		FALSE	
TMC备注信息	tmc_note		text				FALSE	
订单状态		status			varchar(2)	2		TRUE	字典表：order_status
 * 
 * 
 * 
 * Enter description here ...
 * @author xiaogan
 *
 */
class OrderModel extends Model {
	/**
	 * 根据订单id查询订单
	 * @param $orderId（订单id）,$uodrId联合订单id
	 * 创建者：甘世凤
	 * 2014-12-21下午03:26:04
	 */
	public function getOrderByCode($orderId,$uodrId){
		$cond['o_id']=$orderId;
		$cond['uodr_id']=$uodrId;
		return $this->where($cond)->select();
	}
	

	/**
	 * 按照给定的数据ID获得数据
	 * @param $id 记录ID 
	 * 创建者：Lanny Lee
	 * 2014-12-24上午11:49:46
	 */
	public function getById($id) {
		$cond['id'] = $id;
		return $this->where($cond)->find();
	}

	/**
	 * 使用订单号获得数据。
	 * 创建者：Lanny Lee
	 * 2014-12-24上午11:52:01
	 */
	public function getByOrderNumber($orderNum) {
		$cond['order_num'] = $orderNum;
		return $this->where($cond)->find();		
	}
	
	/**
	 * 判断给定的订单号是否订单
	 * 
	 * 返回：bool
	 * 创建者：Lanny Lee
	 * 2014-12-24上午11:57:06
	 */
	public function isOrder($orderNum) {
		$cond['order_num'] = $orderNum;
		$data = $this->where($cond)->field('1')->select();
		if ($data) 
			return true;
		else 
			return false;
	}
	
	/**
	 * 根据联合订单ID获得订单数据列表
	 * 创建者：Lanny Lee
	 * 
	 * @param int $soId 联合订单的ID
	 * @return 订单数据
	 * 2014-12-24下午10:02:13
	 */
	public function getByOUId($ouId) {
		$cond['uodr_id'] = $ouId;
		return $this->where($cond)->select();
	}
	
	/**
	 * 按订单id删除数据
	 * 创建者：Lanny Lee
	 * 2014-12-25上午01:41:03
	 */
	public function deleteById($orderId) {
		$cond['id'] = $orderId;
		$this->where($cond)->delete();
	}

	public function saveFromUIData($uiData) {

	}
	
	
}