<?php
namespace Home\Controller;
use Home\Logic\UserLogic;
use System\LoginInfo;
use Think\Controller;

class OrderController extends Controller {
	
	/**
	 * 
	 * 注意此Controller按顺序分为若干部分：
	 * 第一部分：加载页面
	 * 第二部分：对订单（order）的操作
	 * 第三部分：对机票状态（73go_flight_ticket_info  status）的操作
	 * 第四部分：对酒店状态（73go_hotel_info          status）的操作
	 * 第五部分：对火车状态（73go_train_ticket_info   status）的操作
	 * 第六部分：对其他状态（73go_other_produ_info    status）的操作
	 * 第七部分：对保险状态（73go_air_insur_info      status）的操作
	 * 第八部分：其他操作
	 * 第九部分：退改签操作
	 * 
	 */
	
	
/***********   第一部分：加载页面      ****************/
	
	//加载订单所有的页面（order_list_6）页面
	public function  order_list_6(){
		//加载布局文件
		layout("home");
		$id = LI('userId');
		$order = D('Home/Order', 'Logic');
		$Page = 0;
		$result = $order->queryOrder($id,$Page,$_POST);
		$count=count($result);
		$Page  = new \Think\Page($count,10);
		$show  = $Page->show();// 分页显示输出
		$result = $order->queryOrder($id,$Page,$_POST);
		$this->assign('Page',$show);// 赋值分页输出
		$this->assign('order',$result);
		$this->assign('start_date',$_POST['start_date']);
		$this->assign('end_date',$_POST['end_date']);
		$this->assign('keywords',$_POST['keywords']);
		$this->theme("default")->display("order_list");

	}
	//加载订单待支付的页面（order_list_pay）页面
	public function order_list_pay(){
		//加载布局文件
		layout("home");
		$id = LI('userId');
		$order = D('Home/Order', 'Logic');
		$Page = 0;
		$result = $order->queryNotPay($id,$Page,$_POST);
		$count=count($result);
		$Page  = new \Think\Page($count,10);
		$show  = $Page->show();// 分页显示输出
		$result = $order->queryNotPay($id,$Page,$_POST);
		$this->assign('Page',$show);// 赋值分页输出
		$this->assign('order',$result);
		$this->assign('start_date',$_POST['start_date']);
		$this->assign('end_date',$_POST['end_date']);
		$this->assign('keywords',$_POST['keywords']);
		$this->theme("default")->display("order_list_pay");

	}
	
	//加载订单待确认的页面（order_list_confirm）页面
	public function  order_list_confirm(){
		//加载布局文件
		layout("home");
		$id = LI('userId');
		$order = D('Home/Order', 'Logic');
		$Page = 0;
		$result = $order->queryNotConfirm($id,$Page,$_POST);
		$count=count($result);
		$Page  = new \Think\Page($count,10);
		$show  = $Page->show();// 分页显示输出
		$result = $order->queryNotConfirm($id,$Page,$_POST);
		$this->assign('Page',$show);// 赋值分页输出
		$this->assign('order',$result);
		$this->assign('start_date',$_POST['start_date']);
		$this->assign('end_date',$_POST['end_date']);
		$this->assign('keywords',$_POST['keywords']);
		$this->theme("default")->display("order_list_confirm");

	}	
	
	//加载订单已取消的页面（order_list_cancel）页面
	public function  order_list_cancel(){
		//加载布局文件
		layout("home");
		$id = LI('userId');
		$order = D('Home/Order', 'Logic');
		$Page = 0;
		$result = $order->queryCancel($id,$Page,$_POST);
		$count=count($result);
		$Page  = new \Think\Page($count,10);
		$show  = $Page->show();// 分页显示输出
		$result = $order->queryCancel($id,$Page,$_POST);
		$this->assign('Page',$show);// 赋值分页输出
		$this->assign('order',$result);
		$this->assign('start_date',$_POST['start_date']);
		$this->assign('end_date',$_POST['end_date']);
		$this->assign('keywords',$_POST['keywords']);
		$this->theme("default")->display("order_list_cancel");

	}	
	
	//加载已过期机票的页面 (exp_flight_list) 页面;
	 public function  exp_flight_list(){
		//加载布局文件
		layout("home");
		$id = LI('userId');
		$order = D('Home/Order', 'Logic');
		$Page = 0;
		$result = $order->queryExpFlight($id,$Page);
		$count=count($result);
		$Page  = new \Think\Page($count,10);
		$show  = $Page->show();// 分页显示输出
		$result = $order->queryExpFlight($id,$Page);
		//print_r($result);
		$this->assign('Page',$show);// 赋值分页输出
		$this->assign('order',$result);
		$this->theme("default")->display("exp_flight_list");
		
	 }
	 
	 
	 
	 
/*************  第二部分：对订单（order）的操作   **************/
	
	//付款
	public function  pay(){
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
	 
	 
	 //取消订单       分为两种情况：原状态是“待确认或待支付”开始的，跳转到“已取消”；
	 //                       原状态是“已确认”，现付跳转到“待退款”，月结跳转到“已退单”
	public function cancel(){
		
		$id = $_POST['id'];//获取订单id
		$order = M('order');
		$orderData['status'] = 19 ;
		
		$o_result = $order->where('id='.$id)->save($orderData); 
		if($o_result){
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
				
		################订单生成后进行短信的发送
		// 轻松预定生成订单后，通知TMC 公司
		// 需要传的参数：
		// TMC 邮箱（$data['tmc_email']） , TMC 电话（$data['tmc_phone']）;
		// 1->OP的姓名($data['op_name'])，2->预定人公司名称($data['co_name']),3->预定人的姓名($data['emp_name']),
		// 4->出发时间($data['begin_time']),5->航班信息($data['flight_content']);
		// 根据订单id查询出 订单的相关信息
		$company=M("company");
		$tmc_employee=M('tmc_employee');
		$order_info=M("order")->where('id='.$id)->find();
		$tmcemp_info=$tmc_employee->where("id=".$order_info['tmc_uid'])->find();
		//查询出user 表中的 wx_openid
		$user=M("user");
		$wx_openid=$user->where("id=".$tmcemp_info['u_id'])->find();

		$comId=Li('comId');
		$co_result=$company->where("id=".$comId)->find();
				
		$send=D("Home/SendMessage","Logic");
		$case="TmcOrder";
		$datt['wx_openid']=$wx_openid['wx_openid'];//获取 wx_openid;
		$datt['tmc_email']=$tmcemp_info['email'];//OP专员的邮箱
		$datt['tmc_phone']=$tmcemp_info['phone'];//OP专员的电话
		$datt['op_name']= $order_info['tmc_uname'];//op专员的姓名
		$datt['co_name']=$co_result['name'];//预定人公司的名称
		$datt['begin_time']= $order_info['time'];//订单时间
		$datt['flight_content']=$order_info['order_num'];//订单号
		$send->SendDetails($case,$datt);

		//------------------------确认订单时，消息发送-------------------------------
			$emp_id=Li("empId");//根据li方法查出empId；
			$wx_openid=Li("userOpenid");//获取用户的wx_openid；

			//用户确认订单后通知该人
			$employee=M("employee");
			$emp_info=$employee->where("id=".$emp_id)->find();

			// 轻松预定生成订单后进行短信和邮箱的发送;
			// 需要传的参数有：
			// 提交人的邮箱 （$data['emp_phone']）, 提交人的电话（$data['emp_email']）;
			// 1->提交人的姓名($data['emp_name']),2->TMC的公司($data['tmc_name']),
			// 3->航班出发的时间($data['begin_time'])，4->航班的内容($data['flight_content']);
			$send_1=D("Home/SendMessage","Logic");
			$case_1="QsxBook";
			$ress['emp_email']=$emp_info['email'];
			$ress['emp_phone']=$emp_info['phone'];
			$ress['emp_name']= $emp_info['name'];
			$ress['wx_openid']=$wx_openid;//传用户的wx_openid;
			$ress['begin_time'] = date ( 'Y-m-d H:i:s', time () );
			$ress['flight_content']=$order_info['order_num'];
			$send_1->SendDetails($case_1,$ress);

		//-------------------------------------------------------


				
				
			}else{
				$this->ajaxReturn(1);
			}
	}
	/*
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
	}*/
	

	
	
	
/***********第三部分：对机票状态（73go_flight_ticket_info  status）的操作********/
	/*
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
	*/
    //申请退票
	public function  flight_apply_refund_ticket($id){    //产品详情id
	    //加载布局文件
		layout("home");
		$tag = D('Home/Order', 'Logic');
		$change = $tag->queryChange($id,0,0);
		//print_r($change);
		$this->assign('change',$change);
		$this->theme("default")->display("refund_product");
	
	
	}
	
/*	
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
	*/
	//申请改签
	public function  flight_apply_change_ticket($id){ //产品详情id
		//加载布局文件
		layout("home");
		$tag = D('Home/Order', 'Logic');
		$change = $tag->queryChange($id,0,0);
		//print_r($change);
		$this->assign('change',$change);
		$this->theme("default")->display("change_product");
	}
	
	//补款
	public function  flight_add_money(){
		$id = $_POST['id'];//获取机票详情id
		$data['status'] =13 ;    //已补款
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
	

	
	
	
	
	
/***********第四部分：对酒店状态（73go_hotel_info          status）的操作********/
	/*
    //申请退单
	public function  hotel_apply_refund_order(){
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
	*/
    //申请退单
	public function  hotel_apply_refund_order($id){    //产品详情id
	    //加载布局文件
		layout("home");
		$tag = D('Home/Order', 'Logic');
		$change = $tag->queryChange(0,$id,0);
		//print_r($change);
		$this->assign('change',$change);
		$this->theme("default")->display("refund_product");
	
	
	}
	/*
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
	*/
	//申请改单
	public function  hotel_apply_change_order($id){
		//加载布局文件
		layout("home");
		$tag = D('Home/Order', 'Logic');
		$change = $tag->queryChange(0,$id,0);
		//print_r($change);
		$this->assign('change',$change);
		$this->theme("default")->display("change_product");
	}
	
	

	
	
	
/***********第五部分：对火车状态（73go_train_ticket_info   status）的操作********/	
	/*
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
	}*/
	
    //申请退票
	public function  train_apply_refund_ticket($id){    //产品详情id
	    //加载布局文件
		layout("home");
		$tag = D('Home/Order', 'Logic');
		$change = $tag->queryChange(0,0,$id);
		//print_r($change);
		$this->assign('change',$change);
		$this->theme("default")->display("refund_product");
	
	
	}
	
/*	
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
	*/
	//申请改签
	public function  train_apply_change_ticket($id){
		//加载布局文件
		layout("home");
		$tag = D('Home/Order', 'Logic');
		$change = $tag->queryChange(0,0,$id);
		//print_r($change);
		$this->assign('change',$change);
		$this->theme("default")->display("change_product");
	}
	
	//补款
	public function  train_add_money(){
		$id = $_POST['id'];//获取火车详情id
		$data['status'] =13 ;    //已补款
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
	

	
	
	

/***********第六部分：对其他状态（73go_other_produ_info    status）的操作********/	
	

/*
 ********** 完全按照订单（order）的状态改变
 */	
	

/***********第七部分：对保险状态（73go_air_insur_info      status）的操作********/
	
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
     

     
     
     
/******************** 详情页面 ****************************/     



	//酒店详情
	public function hotel_detail(){
				//加载布局
				layout("home");
				$id = $_GET['id'];
				$m = M('');
				$sql = "SELECT 
						t.*,
						o.order_num orderNum,
						o.time orderTime,
						tmc.name tmcName
						FROM 73go_hotel_info AS t
						LEFT JOIN 73go_order AS o ON o.id = t.o_id
						LEFT JOIN 73go_tmc AS tmc ON tmc.id = o.tmc_id
						WHERE t.id = ".$id;
				
				$sql1 = "SELECT
					t.*,
					b.name
					FROM	73go_hotel_userinfo AS t
					LEFT JOIN 73go_order_user AS b ON t.ou_id = b.id
					WHERE t.h_id = ".$id;
				
				$train = $m->query($sql);
				$info = $m->query($sql1);
				$this->assign('hotel',$train);
				$this->assign('info',$info);
				$this->theme("default")->display("hotel");

	}
	
     //机票详情
	public function flight_detail(){
		layout("home");
		$id = $_GET['id'];
		$m = M('');
		$sql = "SELECT 
					f.* ,
					(f.price+f.baf+f.acf+f.tax)  allprice,
					o.order_num orderNum,
					o.time orderTime,
					o.pay_type payType,
					tmc.name tmcName
					FROM 73go_flight_ticket_info AS f
					LEFT JOIN 73go_order AS o ON o.id = f.o_id
					LEFT JOIN 73go_tmc AS tmc ON tmc.id = o.tmc_id
					WHERE f.id = ".$id;
		
		$sql1 = "SELECT
					a.id,a.h_id,a.ou_id,a.seat_num,a.refund_price,a.status,
					b.name
					FROM	73go_flight_userinfo AS a
					LEFT JOIN 73go_order_user AS b ON a.ou_id = b.id
					WHERE a.h_id = ".$id;
		//echo $sql;
		$flight = $m->query($sql);
		$info = $m->query($sql1);

		$this->assign('flight',$flight);

		$this->assign('info',$info);
		$this->theme("default")->display("flight");

	}
	
	//火车票详情
	public function train_detail(){
				layout("home");
				$id = $_GET['id'];
				$m = M('');
				$sql = "SELECT 
						t.*,
						o.order_num orderNum,
						o.time orderTime,
						o.pay_type payType,
						tmc.name tmcName
						FROM 73go_train_ticket_info AS t
						LEFT JOIN 73go_order AS o ON o.id = t.o_id
						LEFT JOIN 73go_tmc AS tmc ON tmc.id = o.tmc_id
						WHERE t.id = ".$id;
				
				$sql1 = "SELECT
					t.*,
					b.name
					FROM	73go_train_ticket_userinfo AS t
					LEFT JOIN 73go_order_user AS b ON t.ou_id = b.id
					WHERE t.t_id = ".$id;
				//echo $sql1;
				$train = $m->query($sql);
				$info = $m->query($sql1);
				//print_r($train);
				$this->assign('train',$train);				
				$this->assign('info',$info);
				$this->theme("default")->display("train");
		
		
		
		}
	

/********** 对酒店使用者状态（73go_hotel_userinfo          status）的操作 ****************************/

	/*	
    //申请退单
	public function  hoteluser_apply_cancel_order(){
		$id = $_POST['id'];//获取酒店使用者id
		$data['status'] =5;    //待取消
		$hotelUser = M('hotel_userinfo');
		$h_result = $hotelUser->where('id='.$id)->save($data); 
		//$orderId = $hotelUser->where('id='.$id)->getField('o_id');//返回该酒店的订单id
		//$this->isInSameStatus($orderId);
		if($h_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}*/
/*	
    //申请改单
	public function  hoteluser_apply_change_order(){
		$id = $_POST['id'];//获取酒店详情id
		$data['status'] =3;    //待改单
		$hotelUser = M('hotel_userinfo');
		$h_result = $hotelUser->where('id='.$id)->save($data); 
		if($h_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	*/

	
/***********：对火车用户详情状态（73go_train_ticket_userinfo   status）的操作********/	
	/*
	//申请退票
	public function  trainuser_apply_refund_ticket(){
		$id = $_POST['id'];//获取火车详情id
		$data['status'] =9 ;     //待退票
		$trainTicket = M('train_ticket_userinfo');
		$trt_result = $trainTicket->where('id='.$id)->save($data); 
		$orderId = $trainTicket->where('id='.$id)->getField('o_id');//返回该火车的订单id
		$this->isInSameStatus($orderId);
		if($trt_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}*/
	
/*	
	//申请改签
	public function  trainuser_apply_change_ticket(){
		$id = $_POST['id'];//获取火车详情id
		$data['status'] =4 ;    //待改签
		$trainTicket = M('train_ticket_userinfo');
		$trt_result = $trainTicket->where('id='.$id)->save($data); 
		if($trt_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	*/
	//补款
	public function  trainuser_add_money(){
		$id = $_POST['id'];//获取火车详情id
		$data['status'] =13 ;    //已补款
		$trainTicket = M('train_ticket_userinfo');
		$trt_result = $trainTicket->where('id='.$id)->save($data); 
		if($trt_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
    //取消改签
	public function  trainuser_cancel_change_ticket(){
		$id = $_POST['id'];//获取火车详情id
		$data['status'] =14;    //已出票
		$trainTicket = M('train_ticket_userinfo');
		$trt_result = $trainTicket->where('id='.$id)->save($data); 
		if($trt_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
	
	
/***********对机票用户使用者详情状态（73go_flight_userinfo  status）的操作********/
	/*
	//申请退票
	public function  flightuser_apply_refund_ticket(){
		$id = $_POST['id'];//获取机票详情id
		$data['status'] =9 ;     //待退票
		$flightTicket = M('flight_userinfo');
		$ft_result = $flightTicket->where('id='.$id)->save($data); 
		$orderId = $flightTicket->where('id='.$id)->getField('o_id');//返回该机票的订单id
		$this->isInSameStatus($orderId);
		if($ft_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}*/
	
/*	
	//申请改签
	public function  flightuser_apply_change_ticket(){
		$id = $_POST['id'];//获取机票详情id
		$data['status'] =4 ;    //待改签
		$flightTicket = M('flight_userinfo');
		$ft_result = $flightTicket->where('id='.$id)->save($data); 
		if($ft_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	*/
	//补款
	public function  flightuser_add_money(){
		$id = $_POST['id'];//获取机票详情id
		$data['status'] =13 ;    //已补款
		$flightTicket = M('flight_userinfo');
		$ft_result = $flightTicket->where('id='.$id)->save($data); 
		if($ft_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	
    //取消改签
	public function  flightuser_cancel_change_ticket(){
		$id = $_POST['id'];//获取机票详情id
		$data['status'] =14;    //已出票
		$flightTicket = M('flight_userinfo');
		$ft_result = $flightTicket->where('id='.$id)->save($data); 
		if($ft_result){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
	}
	

/***********   第九部分：退改签操作      ****************/	
	
	
	//改签改单页面的提交申请
	public function change_submit(){
	
		//从页面传来的数据，除选中之外的id值为空，把空值改为0
		$flightId= $_POST['flightId'];           //$change[0]['choice']['flight'][0]['id'] 被选中的机票详情id
		$hotelId= $_POST['hotelId'];             //$change[0]['choice']['hotel'][0]['id']  被选中的酒店详情id
		$trainId= $_POST['trainId'];             //$change[0]['choice']['train'][0]['id']  被选中的火车详情id
	    $nameIdArray= $_POST['nameIdArray'];     //被选中使用者的订单使用者id 数组
	    $content = $_POST['content'];            //改签内容
	    $allOrder = $_POST['allOrder'];          //有没有选中其他订单产品。没有勾选为0，有勾选为1
	   
    
	if($allOrder == 0 ){                         //没有勾选订单其他产品的操作，只对单一产品进行改签改单
	    //查询出“订单退改签申请”表所需要存储的数据
		$tag = D('Home/Order', 'Logic');
		$change = $tag->queryChange($flightId,$hotelId,$trainId);
		$orderId = $change[0]['orderId'];        //订单id
		$orderNum = $change[0]['orderNum'];      //订单号
		if($flightId != 0){$table_id = $flightId; $table_name = 2; $cmd = 25;}     //表单id，表单名，cmd
        if($hotelId != 0){ $table_id = $hotelId;  $table_name = 3; $cmd = 24;}
		if($trainId != 0){ $table_id = $trainId;  $table_name = 4; $cmd = 25;}
		$bookUserId = $change[0]['userId'];      //预订人id
		$countCheckedName = count($nameIdArray);
	    foreach ($nameIdArray as $key1=>$vo1){
	    	if($key1 == $countCheckedName-1){
	    		$checkedUserId.= $vo1;
	    	}else{
	    		$checkedUserId.= $vo1.",";	 //使用者ID，即被选中的人id 格式：id1,id2,id3,id4
	    	}	
       	}
		$tmcId = $change[0]['tmcId'];            //tmc公司id
		
		
		//把所有信息存入“订单退改签申请”表
		$refundData['time'] = date("Y-m-d H:i:s",time());     //当前时间
        $refundData['o_num'] = $orderNum;                     //订单号
        $refundData['table_name'] = $table_name;              //表单名
        $refundData['table_id'] = $table_id;                  //表单id
        $refundData['cmd'] = $cmd;                            //执行的操作      字典表：cmd_def
        $refundData['u_id'] = $bookUserId;                    //预订人ID
        $refundData['user_ids'] = $checkedUserId;              //使用者ID，即被选中的人id
        $refundData['user_req'] = $content;                   //退改签要求
        $refundData['tmc_id'] = $tmcId;                       //TMC公司
        $refundData['status'] = 0;                            //状态req_status：未处理
        $order_refund_req = M('order_refund_req');
		$refundResult = $order_refund_req->data($refundData)->add();
        
		//修改状态为待改签或待改单
		//当产品status为98时，更改产品使用者的status，机票火车为4，酒店改为3
		//当产品status不为98时，只选择了部分人，则要添加相应的产品使用者表数据，更改产品使用者的status，机票火车为4，酒店改为3
		//当产品status不为98时，选择了所有人，则直接改变产品status
		$countOrderUser = count($change[0]['name']);
		
		if($flightId != 0){                                   //机票
			$flightUserStatusData['status'] = 4 ;    //待改签
			$flightStatusData['status'] = 4 ;    //待改签
			$flight_ticket_info = M('flight_ticket_info');
			$flight_userinfo = M('flight_userinfo');
			$flightStatus = $flight_ticket_info->where('id='.$flightId)->getField('status');
			if($flightStatus == 98){
			  foreach ($nameIdArray as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        $flightUserStatus = $flight_userinfo->where('ou_id='.$vo)->getField('status');
		        if($flightUserStatus == 14 || $flightUserStatus == 16 || $flightUserStatus ==18){   //判断原状态
		        	$fu_result = $flight_userinfo->where('ou_id='.$vo)->save($flightUserStatusData);
		        }
			  }
			}
		    else{                                             //当 产品status不为98时
		    	if($countCheckedName == $countOrderUser){     //全选了使用者，直接改产品status
		    		  $f_result = $flight_ticket_info->where('id='.$flightId)->save($flightStatusData);
		    	}
		    	elseif($countCheckedName < $countOrderUser){   //只选了部分使用者，则添加相应的“产品使用者表”数据
		    		  $Data98['status'] = 98;
		    		  $flight_ticket_info->where('id='.$flightId)->save($Data98);
		    	      foreach ($change[0]['name'] as $key=>$vo){           //$vo['id']订单使用者的id
		    	      	  $createFlightUserData['h_id'] = $flightId;
		    	      	  $createFlightUserData['ou_id'] = $vo['id'];
		    	      	  $createFlightUserData['status'] = $flightStatus; //存入原状态
		    	      	  $flight_userinfo->data($createFlightUserData)->add(); //添加相应的“产品使用者表”数据
		    	      	  
		    	      }
		    		  foreach ($nameIdArray as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        	      $fu_result = $flight_userinfo->where('ou_id='.$vo)->save($flightUserStatusData);
			          }
			             
		    	}
		    	
		    }
		}
		
    	if($hotelId != 0){                                    //酒店
    		$hotelUserStatusData['status'] = 3 ;    //待改单
    		$hotelStatusData['status'] = 3 ;    //待改单
    		$hotel_info = M('hotel_info');
    		$hotel_userinfo = M('hotel_userinfo');
    		$hotelStatus = $hotel_info->where('id='.$hotelId)->getField('status');
    		if($hotelStatus == 98){
			  foreach ($nameIdArray as $key=>$vo){              //被选中使用者的订单使用者id 数组
		        $hotelUserStatus = $hotel_userinfo->where('ou_id='.$vo)->getField('status');
		        if($hotelUserStatus == 25 || $hotelUserStatus == 15){
		        	$hu_result = $hotel_userinfo->where('ou_id='.$vo)->save($hotelUserStatusData);
		        }
			  }
    		}
    	    else{                                              //当 产品status不为98时
		    	if($countCheckedName == $countOrderUser){     //全选了使用者，直接改产品status
		    		  $h_result = $hotel_info->where('id='.$hotelId)->save($hotelStatusData);
		    	}
		    	elseif($countCheckedName < $countOrderUser){   //只选了部分使用者，则添加相应的“产品使用者表”数据
		    		  $Data98['status'] = 98;
		    		  $hotel_info->where('id='.$hotelId)->save($Data98);
		    	      foreach ($change[0]['name'] as $key=>$vo){           //$vo['id']订单使用者的id
		    	      	  $createHotelUserData['h_id'] = $hotelId;
		    	      	  $createHotelUserData['ou_id'] = $vo['id'];
		    	      	  $createHotelUserData['status'] = $hotelStatus; //存入原状态
		    	      	  $hotel_userinfo->data($createHotelUserData)->add(); //添加相应的“产品使用者表”数据
		    	      	  
		    	      }
		    		  foreach ($nameIdArray as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        	      $hu_result = $hotel_userinfo->where('ou_id='.$vo)->save($hotelUserStatusData);
			          }
			             
		    	}
		    	
		    }
		}
		
    	if($trainId != 0){                                    //火车
    		$trainUserStatusData['status'] = 4 ;    //待改签
    		$trainStatusData['status'] = 4 ;    //待改签
    		$train_ticket_info = M('train_ticket_info'); 
    		$train_ticket_userinfo = M('train_ticket_userinfo');
		    $trainStatus = $train_ticket_info->where('id='.$trainId)->getField('status'); 
		    if($trainStatus == 98){
			  foreach ($nameIdArray as $key=>$vo){              //被选中使用者的订单使用者id 数组
		        $trainUserStatus = $train_ticket_userinfo->where('ou_id='.$vo)->getField('status');
		        if($trainUserStatus == 14 || $trainUserStatus == 16 || $trainUserStatus ==18){
		        	$tu_result = $train_ticket_userinfo->where('ou_id='.$vo)->save($trainUserStatusData);
		        }
			  }
		    }
		    else{                                             //当 产品status不为98时
		    	if($countCheckedName == $countOrderUser){     //全选了使用者，直接改产品status
		    		  $t_result = $train_ticket_info->where('id='.$trainId)->save($trainStatusData);
		    	}
		    	elseif($countCheckedName < $countOrderUser){   //只选了部分使用者，火车已有“产品使用者表”数据，无需新建
		    		  $Data98['status'] = 98;
		    		  $train_ticket_info->where('id='.$trainId)->save($Data98);
		    	      foreach ($change[0]['name'] as $key=>$vo){           //$vo['id']订单使用者的id
		    	      	  $createTrainUserData['t_id'] = $trainId;
		    	      	  $createTrainUserData['ou_id'] = $vo['id'];
		    	      	  $createTrainUserData['status'] = $trainStatus; //存入原状态
		    	      	  $tu_result = $train_ticket_userinfo->add($createTrainUserData); //添加相应的“产品使用者表”数据
		    	      	  
		    	      }
		    		  foreach ($nameIdArray as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        	      $tu_result = $train_ticket_userinfo->where('ou_id='.$vo)->save($trainUserStatusData);
			          }
			             
		    	}
		    	
		    
		    }
		}

	}	


	
	elseif ($allOrder == 1)	{               //有勾选订单其他产品的操作，对订单内所有产品进行改签改单
		
	    //查询出“订单退改签申请”表所需要存储的数据
		$tag = D('Home/Order', 'Logic');
		$change = $tag->queryChange($flightId,$hotelId,$trainId);
		$orderId = $change[0]['orderId'];        //订单id
		$orderNum = $change[0]['orderNum'];      //订单号
		$bookUserId = $change[0]['userId'];      //预订人id
		$countCheckedName = count($nameIdArray);
	    foreach ($nameIdArray as $key1=>$vo1){
	    	if($key1 == $countCheckedName-1){
	    		$checkedUserId.= $vo1;
	    	}else{
	    		$checkedUserId.= $vo1.",";	 //使用者ID，即被选中的人id 格式：id1,id2,id3,id4
	    	}	
       	}
		$tmcId = $change[0]['tmcId'];            //tmc公司id
		$countOrderUser = count($change[0]['name']);          //计算订单使用者的人数
		
		//把所有信息存入“订单退改签申请”表
		$refundData['time'] = date("Y-m-d H:i:s",time());     //当前时间
        $refundData['o_num'] = $orderNum;                     //订单号
        $refundData['table_name'] = 1;                        //表单名
        $refundData['table_id'] = $orderId;                   //表单id
        $refundData['cmd'] = 24;                              //执行的操作      字典表：cmd_def 申请改单（整个订单）
        $refundData['u_id'] = $bookUserId;                    //预订人ID
        $refundData['user_ids'] = $checkedUserId;              //使用者ID，即被选中的人id
        $refundData['user_req'] = $content;                   //退改签要求
        $refundData['tmc_id'] = $tmcId;                       //TMC公司
        $refundData['status'] = 0;                            //状态req_status：未处理
        $order_refund_req = M('order_refund_req');
		$refundResult = $order_refund_req->data($refundData)->add();
        
		//修改订单状态为待改单，各产品为待改签或待改单
		//遍历订单内所有产品
		//把订单状态改为待改单3
		//当产品status为98时，更改产品使用者的status，机票火车为4，酒店改为3
		//当产品status不为98时，只选择了部分人，则要添加相应的产品使用者表数据，更改产品使用者的status，机票火车为4，酒店改为3
		//当产品status不为98时，选择了所有人，则直接改变产品status
				
		$order = M('order');
		$orderData['status'] = 3 ;
		$o_result = $order->where('id='.$orderId)->save($orderData);
		
		
		
		
		if($flightId != 0){                                   //被选中的机票id
			$flightUserStatusData['status'] = 4 ;    //待改签
			$flightStatusData['status'] = 4 ;    //待改签
			$flight_ticket_info = M('flight_ticket_info');
			$flight_userinfo = M('flight_userinfo');
			$flightStatus = $flight_ticket_info->where('id='.$flightId)->getField('status');
			if($flightStatus == 98){
			  foreach ($nameIdArray as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        $flightUserStatus = $flight_userinfo->where('ou_id='.$vo)->getField('status');
		        if($flightUserStatus == 14 || $flightUserStatus == 16 || $flightUserStatus ==18){   //判断原状态
		        	$fu_result = $flight_userinfo->where('ou_id='.$vo)->save($flightUserStatusData);
		        }
			  }
			}
		    else{                                             //当 产品status不为98时
		    	if($countCheckedName == $countOrderUser){     //全选了使用者，直接改产品status
		    		  $f_result = $flight_ticket_info->where('id='.$flightId)->save($flightStatusData);
		    	}
		    	elseif($countCheckedName < $countOrderUser){   //只选了部分使用者，则添加相应的“产品使用者表”数据
		    		  $Data98['status'] = 98;
		    		  $flight_ticket_info->where('id='.$flightId)->save($Data98);
		    	      foreach ($change[0]['name'] as $key=>$vo){           //$vo['id']订单使用者的id
		    	      	  $createFlightUserData['h_id'] = $flightId;
		    	      	  $createFlightUserData['ou_id'] = $vo['id'];
		    	      	  $createFlightUserData['status'] = $flightStatus; //存入原状态
		    	      	  $flight_userinfo->data($createFlightUserData)->add(); //添加相应的“产品使用者表”数据
		    	      	  
		    	      }
		    		  foreach ($nameIdArray as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        	      $fu_result = $flight_userinfo->where('ou_id='.$vo)->save($flightUserStatusData);
			          }
			             
		    	}
		    	
		    }
		}
		
    	if($hotelId != 0){                                    //被选中的酒店id
    		$hotelUserStatusData['status'] = 3 ;    //待改单
    		$hotelStatusData['status'] = 3 ;    //待改单
    		$hotel_info = M('hotel_info');
    		$hotel_userinfo = M('hotel_userinfo');
    		$hotelStatus = $hotel_info->where('id='.$hotelId)->getField('status');
    		if($hotelStatus == 98){
			  foreach ($nameIdArray as $key=>$vo){              //被选中使用者的订单使用者id 数组
		        $hotelUserStatus = $hotel_userinfo->where('ou_id='.$vo)->getField('status');
		        if($hotelUserStatus == 25 || $hotelUserStatus == 15){
		        	$hu_result = $hotel_userinfo->where('ou_id='.$vo)->save($hotelUserStatusData);
		        }
			  }
    		}
    	    else{                                              //当 产品status不为98时
		    	if($countCheckedName == $countOrderUser){     //全选了使用者，直接改产品status
		    		  $h_result = $hotel_info->where('id='.$hotelId)->save($hotelStatusData);
		    	}
		    	elseif($countCheckedName < $countOrderUser){   //只选了部分使用者，则添加相应的“产品使用者表”数据
		    		  $Data98['status'] = 98;
		    		  $hotel_info->where('id='.$hotelId)->save($Data98);
		    	      foreach ($change[0]['name'] as $key=>$vo){           //$vo['id']订单使用者的id
		    	      	  $createHotelUserData['h_id'] = $hotelId;
		    	      	  $createHotelUserData['ou_id'] = $vo['id'];
		    	      	  $createHotelUserData['status'] = $hotelStatus; //存入原状态
		    	      	  $hotel_userinfo->data($createHotelUserData)->add(); //添加相应的“产品使用者表”数据
		    	      	  
		    	      }
		    		  foreach ($nameIdArray as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        	      $hu_result = $hotel_userinfo->where('ou_id='.$vo)->save($hotelUserStatusData);
			          }
			             
		    	}
		    	
		    }
		}
		
    	if($trainId != 0){                                    //被选中的火车id
    		$trainUserStatusData['status'] = 4 ;    //待改签
    		$trainStatusData['status'] = 4 ;    //待改签
    		$train_ticket_info = M('train_ticket_info'); 
    		$train_ticket_userinfo = M('train_ticket_userinfo');
		    $trainStatus = $train_ticket_info->where('id='.$trainId)->getField('status'); 
		    if($trainStatus == 98){
			  foreach ($nameIdArray as $key=>$vo){              //被选中使用者的订单使用者id 数组
		        $trainUserStatus = $train_ticket_userinfo->where('ou_id='.$vo)->getField('status');
		        if($trainUserStatus == 14 || $trainUserStatus == 16 || $trainUserStatus ==18){
		        	$tu_result = $train_ticket_userinfo->where('ou_id='.$vo)->save($trainUserStatusData);
		        }
			  }
		    }
		    else{                                             //当 产品status不为98时
		    	if($countCheckedName == $countOrderUser){     //全选了使用者，直接改产品status
		    		  $t_result = $train_ticket_info->where('id='.$trainId)->save($trainStatusData);
		    	}
		    	elseif($countCheckedName < $countOrderUser){   //只选了部分使用者，火车已有“产品使用者表”数据，无需新建
		    		  $Data98['status'] = 98;
		    		  $train_ticket_info->where('id='.$trainId)->save($Data98);
		    	      foreach ($change[0]['name'] as $key=>$vo){           //$vo['id']订单使用者的id
		    	      	  $createTrainUserData['t_id'] = $trainId;
		    	      	  $createTrainUserData['ou_id'] = $vo['id'];
		    	      	  $createTrainUserData['status'] = $trainStatus; //存入原状态
		    	      	  $tu_result = $train_ticket_userinfo->add($createTrainUserData); //添加相应的“产品使用者表”数据
		    	      	  
		    	      }
		    		  foreach ($nameIdArray as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        	      $tu_result = $train_ticket_userinfo->where('ou_id='.$vo)->save($trainUserStatusData);
			          }
			             
		    	}
		    	
		    
		    }
		}
		

			foreach ($change[0]['notChoice']['flight'] as $key=>$flightId){
				                                   
			$flightUserStatusData['status'] = 4 ;    //待改签
			$flightStatusData['status'] = 4 ;    //待改签
			$flight_ticket_info = M('flight_ticket_info');
			$flight_userinfo = M('flight_userinfo');
			$flightStatus = $flight_ticket_info->where('id='.$flightId['id'])->getField('status');
			if($flightStatus == 98){
			  foreach ($nameIdArray as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        $flightUserStatus = $flight_userinfo->where('ou_id='.$vo)->getField('status');
		        if($flightUserStatus == 14 || $flightUserStatus == 16 || $flightUserStatus ==18){   //判断原状态
		        	$fu_result = $flight_userinfo->where('ou_id='.$vo)->save($flightUserStatusData);
		        }
			  }
			}
		    else{                                             //当 产品status不为98时
		    	
		      if($flightStatus == 14 || $flightStatus == 16 || $flightStatus ==18){
		    	
		    	if($countCheckedName == $countOrderUser){     //全选了使用者，直接改产品status
		    		  $f_result = $flight_ticket_info->where('id='.$flightId['id'])->save($flightStatusData);
		    	}
		    	elseif($countCheckedName < $countOrderUser){   //只选了部分使用者，则添加相应的“产品使用者表”数据
		    		  $Data98['status'] = 98;
		    		  $flight_ticket_info->where('id='.$flightId['id'])->save($Data98);
		    	      foreach ($change[0]['name'] as $key=>$vo){           //$vo['id']订单使用者的id
		    	      	  $createFlightUserData['h_id'] = $flightId['id'];
		    	      	  $createFlightUserData['ou_id'] = $vo['id'];
		    	      	  $createFlightUserData['status'] = $flightStatus; //存入原状态
		    	      	  $flight_userinfo->data($createFlightUserData)->add(); //添加相应的“产品使用者表”数据
		    	      	  
		    	      }
		    		  foreach ($nameIdArray as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        	      $fu_result = $flight_userinfo->where('ou_id='.$vo)->save($flightUserStatusData);
			          }
			             
		    	}
		      }
		    }
		
				
			}

			foreach ($change[0]['notChoice']['hotel'] as $key=>$hotelId){
				                                    
    		$hotelUserStatusData['status'] = 3 ;    //待改单
    		$hotelStatusData['status'] = 3 ;    //待改单
    		$hotel_info = M('hotel_info');
    		$hotel_userinfo = M('hotel_userinfo');
    		$hotelStatus = $hotel_info->where('id='.$hotelId['id'])->getField('status');
    		if($hotelStatus == 98){
			  foreach ($nameIdArray as $key=>$vo){              //被选中使用者的订单使用者id 数组
		        $hotelUserStatus = $hotel_userinfo->where('ou_id='.$vo)->getField('status');
		        if($hotelUserStatus == 25 || $hotelUserStatus == 15){
		        	$hu_result = $hotel_userinfo->where('ou_id='.$vo)->save($hotelUserStatusData);
		        }
			  }
    		}
    	    else{                                              //当 产品status不为98时
    	    	
    	      if($hotelStatus == 25 || $hotelStatus == 15){
    	    	
		    	if($countCheckedName == $countOrderUser){     //全选了使用者，直接改产品status
		    		  $h_result = $hotel_info->where('id='.$hotelId['id'])->save($hotelStatusData);
		    	}
		    	elseif($countCheckedName < $countOrderUser){   //只选了部分使用者，则添加相应的“产品使用者表”数据
		    		  $Data98['status'] = 98;
		    		  $hotel_info->where('id='.$hotelId['id'])->save($Data98);
		    	      foreach ($change[0]['name'] as $key=>$vo){           //$vo['id']订单使用者的id
		    	      	  $createHotelUserData['h_id'] = $hotelId['id'];
		    	      	  $createHotelUserData['ou_id'] = $vo['id'];
		    	      	  $createHotelUserData['status'] = $hotelStatus; //存入原状态
		    	      	  $hotel_userinfo->data($createHotelUserData)->add(); //添加相应的“产品使用者表”数据
		    	      	  
		    	      }
		    		  foreach ($nameIdArray as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        	      $hu_result = $hotel_userinfo->where('ou_id='.$vo)->save($hotelUserStatusData);
			          }
			             
		    	}
    	      }
		    }
		
		
			}
			


			foreach ($change[0]['notChoice']['train'] as $key=>$trainId){
				                                  
    		$trainUserStatusData['status'] = 4 ;    //待改签
    		$trainStatusData['status'] = 4 ;    //待改签
    		$train_ticket_info = M('train_ticket_info'); 
    		$train_ticket_userinfo = M('train_ticket_userinfo');
		    $trainStatus = $train_ticket_info->where('id='.$trainId['id'])->getField('status'); 
		    if($trainStatus == 98){
			  foreach ($nameIdArray as $key=>$vo){              //被选中使用者的订单使用者id 数组
		        $trainUserStatus = $train_ticket_userinfo->where('ou_id='.$vo)->getField('status');
		        if($trainUserStatus == 14 || $trainUserStatus == 16 || $trainUserStatus ==18){
		        	$tu_result = $train_ticket_userinfo->where('ou_id='.$vo)->save($trainUserStatusData);
		        }
			  }
		    }
		    else{                                             //当 产品status不为98时
		    	
		      if($trainStatus == 14 || $trainStatus == 16 || $trainStatus ==18){
		    	
		    	if($countCheckedName == $countOrderUser){     //全选了使用者，直接改产品status
		    		  $t_result = $train_ticket_info->where('id='.$trainId['id'])->save($trainStatusData);
		    	}
		    	elseif($countCheckedName < $countOrderUser){   //只选了部分使用者，火车已有“产品使用者表”数据，无需新建
		    		  $Data98['status'] = 98;
		    		  $train_ticket_info->where('id='.$trainId['id'])->save($Data98);
		    	      foreach ($change[0]['name'] as $key=>$vo){           //$vo['id']订单使用者的id
		    	      	  $createTrainUserData['t_id'] = $trainId['id'];
		    	      	  $createTrainUserData['ou_id'] = $vo['id'];
		    	          $createTrainUserData['status'] = $trainStatus; //存入原状态
		    	      	  $tu_result = $train_ticket_userinfo->add($createTrainUserData); //添加相应的“产品使用者表”数据
		    	      	  
		    	      }
		    		  foreach ($nameIdArray as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        	      $tu_result = $train_ticket_userinfo->where('ou_id='.$vo)->save($trainUserStatusData);
			          }
			             
		    	}
		      }
		    
		    }
		
			}

	}	

	    if($refundResult){
				$this->ajaxReturn(0);  //成功添加申请表
			}else{
				$this->ajaxReturn(1);
			}	
			
	}
	
	
	
	
	
	
	
	
	//退票退单页面的提交申请
	public function refund_submit(){
	
		//从页面传来的数据，除选中之外的id值为空，把空值改为0
		$flightId= $_POST['flightId'];           //$change[0]['choice']['flight'][0]['id'] 被选中的机票详情id
		$hotelId= $_POST['hotelId'];             //$change[0]['choice']['hotel'][0]['id']  被选中的酒店详情id
		$trainId= $_POST['trainId'];             //$change[0]['choice']['train'][0]['id']  被选中的火车详情id
	    $nameIdArray= $_POST['nameIdArray'];     //被选中使用者的订单使用者id 数组
	    $content = $_POST['content'];            //改签内容
	    $allOrder = $_POST['allOrder'];          //有没有选中其他订单产品。没有勾选为0，有勾选为1
	   
    
	if($allOrder == 0 ){                         //没有勾选订单其他产品的操作，只对单一产品进行退票退单
	    //查询出“订单退改签申请”表所需要存储的数据
		$tag = D('Home/Order', 'Logic');
		$change = $tag->queryChange($flightId,$hotelId,$trainId);
		$orderId = $change[0]['orderId'];        //订单id
		$orderNum = $change[0]['orderNum'];      //订单号
		if($flightId != 0){$table_id = $flightId; $table_name = 2; $cmd = 27;}     //表单id，表单名，cmd
        if($hotelId != 0){ $table_id = $hotelId;  $table_name = 3; $cmd = 26;}
		if($trainId != 0){ $table_id = $trainId;  $table_name = 4; $cmd = 27;}
		$bookUserId = $change[0]['userId'];      //预订人id
		$countCheckedName = count($nameIdArray);
	    foreach ($nameIdArray as $key1=>$vo1){
	    	if($key1 == $countCheckedName-1){
	    		$checkedUserId.= $vo1;
	    	}else{
	    		$checkedUserId.= $vo1.",";	 //使用者ID，即被选中的人id 格式：id1,id2,id3,id4
	    	}	
       	}
       	$tmcId = $change[0]['tmcId'];            //tmc公司id
		
		
		//把所有信息存入“订单退改签申请”表
		$refundData['time'] = date("Y-m-d H:i:s",time());     //当前时间
        $refundData['o_num'] = $orderNum;                     //订单号
        $refundData['table_name'] = $table_name;              //表单名
        $refundData['table_id'] = $table_id;                  //表单id
        $refundData['cmd'] = $cmd;                            //执行的操作      字典表：cmd_def
        $refundData['u_id'] = $bookUserId;                    //预订人ID
        $refundData['user_ids'] = $checkedUserId;              //使用者ID，即被选中的人id
        $refundData['user_req'] = $content;                   //退改签要求
        $refundData['tmc_id'] = $tmcId;                       //TMC公司
        $refundData['status'] = 0;                            //状态req_status：未处理
        $order_refund_req = M('order_refund_req');
		$refundResult = $order_refund_req->data($refundData)->add();
        
		//修改状态为待退票或待取消
		//当产品status为98时，更改产品使用者的status，机票火车为9，酒店改为5
		//当产品status不为98时，只选择了部分人，则要添加相应的产品使用者表数据，更改产品使用者的status，机票火车为9，酒店改为5
		//当产品status不为98时，选择了所有人，则直接改变产品status
		$countOrderUser = count($change[0]['name']);
		
		if($flightId != 0){                                   //机票
			$flightUserStatusData['status'] = 9 ;    //待退票
			$flightStatusData['status'] = 9 ;    //待退票
			$flight_ticket_info = M('flight_ticket_info');
			$flight_userinfo = M('flight_userinfo');
			$flightStatus = $flight_ticket_info->where('id='.$flightId)->getField('status');
			if($flightStatus == 98){
			  foreach ($nameIdArray as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        $flightUserStatus = $flight_userinfo->where('ou_id='.$vo)->getField('status');
		        if($flightUserStatus == 14 || $flightUserStatus == 16 || $flightUserStatus ==18){   //判断原状态
		        	$fu_result = $flight_userinfo->where('ou_id='.$vo)->save($flightUserStatusData);
		        }
			  }
			}
		    else{                                             //当 产品status不为98时
		    	if($countCheckedName == $countOrderUser){     //全选了使用者，直接改产品status
		    		  $f_result = $flight_ticket_info->where('id='.$flightId)->save($flightStatusData);
		    	}
		    	elseif($countCheckedName < $countOrderUser){   //只选了部分使用者，则添加相应的“产品使用者表”数据
		    		  $Data98['status'] = 98;
		    		  $flight_ticket_info->where('id='.$flightId)->save($Data98);
		    	      foreach ($change[0]['name'] as $key=>$vo){           //$vo['id']订单使用者的id
		    	      	  $createFlightUserData['h_id'] = $flightId;
		    	      	  $createFlightUserData['ou_id'] = $vo['id'];
		    	      	  $createFlightUserData['status'] = $flightStatus; //存入原状态
		    	      	  $flight_userinfo->data($createFlightUserData)->add(); //添加相应的“产品使用者表”数据
		    	      	  
		    	      }
		    		  foreach ($nameIdArray as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        	      $fu_result = $flight_userinfo->where('ou_id='.$vo)->save($flightUserStatusData);
			          }
			             
		    	}
		    	
		    }
		}
		
    	if($hotelId != 0){                                    //酒店
    		$hotelUserStatusData['status'] = 5 ;    //待取消
    		$hotelStatusData['status'] = 5 ;    //待取消
    		$hotel_info = M('hotel_info');
    		$hotel_userinfo = M('hotel_userinfo');
    		$hotelStatus = $hotel_info->where('id='.$hotelId)->getField('status');
    		if($hotelStatus == 98){
			  foreach ($nameIdArray as $key=>$vo){              //被选中使用者的订单使用者id 数组
		        $hotelUserStatus = $hotel_userinfo->where('ou_id='.$vo)->getField('status');
		        if($hotelUserStatus == 25 || $hotelUserStatus == 15){
		        	$hu_result = $hotel_userinfo->where('ou_id='.$vo)->save($hotelUserStatusData);
		        }
			  }
    		}
    	    else{                                              //当 产品status不为98时
		    	if($countCheckedName == $countOrderUser){     //全选了使用者，直接改产品status
		    		  $h_result = $hotel_info->where('id='.$hotelId)->save($hotelStatusData);
		    	}
		    	elseif($countCheckedName < $countOrderUser){   //只选了部分使用者，则添加相应的“产品使用者表”数据
		    		  $Data98['status'] = 98;
		    		  $hotel_info->where('id='.$hotelId)->save($Data98);
		    	      foreach ($change[0]['name'] as $key=>$vo){           //$vo['id']订单使用者的id
		    	      	  $createHotelUserData['h_id'] = $hotelId;
		    	      	  $createHotelUserData['ou_id'] = $vo['id'];
		    	      	  $createHotelUserData['status'] = $hotelStatus; //存入原状态
		    	      	  $hotel_userinfo->data($createHotelUserData)->add(); //添加相应的“产品使用者表”数据
		    	      	  
		    	      }
		    		  foreach ($nameIdArray as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        	      $hu_result = $hotel_userinfo->where('ou_id='.$vo)->save($hotelUserStatusData);
			          }
			             
		    	}
		    	
		    }
		}
		
    	if($trainId != 0){                                    //火车
    		$trainUserStatusData['status'] = 9 ;    //待退票
    		$trainStatusData['status'] = 9 ;    //待退票
    		$train_ticket_info = M('train_ticket_info'); 
    		$train_ticket_userinfo = M('train_ticket_userinfo');
		    $trainStatus = $train_ticket_info->where('id='.$trainId)->getField('status'); 
		    if($trainStatus == 98){
			  foreach ($nameIdArray as $key=>$vo){              //被选中使用者的订单使用者id 数组
		        $trainUserStatus = $train_ticket_userinfo->where('ou_id='.$vo)->getField('status');
		        if($trainUserStatus == 14 || $trainUserStatus == 16 || $trainUserStatus ==18){
		        	$tu_result = $train_ticket_userinfo->where('ou_id='.$vo)->save($trainUserStatusData);
		        }
			  }
		    }
		    else{                                             //当 产品status不为98时
		    	if($countCheckedName == $countOrderUser){     //全选了使用者，直接改产品status
		    		  $t_result = $train_ticket_info->where('id='.$trainId)->save($trainStatusData);
		    	}
		    	elseif($countCheckedName < $countOrderUser){   //只选了部分使用者，火车已有“产品使用者表”数据，无需新建
		    		  $Data98['status'] = 98;
		    		  $train_ticket_info->where('id='.$trainId)->save($Data98);
		    	      foreach ($change[0]['name'] as $key=>$vo){           //$vo['id']订单使用者的id
		    	      	  $createTrainUserData['t_id'] = $trainId;
		    	      	  $createTrainUserData['ou_id'] = $vo['id'];
		    	      	  $createTrainUserData['status'] = $trainStatus; //存入原状态
		    	      	  $tu_result = $train_ticket_userinfo->add($createTrainUserData); //添加相应的“产品使用者表”数据
		    	      	  
		    	      }
		    		  foreach ($nameIdArray as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        	      $tu_result = $train_ticket_userinfo->where('ou_id='.$vo)->save($trainUserStatusData);
			          }
			             
		    	}
		    	
		    
		    }
		}

	}	


	
	elseif ($allOrder == 1)	{               //有勾选订单其他产品的操作，对订单内所有产品进行退票退单
		
	    //查询出“订单退改签申请”表所需要存储的数据
		$tag = D('Home/Order', 'Logic');
		$change = $tag->queryChange($flightId,$hotelId,$trainId);
		$orderId = $change[0]['orderId'];        //订单id
		$orderNum = $change[0]['orderNum'];      //订单号
		$bookUserId = $change[0]['userId'];      //预订人id
		$countCheckedName = count($nameIdArray);
	    foreach ($nameIdArray as $key1=>$vo1){
	    	if($key1 == $countCheckedName-1){
	    		$checkedUserId.= $vo1;
	    	}else{
	    		$checkedUserId.= $vo1.",";	 //使用者ID，即被选中的人id 格式：id1,id2,id3,id4
	    	}	
       	}
		$tmcId = $change[0]['tmcId'];            //tmc公司id
		$countOrderUser = count($change[0]['name']);          //计算订单使用者的人数
		
		//把所有信息存入“订单退改签申请”表
		$refundData['time'] = date("Y-m-d H:i:s",time());     //当前时间
        $refundData['o_num'] = $orderNum;                     //订单号
        $refundData['table_name'] = 1;                        //表单名
        $refundData['table_id'] = $orderId;                   //表单id
        $refundData['cmd'] = 26;                              //执行的操作      字典表：cmd_def 申请退单（整个订单）
        $refundData['u_id'] = $bookUserId;                    //预订人ID
        $refundData['user_ids'] = $checkedUserId;              //使用者ID，即被选中的人id
        $refundData['user_req'] = $content;                   //退改签要求
        $refundData['tmc_id'] = $tmcId;                       //TMC公司
        $refundData['status'] = 0;                            //状态req_status：未处理
        $order_refund_req = M('order_refund_req');
		$refundResult = $order_refund_req->data($refundData)->add();
        
		//修改订单状态为待改单，各产品为待改签或待改单
		//遍历订单内所有产品
		//把订单状态改为待退单7
		//当产品status为98时，更改产品使用者的status，机票火车为4，酒店改为3
		//当产品status不为98时，只选择了部分人，则要添加相应的产品使用者表数据，更改产品使用者的status，机票火车为4，酒店改为3
		//当产品status不为98时，选择了所有人，则直接改变产品status
				
		$order = M('order');
		$orderData['status'] = 7 ;
		$o_result = $order->where('id='.$orderId)->save($orderData);
		
		
		
		
		if($flightId != 0){                                   //被选中的机票id
			$flightUserStatusData['status'] = 9 ;    //待退票
			$flightStatusData['status'] = 9 ;    //待退票
			$flight_ticket_info = M('flight_ticket_info');
			$flight_userinfo = M('flight_userinfo');
			$flightStatus = $flight_ticket_info->where('id='.$flightId)->getField('status');
			if($flightStatus == 98){
			  foreach ($nameIdArray as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        $flightUserStatus = $flight_userinfo->where('ou_id='.$vo)->getField('status');
		        if($flightUserStatus == 14 || $flightUserStatus == 16 || $flightUserStatus ==18){   //判断原状态
		        	$fu_result = $flight_userinfo->where('ou_id='.$vo)->save($flightUserStatusData);
		        }
			  }
			}
		    else{                                             //当 产品status不为98时
		    	if($countCheckedName == $countOrderUser){     //全选了使用者，直接改产品status
		    		  $f_result = $flight_ticket_info->where('id='.$flightId)->save($flightStatusData);
		    	}
		    	elseif($countCheckedName < $countOrderUser){   //只选了部分使用者，则添加相应的“产品使用者表”数据
		    		  $Data98['status'] = 98;
		    		  $flight_ticket_info->where('id='.$flightId)->save($Data98);
		    	      foreach ($change[0]['name'] as $key=>$vo){           //$vo['id']订单使用者的id
		    	      	  $createFlightUserData['h_id'] = $flightId;
		    	      	  $createFlightUserData['ou_id'] = $vo['id'];
		    	      	  $createFlightUserData['status'] = $flightStatus; //存入原状态
		    	      	  $flight_userinfo->data($createFlightUserData)->add(); //添加相应的“产品使用者表”数据
		    	      	  
		    	      }
		    		  foreach ($nameIdArray as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        	      $fu_result = $flight_userinfo->where('ou_id='.$vo)->save($flightUserStatusData);
			          }
			             
		    	}
		    	
		    }
		}
		
    	if($hotelId != 0){                                    //被选中的酒店id
    		$hotelUserStatusData['status'] = 5 ;    //待取消
    		$hotelStatusData['status'] = 5 ;    //待取消
    		$hotel_info = M('hotel_info');
    		$hotel_userinfo = M('hotel_userinfo');
    		$hotelStatus = $hotel_info->where('id='.$hotelId)->getField('status');
    		if($hotelStatus == 98){
			  foreach ($nameIdArray as $key=>$vo){              //被选中使用者的订单使用者id 数组
		        $hotelUserStatus = $hotel_userinfo->where('ou_id='.$vo)->getField('status');
		        if($hotelUserStatus == 25 || $hotelUserStatus == 15){
		        	$hu_result = $hotel_userinfo->where('ou_id='.$vo)->save($hotelUserStatusData);
		        }
			  }
    		}
    	    else{                                              //当 产品status不为98时
		    	if($countCheckedName == $countOrderUser){     //全选了使用者，直接改产品status
		    		  $h_result = $hotel_info->where('id='.$hotelId)->save($hotelStatusData);
		    	}
		    	elseif($countCheckedName < $countOrderUser){   //只选了部分使用者，则添加相应的“产品使用者表”数据
		    		  $Data98['status'] = 98;
		    		  $hotel_info->where('id='.$hotelId)->save($Data98);
		    	      foreach ($change[0]['name'] as $key=>$vo){           //$vo['id']订单使用者的id
		    	      	  $createHotelUserData['h_id'] = $hotelId;
		    	      	  $createHotelUserData['ou_id'] = $vo['id'];
		    	      	  $createHotelUserData['status'] = $hotelStatus; //存入原状态
		    	      	  $hotel_userinfo->data($createHotelUserData)->add(); //添加相应的“产品使用者表”数据
		    	      	  
		    	      }
		    		  foreach ($nameIdArray as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        	      $hu_result = $hotel_userinfo->where('ou_id='.$vo)->save($hotelUserStatusData);
			          }
			             
		    	}
		    	
		    }
		}
		
    	if($trainId != 0){                                    //被选中的火车id
    		$trainUserStatusData['status'] = 9 ;    //待退票
    		$trainStatusData['status'] = 9 ;    //待退票
    		$train_ticket_info = M('train_ticket_info'); 
    		$train_ticket_userinfo = M('train_ticket_userinfo');
		    $trainStatus = $train_ticket_info->where('id='.$trainId)->getField('status'); 
		    if($trainStatus == 98){
			  foreach ($nameIdArray as $key=>$vo){              //被选中使用者的订单使用者id 数组
		        $trainUserStatus = $train_ticket_userinfo->where('ou_id='.$vo)->getField('status');
		        if($trainUserStatus == 14 || $trainUserStatus == 16 || $trainUserStatus ==18){
		        	$tu_result = $train_ticket_userinfo->where('ou_id='.$vo)->save($trainUserStatusData);
		        }
			  }
		    }
		    else{                                             //当 产品status不为98时
		    	if($countCheckedName == $countOrderUser){     //全选了使用者，直接改产品status
		    		  $t_result = $train_ticket_info->where('id='.$trainId)->save($trainStatusData);
		    	}
		    	elseif($countCheckedName < $countOrderUser){   //只选了部分使用者，火车已有“产品使用者表”数据，无需新建
		    		  $Data98['status'] = 98;
		    		  $train_ticket_info->where('id='.$trainId)->save($Data98);
		    	      foreach ($change[0]['name'] as $key=>$vo){           //$vo['id']订单使用者的id
		    	      	  $createTrainUserData['t_id'] = $trainId;
		    	      	  $createTrainUserData['ou_id'] = $vo['id'];
		    	      	  $createTrainUserData['status'] = $trainStatus; //存入原状态
		    	      	  $tu_result = $train_ticket_userinfo->add($createTrainUserData); //添加相应的“产品使用者表”数据
		    	      	  
		    	      }
		    		  foreach ($nameIdArray as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        	      $tu_result = $train_ticket_userinfo->where('ou_id='.$vo)->save($trainUserStatusData);
			          }
			             
		    	}
		    	
		    
		    }
		}
		

			foreach ($change[0]['notChoice']['flight'] as $key=>$flightId){
				                                   
			$flightUserStatusData['status'] = 9 ;    //待退票
			$flightStatusData['status'] = 9 ;    //待退票
			$flight_ticket_info = M('flight_ticket_info');
			$flight_userinfo = M('flight_userinfo');
			$flightStatus = $flight_ticket_info->where('id='.$flightId['id'])->getField('status');
			if($flightStatus == 98){
			  foreach ($nameIdArray as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        $flightUserStatus = $flight_userinfo->where('ou_id='.$vo)->getField('status');
		        if($flightUserStatus == 14 || $flightUserStatus == 16 || $flightUserStatus ==18){   //判断原状态
		        	$fu_result = $flight_userinfo->where('ou_id='.$vo)->save($flightUserStatusData);
		        }
			  }
			}
		    else{                                             //当 产品status不为98时
		    	
		      if($flightStatus == 14 || $flightStatus == 16 || $flightStatus ==18){
		    	
		    	if($countCheckedName == $countOrderUser){     //全选了使用者，直接改产品status
		    		  $f_result = $flight_ticket_info->where('id='.$flightId['id'])->save($flightStatusData);
		    	}
		    	elseif($countCheckedName < $countOrderUser){   //只选了部分使用者，则添加相应的“产品使用者表”数据
		    		  $Data98['status'] = 98;
		    		  $flight_ticket_info->where('id='.$flightId['id'])->save($Data98);
		    	      foreach ($change[0]['name'] as $key=>$vo){           //$vo['id']订单使用者的id
		    	      	  $createFlightUserData['h_id'] = $flightId['id'];
		    	      	  $createFlightUserData['ou_id'] = $vo['id'];
		    	      	  $createFlightUserData['status'] = $flightStatus; //存入原状态
		    	      	  $flight_userinfo->data($createFlightUserData)->add(); //添加相应的“产品使用者表”数据
		    	      	  
		    	      }
		    		  foreach ($nameIdArray as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        	      $fu_result = $flight_userinfo->where('ou_id='.$vo)->save($flightUserStatusData);
			          }
			             
		    	}
		      }
		    }
		
				
			}

			foreach ($change[0]['notChoice']['hotel'] as $key=>$hotelId){
				                                    
    		$hotelUserStatusData['status'] = 5 ;    //待取消
    		$hotelStatusData['status'] = 5 ;    //待取消
    		$hotel_info = M('hotel_info');
    		$hotel_userinfo = M('hotel_userinfo');
    		$hotelStatus = $hotel_info->where('id='.$hotelId['id'])->getField('status');
    		if($hotelStatus == 98){
			  foreach ($nameIdArray as $key=>$vo){              //被选中使用者的订单使用者id 数组
		        $hotelUserStatus = $hotel_userinfo->where('ou_id='.$vo)->getField('status');
		        if($hotelUserStatus == 25 || $hotelUserStatus == 15){
		        	$hu_result = $hotel_userinfo->where('ou_id='.$vo)->save($hotelUserStatusData);
		        }
			  }
    		}
    	    else{                                              //当 产品status不为98时
    	    	
    	      if($hotelStatus == 25 || $hotelStatus == 15){
    	    	
		    	if($countCheckedName == $countOrderUser){     //全选了使用者，直接改产品status
		    		  $h_result = $hotel_info->where('id='.$hotelId['id'])->save($hotelStatusData);
		    	}
		    	elseif($countCheckedName < $countOrderUser){   //只选了部分使用者，则添加相应的“产品使用者表”数据
		    		  $Data98['status'] = 98;
		    		  $hotel_info->where('id='.$hotelId['id'])->save($Data98);
		    	      foreach ($change[0]['name'] as $key=>$vo){           //$vo['id']订单使用者的id
		    	      	  $createHotelUserData['h_id'] = $hotelId['id'];
		    	      	  $createHotelUserData['ou_id'] = $vo['id'];
		    	      	  $createHotelUserData['status'] = $hotelStatus; //存入原状态
		    	      	  $hotel_userinfo->data($createHotelUserData)->add(); //添加相应的“产品使用者表”数据
		    	      	  
		    	      }
		    		  foreach ($nameIdArray as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        	      $hu_result = $hotel_userinfo->where('ou_id='.$vo)->save($hotelUserStatusData);
			          }
			             
		    	}
    	      }
		    }
		
		
			}
			


			foreach ($change[0]['notChoice']['train'] as $key=>$trainId){
				                                  
    		$trainUserStatusData['status'] = 9 ;    //待退票
    		$trainStatusData['status'] = 9 ;    //待退票
    		$train_ticket_info = M('train_ticket_info'); 
    		$train_ticket_userinfo = M('train_ticket_userinfo');
		    $trainStatus = $train_ticket_info->where('id='.$trainId['id'])->getField('status'); 
		    if($trainStatus == 98){
			  foreach ($nameIdArray as $key=>$vo){              //被选中使用者的订单使用者id 数组
		        $trainUserStatus = $train_ticket_userinfo->where('ou_id='.$vo)->getField('status');
		        if($trainUserStatus == 14 || $trainUserStatus == 16 || $trainUserStatus ==18){
		        	$tu_result = $train_ticket_userinfo->where('ou_id='.$vo)->save($trainUserStatusData);
		        }
			  }
		    }
		    else{                                             //当 产品status不为98时
		    	
		      if($trainStatus == 14 || $trainStatus == 16 || $trainStatus ==18){
		    	
		    	if($countCheckedName == $countOrderUser){     //全选了使用者，直接改产品status
		    		  $t_result = $train_ticket_info->where('id='.$trainId['id'])->save($trainStatusData);
		    	}
		    	elseif($countCheckedName < $countOrderUser){   //只选了部分使用者，火车已有“产品使用者表”数据，无需新建
		    		  $Data98['status'] = 98;
		    		  $train_ticket_info->where('id='.$trainId['id'])->save($Data98);
		    	      foreach ($change[0]['name'] as $key=>$vo){           //$vo['id']订单使用者的id
		    	      	  $createTrainUserData['t_id'] = $trainId['id'];
		    	      	  $createTrainUserData['ou_id'] = $vo['id'];
		    	      	  $createTrainUserData['status'] = $trainStatus; //存入原状态
		    	      	  $tu_result = $train_ticket_userinfo->add($createTrainUserData); //添加相应的“产品使用者表”数据
		    	      	  
		    	      }
		    		  foreach ($nameIdArray as $key=>$vo){            //被选中使用者的订单使用者id 数组
		        	      $tu_result = $train_ticket_userinfo->where('ou_id='.$vo)->save($trainUserStatusData);
			          }
			             
		    	}
		      }
		    
		    }
		
			}

	}	
        $this->isInSameStatus($orderId);
	    if($refundResult){
				$this->ajaxReturn(0);  //成功添加申请表
			}else{
				$this->ajaxReturn(1);
			}	
			
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}

