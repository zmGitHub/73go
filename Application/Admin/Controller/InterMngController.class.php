<?php
namespace Admin\Controller;

use Home\Model\Model73go;

use Think\Model;
use Think\Controller;
use Home\Logic\SysFuncLogic;

class InterMngController extends Controller {
	
	public function showUtilsPage() {
		C('LAYOUT_ON',FALSE);
		$this->theme('admin')->display("internal_utils");
	}

	private function utilPage() {
		return U("Admin/InterMng/showUtilsPage");
	}
	
	
	/**
	 * 重建数据库中的系统菜单表项
	 * Enter description here ...
	 */
	public function buildMenus() {
		$l = new SysFuncLogic(); //M('SysFunc', 'Logic');
		$l->rebuildAll();
		$this->success("创建系统菜单成功。", $this->utilPage());
	}
	
	public function menuTest() {
		//if (isset($_REQUEST["userId"])) 				
	}
	
	private function testModel73go() {
		echo "About to Test Model73go<br/>\r";
		
/*		$m = new Model73go("user");
		$user = $m->findById(2);
		if ($user) {
			echo "User id=2 FOUND!\r";
			echo "username=".$user['username']."<br/>\r";		
		} else {
			echo "CAN NOT found User id=2!<br/>\r";		
		}*/

		$id = 1;
		$m = new Model73go('tmc');
		
		$tmc = $m->findById($id);
		if ($tmc) {
			echo "TMC id=".$id." FOUND! ";
			echo "name=".$tmc['name']."<br/>\r";		
		} else {
			echo "CAN NOT find TMC id=".$id."!<br/>\r";		
		}
		
		$m1 = new Model73go("sys_func");
		$id = 95;
		$sysFunc = $m1->findById($id);
		if ($sysFunc) {
			echo "Func id = ".$id." FOUND! ";
			echo "Caption = [".$sysFunc['caption']."]<br/>\r";
		} else {
			echo "CAN NOT find Sys Func id=".$id."!<br/>\r";		
		}
	}
	
	
	public function tempTest() {
	/**
	 * Model73go 调试完毕，测试通过！
	 *  2014-11-13 Lanny Lee
		$this->testModel73go();
		$this->success("Model73go 测试完成。", $this->utilPage(), 4);
	**/
	}
	
	public function ajaxTest() {
		$data['id'] = 100;
		$data['name'] = "Lanny Lee";
		$data['hometown'] = "广东台山";
		
		echo $this->ajaxReturn($data);
	}
	
}