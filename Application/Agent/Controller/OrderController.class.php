<?php
namespace Agent\Controller;
use Think\Controller;
use Agent\Logic\NewDemandLogic;
use Agent\Logic\OrderLogic;

/**
 * 订单管理
 * Enter description here ...
 * @author xiaogan
 *
 */
class OrderController extends Controller {
	/**
	 * 创建订单
	 * 创建者：甘世凤
	 * 2014-12-4上午11:22:21
	 */
	public function qsx_req_list_order(){
		//加载布局文件
		layout("tmc");



		$req_order = D('Agent/Order','Logic');
		$result=$req_order->querylistorder();
		//增加客服信息
		$name = array();
		$phone = array();
		$qq = array();
		for($i=0;$i<count($result,2);$i++){
			$qsx_req = M("qsx_req");
			$qro = $result[$i]['qsx_rq_no'];
			$result1 = $qsx_req->where('qsx_rq_no='."'$qro'")->find();
			$id = $result1['u_id'];
			$employee = M("employee");
			$result2 = $employee->where('u_id='.$id)->find();
			$this->assign ( 'info', $result2 );
			array_push($name,$result2['name']);
			array_push($phone,$result2['phone']);
			array_push($qq,$result2['qq']);
			$this->assign ( 'u_name', $name );
			$this->assign ( 'u_phone', $phone );
			$this->assign ( 'u_qq', $qq );
		}

		$this->assign('sol_order',$result);


		//调用是否显示紧急预定
		$emergencyM=D("Home/Emergency","Logic");
		$booking=$emergencyM->emergency_book();
		$this->assign('datt',$booking);
		$this->theme('agent')->display('qsx_req_list_order');
	}

	/**
	 * 订单管理
	 * 创建者：甘世凤
	 * 2014-12-10下午02:53:06
	 */
	public function order_edit(){
		//加载布局文件
		layout(false);
		$type=$_REQUEST['type'];

		//print_r($type);
		$this->assign('type',$type);
		//$this->theme('agent')->search_employee();
		
		//页面模板名
		$page = "order_edit";
		$logic = new OrderLogic();
		switch ($type) {
		case 1:
			//轻松行方案
			$solu_id=$_POST['solu_id'];
			if (isset($solu_id)) {
				//从方案表找回需求id，再根据需求表中的需求提出人获取需求人！
				$solu_rec = $logic->getOrderSolutionSource($solu_id);
				if ($solu_rec) {
					$this->assign('user', $solu_rec['user']);
					unset($solu_rec['user']);
					$this->assign('solu',$solu_rec);
				}
			}
			break;
		case 2:
			//op进去
			//-- 2014/12/26, by Lanny Lee -->
			//这里增加规定：
			//  需要编辑订单，必须送入订单号
			//<--

			$orderNum = $_REQUEST['order_num'];
			if (empty($orderNum)) {
				echo "必须送入订单号！";
				exit;
			}
			//执行订单数据打包
			$orderData = $logic->buildOrderUIData($orderNum);
			if ($orderData) {
				//如果有数据，将数据转换成json字符串注入
				$this->assign('order_json', json_encode($orderData));
			}

			//废弃 by Lanny Lee, 2014/12/26 -->
			//$orderId=$_GET['orderId'];
			//$orderType=$_REQUEST['orderType'];
			//if($orderType==1){
			//	$mu = M("order_union");
			//	$uorder = $mu->where("id=".$orderId)->find();
			//	if($uorder)
			//		$this->assign('order_json', $this->getUnionOrderJson($orderId));
			//}else
			//$this->assign('order_json', '{"orders":['.$this->getOrderJson($orderId).']}');
			//$this->assign('order_id', $orderId);
			//<--
			break;
		}
		$this->theme('agent')->display($page);
	}

/***** 废弃 by Lanny Lee, 2014/12/26 -->
	public function getUnionOrderJson($uOrderId) {
		$mu = M("order_union");
		$mo = M('order');
		$uorder = $mu->where("id=".$uOrderId)->find();
		$json = '{';
		if ($uorder) {
			$json .=  '
  "recId": '.$uorder['id'].',
  "order_num": '.$uorder['order_num'].',
  "amount": '.$uorder['amount'].',
  "orders": [';

			$cond['uodr_id'] = $uOrderId;
			$orders = $mo->where($cond)->select();
			$idx = 0;
			foreach ($orders as $order) {
				if ($idx > 0) $json .= ',';
				$json .= $this->getOrderJson($order['id']);
			}
				
				
			$json .= ']';
		}
		$json .= '}';
		return $json;
	}




	//订单
	public function getOrderJson($orderId) {
		$order=M('order');
		$order=$order->where('id='.$orderId)->find();
		$json =
'{
  "recId":'.$orderId.',
  "order_num":"'.$order['order_num'].'",
  "amount":'.$order['amount'].',
  "service_price":'.$order['service_price'].',
  "users":[';

		$order_user=M('order_user');
		$users=$order_user->where('o_id='.$orderId)->select();
		if ($users) {
			$idx = 0;
			foreach ($users as $user) {
				if ($idx++ > 0) $json .= ',';
				$json .=
'{
  	"recId": '.$user['id'].',';
				if (!empty($user['emp_id']))
				$json .= '
	"emp_id": '.$user['emp_id'].',';
				$json .= '
  	"name": "'.$user['name'].'",
  	"id_type": "'.$user['id_type'].'",
  	"id_no": "'.$user['id_no'].'",
  	"phone": "'.$user['phone'].'",
}';			
			}
		}
		$json .= '  ],';


		$json .= '  "parts":[';

		$json .= $this->getFlightTicketData($orderId);

		$json .= $this->getHotelData($orderId);

		$json .= $this->getTrainData($orderId);

		$json .= $this->getOtherData($orderId);
			
		$json .= '  ]';
		$json .= '}';


		return $json;
	}
 *///<--

	private function getBoolStr($val) {
		return $val === '1' ? 'true' : 'false';
	}


	private function getFlightOneData($orderId){
		$flight_ticket=M('flight_ticket_info');
		$where['o_id']=$orderId;
		$where['seg_type']=0;
		$res=$flight_ticket->where($where)->select();
		$cnt = 0;
		if($res){
		foreach ($res as $k=>$v){
		if ($cnt++ > 0) $str .= ',';
		$str .='{
	 				"kind":1,
	 				"recId":"'.$v['id'].'",
        			"pnr":"'.$v['pnr'].'",
        			"dep_date":"'.substr($v['time_dep'],0,10).'",
        			"dep_time":"'.substr($v['time_dep'],10,6).'",
        			"arv_time":"'.substr($v['time_arv'],10,6).'",
        			"flight_no":"'.$v['airline_num'].'",
        			"class":"'.$v['class'].'",
        			"city_from":"'.$v['city_from'].'",
        			"city_arv":"'.$v['city_to'].'",
        			"price":"'.$v['price'].'",
        			"service_price":"'.$v['service_price'].'",
        			"baf":"'.$v['baf'].'",
        			"acf":"'.$v['acf'].'",
        			"tax":"'.$v['tax'].'",
        			"can_return":'.$this->getBoolStr($v['refund_enable']).',
        			"can_change":'.$this->getBoolStr($v['reschdule_enable']).',
        			"can_sign":'.$this->getBoolStr($v['resign_enable']).',
        			"tmc_note":"'.$v['tmc_note'].'",
        			"refund_policy":"'.$v['refund_policy'].'",
	 			}';
			if ($v['id'])$this->getAirInsurData($orderId, $v['id']);
		}}
		return $str;
	}
	
	private function getFlightTwoData($orderId){
		$flight_ticket=M('flight_ticket_info');
		$where['o_id']=$orderId;
		$where['seg_type']=array('exp','in (1,2)');
		$res=$flight_ticket->where($where)->order('id')->select();
		if($res){
		$cnt = 0;
			foreach ($res as $air=>$v){
			if ($cnt++ > 0) $str .= ',';
			$datas=array();
				$str .='{
	 				"kind":2,
	 				"recId":"'.$v['id'].'",
        			"pnr2":"'.$v['pnr'].'",
        			"dep_date1":"'.substr($v['time_dep'],0,10).'",
        			"dep_time1":"'.substr($v['time_dep'],10,6).'",
        			"arv_time1":"'.substr($v['time_arv'],10,6).'",
        			"flight_no1":"'.$v['airline_num'].'",
        			"class1":"'.$v['class'].'",
        			"city_from1":"'.$v['city_from'].'",
        			"city_arv1":"'.$v['city_to'].'",
        			"price1":"'.$v['price'].'",
        			"service_price1":"'.$v['service_price'].'",
        			"baf1":"'.$v['baf'].'",
        			"acf1":"'.$v['acf'].'",
        			"tax1":"'.$v['tax'].'",
        			"can_return1":'.$this->getBoolStr($v['refund_enable']).',
        			"can_change1":'.$this->getBoolStr($v['reschdule_enable']).',

        			"dep_date2":"'.substr($v['time_dep'],0,10).'",
        			"dep_time2":"'.substr($v['time_dep'],10,6).'",
        			"arv_time2":"'.substr($v['time_arv'],10,6).'",
        			"flight_no2":"'.$v['airline_num'].'",
        			"class2":"'.$v['class'].'",
        			"city_from2":"'.$v['city_from'].'",
        			"city_arv2":"'.$v['city_to'].'",
        			"price2":"'.$v['price'].'",
        			"service_price2":"'.$v['service_price'].'",
        			"baf2":"'.$v['baf'].'",
        			"acf2":"'.$v['acf'].'",
        			"tax2":"'.$v['tax'].'",
        			"can_return2":'.$this->getBoolStr($v['refund_enable']).',
        			"can_change2":'.$this->getBoolStr($v['reschdule_enable']).',
        			"can_sign2":'.$this->getBoolStr($v['resign_enable']).',
        			
        			"tmc_note2":"'.$v['tmc_note'].'",
        			"refund_policy2":"'.$v['refund_policy'].'",
	 			}';
			}
			$datas=$str;
			
		}
	
	}
	
	private function getFlightThreeData($orderId){
		$flight_ticket=M('flight_ticket_info');
		$where['o_id']=$orderId;
		$where['seg_type']=3;
		$res=$flight_ticket->where($where)->order('id')->select();
		
	
	}
	public function getFlightTicketData($orderId) {
		$flight_ticket=M('flight_ticket_info');
		$res=$flight_ticket->where('o_id='.$orderId)->select();
		
		if($res){
			$cnt = 0;
			foreach ($res as $k=>$v){
				if ($cnt++ > 0) $str .= ',';
				if($v['seg_type']==0){
					$str .='{
	 				"kind":1,
	 				"recId":"'.$v['id'].'",
        			"pnr":"'.$v['pnr'].'",
        			"dep_date":"'.substr($v['time_dep'],0,10).'",
        			"dep_time":"'.substr($v['time_dep'],10,6).'",
        			"arv_time":"'.substr($v['time_arv'],10,6).'",
        			"flight_no":"'.$v['airline_num'].'",
        			"class":"'.$v['class'].'",
        			"city_from":"'.$v['city_from'].'",
        			"city_arv":"'.$v['city_to'].'",
        			"price":"'.$v['price'].'",
        			"service_price":"'.$v['service_price'].'",
        			"baf":"'.$v['baf'].'",
        			"acf":"'.$v['acf'].'",
        			"tax":"'.$v['tax'].'",
        			"can_return":'.$this->getBoolStr($v['refund_enable']).',
        			"can_change":'.$this->getBoolStr($v['reschdule_enable']).',
        			"can_sign":'.$this->getBoolStr($v['resign_enable']).',
        			"tmc_note":"'.$v['tmc_note'].'",
        			"refund_policy":"'.$v['refund_policy'].'",
	 			}';
				}
				else if ($v['seg_type']==1||$v['seg_type']==2){
					$str .='{
	 				"kind":2,
	 				"recId":"'.$v['id'].'",
        			"pnr2":"'.$v['pnr'].'",
        			"dep_date1":"'.substr($v['time_dep'],0,10).'",
        			"dep_time1":"'.substr($v['time_dep'],10,6).'",
        			"arv_time1":"'.substr($v['time_arv'],10,6).'",
        			"flight_no1":"'.$v['airline_num'].'",
        			"class1":"'.$v['class'].'",
        			"city_from1":"'.$v['city_from'].'",
        			"city_arv1":"'.$v['city_to'].'",
        			"price1":"'.$v['price'].'",
        			"service_price1":"'.$v['service_price'].'",
        			"baf1":"'.$v['baf'].'",
        			"acf1":"'.$v['acf'].'",
        			"tax1":"'.$v['tax'].'",
        			"can_return1":'.$this->getBoolStr($v['refund_enable']).',
        			"can_change1":'.$this->getBoolStr($v['reschdule_enable']).',
        			"can_sign1":'.$this->getBoolStr($v['resign_enable']).',
        			
        			"dep_date2":"'.substr($v['time_dep'],0,10).'",
        			"dep_time2":"'.substr($v['time_dep'],10,6).'",
        			"arv_time2":"'.substr($v['time_arv'],10,6).'",
        			"flight_no2":"'.$v['airline_num'].'",
        			"class2":"'.$v['class'].'",
        			"city_from2":"'.$v['city_from'].'",
        			"city_arv2":"'.$v['city_to'].'",
        			"price2":"'.$v['price'].'",
        			"service_price2":"'.$v['service_price'].'",
        			"baf2":"'.$v['baf'].'",
        			"acf2":"'.$v['acf'].'",
        			"tax2":"'.$v['tax'].'",
        			"can_return2":'.$this->getBoolStr($v['refund_enable']).',
        			"can_change2":'.$this->getBoolStr($v['reschdule_enable']).',
        			"can_sign2":'.$this->getBoolStr($v['resign_enable']).',
        			
        			"tmc_note2":"'.$v['tmc_note'].'",
        			"refund_policy2":"'.$v['refund_policy'].'",
	 			}';
				}
	 		else if ($v['seg_type']==3){
	 			$str .='{
	 				"kind":3,
	 				"recId":"'.$v['id'].'",
        			"pnr2":"'.$v['pnr'].'",
        			"dep_date1":"'.substr($v['time_dep'],0,10).'",
        			"dep_time1":"'.substr($v['time_dep'],10,6).'",
        			"arv_time1":"'.substr($v['time_arv'],10,6).'",
        			"flight_no1":"'.$v['airline_num'].'",
        			"class1":"'.$v['class'].'",
        			"city_from1":"'.$v['city_from'].'",
        			"city_arv1":"'.$v['city_to'].'",
        			"price1":"'.$v['price'].'",
        			"service_price1":"'.$v['service_price'].'",
        			"baf1":"'.$v['baf'].'",
        			"acf1":"'.$v['acf'].'",
        			"tax1":"'.$v['tax'].'",
        			"can_return1":'.$this->getBoolStr($v['refund_enable']).',
        			"can_change1":'.$this->getBoolStr($v['reschdule_enable']).',
        			"can_sign1":'.$this->getBoolStr($v['resign_enable']).',
        			
        			"dep_date2":"'.substr($v['time_dep'],0,10).'",
        			"dep_time2":"'.substr($v['time_dep'],10,6).'",
        			"arv_time2":"'.substr($v['time_arv'],10,6).'",
        			"flight_no2":"'.$v['airline_num'].'",
        			"class2":"'.$v['class'].'",
        			"city_from2":"'.$v['city_from'].'",
        			"city_arv2":"'.$v['city_to'].'",
        			"price2":"'.$v['price'].'",
        			"service_price2":"'.$v['service_price'].'",
        			"baf2":"'.$v['baf'].'",
        			"acf2":"'.$v['acf'].'",
        			"tax2":"'.$v['tax'].'",
        			"can_return2":'.$this->getBoolStr($v['refund_enable']).',
        			"can_change2":'.$this->getBoolStr($v['reschdule_enable']).',
        			"can_sign2":'.$this->getBoolStr($v['resign_enable']).',
        			
        			"dep_date3":"'.substr($v['time_dep'],0,10).'",
        			"dep_time3":"'.substr($v['time_dep'],10,6).'",
        			"arv_time3":"'.substr($v['time_arv'],10,6).'",
        			"flight_no3":"'.$v['airline_num'].'",
        			"class3":"'.$v['class'].'",
        			"city_from3":"'.$v['city_from'].'",
        			"city_arv3":"'.$v['city_to'].'",
        			"price3":"'.$v['price'].'",
        			"service_price3":"'.$v['service_price'].'",
        			"baf3":"'.$v['baf'].'",
        			"acf3":"'.$v['acf'].'",
        			"tax3":"'.$v['tax'].'",
        			"can_return3":'.$this->getBoolStr($v['refund_enable']).',
        			"can_change3":'.$this->getBoolStr($v['reschdule_enable']).',
        			"can_sign3":'.$this->getBoolStr($v['resign_enable']).',
        			
        			"tmc_note3":"'.$v['tmc_note'].'",
        			"refund_policy3":"'.$v['refund_policy'].'",
	 			}';
	 		}
			}
		}
		return $str;
	}
	//获取酒店数据
	public  function getHotelData($orderId){
		$hotel_info=M('hotel_info');
		$hotels=$hotel_info->where('o_id='.$orderId)->select();
		if(holtes){
			$cnt = 0;
			foreach ($hotels as $hotel=>$v){
				if ($cnt++ > 0) $str .= ',';
				$str .='{
	 				"kind":5,
	    			"recId":"'.$v['id'].'",
        			"date_ckin":"'.$v['date_ckin'].'",
        			"date_ckout":"'.$v['date_ckout'].'",
        			"hotel_name":"'.$v['hotel_name'].'",
        			"room_type":"'.$v['room_type'].'",
        			"count":"'.$v['count'].'",
        			"hotel_addr":"'.$v['hotel_addr'].'",
        			"hotel_info_url":"'.$v['hotel_info_url'].'",
        			"amount":"'.$v['amount'].'",
        			"crecard_val":"'.$v['crecard_val'].'",
        			"prepay_val":"'.$v['prepay_val'].'",
        			"price":"'.$v['price'].'",
        			"service_price":"'.$v['service_price'].'",
        			"tmc_note":"'.$v['tmc_note'].'",
        			"refund_policy":"'.$v['refund_policy'].'",
	 			}';
			}
		}
		return $str;
	}
	//获取火车票数据
	public  function getTrainData($orderId){
		$train_ticket=M('train_ticket_info');
		$trains=$train_ticket->where('o_id='.$orderId)->select();
		if($trains){
			$cnt = 0;
			foreach ($trains as $train=>$v){
				if ($cnt++ > 0) $str .= ',';
				$str .='{
	 				"kind":6,
	    			"recId":"'.$v['id'].'",
        			"boarding_time":"'.substr($v['boarding_time'],0,10).'",
        			"time":"'.substr($v['boarding_time'],10,6).'",
        			"time_arv":"'.$v['arrive_time'].'",
        			"train_num":"'.$v['train_num'].'",
        			"class_level":"'.$v['class_level'].'",
        			"station_dep":"'.$v['station_dep'].'",
        			"station_arv":"'.$v['station_arv'].'",
        			"price":"'.$v['price'].'",
        			"service_price":"'.$v['service_price'].'",
        			"tmc_note":"'.$v['tmc_note'].'",
        			"refund_policy":"'.$v['refund_policy'].'",
	 			}';
			}
		}
		return $str;
	}

	//获取其他数据
	public  function getOtherData($orderId){
		$other_produ_info=M('other_produ_info');
		$others=$other_produ_info->where('o_id='.$orderId)->select();
		if($others){
				$cnt = 0;
			foreach ($others as $other=>$v){
				if ($cnt++ > 0) $str .= ',';
				$str .='{
	 				"kind":7,
	    			"recId":"'.$v['id'].'",
        			"time_start":"'.$v['time_start'].'",
        			"time_end":"'.$v['time_end'].'",
        			"content":"fdgdfg",
        			"price":"'.$v['price'].'",
        			"service_price":"'.$v['service_price'].'",
        			"tmc_note":"'.$v['tmc_note'].'",
        			"refund_policy":"'.$v['refund_policy'].'",
	 			},';
			}
		}
		return $str;
	}

	public function getAirInsurData($orderId,$a_id){
		$flight_ticket=M('flight_ticket_info');
		
		$str =
	'{
 	 "insures":[';

		$air_insur=M('air_insur_info');
		$where['o_id']=$orderId;
		$where['a_id']=$a_id;
		$insures=$air_insur->where($where)->select();
		if($insures){
			$cnt = 0;
			foreach ($insures as $insure=>$v){
				$airline_num=$flight_ticket->where('a_id='.$v['id'])->getField('airline_num');
				if ($cnt++ > 0) $str .= ',';
				$str .=
		'{
			"recId": "'.$v['id'].'",
			"airline_num": "'.$airline_num.'",
			"insur_co": "'.$v['insur_co'].'",
  			"price": "'.$v['price'].'",
  			"cov_amount": "'.$v['cov_amount'].'",
  			"eff_time": "'.$v['eff_time'].'",
  			"exp_time": "'.$v['exp_time'].'",
  			"tmc_note": "'.$v['tmc_note'].'",
		}'; 	 
		
			}
		$str .= '  ]';
		$str .= '}';
		}
		return $str;	
	}
	

	//订单使用者
	public function setOrderUsersData($id,$users){
		$ouM=M('order_user');//订单使用者
		if($users!=null){
			foreach($users as $key=>$u){
				$oudata['o_id']=$id;
				$oudata['name']=$u['name'];
				$oudata['id_type']=$u['id_type'];
				$oudata['id_no']=$u['id_no'];
				$oudata['phone']=$u['phone'];
				/*if($u[4]!=null){
				 $oudata['emp_id']=$v[4];
				 }*/
				$ouid=$ouM->data($oudata)->add();
			}
		}
	}
	//单程
	public function setFlightOneData($id,$p){
		//print_r($p['dep_date']);
		$m=M('flight_info');//航班信息
		$ftickeM=M('flight_ticket_info');//机票详情
		$f_data['o_id']=$id;
		$f_data['pnr']=$p['pnr'];
		$f_data['time_dep']=$p['dep_date'].' '.$p['dep_time'];
		$f_data['time_arv']=$p['arv_time'];
		$f_data['airline_num']=$p['flight_no'];
		$where['airline_num']=$f_data['airline_num'];
		$f_data['airline_co']='1234';//$m->where($where)->getField('airline_co');
		$f_data['class']=$p['class'];
		$f_data['city_from']=$p['city_from'];
		$f_data['city_to']=$p['city_arv'];
		$f_data['price']=$p['price'];
		$f_data['baf']=$p['baf'];
		$f_data['acf']=$p['acf'];
		$f_data['tax']=$p['tax'];
		$f_data['service_price']=$p['service_price'];
		$f_data['refund_enable']=$p['can_return'];
		$f_data['reschdule_enable']=$p['can_change'];
		$f_data['resign_enable']=$p['can_sign'];
		$f_data['refund_policy']=$p['refund_policy'];
		$f_data['tmc_note']=$p['tmc_note'];
		$f_data['seg_type']=0;
		$f_data['status']=0;

		$fid=$ftickeM->data($f_data)->add();
			
	}

	//两航段机票
	public function setFlightTwoData($id,$p){
			
		$m=M('flight_info');//航班信息
		$ftickeM=M('flight_ticket_info');//机票详情
		
		$f_data1['o_id']=$id;
		$f_data1['pnr']=$p['pnr2'];
		$f_data1['time_dep']=$p['dep_date1'].$p['dep_time1'];
		$f_data1['time_arv']=$p['arv_time1'];
		$f_data1['airline_num']=$p['flight_no1'];
		$where['airline_num']=$f_data1['airline_num'];
		$f_data1['airline_co']='123';//$m->where($where)->getField('airline_co');
		$f_data1['class']=$p['class1'];
		$f_data1['city_from']=$p['city_from1'];
		$f_data1['city_to']=$p['city_arv1'];
		$f_data1['price']=$p['price1'];
		$f_data1['baf']=$p['baf1'];
		$f_data1['acf']=$p['acf1'];
		$f_data1['tax']=$p['tax1'];
		$f_data1['service_price']=$p['service_price1'];
		$f_data1['refund_enable']=$p['can_return1'];
		$f_data1['reschdule_enable']=$p['can_change1'];
		$f_data1['resign_enable']=$p['can_sign1'];
		$f_data1['refund_policy']=$p['refund_policy2'];
		$f_data1['tmc_note']=$p['tmc_note2'];
		$f_data1['seg_type']=0;
		$f_data1['status']=0;

		$fid1=$ftickeM->data($f_data1)->add();

		$f_data2['o_id']=$id;
		$f_data2['pnr']=$p['pnr2'];
		$f_data2['time_dep']=$p['dep_date2'].$p['dep_time2'];
		$f_data2['time_arv']=$p['arv_time2'];
		$f_data2['airline_num']=$p['flight_no2'];
		$where['airline_num']=$f_data2['airline_num'];
		$f_data2['airline_co']= '123';//$m->where($where)->getField('airline_co');
		$f_data2['class']=$p['class2'];
		$f_data2['city_from']=$p['city_from2'];
		$f_data2['city_to']=$p['city_arv2'];
		$f_data2['price']=$p['price2'];
		$f_data2['baf']=$p['baf2'];
		$f_data2['acf']=$p['acf2'];
		$f_data2['tax']=$p['tax2'];
		$f_data2['service_price']=$p['service_price2'];
		$f_data2['refund_enable']=$p['can_return2'];
		$f_data2['reschdule_enable']=$p['can_change2'];
		$f_data2['resign_enable']=$p['can_sign2'];
		$f_data2['refund_policy']=$p['refund_policy2'];
		$f_data2['tmc_note']=$p['tmc_note2'];
		$f_data2['seg_type']=0;
		$f_data2['status']=0;

		$fid2=$ftickeM->data($f_data2)->add();
	}

	//三航段机票
	public function setFlightThreeData($id,$p){
		$m=M('flight_info');//航班信息
		$ftickeM=M('flight_ticket_info');//机票详情

		$f_data3_1['o_id']=$id;
		$f_data3_1['pnr']=$p['pnr3'];
		$f_data3_1['time_dep']=$p['dep_date1'].$p['dep_time1'];
		$f_data3_1['time_arv']=$p['arv_time1'];
		$f_data3_1['airline_num']=$p['flight_no1'];
		$where['airline_num']=$f_data3_1['airline_num'];
		$f_data3_1['airline_co']='123';//$m->where($where)->getField('airline_co');
		$f_data3_1['class']=$p['class1'];
		$f_data3_1['city_from']=$p['city_from1'];
		$f_data3_1['city_to']=$p['city_arv1'];
		$f_data3_1['price']=$p['price1'];
		$f_data3_1['baf']=$p['baf1'];
		$f_data3_1['acf']=$p['acf1'];
		$f_data3_1['tax']=$p['tax1'];
		$f_data3_1['service_price']=$p['service_price1'];
		$f_data3_1['refund_enable']=$p['can_return1'];
		$f_data3_1['reschdule_enable']=$p['can_change1'];
		$f_data3_1['resign_enable']=$p['can_sign1'];
		$f_data3_1['refund_policy']=$p['refund_policy3'];
		$f_data3_1['tmc_note']=$p['tmc_note3'];
		$f_data3_1['seg_type']=0;
		$f_data3_1['status']=0;

		$fid=$ftickeM->data($f_data3_1)->add();

		$f_data3_2['o_id']=$id;
		$f_data3_2['pnr']=$p['pnr3'];
		$f_data3_2['time_dep']=$p['dep_date2'].$p['dep_time2'];
		$f_data3_2['time_arv']=$p['arv_time2'];
		$f_data3_2['airline_num']=$p['flight_no2'];
		$where['airline_num']=$f_data3_2['airline_num'];
		$f_data3_2['airline_co']='123';//$m->where($where)->getField('airline_co');
		$f_data3_2['class']=$p['class2'];
		$f_data3_2['city_from']=$p['city_from2'];
		$f_data3_2['city_to']=$p['city_arv2'];
		$f_data3_2['price']=$p['price2'];
		$f_data3_2['baf']=$p['baf2'];
		$f_data3_2['acf']=$p['acf2'];
		$f_data3_2['tax']=$p['tax2'];
		$f_data3_2['service_price']=$p['service_price2'];
		$f_data3_2['refund_enable']=$p['can_return2'];
		$f_data3_2['reschdule_enable']=$p['can_change2'];
		$f_data3_2['resign_enable']=$p['can_sign2'];
		$f_data3_2['refund_policy']=$p['refund_policy3'];
		$f_data3_2['tmc_note']=$p['tmc_note3'];
		$f_data3_2['seg_type']=0;
		$f_data3_2['status']=0;

		$fid=$ftickeM->data($f_data3_2)->add();

		$f_data3_3['o_id']=$id;
		$f_data3_3['pnr']=$p['pnr3'];
		$f_data3_3['time_dep']=$p['dep_date3'].$p['dep_time3'];
		$f_data3_3['time_arv']=$p['arv_time3'];
		$f_data3_3['airline_num']=$p['flight_no3'];
		$where['airline_num']=$f_data3_3['airline_num'];
		$f_data3_3['airline_co']='123';//$m->where($where)->getField('airline_co');
		$f_data3_3['class']=$p['class3'];
		$f_data3_3['city_from']=$p['city_from3'];
		$f_data3_3['city_to']=$p['city_arv3'];
		$f_data3_3['price']=$p['price3'];
		$f_data3_3['baf']=$p['baf3'];
		$f_data3_3['acf']=$p['acf3'];
		$f_data3_3['tax']=$p['tax3'];
		$f_data3_3['service_price']=$p['service_price3'];
		$f_data3_3['refund_enable']=$p['can_return3'];
		$f_data3_3['reschdule_enable']=$p['can_change3'];
		$f_data3_3['resign_enable']=$p['can_sign3'];
		$f_data3_3['refund_policy']=$p['refund_policy3'];
		$f_data3_3['tmc_note']=$p['tmc_note3'];
		$f_data3_3['seg_type']=0;
		$f_data3_3['status']=0;

		$fid=$ftickeM->data($f_data3_3)->add();

	}
	//酒店
	public function setHotelData($id,$p){
		$hotelM=M('hotel_info');

		$h_data['o_id']=$id;
		$h_data['date_ckin']=$p['date_ckin'];
		$h_data['hotel_name']=$p['hotel_name'];
		$h_data['room_type']=$p['room_type'];
		$h_data['count']=$p['count'];
		$h_data['price']=$p['price'];
		$h_data['service_price']=$p['service_price'];
		$h_data['status']=0;

		$h_data['date_ckout']=$p['date_ckout'];
		$h_data['refund_policy']=$p['refund_policy'];
		$h_data['prepay_val']=$p['prepay_val'];
		$h_data['crecard_val']=$p['crecard_val'];
		$h_data['hotel_addr']=$p['hotel_addr'];
		$h_data['hotel_info_url']=$p['hotel_info_url'];
		$h_data['tmc_note']=$p['tmc_note'];

		$hid=$hotelM->data($h_data)->add();

	}
	//火车票
	public function setTrainData($id,$p){
		$trainM=M('train_ticket_info');

		$t_data = $trainM->create($p, 3);
		
		$t_data['o_id']=$id;
		//$t_data['boarding_time']=$p['boarding_time'].$p['time'];
		//$t_data['arrive_time']=$p['time_arv'];
		//$t_data['train_num']=$p['train_num'];
		//$t_data['class_level']=$p['class_level'];
		//$t_data['station_dep']=$p['station_dep'];
		//$t_data['station_arv']=$p['station_arv'];
		//$t_data['price']=$p['price'];
		$t_data['status']=0;
		//$t_data['service_price']=$p['service_price'];
		//$t_data['refund_policy']=$p['refund_policy'];
		//$t_data['tmc_note']=$p['tmc_note'];

		$tid=$trainM->data($t_data)->add();

	}

	//其他
	public function setOtherData($id,$p){
		$otherM=M('other_produ_info');//其他

		$o_data['o_id']=$id;
		$o_data['time_start']=$p['time_start'];
		$o_data['time_end']=$p['time_end'];
		$o_data['content']=$p['content'];
		$o_data['price']=$p['price'];
		$o_data['service_price']=$p['service_price'];
		$o_data['refund_policy']=$p['refund_policy'];
		$o_data['tmc_note']=$p['tmc_note'];
		$o_data['status']=0;
		$oid=$otherM->data($o_data)->add();
	}

	public function setAirInsurData($id,$insurs){
		$ftickeM=M('flight_ticket_info');//机票详情
		$airinsurM=M('air_insur_info');//保险
		if($insurs!=null){
			foreach ($insurs as $insur=>$i){
				$where['airline_num']=$i['airline_num'];
				$air_id=$ftickeM->where($where)->getField('id');
				$fi_data['a_id']=$air_id;
				$fi_data['o_id']=$id;
				$fi_data['insur_code']="111";
				$fi_data['insur_co']=$i['insur_co'];
				$fi_data['price']=$i['price'];
				$fi_data['cov_amount']=$i['cov_amount'];
				$fi_data['status']=6;
				$fi_data['time']=date('Y-m-d H:i:s',time());
				$fi_data['eff_time']=$i['eff_time'];
				$fi_data['exp_time']=$i['exp_time'];
				$fi_data['tmc_note']=$i['tmc_note'];
				$aid=$airinsurM->data($fi_data)->add();
			}
		}
	}
	
	private function delInsur($orderId,$a_id){
		$airinsurM=M('air_insur_info');//保险
		$where['o_id']=$orderId;
		$where['a_id']=$a_id;
		$airinsurM->where($where)->delete(); 
		
	}
	private function delFlight($orderId){
		$ftickeM=M('flight_ticket_info');//机票详情
		$where['o_id']=$orderId;
		$res=$ftickeM->where($where)->select(); 
		if($res){
			foreach ($res as $k=>$v){
				if($this->delInsur($orderId, $v['id'])) $ftickeM->where($where)->delete();
			}
		}
		
	}
	
	private function delTrain($orderId){
		$trainM=M('train_ticket_info');
		$where['o_id']=$orderId;
		$trainM->where($where)->delete(); 
	}
	private function delHotel($orderId){
		$hotelM=M('hotel_info');
		$where['o_id']=$orderId;
		$hotelM->where($where)->delete(); 
	}
	
	private function delOther($orderId){
		$otherM=M('other_produ_info');//其他
		$where['o_id']=$orderId;
		$otherM->where($where)->delete(); 
	}
	
	private function delOrderUser($orderId){
		$ouserM=M('order_user');//其他
		$where['o_id']=$orderId;
		$ouserM->where($where)->delete(); 
	}
	
	
	public function addNewOrder() {
		//request中的data是js对象转换成的Array数据。
		$orderData = $_REQUEST;
		$logic = D('Agent/Order', 'Logic');
		$result = $logic->saveOrder($orderData);
		if ($result && $result['error'] == 0) $this->ajaxReturn(1);
		else $this->ajaxReturn(0);
	}

	//添加新订单
	public function addNewOrder_1(){
		//登录的tmc用户
		$tmc_uid=LI('tmcempId');
		$M=M('tmc_employee');
		$tmc_id=$M->where("id=".$tmc_uid)->getField('tmc_id');
		$tmcM=M('tmc');
		$res_name=$tmcM->where("id=".$tmc_id)->find();
		$tmc_name=$res_name['name'];
		
		$order_unionM=M('order_union');//联合订单
		$orderM=M('order');//订单
		
		//订单来源识别type
		$type=$_POST['type'];
		if($type==1){//轻松行需求方案
			$src=1;
			$src_id=$_POST['solu_id'];
		}else if($type==2){//OP添加
			$src=2;
			$src_id=$tmc_uid;
			$orderId=$_POST['order_id'];
			
			$this->delOrderUser($orderId);
			$this->delFlight($orderId);
			$this->delHotel($orderId);
			$this->delOther($orderId);
			$this->delTrain($orderId);
		}else{//自助预订
			$src=0;
			$src_id='';
		}

		$status=0;//订单状态
		
		//页面上采用js对象提交，已不需要json_decode
		//$orderList=$_POST['data'];
		$orders = $_POST['data'];
		//print_r($orderList);
		//将JSON转换对象数组
		//$orders=json_decode($orderList,true);
		print_r($orders);

		if(count($orders['orders'])>1){
			//生成联合订单号
			$union_num=VNumGen('order');
			$union['order_num']=$union_num;
			$union['amount']=11;
			$un_id=$order_unionM->data($union)->add();
		}
		//第二层
		$tr_num=$orders['tr_num'];
		foreach ($orders['orders'] as $k=>$v){
			//如果订单有多个生成联合订单
			//print_r($v);
			//单个订单
			$data['order_num']=$v['order_num'];
			$data['uodr_id']=$un_id;
			$data['src_id']=$src_id;
			$data['src']=$src;
			$data['tr_num']=$tr_num;
			$data['time']=date('Y-m-d H:i:s',time());
			$data['amount']=$v['amount'];
			$data['service_price']=$v['service_price'];
			$data['pay_type']=1;
			$data['tmc_id']=$tmc_id;
			$data['tmc_uid']=$tmc_uid;
			//$data['tmc_name']=$tmc_name;
			$data['status']=$status;
			//print_r($v);
			
			if($type==2){
				$id=$orderM->where('id='.$orderId)->save($data); // 根据条件更新记录
			}else{
				$id=$orderM->data($data)->add();
			}
			//print_r($v);
			//如果有订单
			if($id!=0){
				//订单使用者
				$users=$v['users'];
				if($users){
					$this->setOrderUsersData($id, $users);
				}
				$parts=$v['parts'];
				if($parts){
					foreach($parts as $key=>$p){
						
					//	print_r($key);
						$kind=$p['kind'];
						//单程机票
						if($kind=='1'){
							$this->setFlightOneData($id, $p);
						}
						//两航段机票
						if($kind=='2'){
							$this->setFlightTwoData($id, $p);
						}
						//三航段机票
						if($kind=='3'){
							$this->setFlightThreeData($id, $p);
						}
						if($kind=='1'||$kind=='2'||$kind=='3'){
							if($p['insures']!=null){
								$this->setAirInsurData($id,$p['insures']);
							}
						}
						//酒店
						if($kind=='5'){
							$this->setHotelData($id, $p);
						}
						//火车票
						if($kind=='6'){
							$this->setTrainData($id, $p);
						}
						//其他
						if($kind=='7'){
							$this->setOtherData($id, $p);
						}
					}

				}
				
				if($users!=null){
					if($parts!=null){
						$this->ajaxReturn(1);
					}else{
						$this->ajaxReturn(0);
					}
				}else{
					$this->ajaxReturn(0);
				}
				
			}else{
				$this->ajaxReturn(0);
			}
		}
	}



	////////////////////////////////
	/**
	 * 查询旅客
	 * 创建者：甘世凤
	 * 2014-12-16下午06:50:47
	 */
	public function search_employee(){
		$tmc_emp_id=LI('tmcempId');

		$m_dict = M('dictionary');
		$cond['d_group'] = 'id_type';
		$idTypes = $m_dict->where($cond)->select();
		$idTypePairs = array();
		if ($idTypes) {
			foreach ($idTypes as $rec) {
				$idTypePairs[$rec['d_key']] = $rec['d_value'];
			}
		}

		$M=M('tmc_employee');
		$tmc_id=$M->where("id=".$tmc_emp_id)->getField('tmc_id');
		$co_tmc=M('co_tmc_link');
		$co_id=$co_tmc->where("tmc_id=".$tmc_id)->getField('co_id');
		$emp=M('employee');
		$result=$emp->where("co_id=".$co_id)->select();
		foreach ($result as $k=>$v){
			$sql = "select name from 73go_branch  WHERE  id = ".$v['br_id'];
			$names = $emp->query($sql);
			$result[$k]['br_name'] = $names[0]['name'];
		}

		foreach ($result as &$emp) {
			$emp['id_name'] = $idTypePairs[$emp['id_type']];
		}
		$this->assign('emps',$result);
	}

	///////////////////////////////

	//出发地点
	public function address_dep(){ //一级分类联动二级分类
		$airline_num=$_POST['data'];  //接收模板文件jquery $(load)传来参数。data
		$m=M('flight_info');
		$where['airline_num']=$airline_num;
		$query=$m->where($where)->find();   //在二级分类表classb里找出字段class_id=$class_id
		if($query){
			$temp="<option value=''>请选择地点</option>";
		}else{
			$temp="<option value=''>输入出发地点</option>";
		}
		$temp.="<option rel='".$query['time_dep']."' value='".$query['city_dep']."'>".$query['city_dep']."</option>"
		."<option rel='".$query['time_dep2']."' value='".$query['city_arv']."'>".$query['city_arv']."</option>"
		."<option rel='".$query['time_dep3']."' value='".$query['city_arv2']."'>".$query['city_arv2']."</option>";
		echo $temp;
	}
	 
	//到达地点
	public function address_arv(){ //一级分类联动二级分类
		$airline_num=$_POST['data'];  //接收模板文件jquery $(load)传来参数。data
		$m=M('flight_info');
		$where['airline_num']=$airline_num;
		$query=$m->where($where)->find();   //在二级分类表classb里找出字段class_id=$class_id
		if($query){
			$temp="<option value=''>请选择地点</option>";
		}else{
			$temp="<option value=''>输入到达地点</option>";
		}
		$temp.="<option rel='".$query['time_arv']."' value='".$query['city_arv']."'>".$query['city_arv']."</option>"
		."<option rel='".$query['time_arv2']."' value='".$query['city_arv2']."'>".$query['city_arv2']."</option>"
		."<option rel='".$query['time_arv3']."' value='".$query['city_arv3']."'>".$query['city_arv3']."</option>";
		echo $temp;
	}
	//////////////////////
}