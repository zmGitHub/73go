<?php
namespace Home\Controller;
use System\LoginInfo;
use Think\Controller;
use UMessage\Logic\UnifyMessage;
use UMessage\Logic\UnifyMessageSender;

class OrderDistributeController extends Controller{


    public function testAdd(){

        $account = $_POST['user'][0]['account'];

        $user = $_POST['user'];
        $passengers = $_POST['passengers'];//array('amount' => '1', 'PassengerList' => array('0' => array('name' => '张鹏', 'ordertype' => 'adult', 'card_type' => '身份证', 'card_id' => '350622198510301039')));//
        $passenger_amount = $_POST['passenger_amount'];
        $need_ticket= $_POST['need_ticket'];
        $phone = $_POST['phone'];
        $flight_type = $_POST['flight_type'];
        $flight = $_POST['flight'];//array('flight1' => array('flight_num' => 'MF8527', 'dcity' => '上海', 'acity' => '北京', 'dport' => '虹桥', 'aport' => '首都', 'dtime' => '2015-05-20 16:05:00', 'atime' => '2015-05-20 18:05:00', 'cabin_grade' => 'y', 'flight_price' => '500', 'adulttax' => '50', 'oilfee' => '50', 'acci_insu' => '50', 'delay_insu' => '50', 'total_price' => '720'));//
        $total_price = $_POST['full_price'];


        //生成 qsx订单号
        $num = _getOrderCode();
        $m_order = M('orders');
        $data['order_num'] = $num;
        $data['account'] = $account;
        $data['phone'] = $phone;
        $data['need_ticket'] = $need_ticket;
        $data['passenger_amount'] =$passenger_amount;//
        $data['refund_amount'] =0;//
        $data['total_price'] =$total_price;//
        $data['snatch_status'] = 0;
        $data['ticket_status'] = 0;
        $data['passengers'] = $passengers;
        $data['flight'] = $flight;
        $data['user'] = $user;
        var_dump($data);
    }

	/*订票数据存储到数据库，短信通知客户
	*author:zhangpeng
	*
	*/
	public function orderdistribute()
	{
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$account = $_POST['user'][0]['account'];
		$user = $_POST['user'];

		$passengers = $_POST['passengers'];//array('amount' => '1', 'PassengerList' => array('0' => array('name' => '张鹏', 'ordertype' => 'adult', 'card_type' => '身份证', 'card_id' => '350622198510301039')));//
		$passenger_amount = $_POST['passenger_amount'];
		$need_ticket= $_POST['need_ticket'];
		$phone = $_POST['phone'];
		$flight_type = $_POST['flight_type'];
		$flight = $_POST['flight'];//array('flight1' => array('flight_num' => 'MF8527', 'dcity' => '上海', 'acity' => '北京', 'dport' => '虹桥', 'aport' => '首都', 'dtime' => '2015-05-20 16:05:00', 'atime' => '2015-05-20 18:05:00', 'cabin_grade' => 'y', 'flight_price' => '500', 'adulttax' => '50', 'oilfee' => '50', 'acci_insu' => '50', 'delay_insu' => '50', 'total_price' => '720'));//
		$total_price = $_POST['full_price'];

		if($flight_type == 'S'){
			$trip_type = 1;
			$ticket_amount = $passenger_amount;
		}else{
			$trip_type = 2;
			$ticket_amount = $passenger_amount *2;
		}

		//生成 qsx订单号
		$num = $this->_getOrderCode();
		$m_order = M('orders');
		$data['order_num'] = $num;
		$data['account'] = $account;
		$data['phone'] = $phone;
		$data['trip_type'] = $trip_type;
		//航班信息
		$data['dpt_dtime'] = $flight['flight1']['dtime'];
		$data['dpt_flight_num'] = $flight['flight1']['flight_num'];
		$data['dpt_origin_price'] = $flight['flight1']['origin_price'];
		$data['dpt_flight_price'] = $flight['flight1']['flight_price'];
		$data['rtn_dtime'] = $flight['flight2']['dtime'];
		$data['rtn_flight_num'] = $flight['flight2']['flight_num'];
		$data['rtn_origin_price'] = $flight['flight2']['origin_price'];
		$data['rtn_flight_price'] = $flight['flight2']['flight_price'];
		//订单信息
		$data['need_ticket'] = $need_ticket;
		$data['ticket_amount'] = $ticket_amount;

		$data['refund_amount'] =0;//
		$data['total_price'] =$total_price;//
		$data['snatch_status'] = 0;
		$data['ticket_status'] = 0;
		$time  =  date ( "Y-m-d H:i:s" );
		$data['create_time'] = $time;
		$order_add = $m_order->add($data);
		//写入订单信息

		$m_flight = M('flight');
		if ($flight_type == 'S') {
			//单程
			for ($i = 0; $i < $passenger_amount; $i++) {
				$flight['flight1']['passenger'] = $passengers[$i]['name'];
				$flight['flight1']['id_type'] = $passengers[$i]['idType'];
				$flight['flight1']['card_id'] = $passengers[$i]['idNumber'];
				$flight['flight1']['account'] = $account;
				$flight['flight1']['order_num'] = $num;
				$data =$flight['flight1'];
				$flight1_result = $m_flight->add($data);
			}
		} else {
			//往返行程
			//去程
			for ($i = 0; $i < $passenger_amount; $i++) {
				$flight['flight1']['passenger'] = $passengers[$i]['name'];
				$flight['flight1']['id_type'] = $passengers[$i]['idType'];
				$flight['flight1']['card_id'] = $passengers[$i]['idNumber'];
				$flight['flight1']['account'] = $account;
				$flight['flight1']['order_num'] = $num;
				$map = $flight['flight1'];
				$flight1_result = $m_flight->add($map);
				//返程
				$flight['flight2']['passenger'] = $passengers[$i]['name'];
				$flight['flight2']['id_type'] = $passengers[$i]['idType'];
				$flight['flight2']['card_id'] = $passengers[$i]['idNumber'];
				$flight['flight2']['account'] = $account;
				$flight['flight2']['order_num'] = $num;
				$flight2_result = $m_flight->add($flight['flight2']);
			}
		}

		$tmc= M('tmc');
		$tmc_info=$tmc->where('cert_val=1')->select();

		if($order_add){

			foreach($tmc_info as $key=>$vo){
				//发送短信和邮件  郭攀  2015.3.2
				//将需求单分发给所有TMC企业，给所有TMC企业发送新需求单的请求


				//查询出提出人所在的公司
				//根据tmcID公司，查询好出tmc 公司下所有tmc员工的邮箱和电话
				$Temp=M("operator");
				$Tmc_emp=$Temp->where("tmc_id=".$vo['tmc_id'])->select();

				foreach($Tmc_emp as $kk=>$vv){
					//查询出所有op员工对应用户表中的 wx_openid ,进行微信的发送信息的添加
					//郭攀
						$types = array(2) ;
						$targets = array (2 =>$vv['email']);
					//$targets = array (	1 => $vv['phone'],2 =>$vv['email']);
					$sender[$key] = new UnifyMessageSender ();
					//$types = array(1,2);
					$title =  '通知OP抢单';
					$text =  $vv['op_name'].':'.$passengers['PassengerList'][0]['name']."发出".$flight['flight1']['dtime']."乘坐".$flight['flight1']['flight_num']."航班的需求。请尽快刷新页面抢单。";
					$html = $vv['op_name'].'：<br />.'.
						$passengers['PassengerList'][0]['name']."发出".$flight['flight1']['dtime']."乘坐".$flight['flight1']['flight_num']."航班的需求。请尽快刷新页面抢单。";

					$um[$key] = UnifyMessage::NewUnionMessage ( $types,$targets,$title,$text, $html );
					$sender[$key]->sendUMessage ($um[$key]);
				}
			}
            $this->ajaxReturn($num);
		}else{
            $this->ajaxReturn(-1);
		}

	}
	//乘客所有的订单信息
	public function whole_order(){

		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$account =$_POST['account'];//与LI方法等价
		$map['account'] = $account;
		$m_orders = M('orders');
		$orderlists=$m_orders->where($map)->select();
		$this->_updateticket($orderlists);
		$order_count = count($orderlists);
		for($i=0;$i<$order_count;$i++){
			$flights = M('flight');
			$flight[$i]  = $flights->where("order_num='%s'",$orderlists[$i]['order_num'])->select();
				$orderlists[$i]['flight'] = $flight[$i];
		}
		$this->ajaxreturn($orderlists,'JSON');
	}
	//乘客所有未支付的订单信息
	public function ticket_paying(){
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$account = $_POST['account'];//与LI方法等价
		$map['account'] = $account;
		$map['snatch_status'] = 1;
		$map['ticket_status'] = 0;
		$m_orders = M('orders');
		$orderlists=$m_orders->where($map)->select();
		$this->_updateticket($orderlists);
		$now_time = time();
		foreach($orderlists as $key=>$order){
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
	//乘客所有未出行的订单信息,包括已出票和未出票
	public function no_travel(){
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$account = $_POST['account'];//与LI方法等价
		$map['account'] = $account;
		$map['snatch_status'] = 1;
		$map['ticket_status'] = array('exp','IN(1,2)');
		$m_orders = M('orders');
		$orderlists=$m_orders->where($map)->select();
		$order_count = count($orderlists);
		for($i=0;$i<$order_count;$i++){
			$flights = M('flight');
			$flight[$i]  = $flights->where("order_num='%s'",$orderlists[$i]['order_num'])->select();
			$order[$i]['flight'] = $flight[$i];
		}
		$this->ajaxreturn($order,'JSON');
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
	private function _getOrderCode(){
		$code = rand(1000000,9999999);
		$m_order= M('orders');
		$pinfo=$m_order->where('order_num='.$code)->select();
		if($pinfo){
			$this->_getCompanyCode();
		}else{
			return $code;
		}
	}



}

