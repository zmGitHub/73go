<?php
/**
 * Created by PhpStorm.
 * User: Lanny Lee
 * Date: 2015/1/10
 * Time: 4:13
 */

namespace Home\Controller;


use Common\Logic\CommonLogic;
use Think\Controller;

class WidgetController extends Controller{

    /**
     * 为抓取的数据生成select控件的options列表。
     * @param $data 数据
     * @param $k key字段名
     * @param $v value字段名
     */
    private function printOptions($data, $k, $v) {
        if ($data) {
            $selected_id = $_REQUEST['s_id'];
            $selectOnce = false;
            if (!$selected_id) $selectOnce = true;
            foreach($data as $rec) {
                echo '<option value="'.$rec[$k].'"'.
                    (($selectOnce || $selected_id==$rec[$k])?' selected':'').'>'.$rec[$v]."</option>\r";
                $selectOnce = false;
            }
        }
    }

    public function tmcLinkedCompanies() {
        //统一采用创建指定的Logic类的方法，替代D('...')函数
        $m = new CommonLogic();
        //抓取当前系统用户所在的tmc关联的协议客户列表。
        $this->printOptions($m->getLinkedCompanies(), 'id', 'name');

    }

    public function dictOptions() {
        $group = $_REQUEST['group'];
        $m = new CommonLogic();
        //强制按照数字顺序排列！
        $this->printOptions($m->getDictOfGroup($group, 1), 'd_key', 'd_value');
    }

    /**
     * co_emps数据列表的html内容。
     * 要求前端传入coId参数
     */
    public function coEmpList()
    {
        $coId = $_REQUEST['coId'];
        if ($coId) {
            //统一采用创建指定的Logic类的方法，替代D('...')函数
            $m = new CommonLogic();
            $emps = $m->getEmpOfCom($coId);
            if ($emps) $this->assign('emps', $emps);
        }
        $this->theme("default")->display('coEmpList');
    }

    public function coOdrEmpList() {
        $coId = $_REQUEST['coId'];
        if ($coId) {
            //统一采用创建指定的Logic类的方法，替代D('...')函数
            $m = new CommonLogic();
            $emps = $m->getEmpOfCom($coId);
            if ($emps) $this->assign('emps', $emps);
        }
        $this->theme("default")->display('coOdrEmpList');
    }


    /**
     * 订单预订人的显示。
     * 要求必须具有参数uId<--此为emp
     */
    public function staOrderBooker() {
        $empId = $_REQUEST['uId'];
        if ($empId) {
            $m = new CommonLogic();
            $cond['id'] = $empId;
            $rec = $m->getEmps($cond);
            if ($rec) {
                $rec = $rec[0];
                echo '<strong class="ml_25">'.$rec['co_name'].'</strong>&nbsp;'.$rec['name'];
            }
        }
    }
}