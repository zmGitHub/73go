<?php
namespace Home\Controller;

use Home\Logic\SysFuncLogic;

use Common\Logic\DbSeqGenerator;

use Common\Logic\YMDBase62;

use Home\Logic\VNumGenerator;

use Think\Controller;

class TestController extends Controller {
	
	public function testStr() {
		$str = '"我是 I am good!()"';
		echo 'First char is "'.$str{0}.'"'."<br/>\r";
	}
	
	public function testStr1() {
		$str = '序列(8)过';
		echo "原始字符串：".$str."<br/>\r";
		echo "  startwith(...) = ".(startWith($str, "序列(")?"True":"False")."<br/>\r";
		echo "  endwith(...) = ".(endwith($str, ")")?"True":"False")."<br/>\r";
	}
	
	private function printContent($var, $nLevel=0) {
		if (is_array($var)) {
			echo "(array[".$nLevel."])<br/>\r";
			$aryIdx = 0;
			foreach ($var as $varItm) {
				echo "(array Index ".$aryIdx++.")<br/>\r";
				$this->printContent($varItm, $nLevel + 1);
			}
		} else 
			echo "".$var."<br/>\r";
	}
	
	public function testStr2() {
		echo 'preg_match_all("/^(.*)\(([0-9]{1,})\)$/", "序列(8)", $matches) = ';
		$nMatches = preg_match_all("/^(.*)\(([0-9]{1,})\)$/", "序列(8)", $matches);
		
		echo "".$nMatches."<br/>\r";
		echo 'count($matches)='.count($matches)."<br/>\r";
		$this->printContent($matches, 0);
	}
	
	public function testStr3() {
		echo 'preg_match_all("/^(.*)\((Y?)(M?)(D?)\)$/", "日期(YMDD)", $matches) = ';
		$nMatches = preg_match_all("/^(.*)\((\Y{1,})(\M{1,})(\D{1,})\)$/", "日期(YMD)", $matches);
		
		echo "".$nMatches."<br/>\r";
		echo 'count($matches)='.count($matches)."<br/>\r";
		$this->printContent($matches, 0);
	}
	
	public function testStr4() {
		echo 'preg_match_all("/^\\\"([A-Z|a-z|0-9]{1,})\\\"$/", "\\\"DD\\\"", $matches) = ';
		$nMatches = preg_match_all('/^\"([A-Z|a-z|0-9]{1,})\"$/', '"DD"', $matches);
		
		echo "".$nMatches."<br/>\r";
		echo 'count($matches)='.count($matches)."<br/>\r";
		$this->printContent($matches, 0);
	}
	
	public function testVGen() {
		$formula = '"ODR".日期(YYMMDD).序列(4)';
		echo "The formula is [".$formula."]<br/>\r";
		$generator = new VNumGenerator();
		$generator->testParse($formula, true);
	}
	
	
	public function testBase62() {
		echo 'Number of "X" is '.YMDBase62::getNumberOfStr('X')."<br/>\r";
		echo 'Char of 38 is "'.YMDBase62::getStrOfNumber(38).'"'."<br/>\r";
	}
	
	public function testDBSeqGen() {
		$generator = new DbSeqGenerator();
		echo "Genrated Number is :".$generator->getNextSeq(0, 2, 'DD4B20')."<br/>\r";
		echo "Genrated Number is :".$generator->getNextSeq(0, 2, 'DD4B20')."<br/>\r";
		echo "Commited Number is :".$generator->getNextSeq(0, 2, 'DD4B20', true)."<br/>\r";
		echo "Commited Number is :".$generator->getNextSeq(0, 2, 'DD4B20', true)."<br/>\r";
		echo "Genrated Number is :".$generator->getNextSeq(0, 2, 'DD4B20')."<br/>\r";
	}
	
	public function testArray() {
		$list = array();
		$list[] = '123';
		dump($list);
		$list = array_merge($list, array('456', 'Yes', 'OK!'));
		dump($list);
	}
	
	public function initVNoData() {
		VNumGenerator::initialDictionary();
		$this->success("成功！");
	}
	
	public function initVnoDict() {
		VNumGenerator::initialDictionary(true);
		echo 'DONE';
	}
	
	public function testMenu() {
		$system = $_REQUEST['sys'];
		$sysFuncHandler = new SysFuncLogic();
		$menu = $sysFuncHandler->buildSystemMenu($system);
		var_dump($menu);
	}
	
	
	
	public function testGenVNo() {
		echo '轻松行需求:';
		$vno = VNumGen('qsx_req');
		echo $vno."\r";
		echo '差旅申请:';
		$vno = VNumGen('travel_req');
		echo $vno."\r";
	}
	
	
}