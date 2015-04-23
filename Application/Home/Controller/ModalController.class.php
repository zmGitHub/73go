<?php
/**
 * Created by PhpStorm.
 * User: Lanny Lee
 * Date: 2015/1/6
 * Time: 17:56
 */

namespace Home\Controller;
use Common\Logic\CommonLogic;
use Think\Controller;

class ModalController extends Controller{

    private $btnMap;

    private function showModal($page, $params) {
        layout("modal");
        //装入传来的buttons
        if ($_REQUEST['buttons']) $params['buttons'] = $_REQUEST['buttons'];
        else $params[buttons] = array('ok'); //如果没有参数，那么就只有一个确定按钮
        //div的ID
        if ($params["div_id"]) $this->assign("div_id", $params["div_id"]);
        //对话框标题
        if ($params["title"]) $this->assign("title", $params["title"]);
        //按钮处理
        if ($params["buttons"]) {
            $newBtns = array();
            foreach($params["buttons"] as $button) {
                $button = strtolower($button);
                if (array_key_exists($button, $this->btnMap))
                    $newBtns[$button] = $this->btnMap[$button];
            }
            $this->assign("buttons", $newBtns);
        }
        $this->theme("modal")->display($page);
    }

    public function __construct() {
        parent::__construct();
        //初始化按钮对照表
        $this->btnMap = array(
            "ok"=>"确定",
            "cancel"=>"取消",
            "yes"=>"是",
            "no"=>"否",
            "close"=>"关闭"
        );
    }

    public function demoModal() {
        $this->showModal("demo", $_REQUEST);
    }

    public function findEmp() {
        $currUserType = LI('userType');
        if ($currUserType == 3 || $currUserType == 4) {
            //此功能仅提供给Tmc用户以及TMC员工使用！
            $empId = LI('tmcempId');
            $m = new CommonLogic();
            //获得本TMC的协议客户的员工列表
            $emps = $m->getLinkedEmployees();
            if ($emps) $this->assign('emps',$emps);
            $pageParams['title'] = "添加旅客";
            $this->showModal("findEmp", $pageParams);
        }
    }


    public function findEmpFormal() {
        $currUserType = LI('userType');
        if ($currUserType == 3 || $currUserType == 4) {
            //此功能仅提供给Tmc用户以及TMC员工使用！
            $pageParams['title'] = "查找企业员工";
            $this->showModal("findEmpFormal", $pageParams);
        }
    }



}