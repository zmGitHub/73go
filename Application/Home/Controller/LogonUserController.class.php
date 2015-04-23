<?php
namespace Home\Controller;
use Think\Controller;
/**
 * 登录用户业务层
 * 创建者：董发勇
 * 创建时间：2014-12-12上午10:14:52
 *
 *
 */
class LogonUserController extends Controller {
	
	/*****************登录用户管理*********************/
	public function co_userid_mgnt(){
		C('LAYOUT_ON',TRUE);//允许布局
		layout("home");//加载布局文件
		$cond['type']=$_REQUEST['type'];
		$request=$this->common($cond);
		$this->assign('emps',$request);		
		$this->theme('default')->display('co_userid_mgnt');
	}
	public function ajax_co_userid_mgnt(){
		$cond['search']=$_POST['search'];
		$request=$this->common($cond);
		$this->ajaxReturn($request,'json');				
	}
	//登录用户管理
	public function common($cond){
		$co_user=D('Home/LogonUser','Logic');//调用企业登录用户业务处理层
		$Page = 0;
		$request=$co_user->co_userid_mgnt($cond,$Page);//查看登录用户信息
		$count=count($request);
		$Page  = new \Think\Page($count,10);
		$show  = $Page->show();// 分页显示输出
		$request=$co_user->co_userid_mgnt($cond,$Page);//查看登录用户信息
		$this->assign('Page',$show);// 赋值分页输出
		return $request;
	}
	//重置密码
	public function reset_pwd(){
		$userM=D('Home/LogonUser','Logic');//调用企业登录用户业务处理层
		$result=$userM->reset_pwd();//查看登录用户信息
		if($result){
			$this->ajaxReturn($result);
		}else{
			$this->ajaxReturn(0);
		}
		
	}
	//创建用户
	public function create_user(){
		$userM=D('Home/LogonUser','Logic');//调用企业登录用户业务处理层
		$result=$userM->create_user();//查看登录用户信息
		if($result){
			$this->ajaxReturn($result);
		}else{
			$this->ajaxReturn(0);
		}
	}
	//修改状态
	public function update_status(){
		$empM=D('Home/LogonUser','Logic');//调用企业登录用户业务处理层
		$result=$empM->update_status();
		if($result){
			$this->redirect("LogonUser/co_userid_mgnt", null);
		}
	}
	/*****************登录用户管理*********************/
	
	/**
	 * 查看登录用户信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-12上午10:33:45
	 */
	public function showLogonUser(){
		$LogonUser=D('Home/LogonUser','Logic');//调用企业登录用户业务处理层
		$Page = 0;
		$request=$LogonUser->showLogonUserLogic($Page);//查看登录用户信息
		C('LAYOUT_ON',TRUE);//允许布局
		layout("home");//加载布局文件
		$count=count($request);
		$Page  = new \Think\Page($count,10);
		$show  = $Page->show();// 分页显示输出
		$request=$LogonUser->showLogonUserLogic($Page);//查看登录用户信息
		$this->assign('Page',$show);// 赋值分页输出
		$this->assign('emps',$request);		
		$this->theme('default')->display('co_userid_mgnt');
	}
	/**
	 * 查询有登录名的用户信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-12上午11:15:23
	 */
	public function showLogonUserYes(){
		$LogonUser=D('Home/LogonUser','Logic');//调用企业登录用户业务处理层
		$request=$LogonUser->showLogonUserYesLogic();//查看有登录名的用户信息
		$this->ajaxReturn($request);
	}
	/**
	 * 查询没有登录名的用户信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-12上午11:18:58
	 */
	public function showLogonUserNo(){
		$LogonUser=D('Home/LogonUser','Logic');//调用企业登录用户业务处理层
		$request=$LogonUser->showLogonUserNoLogic();//查看有登录名的用户信息
		$this->ajaxReturn($request);
	}
	/**
	 * 停用和启用用户信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-12下午02:16:43
	 */
	public function updateLogonUserStasus($id,$status){
		$esid=$id;
		$status=$status;
		$emp = M('employee');
		if($status==0){
		 $sql="update 73go_employee te set te.`status`=99 where te.id=".$esid;
		}else{
		 $sql="update 73go_employee te set te.`status`=0 where te.id=".$esid;
		}
		$requestsion=$emp->execute($sql);
		$this->theme('Home')->showLogonUser();
	}
	/**
	 * 添加登录用户信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-12下午02:42:07
	 */
	public function addLogonUser(){
		$esid=$_POST['esid'];
		$data['username']=$_POST['regname'];
		$data['password']=md5($_POST['password']);
		$data['user_type']=1;
		$data['register_time']=date('Y-m-d H:i:s',time());
		$usertable=M('user');
		$request=$usertable->data($data)->add();
		if($request){
			$table=M('');
			$sql="select max(id)from 73go_user";
			$requestid=$table->query($sql);
			$id=$requestid[0]['max(id)'];
			$updatesql="update 73go_employee set u_id=$id where id=$esid";
			$requestupd=$table->execute($updatesql);
			$this->ajaxReturn(1);
		}
		else{
			$this->ajaxReturn(0);
		}
		
	}
	/**
	 * 修改登录用户信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-12下午02:44:28
	 */
	public function updateLogonUser(){
		$esid=$_POST['esid'];
		$tmc_employee=M('employee');
		$request=$tmc_employee->where('id='.$esid)->find();
		$userid=$request[u_id];
		$data['username']=$_POST['regname'];
		$data['password']=md5($_POST['password']);
		$usertable=M('user');
		$requestupd=$usertable->where('id='.$userid)->save($data);
		if($requestupd){
			$this->ajaxReturn(1);
		}else{
			$this->ajaxReturn(0);
		}
			
	}
	
}