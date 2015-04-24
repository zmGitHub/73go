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
		$account = '18857166486';//I("session.LoginInfo");//与LI方法等价
		$order = $_POST['order'];
		$order_info = json_decode($order, true);
		$user = $order_info['user'];
		$passengers = array('amount' => '1', 'PassengerList' => array('0' => array('name' => '张鹏', 'ordertype' => 'adult', 'card_type' => '身份证', 'card_id' => '350622198510301039')));//$order_info['passengers'];
		$phone = '18857166486';//$order_info['phone'];
		$flight_type = 'S';//$order_info['flight_type'];
		$flight = array('flight1' => array('flight_num' => 'MF8527', 'dcity' => '上海', 'acity' => '北京', 'dport' => '虹桥', 'aport' => '首都', 'dtime' => '2015-05-20 16:05:00', 'atime' => '2015-05-20 18:05:00', 'cabin_grade' => 'y', 'flight_price' => '500', 'adulttax' => '50', 'oilfee' => '50', 'acci_insu' => '50', 'delay_insu' => '50', 'total_price' => '720'));//$order_info['flight'];
		$passenger_amount = 1;//$passengers['amount'];


		//生成 qsx订单号
		$num = VNumGen('tmc_code');
		$m_order = M('orders');
		$data['order_num'] = $num;
		$data['account'] = $account;
		//$data['phone'] = $phone;
		$data['need_ticket'] = 1;//$order_info['need_ticket'];
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
				$map= $flight['flight1'];
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
				$map= $flight['flight1'];
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

	//加载方案查看的列表
	/*public function order_list(){
		//加载布局文件
		layout('home');
		$id = LI('userId');
		//增加客服信息 dx 15-3-18
		$employee = M("employee");
		$result = $employee->where('u_id='.$id)->find();
		$name = $result['name'];
		$phone = $result['phone'];
		$qq = $result['qq'];
		$this->assign ( 'u_name', $name );
		$this->assign ( 'u_phone', $phone );
		$this->assign ( 'u_qq', $qq );
		//$map['u_id'] = $id;
		$M = M('');

		$qsx_req=M("qsx_req");
		//----------------进行分页的处理-------------------
		//查询出满足要求的分页条数
		//查询出提出需求，如果没有做出方案，点击取消可以删除该需求；
		$datt['status']=array("neq",3);
		$datt['u_id']=$id;
		$count=$qsx_req->where($datt)->count();
		// 实例化分页类 传入总记录数和每页显示的记录数(4)
		$Page  = new \Think\Page($count,4);

		$show       = $Page->show();// 分页显示输出
		$this->assign('Page',$show);// 赋值分页输出

		$sql = "select a.*
						from 73go_qsx_req AS a
						WHERE  a.u_id = ".$id ." and a.status!=3 order by id desc
						limit $Page->firstRow , $Page->listRows";

		$qs_res = $M->query($sql);
		foreach ($qs_res as $key=>$val){
			$sql = "select count(id) as num from 73go_qsx_solution  AS a
								WHERE  a.req_id = ".$val['id'];
			$count = $M->query($sql);
			$qs_res[$key]['count'] = $count[0]['num'];
		};
		//print_r($qs_res);
		$this->assign('data',$qs_res);

		$this->theme('default')->display('qsx_req_list');


	}*/

		//加载解决方案的列表
		/*public function qsx_solution(){
			//加载布局文件
			layout('home');
			$id = LI('userId');
			//增加客服信息 dx 15-3-18
			$employee = M("employee");
			$result = $employee->where('u_id='.$id)->find();
			$name = $result['name'];
			$phone = $result['phone'];
			$qq = $result['qq'];
			$this->assign ( 'u_name', $name );
			$this->assign ( 'u_phone', $phone );
			$this->assign ( 'u_qq', $qq );

			$id = $_GET['id'];
			$M = M('');
			$sql = "select a.*
						from 73go_qsx_req AS a
						WHERE  a.id = ".$id;

		$qs_res = $M->query($sql);
			//
			$sql = "select * from 73go_qsx_solution  where req_id=".$id." and status =0";
			$statusCount = $M->query($sql);
			$this->assign('statusCount',$statusCount);
					$sql = "select a.*, b.name tmcEmpName, b.phone, b.qq,c.name tmcName from 73go_qsx_solution  AS a
					            Left join 73go_tmc_employee AS b ON b.u_id = a.u_id
					            Left join 73go_tmc AS c ON c.id = b.tmc_id
								WHERE  a.req_id = ".$id." and a.status != 3 order by a.status asc";
		$solution = $M->query($sql);
		$count = count($solution);
		$qs_res[0]['count'] = $count;

		$Page  = new \Think\Page($count,3);
		$show       = $Page->show();// 分页显示输出
		$limit = " limit $Page->firstRow , $Page->listRows";
		$solution = $M->query($sql.$limit);

		$this->assign('Page',$show);// 赋值分页输出
		$this->assign('data',$qs_res);
		$this->assign('solution',$solution);
		$this->theme('default')->display('qsx_solution');

	}*/

		/*public  function  off(){
			$id = $_POST['id'];//获取需求id
			$Mqs_req = M('qsx_req');//实例化需求表
			$map['status'] = 3;//取消状态
			$result1 = $Mqs_req->where('id = '.$id)->save($map);

			$tmc_qsx_rec = M('tmc_qsx_rec');//实例化需求表
			$map['status'] = 3;//取消状态
			$result2 = $tmc_qsx_rec->where('req_id = '.$id)->save($map);

			if($result1 && $result2){

			$data = 0;

			$this->ajaxReturn($data,'json');

		}else{

			$data = 1;

			$this->ajaxReturn($data,'json');

		}

	}*/
	//选取方案
	/*public function check(){
		$id = $_POST['id'];//方案id
		$rid = $_POST['rid'];//需求id
		$Mqs_req = M('qsx_req');
		$company=M("company");
		$co_employee=M("employee");
		$map['solu_id'] = $id;
		$map['status'] = 1;
		$map['selec_time'] = date('Y-m-d H:i',time());
		$result = $Mqs_req->where('id='.$rid)->save($map);//更新需求的状态
		if($result){
			$Mqsx_solution = M('qsx_solution');
			$map1['status'] = 1;
			$result = $Mqsx_solution->where('id ='.$id)->save($map1);//更新选中方案的状态
			if($result){

				//发送短信和邮件  郭攀  2015.3.3
				//如果方案被用户选中时，给TMC 公司发送 短信和邮箱
				//查询出OP姓名，公司名称，员工名称。 预定中者公司，预定者姓名 预订者的手机号码；
				//调用logic 中封装的方法；
				$OP=D('Home/OpName','Logic');
				$result_op=$OP->tmcemp($rid); //OP专员的信息
				$wx_openid=$result_op['wx_openid'];//获取OP 专员的openid
				//根据req_id, 查询出预订者的邮箱，和电话号码
				$solution_1=$Mqs_req->where('id='.$rid)->find();//查询出预订者的id
				$result_emp=$co_employee->where('u_id='.$solution_1['u_id'])->find();//查询出预订者的相关信息
				$co_name=$company->where("id=".$result_emp['co_id'])->find();                //查询出预订者的公司名称
				$sender = new UnifyMessageSender ();
				//添加 微信消息发送信息 根据wx_openid 来进行判断
				// 郭攀
				$types =  strlen($wx_openid) != 28 ? array(1,2) : array(1,2,3);
				$targets = strlen($wx_openid) != 28 ? array (1 => $result_op['phone'], 2 =>$result_op['email']) : array (1 => $result_op['phone'], 2 =>$result_op['email'], 3 => $wx_openid);
				//$types = array(1,2);
				//$targets = array (	1 => $result_op['phone'],2 =>$result_op['email']);
				$title =  '员工选择方案后通知OP';
				$text =  $result_op['op_name'].':您为'.$co_name['name'].$result_emp['name']."制作的".$result_op['content']."方案已经被选用。请及时进行处理".$result_emp['co_name']." ".$result_emp['name']." ".$result_emp['phone']."。(".$result_op['tmc_name'].")";
				$html = $result_op['op_name'].'：<br />.'.
					'您为'.$co_name['name'].$result_emp['name']."制作的".$result_op['content']."方案已经被选用。请及时进行处理".$co_name['name']." ".$result_emp['name']." ".$result_emp['phone']."。(".$result_op['tmc_name'].")";

				$um = UnifyMessage::NewUnionMessage ( $types,$targets,$title,$text, $html );
				$sender->sendUMessage ($um);

				$M = M('');
				$sql = "UPDATE 73go_qsx_solution set status =2 where req_id=".$rid." and status =0 ";//更新未被选中方案状态
				$result = $M->execute($sql);
				if($result){
					$data = 2;
					$this->ajaxReturn($data,'json');


				}else{
					$data = 3;
					$this->ajaxReturn($data,'json');
				}

			}else{

				$data = 1;
				$this->ajaxReturn($data,'json');

			}


		}else{
			$data = 0;
			$this->ajaxReturn($data,'json');

		}






	}*/
	/*public function turnoff(){
		$req_id = $_POST['id'];
		$M = M('');
		$sql = "UPDATE 73go_qsx_solution SET status = 2 where req_id=".$req_id." and status =0";
		$result = $M->execute($sql);
		if($result){
			$data = 0;
			$this->ajaxReturn($data,'json');

		}else{
			$data = 1;
			$this->ajaxReturn($data,'json');
		}

	}*/




}

