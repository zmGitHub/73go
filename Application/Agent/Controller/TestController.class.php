<?php
namespace Agent\Controller;
use Home\Logic\VNumGenerator;

use Think\Controller;

class TestController extends Controller{
	
	public function  test(){
	
		VNumGenerator::initialDictionary(true);
		echo "success!";
	
	}

	public function testDummyOutput() {
		C('LAYOUT_ON',FALSE);
		echo $this->theme("agent")->fetch("dummy");
	}
	
	


}