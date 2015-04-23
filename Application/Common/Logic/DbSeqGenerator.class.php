<?php
namespace Common\Logic;

use Think\Model;

class DbSeqGenerator {
	
	private function getSeqTableName() {
		return C("DB_PREFIX")."seq_table";
	}
	
	public function __construct() {
		$m = M();

		//如果表不存在，则创建之
		$m->execute(
"  create table if not exists ".$this->getSeqTableName()." (
	  co_type int not null,
	  co_id int not null,
	  vno_str varchar(30) not null,
	  v_seq int,
	  primary key (co_type, co_id, vno_str)
  );		
"
		);
	}
	
	public function getNextSeq($coType, $coId, $vNoStr, $isCommit=false) {
		$m = M('seq_table');
		$cond['co_type'] = $coType;
		$cond['co_id'] = $coId;
		$cond['vno_str'] = $vNoStr;
		$map = $cond;
		$map['v_seq'] = 0;
		if ($rec = $m->where($cond)->find()) {
			$map['v_seq'] = $rec['v_seq'] + 1;
			if ($isCommit)
				$m->where($cond)->save($map);
		} else {
			$map['v_seq'] = 1;
			if ($isCommit)
				$m->add($map);			
		}
		return $map['v_seq'];
	}
	
}