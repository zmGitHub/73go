<?php
namespace Home\Controller;
use Agent\Logic\TmcDepartmentStaffLogic;
use Common\Logic\CommonLogic;
use Think\Controller;
/**
 * TMC部门与员工业务层
 * @author dfy
 * @2014-12-6 下午03:32:51
 */
class TmcDepartmentStaffController extends Controller{
	/**
	 * 查看tmc部门信息
	 * 创建者：张鹏
	 * 创建时间：2014-12-8下午03:27:45
	 */
	public function showTmcDepartment(){

		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$tmc_id = LI('tmcId');
		$m_tmc=M('tmc');
		$tmc_info=$m_tmc->where('tmc_id='.$tmc_id)->find();
		$this->ajaxreturn($tmc_info,'json');
	}

	/**
	 * 获取当前登录的TMC所有员工列表。
	 */
	public function showCurrTmcStaff() {

		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$tmcId = LI('tmcId');
		$m_op = M('operator');
		$op_list = $m_op->where('tmc_id='.$tmcId)->select();
		$this->ajaxreturn($op_list,'json');
	}


	/**
	 * 模糊查询部门下的员工
	 * 创建者：张鹏
	 * 创建时间：2014-12-8下午04:33:39
	 */
	public function showTmcStaffLike(){

		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    	$eplike=$_POST['eplike'];//获取模糊搜索值
    	$tmcDeparmentStaff=D('Agent/TmcDepartmentStaff','Logic');//调用TMC部门员工业务处理层
		$request=$tmcDeparmentStaff->showTmcStaffLikeLogic('',$eplike);//模糊查询员工
    	$this->ajaxReturn($request);
	}
	/**
	 * 删除部门下的员工
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午04:37:08
	 */
	public function deleteTmcStaff(){

		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$op_id=1;//$_POST['op_id'];
		$tmcDeparmentStaff=D('Agent/TmcDepartmentStaff','Logic');//调用TMC部门员工业务处理层
		$request=$tmcDeparmentStaff->deleteTmcStaffLogic($op_id);//删除员工
		$this->ajaxReturn($request);
	}


	/**
	 * 添加员工
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午04:43:56
	 */
	public function addTmcStaff(){

		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$data['account']=$_POST['account'];
		$data['password']=md5($_POST['password']);
	 	$data['name']=$_POST['name'];
	 	$data['sex']=$_POST['sex'];
	 	$data['phone']=$_POST['phone'];
		$data['qq']=$_POST['qq'];
	 	$data['email']=$_POST['email'];
	 	$data['card_type']=$_POST['id_type'];
	 	$data['card_id']=$_POST['id_num'];
		$m_user = M('user');
		$result = $m_user->add($data);
		if($result){
			$data['tmc_id'] = LI('tmcId');//tmc企业id
			$data['op_id']=$_POST['op_id'];
			$tmc_employee = M('operator');
			$request = $tmc_employee->add($data);
			if($request){
				$this->ajaxReturn(0);
			}
			else {
				$this->ajaxReturn(0);
			}
		}else{
			$this->ajaxReturn(0);
		}
	}
	/**
	 * 修改员工
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午04:51:42
	 */
	public function updateTmcStaff(){

		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$data['account']=$_POST['account'];
		$data['password']=md5($_POST['password']);
		$data['name']=$_POST['name'];
		$data['sex']=$_POST['sex'];
		$data['phone']=$_POST['phone'];
		$data['qq']=$_POST['qq'];
		$data['email']=$_POST['email'];
		$data['card_type']=$_POST['id_type'];
		$data['card_id']=$_POST['id_num'];
		$m_user = M('user');
		$result = $m_user->where('account='.$data['account'])->save($data);
		if($result){
			$data['tmc_id'] = LI('tmcId');//tmc企业id
			$data['op_id']=$_POST['op_id'];
			$tmc_employee = M('operator');
			$request = $tmc_employee->where('op_id= and tmc_id = ',array($data['op_id'],$data['tmc_id']))->save($data);
			if($request){
				$this->ajaxReturn(0);
			}
			else {
				$this->ajaxReturn(0);
			}
		}else{
			$this->ajaxReturn(0);
		}
	}

	/**
	 *
	 * 根据id查询员工
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午04:47:24
	 */
	public function showTmcStaffById($emps_id){

		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$this->prepareStaffView();
		$branche=M('');
		$co_idbr=LI('tmcId');
		$branchs = D('Agent/TmcDepartmentStaff','Logic');//调用TMC部门员工业务处理层
		$branch = $branchs->getDeprtmentTree();
		$this->assign('branchtree',$branch);

		$branchesql="select id,name from 73go_tmc_branch where tmc_id=$co_idbr and status=0";
		$branrequest=$branche->query($branchesql);
		$this->assign('brupdate',$branrequest);
    	$tmcemptable=M('tmc_employee');
    	$request=$tmcemptable->where('id='.$emps_id)->find();
		$this->assign('tmcemp',$request);//保存员工信息
		//得到身份类型和身份号码 dx 15-3-19
		$id_info = M('dictionary');//实例化字典表
		$id_type = $request['id_type'];
		$result = $id_info->where('id='."'$id_type'")->select();
		$this->assign('tmcempId',$result[0]);//二位数组
		if(LI('userType') == 3){
			$this->theme('agent')->display('tmc_employee_update');//TMC修改员工信息
		}else{
			$this->theme('agent')->display('tmc_employee_edit');//TMC员工修改员工信息
		}
	}


	//查询员工所有信息
	public function selectEmp($m,$co_id){

		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$data['tmc_id']=$co_id;
		$data['status']=0;
		return $m->where($data)->select();
	}

	//带工号的查询方法
	public function selectBycode($m,$coId,$code){

		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		$data['tmc_id']=$coId;
		$data['emp_code']=$code;
		return $m->where($data)->find();
	}
	//带证件号的查询方法
	public function selectBynum($m,$coId,$type,$num){

		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		//根据Type查出证件类型的id
		$dictionary=M('dictionary');
		if($type=="大陆居民身份证" || $type=="身份证"){
			$type="大陆居民身份证";
		}
		$dict['d_group']="id_type";
		$dict['d_value']=$type;
		$request=$dictionary->where($dict)->find();
		$dictId=$request['id'];
		$data['tmc_id']=$coId;
		$data['id_type']=$dictId;
		$data['id_num']=$num;
		return $m->where($data)->find();
	}
	//带姓名+手机的方法
	public function selectByname($m,$coId,$name,$phone){

		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		//根据$brId查询出部门id
		$branch=M('tmc_branch');
		$br['tmc_id']=$coId;
		$brrequest=$branch->where($br)->find();
		$br_id=$brrequest['id'];
		
		$data['tmc_id']=$coId;
		$data['name']=$name;
		$data['phone']=$phone;
		
		$request= $m->where($data)->find();
		return $request['id'];
	}
	//带姓名+邮箱+部门的方法
	public function selectByemail($m,$coId,$name,$email){
		//根据$brId查询出部门id
		$branch=M('tmc_branch');
		$br['tmc_id']=$coId;
		$brrequest=$branch->where($br)->find();
		$br_id=$brrequest['id'];
		
		$data['tmc_id']=$coId;
		$data['name']=$name;
		$data['email']=$email;
		
		$request= $m->where($data)->find();
		return $request['id'];
	}

	//获取证件id
	public function selectBytypeId($type){
		$dictionary=M('dictionary');
		$dict['d_group']="id_type";
		if($type=="大陆居民身份证" || $type=="身份证"){
			$dict['d_value']="大陆居民身份证";
		}else{
			$dict['d_value']=$type;
		}
		$request= $dictionary->where($dict)->find();
		dump($request);
		return $request['id'];
	}
}