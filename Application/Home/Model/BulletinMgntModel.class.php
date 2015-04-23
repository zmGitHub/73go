<?php
namespace Home\Model;

use Think\Exception;
use Think\Model;

class BulletinMgntModel extends Model {
	
	//添加公告
	public function addBulletin($data){
		$data['time']=date('Y-m-d H:i:s',time());
		$data['show_enable']=1;
		return $this->data($data)->add();
	}
	
	//获取公告
	public function getBulletin($cond){
	    
		$result = $this->where($cond)->order('CAST(LEVEL AS unsigned integer)  DESC,time DESC')->limit(0,30)->select();
		foreach ($result as &$val) {
			if(strlen($val['content_link']) > 4 ){
				substr($val['content_link'],0,4) != 'http' &&	$val['content_link'] = 'http://'.$val['content_link'];
			}else{
				$val['content_link'] = '';
			}
		}

		return $result;
	}
	
	public function deleteById($id){
		return $this->where('id='.$id)->delete();
	}
	
	public function update($id,$data){
		return $this->where('id='.$id)->data($data)->save();
	}
}