<?php
namespace Admin\Controller;
use Think\Controller;

class QsxBulletinMgntController extends Controller{

	public function qsx_bulletin_mgnt(){
		layout('admin');
		
		$u_id=LI('userId');

		$bulletinM=M('bulletin_mgnt');
		$cond['send_id']=$u_id;
		$cond['send_type']=0;
		$bulletin = D('Home/BulletinMgnt');
		$result=$bulletin->getBulletin($cond);
		
		$this->assign('bulletins',$result);
		$this->theme('admin')->display('qsx_bulletin_mgnt');
		
	}
	
	public function add_qsx_bulletin(){
		$u_id=LI('userId');
		
		$data['send_type']=0;
		$data['send_id']=$u_id;
		if($_POST['recv_id']==-1){
			$recv_id=0;
			$recv_type=1;
		}else{
			$recv_id=0;
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