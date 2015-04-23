<?php
namespace Home\Behaviors;

use Think\Exception;
use Think\Behavior;


/**
 * Class VNumChecker
 * 单号重复检查类，用于配合VNumGenerator类执行散列的单号生成中的重复检查。
 * @package Home\Behaviors
 */
class VNumChecker extends Behavior {
	
	private $table = '';
	private $field = '';
	
	public function __construct($table, $field) {
		$this->table = $table;
		$this->field = $field;	
	}
	
	/**
	 * 表名
	 */
	protected function getTableName() {
		return $this->table;	
	}
	/**
	 * 单据号字段名
	 */
	protected function getFieldName() {
		return $this->field;
	}

	/**
	 * 执行重复检查。
	 * @param mixed $params 其中 "Generated"是生成的单号，
	 *                           "CheckPass"是检验结果。
	 *                       这个参数是输入及输出双类型，检查不通过需要将"CheckPass"置为0
	 * @throws Exception
	 */
	public function run(&$params) {
		//echo "checking".$this->getTableName().".".$this->getFieldName(); 
		$tname = $this->getTableName();
		$fname = $this->getFieldName();
		if (empty($tname) || empty($fname)) {
			throw new Exception("必须配置表名和字段名，请检查。");
		}
		$model = M($tname);
		$cond[$fname] = $params['Generated'];
		//需要查询到空结果
		$found = $model->field('1')->where($cond)->select();
		//echo '检查重复'.$checkPass?"通过":"不通过";
		if ($found) $params['CheckPass'] = 0;
	}
	
}