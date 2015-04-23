<?php

namespace Agent\Logic;

use Think\Model;
use Common\Datasource\Datasource;

class TmcOrderLogic extends Model {
	
	/**
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
	 */
	
	/**
	 * Checkout all the orders, including information, according to userId
	 *
	 * Programmer: Yu Zhuoran
	 */
	public function queryOrder($id, $Page, $data) {
		$tmc_employee = M ( 'tmc_employee' );
		$user = $tmc_employee->where ( 'u_id = ' . $id )->select ();
		$m = M ( '' );
		if ($Page == 0) {
			$link = "";
		} else {
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
			$where .= " and (o.order_num  like '%$keywords%' or co.name  like '%$keywords%' or em.name  like '%$keywords%')";
		}
		$sql = "
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
co.name comName

FROM 73go_order AS o 
LEFT JOIN 73go_employee AS em ON em.u_id = o.u_id
LEFT JOIN 73go_company AS co ON co.id = em.co_id
WHERE (o.tmc_uid = " . $user [0] ['id'] . " or o.tmc_uid = 0 ) AND o.tmc_id = ". $user[0]['tmc_id'] ." AND o.uodr_Id is NULL  ".$where." order by o.id desc" . $link;
		
		$request = $m->query ( $sql );
		foreach ( $request as $key => $vo ) {
			$uname = '';
			$sql1 = "select * from 73go_order_user where o_id = " . $vo ['orderId'];
			$sql2 = "select flt.*,(flt.price+flt.baf+flt.acf+flt.tax) allPrice,insur.id insurId from 73go_flight_ticket_info AS flt LEFT JOIN 73go_air_insur_info AS insur ON insur.a_id = flt.id where flt.o_id = " . $vo ['orderId'];
			$sql3 = "select * from 73go_hotel_info where o_id = " . $vo ['orderId'];
			$sql4 = "select * from 73go_train_ticket_info where o_id = " . $vo ['orderId'];
			$sql5 = "select * from 73go_other_produ_info where o_id = " . $vo ['orderId'];
			$user = $m->query ( $sql1 );
			$flight = $m->query ( $sql2 );
			$hotel = $m->query ( $sql3 );
			$train = $m->query ( $sql4 );
			$other = $m->query ( $sql5 );
			foreach ( $user as $key1 => $vo1 ) {
				$uname .= $vo1 ['name'] . "<br/>";
			}
			$request [$key] ['uname'] = $uname;
			$request [$key] ['flight'] = $flight;
			$request [$key] ['hotel'] = $hotel;
			$request [$key] ['train'] = $train;
			$request [$key] ['other'] = $other;
			$request [$key] ['amount'] = number_format ( $vo ['amount'] + $vo ['servPrice'], 2, '.', '' );
		}
		
		// 判断是否具体操作权限
		$a = array (
				'1',
				'4',
				'13',
				'14',
				'16',
				'18' 
		); // 机票火车的状态基（基于已购买）
		$b = array (
				'3',
				'15',
				'25' 
		); // 酒店的状态基（基于已购买）
		$c = array (
				'2' 
		); // 机票火车的状态（基于已确认和已付款，但未出票）
		$d = array (
				'10' 
		); // 酒店状态（基于已确认，但未预定）
		foreach ( $request as $key2 => $v ) {
			$ab = 0;
			/* 状态为已确认，已支付 */
			if ($v ['orderStatus'] == '20') { // 状态为确认
				if (! empty ( $v ['flight'] )) {
					if ($ab == 0) {
						foreach ( $v ['flight'] as $key3 => $v1 ) {
							if (in_array ( $v1 ['status'], $c )) {
								$ab = 0;
							} else {
								$ab = 1;
							}
						}
					}
				}
				;
				if (! empty ( $v ['train'] )) {
					if ($ab == 0) {
						foreach ( $v ['train'] as $key4 => $v2 ) {
							if (in_array ( $v2 ['status'], $c )) {
								$ab = 0;
							} else {
								$ab = 1;
							}
						}
					}
				}
				;
				if (! empty ( $v ['hotel'] )) {
					if ($ab == 0) {
						foreach ( $v ['hotel'] as $key5 => $v3 ) {
							if (in_array ( $v3 ['status'], $d )) {
								$ab = 0;
							} else {
								
								$ab = 1;
							}
						}
					}
				}
			}
			/* 状态基于已购买 */
			if ($v ['orderStatus'] == '17') {
				if (! empty ( $v ['flight'] )) {
					if ($ab == 0) {
						foreach ( $v ['flight'] as $key6 => $v4 ) {
							if (in_array ( $v4 ['status'], $a )) {
								$ab = 0;
							} else {
								$ab = 1;
							}
						}
					}
				}
				;
				if (! empty ( $v ['train'] )) {
					if ($ab == 0) {
						foreach ( $v ['train'] as $key7 => $v5 ) {
							if (in_array ( $v5 ['status'], $a )) {
								$ab = 0;
							} else {
								$ab = 1;
							}
						}
					}
				}
				;
				if (! empty ( $v ['hotel'] )) {
					if ($ab == 0) {
						foreach ( $v ['hotel'] as $key8 => $v6 ) {
							if (in_array ( $v6 ['status'], $b )) {
								$ab = 0;
							} else {
								
								$ab = 1;
							}
						}
					}
				}
			}
			
			$request [$key2] ['limit'] = $ab;
		}
		
		return $request;
	}
	
	/**
	 * Checkout the orders which has not been paid, including information, according to userId
	 * Pay now
	 * Programmer: Yu Zhuoran
	 */
	public function queryNotPay($id, $Page,$data) {
		$tmc_employee = M ( 'tmc_employee' );
		$user = $tmc_employee->where ( 'u_id = ' . $id )->select ();
		$m = M ( '' );
		if ($Page == 0) {
			$link = "";
		} else {
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
			$where .= " and (o.order_num  like '%$keywords%' or co.name  like '%$keywords%' or em.name  like '%$keywords%')";
		}
		
		$sql = "
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
co.name comName

FROM 73go_order AS o 
LEFT JOIN 73go_employee AS em ON em.u_id = o.u_id
LEFT JOIN 73go_company AS co ON co.id = em.co_id

WHERE (o.tmc_uid = " . $user [0] ['id'] . " or o.tmc_uid = 0 ) AND o.tmc_id = ". $user[0]['tmc_id'] ." AND o.uodr_Id is NULL AND o.`status` = 11 AND o.pay_type = 0 ".$where." order by o.id desc" . $link;
		
		$request = $m->query ( $sql );
		foreach ( $request as $key => $vo ) {
			$uname = '';
			$sql1 = "select * from 73go_order_user where o_id = " . $vo ['orderId'];
			$sql2 = "select flt.*,(flt.price+flt.baf+flt.acf+flt.tax) allPrice,insur.id insurId from 73go_flight_ticket_info AS flt LEFT JOIN 73go_air_insur_info AS insur ON insur.a_id = flt.id where flt.o_id = " . $vo ['orderId'];
			$sql3 = "select * from 73go_hotel_info where o_id = " . $vo ['orderId'];
			$sql4 = "select * from 73go_train_ticket_info where o_id = " . $vo ['orderId'];
			$sql5 = "select * from 73go_other_produ_info where o_id = " . $vo ['orderId'];
			$user = $m->query ( $sql1 );
			$flight = $m->query ( $sql2 );
			$hotel = $m->query ( $sql3 );
			$train = $m->query ( $sql4 );
			$other = $m->query ( $sql5 );
			foreach ( $user as $key1 => $vo1 ) {
				$uname .= $vo1 ['name'] . "<br/>";
			}
			$request [$key] ['uname'] = $uname;
			$request [$key] ['flight'] = $flight;
			$request [$key] ['hotel'] = $hotel;
			$request [$key] ['train'] = $train;
			$request [$key] ['other'] = $other;
			$request [$key] ['amount'] = number_format ( $vo ['amount'] + $vo ['servPrice'], 2, '.', '' );
		}
		return $request;
	}
	
	/**
	 * Checkout the orders which has not been confirmed, including information, according to userId
	 * Pay once a month
	 * Programmer: Yu Zhuoran
	 */
	public function queryNotConfirm($id, $Page,$data) {
		$tmc_employee = M ( 'tmc_employee' );
		$user = $tmc_employee->where ( 'u_id = ' . $id )->select ();
		$m = M ( '' );
		if ($Page == 0) {
			$link = "";
		} else {
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
			$where .= " and (o.order_num  like '%$keywords%' or co.name  like '%$keywords%' or em.name  like '%$keywords%')";
		}
		
		$sql = "
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
co.name comName

FROM 73go_order AS o 
LEFT JOIN 73go_employee AS em ON em.u_id = o.u_id
LEFT JOIN 73go_company AS co ON co.id = em.co_id

WHERE  (o.tmc_uid = " . $user [0] ['id'] . " or o.tmc_uid = 0 ) AND o.tmc_id = ". $user[0]['tmc_id'] ." AND o.uodr_Id is NULL AND o.`status` = 6 and o.pay_type = 1 ".$where." order by o.id desc" . $link;
		
		$request = $m->query ( $sql );
		foreach ( $request as $key => $vo ) {
			$uname = '';
			$sql1 = "select * from 73go_order_user where o_id = " . $vo ['orderId'];
			$sql2 = "select flt.*,(flt.price+flt.baf+flt.acf+flt.tax) allPrice,insur.id insurId from 73go_flight_ticket_info AS flt LEFT JOIN 73go_air_insur_info AS insur ON insur.a_id = flt.id where flt.o_id = " . $vo ['orderId'];
			$sql3 = "select * from 73go_hotel_info where o_id = " . $vo ['orderId'];
			$sql4 = "select * from 73go_train_ticket_info where o_id = " . $vo ['orderId'];
			$sql5 = "select * from 73go_other_produ_info where o_id = " . $vo ['orderId'];
			$user = $m->query ( $sql1 );
			$flight = $m->query ( $sql2 );
			$hotel = $m->query ( $sql3 );
			$train = $m->query ( $sql4 );
			$other = $m->query ( $sql5 );
			foreach ( $user as $key1 => $vo1 ) {
				$uname .= $vo1 ['name'] . "<br/>";
			}
			$request [$key] ['uname'] = $uname;
			$request [$key] ['flight'] = $flight;
			$request [$key] ['hotel'] = $hotel;
			$request [$key] ['train'] = $train;
			$request [$key] ['other'] = $other;
			$request [$key] ['amount'] = number_format ( $vo ['amount'] + $vo ['servPrice'], 2, '.', '' );
		}
		return $request;
	}
	
	/**
	 * Checkout the orders which has not been cancelled, including information, according to userId
	 *
	 * Programmer: Yu Zhuoran
	 */
	public function queryCancel($id, $Page,$data) {
		$tmc_employee = M ( 'tmc_employee' );
		$user = $tmc_employee->where ( 'u_id = ' . $id )->select ();
		$m = M ( '' );
		if ($Page == 0) {
			$link = "";
		} else {
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
			$where .= " and (o.order_num  like '%$keywords%' or co.name  like '%$keywords%' or em.name  like '%$keywords%')";
		}
		
		$sql = "
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
co.name comName

FROM 73go_order AS o 
LEFT JOIN 73go_employee AS em ON em.u_id = o.u_id
LEFT JOIN 73go_company AS co ON co.id = em.co_id

WHERE (o.tmc_uid = " . $user [0] ['id'] . " or o.tmc_uid = 0 ) AND o.tmc_id = ". $user[0]['tmc_id'] ." AND o.uodr_Id is NULL AND o.`status` = 19 ".$where." order by o.id desc " . $link;
		
		$request = $m->query ( $sql );
		foreach ( $request as $key => $vo ) {
			$uname = '';
			$sql1 = "select * from 73go_order_user where o_id = " . $vo ['orderId'];
			$sql2 = "select flt.*,(flt.price+flt.baf+flt.acf+flt.tax) allPrice,insur.id insurId from 73go_flight_ticket_info AS flt LEFT JOIN 73go_air_insur_info AS insur ON insur.a_id = flt.id where flt.o_id = " . $vo ['orderId'];
			$sql3 = "select * from 73go_hotel_info where o_id = " . $vo ['orderId'];
			$sql4 = "select * from 73go_train_ticket_info where o_id = " . $vo ['orderId'];
			$sql5 = "select * from 73go_other_produ_info where o_id = " . $vo ['orderId'];
			$user = $m->query ( $sql1 );
			$flight = $m->query ( $sql2 );
			$hotel = $m->query ( $sql3 );
			$train = $m->query ( $sql4 );
			$other = $m->query ( $sql5 );
			foreach ( $user as $key1 => $vo1 ) {
				$uname .= $vo1 ['name'] . "<br/>";
			}
			$request [$key] ['uname'] = $uname;
			$request [$key] ['flight'] = $flight;
			$request [$key] ['hotel'] = $hotel;
			$request [$key] ['train'] = $train;
			$request [$key] ['other'] = $other;
			$request [$key] ['amount'] = number_format ( $vo ['amount'] + $vo ['servPrice'], 2, '.', '' );
		}
		return $request;
	}
	

	/**
	 * Checkout the orders which waiting for ticket, including information, according to userId
	 * Programmer: Yu Zhuoran
	 */
	public function queryWait($id, $Page,$data) {
		$tmc_employee = M ( 'tmc_employee' );
		$user = $tmc_employee->where ( 'u_id = ' . $id )->select ();
		$m = M ( '' );
		if ($Page == 0) {
			$link = "";
		} else {
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
			$where .= " and (o.order_num  like '%$keywords%' or co.name  like '%$keywords%' or em.name  like '%$keywords%')";
		}
		
		$sql = "
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
co.name comName

FROM 73go_order AS o 
LEFT JOIN 73go_employee AS em ON em.u_id = o.u_id
LEFT JOIN 73go_company AS co ON co.id = em.co_id

WHERE  (o.tmc_uid = " . $user [0] ['id'] . " or o.tmc_uid = 0 ) AND o.tmc_id = ". $user[0]['tmc_id'] ." AND o.uodr_Id is NULL AND o.`status` = 20 ".$where." order by o.id desc" . $link;
		
		$request = $m->query ( $sql );
		foreach ( $request as $key => $vo ) {
			$uname = '';
			$sql1 = "select * from 73go_order_user where o_id = " . $vo ['orderId'];
			$sql2 = "select flt.*,(flt.price+flt.baf+flt.acf+flt.tax) allPrice,insur.id insurId from 73go_flight_ticket_info AS flt LEFT JOIN 73go_air_insur_info AS insur ON insur.a_id = flt.id where flt.o_id = " . $vo ['orderId'];
			$sql3 = "select * from 73go_hotel_info where o_id = " . $vo ['orderId'];
			$sql4 = "select * from 73go_train_ticket_info where o_id = " . $vo ['orderId'];
			$sql5 = "select * from 73go_other_produ_info where o_id = " . $vo ['orderId'];
			$user = $m->query ( $sql1 );
			$flight = $m->query ( $sql2 );
			$hotel = $m->query ( $sql3 );
			$train = $m->query ( $sql4 );
			$other = $m->query ( $sql5 );
			foreach ( $user as $key1 => $vo1 ) {
				$uname .= $vo1 ['name'] . "<br/>";
			}
			$request [$key] ['uname'] = $uname;
			$request [$key] ['flight'] = $flight;
			$request [$key] ['hotel'] = $hotel;
			$request [$key] ['train'] = $train;
			$request [$key] ['other'] = $other;
			$request [$key] ['amount'] = number_format ( $vo ['amount'] + $vo ['servPrice'], 2, '.', '' );
		}
		return $request;
	}
	
	
	/**
	 * 查询订单
	 * 创建者：甘世凤
	 * 2014-12-18下午03:10:09
	 */
	public function querylistorder() {
		$u_id = LI ( 'userId' );
		$tmc_emp_id = LI ( 'tmcempId' );
		$M = M ( '' );
		
		$sql = "SELECT qs.id as qid,qs.content as qcontent,qs.time as qtime,re.id as rid,re.*,vip.vip_level,
				qs.u_id as quid,us.*,em.* FROM 73go_qsx_solution qs
				LEFT JOIN 73go_qsx_req as re ON qs.req_id = re.id 
				LEFT JOIN 73go_user as us ON re.u_id = us.id
				LEFT JOIN 73go_employee as em ON us.id = em.u_id
				LEFT JOIN 73go_vip_table as vip ON em.id = vip.emp_id
				WHERE qs.`status`=1 AND re.`status`=1 AND qs.u_id=" . $u_id . " 
				ORDER BY vip.vip_level DESC,re.submit_time DESC";
		
		// echo $sql;
		$reult = $M->query ( $sql );
		foreach ( $reult as $k => $val ) {
			$sql2 = "select count(id) as num from 73go_qsx_solution  AS  a 
					WHERE  a.req_id = " . $val ['rid'];
			$count = $M->query ( $sql2 );
			$reult [$k] ['count'] = $count [0] ['num'];
			
			$sql3 = "select name  from 73go_tmc_employee  AS  a 
					WHERE  a.id = " . $tmc_emp_id;
			$names = $M->query ( $sql3 );
			$reult [$k] ['tename'] = $names [0] ['name'];
			
			$sql4 = "select short_name  from 73go_company  AS  a 
					WHERE  a.id = " . $val ['co_id'];
			$cnames = $M->query ( $sql4 );
			$reult [$k] ['short_name'] = $cnames [0] ['short_name'];
		}
		return $reult;
	}
	
	/**
	 * 根据订单id，查询出该订单所有产品
	 *
	 * Programmer: Yu Zhuoran
	 */
	public function queryThisOrder($id) {
		$m = M ( '' );
		$sql = "
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
co.name comName

FROM 73go_order AS o 
LEFT JOIN 73go_employee AS em ON em.u_id = o.u_id
LEFT JOIN 73go_company AS co ON co.id = em.co_id
WHERE o.id = " . $id . " AND o.uodr_Id is NULL";
		
		$request = $m->query ( $sql );
		foreach ( $request as $key => $vo ) {
			$uname = '';
			$sql1 = "select * from 73go_order_user where o_id = " . $vo ['orderId'];
			$sql2 = "select flt.*,(flt.price+flt.baf+flt.acf+flt.tax) allPrice,insur.id insurId from 73go_flight_ticket_info AS flt LEFT JOIN 73go_air_insur_info AS insur ON insur.a_id = flt.id where flt.o_id = " . $vo ['orderId'];
			$sql3 = "select * from 73go_hotel_info where o_id = " . $vo ['orderId'];
			$sql4 = "select * from 73go_train_ticket_info where o_id = " . $vo ['orderId'];
			$sql5 = "select * from 73go_other_produ_info where o_id = " . $vo ['orderId'];
			$user = $m->query ( $sql1 );
			$flight = $m->query ( $sql2 );
			$hotel = $m->query ( $sql3 );
			$train = $m->query ( $sql4 );
			$other = $m->query ( $sql5 );
			foreach ( $user as $key1 => $vo1 ) {
				$uname .= $vo1 ['name'] . "<br/>";
			}
			$request [$key] ['uname'] = $uname;
			$request [$key] ['flight'] = $flight;
			$request [$key] ['hotel'] = $hotel;
			$request [$key] ['train'] = $train;
			$request [$key] ['other'] = $other;
		}
		
		return $request;
	}
	
	/**
	 * 订单退改签申请列表
	 *
	 *
	 * Programmer: Yu Zhuoran
	 */
	public function queryRefundRequestList($id, $Page) {
		$tmc_employee = M ( 'tmc_employee' );
		$tmc = $tmc_employee->where ( 'u_id = ' . $id )->select ();
		$m = M ( '' );
		if ($Page == 0) {
			$link = "";
		} else {
			$link = " order by id desc limit $Page->firstRow , $Page->listRows";
		}
		
		$sql = "SELECT
73go_order_refund_req.id,
73go_order_refund_req.time,
73go_order_refund_req.o_num,
73go_order_refund_req.table_name,
73go_order_refund_req.table_id,
73go_order_refund_req.cmd,
73go_order_refund_req.u_id,
73go_order_refund_req.user_ids,
73go_order_refund_req.user_req,
73go_order_refund_req.tmc_id,
73go_order_refund_req.t_emp_id,
73go_order_refund_req.x_time,
73go_order_refund_req.opinion,
73go_order_refund_req.`status`
FROM
73go_order_refund_req
WHERE tmc_id = " . $tmc [0] ['tmc_id'] . " AND `status` = 0 " . $link;
		
		$request = $m->query ( $sql );
		foreach ( $request as $key => $vo ) {
			$uname = '';
			$user_ids = explode ( ",", $vo ['user_ids'] );
			foreach ( $user_ids as $key1 => $vo1 ) {
				$orderUserTable = M ( 'order_user' );
				$orderUserName = $orderUserTable->where ( 'id=' . $vo1 )->getField ( 'name' );
				$uname .= $orderUserName . " ";
			}
			$request [$key] ['uname'] = $uname;
			$employeeTable = M ( 'employee' );
			$employeeName = $employeeTable->where ( 'id=' . $vo ['u_id'] )->getField ( 'name' ); // 注意：这是来自订单表里的u_id，实际上存的是员工表的id（emp_id）
			$companyId = $employeeTable->where ( 'id=' . $vo ['u_id'] )->getField ( 'co_id' );
			$companyTable = M ( 'company' );
			$companyName = $companyTable->where ( 'id=' . $companyId )->getField ( 'name' );
			$request [$key] ['bookName'] = $employeeName;
			$request [$key] ['company'] = $companyName;
		}
		return $request;
	}
	
	/*
	 * /**
	 * Checkout the flight, including information, according to userId
	 *
	 * Programmer: Yu Zhuoran
	 *
	 * public function queryFlight($id) {
	 *
	 * $tmc_employee = M('tmc_employee');
	 * $user = $tmc_employee->where('u_id = '.$id)->select();
	 * $m = M('');
	 * $sql="
	 * SELECT
	 * o.id orderId,
	 * o.uodr_id uodrId,
	 * o.order_num orderNum,
	 * o.src_id srcId,
	 * o.src src,
	 * o.time orderTime,
	 * o.co_id comId,
	 * o.u_id userId,
	 * o.amount amount,
	 * o.service_price servPrice,
	 * o.pay_type payType,
	 * o.tmc_id tmcId,
	 * o.tmc_uid tmcEmId,
	 * o.tmc_uname tmcEmName,
	 * o.tmc_note tmcNote,
	 * o.`status` orderStatus,
	 * co.name comName
	 *
	 * FROM 73go_order AS o
	 * LEFT JOIN 73go_employee AS em ON em.u_id = o.u_id
	 * LEFT JOIN 73go_company AS co ON co.id = em.co_id
	 *
	 * WHERE o.tmc_uid = ".$user[0]['id']." AND o.uodr_Id is NULL";
	 *
	 * $request = $m->query($sql);
	 * foreach($request as $key=>$vo){
	 * $uname = '';
	 * $sql1 = "select * from 73go_order_user where o_id = ".$vo['orderId'];
	 * $sql2 = "select flt.*,(flt.price+flt.baf+flt.acf+flt.tax) allPrice,insur.id insurId from 73go_flight_ticket_info AS flt LEFT JOIN 73go_air_insur_info AS insur ON insur.a_id = flt.id where flt.o_id = ".$vo['orderId'];
	 * $user = $m->query($sql1);
	 * $flight = $m->query($sql2);
	 * foreach ($user as $key1=>$vo1){
	 * $uname.= $vo1['name']."<br/>";
	 *
	 * }
	 * $request[$key]['uname'] = $uname;
	 * $request[$key]['flight'] = $flight;
	 *
	 * }
	 * return $request;
	 * }
	 *
	 */
}