<?php

namespace Home\Logic;

use Think\Model;
/**
 * 用户选中方案时，查询出给用户制作方案的OP专员
 * 创建者：郭攀
 * 创建时间：2014-03-03
 *
 */

	class OpNamelogic extends Model{
		
		//查询出给用户做出方案被选中的tmc员工的名称
		public function tmcemp($req_id){
			$qsx_rec=M("tmc_qsx_rec");
			$tmcemp=M("tmc_employee");
			$qsxreq=M("qsx_req");
			$tmc=M("tmc");
			$user=M("user");
			$map['status']=1;
			$map['req_id']=$req_id;
			$result=$qsx_rec->where($map)->find();
			//根据查询条件可以查询出tmc_id(tmc企业) 和 rec_emp_id(接单人的Id);	
			//根据接单人的id 查询出 op专员的姓名
			$data=$tmcemp->where("id=".$result['rec_emp_id'])->find();
			$datt=$tmc->where("id=".$data['tmc_id'])->find();
			//查询出是否有useropenId,进行微信的发送
			$u_id=$data['u_id'];
			$datt_res=$user->where("id=".$u_id)->find();

			//查询出需求的内容
			$req_content=$qsxreq->where("id=".$req_id)->find();
			$result['tmc_name']=$datt['name']; //TMC企业的名称
			$result['tmc_email']=$datt['contact_email'];//tmc企业的电话
			$result['tmc_phone']=$datt['contact_phone'];//tmc企业的电话
			$result['op_name']=$data['name']; // OP专员的名称
			$result['email']=$data['email'];//做出方案人的邮箱
			$result['phone']=$data['phone'];//做出方案人的电话
			$result['wx_openid']=$datt_res['wx_openid'];  //做方案人的openid
			//查询出方案的内容
			$result['content']=$req_content['other_content'];
			
			return $result;
			
		}	
		
		//查询出预订者的相关信息  预订者的公司，预订者的姓名 ，预订者的手机
		public function BookInfo(){
			
			//调用系统用户的姓名
			$SysName=Li('userName');
			//调用登录用户empId 
			$empId=Li('empId');
			$co_employee=M('employee');
			$qsx_req=M("qsx_req");
			$company=M("company");
			$request=$co_employee->where('id='.$empId)->find();
			$employee=$request['name'];
			//进行判断在emp表存有真实姓名则使用姓名，否则使用系统登录用户名

			//如果真是姓名为空，显示系统用户的登录名称
				if($employee ==''){
								
					$request['name']=$SysName;
		
				}
				
			//查询出预订者所在的公司
			$ress=$company->where("id=".$request['co_id'])->find();
			$request['co_name']=$ress['name'];
			
			return $request;
		
			}
			

		
		}

		
