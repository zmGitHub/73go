<?php
namespace Home\Controller;
use System\LoginInfo;
use Think\Controller;
use UMessage\Logic\UnifyMessage;
use UMessage\Logic\UnifyMessageSender;

class OrderDistributeController extends Controller{


	/*订票数据存储到数据库，短信通知客户
	*author:zhangpeng
	*
	*/
	public function orderdistribute()
	{
		$account = I("session.LoginInfo");//与LI方法等价
		$order = $_POST['order'];
		$order_info = json_decode($order, true);
		$user = $order_info['user'];
		$passengers = $order_info['passengers'];//array('amount' => '1', 'PassengerList' => array('0' => array('name' => '张鹏', 'ordertype' => 'adult', 'card_type' => '身份证', 'card_id' => '350622198510301039')));
		$phone = $order_info['phone'];
		$flight_type = $order_info['flight_type'];
		$flight = $order_info['flight'];//array('flight1' => array('flight_num' => 'MF8527', 'dcity' => '上海', 'acity' => '北京', 'dport' => '虹桥', 'aport' => '首都', 'dtime' => '2015-05-20 16:05:00', 'atime' => '2015-05-20 18:05:00', 'cabin_grade' => 'y', 'flight_price' => '500', 'adulttax' => '50', 'oilfee' => '50', 'acci_insu' => '50', 'delay_insu' => '50', 'total_price' => '720'));//
		$passenger_amount = $passengers['amount'];//1;//


		//生成 qsx订单号
		$num = VNumGen('tmc_code');
		$m_order = M('orders');
		$data['order_num'] = $num;
		$data['account'] = $account;
		$data['phone'] = $phone;
		$data['need_ticket'] = $order_info['need_ticket'];
		$data['snatch_status'] = 0;
		$data['ticket_status'] = 0;
		$order_add = $m_order->add($data);
		//写入订单信息

		$m_flight = M('flight');
		if ($flight_type == 'S') {
			//单程
			for ($i = 0; $i < $passenger_amount; $i++) {
				$flight['flight1']['passenger'] = '张鹏';//$passengers['passengerList'][$i]['name'];
				$flight['flight1']['id_type'] = '身份证';//$passengers['passengerList'][$i]['id_type'];
				$flight['flight1']['card_id'] = '350622198510301039';//$passengers['passengerList'][$i]['card_id'];
				$flight['flight1']['account'] = '18857166486';//$user['account'];
				$flight['flight1']['order_num'] = $num;
				$map = $flight['flight1'];
				$flight1_result = $m_flight->add($map);
			}
		} else {
			//往返行程
			//去程
			for ($i = 0; $i < $passenger_amount; $i++) {
				$flight['flight1']['passenger'] = $passengers['passengerList'][$i]['name'];
				$flight['flight1']['id_type'] = $passengers['passengerList'][$i]['id_type'];
				$flight['flight1']['card_id'] = $passengers['passengerList'][$i]['card_id'];
				$flight['flight1']['account'] = $user['account'];
				$flight['flight1']['order_num'] = $num;
				$map = $flight['flight1'];
				$flight1_result = $m_flight->add($map);
				//返程
				$flight['flight2']['passenger'] = $passengers['passengerList'][$i]['name'];
				$flight['flight2']['id_type'] = $passengers['passengerList'][$i]['id_type'];
				$flight['flight2']['card_id'] = $passengers['passengerList'][$i]['card_id'];
				$flight['flight2']['account'] = $user['account'];
				$flight['flight2']['order_num'] = $num;
				$flight2_result = $m_flight->add($flight['flight2']);
			}
		}





		$tmc= M('tmc');
		$tmc_info=$tmc->where('cert_val=1')->select();

		if($order_add){

			// 发送短信和邮件  郭攀  2015.3.2
			// 企业员工出差需求提交成功通知
			// 调用Logic/ApprovalLogic.class.php
			//$map2['password'] = md5($newpassword);
			//if ($type == 1) {//判断帐号的类型 1、普通用户  2、企业 3、tmc 4、op
			// $emp = M('employee');
			// $emp_phone = $emp->where($map1)->getField('phone');
			// if ($emp_phone == $phone) {
			//     $user->where("id=" . $id)->save($map2);
			//预订成功给客户发送消息，
			/*$send = D("Home/SendMessage", "Logic");
			$case = "FlightBook";
			$datt['user_phone'] = $phone;
			$datt['wx_openid']= $user['wx_openid'];
			$datt['email']= $user['email'];
			$datt['num'] =$num;
			$datt['name'] =$passengers['passengerList'][0]['name'];
			$datt['flight_no'] = $flight['flight1']['flight_no'];//此处仅考虑单程。双程的待修改
			$datt['begin_time'] = $flight['flight1']['dtime'];
			$send->SendDetails($case, $datt);
			*/



			foreach($tmc_info as $key=>$vo){
				//发送短信和邮件  郭攀  2015.3.2
				//将需求单分发给所有TMC企业，给所有TMC企业发送新需求单的请求


				//查询出提出人所在的公司
				//根据tmcID公司，查询好出tmc 公司下所有tmc员工的邮箱和电话
				$Temp=M("oparator");
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
					$text =  $vv['name'].':'.$passengers['passengerList'][0]['name']."发出".$flight['flight1']['dtime']."乘坐".$flight['flight1']['flight_no']."航班的需求。请尽快刷新页面抢单。";
					$html = $vv['name'].'：<br />.'.
						$passengers['passengerList'][0]['name']."发出".$flight['flight1']['dtime']."乘坐".$flight['flight1']['flight_no']."航班的需求。请尽快刷新页面抢单。";

					$um[$key] = UnifyMessage::NewUnionMessage ( $types,$targets,$title,$text, $html );
					$sender[$key]->sendUMessage ($um[$key]);
				}
			}
            $this->ajaxReturn(1);
		}else{
            $this->ajaxReturn(-1);
		}

	}






}

