<?php
namespace Home\Logic;
use Think\Model;

class OrderLogic extends Model {

	/**
	 * 
	 * 注意：order表内的u_id字段存的实际上是员工（emp）表的id
	 * 73go_order --------------------------o 
	 * 73go_tmc -----------------------------tmc
	 * 73go_order_user ----------------------ou
	 * 73go_flight_ticket_info --------------flt
	 * 73go_flight_userinfo -----------------fltu
	 * 73go_air_insur_info ------------------oai
	 * 73go_hotel_info ----------------------ht
	 * 73go_hotel_userinfo ------------------htu
	 * 73go_train_ticket_info ---------------trt
	 * 73go_train_ticket_userinfo -----------trtu
	 * 73go_other_produ_info ----------------oth
	 * 73go_other_userinfo ------------------othu
	 * 73go_air_insur_info ------------------insur
	 * 
	 */
	
	
	/**
	 * Checkout all the orders, including information, according to userId
	 * 
	 * Programmer: Yu Zhuoran
	 */
	/**
	 * 修改订单仓位信息
	 * 修改人：王月
	 * 2015-3-24
	 */
	public function queryOrder($id,$Page,$data) {
	if($Page == 0){
			$link = "";
		}else{
			$link = " limit $Page->firstRow , $Page->listRows";
		}
		
		$start_date = $data ['start_date'];
		$end_date = $data ['end_date'];
		$keywords = $data ['keywords'];
		
		if ($start_date!='') {
			$start_date = $start_date . " 00:00:00";
			$where = " and unix_timestamp(o.time) >= unix_timestamp('".$start_date."')";
		}
		if ($end_date!='') {
			$end_date = $end_date . " 23:59:59";
			$where .=  " and unix_timestamp(o.time) <= unix_timestamp('".$end_date."')";
		}
		if ($keywords!='') {
			$where .= " and (o.order_num  like '%$keywords%' or tmc.name  like '%$keywords%')";
		}
		
		$employee =  M('employee');
		$empId = $employee->where('u_id = '.$id)->getField('id');
		$m = M('');
        $sql="
        SELECT 
o.id orderId,
o.uodr_id uodrId,
o.order_num orderNum,
o.src_id srcId,
o.src src,
o.time orderTime,
o.co_id comId,
o.u_id userId,
o.amount amount,
o.service_price servPrice,
o.pay_type payType,
o.tmc_id tmcId,
o.tmc_uid tmcEmId,
o.tmc_uname tmcEmName,
o.tmc_note tmcNote,
o.`status` orderStatus,
tmc.name tmcName

FROM 73go_order AS o 
LEFT JOIN 73go_tmc AS tmc ON tmc.id = o.tmc_id

WHERE o.u_id = ".$empId." AND o.uodr_Id is NULL AND o.`status`!= 12  ".$where." order by o.id desc".$link;
        
       $request = $m->query($sql);
       foreach($request as $key=>$vo){
       		$uname = '';
       		$sql1 = "select * from 73go_order_user where o_id = ".$vo['orderId']; 
       		$sql2 = "select flt.*,(flt.price+flt.baf+flt.acf+flt.tax) allPrice,insur.id insurId from 73go_flight_ticket_info AS flt LEFT JOIN 73go_air_insur_info AS insur ON insur.a_id = flt.id where flt.o_id = ".$vo['orderId'];
       		$sql3 = "select * from 73go_hotel_info where o_id = ".$vo['orderId'];
       		$sql4 = "select * from 73go_train_ticket_info where o_id = ".$vo['orderId'];
       		$sql5 = "select * from 73go_other_produ_info where o_id = ".$vo['orderId'];
       		$user = $m->query($sql1);
       		$flight = $m->query($sql2);
       		$hotel = $m->query($sql3);
       		$train = $m->query($sql4);
       		$other = $m->query($sql5);
       		foreach ($user as $key1=>$vo1){
       				$uname.= $vo1['name']."<br/>";	
       			
       		}
       		$request[$key]['uname'] = $uname;
		    //仓位信息
		    $class = $flight[0]['class'];
		    if($class==' F'||$class == ' A') {
				$flight[0]['class'] = '头等舱';
			} else if($class==' C'||$class==' D'||$class==' J') {
				$flight[0]['class'] = '商务舱';
			} else if($class==' Y'||$class==' B'||$class==' H'||$class==' K'||$class==' L'||$class==' M'||$class==' N'
				||$class==' Q'||$class==' T'||$class==' X'||$class==' U'||$class==' E'||$class==' R'||$class==' O'
				||$class==' Z'||$class==' V'||$class==' G'||$class==' S') {
				$flight[0]['class'] = '经济舱';
			}
       		$request[$key]['flight'] = $flight;
       		$request[$key]['hotel'] = $hotel;
       		$request[$key]['train'] = $train;
       		$request[$key]['other'] = $other;
       		
       };
	    $a = array('1','4','13','14','16','18');//机票火车的状态基（基于已购买）
       $b = array('3','15','25');//酒店的状态基（基于已购买）
	 foreach ($request as $key2=>$v){
	 	$ab =0;
	 	if($v['orderStatus']== '17'){
		 	if(!empty($v['flight']) ){
		 		foreach($v['flight'] as $key3=>$v1){
		 			if(in_array($v1['status'], $a)){
		 					$ab =0;
		 			}else{
		 					$ab =1;
		 			}	
		 		}
	
		 		};
		 	if(!empty($v['train'])){
		 		if($ab==0){
		 			foreach($v['train'] as $key4=>$v2){
		 				if(in_array($v2['status'], $a)){
		 						$ab =0;
		 					}else{
		 						$ab =1;
		 				}	
		 		}
		 			}
		 		};
		 	if(!empty($v['hotel'])){
		 		if($ab==0){
		 			 foreach($v['hotel'] as $key5=>$v3){
						  if(in_array($v3['status'], $b)){
						       	$ab = 0;	
						   }else{
						       						
						       	$ab = 1;
						       					
						       	}
       						} 
		 		
		 		}
		 	
		 	}
	 			
	 		
	 	
	 	}
       		
       	$request[$key2]['limit'] = $ab;	
       
     }
       

       
		return $request;
	}

	
	/**
	 * Checkout the orders which has not been paid, including information, according to userId
	 * Pay now
	 * Programmer: Yu Zhuoran
	 */
	public function queryNotPay($id,$Page,$data) {
	if($Page == 0){
			$link = "";
		}else{
			$link = " limit $Page->firstRow , $Page->listRows";
		}
		
		$start_date = $data ['start_date'];
		$end_date = $data ['end_date'];
		$keywords = $data ['keywords'];
		
		if ($start_date!='') {
			$start_date = $start_date . " 00:00:00";
			$where = " and unix_timestamp(o.time) >= unix_timestamp('".$start_date."')";
		}
		if ($end_date!='') {
			$end_date = $end_date . " 23:59:59";
			$where .=  " and unix_timestamp(o.time) <= unix_timestamp('".$end_date."')";
		}
		if ($keywords!='') {
			$where .= " and (o.order_num  like '%$keywords%' or tmc.name  like '%$keywords%')";
		}
		
		
		$employee =  M('employee');
		$empId = $employee->where('u_id = '.$id)->getField('id');
		$m = M('');
        $sql="
        SELECT 
o.id orderId,
o.uodr_id uodrId,
o.order_num orderNum,
o.src_id srcId,
o.src src,
o.time orderTime,
o.co_id comId,
o.u_id userId,
o.amount amount,
o.service_price servPrice,
o.pay_type payType,
o.tmc_id tmcId,
o.tmc_uid tmcEmId,
o.tmc_uname tmcEmName,
o.tmc_note tmcNote,
o.`status` orderStatus,
tmc.name tmcName

FROM 73go_order AS o 
LEFT JOIN 73go_tmc AS tmc ON tmc.id = o.tmc_id

WHERE o.u_id = ".$empId." AND o.uodr_Id is NULL AND o.`status` = 11 AND o.pay_type = 0  ".$where." order by o.id desc".$link;
        
       $request = $m->query($sql);
       foreach($request as $key=>$vo){
       		$uname = '';
       		$sql1 = "select * from 73go_order_user where o_id = ".$vo['orderId']; 
       		$sql2 = "select flt.*,(flt.price+flt.baf+flt.acf+flt.tax) allPrice,insur.id insurId from 73go_flight_ticket_info AS flt LEFT JOIN 73go_air_insur_info AS insur ON insur.a_id = flt.id where flt.o_id = ".$vo['orderId'];
       		$sql3 = "select * from 73go_hotel_info where o_id = ".$vo['orderId'];
       		$sql4 = "select * from 73go_train_ticket_info where o_id = ".$vo['orderId'];
       		$sql5 = "select * from 73go_other_produ_info where o_id = ".$vo['orderId'];
       		$user = $m->query($sql1);
       		$flight = $m->query($sql2);
       		$hotel = $m->query($sql3);
       		$train = $m->query($sql4);
       		$other = $m->query($sql5);
       		foreach ($user as $key1=>$vo1){
       				$uname.= $vo1['name']."<br/>";	
       			
       		}
       		$request[$key]['uname'] = $uname;
       		$request[$key]['flight'] = $flight;
       		$request[$key]['hotel'] = $hotel;
       		$request[$key]['train'] = $train;
       		$request[$key]['other'] = $other;
       		
       }
		return $request;
	}
	
	
     /**
	 * Checkout the orders which has not been confirmed, including information, according to userId
	 * Pay once a month
	 * Programmer: Yu Zhuoran
	 */
	public function queryNotConfirm($id,$Page,$data) {
	if($Page == 0){
			$link = "";
		}else{
			$link = " limit $Page->firstRow , $Page->listRows";
		}
		
		$start_date = $data ['start_date'];
		$end_date = $data ['end_date'];
		$keywords = $data ['keywords'];
		
		if ($start_date!='') {
			$start_date = $start_date . " 00:00:00";
			$where = " and unix_timestamp(o.time) >= unix_timestamp('".$start_date."')";
		}
		if ($end_date!='') {
			$end_date = $end_date . " 23:59:59";
			$where .=  " and unix_timestamp(o.time) <= unix_timestamp('".$end_date."')";
		}
		if ($keywords!='') {
			$where .= " and (o.order_num  like '%$keywords%' or tmc.name  like '%$keywords%')";
		}
		
		
		$employee =  M('employee');
		$empId = $employee->where('u_id = '.$id)->getField('id');
		$m = M('');
        $sql="
        SELECT 
o.id orderId,
o.uodr_id uodrId,
o.order_num orderNum,
o.src_id srcId,
o.src src,
o.time orderTime,
o.co_id comId,
o.u_id userId,
o.amount amount,
o.service_price servPrice,
o.pay_type payType,
o.tmc_id tmcId,
o.tmc_uid tmcEmId,
o.tmc_uname tmcEmName,
o.tmc_note tmcNote,
o.`status` orderStatus,
tmc.name tmcName

FROM 73go_order AS o 
LEFT JOIN 73go_tmc AS tmc ON tmc.id = o.tmc_id

WHERE o.u_id = ".$empId." AND o.uodr_Id is NULL AND o.`status` = 6 AND o.pay_type = 1  ".$where." order by o.id desc".$link;
        
       $request = $m->query($sql);
       foreach($request as $key=>$vo){
       		$uname = '';
       		$sql1 = "select * from 73go_order_user where o_id = ".$vo['orderId']; 
       		$sql2 = "select flt.*,(flt.price+flt.baf+flt.acf+flt.tax) allPrice,insur.id insurId from 73go_flight_ticket_info AS flt LEFT JOIN 73go_air_insur_info AS insur ON insur.a_id = flt.id where flt.o_id = ".$vo['orderId'];
       		$sql3 = "select * from 73go_hotel_info where o_id = ".$vo['orderId'];
       		$sql4 = "select * from 73go_train_ticket_info where o_id = ".$vo['orderId'];
       		$sql5 = "select * from 73go_other_produ_info where o_id = ".$vo['orderId'];
       		$user = $m->query($sql1);
       		$flight = $m->query($sql2);
       		$hotel = $m->query($sql3);
       		$train = $m->query($sql4);
       		$other = $m->query($sql5);
       		foreach ($user as $key1=>$vo1){
       				$uname.= $vo1['name']."<br/>";	
       			
       		}
       		$request[$key]['uname'] = $uname;
       		$request[$key]['flight'] = $flight;
       		$request[$key]['hotel'] = $hotel;
       		$request[$key]['train'] = $train;
       		$request[$key]['other'] = $other;
       		
       }

		return $request;
	}
	
	
    /**
	 * Checkout the orders which has not been cancelled, including information, according to userId
	 * 
	 * Programmer: Yu Zhuoran
	 */
	public function queryCancel($id,$Page,$data) {
	if($Page == 0){
			$link = "";
		}else{
			$link = " limit $Page->firstRow , $Page->listRows";
		}
		
		$start_date = $data ['start_date'];
		$end_date = $data ['end_date'];
		$keywords = $data ['keywords'];
		
		if ($start_date!='') {
			$start_date = $start_date . " 00:00:00";
			$where = " and unix_timestamp(o.time) >= unix_timestamp('".$start_date."')";
		}
		if ($end_date!='') {
			$end_date = $end_date . " 23:59:59";
			$where .=  " and unix_timestamp(o.time) <= unix_timestamp('".$end_date."')";
		}
		if ($keywords!='') {
			$where .= " and (o.order_num  like '%$keywords%' or tmc.name  like '%$keywords%')";
		}
		
		
		$employee =  M('employee');
		$empId = $employee->where('u_id = '.$id)->getField('id');
		$m = M('');
        $sql="
        SELECT 
o.id orderId,
o.uodr_id uodrId,
o.order_num orderNum,
o.src_id srcId,
o.src src,
o.time orderTime,
o.co_id comId,
o.u_id userId,
o.amount amount,
o.service_price servPrice,
o.pay_type payType,
o.tmc_id tmcId,
o.tmc_uid tmcEmId,
o.tmc_uname tmcEmName,
o.tmc_note tmcNote,
o.`status` orderStatus,
tmc.name tmcName

FROM 73go_order AS o 
LEFT JOIN 73go_tmc AS tmc ON tmc.id = o.tmc_id

WHERE o.u_id = ".$empId." AND o.uodr_Id is NULL AND o.`status` = 19  ".$where." order by o.id desc".$link;
        
       $request = $m->query($sql);
       foreach($request as $key=>$vo){
       		$uname = '';
       		$sql1 = "select * from 73go_order_user where o_id = ".$vo['orderId']; 
       		$sql2 = "select flt.*,(flt.price+flt.baf+flt.acf+flt.tax) allPrice,insur.id insurId from 73go_flight_ticket_info AS flt LEFT JOIN 73go_air_insur_info AS insur ON insur.a_id = flt.id where flt.o_id = ".$vo['orderId'];
       		$sql3 = "select * from 73go_hotel_info where o_id = ".$vo['orderId'];
       		$sql4 = "select * from 73go_train_ticket_info where o_id = ".$vo['orderId'];
       		$sql5 = "select * from 73go_other_produ_info where o_id = ".$vo['orderId'];
       		$user = $m->query($sql1);
       		$flight = $m->query($sql2);
       		$hotel = $m->query($sql3);
       		$train = $m->query($sql4);
       		$other = $m->query($sql5);
       		foreach ($user as $key1=>$vo1){
       				$uname.= $vo1['name']."<br/>";	
       			
       		}
       		$request[$key]['uname'] = $uname;
       		$request[$key]['flight'] = $flight;
       		$request[$key]['hotel'] = $hotel;
       		$request[$key]['train'] = $train;
       		$request[$key]['other'] = $other;
       		
       }
		return $request;
	}
	
	
	
/**
	 * Checkout the expired flight, including information, according to userId
	 * 
	 * Programmer: Yu Zhuoran
	 */
	public function queryExpFlight($id,$Page) {
	
        if($Page == 0){
			$link = "";
		}else{
			$link = " limit $Page->firstRow , $Page->listRows";
		}
		$employee =  M('employee');
		$empId = $employee->where('u_id = '.$id)->getField('id');
		$m = M('');
        $sql="
        SELECT 
o.id orderId,
o.uodr_id uodrId,
o.order_num orderNum,
o.src_id srcId,
o.src src,
o.time orderTime,
o.co_id comId,
o.u_id userId,
o.amount amount,
o.service_price servPrice,
o.pay_type payType,
o.tmc_id tmcId,
o.tmc_uid tmcEmId,
o.tmc_uname tmcEmName,
o.tmc_note tmcNote,
o.`status` orderStatus,
tmc.name tmcName

FROM 73go_order AS o 
LEFT JOIN 73go_tmc AS tmc ON tmc.id = o.tmc_id

WHERE o.u_id = ".$empId." AND o.uodr_Id is NULL order by o.id desc".$link;
        
       $request = $m->query($sql);
       foreach($request as $key=>$vo){
       		$uname = '';
       		$sql1 = "select * from 73go_order_user where o_id = ".$vo['orderId']; 
       		$sql2 = "select flt.*,(flt.price+flt.baf+flt.acf+flt.tax) allPrice,insur.id insurId from 73go_flight_ticket_info AS flt LEFT JOIN 73go_air_insur_info AS insur ON insur.a_id = flt.id where flt.`status`=18 AND flt.o_id = ".$vo['orderId'];
       		$user = $m->query($sql1);
       		$flight = $m->query($sql2);
       	 if($flight){
       		foreach ($user as $key1=>$vo1){
       				$uname.= $vo1['name']."<br/>";	
       		}
       		$request[$key]['uname'] = $uname;
       		$request[$key]['flight'] = $flight;
       	 }
       	 else{
       	 	unset($request[$key]);
       	 }
       		
       }
       
       
		return $request;
	}
	
	
	/**
	 * 
	 * 根据传来的机票产品详情id
	 * 查询所属的订单的（所有信息）
	 * 同时查出订单使用者，TMC公司
	 * $flightId,$hotelId,$trainId只有一个有值
	 * 
	 * 
	 */
	public function queryChange($flightId,$hotelId,$trainId){
		$tagChoice = array();
		$tagNotChoice = array();
		$m = M('');
		$flight = M('flight_ticket_info');
		$hotel = M('hotel_info');
		$train = M('train_ticket_info');
		
		//获取产品所属订单id
		//获取企业选择的改签、改单的产品详情
		if($flightId != 0){
			$orderId = $flight->where('id='.$flightId)->getField('o_id');
			$sql = "select flt.*,(flt.price+flt.baf+flt.acf+flt.tax) allPrice,insur.id insurId from 73go_flight_ticket_info AS flt LEFT JOIN 73go_air_insur_info AS insur ON insur.a_id = flt.id where flt.id = ".$flightId;
			$tagChoice['flight'] = $m->query($sql);
		}
	    if($hotelId != 0){
			$orderId = $hotel->where('id='.$hotelId)->getField('o_id');
			$sql = "select * from 73go_hotel_info where id = ".$hotelId;
			$tagChoice['hotel'] = $m->query($sql);
		}
	    if($trainId != 0){
			$orderId = $train->where('id='.$trainId)->getField('o_id');
			$sql = "select * from 73go_train_ticket_info where id = ".$trainId;
			$tagChoice['train'] = $m->query($sql);
		}
		
		$sqlOreder="
        SELECT 
o.id orderId,
o.uodr_id uodrId,
o.order_num orderNum,
o.src_id srcId,
o.src src,
o.time orderTime,
o.co_id comId,
o.u_id userId,
o.amount amount,
o.service_price servPrice,
o.pay_type payType,
o.tmc_id tmcId,
o.tmc_uid tmcEmId,
o.tmc_uname tmcEmName,
o.tmc_note tmcNote,
o.`status` orderStatus,
tmc.name tmcName

FROM 73go_order AS o 
LEFT JOIN 73go_tmc AS tmc ON tmc.id = o.tmc_id

WHERE o.id = ".$orderId." AND o.uodr_Id is NULL";
		
		$change = $m->query($sqlOreder);
		
		//choice被选择要改签改单的产品
		$change[0]['choice'] = $tagChoice;
		
		
		//该订单的使用者
		$sqlName = "select * from 73go_order_user where o_id = ".$orderId;
		$change[0]['name']= $m->query($sqlName);
		
		//notChoice该订单的其他产品
		$sqlFlight = "select flt.*,(flt.price+flt.baf+flt.acf+flt.tax) allPrice,insur.id insurId from 73go_flight_ticket_info AS flt LEFT JOIN 73go_air_insur_info AS insur ON insur.a_id = flt.id where flt.o_id = ".$orderId." AND flt.id != ".$flightId;
       	$sqlHotel = "select * from 73go_hotel_info where o_id = ".$orderId." AND id != ".$hotelId;
       	$sqlTrain = "select * from 73go_train_ticket_info where o_id = ".$orderId." AND id != ".$trainId;
       	$sqlOther = "select * from 73go_other_produ_info where o_id = ".$orderId;
       	$tagNotChoice['flight'] = $m->query($sqlFlight);
       	$tagNotChoice['hotel'] = $m->query($sqlHotel);
       	$tagNotChoice['train'] = $m->query($sqlTrain);
       	$tagNotChoice['other'] = $m->query($sqlOther);
		$change[0]['notChoice'] = $tagNotChoice;
		
		
		return $change;
		
		
		
	}
	
	
	
	
	
	
	
	
}