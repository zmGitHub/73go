<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 用户登录
 * 创建者：董发勇
 * 创建时间：2014-11-7上午09:37:07
 *
 *
 */
class IndexController extends Controller {
	/**
	 * 跳转到系统登录界面
	 * 创建者：董发勇
	 * 创建时间：2014-11-7上午09:31:21
	 */
    public function index(){
     	//清空session
		C('LAYOUT_ON',FALSE);
		LI()->clean();
		
		$this->theme('admin')->display('qsx_login');
    }
    /**
     * 系统登录
     * 创建者：董发勇
     * 创建时间：2014-11-7上午09:32:16
     */
    public function login(){
    	$map['username']=$_POST['username'];
		$map['password']= $_POST['password'];
		$map['user_type']=99;
		$map['status']=0;
		$user = M('user');
		$result = $user->where($map)->find();//查询验证用户

		if($result){
		 	LI('userName', $result['username']);
			LI('userId', $result['id']);
			LI('userType', $result['user_type']);
			$info = '帐号 ' . $map['username'] . ' 登录管理员网页成功';
			LOGS($type=1, $info);
			$this->ajaxReturn(1);
		}else{
			$info = '帐号 ' . $map['username'] . ' 登录管理员网页失败';
			LOGS($type=1, $info,$map['username']);
			$this->ajaxReturn(0);
		}
    }
    
    
 	public function main(){
     	//清空session
		C('LAYOUT_ON',FALSE);
		
//		LI()->clean();
		$company =M('company');
		$employee =M('employee');
		$tmc =M('tmc');
		$op =M('tmc_employee');
		$nums['empnums']=count($employee->select());
		$nums['comnums']=count($company->select());
		$nums['tmcnums']=count($tmc->select());
		$nums['opnums']=count($op->select());

		$this->assign('nums',$nums);
		$this->theme('admin')->display('mainpage');
    }
}