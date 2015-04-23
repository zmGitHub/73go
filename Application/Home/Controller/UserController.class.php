<?php
namespace Home\Controller;
use Home\Logic\UserLogic;
use System\LoginInfo;
use Think\Controller;
class UserController extends Controller {

	public function check_login(){
		$userName = $_POST['loginNo'];
		$password =  md5($_POST['password']);
		$logic = new UserLogic();
		$user = $logic->findByNameAndPsw($userName, $password);
		$type=1;
		
		if(!$user){
			$info = '帐号 ' . $userName . ' 登录73go前台网页失败';
			LOGS($type, $info, $userName);
			$this->ajaxReturn(0);
		}

		if ($user['status']==99) {
			$info='帐号 '.$userName.' 被禁用登录73go前台页面失败';
			LOGS($type,$info,$userName);
			$this->ajaxReturn(-1);

		}else {
			$info='帐号'.$userName.'登录73go前台页面成功';
			session('user_type', $user['user_type']);
			$logic->userLogins('', $user);
			LOGS($type,$info);
			$this->ajaxReturn((int)$user['user_type']);
		}
	}


	
	//check if the user name has been registered   reviewer:Yu Zhuoran
	public function check_user(){
		
		$username = $_POST['username'];
		$user = M('user');
		$map['username'] = $username;
		$result= $user->where($map)->select();
		if($result){
			//echo 1;
			$this->ajaxReturn(1);
		}else{
			//echo 0;
			$this->ajaxReturn(0);
		}
		
	}
	
	public function add_user(){
	
	$map['username'] = $_POST['username'];
	$map['password'] = md5($_POST['password']);
	$map['user_type']=1;        //'user_type' should be 0  reviewer:Yu Zhuoran 
	$map['status']=0;
	$map['register_time']=date('Y-m-d H:i:s',time());
	$map['reg_id_revisable'] = 1;
	$user = M('user');
	$employee = M('employee');
	$result= $user->data($map)->add();
			if($result){
				$data['u_id'] = $result;
				LI('userName', $map['username']);
				LI('userId', $result);
				$res = $employee->data($data)->add();
				
						if($res){
								LI('empId', $res);
								echo 0;
								
						}else{
								echo 1;
								
						}
								
			}
		
	}
	
	//check if the company name has been registered   reviewer:Yu Zhuoran
	public function check_company(){
		
		$username = $_POST['username'];
		$user = M('user');
		$map['username'] = $username;
		$result= $user->where($map)->select();
		if($result){
			echo 1;
		}else{
			echo 0;
		}
	}
	
	
	/*添加公司*/
	public function add_company(){
		
		$map['register_time']=date('Y-m-d H:i:s',time());
		$map['username']=$_POST['username'];
		$map['password'] = md5($_POST['password']);
		$map['user_type']=2;    //2 means TMC, so company's status should be 1   reviewer:Yu Zhuoran 
		$map['status']=0;
		$map['reg_id_revisable'] = 1;
		$user=M("user");
		$company = M("company");
		$result=$user->data($map)->add();	
		
		//获取5位不同的散列数作为企业的编号
		$aa=VNumGen('co_code');

		if($result){
			LI('userName', $_POST['username']);
			LI('userId', $result);
			$data['u_id']=$result;
			$data['name']=$_POST['username'];
			
			$data['co_code']=$aa;

			$res=$company->data($data)->add();
			if($res){
				//添加差旅政策记录
				$travel_policy=M('travel_policy');
				$travel['co_id']=$res;
				$travelrequest=$travel_policy->data($travel)->add();
				LI('comId', $res);
				echo 0;
			}else{
				echo 1;
			}
		}	
	}
	/*修改个人用户详细信息*/
	public function mod_user_detail(){
	 
		$id = LI('userId');
		$employee = M('employee');
		$map = $_POST;
		//print_r($map);exit;
		$result = $employee->where("u_id=".$id)->save($map);
		$this->ajaxReturn($result, "JSON");
	}
	
	/*修改用户名*/
	public function mod_username(){
		
		$id = LI('userId');
		$user = M('user');
		
		//查找重复名
		$map['username'] = $_POST['username'];
		$map['id'] = array('neq',$id);
		$result = $user->where($map)->find();
		if($result){
			$this->error('修改失败,该用户名已存在！',U('Index/config_myinfo_acount'));
		}
		$map['reg_id_revisable'] = 0;
		$map['username'] = $_POST['username'];
		
		$res = $user->where('id = '.$id)->save($map);
		if($res){
			$this->redirect('Index/config_myinfo_acount');
			//$this->success('修改成功');//, U('Index/config_myinfo_acount'));
		}else{
			$this->redirect('Index/config_myinfo_acount');
			//$this->error('修改失败');//, U('Index/config_myinfo_acount'));
		}
	
	
	}
	/* 修改用户密码*/
	public function mod_password(){
		
		$id = LI('userId');
		$user = M('user');
		$map['password'] =md5($_POST['newpassword']);
		$res=$user->where("id=".$id)->save($map);
		if($res){
			//$this->success('修改成功');, U('Index/config_myinfo_acount'));
			echo $res;
		}else{
			//$this->success('修改失败');, U('Index/config_myinfo_acount'));
			echo $res;
		}
		
	
	
		}
	/*检车企业合法性*/
		
	public function check_company1(){
		
		$map['co_code'] = $_POST['co_code'];//获取的公司编码
		$company = M('company');
		$branch = M('branch');
		$res = $company->where($map)->select();
		if($res){
			$res[0]['parts'] = $branch->where("co_id=".$res[0]['id'])->select();
			$this->ajaxReturn($res[0],"JSON");
		
		}else{
			$this->ajaxReturn("","JSON");

		}
		
	
	
	}	
		/* 修改用户公司信息*/
	public function mod_company(){
		//print_r($_POST);
		$status = $_POST['status'];
		$id = LI('userId');
		$employee = M('employee');
		$company = M('company');
		$co_request = M('join_co_request');
		$map['co_code'] = $_POST['co_code'];
		if($status==0){
			
			$co_id = $company->where($map)->select();
			$data['status'] = "2";
			$employee->where('u_id = '.$id)->save($data);
			$data1['co_id'] = $co_id[0]['id'];
			$data1['u_id'] = $id;
			
			$data1['req_time']=date('Y-m-d H:i:s',time());
			
			$data1['status'] ='0';
			$res1 = $co_request->data($data1)->add();
			if($res1){
			$this->ajaxReturn(1);
				}else{
			$this->ajaxReturn(0);
			}
	
		}else{
			$date['br_id'] = $_POST['br_id'];
			$date['emp_code'] = $_POST['emp_code'];
			$res1 = $employee->where('u_id = '.$id)->save($date);
				if($res1){
					$this->ajaxReturn(1);
				}else{
					$this->ajaxReturn(0);
				}
		}

	}

	public function checkBaseInfo(){
		$employee = new \Home\Model\EmployeeMessageModel();
		$employeeMessage = $employee->employeeMessage();
		$this->ajaxReturn($employeeMessage, "JSON");
	}

	/*修改企业信息*/
	public function mod_comapny_info(){
		//print_r($_POST);exit;
		$id = LI('userId');
		$company = M('company');
		$map['name'] = $_POST['name'];
		$map['industry'] = $_POST['industry'];
		$map['scale'] = $_POST['scale'];
		$map['website'] = $_POST['website'];
		$map['province'] = $_POST['province'];
		$map['city'] = $_POST['city'];
		$map['short_name'] = $_POST['short_name'];
		$map['address'] = $_POST['address'];
		$map['contact_name'] = $_POST['contact_name'];
		$map['contact_phone'] = $_POST['contact_phone'];
		$map['contact_email'] = $_POST['contact_email'];
		$result = $company->where('u_id = '.$id)->save($map);
		if($result){
			$this->redirect('Index/config_coinfo_basicinfo');
			//$this->success('修改成功', U('Index/config_coinfo_basicinfo'));			
				}else{
			$this->redirect('Index/config_coinfo_basicinfo');		
			//$this->error('修改失败', U('Index/config_coinfo_basicinfo'));	
		}

	}
	/*检查用户密码*/
	public function check_password(){
	
		$map['id']= LI('userId');
		$map['password'] = md5($_POST['password']);
		$user = M('user');
		$result = $user->where($map)->select();
		//print_r($result);
		if($result){
		   echo 0;
		}else{
			echo 1;
		}

	}

	/*通过邮箱获取新密码*/
	public function find_pwd_email(){
		$username = $_POST['username'];//获取输入的帐号及邮箱
		$email = $_POST['email'];
		$user = M('user');
		$map['username'] = $username;
		$user1 = $user->where($map)->select();
		$type = $user1[0]['user_type'];//通过帐号查询，帐号的类型和id
		$id = $user1[0]['id'];
		$map1['u_id'] = $id;
		$newpassword=$this->generate_password(6);//生成随机密码
		$map2['password'] =md5($newpassword);
		if($type==1){//判断帐号的类型 1、普通用户  2、企业 3、tmc 4、op
			$emp = M('employee');
			$emp_email=$emp->where($map1)->getField('email');
			if($emp_email==$email){
				$user->where("id=".$id)->save($map2);
				//发邮件
				$send=D("Home/SendMessage","Logic");
				$case="EmailGetNewPassword";
				$datt['username'] = $username;
				$datt['newpassword'] = $newpassword;
				$datt['user_email'] =$emp_email;
				$send->SendDetails($case,$datt);
				 echo 0;
			}else{
				echo 1;
			}
		}else if($type==2){
			$company = M('company');
			$com_email=$company->where($map1)->getField('contact_email');

			if($com_email==$email){
				$user->where("id=".$id)->save($map2);
				//发邮件
				$send=D("Home/SendMessage","Logic");
				$case="EmailGetNewPassword";
				$datt['username'] = $username;
				$datt['newpassword'] = $newpassword;
				$datt['user_email'] =$com_email;
				$send->SendDetails($case,$datt);
				echo 0;
			}else{
				echo 1;
			}
		}else if($type==3){
			$tmc=M('tmc');
			$tmc_email=$tmc->where($map1)->getField('contact_email');
			if($tmc_email==$email){
				$user->where("id=".$id)->save($map2);
				//发邮件
				$send=D("Home/SendMessage","Logic");
				$case="EmailGetNewPassword";
				$datt['username'] = $username;
				$datt['newpassword'] = $newpassword;
				$datt['user_email'] =$tmc_email;
				$send->SendDetails($case,$datt);
				echo 2;
			}else{
				echo 1;
			}
		}else if($type==4){
			$tmc_emp=M('tmc_employee');
			$tmc_emp_email=$tmc_emp->where($map1)->getField('email');
			if($tmc_emp_email==$email){
				$user->where("id=".$id)->save($map2);
				//发邮件
				$send=D("Home/SendMessage","Logic");
				$case="EmailGetNewPassword";
				$datt['username'] = $username;
				$datt['newpassword'] = $newpassword;
				$datt['user_email'] =$tmc_emp_email;
				$send->SendDetails($case,$datt);
				echo 2;
			}else{
				echo 1;
			}
		}else{
			echo 1;
		}
	}
	/*通过手机短信获得新密码*/
	public function find_pwd_phone(){
		$username = $_POST['username'];//获取输入的帐号及邮箱
		$phone = $_POST['phone'];
		$user = M('user');
		$map['username'] = $username;
		$user1 = $user->where($map)->select();
		$type = $user1[0]['user_type'];//通过帐号查询，帐号的类型和id
		$id = $user1[0]['id'];
		$map1['u_id'] = $id;
		$newpassword=$this->generate_password(6);//生成六位随机密码
		$map2['password'] =md5($newpassword);
		if($type==1){//判断帐号的类型 1、普通用户  2、企业 3、tmc 4、op
			$emp = M('employee');
			$emp_phone=$emp->where($map1)->getField('phone');
			if($emp_phone==$phone){
				$user->where("id=".$id)->save($map2);
				//发短信
				$send=D("Home/SendMessage","Logic");
				$case="PhoneGetNewPassword";
				$datt['username'] = $username;
				$datt['newpassword'] = $newpassword;
				$datt['user_phone'] =$emp_phone;
				$send->SendDetails($case,$datt);
				echo 0;
			}else{
				echo 1;
			}
		}else if($type==2){
			$company = M('company');
			$com_phone=$company->where($map1)->getField('contact_phone');

			if($com_phone==$phone){
				$user->where("id=".$id)->save($map2);
				//发短信
				$send=D("Home/SendMessage","Logic");
				$case="PhoneGetNewPassword";
				$datt['username'] = $username;
				$datt['newpassword'] = $newpassword;
				$datt['user_phone'] =$com_phone;
				$send->SendDetails($case,$datt);
				echo 0;
			}else{
				echo 1;
			}
		}else if($type==3){
			$tmc=M('tmc');
			$tmc_phone=$tmc->where($map1)->getField('contact_phone');
			if($tmc_phone==$phone){
				$user->where("id=".$id)->save($map2);
				//发短信
				$send=D("Home/SendMessage","Logic");
				$case="PhoneGetNewPassword";
				$datt['username'] = $username;
				$datt['newpassword'] = $newpassword;
				$datt['user_phone'] =$tmc_phone;
				$send->SendDetails($case,$datt);
				echo 2;
			}else{
				echo 1;
			}
		}else if($type==4){
			$tmc_emp=M('tmc_employee');
			$tmc_emp_phone=$tmc_emp->where($map1)->getField('phone');
			if($tmc_emp_phone==$phone){
				$user->where("id=".$id)->save($map2);
				//发短信
				$send=D("Home/SendMessage","Logic");
				$case="PhoneGetNewPassword";
				$datt['username'] = $username;
				$datt['newpassword'] = $newpassword;
				$datt['user_phone'] =$tmc_emp_phone;
				$send->SendDetails($case,$datt);
				echo 2;
			}else{
				echo 1;
			}
		}else{
			echo 1;
		}
	}
	/*生成随机密码类*/
	function generate_password( $length = 8 ) {
		// 密码字符集，可任意添加你需要的字符
		$chars = 'abcdefghijklmnopqrstuvwxyz0123456789';

		$password = '';
		for ( $i = 0; $i < $length; $i++ )
		{
			// 这里提供两种字符获取方式
			// 第一种是使用 substr 截取$chars中的任意一位字符；
			// 第二种是取字符数组 $chars 的任意元素
			// $password .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
			$password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
		}

		return $password;
	}

}






