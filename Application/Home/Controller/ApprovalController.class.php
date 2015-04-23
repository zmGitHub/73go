<?php

namespace Home\Controller;

use System\LoginInfo;
use Think\Controller;
use UMessage\Logic\UnifyMessage;
use UMessage\Logic\UnifyMessageSender;

class ApprovalController extends Controller {
	
	// 加载基本政策页面
	public function co_travel_policy_set() {
		// C('LAYOUT_ON',TRUE);
		// 加载布局文件
		layout ( "home" );
		
		$this->theme ( 'default' )->display ( "co_travel_policy_set" );
	}
	
	// 加载基本政策页面
	public function co_travel_policy_text() {
		
		// 加载布局文件
		layout ( "home" );
		
		$this->theme ( "default" )->display ( 'co_travel_policy_text' );
	}
	
	// 加载基本政策页面
	public function co_travel_policy_set2() {
		// C('LAYOUT_ON',TRUE);
		// 加载布局文件
		layout ( "home" );
		
		$this->theme ( "default" )->display ( 'co_travel_policy_set2' );
	}
	
	// 加载新建申请的页面
	public function tr_request() {
		
		// C('LAYOUT_ON',TRUE);
		// 加载布局文件
		$userid = LI('userId');
		$user= M('employee');
		$userinfo =  $user-> where('u_id='.$userid)->select();
		$travel_policy= M('travel_policy');
		$com_travel_policy = $travel_policy->where('co_id='.$userinfo[0]['co_id'])->select();
		layout ( "home" );
		$this->assign ( "name", $userinfo[0]['name']);
		$this->assign("userinfo",$userinfo);
		$this->assign("travel",$com_travel_policy[0]);
		$this->assign ( "posturl", U ( 'Approval/add' ) );
		$this->theme ( "default" )->display ( "tr_request" );
	}
	
	// 查询申请单的相关信息
	public function search_apply() {
		// 获取模糊查询的内容
		$info = $_POST ['info'];
		
		$tr_approval = M ( "" );
		// 获取登录人的id
		$id = LI ( "userId" );
		// 实例化73go_travel_request 表
		$Request = M ( "" );
		// 查询出相关的参数
		$strSql = "select r.tr_no as apply_num ,r.id,r.back_date,r.time,
							r.leave_date,r.tr_name,r.cost,r.description,e.id,e.name,a.tr_id,a.status,a.appv_id,a.tr_id
								from 73go_travel_request as r LEFT JOIN 73go_tr_approval as a ON r.id=a.tr_id
									LEFT JOIN 73go_employee as e ON e.id=a.appv_id  
										where r.u_id =$id  and a.status !='4' and  ( r.tr_name like '%" . $info . "%'  or 
										  r.tr_no like '%" . $info . "%') order by a.id desc ";
		
		$data = $Request->query ( $strSql );
		
		foreach ( $data as $key => $value ) {
			
			$sql = "select b.name from 73go_tr_approval AS a
									LEFT JOIN 	73go_employee as b ON  a.appv_id = b.id
									WHERE tr_id=".$value['tr_id']." order by a.status desc
						";
						
						$approver_user = $tr_approval->query($sql);
						foreach($approver_user as $key1=>$name){
								if($key1 == 0){
									$a_name = $name['name'];
								}else{
									$a_name.= "->".$name['name'];
								}
						
						};
						$data[$key]['aname']=$a_name;
						
				
			
			};

				
				
				//$user=$Request->query($strSql);	

				$this->ajaxReturn($data,'json');

			}

		
		//加载我的出差申请页面
		public  function  my_tr_request_list(){
			//加载布局文件
			layout("home");
			$id = LI('userId');
			//$num = C('Apply_number');
			//实力话出差申请表
			$Request=M("travel_request");
			$tr_approval=M("tr_approval");
			$employee = M('employee');
			
			//----------------进行分页的处理-------------------
			//查询出满足要求的分页条数
			$count=$Request->where('u_id='.$id)->count();
			//$count=count($data);
			// 实例化分页类 传入总记录数和每页显示的记录数(4)
			$Page  = new \Think\Page($count,4);

			$show       = $Page->show();// 分页显示输出
			$this->assign('Page',$show);// 赋值分页输出
			//查询出相关的数据
			$strSql="select r.tr_no as apply_num ,r.back_date,r.time,
					r.leave_date,r.tr_name,r.cost,r.description,e.id,e.name,a.tr_id,a.status,a.appv_id,a.tr_id
						from 73go_travel_request as r LEFT JOIN 73go_tr_approval as a ON r.id=a.tr_id
							LEFT JOIN 73go_employee as e ON e.id=a.appv_id  
								where r.u_id =" . $id . " and a.status !='4'  order by a.id desc  limit $Page->firstRow , $Page->listRows";
		
		$data = $Request->query ( $strSql );
		// $count=count($data);
		// print_r($data);exit;
		foreach ( $data as $key => $value ) {
			
			$sql = "select b.name from 73go_tr_approval AS a
									LEFT JOIN 	73go_employee as b ON  a.appv_id = b.id
									WHERE tr_id=" . $value ['tr_id'] . " order by a.status desc
						";
			
			$approver_user = $tr_approval->query ( $sql );
			foreach ( $approver_user as $key1 => $name ) {
				if ($key1 == 0) {
					$a_name = $name ['name'];
				} else {
					$a_name .= "->" . $name ['name'];
				}
			}
			;
			$data [$key] ['aname'] = $a_name;
		}
		;
		// print_r($data);
		$this->assign ( "result", $data );
		
		$this->theme ( "default" )->display ( "my_tr_request_list" );
	}
	
	// 进行申请状态的查询
	// 0----->待审批 1----->审批未通过 3----->审批通过 4----->审批转移
	public function apply_status() {
		// 加载布局
		layout ( "home" );
		$status = $_GET ['status'];
		// print_r($status);exit;
		$id = LI ( 'userId' );
		
		// 实力话出差申请表
		$Request = M ( "" );
		$tr_approval = M ( "" );
		// 查询出相关的数据
		$strSql = "select r.tr_no as apply_num ,r.back_date,r.time,r.id,
							r.leave_date,r.tr_name,r.cost,r.description,e.id,e.name,a.tr_id,a.status,a.appv_id,a.tr_id
								from 73go_travel_request as r LEFT JOIN 73go_tr_approval as a ON r.id=a.tr_id
									LEFT JOIN 73go_employee as e ON e.id=a.appv_id  
										where r.u_id =$id and ( a.status=$status) order by a.id desc ";
		// echo $strSql;exit;
		
		$data = $Request->query ( $strSql );
		
		foreach ( $data as $key => $value ) {
			
			$sql = "select b.name from 73go_tr_approval AS a
									LEFT JOIN 	73go_employee as b ON  a.appv_id = b.id
									WHERE tr_id=" . $value ['tr_id'] . " order by a.status desc
						";
			
			$approver_user = $tr_approval->query ( $sql );
			foreach ( $approver_user as $key1 => $name ) {
				if ($key1 == 0) {
					$a_name = $name ['name'];
				} else {
					$a_name .= "->" . $name ['name'];
				}
			};
			$data [$key] ['aname'] = $a_name;
		}
		;
		
		$this->assign ( "result", $data );
		$this->theme ( "default" )->display ( "my_tr_request_list" );
	}
	
	// 新建申请
	public function find_user() {
		$info = $_POST ['info'];
		$id = LI ( 'userId' );
		$employee = M ( 'employee' );
		$co_id = $employee->where ( 'u_id = ' . $id )->find ();
		$map ['co_id'] = $co_id ['co_id'];
		$map ['name'] = array (
				'like',
				"%" . $info . "%" 
		);
		// $map['emp_code'] = array('like',"%".$info."%");
		$user = $employee->where ( $map )->select ();
		// print_r($user);
		$this->ajaxReturn ( $user, 'json' );
	}
	
	// 加载待我审批页面
	public function my_approval_list() {
		layout ( "home" );
		// 调用Logic/ApprovalLogic.class.php
		$approvalD = D ( "Home/Approval", 'Logic' );
		$result = $approvalD->approval_list ( 0 );
		$count = count ( $result );
		// 实例化分页类 传入总记录数和每页显示的记录数(4)
		$Page = new \Think\Page ( $count, 4 );
		
		$show = $Page->show (); // 分页显示输出
		
		$result = $approvalD->approval_list ( $Page );
		
		$this->assign ( 'Page', $show ); // 赋值分页输出
		$this->assign ( "data", $result );
		$this->theme ( "default" )->display ( "my_approval_list" );
	}
	
	// -------------------进行待我审批的搜索功能查询-------------------------
	public function search_approval() {
		// 加载布局文件
		// layout("home");
		$id = LI ( 'userId' );
		// $Muser=M("user");
		$request = M ( "travel_request" );
		$tr_approval = M ( "tr_approval" );
		$employee = M ( 'employee' );
		$info = $_POST ['info'];
		$sql = "select  b.tr_no AS apply_num,a.id ,a.appv_id,a.tr_id,a.status,b.co_id,b.tr_name,b.time,b.description,b.leave_date,b.back_date,b.cost,c.name as appro_name  from  73go_tr_approval as a
						LEFT JOIN 73go_travel_request as b ON a.tr_id = b.id
							LEFT JOIN 73go_employee as c ON a.appv_id = c.id
						         where  c.u_id = $id and  ( b.tr_name like '%" . $info . "%'  or 
										  b.tr_no like '%" . $info . "%') order by a.id desc ";
		
		$data = $request->query ( $sql );
		// $des_detail=$data[0]['description'];
		// $data[0]['description']=str_replace("<br>",".",$des_detail);
		
		foreach ( $data as $key => $value ) {
			
			$sql = "select b.name from 73go_tr_approval AS a
									LEFT JOIN 	73go_employee as b ON  a.appv_id = b.id
									WHERE tr_id=" . $value ['tr_id'] . " order by a.status desc";
			
			$approver_user = $tr_approval->query ( $sql );
			foreach ( $approver_user as $key1 => $name ) {
				if ($key1 == 0) {
					$a_name = $name ['name'];
				} else {
					$a_name .= "->" . $name ['name'];
				}
			}
			;
			$data [$key] ['aname'] = $a_name;
		}
		;
		
		$this->ajaxReturn ( $data, 'json' );
	}
	
	// 新建申请
	public function add() {
		
		// print_r($_POST);exit;
		$id = LI ( 'userId' ); // 获取用户id
		$name = LI ( 'displayName' );
		$travel_request = M ( 'travel_request' );
		$tr_approval = M ( 'tr_approval' );
		$employee = M ( 'employee' );
		$co = $employee->where ( 'u_id =' . $id )->find ();
		$map ['u_id'] = $id;
		$map ['co_id'] = $co ['co_id'];
		
		$map ['tr_name'] = $_POST ['tr_user'];
		$map ['leave_date'] = $_POST ['leave_date'];
		$map ['back_date'] = $_POST ['back_date'];
		$map ['cost'] = $_POST ['cost'];
		$map ['description'] = str_replace ( "\r\n", "<br>", $_POST ['description'] );
		$map ['time'] = date ( 'Y-m-d H:i:s', time () );
		
		//郭攀 3.3 
		//如果差旅政策配置了审批流程，给审批人发送短信
		//调用logic 里面的方法
		$res=D('Home/JudgeName','Logic');
		$solution=$res->UserName();	
		$travel_policy = M('travel_policy');
		$com_travel_policy = $travel_policy->where('co_id='.$solution['co_id'])->find();
		//根据审批人的id,查询出审批人的邮箱 和电话
		$approval_info=$employee->where("id=".$_POST['approval_id'])->find();

		//有审批流程的话，进行发送短信的操作；
		if($com_travel_policy['without_app']==0){

			//申请通知预定是通过主网站预定，还是TMC旗舰店网站判断
			if(getHostedTmcInfo('name')){
				$show_name=	getHostedTmcInfo('name');
			}else{
				$show_name="轻松行科技";
			}

			$sender_1 = new UnifyMessageSender ();
			$types = array(1,2);
			$targets = array (	1 => $approval_info['phone'],2 =>$approval_info['email']);
			$title =  '企业员工出差申请通知审批人';
			$text =  $approval_info['name'].'：'.'您提交的'.$map ['leave_date'].'至'.$map ['back_date'].'出差，事由：'.$map ['description'].'。请登录审批。('.$show_name.")";
			$html =  $approval_info['name'].'：<br>
			'.$solution['name'].'您提交的'.$map ['leave_date'].'至'.$map ['back_date'].'出差。<br>
			出差事由：'.$map ['description'].'请登录系统审批。<br>
			'.$show_name.'<br>
			'.$map ['time'].'<br>
			<此为系统发送邮件，请勿回复>';
			$um_1 = UnifyMessage::NewUnionMessage ( $types,$targets,$title,$text, $html ); 
			$sender_1->sendUMessage ($um_1);
			
		}

			// 发送邮件通知
			//员工提出出差申请后向公司发送邮件

			$Company=M("company");
			$co_info=$Company->where("id=".$co['co_id'])->find();
			$sender_3= new UnifyMessageSender ();
			$types=array(1);
			$targets = array (	1 => $co_info['contact_phone']);
			$title =  '企业员工出差通知';
			$text = $co_info['name'].'：'.'员工'.$co['name'].'提交的'.$map ['leave_date'].'至'.$map ['back_date'].'出差，事由：'.$map ['description'].'。('.$show_name.")";
			$html =  $co_info['name'].'：<br>
					员工.'.$co['name'].'.提交的'.$map ['leave_date'].'至'.$map ['back_date'].'出差。<br>
					出差事由：'.$map ['description'].'
					<br>
					'.$map ['time'].'<br>
					<此为系统发送邮件，请勿回复>';
			$um_3 = UnifyMessage::NewUnionMessage ( $types,$targets,$title,$text, $html );
			$sender_3->sendUMessage ($um_3);

			//给提交人发送短信通知
			//企业员工出差申请提交成功通知
			$userM = M('user');
			$user = $userM->where('id='.$id)->find();
			$wx_openid=$user['wx_openid'];
			$sender = new UnifyMessageSender ();
			$types =  strlen($wx_openid) != 28 ? array(1,2) : array(1,2,3);
			$targets = strlen($wx_openid) != 28 ? array (1 => $solution['phone'], 2 =>$solution['email']) : array (1 => $solution['phone'], 2 =>$solution['email'], 3 => $wx_openid);
			$title =  '企业员工出差申请提交成功通知';
			$text =  $solution['name'].'：'.'您提交的'.$map ['leave_date'].'至'.$map ['back_date'].'出差，事由：'.$map ['description'].'申请提交成功。请耐心等待审批。';
			$html = '尊敬的'.$solution['name'].'：<br>
			'.$solution['name'].'您提交的'.$map ['leave_date'].'至'.$map ['back_date'].'出差。<br>
			出差事由：'.$map ['description'].'申请提交成功。请耐心等待审批人审批。<br>
			'.$map ['time'].'<br>
			<此为系统发送邮件，请勿回复>';
			$um = UnifyMessage::NewUnionMessage ( $types,$targets,$title,$text, $html ); 
			$sender->sendUMessage ($um);
		

		//给知会人发送短信和邮件
		foreach ( $_POST ['not_name'] as $key => $value ) {
			$sender_2 = new UnifyMessageSender ();
			$notifier .= $value . "," . $_POST ['not_phone'] [$key] . "," . $_POST ['not_email'] [$key] . ";";
			// 尝试发送短信
			$types = array(1,2);
			$targets = array (	1 => $_POST ['not_phone'] [$key],2 =>  $_POST ['not_email'] [$key]);
			$title =  '出差申请-'.$name.'申请'.$map ['leave_date'].'至'.$map ['back_date'].'出差';
			$text =  $value.'：'.$name.'申请'.$map ['leave_date'].'至'.$map ['back_date'].'出差，事由：'.$map ['description'].'。请知悉。';
			$html = '尊敬的'.$value.'：<br>
			'.$name.'申请'.$map ['leave_date'].'至'.$map ['back_date'].'出差。<br>
			出差事由：'.$map ['description'].'请知悉。<br>
			'.$map ['time'].'<br>
			<此为系统发送邮件，请勿回复>';
			$um_2 = UnifyMessage::NewUnionMessage ( $types,$targets,$title,$text, $html ); 
			$sender_2->sendUMessage ($um_2);
		}
		
		$map ['notifier'] = substr ( $notifier, 0, - 1 );
		
		

		//进行单号的添加 按照---"TR".日期(YMD).散列(6) 进行生成
		$map['tr_no']=VNumGen('travel_req');
		$tr_id = $travel_request->add($map);//插入新建申请
		if($tr_id){
		$data['appv_id'] = $_POST['approval_id'];
		$data['tr_id'] = $tr_id;
		$result = $tr_approval->add($data);
			if($result){
				$this->redirect('Approval/my_tr_request_list');
				//$this->success('新建申请成功', U('Approval/my_tr_request_list'));
			}else{
				$this->redirect('Approval/tr_request');
				//$this->error('新建申请失败', U('Approval/tr_request'));

			}

		
		}else{
			$this->redirect('Approval/tr_request');
			//$this->error('新建申请失败', U('Approval/tr_request'));

		}
	}
	// 审核通过
	public function pass() {
		$id = $_POST ['id'];
		$employee=M("employee");
		$Mtr_approval = M ( 'tr_approval' );
		$travel_request=M("travel_request");
		$map ['status'] = 3;
		$result = $Mtr_approval->where ( 'id = ' . $id )->save ( $map );
		if ($result) {
			
				
			//郭攀 3.3 
			//如果审核通过进行短信和短信的发送
			//查询出 出差申请的相关信息
			$datt=$travel_request->where("id=".$id)->find();
			//查询出提交人的相关信息；
			$solution_emp=$employee->where("u_id=".$datt['u_id'])->find();			
			$sender = new UnifyMessageSender ();			
			$types = array(1,2);
			$targets = array (	1 => $solution_emp['phone'],2 => $solution_emp['email']);
			$title =  '企业员工出差申请通过，通知员工';
			$text =  $solution_emp['name'].'：'.'您提交的'.$datt['leave_date'].'至'.$datt['back_date'].'出差申请已经通过审批，您可以预定机票和酒店了 ！';
			$html = '尊敬的'.$solution_emp['name'].'：<br>
			'.'您提交的'.$datt['leave_date'].'至'.$datt['back_date'].'出差申请已经通过审批，您可以预定机票和酒店了 ！<br>			
			<此为系统发送邮件，请勿回复>';
			$um = UnifyMessage::NewUnionMessage ( $types,$targets,$title,$text, $html ); 
			$sender->sendUMessage ($um);	
			
			$data [] = 0;
			$this->ajaxReturn ( $data, 'json' );
		} else {
			$data [] = 1;
			$this->ajaxReturn ( $data, 'json' );
		}
	}
	
	// 审核不通过
	public function refuse() {
		$id = $_POST ['id'];
		$employee=M("employee");
		$Mtr_approval = M ( 'tr_approval' );
		$travel_request=M("travel_request");
		$map ['status'] = 1;
		$result = $Mtr_approval->where ( 'id = ' . $id )->save ( $map );
		if ($result) {

			//郭攀 3.3 
			//审核不通过是进行邮件和短信的发送
			//查询出 出差申请的相关信息
			$datt=$travel_request->where("id=".$id)->find();
			//查询出提交人的相关信息；
			$solution_emp=$employee->where("u_id=".$datt['u_id'])->find();
			$sender = new UnifyMessageSender ();			
			$types = array(1,2);
			$targets = array (	1 => $solution_emp['phone'],2 =>  $solution_emp['email']);
			$title =  '企业员工出差申请被拒绝，通知员工';
			$text =  $solution_emp['name'].'：'.'您提交的'.$datt['leave_date'].'至'.$datt['back_date'].'审批未通过，您可以更改提前申请或与审批人沟通 。';
			$html = $solution_emp['name'].'：<br>
			'.'您提交的'.$datt['leave_date'].'至'.$datt['back_date'].'审批未通过，您可以更改提前申请或与审批人沟通 。<br>			
			<此为系统发送邮件，请勿回复>';
			$um = UnifyMessage::NewUnionMessage ( $types,$targets,$title,$text, $html ); 
			$sender->sendUMessage ($um);
			
			$data [] = 0;
			$this->ajaxReturn ( $data, 'json' );
		} else {
			$data [] = 1;
			$this->ajaxReturn ( $data, 'json' );
		}
	}
	// 审批转移
	public function turn() {
		// print_r($_POST);exit;
		$tr_approval = M ( 'tr_approval' );
		$employee = M ( 'employee' );
		
		if($_POST ['id']==LI('empId')){
			$data [] = 1;
			$this->ajaxReturn ( $data, 'json' );
		}
		$id = $_POST ['a_id'];
		$map ['status'] = '4';
		$result = $tr_approval->where ( 'id = ' . $id )->save ( $map );
		$data ['tr_id'] = $_POST ['tr_id']; // 获取审批id
		$data ['appv_id'] = $_POST ['id']; // 获取新的审批人id
		
		$result = $tr_approval->add ( $data );
		
		if ($result) {
			$data [] = 0;
			$this->ajaxReturn ( $data, 'json' );
		} else {
			$data [] = 2;
			$this->ajaxReturn ( $data, 'json' );
		}
	}
	// 进行新建申请的修改页面显现；
	public function mod_tr_request() {
		// 加载布局文件
		layout ( "home" );
		$id = LI ( "userId" ); // 获取用户的id
		$tr_id = $_GET ['tr_id'];
		$Mrequest = M ( "travel_request" );
		// 进行关联查询出改新建申请的相关信息
		$strSql = "select r.id,r.back_date,r.time,r.leave_date,r.tr_name,r.cost,r.description, 
					a.tr_id,a.appv_id,e.u_id,e.name,e.phone,e.email,r.notifier from  
							73go_travel_request as	r LEFT JOIN 73go_tr_approval as a ON  r.id=$tr_id  
										LEFT JOIN 73go_employee as e ON e.id=a.appv_id 
												where r.u_id =$id  and r.id=a.tr_id  order by a.id desc";
		$data = $Mrequest->query ( $strSql );
		$description = str_replace ( "<br>", "\r\n", $data [0] ['description'] );
		// 知会人
		$notifier_tmp = explode ( ";", $data [0] ['notifier'] );
		for($i = 0; $i < count ( $notifier_tmp ); $i ++) {
			$arr = explode ( ",", $notifier_tmp [$i] );
			$notifiers [$i] ['name'] = $arr [0];
			$notifiers [$i] ['phone'] = $arr [1];
			$notifiers [$i] ['email'] = $arr [2];
		}
		
		$this->assign ( "posturl", U ( 'Approval/mod_req', array (
				'tr_id' => $tr_id 
		) ) );
		$this->assign ( "notifiers", $notifiers );
		$this->assign ( "description", $description );
		$this->assign ( "val", $data [0] );
		$this->theme ( "default" )->display ( "tr_request" );
	}
	
	// 修改新建申请的相关信息
	public function mod_req() {
		layout ( "home" );
		$t_id = $_GET ['tr_id'];
		// echo $t_id; exit;
		$id = LI ( 'userId' ); // 获取用户id
		$name = LI ( 'displayName' );
		$travel_request = M ( 'travel_request' );
		$tr_approval = M ( 'tr_approval' );
		$map ['u_id'] = $id;
		// $map['co_id'] = $co['co_id'];
		$map ['tr_name'] = $_POST ['tr_user'];
		$map ['leave_date'] = $_POST ['leave_date'];
		$map ['back_date'] = $_POST ['back_date'];
		$map ['cost'] = $_POST ['cost'];
		$map ['description'] = str_replace ( "\r\n", "<br>", $_POST ['description'] );
		$map ['time'] = date ( 'Y-m-d H:i:s', time () );
		
		//发送短信和邮件
		$sender = new UnifyMessageSender ();
		foreach ( $_POST ['not_name'] as $key => $value ) {
			$notifier .= $value . "," . $_POST ['not_phone'] [$key] . "," . $_POST ['not_email'] [$key] . ";";
			// 尝试发送短信
			$types = array(1,2);
			$targets = array (	1 => $_POST ['not_phone'] [$key],2 =>  $_POST ['not_email'] [$key]);
			$title =  '出差申请-'.$name.'申请'.$map ['leave_date'].'至'.$map ['back_date'].'出差';
			$text =  $value.'：'.$name.'申请'.$map ['leave_date'].'至'.$map ['back_date'].'出差，事由：'.$map ['description'].'。请知悉。';
			$html = '尊敬的'.$value.'：<br>
			'.$name.'申请'.$map ['leave_date'].'至'.$map ['back_date'].'出差。<br>
			出差事由：'.$map ['description'].'请知悉。<br>
			'.$map ['time'].'<br>
			<此为系统发送邮件，请勿回复>';
			$um = UnifyMessage::NewUnionMessage ( $types,$targets,$title,$text, $html ); 
			$sender->sendUMessage ($um);
		}
		
		$map ['notifier'] = substr ( $notifier, 0, - 1 );
		
		$tr_id = $travel_request->where ( "id=" . $t_id )->save ( $map ); // 更新新建申请
		if ($tr_id) {
			$datt ['appv_id'] = $_POST ['approval_id'];
			// 审批的人可以没有修改，如果没有修改进入$this->error("相关信息已经修改，审批人没有进行修改")里面;
			
			// 修改审批人的相关信息
			$res = $tr_approval->where ( 'tr_id=' . $t_id )->save ( $datt );
			if ($res) {
    				$this->redirect('Approval/my_tr_request_list');
				//$this->success ( '相关信息已经修改成功', U ( 'Approval/my_tr_request_list' ) );
			} else {
				$this->redirect('Approval/my_tr_request_list');
				//$this->error ( '相关信息已经修改，审批人没有进行修改', U ( 'Approval/my_tr_request_list' ) );
			}
		} else {
			$this->redirect('Approval/mod_tr_request');
			//$this->error ( '申请修改失败', U ( 'Approval/mod_tr_request' ) );
		}
	}
	
	// 申请记录的删除处理
	public function del_request() {
		$t_id = $_GET ['tr_id'];
		
		$Mrequest = M ( "travel_request" );
		$Mapproval = M ( "tr_approval" );
		

		$data=$Mapproval->where("tr_id=".$t_id)->delete();
			if($data){
				$datt=$Mrequest->where("id=".$t_id)->delete();
				if($datt){
					$this->redirect('Approval/my_tr_request_list');
					//$this->success('删除成功', U('Approval/my_tr_request_list'));
				}else{
					$this->redirect('Approval/my_tr_request_list');
					//$this->error('删除失败', U('Approval/my_tr_request_list'));
				}
			
			}else{
				$this->redirect('Approval/my_tr_request_list');
				//$this->error('删除失败', U('Approval/my_tr_request_list'));

			}

	}
}
