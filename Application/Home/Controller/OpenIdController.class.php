<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 
 * QQ登陆
 * 获取QQ的openid
 * 
 * @author 余卓燃
 *
 */


class OpenIdController extends Controller {

/**
 * 获取登陆QQ的openId
 * 如果数据库存在（即用户已绑定过账号），则直接登陆。
 * 如果数据库不存在（即用户未绑定账号），则跳转到绑定账号的页面
 * 
 * 添加日期：2015.3.24
 * programmer：余卓燃
 */
	public function getOpenId(){
		$openId = $_POST['openId'];
		$user = M('user');
		$qqUser = $user->where("qq_openid= '".$openId."'")->find();
		if($qqUser){
			
			LI('userId', $qqUser['id']);
			LI('userName', $qqUser['username']);
			LI('userType', $qqUser['user_type']);
			$uType = (int) $qqUser['user_type'];
			
			switch ($uType) {
					case 1:
						
						$m_emp=M('employee');
						$emp = $m_emp->where('u_id='.$qqUser['id'])->find();
						if ($emp) {
							LI('empId', $emp['id']);
							LI('comId', $emp['co_id']);
							LI('displayName', $emp['name']);
							LI('email', $emp['email']);
							Li('phone',$emp['phone']);
						}
						$info='帐号'.$qqUser['username'].'使用QQ登录73go前台页面成功';
						LOGS(1,$info);
						$this->ajaxReturn(1);
						
						break;
					case 2:
							$m_company=M('company');
							$com = $m_company->where('u_id='.$qqUser['id'])->find();
							if ($com) {
								LI('comId', $com['id']);
								LI('displayName', $com['name']);
							}
							$info='帐号'.$qqUser['username'].'使用QQ登录73go前台页面成功';
						    LOGS(1,$info);
						    $this->ajaxReturn(2);
							break;
			}
		
		}else{
			$this->ajaxReturn(0);
		}
		
	}
	
	/**
	 * 
	 * QQ尚未绑定，跳转到绑定页面
	 * 
	 * 添加日期：2015.3.27
     * programmer：余卓燃
	 */
	
	public function bindOpenIdPage(){
	    C('LAYOUT_ON',FALSE);
	    $this->theme('default')->display("bind_QQ_openid");
	}
	
	/**
	 * 
	 * 绑定当前登录的账号
	 * 
	 * 添加日期：2015.3.27
     * programmer：余卓燃
	 */
	public function bind_current_account(){
		
	}
	
	
	/**
	 * 填写用户名和密码后，绑定账号
	 * 
	 * 添加日期：2015.3.27
     * programmer：余卓燃
	 */
	public function bind_account(){
		$loginNo = $_POST['loginNo'];
		$password = md5($_POST['password']);
		$openId = $_POST['openId'];
		
		//在user表中搜索该账号
		$m = M('user');
		$cond['username'] = $loginNo;
		$cond['password'] = $password;
		$qqUser = $m->where($cond)->find();
		
	    if(!$qqUser){
			$info = '帐号 ' . $loginNo . ' 绑定QQ账号失败';
			LOGS(1, $info, $loginNo);
			$this->ajaxReturn(0);
		}

		if ($qqUser['status']==99) {
			$info='帐号 '.$loginNo.' 被禁用绑定QQ账号失败';
			LOGS(1,$info,$loginNo);
			$this->ajaxReturn(-1);

		}else {
			//把QQ的openid存入该账号的qq_openid字段中
			$data['qq_openid'] = $openId;
			$m->where('id = '.$qqUser['id'] )->save($data);
			
			LI('userId', $qqUser['id']);
			LI('userName', $qqUser['username']);
			LI('userType', $qqUser['user_type']);
			$uType = (int) $qqUser['user_type'];
			$info='帐号'.$loginNo.'绑定QQ账号成功';
			
			switch ($uType) {
					case 1:
						
						$m_emp=M('employee');
						$emp = $m_emp->where('u_id='.$qqUser['id'])->find();
						if ($emp) {
							LI('empId', $emp['id']);
							LI('comId', $emp['co_id']);
							LI('displayName', $emp['name']);
							LI('email', $emp['email']);
							Li('phone',$emp['phone']);
						}
						LOGS(1,$info);
						$this->ajaxReturn(1);
						
						break;
					case 2:
						$m_company=M('company');
						$com = $m_company->where('u_id='.$qqUser['id'])->find();
						if ($com) {
							LI('comId', $com['id']);
							LI('displayName', $com['name']);
						}
					    LOGS(1,$info);
					    $this->ajaxReturn(2);
						break;
			}
		
		}
		
	}
	
	


}