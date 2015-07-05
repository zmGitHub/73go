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

        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$orders = M('orders');
		$orderlist= $orders->where('snatch_status =0 and ticket_status = 0')->select();
		$order_count = count($orderlist);
		for($i=0 ;$i<$order_count;$i++){
			$flights = M('flight');
			$flight[$i]  = $flights->where("order_num='%s'",$orderlist[$i]['order_num'])->select();
			$orderlist[$i]['flight'] = $flight[$i];
		}
		$this->ajaxreturn($orderlist,'json');
    }
	/**
	 * OP抢单成功更新数据库
	 * 创建者：张鹏
	 * 2015-3-17
	 */
	public function order_snatch() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$op_id = $_POST['op_id'];
		$tmc_id = $_POST['tmc_id'];
		$map['op_id'] = $op_id;
		$map['tmc_id'] = $tmc_id;
		$map['snatch_status'] = 1;
		$map['ticket_status'] = 0;
		$order_num = $_POST['order_num'];
		$orders = M ('orders');
		$save_success=$orders->where("order_num ='%s'",$order_num)->save($map);
		if($save_success){
		    $this->ajaxreturn(1);
		}
		else{
			$this->ajaxreturn(0);
		}
	}

	/**
	 * OP待支付页面
	 * 创建者：张鹏
	 * 2015-3-17
	 */
	public function ticketpaying() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$op_id = $_POST['op_id'];
		$tmc_id = $_POST['tmc_id'];
		$map['op_id'] = $op_id;
		$map['tmc_id'] = $tmc_id;
		$map['snatch_status'] = 1;
		$map['ticket_status'] = 0;
		$orders = M ('orders');
		$orderlist=$orders->where($map)->select();
		$this->_updateticket($orderlist);
		$now_time = time();
		foreach($orderlist as $key=>$order){
			$create_time = strtotime($order['create_time']);
			if(($now_time - $create_time < 1800) &&($order['snatch_status'] ==1) &&($order['ticket_status'] ==0)){
				$unpaylist[$key] = $order;
			}
		}
		foreach($unpaylist as $pkey=> $unpayorder){
			$flights = M('flight');
			$flight = $flights->where("order_num='%s'",$unpayorder['order_num'])->select();
			$unpaylist[$pkey]['flight'] = $flight[$pkey];
		}
		$this->ajaxreturn($unpaylist,'JSON');
	}
	/**
	 * OP抢单成功,客户支付完成待出票页面
	 * 创建者：张鹏
	 * 2015-3-17
	 */
	public function ticketprint() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$op_id = $_POST['op_id'];
		$tmc_id =$_POST['tmc_id'];
		$map['op_id'] = $op_id;
		$map['tmc_id'] = $tmc_id;
		$map['snatch_status'] = 1;
		$map['ticket_status'] = 1;
		$orders = M ('orders');
		$orderlist=$orders->where($map)->select();
		$order_count = count($orderlist);
		for($i=0 ;$i<$order_count;$i++){
			$flights = M('flight');
			$flight[$i]  = $flights->where("order_num='%s'",$orderlist[$i]['order_num'])->select();
			$orderlist[$i]['flight'] = $flight[$i];
		}
		$this->ajaxreturn($orderlist,'json');
	}

	/**
	 * OP出票状态更新
	 * 创建者：张鹏
	 * 2015-3-17
	 */
	public function ticketprinting() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$op_id = $_POST['op_id'];
		$tmc_id = $_POST['tmc_id'];
		$map['op_id'] = $op_id;
		$map['tmc_id'] = $tmc_id;
		$map['snatch_status'] = 1;
		$map['ticket_status'] = 2;
		$order_num = $_POST['order_num'];
		$orders = M ('orders');
		$save_success=$orders->where("order_num ='%s'",$order_num)->save($map);
		if($save_success){
			$this->ajaxreturn(1);
		}
		else{
			$this->ajaxreturn(0);
		}
	}
	/**
	 * OP抢单成功,客户支付完成已出票页面
	 * 创建者：张鹏
	 * 2015-3-17
	 */
	public function ticketprinted() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$op_id = $_POST['op_id'];
		$tmc_id = $_POST['tmc_id'];
		$map['op_id'] = $op_id;
		$map['tmc_id'] = $tmc_id;
		$map['snatch_status'] = 1;
		$map['ticket_status'] = 2;
		$orders = M ('orders');
		$orderlist=$orders->where($map)->select();
		$order_count = count($orderlist);
		for($i=0 ;$i<$order_count;$i++){
			$flights = M('flight');
			$flight[$i]  = $flights->where("order_num='%s'",$orderlist[$i]['order_num'])->select();
			$orderlist[$i]['flight'] = $flight[$i];
		}
		$this->ajaxreturn($orderlist,'json');
	}
	/**
	 * OP电脑显示客户申请退票的页面
	 * 创建者：张鹏
	 * 2015-3-17
	 */
	public function refund() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$op_id = $_POST['op_id'];
		$tmc_id = $_POST['tmc_id'];
		$map['op_id'] = $op_id;
		$map['tmc_id'] = $tmc_id;
		$map['snatch_status'] = 1;
		$map['ticket_status'] = 3;
		$orders = M ('orders');
		$orderlist=$orders->where($map)->select();
		$order_count = count($orderlist);
		for($i=0 ;$i<$order_count;$i++){
			$flights = M('flight');
			$flight[$i]  = $flights->where("order_num='%s'",$orderlist[$i]['order_num'])->select();
			$orderlist[$i]['flight'] = $flight[$i];
		}
		$this->ajaxreturn($orderlist,'json');
	}
	/**
	 * OP电脑显示客户申请改签的页面
	 * 创建者：张鹏
	 * 2015-3-17
	 */
	public function ticket_alter() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$op_id = $_POST['op_id'];
		$tmc_id = $_POST['tmc_id'];
		$map['op_id'] = $op_id;
		$map['tmc_id'] = $tmc_id;
		$map['snatch_status'] = 1;
		$map['ticket_status'] = 4;
		$orders = M ('orders');
		$orderlist=$orders->where($map)->select();
		$order_count = count($orderlist);
		for($i=0 ;$i<$order_count;$i++){
			$flights = M('flight');
			$flight[$i]  = $flights->where("order_num='%s'",$orderlist[$i]['order_num'])->select();
			$orderlist[$i]['flight'] = $flight[$i];
		}
		$this->ajaxreturn($orderlist,'json');
	}

	/**
	 * OP电脑显示所有失效的订单
	 * 创建者：张鹏
	 * 2015-3-17
	 */
	public function cancel() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$op_id = $_POST['op_id'];
		$tmc_id = $_POST['tmc_id'];
		$map['op_id'] = $op_id;
		$map['tmc_id'] = $tmc_id;
		$map['snatch_status'] = 1;
		$map['ticket_status'] = 99;
		$orders = M ('orders');
		$orderlist=$orders->where($map)->select();
		$order_count = count($orderlist);
		for($i=0 ;$i<$order_count;$i++){
			$flights = M('flight');
			$flight[$i]  = $flights->where("order_num='%s'",$orderlist[$i]['order_num'])->select();
			$orderlist[$i]['flight'] = $flight[$i];
		}
		$this->ajaxreturn($orderlist,'json');
	}
	private function _updateticket($orderlists){
		$now_time = time();
		foreach($orderlists as $key=>$order){
			$create_time = strtotime($order['create_time']);
			if(($now_time - $create_time > 1800) &&($order['snatch_status'] ==1) &&($order['ticket_status'] ==0)){
				$outdateList[$key] = $order;
			}
		}
		foreach ($outdateList as $outdateorder){
			$m_orders = M('orders');
			$outdateorder['ticket_status'] = 99;
			$new_map['order_num'] = $outdateorder['order_num'];
			$m_orders->where($new_map)->save($outdateorder);
		}
	}
}