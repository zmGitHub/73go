<?php
namespace Agent\Controller;
use Think\Controller;
	
class TmcBulletinMgntController extends Controller {
	
	public function tmc_bulletin_mgnt(){
		layout('tmc');
		
		$tmc_id=LI('tmcId');
		
		//获取发布对象为协议客户
		$linkM=D('Agent/TmcBulletinMgnt','Logic');
		$coids=$linkM->getRecv();
		
		$bulletinM=M('bulletin_mgnt');
		$cond['send_id']=$tmc_id;
		$cond['send_type']=1;
		$bulletin = D('Home/BulletinMgnt');
		$result=$bulletin->getBulletin($cond);
		
		$comM=M('company');
		foreach ($result as &$val){
			$val['co_display'] = '';
			if($val['recv_type']==1 && $val['recv_id'] == $tmc_id){
				$val['co_display'] = '本公司员工';
			} else
			if($val['recv_type']==2 && $val['recv_id'] == 0){
				$val['co_display'] = '所有企业客户';
			} else
			if($val['recv_type']==2 && $val['recv_id']>0){
				$co_id=$val['recv_id'];
				$res=$comM->where("id=".$co_id)->find();
				if ($res) {
					$val['co_display'] = $res['short_name'];
				}
			} 
		}
		
		$this->assign('coids',$coids);
		$this->assign('tmc_id',$tmc_id);
		$this->assign('bulletins',$result);
		$this->theme('agent')->display('tmc_notice_mgnt');
		
	}
	
	public function add_tmc_bulletin(){
		$tmc_id=LI('tmcId');
		
		$data['send_type']=1;
		$data['send_id']=$tmc_id;
		
		if($_POST['recv_id']==-1){
			$recv_id=$tmc_id;
			$recv_type=1;
		}else{
			$recv_id=$_POST['recv_id'];
			$recv_type=2;
		}
		$data['recv_type']=$recv_type;
		$data['recv_id']=$recv_id;
		$data['level']=$_POST['level'];
		$data['content']=$_POST['content'];
		$data['content_link']=$_POST['content_link'];
		$bulletin = D('Home/BulletinMgnt');
		$bulletin_id= $bulletin->addBulletin($data);

		if($bulletin_id){
			$this->success('添加成功');
		}else{
			$this->error('添加失败');
		}
	}
	
	public function del_bulletin(){
		$bulletin_id=$_POST['id'];
		$bulletin = D('Home/BulletinMgnt');
		$res=$bulletin->deleteById($bulletin_id);
		if($res){
			$this->ajaxReturn(1);
		}else{
			$this->ajaxReturn(0);
		}
	}
	public function upd_bulletin(){
		$bulletin_id=$_POST['id'];
		$show_enable=$_POST['show_enable'];
		$data['show_enable'] = $show_enable==0?1:0 ;
		$bulletin = D('Home/BulletinMgnt');
		$res=$bulletin->update($bulletin_id,$data);
		if($res){
			$this->ajaxReturn(1);
		}else{
			$this->ajaxReturn(0);
		}
	}
}