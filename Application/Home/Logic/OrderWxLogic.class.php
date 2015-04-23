<?php
namespace Home\Logic;
use Think\Model;
/**
 * Created by PhpStorm.
 * User: guopan
 * Date: 2015.03.11
 * Time: 上午 11:35
 */



class OrderWxLogic extends Model
{

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
     * 查询出绑定微信用户的订单内容
     *
     * 所有订单的显示
     * 待确认订单的显示
     * 待支付订单的显示
     * 已取消订单的显示
     *
     * 郭攀
     */
    public function queryOrder($id,$Page,$status=1) {
        if($Page == 0){
            $link = "";
        }else{
            $link = " limit $Page->firstRow , $Page->listRows";
        }
	
	if($status==1){
	
		$status=" ";
	}else{
		
		$status=" AND o.status =" .$status;
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

WHERE o.u_id = ".$empId." AND o.uodr_Id is NULL  $status   order by o.id desc".$link;

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




}