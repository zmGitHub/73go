<?php

namespace Home\Model;

use Think\Model;

class EmployeeMessageModel extends Model{

	/**
	 * 描述：判断employee所填信息是否齐全，如果齐全返回1，不齐全返回-1
	 * @author 王月
	 * @date 2015-02-03
	 * 版本信息：V1.0	王月	2015-02-03
	 *
	 */
	public function employeeMessage() {
		$u_id = LI('userId');
		$employee = M('employee');
		$employeeMessage = $employee->field('name,sex,phone,email,id_type,id_num,province,city')
			->where("u_id=".$u_id)->select();
		$name = $employeeMessage[0]['name'];
		$sex = $employeeMessage[0]['sex'];
		if(($name == null) or ($sex == null)) {
			return -1;
		} else {
			return 1;
		}

	}
}

?>