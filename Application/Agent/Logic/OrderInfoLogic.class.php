<?php
namespace Agent\Logic;
use Think\Model;
use Common\Datasource\Datasource;
/**
 * 根据订单单号查看订单详情
 * 创建者：董发勇
 * 创建时间：2014-12-17下午02:45:53
 *
 *
 */
class OrderInfoLogic extends Model {
	/**
	 * 查询订单详情
	 * 创建者：董发勇
	 * 创建时间：2014-12-17下午02:47:19
	 */
	public function showOrderInfoLogic($orderid){
		//根据orderid查询联合订单id，看是否有联合订单
		$OrderInfo=M('');
		$sql="select a. id aid,b.id bid,a.order_num,a.amount,b.emp_id,b.`name`,b.id_type,b.id_no,b.phone ,a.amount,a.service_price
		from 73go_order  a left JOIN 73go_order_user b on a.id=b.o_id 
		where a.order_num=$orderid and (a.`status`=20 or a.`status`=28)";
		$request=$OrderInfo->query($sql);
		//列出订单号下面的订单机票信息
		foreach ($request as $keyo=>$val){
			$fightsql="select a.id fightid, a.pnr,a.airline_num,a.class,a.time_dep,a.city_from,a.time_arv,a.city_to,a.price,a.baf,a.acf,a.tax,a.refund_policy,a.tmc_note,a.refund_price,a.status
			from 73go_flight_ticket_info a where a.o_id=".$val['aid'];
			$fightrequest=$OrderInfo->query($fightsql);
			
			
			foreach ($fightrequest as $key1 => $value){
					$fightinfosql="select a.id fightinfoid,a.h_id,a.ou_id,a.seat_num,a.refund_price,a.status   
					from 73go_flight_userinfo a LEFT JOIN 73go_order_user o ON a.ou_id=o.id 
					where a.h_id=".$value['fightid'];
					$fightinforequest=$OrderInfo->query($fightinfosql);
					$fightrequest[$key1]['flightinfoorder']=$fightinforequest;
			}
			
			
			$request[$keyo]['flight']=$fightrequest;
		}
		//飞机票状态98
		foreach ($request as $key=>$val){
			$fightsql="select a.id fightid, a.pnr,a.airline_num,a.class,a.time_dep,a.city_from,a.time_arv,a.city_to,a.price,a.baf,a.acf,a.tax,a.refund_policy,a.tmc_note,a.refund_price,a.status
			from 73go_flight_ticket_info a where a.status=98 and a.o_id=".$val['aid'];
			$fightrequest=$OrderInfo->query($fightsql);
			
			foreach ($fightrequest as $key1 => $value){
					$fightinfosql="select a.id fightinfoid,a.h_id,a.ou_id,a.seat_num,a.refund_price,a.status   
					from 73go_flight_userinfo a LEFT JOIN 73go_order_user o ON a.ou_id=o.id 
					where a.h_id=".$value['fightid'];
					$fightinforequest=$OrderInfo->query($fightinfosql);
					$fightrequest[$key1]['flightinfoorder']=$fightinforequest;
			}
			
			$request[$key]['flightinfo']=$fightrequest;
			
			
		}
		
		//列出订单号下面的火车票信息
		foreach ($request as $key=>$val){
			$trainsql="select a.boarding_time,a.train_num,a.class_level,a.station_dep,a.station_arv,a.price,a.service_price,a.redund_policy,a.tmc_note,a.refund_price,a.status
			from 73go_train_ticket_info a where a.o_id=".$val['aid'];
			$trainrequest=$OrderInfo->query($trainsql);
			$request[$key]['train']=$trainrequest;
		}
		//列出订单号下面的酒店信息
		foreach ($request as $key=>$val){
			$hotelsql="select a.hotel_name,a.date_ckin,a.room_type,a.count,a.price,a.redund_policy,a.crecard_val,a.hotel_addr,a.hotel_info_url,a.tmc_note,a.refund_price,a.status
			from 73go_hotel_info a where a.o_id=".$val['aid'];
			$hotelrequest=$OrderInfo->query($hotelsql);
			$request[$key]['hotel']=$hotelrequest;
		}
		//列出其他的信息
		foreach ($request as $key=>$val){
			$othersql="select a.time_start,a.time_end,a.content,a.price,a.redund_policy,a.tmc_note,a.content,a.status
			from 73go_other_produ_info a where a.o_id=".$val['aid'];
			$otherrequest=$OrderInfo->query($othersql);
			$request[$key]['other']=$otherrequest;
		}
		print_r($request);
		return $request;
	}
}