<?php

/***
 *
 * 修改记录
 *
 * 2015/1/24		李浩源，统一整改ajax返回需要使用的ajaxReturn
 *
 */



namespace Home\Controller;
use Agent\Logic\TmcempUserLogic;

use Agent\Logic\TmcUserLogic;
use Home\Logic\UserLogic;
use System\LoginInfo;
use Think\Controller;

class TicketServerController extends Controller {



	//发送验证码
	public function verify_code()
	{
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$phone= $_POST['phone'];

		$newpassword = $this->generate_password(6);//生成六位随机验证码
		//$map2['password'] = md5($newpassword);
		//if ($type == 1) {//判断帐号的类型 1、普通用户  2、企业 3、tmc 4、op
		// $emp = M('employee');
		// $emp_phone = $emp->where($map1)->getField('phone');
		// if ($emp_phone == $phone) {
		//     $user->where("id=" . $id)->save($map2);
		//发短信
		$send = D("Home/SendMessage", "Logic");
		$case = "PhoneGetVerifyCode";
		$datt['user_phone'] = $phone;
		$datt['newpassword'] = $newpassword;
		$send->SendDetails($case, $datt);
		$this->ajaxReturn(1,'json');
	}
	//邮件收到验证码
	public function verify_code_email()
	{
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$data= $_POST['data'];
		$user = json_decode($data,true);
		$email = $user['email'];
		$newpassword = $this->generate_password(6);//生成六位随机验证码
		//$map2['password'] = md5($newpassword);
		//if ($type == 1) {//判断帐号的类型 1、普通用户  2、企业 3、tmc 4、op
		// $emp = M('employee');
		// $emp_phone = $emp->where($map1)->getField('phone');
		// if ($emp_phone == $phone) {
		//     $user->where("id=" . $id)->save($map2);
		//发短信
		$send = D("Home/SendMessage", "Logic");
		$case = "EmailGetNewPassword";
		$datt['user_email'] = $email;
		$datt['newpassword'] = $newpassword;
		$send->SendDetails($case, $datt);
		$this->ajaxReturn(1,'json');
	}
	//查询到账号，发送密码，返回1
	//查询不到账号，返回0；
	public function forget_password()
	{
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$data= $_POST['data'];
		$user = json_decode($data,true);
		$phone = $user['phone'];
		$newpassword = $this->generate_password(6);//生成六位随机密码
		//发短信
		$users = M('user');
		$map['account'] = $user['account'];
		$user = $users->where($map)->select();
		if($user && ($user['phone'] == $phone)) {
			$send = D("Home/SendMessage", "Logic");
			$case = "PhoneGetNewPassword";
			$datt['user_phone'] = $phone;
			$datt['newpassword'] = $newpassword;
			$send->SendDetails($case, $datt);
			$this->ajaxReturn(1, 'json');
		}else {
			$this->ajaxReturn(0, 'json');
		}
	}
	/*生成随机密码类*/
	function generate_password($length = 6)
	{

		// 密码字符集，可任意添加你需要的字符
		$chars = '0123456789';

		$password = '';
		for ($i = 0; $i < $length; $i++) {
			// 这里提供两种字符获取方式
			// 第一种是使用 substr 截取$chars中的任意一位字符；
			// 第二种是取字符数组 $chars 的任意元素
			// $password .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
			$password .= $chars[mt_rand(0, strlen($chars) - 1)];
		}

		return $password;
	}
	//代理商的登录
	//查看是否具有该用户 
	public function check_login(){
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$map['account']=$_POST['account'];
		$map['password']= md5($_POST['password']);
		$map['user_type']=array('exp','in(3,4)');
		$user = M('user');
		$result = $user->where($map)->find();//查询验证用户
		//保存企业信息
		if(!$result){
			$info = '帐号 ' . $map['account'] . ' 登录73goTMC网页失败';
			LOGS($type=1, $info, $map['account']);
			$this->ajaxReturn(0);
		}
		if ($result['status']==99) {
			$info = '帐号 ' . $map['account'] . ' 被禁用登录73goTMC网页失败';
			LOGS($type=1, $info, $map['account']);
			$this->ajaxReturn(-1);
		}
		$m = new UserLogic();
		$m->userLogins('', $result);
		session('user_type',$result['user_type']);
		if($result['user_type']== 3){
			$info = '帐号 ' . $map['account'] . ' 登录73goTMC网页成功';
			LOGS($type=1, $info);
			$this->ajaxReturn(1);       //TMC公司登陆
		}
		else{
			$info = '帐号 ' . $map['account'] . ' 登录73goOP网页成功';
			LOGS($type=1, $info);
			$this->ajaxReturn(2);       //TMC员工登陆
		}
	}
	//代理商的注册信息
	
	//进行校验是否存在该代理商用户
	public function check_tmc(){
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$map['account'] = $_POST['account'];
		$user = M('user');
		$result= $user->where($map)->select();
		if($result){
			$this->ajaxReturn(1);
		}else{
			$this->ajaxReturn(0);
		}

	}
		
	public function add_tmc(){
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$map['account']=$_POST['account'];
		$map['password'] =md5($_POST['password']);
		$map['name'] = $_POST['name'];
		$map['user_type']=3;
		//$map['status']=0;
		$m_user=M("user");
		$newUserId=$m_user->data($map)->add();//返回当前操作的id；
		if($newUserId){
			$data['u_id']=$newUserId;//获取刚才注册的公司的id；
			//获取5位不同的散列数作为TMC企业的编号
			$tmcCode = VNumGen('tmc_code');//公司的编号；
			$data['tmc_id'] = $tmcCode;
			$m_tmc = M("tmc");
			$newTmcId = $m_tmc->add($data);
			if($newTmcId){
				$this->ajaxReturn(1);
			}else{
				$this->ajaxReturn(0);
			}
		}else {
			$this->ajaxReturn(0);
		}
	}
	/*
	 * 用于展示tmc的信息：提供tmc信息的初始修改页面
	 */
	public function show_tmcinfo()
	{
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$account = I('SESSION.account');
		$m_user = M('user');
		$user = $m_user->where('account='.$account)->select();
		$m_tmc = M("tmc");
		$u_id = $user['0']['id'];
		//$id=173;
		$result = $m_tmc->where("u_id=" . $u_id)->find();
		$this->ajaxreturn($result,'json');
	}


	//进行config_tmcinfo_basicinfo 的处理
	public function update_tmc()
	{
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$account = Li("account");
		$m_user = M('user');
		$user = $m_user->where('account='.$account)->select();
		$m_tmc = M("tmc");
		$u_id = $user[0]['id'];
		//将post传来的值进行更新
		$map = $_POST;
		$res = $m_tmc->where("u_id=" . $u_id)->save($map);

		if ($res) {
			$this->ajaxreturn(1);
		} else {
			$this->ajaxreturn(0);
		}
	}




	//用户修改密码的操作

	public function mod_password()
	{
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$account = Li("account");
		$user = M('user');
		$map['password'] = md5($_POST['newpassword']);
		$res = $user->where("account=" . $account)->save($map);
		if ($res) {
			$this->ajaxreturn(1);//, U('Index/config_myinfo_acount'));
		} else {
			$this->ajaxreturn(0);//, U('Index/config_myinfo_acount'));
		}


	}

	//验证原有的密码

	public function check_password()
	{
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$map['account'] = Li("account");
		$map['password'] = md5($_POST['password']);
		$user = M('user');
		$result = $user->where($map)->select();
		//print_r($result);
		if ($result) {
			$this->ajaxreturn(1);
		} else {
			$this->ajaxreturn(0);
		}


	}
	/**
	 * upload函数用于上传文件 可检测文件的大小和类型 ,自动判断文件的路径
	 * author: 钟明 2015-01-30
	 */
	public function upload()
	{
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$imgTypes = array('image/jpg', 'image/jpeg', 'image/png', 'image/pjpeg', 'image/gif', 'image/bmp', 'image/x-png');//允许上传的文件类型
		$imgSize = 1048576 * 2;//文件大小
		$savePath = $_SERVER['DOCUMENT_ROOT'] . '/Uploads/';// 设置附件上传目录
		$uploadName = $_FILES["uploadFile"]["name"];
		$uploadPostfix = explode('.', $uploadName);//文件后缀
		$uploadPostfix = $uploadPostfix[1];//文件后缀
		$uploadType = $_FILES['uploadFile']['type']; //文件类型
		$uploadError = $_FILES['uploadFile']['error']; //错误类型
		$uploadSize = $_FILES['uploadFile']['size']; //文件大小
		if ($uploadError > 0) { //上传系统出错时
			$result = "";
			switch ($_FILES['uploadFile']['error']) {
				case 1:
					$result = '文件大小超过服务器限制';
					break;
				case 2:
					$result = '文件太大!';
					break;
				case 3:
					$result = '文件只加载了一部分!';
					break;
				case 4:
					$result = '文件加载失败!';
					break;
			}
			$this->showError($uploadError, $result);
		}
		if (!in_array(strtolower($uploadType), $imgTypes)) {
			$this->showError(-1, "文件格式不正确!");
		}
		if ($uploadSize > $imgSize) {
			$this->showError(-1, "文件大小不能超过2M!!");
		}
		$today = date("YmdHis");
		$saveName = $today . '.' . $uploadPostfix;
		$finalName = $savePath . $saveName;
		if (is_uploaded_file($_FILES['uploadFile']['tmp_name'])) {
			if (!move_uploaded_file($_FILES['uploadFile']['tmp_name'], $finalName)) {
				$this->showError(-1, "文件保存失败!!");
			} else {
				$res = $this->updateTMCImg($saveName);
				if ($res) {
					$this->showError(1, "图片上传成功!!");
				} else {
					$this->showError(-1, "图片保存错误!!");
				};
			}
		} else {
			$this->showError(-1, "文件已经上传!!");
		}

	}

	private function showError($errorCode, $errorInfo)
	{
		$result['code'] = $errorCode;
		$result['info'] = $errorInfo;
		$this->ajaxReturn($result, "JSON");
	}

	private function updateTMCImg($licenseImage)
	{
		$id = Li("userId");
		$Tmc = M("Tmc");
		$map['u_id'] = $id;
		$map['license_image'] = $licenseImage;
		return $Tmc->where("u_id=" . $id)->save($map);
	}
	
	
	
	
	}
	







