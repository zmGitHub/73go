<?php
namespace Home\Controller;
use System\LoginInfo;
use Think\Controller;
	
class BulletinMgntController extends Controller {
	
	public function getCoid(){
		$u_id = LI('userId');
		$companyM=M('company');
		$co_id=$companyM->where("u_id=".$u_id)->getField('id');
		return $co_id;
	}
	
	public function co_bulletin_mgnt(){
		$co_id=$this->getCoid();
		
		$bulletinM=M('bulletin_mgnt');
		$cond['send_id']=$co_id;
		$cond['send_type']=2;
		$bulletin = D('Home/BulletinMgnt');
		$result=$bulletin->getBulletin($cond);
		$this->assign('notices',$result);
		$this->theme('default')->display('co_notice_mgnt');
		
	}
	
	public function add_co_bulletin(){
		$co_id=$this->getCoid();
		$data['send_type']=2;
		$data['send_id']=$co_id;
		$data['recv_type']=2;
		$data['recv_id']=$co_id;
		$data['level']=$_POST['level'];
		$data['content']=$_POST['content'];
		$data['content_link']=$_POST['content_link'];
		$bulletin = D('Home/BulletinMgnt');
		$bulletin_id= $bulletin->addBulletin($data);
		if($bulletin_id){
			$this->success('添加成功');
		}else{
			$this->success('添加失败');
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