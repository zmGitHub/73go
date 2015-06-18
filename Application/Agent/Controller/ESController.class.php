<?php
namespace Agent\Controller;

use Home\Logic;
use System\LoginInfo;
use Think\Controller;
use Agent\Logic\NewDemandLogic;
use UMessage\Logic\UnifyMessage;
use UMessage\Logic\UnifyMessageSender;

/**
 * TMC
 * 企业：Enterprise
 * 方案：Scheme
 * 创建者：董发勇
 * 创建时间：2014-11-19上午09:28:47
 *
 */
class ESController extends Controller {
    
	/**
	 * 查询轻松行需求单
	 * 创建者：甘世凤
	 * 2014-11-19上午09:34:46
	 */
    public function showEReq(){

    	$bygrp=$_REQUEST['bygrp'];
    	if(!$bygrp){
    		$bygrp=3;
    	}
    	
    	$u_id = LI('userId');
		//加载布局文件
		layout("tmc");

		//添加分页效果
		$req = D('Agent/ES','Logic');
		$cond['u_id'] = $u_id;
		$cond['bygrp'] = $bygrp;
		$result= $req->queryQSXRequest($cond,0);
		$count=count($result);
		$Page  = new \Think\Page($count,4);
		$show       = $Page->show();// 分页显示输出
		$result= $req->queryQSXRequest($cond,$Page);
		$this->assign('Page',$show);// 赋值分页输出
		$this->assign('reqList', $result);

		//增加客服信息
		$name = array();
		$phone = array();
		$qq = array();
		for($i=0;$i<count($result,2);$i++){
			$qsx_req = M("qsx_req");
			$qro = $result[$i]['qsx_rq_no'];
			$result1 = $qsx_req->where('qsx_rq_no='."'$qro'")->find();
			$id = $result1['u_id'];
			$employee = M("employee");
			$result2 = $employee->where('u_id='.$id)->find();
			$this->assign ( 'info', $result2 );
			array_push($name,$result2['name']);
			array_push($phone,$result2['phone']);
			array_push($qq,$result2['qq']);
			$this->assign ( 'u_name', $name );
			$this->assign ( 'u_phone', $phone );
			$this->assign ( 'u_qq', $qq );
		}

		//调用是否显示紧急预定
		$emergencyM=D("Home/Emergency","Logic");
		$booking=$emergencyM->emergency_book();
		$this->assign('datt',$booking);

        $this->theme('agent')->display("qsx_req_list_sloution");
    }

	/**
	 * 查询轻松行需求单所有订单
	 * 创建者：王月
	 * 2015-3-17
	 */
	public function showEReqAll() {
		$bygrp=$_REQUEST['bygrp'];
		if(!$bygrp){
			$bygrp=3;
		}

		$u_id = LI('userId');

		//加载布局文件
		layout("tmc");
		//添加分页效果
		$req = D('Agent/ES','Logic');

		$cond['u_id'] = $u_id;
		$cond['bygrp'] = $bygrp;

		$result= $req->queryQSXRequestAll($cond,0);
		$count=count($result);

		$Page  = new \Think\Page($count,4);
		$show       = $Page->show();// 分页显示输出

		$result= $req->queryQSXRequestAll($cond,$Page);
		$this->assign('Page',$show);// 赋值分页输出
		$this->assign('reqList', $result);
		/*增加客服信息
		增加了所有需求中的客服 dx 15-3-19*/
		$name = array();
		$phone = array();
		$qq = array();
		for($i=0;$i<count($result,2);$i++){
			$qsx_req = M("qsx_req");
			$qro = $result[$i]['qsx_rq_no'];
			$result1 = $qsx_req->where('qsx_rq_no='."'$qro'")->find();
			$id = $result1['u_id'];
			$employee = M("employee");
			$result2 = $employee->where('u_id='.$id)->find();
			$this->assign ( 'info', $result2 );
			array_push($name,$result2['name']);
			array_push($phone,$result2['phone']);
			array_push($qq,$result2['qq']);
			$this->assign ( 'u_name', $name );
			$this->assign ( 'u_phone', $phone );
			$this->assign ( 'u_qq', $qq );
		}

		//调用是否显示紧急预定
		$emergencyM=D("Home/Emergency","Logic");
		$booking=$emergencyM->emergency_book();
		$this->assign('datt',$booking);

		$this->theme('agent')->display("qsx_req_list_sloution_all");
	}

    public function qsx_req_list_finish(){
    	$bygrp=$_REQUEST['bygrp'];
    	if(!$bygrp){
    		$bygrp=3;
    	}
    	//加载布局文件
		layout("tmc");
		
		$req = D('Agent/ES','Logic');
		
		$cond['bygrp'] = $bygrp; 
		$result= $req->qsx_req_list_finish($cond,0);
		$count=count($result);
		
		$Page  = new \Think\Page($count,4);
		$show       = $Page->show();// 分页显示输出
		
		$result= $req->qsx_req_list_finish($cond,$Page);
		$this->assign('Page',$show);// 赋值分页输出
		//调用是否显示紧急预定
		$emergencyM=D("Home/Emergency","Logic");
		$booking=$emergencyM->emergency_book();
		$this->assign('datt',$booking);
		
		
		$this->assign('reqList', $result);	   	
        $this->theme('agent')->display("qsx_req_list_finish");
    }
    
    /**
     * 准备添加方案（根据需求单id查询）
     * $id需求单id
     * 创建者：甘世凤
     * 2014-11-21下午03:36:33
     */
    public function preAddScheme($id){
    	C('LAYOUT_ON',TRUE);
		//加载布局文件
		layout("tmc");

    	$tqrid=$id;
    	
		$tqr=M('tmc_qsx_rec');
		$resulttqr=$tqr->where('id='.$tqrid)->find();
		$status=$resulttqr['status'];
		
		$cond['req_id']=$resulttqr['req_id'];
		$cond['tqrid']=$tqrid;
		
    	if($status != 1){
    		$this->error('不能进行方案设计', U('ES/showEReq'));
    	}else{
    		$es = D('Agent/ES', 'Logic');
			$result = $es->preAddQSXScheme($cond);

			//兼容处理已经完成需求处理单的查询 David law 2015-2-4
			if(count($result)> 0){
				$result2= $es->queryQSXcheme(0);
				$count=count($result2);
				$Page  = new \Think\Page($count,5);
				$show  = $Page->show();// 分页显示输出
				$result2= $es->queryQSXcheme($Page);
				$this->assign('rec_status',true);
			}else{
				$result = $es->preAddQSXScheme($cond,false);
				$result2= $es->queryQSXcheme(0,false);
				$count=count($result2);
				$Page  = new \Think\Page($count,5);
				$show  = $Page->show();// 分页显示输出
				$result2= $es->queryQSXcheme($Page,false);
				$this->assign('rec_status',false);
			}

			$this->assign('qsx_rec',$result);
		    $this->assign('Page',$show);// 赋值分页输出
    		$this->assign('contentList',$result2);

    		//增加客服信息
			$name = array();
			$phone = array();
			$qq = array();
			for($i=0;$i<count($result,2);$i++){
				$qsx_req = M("qsx_req");
				$qro = $result[$i]['qsx_rq_no'];
				$result1 = $qsx_req->where('qsx_rq_no='."'$qro'")->find();
				$id = $result1['u_id'];
				$employee = M("employee");
				$result2 = $employee->where('u_id='.$id)->find();
				$this->assign ( 'info', $result2 );
				array_push($name,$result2['name']);
				array_push($phone,$result2['phone']);
				array_push($qq,$result2['qq']);
				$this->assign ( 'u_name', $name );
				$this->assign ( 'u_phone', $phone );
				$this->assign ( 'u_qq', $qq );
			}
    		//调用是否显示紧急预定
		   $emergencyM=D("Home/Emergency","Logic");
		   $booking=$emergencyM->emergency_book();
		   $this->assign('datt',$booking);
           $this->theme('agent')->display("qsx_solution_edit");
    	}
    }


    /**
     * 添加方案
     * 创建者：甘世凤
     * 2014-11-19下午03:04:16
     */
	public function  addScheme(){
		C('LAYOUT_ON',TRUE);
		//加载布局文件
		layout("tmc");
		$u_id=LI("userId");
	    $tmc_id=LI('tmcId');
		$req_id=$_POST['req_id'];
		$co_employee=M("employee");
		$reqM = M('qsx_req');
		$req = $reqM->where('id='.$req_id)->find();
		$userM = M('user');
		$user = $userM->where('id='.$req['u_id'])->find();
		$wx_openid=$user['wx_openid'];
		
		//生成方案号
		$data['qsx_sol_no']=VNumGen('qsx_sol');
		
		$data['req_id']=$req_id;
		$data['time'] =  date('Y-m-d H:i:s',time());
		$data['u_id']=$u_id;
		$data['content']=$_POST['content'];
		$data['status']=0;
		$scheme = M('qsx_solution');
		$id = $scheme->add($data);
		
		if($id!=0){

			// 郭攀 3.4
			// 添加方案时，给提交人发送短信和邮件的通知
			//申请通知预定是通过主网站预定，还是TMC旗舰店网站判断
			if(getHostedTmcInfo('name')){				
				$show_name=	getHostedTmcInfo('name');
			}else{
				$show_name="轻松行科技";
			}		
			//调用logic 里边封装的方法	
			$OP=D('Home/OpName','Logic');
			$result_op=$OP->tmcemp($req_id); //OP专员的信息	 					
			//根据req_id, 查询出预订者的邮箱，和电环号码
			$result_emp=$co_employee->where('u_id='.$user['id'])->find();//查询出预订者的相关信息
			 
			$sender = new UnifyMessageSender ();
			$types =  strlen($wx_openid) != 28 ? array(1,2) : array(1,2,3);
			$targets = strlen($wx_openid) != 28 ? array (1 => $result_emp['phone'], 2 =>$result_emp['email']) : array (1 => $result_emp['phone'], 2 =>$result_emp['email'], 3 => $wx_openid);
			$title =  'OP制作方案后，员工接到方案提醒';
			$text =  $result_emp['name'].':您提交的'.$result_op['content']."需求，已有".$result_op['tmc_name']."为您制作方案，请及时查看方案是否满意。(".$show_name.")";
			$html = $result_emp['name'].'：<br />您提交的'.$result_op['content']."需求，已有".$result_op['tmc_name']."为您制作方案，请及时查看方案是否满意。(".$show_name.")";

			$um = UnifyMessage::NewUnionMessage ( $types,$targets,$title,$text, $html );
			$sender->sendUMessage ($um);
			

			//调微信方法，将需求id传给微信
			if($req['req_from'] == 2){
				$wx= new \Mftont\Controller\WScheduleInterfaceController();
				$wx->callbackVoiceOrder($wx_openid,$req_id);
			}
					
    		$qs=M('');
    		$sql="select qs.id,qs.content,qs.u_id,qs.time from 73go_qsx_solution qs 
    		LEFT JOIN 73go_user u on qs.u_id = u.id where qs.`status`=0 and qs.id=".$id;
//   			echo $sql;
    		$result1=$qs->query($sql);
    		foreach ($result1 as $key=>$val){
			$sql2 = "select count(qs.id) as num  
				FROM 73go_qsx_solution qs 
    			LEFT JOIN 73go_user u  on qs.u_id =u.id 
				LEFT JOIN 73go_tmc_employee as temp ON temp.u_id=u.id
				LEFT JOIN 73go_tmc as tmc ON 73go_tmc.id=temp.tmc_id
				WHERE 73go_tmc.id=$tmc_id AND qs.`status`=0 AND qs.req_id = " .$req_id;
			$count = $qs->query($sql2);
			$result1[$key]['count'] = $count[0]['num'];
			
			$sql3 = "select name from 73go_tmc_employee  AS  a 
					WHERE  a.u_id = ".$val['u_id'];
			$names = $qs->query($sql3);
			$result1[$key]['tmc_emp_name'] = $names[0]['name'];
			};
    		
    	  	$this->ajaxReturn($result1);
		}else {
			$this->ajaxReturn(0);
		}
		
	
	}
	/**
	 * 取消方案
	 * 创建者：甘世凤
	 * 2014-11-21下午06:59:11
	 */
	public function cancel_scheme(){
		C('LAYOUT_ON',TRUE);
		//加载布局文件
		layout("tmc");
		$id=$_POST['id'];
		
		$qs=M('qsx_solution');
		$data['status'] = 3;
		$data['status_time']=date('Y-m-d H:i:s',time());
		$result=$qs->where('id='.$id)->save($data); 
		if($result){
			$this->ajaxReturn(1);
		}else{
			$this->ajaxReturn(0);
		}
		
	}
	
	/**
	 * 企业差旅政策显示
	 * 具体的人适用的差旅政策
	 * 创建者：甘世凤
	 * 2014-11-19下午05:19:10
	 */
	public function showTravelPolicy($req_id){
		C('LAYOUT_ON',TRUE);
		//加载布局文件
		layout("tmc");
		
//		$req_id=$_GET['req_id'];
		
		$datareq=M('qsx_req');
		$requestreq=$datareq->where("id=".$req_id)->find();
		$u_id=$requestreq['u_id'];
		
		$dataemp=M('employee');
		$requestemp=$dataemp->where("u_id=".$u_id)->find();
		$empid=$requestemp['id'];
		$coid=$requestemp['co_id'];
		
		$datacom=M('company');
		$requestcom=$datacom->where("id=".$coid)->find();

		$datauser=M('user');
		$requestuser=$datauser->where("id=".$u_id)->find();
		
		$this->assign('comcheck',$requestcom);
		$this->assign('usercheck',$requestuser);
		$this->assign('empcheck',$requestemp);
		
		$data['co_id']=$coid;
		$data['emp_id']=$empid;
		
		$datatravel=M('travel_policy');
		$request=$datatravel->where($data)->find();

		//兼容普通员工emp_id为空 David law 2015-2-4
		if(count($request) < 1){
			unset($data['emp_id']);
			$request=$datatravel->where($data)->find();
		}
		
		$this->assign('check',$request);
		
		$this->theme('agent')->display('co_travel_policy_show');
		
	}


   	//////////////////////
}