<?php
namespace Home\Controller;
use System\LoginInfo;
use Think\Controller;

class PopController extends Controller {
	
//根据登陆用户查找本企业员工
	public function find_user(){
		$info = $_POST['info'];
// 		$exc_id = $_POST['exc_id'];
		$id = LI('userId');
		
		$employee = M('employee');
		$co_id = $employee->where('u_id = '.$id)->find();
		
		$map['73go_employee.co_id'] = $co_id['co_id'];
// 		if($exc_id)
// 		$map['73go_employee.id'] =  array('not in',$exc_id);;
		$map['73go_employee.status'] = 1;
		$where['73go_employee.name'] = array('like',"%".$info."%");
		$where['73go_employee.phone'] = array('like',"%".$info."%");
		$where['73go_employee.email'] = array('like',"%".$info."%");
		$where['73go_employee.emp_code'] = $info;
		$where['_logic'] = 'OR';
		$map['_complex'] = $where;
		$user = $employee->field('73go_employee.*,73go_branch.name as branch_name')->join('LEFT JOIN 73go_branch ON 73go_branch.id = 73go_employee.br_id')->where($map)->select();
		//echo $employee->getLastSql();
		$this->ajaxReturn($user,'json');
	
	}
	
	//根据企业登陆账号查找本企业员工
	public function find_emp(){
		$info = $_POST['info'];
		// 		$exc_id = $_POST['exc_id'];
		$co_id = LI('comId');
		$employee = M('employee');
		$map['73go_employee.co_id'] = $co_id;
		// 		if($exc_id)
			// 		$map['73go_employee.id'] =  array('not in',$exc_id);;
		$map['73go_employee.status'] = 1;
		$where['73go_employee.name'] = array('like',"%".$info."%");
		$where['73go_employee.phone'] = array('like',"%".$info."%");
		$where['73go_employee.email'] = array('like',"%".$info."%");
		$where['73go_employee.emp_code'] = $info;
		$where['_logic'] = 'OR';
		$map['_complex'] = $where;
		$user = $employee->field('73go_employee.*,73go_branch.name as branch_name')->join('LEFT JOIN 73go_branch ON 73go_branch.id = 73go_employee.br_id')->where($map)->select();
// 		echo $employee->getLastSql();
		$this->ajaxReturn($user,'json');
	
	}
	
//根据TMC企业登陆账号查找本TMC企业员工
	public function find_tmcuser(){
		$info = $_POST['info'];
		$id = LI('tmcId');
		$employee = M('tmc_employee');
		$map['73go_tmc_employee.tmc_id'] = $id;
		$map['73go_tmc_employee.status'] = 0;
		$where['73go_tmc_employee.name'] = array('like',"%".$info."%");
		$where['73go_tmc_employee.phone'] = array('like',"%".$info."%");
		$where['73go_tmc_employee.email'] = array('like',"%".$info."%");
		$where['73go_tmc_employee.emp_code'] = $info;
		$where['_logic'] = 'OR';
		$map['_complex'] = $where;
		$user = $employee->field('73go_tmc_employee.*,73go_tmc_branch.name as branch_name')->join('LEFT JOIN 73go_tmc_branch ON 73go_tmc_branch.id = 73go_tmc_employee.tmcbr_id')->where($map)->select();
// 		echo $employee->getLastSql();
		$this->ajaxReturn($user,'json');
	
	}
	
//根据TMC企业登陆账号查找协议企业
	public function find_com(){
		$info = $_POST['info'];
		$id = LI('tmcId');
		
		$co_tmc_link = M('co_tmc_link');
		$map['73go_co_tmc_link.tmc_id'] = $id;
		$map['73go_co_tmc_link.status'] = 0;
		$where['73go_company.co_code'] = array('like',"%".$info."%");
		$where['73go_company.name'] = array('like',"%".$info."%");
		$where['_logic'] = 'OR';
		$map['_complex'] = $where;
		$com = $co_tmc_link->field('73go_company.*')->join('LEFT JOIN 73go_company ON 73go_co_tmc_link.co_id = 73go_company.id')->where($map)->select();
		//echo $co_tmc_link->getLastSql();
		$this->ajaxReturn($com,'json');
	}
	
	//根据TMC企业登陆账号查找协议企业的非VIP员工
	public function find_com_user(){
		$info = $_POST['info'];
		$id = LI('tmcId');
		$co_id=$_POST['co_id'];
		
		$Model = M('');
		$map['73go_co_tmc_link.tmc_id'] = $id;
		$map['73go_co_tmc_link.status'] = 0;
		if($co_id!=null)
		$map['73go_employee.co_id'] = $co_id;
		$map['73go_employee.status'] = 1;
		$where['73go_employee.name'] = array('like',"%".$info."%");
		$where['73go_employee.phone'] = array('like',"%".$info."%");
		$where['73go_employee.email'] = array('like',"%".$info."%");
		$where['73go_employee.emp_code'] = $info;
		$where['_logic'] = 'OR';
		$map['_complex'] = $where;
		$com = $Model->field('73go_employee.*,73go_branch.name as branch_name')->table('73go_co_tmc_link,73go_employee')->where('73go_co_tmc_link.co_id=73go_employee.co_id')->join('LEFT JOIN 73go_branch ON 73go_branch.id = 73go_employee.br_id')->where($map)->select();
// 		echo $Model->getLastSql();
		$this->ajaxReturn($com,'json');
		
	}
	
	//查找条件企业
	public function search_com(){
		$info = $_POST['info'];
		$company = M('company');

		if(isset($info) && !empty($info)){
			$com = $company->where("co_code=%d or name='%s'",array($info,$info))->select();
		}else{
			$com = array();
		}
		$this->ajaxReturn($com,'json');
	
	}
	//查找条件TMC企业
	public function search_tmc_com(){
		$info = $_POST['info'];
		$tmc = M('tmc');
		if (isTMCHosting ()) {
			$map['id']=getHostedTmcInfo ("tmc_id");
		}
		$map['tmc_code'] = array('like',"%".$info."%");
		$map['name'] = array('like',"%".$info."%");
		$map['_logic'] = 'OR';
		
		$com = $tmc->where($map)->select();
		//echo $co_tmc_link->getLastSql();
		$this->ajaxReturn($com,'json');
	}
	
	//根据职员ID集查找对应的职员信息
	public function employee_info(){
		$str = $_POST['str'];
		$arr = explode(",",$str);
		unset($arr[count($arr)-1]);
		$Model = M('employee');
		$map['73go_employee.id']  = array('in',$arr);
		$map['73go_dictionary.d_group'] = 'id_type';
		$employee = $Model->field('73go_employee.*,73go_dictionary.d_value')->table('73go_employee,73go_dictionary')->where('73go_employee.id_type=73go_dictionary.d_key')->where($map)->select();
		$this->ajaxReturn($employee,'json');
	
	}
	
	
	
}