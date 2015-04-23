<?php
namespace Home\Logic;

use Think\Model;

class ApprovalLogic extends Model{

	//根据不同的条件查询出待我审批不同的页面
	public function approval_list($Page){
			$id = LI('userId');
			$tr_approval=M("tr_approval");
			//根据不同的条件查询出不同的结果
			$status=$_REQUEST['status'];
			if($status==2){
			   $status=" ";
			}else if($status==1){
			   $status=" and a.status != 0";
			}else if ($status==0){
			   $status=" and a.status = 0";
			}
			
			if($Page==0){
				$link=" ";
			}else{
				$link=" limit $Page->firstRow,$Page->listRows";
			}

			//查询出相关的数据  加上limit限制条件
			$Strsql = "select  b.tr_no AS apply_num,a.id ,a.appv_id,a.tr_id,a.status,b.time,b.co_id,b.tr_name,b.description,b.leave_date,b.back_date,b.cost,c.name as appro_name  from  73go_tr_approval as a
						LEFT JOIN 73go_travel_request as b ON a.tr_id = b.id
						LEFT JOIN 73go_employee as c ON a.appv_id = c.id
						where  c.u_id = ".$id ."  $status
						order by a.id desc ";
			$datt = $tr_approval->query($Strsql.$link);
			foreach ($datt as $key=>$value){
				
						$sql="select b.name from 73go_tr_approval AS a
									LEFT JOIN 	73go_employee as b ON  a.appv_id = b.id
									WHERE tr_id=".$value['tr_id']." order by a.status desc
						";
						
						$approver_user = $tr_approval->query($sql);
						foreach($approver_user as $key1=>$name){
								if($key1 == 0){
									$a_name = $name['name'];
								}else{
									$a_name.= "->".$name['name'];
								}
						
						};
						$datt[$key]['approver_user']=$a_name;
						

			};
			
			return $datt;
				

	}
	
	









}