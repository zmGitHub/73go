<?php
namespace Agent\Controller;
use Think\Controller;

class TmcOrderController extends Controller {
	
	/**
	 * 
	 * 注意此Controller按顺序分为若干部分：
	 * 第一部分：加载页面
	 * 第二部分：对订单（order）的OP操作
	 * 第三部分：对机票状态（73go_flight_ticket_info  status）的OP操作
	 * 第四部分：对酒店状态（73go_hotel_info          status）的OP操作
	 * 第五部分：对火车状态（73go_train_ticket_info   status）的OP操作
	 * 第六部分：对其他状态（73go_other_produ_info    status）的OP操作
	 * 第七部分：对保险状态（73go_air_insur_info      status）的OP操作
	 * 第八部分：其他操作
	 * 
	 * 注：保险的“已取消”的OP操作未完成
	 * 
	 */
	
	
	
	
/***********   第一部分：加载页面      ****************/	
	//加载订单所有的页面（order_list_all）页面
	public function  order_list_all(){
		//加载布局文件
		layout("tmc");
		$id = LI('userId');
		$order = D('Agent/TmcOrder', 'Logic');
		$Page = 0;
		$result = $order->queryOrder($id,$Page,$_POST);
		$count=count($result);
		$Page  = new \Think\Page($count,5);
		$show       = $Page->show();// 分页显示输出
		$result = $order->queryOrder($id,$Page,$_POST);
		$this->assign('Page',$show);// 赋值分页输出
		$this->assign('order',$result);
		$this->assign('start_date',$_POST['start_date']);
		$this->assign('end_date',$_POST['end_date']);
		$this->assign('keywords',$_POST['keywords']);
		$this->theme("agent")->display("order_list");

	}
	//加载订单待支付的页面（order_list_pay）页面
	public function order_list_pay(){
	
		//加载布局文件
		layout("tmc");
		$id = LI('userId');
		$order = D('Agent/TmcOrder', 'Logic');
		$Page = 0;
		$result = $order->queryNotPay($id,$Page,$_POST);
		$count=count($result);
		
		$Page  = new \Think\Page($count,5);
		$show       = $Page->show();// 分页显示输出
		$result = $order->queryNotPay($id,$Page,$_POST);
		$this->assign('Page',$show);// 赋值分页输出
		$this->assign('order',$result);
		$this->assign('start_date',$_POST['start_date']);
		$this->assign('end_date',$_POST['end_date']);
		$this->assign('keywords',$_POST['keywords']);
		$this->theme("agent")->display("order_list_pay");

	}
	
	//加载订单待确认的页面（order_list_confirm）页面
	public function  order_list_confirm(){
		//加载布局文件
		layout("tmc");
		$id = LI('userId');
		$order = D('Agent/TmcOrder', 'Logic');
		$Page = 0;
		$result = $order->queryNotConfirm($id,$Page,$_POST);
		$count=count($result);
		
		$Page  = new \Think\Page($count,5);
		$show       = $Page->show();// 分页显示输出
		$result = $order->queryNotConfirm($id,$Page,$_POST);
		$this->assign('Page',$show);// 赋值分页输出
		$this->assign('order',$result);
		$this->assign('start_date',$_POST['start_date']);
		$this->assign('end_date',$_POST['end_date']);
		$this->assign('keywords',$_POST['keywords']);
		$this->theme("agent")->display("order_list_confirm");

	}	
	
	//加载订单已取消的页面（order_list_cancel）页面
	public function  order_list_cancel(){
		//加载布局文件
		layout("tmc");
		$id = LI('userId');
		$order = D('Agent/TmcOrder', 'Logic');
		$Page = 0;
		$result = $order->queryCancel($id,$Page,$_POST);
		$count=count($result);
		
		$Page  = new \Think\Page($count,5);
		$show       = $Page->show();// 分页显示输出
		$result = $order->queryCancel($id,$Page,$_POST);
		$this->assign('Page',$show);// 赋值分页输出
		$this->assign('order',$result);
		$this->assign('start_date',$_POST['start_date']);
		$this->assign('end_date',$_POST['end_date']);
		$this->assign('keywords',$_POST['keywords']);
		$this->theme("agent")->display("order_list_cancel");

	}	
	
	//加载订单“待出票”的页面（order_list_wait）页面
	public function  order_list_wait(){
		//加载布局文件
		layout("tmc");
		$id = LI('userId');
		$order = D('Agent/TmcOrder', 'Logic');
		$Page = 0;
		$result = $order->queryWait($id,$Page,$_POST);
		$count=count($result);
		
		$Page  = new \Think\Page($count,5);
		$show       = $Page->show();// 分页显示输出
		$result = $order->queryWait($id,$Page,$_POST);
		$this->assign('Page',$show);// 赋值分页输出
		$this->assign('order',$result);
		$this->assign('start_date',$_POST['start_date']);
		$this->assign('end_date',$_POST['end_date']);
		$this->assign('keywords',$_POST['keywords']);
		$this->theme("agent")->display("order_list_wait");

	}	
	
	
	
	
	/*
	 //加载机票的页面 (flight_list) 页面;
	 public function flight_list(){
		//加载布局文件
		layout("tmc");
		$id = LI('userId');
		$order = D('Agent/TmcOrder', 'Logic');
		$result = $order->queryFlight($id);
		//print_r($result);
		$this->assign('order',$result);
		$this->theme("agent")->display("exp_flight_list");
		
	 }

	*/
	
	
/*************  第二部分：对订单（order）的OP操作   **************/
		
    /*//取消订单 
	public function cancle(){
		
		$id = $_POST['id'];//获取订单id
		$data['status'] =19 ;
		$order = M('order');
		$result = $order->where('id='.$id)->save($data); 
		if($result){
			$this->ajaxReturn(0);
		}else{
			$this->ajaxReturn(1);
		}
	
	}*/
	
     //取消订单       分为两种情况：原状态是“待确认或待支付”开始的，跳转到“已取消”；
	 //                       原状态是“已确认”，现付跳转到“待退款”，月结跳转到“已退单”
	public function cancel(){
		
		$id = $_POST['id'];//获取订单id
		$order = M('order');
		$hotel = M('hotel_info');
		$origin = $order->where('id='.$id)->select(); 
		if($origin[0]['status'] == 6 || $origin[0]['status'] == 11 ){   //原状态是待确认或待支付
		    $orderData['status'] = 19 ;    //已取消
		}
		elseif($origin[0]['status'] == 20){   //原状态是已确认   订单分现付和月结两种情况，酒店状态变已取消，机票火车无状态
			  $hotelData['status'] =19 ;     //已取消
			if($origin[0]['pay_type'] == 0){
			  $orderData['status'] = 8 ;    //待退款
			}
			elseif($origin[0]['pay_type'] == 1){
			  $orderData['status'] = 23 ;    //已退单
			}
		}
		$o_result = $order->where('id='.$id)->save($orderData); 
		$h_result = $hotel->where('o_id='.$id)->save($hotelData);
		if($o_result && $h_result){
			$this->ajaxReturn(0);
		}else{
			$this->ajaxReturn(1);
		}
	}
	
    //确认订单
	public function  sure(){
		$id = $_POST['id'];//获取订单id
		$orderData['status'] =20 ;     //已确认
		$flightData['status'] =2 ;     //待出票
		$hotelData['status'] =10 ;     //待预订
		$trainData['status'] =2 ;      //待出票
		$order = M('order');
		$flight = M('flight_ticket_info');
		$hotel = M('hotel_info');
		$train = M('train_ticket_info');
		$o_result = $order->where('id='.$id)->save($orderData); 
		$f_result = $flight->where('o_id='.$id)->save($flightData);
		$h_result = $hotel->where('o_id='.$id)->save($hotelData);
		$t_result = $train->where('o_id='.$id)->save($trainData);
		if($o_result && $f_result && $h_result && $t_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
    //申请退单
	public function  apply_cancel_order(){
		$id = $_POST['id'];//获取订单id
		$orderData['status'] =7 ;     //待退单
		$flightData['status'] =9 ;     //待退票
		$hotelData['status'] =5 ;     //待取消
		$trainData['status'] =9 ;      //待退票
		$order = M('order');
		$flight = M('flight_ticket_info');
		$hotel = M('hotel_info');
		$train = M('train_ticket_info');
		$o_result = $order->where('id='.$id)->save($orderData); 
		$f_result = $flight->where('o_id='.$id)->save($flightData);
		$h_result = $hotel->where('o_id='.$id)->save($hotelData);
		$t_result = $train->where('o_id='.$id)->save($trainData);
		if($o_result && $f_result && $h_result && $t_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
		
    //修改
	public function  modify(){
		$id = $_POST['id'];//获取订单id
		$data['status'] =12 ;     //未提交
		$order = M('order');
		$result = $order->where('id='.$id)->save($data); 
		if($result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}

    //已采购
	public function  already_bought(){
		$id = $_POST['id'];//获取订单id
		//若是自助预订，则把op的信息录入到order表中，所以要先在tmc员工表中搜出op的信息
		$tmcOP = M('tmc_employee');
		$tmc_uname = $tmcOP->where('id = '.LI('tmcempId') )->getField('name');
		$orderData['tmc_uid'] =LI('tmcempId') ;
		$orderData['tmc_uname'] =$tmc_uname ;
		$orderData['status'] =17 ;     //已购买
		$flightData['status'] =14 ;     //已出票
		$hotelData['status'] =25 ;     //已预订
		$trainData['status'] =14 ;     //已出票
		$order = M('order');
		$flight = M('flight_ticket_info');
		$hotel = M('hotel_info');
		$train = M('train_ticket_info');
		$o_result = $order->where('id='.$id)->save($orderData); 
		$f_result = $flight->where('o_id='.$id)->save($flightData);
		$h_result = $hotel->where('o_id='.$id)->save($hotelData);
		$t_result = $train->where('o_id='.$id)->save($trainData);
		
		if($o_result){
			
			$employee=M('employee');
			$user=M('user');
			$empId = $order->where('id='.$id)->getField('u_id');
			$emp_info = $employee->where('id='.$empId)->find();
			$wx_openid = $user->where('id='.$emp_info['u_id'])->getField('wx_openid');
			//触发消息发送
			$send_1=D("Home/SendMessage","Logic");
			$case_1="TicketInfo";
			$ress['emp_email']=$emp_info['email'];
			$ress['emp_phone']=$emp_info['phone'];
			$ress['emp_name']= $emp_info['name'];
			$ress['wx_openid']=$wx_openid;//传用户的wx_openid;
			$ress['order_num'] = $order->where('id='.$id)->getField('order_num');
			$aaaaaaa = $send_1->SendDetails($case_1,$ress);
			    $this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
    //提交订单      提交之后：现付订单跳转到“待支付”状态，月付订单跳转到“待确认”状态
	public function submit(){
		
		$id = $_POST['id'];//获取订单id
		$order = M('order');
		$origin = $order->where('id='.$id)->select(); 
		if($origin[0]['pay_type'] == 0 ){   //现付
		    $data['status'] = 11 ;    //已取消
		}
		elseif($origin[0]['pay_type'] == 1){   //月付
			$data['status'] = 6 ;    //待退款
		}
		$result = $order->where('id='.$id)->save($data); 
		if($result){
			$this->ajaxReturn(0);
		}else{
			$this->ajaxReturn(1);
		}
	
	}
	
    //已支付
	public function  already_paid(){
		$id = $_POST['id'];//获取订单id
		$orderData['status'] =20 ;     //已确认
		$flightData['status'] =2 ;     //待出票
		$hotelData['status'] =10 ;     //待预订
		$trainData['status'] =2 ;     //待出票
		$order = M('order');
		$flight = M('flight_ticket_info');
		$hotel = M('hotel_info');
		$train = M('train_ticket_info');
		$o_result = $order->where('id='.$id)->save($orderData); 
		$f_result = $flight->where('o_id='.$id)->save($flightData);
		$h_result = $hotel->where('o_id='.$id)->save($hotelData);
		$t_result = $train->where('o_id='.$id)->save($trainData);
		if($o_result && $f_result && $h_result && $t_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
    //已使用 
    /******  酒店状态有已使用吗？？  ******/
	public function  already_used(){
		$id = $_POST['id'];//获取订单id
		$orderData['status'] =22 ;     //已使用
		$flightData['status'] =22 ;     //已使用
		$hotelData['status'] =22 ;     //已使用
		$trainData['status'] =22 ;     //已使用
		$order = M('order');
		$flight = M('flight_ticket_info');
		$hotel = M('hotel_info');
		$train = M('train_ticket_info');
		$o_result = $order->where('id='.$id)->save($orderData); 
		$f_result = $flight->where('o_id='.$id)->save($flightData);
		$h_result = $hotel->where('o_id='.$id)->save($hotelData);
		$t_result = $train->where('o_id='.$id)->save($trainData);
		if($o_result && $f_result && $h_result && $t_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
    //拒绝
	public function  refuse(){
		$id = $_POST['id'];//获取订单id
		$orderData['status'] =17 ;     //已购买
		$flightData['status'] =14 ;     //已出票
		$hotelData['status'] =25 ;     //已预订
		$trainData['status'] =14 ;     //已出票
		$order = M('order');
		$flight = M('flight_ticket_info');
		$hotel = M('hotel_info');
		$train = M('train_ticket_info');
		$o_result = $order->where('id='.$id)->save($orderData); 
		$f_result = $flight->where('o_id='.$id)->save($flightData);
		$h_result = $hotel->where('o_id='.$id)->save($hotelData);
		$t_result = $train->where('o_id='.$id)->save($trainData);
		
		$order_refund_req = M('order_refund_req');
		$refuseData['t_emp_id'] = LI('userId');             //tmc员工id
    	$refuseData['x_time'] = date("Y-m-d H:i:s",time()); //处理时间
    	$refuseData['status'] = 4;                          //已拒绝
		$order_refund_req->where('table_name = 1 AND table_id ='.$id)->save($refuseData);
		
		if($o_result && $f_result && $h_result && $t_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
    //已退单        点击之后：现付订单跳转到“待退款”状态，月付订单跳转到“已退单”状态，酒店为已取消，机票火车无状态
	public function already_refunded_order(){
		
		$id = $_POST['id'];//获取订单id
		$order = M('order');
		$hotel = M('hotel_info');
		$origin = $order->where('id='.$id)->select(); 
		$hotelData['status'] =19 ;     //已取消
		if($origin[0]['pay_type'] == 0){
			  $orderData['status'] = 8 ;    //待退款
			}
		elseif($origin[0]['pay_type'] == 1){
			  $orderData['status'] = 23 ;    //已退单
			}
		$o_result = $order->where('id='.$id)->save($orderData); 
		$h_result = $hotel->where('o_id='.$id)->save($hotelData);
		if($o_result && $h_result){
			$this->ajaxReturn(0);
		}else{
			$this->ajaxReturn(1);
		}
	
	}
	
    //已退款
	public function  already_refunded(){
		$id = $_POST['id'];//获取订单id
		$data['status'] = 24 ;     //已退款
		$order = M('order');
		$result = $order->where('id='.$id)->save($data); 
		if($result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
	
	
	
/***********第三部分：对机票状态（73go_flight_ticket_info  status）的OP操作********/	
	
	//申请退票
	public function  flight_apply_refund_ticket(){
		$id = $_POST['id'];//获取机票详情id
		$data['status'] =9 ;     //待退票
		$flightTicket = M('flight_ticket_info');
		$ft_result = $flightTicket->where('id='.$id)->save($data); 
		$orderId = $flightTicket->where('id='.$id)->getField('o_id');//返回该机票的订单id
		$this->isInSameStatus($orderId);
		if($ft_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
	
	//申请改签
	public function  flight_apply_change_ticket(){
		$id = $_POST['id'];//获取机票详情id
		$data['status'] =4 ;    //待改签
		$flightTicket = M('flight_ticket_info');
		$ft_result = $flightTicket->where('id='.$id)->save($data); 
		if($ft_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}

	
    //取消改签
	public function  flight_cancel_change_ticket(){
		$id = $_POST['id'];//获取机票详情id
		$data['status'] =14;    //已出票
		$flightTicket = M('flight_ticket_info');
		$ft_result = $flightTicket->where('id='.$id)->save($data); 
		
		$order_refund_req = M('order_refund_req');
    	$cancelData['status'] = 3;                          //已取消
		$order_refund_req->where('table_name = 2 AND table_id ='.$id)->save($cancelData);
		
		if($ft_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
		
	
    //已出票
	public function  flight_already_made_ticket(){
		$id = $_POST['id'];//获取机票详情id
		$data['status'] =14;    //已出票
		$flightTicket = M('flight_ticket_info');
		$ft_result = $flightTicket->where('id='.$id)->save($data); 
		$orderId = $flightTicket->where('id='.$id)->getField('o_id');//返回该机票的订单id
		$this->isInSameStatus($orderId);
		if($ft_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
    //已使用
	public function  flight_already_used(){
		$id = $_POST['id'];//获取机票详情id
		$data['status'] =22;    //已使用
		$flightTicket = M('flight_ticket_info');
		$ft_result = $flightTicket->where('id='.$id)->save($data); 
		if($ft_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
    //未使用
	public function  flight_not_used(){
		$id = $_POST['id'];//获取机票详情id
		$data['status'] =18;    //已过期
		$flightTicket = M('flight_ticket_info');
		$ft_result = $flightTicket->where('id='.$id)->save($data); 
		if($ft_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
    //拒绝
	public function  flight_refuse(){
		$id = $_POST['id'];//获取机票详情id
		$data['status'] =14;    //已出票
		$flightTicket = M('flight_ticket_info');
		$ft_result = $flightTicket->where('id='.$id)->save($data); 
		$orderId = $flightTicket->where('id='.$id)->getField('o_id');//返回该机票的订单id
		$this->isInSameStatus($orderId);
		
		$order_refund_req = M('order_refund_req');
		$refuseData['t_emp_id'] = LI('userId');             //tmc员工id
    	$refuseData['x_time'] = date("Y-m-d H:i:s",time()); //处理时间
    	$refuseData['status'] = 4;                          //已拒绝
		$order_refund_req->where('table_name = 2 AND table_id ='.$id)->save($refuseData);
		
		if($ft_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
    //已退票
	public function  flight_already_refunded(){
		$id = $_POST['id'];//获取机票详情id
		$data['status'] =26;    //已退票
		$flightTicket = M('flight_ticket_info');
		$ft_result = $flightTicket->where('id='.$id)->save($data); 
		if($ft_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
    //已改签
	public function  flight_already_changed_ticket(){
		$id = $_POST['id'];//获取机票详情id
		$data['status'] =16;    //已改签
		$flightTicket = M('flight_ticket_info');
		$ft_result = $flightTicket->where('id='.$id)->save($data); 
		if($ft_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
    //允许改签
	public function  flight_allow_change_ticket(){
		$id = $_POST['id'];//获取机票详情id
		$data['status'] =1;    //待补款
		$flightTicket = M('flight_ticket_info');
		$ft_result = $flightTicket->where('id='.$id)->save($data); 
		if($ft_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
    //已补款
	public function  flight_already_added_money(){
		$id = $_POST['id'];//获取机票详情id
		$data['status'] =13;    //已补款
		$flightTicket = M('flight_ticket_info');
		$ft_result = $flightTicket->where('id='.$id)->save($data); 
		if($ft_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
	
/***********第四部分：对酒店状态（73go_hotel_info          status）的OP操作********/	
	
    //申请退单
	public function  hotel_apply_cancel_order(){
		$id = $_POST['id'];//获取酒店详情id
		$data['status'] =5;    //待取消
		$hotel = M('hotel_info');
		$h_result = $hotel->where('id='.$id)->save($data); 
		$orderId = $hotel->where('id='.$id)->getField('o_id');//返回该酒店的订单id
		$this->isInSameStatus($orderId);
		if($h_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
    //申请改单
	public function  hotel_apply_change_order(){
		$id = $_POST['id'];//获取酒店详情id
		$data['status'] =3;    //待改单
		$hotel = M('hotel_info');
		$h_result = $hotel->where('id='.$id)->save($data); 
		if($h_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
	
    //已预订
	public function  hotel_already_booked(){
		$id = $_POST['id'];//获取酒店详情id
		$data['status'] = 25 ;    //已预订
		$hotel = M('hotel_info');
		$h_result = $hotel->where('id='.$id)->save($data); 
		$orderId = $hotel->where('id='.$id)->getField('o_id');//返回该酒店的订单id
		$this->isInSameStatus($orderId);
		if($h_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
    //拒绝
	public function  hotel_refuse(){
		$id = $_POST['id'];//获取酒店详情id
		$data['status'] = 25 ;    //已预订
		$hotel = M('hotel_info');
		$h_result = $hotel->where('id='.$id)->save($data); 
		$orderId = $hotel->where('id='.$id)->getField('o_id');//返回该酒店的订单id
		$this->isInSameStatus($orderId);
		
		$order_refund_req = M('order_refund_req');
		$refuseData['t_emp_id'] = LI('userId');             //tmc员工id
    	$refuseData['x_time'] = date("Y-m-d H:i:s",time()); //处理时间
    	$refuseData['status'] = 4;                          //已拒绝
		$order_refund_req->where('table_name = 3 AND table_id ='.$id)->save($refuseData);
		
		if($h_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
    //已改单
	public function  hotel_already_changed_order(){
		$id = $_POST['id'];//获取酒店详情id
		$data['status'] = 15 ;    //已改单
		$hotel = M('hotel_info');
		$h_result = $hotel->where('id='.$id)->save($data); 
		if($h_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
    //已取消        点击之后：有预付款跳转到“待退款”状态，无预付款跳转到“已取消”状态
	public function hotel_already_cancelled(){
		
		$id = $_POST['id'];//获取酒店详情id
		$hotel = M('hotel_info');
		$origin = $hotel->where('id='.$id)->select(); 
		if($origin[0]['prepay_status'] == 1 ){   //有预付款
		    $data['status'] = 8 ;    //待退款
		}
		elseif($origin[0]['prepay_status'] == 0){   //无预付款
			$data['status'] = 19 ;    //已取消
		}
		$h_result = $hotel->where('id='.$id)->save($data); 
		if($h_result){
			$this->ajaxReturn(0);
		}else{
			$this->ajaxReturn(1);
		}
	}
	
    //已退款
	public function  hotel_already_refunded(){
		$id = $_POST['id'];//获取酒店详情id
		$data['status'] = 19 ;    //已取消
		$hotel = M('hotel_info');
		$h_result = $hotel->where('id='.$id)->save($data); 
		if($h_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
		
/***********第五部分：对火车状态（73go_train_ticket_info   status）的OP操作********/	
	
	//申请退票
	public function  train_apply_refund_ticket(){
		$id = $_POST['id'];//获取火车详情id
		$data['status'] =9 ;     //待退票
		$trainTicket = M('train_ticket_info');
		$trt_result = $trainTicket->where('id='.$id)->save($data); 
		$orderId = $trainTicket->where('id='.$id)->getField('o_id');//返回该火车的订单id
		$this->isInSameStatus($orderId);
		if($trt_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
	
	//申请改签
	public function  train_apply_change_ticket(){
		$id = $_POST['id'];//获取火车详情id
		$data['status'] =4 ;    //待改签
		$trainTicket = M('train_ticket_info');
		$trt_result = $trainTicket->where('id='.$id)->save($data); 
		if($trt_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
    
	
    //取消改签
	public function  train_cancel_change_ticket(){
		$id = $_POST['id'];//获取火车详情id
		$data['status'] =14;    //已出票
		$trainTicket = M('train_ticket_info');
		$trt_result = $trainTicket->where('id='.$id)->save($data); 
		
		$order_refund_req = M('order_refund_req');
    	$cancelData['status'] = 3;                          //已取消
		$order_refund_req->where('table_name = 4 AND table_id ='.$id)->save($cancelData);
		
		if($trt_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
	
    //已出票
	public function  train_already_made_ticket(){
		$id = $_POST['id'];//获取火车详情id
		$data['status'] =14;    //已出票
		$trainTicket = M('train_ticket_info');
		$trt_result = $trainTicket->where('id='.$id)->save($data); 
		$orderId = $trainTicket->where('id='.$id)->getField('o_id');//返回该火车的订单id
		$this->isInSameStatus($orderId);
		if($trt_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
    //已使用
	public function  train_already_used(){
		$id = $_POST['id'];//获取火车详情id
		$data['status'] =22;    //已使用
		$trainTicket = M('train_ticket_info');
		$trt_result = $trainTicket->where('id='.$id)->save($data); 
		if($trt_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
    //未使用
	public function  train_not_used(){
		$id = $_POST['id'];//获取火车详情id
		$data['status'] =18;    //已过期
		$trainTicket = M('train_ticket_info');
		$trt_result = $trainTicket->where('id='.$id)->save($data); 
		if($trt_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
    //拒绝
	public function  train_refuse(){
		$id = $_POST['id'];//获取火车详情id
		$data['status'] =14;    //已出票
		$trainTicket = M('train_ticket_info');
		$trt_result = $trainTicket->where('id='.$id)->save($data); 
		$orderId = $trainTicket->where('id='.$id)->getField('o_id');//返回该火车的订单id
		$this->isInSameStatus($orderId);
		
		$order_refund_req = M('order_refund_req');
		$refuseData['t_emp_id'] = LI('userId');             //tmc员工id
    	$refuseData['x_time'] = date("Y-m-d H:i:s",time()); //处理时间
    	$refuseData['status'] = 4;                          //已拒绝
		$order_refund_req->where('table_name = 4 AND table_id ='.$id)->save($refuseData);
		
		if($trt_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
    //已退票
	public function  train_already_refunded(){
		$id = $_POST['id'];//获取火车详情id
		$data['status'] =26;    //已退票
		$trainTicket = M('train_ticket_info');
		$trt_result = $trainTicket->where('id='.$id)->save($data); 
		if($trt_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
    //已改签
	public function  train_already_changed_ticket(){
		$id = $_POST['id'];//获取火车详情id
		$data['status'] =16;    //已改签
		$trainTicket = M('train_ticket_info');
		$trt_result = $trainTicket->where('id='.$id)->save($data); 
		if($trt_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
    //允许改签
	public function  train_allow_change_ticket(){
		$id = $_POST['id'];//获取火车详情id
		$data['status'] =1;    //待补款
		$trainTicket = M('train_ticket_info');
		$trt_result = $trainTicket->where('id='.$id)->save($data); 
		if($trt_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
    //已补款
	public function  train_already_added_money(){
		$id = $_POST['id'];//获取火车详情id
		$data['status'] =13;    //已补款
		$trainTicket = M('train_ticket_info');
		$trt_result = $trainTicket->where('id='.$id)->save($data); 
		if($trt_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
		
/***********第六部分：对其他状态（73go_other_produ_info    status）的OP操作********/	
	
	/*
     ********** 完全按照订单（order）的状态改变
     */	
	
	
	
/***********第七部分：对保险状态（73go_air_insur_info      status）的OP操作********/
	
    //申请退单
	public function  insur_apply_cancel_order(){
		$id = $_POST['id'];//获取保险详情id
		$data['status'] =5;    //待取消
		$inur = M('air_insur_info');
		$result = $inur->where('id='.$id)->save($data); 
		if($result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}

	
    //已使用
	public function  insure_already_used(){
		$id = $_POST['id'];//获取保险详情id
		$data['status'] = 22 ;    //已使用
		$inur = M('air_insur_info');
		$result = $inur->where('id='.$id)->save($data); 
		if($result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
	/*
    //已取消        点击之后：现付跳转到“待退款”状态，月结跳转到“已取消”状态
	public function insure_already_cancelled(){
		
		$id = $_POST['id'];//获取保险详情id
		$m = M('air_insur_info');
		$o = M('order');
		$ou = M('order_user');
		$insur = $m->where('id='.$id)->select();
		$orderId = $insur['o_id'];
		
		$order = $o->where('id='.$orderId)->select();
		if($order['pay_type'] == 1 ){   //现付
		    $data['status'] = 8 ;    //待退款
		}
		elseif($order['pay_type'] == 0){   //月结
			$data['status'] = 19 ;    //已取消
		}
		$result = $order->where('id='.$id)->save($data); 
		if($result){
			$this->ajaxReturn(0);
		}else{
			$this->ajaxReturn(1);
		}
	}
	*/

	
	
	
    //已退款
	public function  insure_already_refunded(){
		$id = $_POST['id'];//获取保险详情id
		$data['status'] = 19 ;    //已取消
		$inur = M('air_insur_info');
		$result = $inur->where('id='.$id)->save($data); 
		if($result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
		
	
/************第八部分：其他操作*************************************************/
	
    //判断订单内所有产品是否处于同一状态，并将订单状态强制跳转到对应的状态
	public function isInSameStatus($id){   //获取订单id
		
		$order = M('order');
		$flight = M('flight_ticket_info');
		$hotel = M('hotel_info');
		$train = M('train_ticket_info'); 
		$f_result = $flight->where('o_id='.$id)->select();
		$h_result = $hotel->where('o_id='.$id)->select();
		$t_result = $train->where('o_id='.$id)->select();
		$flgihtStatus = array();
		$hotelStatus = array();
		$trainStatus = array();
		foreach ($f_result as $key=>$val){
			array_push($flgihtStatus, $val['status']);
		}
	    foreach ($h_result as $key=>$val){
			array_push($hotelStatus, $val['status']);
		}
	    foreach ($t_result as $key=>$val){
			array_push($trainStatus, $val['status']);
		}
		//print_r($flgihtStatus);
		//print_r($hotelStatus);
		//print_r($trainStatus);
		
	    //判断机票酒店火车状态是否都在“已确认”内，若TRUE，则订单状态强制改为“已确认”
		if( $this->array_in_array($flgihtStatus,array(2)) && $this->array_in_array($hotelStatus, array(10)) && $this->array_in_array($trainStatus, array(2)) ){
			$orderData['status'] =20 ;     //已确认
			$o_result = $order->where('id='.$id)->save($orderData); 
		}
		
		//判断机票酒店火车状态是否都在“已购买”内，若TRUE，则订单状态强制改为“已购买”
		if( $this->array_in_array($flgihtStatus,array(14,4,16,1,13,22,18)) && $this->array_in_array($hotelStatus, array(25,3,15)) && $this->array_in_array($trainStatus, array(14,4,16,1,13,22,18)) ){
			$orderData['status'] =17 ;     //已购买
			$o_result = $order->where('id='.$id)->save($orderData); 
		}
		
	    //判断机票酒店火车状态是否都在“待退单”内，若TRUE，则订单状态强制改为“待退单”
		if( $this->array_in_array($flgihtStatus,array(9,26)) && $this->array_in_array($hotelStatus, array(5,8)) && $this->array_in_array($trainStatus, array(9,26)) ){
			$orderData['status'] =7 ;     //待退单
			$o_result = $order->where('id='.$id)->save($orderData); 
		}
		
		
	}
	
	
	//判断数组1是否存在数组2内，该方法被上面方法所调用
    public function array_in_array($arr1,$arr2){
     	foreach ($arr1 as $key=>$val){
     		if(!in_array($val, $arr2)){
     			return 0;
     		}
     	}
     	return 1;
     }
	
	

//---------------------------------配送管理相关的操作 开始----------------------------------------------	
	//加载配送管理页面
	public function dispatch_mgnt(){
	//加载布局
	layout("tmc");
	$src=0;
	$tmc_id=LI("tmcId");
	$dispatch_mgnt=M("dispatch_mgnt");
	
	$strSql="select o.id,o.order_num,o.src,m.o_id,m.name,m.time,m.address,m.status 
				from 73go_order as o left join 73go_dispatch_mgnt as m
					on o.id=m.o_id  where o.src=".$src ." and tmc_id=$tmc_id  order by o.id desc";	
	$data=$dispatch_mgnt->query($strSql);
	$this->assign("data",$data);
	$this->theme("agent")->display("dispatch_mgnt");

	}
	
	//进行配送管理操作的
	public function search_dispatch_mgnt(){
		//加载布局
		layout("tmc");
		$src=0; //0 为自助预定类型的订单
		$tmc_id=LI("tmcId");
		$dispatch_mgnt=M("");//实例化配送管理表
		$info=$_POST['info'];
		$strSql="select o.id,o.order_num,o.src,m.o_id,m.name,m.time,m.address,m.status 
					from 73go_order as o left join 73go_dispatch_mgnt as m
					    on o.id=m.o_id  where o.src=".$src ." and tmc_id=$tmc_id   and
							  (o.order_num like '%".$info."%'  or  m.name like '%".$info."%')
									order by o.id desc";	
		$data=$dispatch_mgnt->query($strSql);
				 
	    $this->ajaxReturn($data,'json');
	
	}
	
	//配送请求状态的操作
	public function option_dispatch_mgnt(){
	//加载布局
	layout("tmc");
	$status=$_GET['status'];
	$tmc_id=LI("tmcId");
	//$count=count($result);
	$src=0;
	$dispatch_mgnt=M("dispatch_mgnt");
	
	
	//----------------进行分页的处理-------------------

			// 实例化分页类 传入总记录数和每页显示的记录数(4)
			//$Page  = new \Think\Page($count,4);

			//$show       = $Page->show();// 分页显示输出
			//$this->assign('Page',$show);// 赋值分页输出

	$strSql="select o.id,o.order_num,o.src,m.o_id,m.name,m.time,m.address,m.status 
				from 73go_order as o left join 73go_dispatch_mgnt as m
					on o.id=m.o_id  and (m.status=$status) where o.src=".$src ." 	
						and tmc_id=$tmc_id 	 and (m.status=$status) 
								order by o.id desc";	
	
	$data=$dispatch_mgnt->query($strSql);
	//$count=count($data);//查询出满足要求的分页条数
	$this->assign("data",$data);
	$this->theme("agent")->display("dispatch_mgnt");	

	}
	
   //进行配送管理状态的改变
	public function change_dispatch_mgnt(){
	//加载布局
	layout("tmc");
	$dispatch_mgnt=M("dispatch_mgnt");
	//进行状态操作的变换
		$map['status']=1;
		$id=$_GET['id'];
		$datt=$dispatch_mgnt->where('o_id='.$id)->save($map);	
		$this->dispatch_mgnt();

	} 
    
//----------------------------------配送管理 结束---------------------------------------------------	
	

	
	
	
	
	
	
	
/*********************退改签操作********************************************/
	
//退该列表页面
    public function change_or_refund_req_list(){
    	//加载布局文件
		layout("tmc");
		$id = LI('userId');
		$order = D('Agent/TmcOrder', 'Logic');
		$Page = 0;
		$req = $order->queryRefundRequestList($id,$Page);
		$count=count($req);
		
		$Page  = new \Think\Page($count,10);
		$show       = $Page->show();// 分页显示输出
		$req = $order->queryRefundRequestList($id,$Page);
		//print_r($req);
		$this->assign('Page',$show);// 赋值分页输出
		$this->assign('req',$req);
		$this->theme("agent")->display("refund_order_list");
    	
    	
    	
    	
    	
    }
	
	
	
	
	
//退该详情页面
    public function change_or_refund_detail($reqId){        //获取退改签表的退改申请表id
    	
    	layout("tmc");
    	 
    	$order_refund_req = M('order_refund_req');
		$reqCmd = $order_refund_req->where('id='.$reqId)->getField('cmd');    //申请的操作   申请改单24 申请改签25 申请退单26 申请退票27
    	$reqTableName = $order_refund_req->where('id='.$reqId)->getField('table_name');  //1订单 2机票 3酒店 4火车
    	$reqTableId = $order_refund_req->where('id='.$reqId)->getField('table_id');    //订单id  或   产品id
    	$reqDetail = $order_refund_req->where('id='.$reqId)->select();
    	
    	
    	//查询订单id
    	if($reqTableName == 1){
    		$orderId = $reqTableId;
    	}
    	elseif($reqTableName == 2){
    		$flight_ticket_info = M('flight_ticket_info');
    		$orderId = $flight_ticket_info->where('id='.$reqTableId)->getField('o_id');
    	}
        elseif($reqTableName == 3){
    		$hotel_info = M('hotel_info');
    		$orderId = $hotel_info->where('id='.$reqTableId)->getField('o_id');
    	}
        elseif($reqTableName == 4){
    		$train_ticket_info = M('train_ticket_info');
    		$orderId = $train_ticket_info->where('id='.$reqTableId)->getField('o_id');
    	}
    	
    	
		$TmcOrder = D('Agent/TmcOrder', 'Logic');
		$order = $TmcOrder->queryThisOrder($orderId);       //查询该订单的所有产品
		
		
		//插入标志位，标志客户申请的是订单中的哪个产品
        if($reqTableName == 1){
    		$order[0]['choiceTag'] = 1;
        	
    	}
    	elseif($reqTableName == 2){
    	    foreach ($order[0]['flight'] as $key=>$of){
    			if($of['id'] == $reqTableId){
    				 $order[0]['flight'][$key]['choiceTag'] = 1;
    			}
    		}
    	}
        elseif($reqTableName == 3){
            foreach ($order[0]['hotel'] as $key=>$oh){
    			if($oh['id'] == $reqTableId){
    				 $order[0]['hotel'][$key]['choiceTag'] = 1;
    			}
    		}
    	}
        elseif($reqTableName == 4){
            foreach ($order[0]['train'] as $key=>$ot){
    			if($ot['id'] == $reqTableId){
    				 $order[0]['train'][$key]['choiceTag'] = 1;
    			}
    		}
    	}
    	
    	//将选择的申请人转化为数组      "id1,id2,id3"转变为array(id1,id2,id3)
		$checkedUserId = explode(",",$reqDetail[0]['user_ids']);
		$reqDetail[0]['user_name'] = array();
		$order_user = M('order_user');
		foreach ($checkedUserId as $key=>$user){
			$userName = $order_user->where('id='.$user)->getField('name');
			array_push($reqDetail[0]['user_name'], $userName);
		}
		
		

		if($reqCmd == 24 || $reqCmd == 25){
			$this->assign('o',$order[0]);
		    $this->assign('reqDetail',$reqDetail[0]);
		    $this->theme("agent")->display("change_product_exc");	
		}
    	elseif($reqCmd == 26 || $reqCmd == 27){
			$this->assign('o',$order[0]);
		    $this->assign('reqDetail',$reqDetail[0]);
		    $this->theme("agent")->display("refund_product_exc");	
		}
    	
    	
    	
    	
    }
    
    
    
	    
//退改签拒绝
    public function change_or_refund_refuse(){    //获取申请表的id
    	$reqId = $_POST['reqId'];
    	$opinion = $_POST['opinion'];
    	
    	
    	$order_refund_req = M('order_refund_req');
    	$reqTableName = $order_refund_req->where('id='.$reqId)->getField('table_name');  //1订单 2机票 3酒店 4火车
    	$reqTableId = $order_refund_req->where('id='.$reqId)->getField('table_id');    //订单id  或   产品id
    	$reqDetail = $order_refund_req->where('id='.$reqId)->select();
    	$checkedUserId = explode(",",$reqDetail[0]['user_ids']);           //将使用者id变为一维数组形式
    	$tmc_employee = M('tmc_employee');
    	$tmcEmpId = $tmc_employee->where('u_id='.LI('userId'))->getField('id');
    	
    	//把所有信息存入“订单退改签申请”表
    	$reqStatusData['t_emp_id'] = $tmcEmpId;                //tmc员工id
    	$reqStatusData['opinion'] = $opinion;                  //处理意见
    	$reqStatusData['x_time'] = date("Y-m-d H:i:s",time()); //处理时间
    	$reqStatusData['status'] = 4;                          //已拒绝
    	$result = $order_refund_req->where('id='.$reqId)->save($reqStatusData);   
    	
    	
    	$order = M('order');
    	$flight_ticket_info = M('flight_ticket_info');
    	$hotel_info = M('hotel_info');
    	$train_ticket_info = M('train_ticket_info');
    	$flight_userinfo = M('flight_userinfo');
    	$hotel_userinfo = M('hotel_userinfo');
    	$train_ticket_userinfo = M('train_ticket_userinfo');
    	
    	if($reqTableName == 2){        //只对机票申请退票
    		$refuseData['status'] = 14;     //已出票
    	
    		$orderId = $flight_ticket_info->where('id='.$reqTableId)->getField('o_id');
    		$flightStatus = $flight_ticket_info->where('id='.$reqTableId)->getField('status');
    		
    		if($flightStatus != 98){
    		    $flight_ticket_info->where('id='.$reqTableId)->save($refuseData);
    		    $this->isInSameStatus($orderId);
    		}
    		elseif ($flightStatus == 98){
    		    foreach ($checkedUserId as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        $flightUserStatus = $flight_userinfo->where('ou_id='.$vo)->getField('status');
		        if(($flightUserStatus == 4) or ($flightUserStatus == 9)) {   //判断原状态
		        	$fu_result = $flight_userinfo->where('ou_id='.$vo)->save($refuseData);
		        }
			  }
    			
    		}

    	}
    	
        if($reqTableName == 3){        //只对酒店申请改单
        	$refuseData['status'] = 25;     //已预订
    	
    		$orderId = $hotel_info->where('id='.$reqTableId)->getField('o_id');
    		$hotelStatus = $hotel_info->where('id='.$reqTableId)->getField('status');
    		
    		if($hotelStatus != 98){
    		    $hotel_info->where('id='.$reqTableId)->save($refuseData);
    		    $this->isInSameStatus($orderId);
    		}
    		elseif ($hotelStatus == 98){
    		    foreach ($checkedUserId as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        $hotelUserStatus = $hotel_userinfo->where('ou_id='.$vo)->getField('status');
		        if(($hotelUserStatus == 3) or ($hotelUserStatus == 5)){   //判断原状态
		        	$hu_result = $hotel_userinfo->where('ou_id='.$vo)->save($refuseData);
		        }
			  }
    			
    		}

    	}
    	
        if($reqTableName == 4){        //只对火车申请改签
        	$refuseData['status'] = 14;     //已出票
    	
    		$orderId = $train_ticket_info->where('id='.$reqTableId)->getField('o_id');
    		$trainStatus = $train_ticket_info->where('id='.$reqTableId)->getField('status');
    		
    		if($trainStatus != 98){
    		    $train_ticket_info->where('id='.$reqTableId)->save($refuseData);
    		    $this->isInSameStatus($orderId);
    		}
    		elseif ($trainStatus == 98){
    		    foreach ($checkedUserId as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        $trainUserStatus = $train_ticket_userinfo->where('ou_id='.$vo)->getField('status');
		        if(($trainUserStatus == 4) or ($trainUserStatus == 9)){   //判断原状态
		        	$tu_result = $train_ticket_userinfo->where('ou_id='.$vo)->save($refuseData);
		        }
			  }
    			
    		}

    	}
    	
        if($reqTableName == 1){        //只对整个订单申请改单
    	$orderRefuseData['status'] = 17;
    	
    	$orderId = $reqTableId;
    	$TmcOrder = D('Agent/TmcOrder', 'Logic');
		$allOrder = $TmcOrder->queryThisOrder($orderId);       //查询该订单的所有产品
    		
    		$orderStatus = $order->where('id='.$orderId)->getField('status');
    		if($orderStatus == 3 || $orderStatus == 7){
    			$order->where('id='.$orderId)->save($orderRefuseData);
    		}
    		
    		
    		
        foreach ($allOrder[0]['flight'] as $key=>$flightId){
    			
    		$refuseData['status'] = 14;     //已出票
    	
    		//$orderId = $flight_ticket_info->where('id='.$flightId['id'])->getField('o_id');
    		$flightStatus = $flight_ticket_info->where('id='.$flightId['id'])->getField('status');
    		
    		if($flightStatus != 98){
    		    $flight_ticket_info->where('id='.$flightId['id'])->save($refuseData);
    		    //$this->isInSameStatus($orderId);
    		}
    		elseif ($flightStatus == 98){
    		    foreach ($checkedUserId as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        $flightUserStatus = $flight_userinfo->where('ou_id='.$vo)->getField('status');
		        if(($flightUserStatus == 4) or ($flightUserStatus == 9)){   //判断原状态
		        	$fu_result = $flight_userinfo->where('ou_id='.$vo)->save($refuseData);
		        }
			  }
    			
    		}
    	}
    		
        foreach ($allOrder[0]['hotel'] as $key=>$hotelId){  
        	$refuseData['status'] = 25;     //已预订
    	
    		$hotelStatus = $hotel_info->where('id='.$hotelId['id'])->getField('status');
    		
    		if($hotelStatus != 98){
    		    $hotel_info->where('id='.$hotelId['id'])->save($refuseData);
    		}
    		elseif ($hotelStatus == 98){
    		    foreach ($checkedUserId as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        $hotelUserStatus = $hotel_userinfo->where('ou_id='.$vo)->getField('status');
		        if(($hotelUserStatus == 3) or ($hotelUserStatus == 5)){   //判断原状态
		        	$hu_result = $hotel_userinfo->where('ou_id='.$vo)->save($refuseData);
		        }
			  }
    			
    		}

    	}
    	
    	foreach ($allOrder[0]['train'] as $key=>$trainId){
        	$refuseData['status'] = 14;     //已出票
    	
    		$trainStatus = $train_ticket_info->where('id='.$trainId['id'])->getField('status');
    		
    		if($trainStatus != 98){
    		    $train_ticket_info->where('id='.$trainId['id'])->save($refuseData);
    		}
    		elseif ($trainStatus == 98){
    		    foreach ($checkedUserId as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        $trainUserStatus = $train_ticket_userinfo->where('ou_id='.$vo)->getField('status');
		        if(($trainUserStatus == 4) or ($trainUserStatus == 9)){   //判断原状态
		        	$tu_result = $train_ticket_userinfo->where('ou_id='.$vo)->save($refuseData);
		        }
			  }
    			
    		}

    	}
    	
    	}
    	
        if($result){
				$this->ajaxReturn(0);
		}else{
				$this->ajaxReturn(1);
		}
    	
    	
    	
    }
 
    
	
	
	
    
    
    
    
//这是整个大类的括弧	
}






