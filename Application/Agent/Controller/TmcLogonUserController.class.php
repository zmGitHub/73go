<?php
namespace Agent\Controller;
use UMessage\Logic\UnifyMessage;
use UMessage\Logic\UnifyMessageSender;
use Think\Controller;
/**
 * TMC登录用户业务层
 * @author dfy
 * @2014-12-6 下午03:32:51
 */
class TmcLogonUserController extends Controller{
	/**
	 * 查看登录用户信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午05:48:29
	 */
	public function showTmcLogonUser(){
		$tmcLogonUser=D('Agent/TmcLogonUser','Logic');//调用工作组业务处理层
		
		$request=$tmcLogonUser->showTmcLogonUserLogic(0);//查看登录用户信息
		
		$count=count($request);
		$Page  = new \Think\Page($count,10);
		$show  = $Page->show();// 分页显示输出
		$request=$tmcLogonUser->showTmcLogonUserLogic($Page);//查看登录用户信息
		
		$this->assign('Page',$show);// 赋值分页输出
		C('LAYOUT_ON',TRUE);//允许布局
		layout("tmc");//加载布局文件
		$this->assign('emp',$request);		
		$this->theme('agent')->display('tmc_userid_mgnt');
	}
	/**
	 * 查询有登陆名的用户信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午06:27:57
	 */
	public function showTmcLogonUserYes(){
		$tmcLogonUser=D('Agent/TmcLogonUser','Logic');//调用工作组业务处理层
		$request=$tmcLogonUser->showTmcLogonUserYesLogic();//查看有登陆名的用户信息
		$this->ajaxReturn($request);
	}
	/**
	 * 查询没有登陆名的用户信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午06:27:57
	 */
	public function showTmcLogonUserNo(){
		$tmcLogonUser=D('Agent/TmcLogonUser','Logic');//调用工作组业务处理层
		$request=$tmcLogonUser->showTmcLogonUserNoLogic();//查看没有登录名用户信息
		$this->ajaxReturn($request);
	}
	/**
	 * 模糊查询用户信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午07:12:57
	 */
	public function showTmcLogonUserLike(){
		$con=$_POST['con'];
		$logon=$_POST['logon'];
		if($logon==1){//有登陆用户信息
			$tmcLogonUser=D('Agent/TmcLogonUser','Logic');//调用工作组业务处理层
			$request=$tmcLogonUser->showTmcLogonUserYesLikeLogic($con);//查看没有登录名用户信息
			$this->ajaxReturn($request);
		}
		if($logon==2){//有登陆用户信息
			$tmcLogonUser=D('Agent/TmcLogonUser','Logic');//调用工作组业务处理层
			$request=$tmcLogonUser->showTmcLogonUserNoLikeLogic($con);//查看没有登录名用户信息
			$this->ajaxReturn($request);
		}
		else{
			//全部用户信息
			$tmcLogonUser=D('Agent/TmcLogonUser','Logic');//调用工作组业务处理层
			$request=$tmcLogonUser->showTmcLogonUserLikeLogic($con);//查看没有登录名用户信息
			$this->ajaxReturn($request);
		}
		
	}
	/**
	 * 停用和启用用户信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午08:26:10
	 */
	public function updateTmcLogonUserStasus($id,$status){
		$esid=$id;
		$status=$status;
		$emp = M('tmc_employee');
		if($status==0){
		 $sql="update 73go_tmc_employee te set te.`status`=99 where te.id=".$esid;
		}else{
		 $sql="update 73go_tmc_employee te set te.`status`=0 where te.id=".$esid;
		}
		$requestsion=$emp->execute($sql);
		$this->theme('agent')->showTmcLogonUser();
	}
	/**
	 * 根据用户id查询用户信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午08:42:45
	 */
	public function showTmcLogonUserByESId(){
		$esid=$_POST['esid'];
		$Requesttable=M('');
		$sql="select te.id as esid,u.username as regname,u.`password`,te.`status`,u.id as uid from
			 73go_tmc_employee te,73go_user u where te.u_id=u.id and te.id=".$esid;
		$request=$Requesttable->query($sql);
    	$this->ajaxReturn($request);
	}
	/**
	 * 修改登录用户信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-9下午02:23:50
	 */
	public function updateTmcLogonUser(){
		$esid=$_POST['esid'];
		$tmc_employee=M('tmc_employee');
		$request=$tmc_employee->where('id='.$esid)->find();
		$userid=$request['u_id'];
		$data['username']=$_POST['regname'];
		$data['password']=md5($_POST['password']);
		$usertable=M('user');
		$requestupd=$usertable->where('id='.$userid)->save($data);
		if($requestupd){

			//发送短信和邮件 david law 2015.2.5
			$employee = $request;			
			$user = $usertable->where('id='.$employee['u_id'])->find();
			$wx_openid=$user['wx_openid'];
			$sender = new UnifyMessageSender ();
			$types =  strlen($wx_openid) != 28 ? array(1,2) : array(1,2,3);
			$targets = strlen($wx_openid) != 28 ? array (1 => $employee ['phone'],2 => $employee ['email']) : array (1 => $employee ['phone'],2 => $employee ['email'], 3 => $wx_openid);
			$title =  '系统登录密码被重置';
			$text =  '您在www.73go.cn上的密码已被管理员重置为'.$_POST['password'].'，请尽快登陆网站修改密码。';
			$html = '尊敬的'.$employee['name'].'：
						您在<a href="http://www.73go.cn">www.73go.cn</a>上的密码已被管理员重置为'.$_POST['password'].'，请尽快登陆网站修改密码。';

			$um = UnifyMessage::NewUnionMessage ( $types,$targets,$title,$text, $html );
			$sender->sendUMessage ($um);

			$this->ajaxReturn(1);
		}else{
			$this->ajaxReturn(0);
		}
			
	}
	/**
	 * 添加登录用户信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-9上午11:09:34
	 */
	public function addTmcLogonUser(){
		$esid=$_POST['esid'];
		$data['username']=$_POST['regname'];
		$data['password']=md5($_POST['password']);
		$data['user_type']=4;
		$data['register_time']=date('Y-m-d H:i:s',time());
		$usertable=M('user');
		$request=$usertable->data($data)->add();
		if($request){
			$table=M('');
			$sql="select max(id)from 73go_user";
			$requestid=$table->query($sql);
			$id=$requestid[0]['max(id)'];
			$updatesql="update 73go_tmc_employee set u_id=$id where id=$esid";
			$requestupd=$table->execute($updatesql);

			//发送短信和邮件 david law 2015.2.5
			$tmc_employee=M('tmc_employee');
			$employee=$tmc_employee->where('id='.$esid)->find();
			$sender = new UnifyMessageSender();
			$types = array(1,2);
			$targets = array (	1 => $employee ['phone'],2 =>  $employee ['email']);
			$title =  '系统登录账户创建';
			$text =  '管理员已在www.73go.cn上的为您创建了账号，用户名：'.$data['username'].'，密码：'.$_POST['password'].'，请即时登录网址修改密码。';
			$html =  '尊敬的'.$employee['name'].'：
								管理员已在<a href="http://www.73go.cn">www.73go.cn</a>上的为您创建了账号，用户名：'.$data['username'].'，密码：'.$_POST['password'].'，请即时登录网址修改密码。';
			$um = UnifyMessage::NewUnionMessage($types,$targets,$title,$text, $html );
			$sender->sendUMessage($um);

			$this->ajaxReturn(1);
		}
		else{
			$this->ajaxReturn(0);
		}
		
	}
}