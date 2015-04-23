<?php
namespace Agent\Controller;
use Think\Controller;
class OrderInfoController extends Controller {
	/**
	 * 根据订单号查询订单详情
	 * 创建者：董发勇
	 * 创建时间：2014-12-17下午03:31:12
	 */
	public function showOrderInfo(){
		//$orderid=$_POST['orderid'];
		$orderid=0004;
		$order=D('Agent/OrderInfo','Logic');//调用订单业务处理层
		$request=$order->showOrderInfoLogic($orderid);//查看订单详细信息
		
		$this->assign('order',$request);
		layout(false);
		$this->theme('agent')->display('order_style_single_confirm');
	}
	/**
	 * 修改订单使用者信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-20下午04:23:44
	 */
	public function updateOrderUser(){
		$add_id=$_POST['bid'];
		$data['name']=$_POST['add_name'];
		$data['id_type']=$_POST['add_id_type'];
		$data['id_no']=$_POST['add_id_no'];
		$data['phone']=$_POST['add_phone'];
		$orderuser=M('order_user');
		$request=$orderuser->where('id='.$add_id)->save($data);
		if($request){
			$this->ajaxReturn(1);
		}else{
			$this->ajaxReturn(0);
		}
	}
	/**
	 * 添加使用者到机票详情使用者信息里面去
	 * 创建者：董发勇
	 * 创建时间：2014-12-20下午04:24:07
	 */
	public function addOrderUser(){
		$order=$_POST['arrs'];
		$order = explode(',', $order);
		print_r($order);
		$fightinfo=M('flight_userinfo');
		foreach($order as $or){
			$data['ou_id']=$or;
			$data['h_id']=$_POST['fid'];
			$data['status']=$_POST['fstatus'];
			$request=$fightinfo->data($data)->add();
		}
		$dataupdate['status']=98;
		$fightid=$_POST['fid'];
		$flight_ticket_info=M('flight_ticket_info');
		$requestinfo=$flight_ticket_info->where('id='.$fightid)->save($dataupdate);
	}
}