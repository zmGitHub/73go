<?php
namespace Home\Controller;

use Home\Logic;
use System\LoginInfo;
use Think\Controller;
use Agent\Logic\NewDemandLogic;
use UMessage\Logic\UnifyMessage;
use UMessage\Logic\UnifyMessageSender;

/**
 * TMC
 * 企业：Enterprise
 * 方案：Scheme
 * 创建者：董发勇
 * 创建时间：2014-11-19上午09:28:47
 *
 */
class OrderSnatchController extends Controller {
    
	/**
	 * 抢单数据输出
	 * 创建者：张鹏
	 * 2014-11-19上午09:34:46
	 */
    public function showreq(){

		$orders = M('orders');
		$orderlist= $orders->where('snatch_status =0 and ticket_status = 0')->select();
		foreach($orderlist as $order){
			$flights = M('flight');
			$flight  = $flights->where("order_num='%s'",$order['order_num'])->select();
			$order['flight'] = $flight;
		}
		$this->ajaxreturn($order,'json');
    }
	/**
	 * OP抢单成功后待支付页面
	 * 创建者：张鹏
	 * 2015-3-17
	 */
	public function order_snatch() {
		$op_id = '1222';//I ('"session.userId"');
		$tmc_id = '1';//I ('"session.tmcId"');
		$map['op_id'] = $op_id;
		$map['tmc_id'] = $tmc_id;
		$map['snatch_status'] = 1;
		$map['ticket_status'] = 0;
		$order_num = 'O123456';//$_POST['order_num'];
		$orders = M ('orders');
		$save_success=$orders->where("order_num ='%s'",$order_num)->save($map);
		if($save_success){
		$this->ajaxreturn(1);
		}
		else{
			$this->ajaxreturn(1);
		}
	}

	/**
	 * OP抢单成功后待支付页面
	 * 创建者：张鹏
	 * 2015-3-17
	 */
	public function ticketneedpay() {
		$op_id = '1222';//I ('"session.userId"');
		$tmc_id =  '1';//I ('"session.tmcId"');
		$map['op_id'] = $op_id;
		$map['tmc_id'] = $tmc_id;
		$map['snatch_status'] = 1;
		$map['ticket_status'] = 0;
		$orders = M ('orders');
		$orderlists=$orders->where($map)->select();
		foreach($orderlists as $order){

			$flights = M('flight');
			$flight  = $flights->where("order_num='%s'",$order['order_num'])->select();
			$order['flight'] = $flight;
		}
		var_dump($order);//$this->ajaxreturn($order,'json');
	}
	/**
	 * OP抢单成功,客户支付完成待出票页面
	 * 创建者：张鹏
	 * 2015-3-17
	 */
	public function ticketprint() {
		$op_id = '1222';//I ('"session.userId"');
		$tmc_id = '1';//I ('"session.tmcId"');
		$map['op_id'] = $op_id;
		$map['tmc_id'] = $tmc_id;
		$map['snatch_status'] = 1;
		$map['ticket_status'] = 1;
		$orders = M ('orders');
		$orderlists=$orders->where($map)->select();
		foreach($orderlists as $order){
			$flights = M('flight');
			$flight  = $flights->where("order_num='%s'",$order['order_num'])->select();
			$order['flight'] = $flight;
		}
		$this->ajaxreturn($order,'json');
	}
	/**
	 * OP抢单成功,客户支付完成待出票页面
	 * 创建者：张鹏
	 * 2015-3-17
	 */
	public function ticketprinted() {
		$op_id = I ('"session.userId"');
		$tmc_id = I ('"session.tmcId"');
		$map['op_id'] = $op_id;
		$map['tmc_id'] = $tmc_id;
		$map['snatch_status'] = 1;
		$map['ticket_status'] = 2;
		$orders = M ('orders');
		$orderlists=$orders->where($map)->select();
		foreach($orderlists as $order){
			$flights = M('flight');
			$flight  = $flights->where("order_no='%s'",$order['order_no'])->select();
			$order['flight'] = $flight;
		}
		$this->ajaxreturn($order,'json');
	}
	/**
	 * OP电脑显示客户申请退票的页面
	 * 创建者：张鹏
	 * 2015-3-17
	 */
	public function refund() {
		$op_id = I ('"session.userId"');
		$tmc_id = I ('"session.tmcId"');
		$map['op_id'] = $op_id;
		$map['tmc_id'] = $tmc_id;
		$map['snatch_status'] = 1;
		$map['ticket_status'] = 3;
		$orders = M ('orders');
		$orderlists=$orders->where($map)->select();
		foreach($orderlists as $order){
			$flights = M('flight');
			$flight  = $flights->where("order_num='%s'",$order['order_num'])->select();
			$order['flight'] = $flight;
		}
		$this->ajaxreturn($order,'json');
	}
	/**
	 * OP电脑显示客户申请改签的页面
	 * 创建者：张鹏
	 * 2015-3-17
	 */
	public function ticketalter() {
		$op_id = I ('"session.userId"');
		$tmc_id = I ('"session.tmcId"');
		$map['op_id'] = $op_id;
		$map['tmc_id'] = $tmc_id;
		$map['snatch_status'] = 1;
		$map['ticket_status'] = 4;
		$orders = M ('orders');
		$orderlists=$orders->where($map)->select();
		foreach($orderlists as $order){
			$flights = M('flight');
			$flight  = $flights->where("order_num='%s'",$order['order_num'])->select();
			$order['flight'] = $flight;
		}
		$this->ajaxreturn($order,'json');
	}

	/**
	 * OP电脑显示所有失效的订单
	 * 创建者：张鹏
	 * 2015-3-17
	 */
	public function cancel() {
		$op_id = I ('"session.userId"');
		$tmc_id = I ('"session.tmcId"');
		$map['op_id'] = $op_id;
		$map['tmc_id'] = $tmc_id;
		$map['snatch_status'] = 3;
		$map['ticket_status'] = 99;
		$orders = M ('orders');
		$orderlists=$orders->where($map)->select();
		foreach($orderlists as $order){
			$flights = M('flight');
			$flight  = $flights->where("order_num='%s'",$order['order_num'])->select();
			$order['flight'] = $flight;
		}
		$this->ajaxreturn($order,'json');
	}



}