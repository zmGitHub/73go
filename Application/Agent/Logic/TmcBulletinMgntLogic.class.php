<?php
namespace Agent\Logic;
use Think\Exception;
use Think\Model;

class TmcBulletinMgntLogic extends Model {
	//获取接受者为协议客户
	public function getRecv(){
		$tmc_id=LI('tmcId');
		$linkM=M('co_tmc_link');
		$res=$linkM->where("tmc_id=".$tmc_id)->select();
		foreach ($res as $key=>$val){
			$sql="select short_name from 73go_company where id=".$val['co_id'];
			$names=$linkM->query($sql);
			$res[$key]['short_name']=$names[0]['short_name'];
		}
		return $res;
	}
	
}