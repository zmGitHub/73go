<?php
namespace Home\Logic;

use Home\Behaviors\VNumChecker;

use Common\Logic\DbSeqGenerator;

use Common\Logic\YMDBase62;

use Think\Exception;

use Home\Model\DictionaryModel;

class VNumGenerator {
	
	/**
	 * 公司类型，0=>企业, 1=>TMC
	 * @var int
	 */
	private $coType = 0;
	/**
	 * 公司ID
	 * @var int
	 */
	private $coId = 0;
	
	public function __construct($coType='', $coId='') {
		if (!empty($coType)) $this->coType = $coType;
		if (!empty($coId)) $this->$coId = $coId;
	}
	
	/**
	 * 根据单据标识，获取公式。
	 * 公式在字典表中，字典表的group为'vno_template'，取key=$ident的val作为该单据的公式。
	 * @param unknown_type $ident
	 */
	private function getFormula($ident) {
		$mDict = new DictionaryModel();
		$formula = $mDict->findByIdent('vno_template', $ident);
		if (empty($formula)) $formula = "散列(6)";
	}
	
	public function genVNum($ident, $isCommit='') {
		if (empty($ident)) {
			throw new Exception("单号生成器：必须传入单据标识($ident)。");
		}
		if (empty($isCommit)) {
			$isCommit = false;
		}
		
		//如果没有单据数据，则初始化数据，不能强制删除。
		VNumGenerator::initialDictionary();
		
		//从字典表抓取对应的编号公式
		$mDict = M('dictionary');
		$cond['d_group'] = 'vno_template';
		$cond['d_key'] = $ident;
		$dictItem = $mDict->where($cond)->find();

		if ($dictItem) {
			//找到标识，取得公式进行编号
			return $this->parseFormula($ident, $dictItem['d_value'], $isCommit);
		} else
			throw new Exception("标识为“".$ident."”的单据编号公式没找到，请检查字典表");
		return '';
	}
	
	private static $REGX_STRING = '/^\"([A-Z|a-z|0-9|\-]{1,})\"$/'; 
	private static $REGX_DATE = "/^(.*)\((\Y{1,4})(\M{1,2})(\D{1,2})\)$/";
	private static $REGX_NUM_FUNCTION = "/^(.*)\(([0-9]{1,})\)$/";
	
	/**
	 * 检查公式，公式必须是：
	 *   "XXXXX"=>用双引号圈住的字符串；
	 *   日期("YMD")
	 *   序列(N)
	 *   散列(N)
	 *   ----> 后续支持
	 *   公司(N)
	 *   部门(N)
	 * Enter description here ...
	 * @param unknown_type $part
	 */
	private function checkFormulaPart($part, &$matches) {
		$nMatches = preg_match_all(VNumGenerator::$REGX_STRING, $part,  $matches);
		if ($nMatches === 1) {
			//字符串型，双引号包裹
			return 1;
		}
		
		$nMatches = preg_match_all(VNumGenerator::$REGX_DATE, $part, $matches);
		if ($nMatches == 1) {
			//日期型，必须是"日期(Y[YYY]M[M]D[D])"
			if (($matches[0][0] === $part) && ($matches[1][0] === "日期"))
				return 2;
		}

		$nMatches = preg_match_all(VNumGenerator::$REGX_NUM_FUNCTION, $part, $matches);
		if ($nMatches == 1) {
			//函数型，必须是"<函数名>(N[NNN...])"
			if (($matches[0][0] === $part) && 
				(($matches[1][0] === "序列") || ($matches[1][0] === "散列"))) 
				return 3;
		}
		return 0;
	}
	
	private function wrongSyntax($part) {
		return "公式不正确：'".$part."'";
	}
	
	private function parseDatePart($YY, $MM, $DD) {
		$result = "";
		//年份生成
		if (!empty($YY)) {
			$lenYY = strlen($YY);
			switch ($lenYY) {
				case 1: 
					$result .= YMDBase62::getStrOfNumber(idate('Y', time()) - 2010);
					break;
				case 2:
				case 3:
				case 4:
					$result .= substr("".idate('Y', time()), 4 - $lenYY, $lenYY);
					break;
				default:
					throw new Exception("不正确的日期格式");
			}
		}
		
		//月份生成
		if (!empty($MM)) {
			$lenMM = strlen($MM);
			$mOfy = idate('m', time());
			switch ($lenMM) {
				case 1: 
					$result .= YMDBase62::getStrOfNumber($mOfy);
					break;
				case 2:
					$mmStr = "".$mOfy;
					if (strlen($mmStr) == 1) $mmStr = "0".$mmStr;
					$result .= $mmStr;
					break;
				default:
					throw new Exception("不正确的日期格式");
			}
		}

		//日生成
		if (!empty($DD)) {
			$lenDD = strlen($DD);
			$dOfm = idate('d', time());
			switch ($lenDD) {
				case 1: 
					$result .= YMDBase62::getStrOfNumber($dOfm);
					break;
				case 2:
					$mmStr = "".$dOfm;
					if (strlen($mmStr) == 1) $mmStr = "0".$mmStr;
					$result .= $mmStr;
					break;
				default:
					throw new Exception("不正确的日期格式");
			}
		}
		return $result;
	}
	
	private function formFixedNumber($number, $nNum) {
		$str = "".$number;
		$l = strlen($str);
		if ($l > $nNum) {
			$str = substr($str, 0, $nNum);	
		} else 
		if ($l < $nNum) {
			$nLoop = $nNum - $l;
			for ($i = 0; $i < $nLoop; $i++) {
				$str = '0'.$str;
			}
		}
		return $str;
	} 
	
	
	private function parseNumberFunction($funcName, $nNum, $ref, &$isRand=false, $isCommit=false) {
		if ($funcName === "序列") {
			$generator = new DbSeqGenerator();
			$number = $generator->getNextSeq($this->coType, $this->coId, $ref, $isCommit);
			$nDigits = (int) $nNum;
			return $this->formFixedNumber($number, $nDigits);
		} else 
		if ($funcName === "散列") {
			//构成 $iStart = 100...0, $iEnd =999...9
			$isRand = true; 
			$iStart = '1';
			$iEnd = '9';
			$nDigits = (int) $nNum;
			for ($i = 1; $i < $nDigits; $i++) {
				$iStart .= '0';
				$iEnd .= '9';
			}
			//生成的随机数，必然是nDigits位数的，不需要再做处理
 			return "".rand((int)$iStart, (int)$iEnd);
		} 
		return "";
	}

	private function doVNumCheck(&$params, $chkCfg) {
		if ($chkCfg) {
			if ($chkCfg) {
				$checker = new VNumChecker($chkCfg[0], $chkCfg[1]);
				$checker->run($params);
			}
		}
	}


	private function parseFormula($ident, $formula, $isCommit=false) {
		if (empty($formula)) return "";
		$reGen = false;
		//公式以.为拆分，这里跟php兼容
		do {
			$formulas = explode(".", $formula);
			$result = "";
			$needCheck = false;
			foreach ($formulas as $formulaPart) {
				$matches = array();
				//检查每个拆分的公式
				$nType = $this->checkFormulaPart($formulaPart, $matches);
				switch ($nType) {
					case 0:
						throw new Exception($this->wrongSyntax($formulaPart));
					case 1:
						//字符型
						$result .= $matches[1][0];
						break;
					case 2:
						//日期型
						$result .= $this->parseDatePart($matches[2][0], $matches[3][0], $matches[4][0]);
						break;
					case 3:
						//函数型
						$isRand = false;
						$result .= $this->parseNumberFunction($matches[1][0], $matches[2][0], $result, $isRand, $isCommit);
						if (!$needCheck) $needCheck = $isRand;
						break;
				}
			}
			
			if ($needCheck) {
				$reGen = !($this->checkIsVNumValids($ident, $result));
			}
		} while ($reGen);
		
		return $result;
	}

	/**
	 * 检查这个标识的单据给定的单据号是否符合要求。
	 * @param $ident
	 * @param $aVNum
	 * @return bool 是否正确
	 */
	private function checkIsVNumValids($ident, $aVNum) {
		$valids = false;
		//CheckPass预设为1，这样如果没有行为扩展，则直接通过
		$params = array (
			'Generated'=>$aVNum,
			'CheckPass'=>1,

		);

		$reGen = false;
		$vnoChkAry = (include APP_PATH.'Common/Conf/vno_check.php');
		$chkCfgs = $vnoChkAry[$ident];
		if ($chkCfgs) {
			if (is_array($chkCfgs[0])) {
				//支持多表检测
				//主要用于订单表中，联合 order_union 与 order 两个表一起需要唯一
				$valids = true;
				foreach ($chkCfgs as $chkCfg) {
					$this->doVNumCheck($params, $chkCfg);
					if ($params['CheckPass'] === 0) {
						$valids = false;
						break;
					}
				}
			} else {
				$this->doVNumCheck($params, $chkCfgs);
				$valids = $params['CheckPass'] != 0;
			}
		}
		return $valids;
	}

	/**
	 * 检查已经生成的单号，若不符合要求则重新生成。
	 * @param $ident 单据标识
	 * @param $currVNum 已有的单号
	 * @return mixed 合格的单号
	 */
	public function checkOrRegenVNum($ident, $currVNum, $isCommit) {
		if (($this->checkIsVNumValids($ident, $currVNum)))
			return $currVNum;
		else
			return $this->genVNum($ident, $isCommit);
	}


	public function testParse($aFormula, $isCommit=false) {
		$txt = $this->parseFormula($aFormula, $isCommit);
		echo "The Formula [".$aFormula."] is OK!"."<br/>\r";
		echo 'parse result is ['.$txt."]<br/>\r";
	}
	
	/**
	 * 增加一条单据编号规则记录
	 * @param Think\Model $model
	 * @param string $ident
	 * @param string $formula
	 */
	private static function addVNoTemplate(&$model, $ident, $formula) {
			$data['d_group'] = 'vno_template';
			$data['d_key'] = $ident;
			$data['d_value'] = $formula;
			$data['reserved'] = 1;
			$model->data($data)->add();
	}
	
	public static function initialDictionary($forceDelete=false) {
		$m = M('dictionary');
		$cond['d_group'] = 'vno_template';
		if ($forceDelete)
			$m->where($cond)->delete();
		if ($m->where($cond)->count() == 0) {
			//没有记录则插入
			//轻松行需求单
			VNumGenerator::addVNoTemplate($m, 'qsx_req', '"R".日期(YMD).散列(6)');
			//轻松行方案单
			VNumGenerator::addVNoTemplate($m, 'qsx_sol', '"S".日期(YMD).散列(6)');
			//订单
			VNumGenerator::addVNoTemplate($m, 'order', '"O".日期(YMD).散列(6)');
			//汇总订单
			VNumGenerator::addVNoTemplate($m, 'union_order', '"U".日期(YMD).散列(6)');
			//出差申请
			VNumGenerator::addVNoTemplate($m, 'travel_req', '"TR".日期(YMD).散列(6)');
			//公司编号
			VNumGenerator::addVNoTemplate($m, 'co_code', '散列(5)');
			//TMC公司编号
			VNumGenerator::addVNoTemplate($m, 'tmc_code', '散列(5)');
			
		}
		
		
	}
	
}