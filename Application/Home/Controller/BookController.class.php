<?php

namespace Home\Controller;

use System\LoginInfo;
use Think\Controller;

class BookController extends Controller {
	
	// 加载基本政策页面
	public function index() {
		// C('LAYOUT_ON',TRUE);
		// 加载布局文件
		layout ( "home" );
		 //$flightD = D ( 'Home/Flight', 'Logic' );
		// $result = $flightD->getFlightInfos ( "SZX", "CSX", "2015-03-15");
		 //$result = $flightD->getOtherCabinPrice("SZX","CSX","16:10","17:20","CZ6890", "2015-03-15", "J4 C1 D1 WA S4 YA BA MA HA KA UA LA QA NA");
		//print_r($result);
		$this->theme ( 'default' )->display ( "index" );
	}
	
	// 加载机票查询结果
	public function book_self() {
		C ( 'LAYOUT_ON', false );
		$startdate = $_POST ['startdate'];
		$startcity = $_POST ['fromCity'];
		$tocity = $_POST ['toCity'];
		$partTime = $_POST ['partTime'];
		/*
		 * $flightD = D ( 'Home/Flight', 'Logic' );
		 * $result = $flightD->getFlightInfo ( $startcity, $tocity, $startdate, $partTime );
		 *
		 * $airport_city= M ( 'airport_city' );
		 * $startcity_find = $airport_city->where('city_code='."'$startcity'")->find();
		 * $tocity_find = $airport_city->where('city_code='."'$tocity'")->find();
		 */
		$this->assign ( 'startdate', $startdate );
		$this->assign ( 'fromcity', $startcity );
		$this->assign ( 'tocity', $tocity );
		$this->assign ( 'partTime', $partTime );
		$this->theme ( 'default' )->display ( 'book_flight_step2' );
	}
	// 机票订单填写
	public function book_flight_order() {
		C ( 'LAYOUT_ON', FALSE );
		$co_id = LI ( 'comId' );
		$u_id = LI ( 'userId' );
		//$tmc_id = 1;//暂时无人抢单，因此此时暂时未对TMC_ID进行选择，后续需要根据前端页面传入的TMC_ID进行判断月结还是现付
		// $tmc_id = $_POST['tmc_id'];
		
		// 是否紧急预订
		$travel_policy = M ( 'travel_policy' );
		$result = $travel_policy->where ( 'co_id=' . $co_id )->find ();
		$emergency_booking = $result ['emergency_booking'];
		// 是否不需要审批的公司
		$without_app = $result ['without_app'];
		
		// 支付方式
		$co_tmc_link = M ( 'co_tmc_link' );
		$result = $co_tmc_link->field('pay_type')->where ( "co_id=%d and status=0",$co_id )->find ();
		$pay_type = $result ['pay_type'];
		
		// 保险信息
		$air_insur_product = M ( 'air_insur_product' );
		$insures = $air_insur_product->field ( 'id,price,cov_amount' )->where ( 'tmc_id=' . $tmc_id . ' and status=0' )->select ();
		
		// ################航班信息
		$tmc_id = $_POST ['tmc_id']; // 航空公司
		$airline_co = $_POST ['FlightName']; // 航空公司
		$airline_num = $_POST ['FlightNo']; // 航班号
		$seg_type = 0; // 0 单程，1 往返联程，2 中转联程，3 三段联程，4 四段联程
		$city_from = $_POST ['FromCity']; // 起飞城市
		$city_to = $_POST ['ToCity']; // 到达城市
		$time_dep = $_POST ['dt']; // 起飞时间
		$time_arv = $_POST ['at']; // 到达时间
		$class = $_POST ['cabin']; // 仓位
		$price = $_POST ['price']; // 票价
		$baf = $_POST ['fuelCosts']; // 燃油附加费
		$acf = $_POST ['airraxFloat']; // 机场建设费
		$service_price = $_POST ['serviceCost']; // 服务费
		$content = $_POST ['content']; // 退改签政策
		
		$this->assign ( 'tmc_id', $tmc_id );
		$this->assign ( 'FlightName', $airline_co );
		$this->assign ( 'FlightNo', $airline_num );
		$this->assign ( 'FromAirport', $city_from );
		$this->assign ( 'ToAirport', $city_to );
		$this->assign ( 'dt', $time_dep );
		$this->assign ( 'at', $time_arv );
		$this->assign ( 'cabin', $class );
		$this->assign ( 'price', $price );
		$this->assign ( 'fuelCosts', $baf );
		$this->assign ( 'airraxFloat', $acf );
		$this->assign ( 'serviceCost', $service_price );
		$this->assign ( 'content', $content );
		
		$this->assign ( 'emergency_booking', $emergency_booking );
		$this->assign ( 'without_app', $without_app );
		$this->assign ( 'pay_type', $pay_type );
		$this->assign ( 'insures', $insures );
		
		$this->theme ( 'default' )->display ( "book_flight_order" );
	}
	public function show_book() {
		C ( 'LAYOUT_ON', false );
		
		$flyData = $_POST ['flyData'];
		$fromCity = $_POST ['fromCity'];
		$toCity = $_POST ['toCity'];
		$partTime = $_POST ['partTime'];
		
		$cabin = $_POST ['cabin'];
		
		$flightD = D ( 'Home/Flight', 'Logic' );
		$result = $flightD->getFlightInfos ( $fromCity, $toCity, $flyData, $partTime, $cabin );
		
		$this->ajaxReturn ( $result, "JSON" );
	}
	public function show_canbins() {
		C ( 'LAYOUT_ON', false );
		
		$startcity = $_POST ['startcity'];
		$tocity = $_POST ['tocity'];
		$dt = $_POST ['dt'];
		$at = $_POST ['at'];
		$flightNum = $_POST ['FlightNo'];
		$depDate = $_POST ['depdate'];
		$cabins = $_POST ['OtherCabin'];
		
		$flightD = D ( 'Home/Flight', 'Logic' );
		$result = $flightD->getOtherCabinPrice ( $startcity, $tocity, $dt, $at, $flightNum, $depDate, $cabins );
		$this->ajaxReturn ( $result, "JSON" );
	}

	/**
	 * 修改订单信息不能显示的问题
	 * 修改者：王月
	 * 创建时间：2015-3-24
	 */
	public function submit_order() {
		$co_id = LI ( 'comId' );
		$u_id = LI ( 'userId' );
		$time = date ( "Y-m-d H:i:s" );
		
		// ################订单信息
		$tr_num = $_POST ['tr_num']; // 出差申请号
		$is_emergency = $_POST ['is_emergency']; // 是否为紧急预定
		$note_name = $_POST ['note_name']; // 知会人姓名
		$note_phone = $_POST ['note_phone']; // 知会人手机
		$note_email = $_POST ['note_email']; // 知会人邮箱
		$pay_type = $_POST ['pay_type']; // 支付方式
		$is_insure = $_POST ['is_insure']; // 是否购买航空意外险
		$p_id = $_POST ['p_id']; // 航空意外险
		$emp_id = explode ( ',', $_POST ['emp_id'] ); // 乘机人
		$emp_id = array_unique ( $emp_id );
		$dispatch = $_POST ['dispatch']; // 配送方式
		$post_name = $_POST ['post_name']; // 收件人
		$post_province = $_POST ['post_province']; // 省份
		$post_city = $_POST ['post_city']; // 城市
		$post_address = $_POST ['post_address']; // 详细地址
		$post_phone = $_POST ['post_phone']; // 手机
		$post_postcode = $_POST ['post_postcode']; // 邮编
		                                           
		// ################航班信息
		$tmc_id = $_POST ['tmc_id'];
		$airline_co = $_POST ['FlightName']; // 航空公司
		$airline_num = $_POST ['FlightNo']; // 航班号
		$seg_type = 0; // 0 单程，1 往返联程，2 中转联程，3 三段联程，4 四段联程
		$city_from = $_POST ['FromAirport']; // 起飞城市
		$city_to = $_POST ['ToAirport']; // 到达城市
		$time_dep = $_POST ['dt']; // 起飞时间
		$time_arv = $_POST ['at']; // 到达时间
		$class = $_POST ['cabin']; // 仓位
		$price = $_POST ['price']; // 票价
		$baf = $_POST ['fuelCosts']; // 燃油附加费
		$acf = $_POST ['airraxFloat']; // 机场建设费
		$service_price = $_POST ['serviceCost']; // 服务费
		$content = $_POST ['content']; // 退改签政策
		
		$emp_count = count ( $emp_id );
		$amount = ($price + $baf + $acf) * $emp_count; // 订单金额
		$service_price_s = $service_price * $emp_count; // 服务费总额
		
		$user_id1 = LI ( 'userId' );
		$employeeTable = M ( 'employee' );
		$emp_id1 = $employeeTable->where ( 'u_id = ' . $user_id1 )->getField ( 'id' ); // $emp_id1 为employee表中的id
		                                                                       // ################添加订单
		$order_data ['order_num'] = VNumGen ( 'order' ); // 订单号
		$order_data ['src'] = "自助预订"; // 自助预订
		$order_data ['tr_num'] = $tr_num; // 出差申请号
		$order_data ['time'] = $time; // 出差申请号
		$order_data ['co_id'] = $co_id;
		$order_data ['u_id'] = $emp_id1;
		$order_data ['tmc_id'] = $tmc_id;
		$order_data ['amount'] = $amount; // 订单金额
		$order_data ['service_price'] = $service_price_s; // 服务费总额
		$order_data ['pay_type'] = $pay_type; // 支付方式
		if ($pay_type == 1) {
			$status = 6;
		} else {
			$status = 11;
		}
		$order_data ['status'] = $status; // 订单状态（待支付）
		/**
		 * 增加配送方式
		 * 修改者：王月
		 * 2015-3-19
		 */
		$order_data ['dispatch'] = $dispatch; // 配送方式
		$order = M ( 'order' );
		
		$o_id = $order->add ( $order_data ); // 添加订单并返回订单ID
		                                     
		// ################添加机票订单详情
		$flight_ticket_info_data ['o_id'] = $o_id;
		$flight_ticket_info_data ['airline_co'] = $airline_co; // 航空公司
		$flight_ticket_info_data ['airline_num'] = $airline_num; // 航班号
		$flight_ticket_info_data ['seg_type'] = $seg_type; // 0 单程，1 往返联程，2 中转联程，3 三段联程，4 四段联程
		$flight_ticket_info_data ['city_from'] = $city_from; // 起飞城市
		$flight_ticket_info_data ['city_to'] = $city_to; // 到达城市
		$my_t=getdate(date("U"));
		$year = $my_t[year];
		$month = $my_t[mon];
		$day = $my_t[mday];
		$flight_ticket_info_data ['time_dep'] = $year.'-'.$month.'-'.$day.' '.$time_dep.':00'; // 起飞时间
		$flight_ticket_info_data ['time_arv'] = $year.'-'.$month.'-'.$day.' '.$time_arv.':00'; // 到达时间

		$flight_ticket_info_data ['class'] = $class; // 仓位
		$flight_ticket_info_data ['price'] = $price; // 票价
		$flight_ticket_info_data ['baf'] = $baf; // 燃油附加费
		$flight_ticket_info_data ['acf'] = $acf; // 机场建设费
		$flight_ticket_info_data ['service_price'] = $service_price; // 服务费
		$flight_ticket_info_data ['refund_enable'] = true; // 是否可退票
		$flight_ticket_info_data ['reschdule_enable'] = true; // 是否可改期
		$flight_ticket_info_data ['resign_enable'] = true; // 是否可签转
		$flight_ticket_info_data ['refund_policy'] = $content; // 退改签政策
		$flight_ticket_info = M ( 'flight_ticket_info' );
		
		$a_id = $flight_ticket_info->add ( $flight_ticket_info_data ); // 添加机票订单详情并返回订单详情ID
		
		$employee = M ( 'employee' );
		$order_user = M ( 'order_user' );
		
		if ($is_insure == 1) {
			
			$where ['id'] = $p_id;
			$air_insur_product = M ( 'air_insur_product' );
			$air_insur_product_data = $air_insur_product->where ( $where )->find ();
			$air_insur_info = M ( 'air_insur_info' );
		}
		foreach ( $emp_id as $value ) {
			$where ['id'] = $value;
			$employee_data = $employee->where ( $where )->find ();
			// ################添加订单使用者
			$order_user_data ['o_id'] = $o_id;
			$order_user_data ['emp_id'] = $value;
			$order_user_data ['name'] = $employee_data ['name'];
			$order_user_data ['id_type'] = $employee_data ['id_type'];
			$order_user_data ['id_no'] = $employee_data ['id_num'];
			$order_user_data ['phone'] = $employee_data ['phone'];
			$order_user_data ['status'] = 11; // 订单状态（待支付）
			$ou_id = $order_user->add ( $order_user_data ); // 添加订单使用者并返回订单使用者ID
			                                                
			// ################添加航空保险详情
			if ($is_insure == 2) {
				continue;
			}
			$air_insur_info_data ['a_id'] = $a_id;
			$air_insur_info_data ['o_id'] = $o_id;
			$air_insur_info_data ['ou_id'] = $ou_id;
			$air_insur_info_data ['time'] = $time;
			$air_insur_info_data ['eff_time'] = $time_dep;
			$air_insur_info_data ['exp_time'] = $time_arv;
			$air_insur_info_data ['insur_co'] = $air_insur_product_data ['insur_co'];
			$air_insur_info_data ['price'] = $air_insur_product_data ['price'];
			$air_insur_info_data ['cov_amount'] = $air_insur_product_data ['cov_amount'];
			$air_insur_info_data ['status'] = 0;
			$air_insur_info->add ( $air_insur_info_data );
		}
		
		// ################添加配送管理信息
		if ($dispatch == 2) {
			$dispatch_mgnt_data ['o_id'] = $o_id;
			$dispatch_mgnt_data ['time'] = $time;
			$dispatch_mgnt_data ['name'] = $post_name;
			$dispatch_mgnt_data ['phone'] = $post_phone;
			$dispatch_mgnt_data ['province'] = $post_province;
			$dispatch_mgnt_data ['city'] = $post_city;
			$dispatch_mgnt_data ['address'] = $post_address;
			$dispatch_mgnt_data ['postcode'] = $post_postcode;
			$dispatch_mgnt_data ['status'] = 0;
			$dispatch_mgnt = M ( 'dispatch_mgnt' );
			$dispatch_mgnt->add ( $dispatch_mgnt_data );
		}
		
		// ###############订单生成后进行短信的发送
		// 提交人的邮箱($data['emp_email'])，提交人的电话($data['emp_phone']),
		// 1->提交人的姓名($data['emp_name'])
		// 2->航班出发的时间($data['begin_time'])，3->航班的内容($data['flight_content']);
		$empId=Li("empId");
		$co_employee=$employee->where("id=".$empId)->find();
		$wx_openid=Li("userOpenid");//获取登录用户的wx_openid

		$send = D ( "Home/SendMessage", "Logic" );
		$case = "BookSelf";
		$datt ['emp_email'] = $co_employee['email'];
		$datt ['emp_phone'] =$co_employee['phone'];
		$datt ['emp_name'] = $co_employee['name'];
		$datt ['begin_time'] = $time_dep;
		$datt['wx_openid']=$wx_openid;
		$datt ['flight_content'] = $airline_num;
		
		$send->SendDetails ( $case, $datt );
		
		// $this->ajaxReturn(dump($ss),json);
		$this->ajaxReturn ( 1 );
	}
}