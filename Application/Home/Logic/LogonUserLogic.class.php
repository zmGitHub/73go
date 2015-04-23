<?php
namespace Home\Logic;
use Common\Logic\YMDBase62;
use UMessage\Logic\UnifyMessage;
use UMessage\Logic\UnifyMessageSender;
use Think\Model;
/**
 * 企业登录用户业务处理实现层
 * 创建者：董发勇
 * 创建时间：2014-12-12上午10:11:50
 *
 *
 */
class LogonUserLogic extends Model {

	/**
	 * 查询登录用户
	 * 创建者：董发勇
	 * 创建时间：2014-12-12上午10:18:38
	 */
	public function showLogonUserLogic($Page){
		$comid=LI('comId');
		
	    if($Page == 0){
			$link = "";
		}else{
			$link = " limit $Page->firstRow , $Page->listRows";
		}
		
		$employee=M('');
		$sql="select te.id id,te.emp_code,te.name name,tb.name br_name,te.phone,te.email,u.username,u.id u_id,u.password,te.status
		from 73go_employee te LEFT JOIN 73go_branch tb on te.br_id=tb.id LEFT JOIN 73go_user u on 
		te.u_id=u.id or te.u_id=null where te.co_id=$comid AND te.status=1 ".$link;
		$request=$employee->query($sql);
		return $request;
	}
	/**
	 * 查询有登录名用户
	 * 创建者：董发勇
	 * 创建时间：2014-12-12上午11:01:51
	 */
	public function showLogonUserYesLogic(){
		$comid=LI('comId');
		$employee=M('');
		$sql="select te.id esid,te.emp_code,te.`name` tename,tb.`name` tbname,te.phone,u.username regname,u.id uid,u.`password`,te.`status`
		from 73go_employee te LEFT JOIN 73go_branch tb on te.br_id=tb.id LEFT JOIN 73go_user u on 
		te.u_id=u.id where te.u_id=u.id and te.co_id=$comid AND te.status=1 ";
		$request=$employee->query($sql);
		return $request;
	}
	/**
	 * 查询没有登录名用户
	 * 创建者：董发勇
	 * 创建时间：2014-12-12上午11:17:57
	 */
	public function showLogonUserNoLogic(){
		$comid=LI('comId');
		$employee=M('');
		$sql="select te.id esid,te.emp_code,te.`name` tename,tb.`name` tbname,te.phone,u.username regname,u.id uid,u.`password`,te.`status`
		from 73go_employee te LEFT JOIN 73go_branch tb on te.br_id=tb.id LEFT JOIN 73go_user u on 
		te.u_id=u.id where te.u_id is null and te.co_id=$comid AND te.status=1 ";
		$request=$employee->query($sql);
		return $request;
	}
	
	//系统登录用户
	public function co_userid_mgnt($cond,$Page){
		$type=$cond['type'];
		$search=$cond['search'];
		if($type==1){
			$link = "";
		}else if($type==2){
			$link = " AND emp.u_id IS NOT NULL ";
		}else if($type==3){
			$link = " AND emp.u_id IS NULL ";
		}
			
		$link1=" AND (emp.emp_code like '%$search%' OR emp.`name` like '%$search%' 
						OR emp.phone like '%$search%' or emp.id_num like '%$search%' or emp.email like '%$search%')";
		
		if($Page == 0){
			$link2 = "";
		}else{
			$link2 = " limit $Page->firstRow , $Page->listRows";
		}
		$co_id=LI('comId');
		$empM=M('employee');
		$sql="SELECT emp.*,br.`name` as br_name,u.username 
				FROM 73go_employee as emp
					LEFT JOIN 73go_branch as br ON emp.br_id=br.id
					LEFT JOIN 73go_user as u ON emp.u_id=u.id
 				WHERE emp.co_id=$co_id and emp.`status`=1";//1.21更改
		$result=$empM->query($sql.$link.$link1.$link2);
		return $result;
	}
	//重置密码
	public function reset_pwd(){
		$emp_id=$_POST['emp_id'];
		$empM=M('employee');
		$employee=$empM->where('id='.$emp_id)->find();
		$pwd=C('DEFAULT_PASSWORD');
		$data['password'] = md5($pwd);
		$userM=M('user');
		$result=$userM->where('id='.$employee['u_id'])->data($data)->save();
		$userinfo = $userM->where('id='.$employee['u_id'])->find();
		$wx_openid = $userinfo['wx_openid'];
		if($result !== false) {
			$return = array(name=>$userinfo['username'],password=>$pwd);
			if($employee['name'])
				$return['name'] = $employee['name'] ;
				
			$sender = new UnifyMessageSender ();
			$types =  strlen($wx_openid) != 28 ? array(1,2) : array(1,2,3);
			$targets = strlen($wx_openid) != 28 ? array (1 => $employee['phone'], 2 =>$employee['email']) : array (1 => $employee['phone'], 2 =>$employee['email'], 3 => $wx_openid);
			$title =  '系统登录密码被重置';
			$text =  '您在www.73go.cn上的密码已被管理员重置为'.$pwd.'，请尽快登陆网站修改密码。';
			$html = '尊敬的'.$return['name'].'：
						您在<a href="http://www.73go.cn">www.73go.cn</a>上的密码已被管理员重置为'.$pwd.'，请尽快登陆网站修改密码。';
			$um = UnifyMessage::NewUnionMessage ( $types,$targets,$title,$text, $html );
			$sender->sendUMessage ($um);
			
			return $return;
		}else{
			return  0;
		}
	}
	//创建用户
	public function create_user(){
		$emp_id=$_POST['emp_id'];
		$empM=M('employee');
		for($i =0;$i<6;$i++){
			$num=rand(0, 61);
			$str .= YMDBase62::getStrOfNumber($num);
		}
		$data['username']=$str;
		$pwd=C('DEFAULT_PASSWORD');
		$data['password'] = md5($pwd);
		$data['register_time']=date('Y-m-d H:i:s',time());
		$userM=M('user');
		//1.21更改gsf
		//如果存在用户id
		//if($u_id){
		//	$result=$userM->where('id='.$u_id)->data($data)->save();
		//}else{
			$data['user_type']=1;
			$data['reg_id_revisable']=1;
			$result=$userM->data($data)->add();
			$dat_emp['u_id']=$result;
			$empM->where('id='.$emp_id)->data($dat_emp)->save();
		//}
		
			//2.4 cgk
			//添加信息邮件发送
		if($result){
			$employee=$empM->where('id='.$emp_id)->find();
			$return = array(username=>$str,name=>$str,password=>$pwd);
			if($employee['name'])
				$wx_openid=Li('userOpenid');//获取该用户的wx_openid;
				$return['name'] = $employee['name'] ;
			$sender = new UnifyMessageSender ();
			//添加微信发送消息 3.16 郭攀
			$types =  strlen($wx_openid) != 28 ? array(1,2) : array(1,2,3);
			$targets = strlen($wx_openid) != 28 ? array (1 => $employee['phone'], 2 =>$employee['email']) : array (1 => $employee['phone'], 2 =>$employee['email'], 3 => $wx_openid);
			//$types = array(1,2);
			//$targets = array (	1 => $employee ['phone'],2 =>  $employee ['email']);
			$title =  '系统登录账户创建';
			$text =  '管理员已在www.73go.cn上的为您创建了账号，用户名：'.$return['username'].'，密码：'.$return['password'].'，请即时登录网址修改密码。';
			$html =  '尊敬的'.$return['name'].'：
								管理员已在<a href="http://www.73go.cn">www.73go.cn</a>上的为您创建了账号，用户名：'.$return['username'].'，密码：'.$return['password'].'，请即时登录网址修改密码。';
			$um = UnifyMessage::NewUnionMessage ( $types,$targets,$title,$text, $html );
			$sender->sendUMessage ($um);
			return $return;
		}
		else return 0;
	}
	//修改状态
	public function update_status(){
		$emp_id=$_REQUEST['emp_id'];
		$empM=M('employee');
		$data['status']=0;
		$data['u_id']='';
		$result=$empM->where('id='.$emp_id)->data($data)->save();
		return $result;
	}
}