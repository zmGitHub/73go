<?php
namespace Home\Controller;
use System\LoginInfo;
use Think\Controller;
use UMessage\Logic\UnifyMessage;
use UMessage\Logic\UnifyMessageSender;

class EasygoController extends Controller{

	//加载轻松行预定页面
	//判断该用户是否具有紧急预定
	public function  qsx_req(){
		//加载布局文件
		layout("home");
		$emergencyM=D("Home/Emergency","Logic");
		$book_info=$emergencyM->emergency_book();
		$this->assign('datt',$book_info);
		$this->theme("default")->display('qsx_req');
	}
	//添加轻松行，需求 内容
	public function add_qsx_req(){
		//print_r($_POST);EXIT;
		$id=LI("userId");
		//$content=str_replace("\r\n","<br>",$content);
		$qsx_req = M("qsx_req");
		$tmc_qsx_rec = M('tmc_qsx_rec');
		$tmc = M("tmc");
		$tmc_cert_request=M("tmc_cert_request");

		$company=M('company');
		//$data=$_POST;

		//生成 qsx订单号
		$num=VNumGen('qsx_req');
		$data['qsx_rq_no']=$num;
		$data['u_id']=$id;
		$data['status']=0;
		$data['tr_no']=$_POST['tr_no'];
		$data['leave_date']=$_POST['leave_date'];
		$data['back_date']=$_POST['back_date'];
		$data['from_city']=$_POST['from_city'];
		$data['to_city']=$_POST['to_city'];
		$data['submit_time']=date('Y-m-d H:i:s',time());
		$other_content=$_POST['other_content'];
		$data['other_content']=str_replace("\r\n","<br>",$other_content);
		//$result=$qsx_req->add($data);
		//echo $result;exit;
		//筛选出TMC公司已经认证通过的公司
		$info=$tmc->where('cert_val=1')->select();

		if($_POST['other_content']==''){
			$data['other'] = 0;
		}
		else{
			$data['other'] = 1;
		}
		$result=$qsx_req->data($data)->add();

		if($result){

			// 发送短信和邮件  郭攀  2015.3.2
			// 企业员工出差需求提交成功通知
			// 调用Logic/ApprovalLogic.class.php

			$ju_name=D('Home/JudgeName','Logic');
			$result_name=$ju_name->UserName();
			$employee = $result_name;
			$wx_openid=Li('userOpenid');//获取预订者的openid ；
			$sender_1 = new UnifyMessageSender ();
			//添加微信发送消息的推送 根据用户表中的wx_openid;
			//郭攀
			$types =  strlen($wx_openid) != 28 ? array(1,2) : array(1,2,3);
			$targets = strlen($wx_openid) != 28 ? array (1 => $employee['phone'], 2 =>$employee['email']) : array (1 => $employee['phone'], 2 =>$employee['email'], 3 => $wx_openid);
			//$types = array(1,2);
			//$targets = array (	1 => $employee ['phone'],2 =>  $employee ['email']);
			$title =  '企业员工出差需求提交成功通知';
			$text =  $employee['name'].'您提交'.$_POST['leave_date']."至".$_POST['back_date']." ".$_POST['from_city']."-".$_POST['to_city'].",".$data['other_content']."的需求已经发送成功。请耐心等待TMC为您提供方案。";
			$html = '尊敬的'.$employee['name'].'<br/>
						您提交'.$_POST['leave_date']."至".$_POST['back_date']." ".$_POST['from_city']."-".$_POST['to_city'].",".$data['other_content']."的需求已经发送成功。请耐心等待TMC为您提供方案。";

			$um_1 = UnifyMessage::NewUnionMessage ( $types,$targets,$title,$text, $html );
			$sender_1->sendUMessage ($um_1);



			foreach($info as $key=>$vo){

				$map['req_id'] = $result;
				$map['tmc_id']= $vo['id'];
				$map['distr_time']=date('Y-m-d H:i:s',time());
				$map['status']= 0;
				$res = $tmc_qsx_rec->data($map)->add();
				//发送短信和邮件  郭攀  2015.3.2
				//将需求单分发给所有TMC企业，给所有TMC企业发送新需求单的请求


				//查询出提出人所在的公司
				$coName=$company->field('name')->where('id='.$employee['co_id'])->find();
				//根据tmcID公司，查询好出tmc 公司下所有tmc员工的邮箱和电话
				$Temp=M("tmc_employee");
				$Tmc_emp=$Temp->where("tmc_id=".$map['tmc_id'])->select();

				foreach($Tmc_emp as $kk=>$vv){
					//查询出所有op员工对应用户表中的 wx_openid ,进行微信的发送信息的添加
					//郭攀
					$user=M("user");
					$datt_result=$user->where("id=".$vv['u_id'])->select();//查询出 所有OP专员对应的wx_openid;
					//循环遍历出所有OP专员对应的wx_openid;
					foreach($datt_result as $k1=>$v1){
						$types =  strlen($v1['wx_openid']) != 28 ? array(1,2) : array(1,2,3);
						$targets = strlen($v1['wx_openid']) != 28 ? array (1 => $vv['phone'], 2 =>$vv['email']) : array (1 => $vv['phone'], 2 =>$vv['email'], 3 =>$v1['wx_openid']);

					}
					//$targets = array (	1 => $vv['phone'],2 =>$vv['email']);
					$sender[$key] = new UnifyMessageSender ();
					//$types = array(1,2);
					$title =  '企业员工出差需求通知TMC';
					$text =  $vv['name'].':'.$coName['name'].$employee['name']."发出".$_POST['leave_date']."至".$_POST['back_date']." ".$_POST['from_city']."-".$_POST['to_city'].",".$data['other_content']."的需求。请尽快为客户提供方案。";
					$html = $vv['name'].'：<br />.'.
						$coName['name'].$employee['name']."发出".$_POST['leave_date']."至".$_POST['back_date']." ".$_POST['from_city']."-".$_POST['to_city'].",".$data['other_content']."的需求。请尽快为客户提供方案。";

					$um[$key] = UnifyMessage::NewUnionMessage ( $types,$targets,$title,$text, $html );
					$sender[$key]->sendUMessage ($um[$key]);
				}
			}

            /**
             *将提交变成ajax提交的方式
             *修改人：王月
             *修改日期： 2015-3-16
             */
            $this->ajaxReturn(1);
		}else{
            $this->ajaxReturn(-1);
		}

	}

	//加载方案查看的列表
	public function qsx_req_list(){
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


	}

		//加载解决方案的列表
		public function qsx_solution(){
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

	}

		public  function  off(){
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

	}
	//选取方案
	public function check(){
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






	}
	public function turnoff(){
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

	}




}

