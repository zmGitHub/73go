<?php
namespace Home\Logic;

use Think\Model;
/**
 * 进行判断 短信 和邮件发送时  姓名的判断；
 * 如果在emp表存有真实姓名则使用姓名，否则使用系统登录用户名
 * 创建者：郭攀
 * 创建时间：2014-02-28
 *
 */

class JudgeNameLogic extends Model {
	
	public  function UserName(){
	//调用系统用户的姓名
	$SysName=Li('userName');
	//调用登录用户empId 
	$empId=Li('empId');
	$co_employee=M('employee');
	$request=$co_employee->where('id='.$empId)->find();
	$employee=$request['name'];
	//进行判断在emp表存有真实姓名则使用姓名，否则使用系统登录用户名
	//如果真是姓名为空，显示系统用户的登录名称
		if($employee ==''){
						
			$request['name']=$SysName;

		}
		return $request;

	}

}