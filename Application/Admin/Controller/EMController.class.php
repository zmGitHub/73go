<?php
namespace Admin\Controller;

use Think\Controller;

/**
 * TMC企业信息管理
 * 员工管理Staff
 * 企业管理Enterprise
 * 部门管理Branch
 * 创建者：甘世凤
 * 创建时间：2014-11-5下午03:28:46
 *
 */
class EMController extends Controller
{
    /**
     * 显示系统用户信息
     * 创建者：甘世凤
     * 2014-11-7上午11:18:40
     */
    public function showSysuser()
    {
        //C('LAYOUT_ON',TRUE);
        //加载布局文件
        layout("admin");
        $user = M('user');
        $list = $user->select();
        $count = count($list);
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();// 分页显示输出
        $list = $user->order('id desc')->limit($Page->firstRow, $Page->listRows)->select();
        $this->assign('Page', $show);// 赋值分页输出
        $this->assign('sysuser', $list);
        $this->theme('admin')->display('qsx_sysuser_list');
    }

    /**
     * 显示日志列表
     * 创建者：许博士
     * 2015-03-19上午11:47:40
     */
    public function showLogs()
    {
        //C('LAYOUT_ON',TRUE);
        //加载布局文件
        layout("admin");
        $logs = M('logs');
        $list = $logs->select();
        $count = count($list);
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();// 分页显示输出
        $list = $logs->order('id desc')->limit($Page->firstRow, $Page->listRows)->select();
        foreach ($list as &$l) {
            $l['date'] = date("Y-m-d H:i:s", $l['addtime']);
        }
        $this->assign('Page', $show);// 赋值分页输出
        $this->assign('logs', $list);
        $this->theme('admin')->display('qsx_logs_list');
    }

    /**
     * 删除所选日志
     * 创建者：许博士
     * 2015-03-19下午05:13:40
     */
    public function deletesomeLogs()
    {
        $ids = $_POST['ids'];
        $id_array = explode(',', $ids);
        $logs = M('logs');
        $count = 0;
        $nowtime = time();
        for ($i = 0; $i < count($id_array) - 1; $i++) {
            $map['id']=$id_array[$i];
            $time_1=$nowtime-2592000;
            $map['addtime'] = array(array('gt',0),array('lt',$time_1)) ;
           $logs->where($map)->delete();
        }
        echo -1;
    }

    /**
     * 更改系统用户的状态
     * 创建者：甘世凤
     * 2014-11-7下午12:56:26
     */
    public function updateSUstatus()
    {
        $suid = $_POST['suid'];
        $status = $_POST['sustatus'];
        $data['status'] = $status == 0 ? 99 : 0;
        $User = M('user');
        $result = $User->where('id=' . $suid)->data($data)->save();
        echo $result;
    }

    /**
     * 重置密码
     * 创建者：甘世凤
     * 2014-11-7下午01:14:00
     */
    public function resetPwd()
    {
        $user = M('user');
        $suid = $_POST['suid'];
        $data['password'] = md5(C('DEFAULT_PASSWORD'));
        $result = $user->where('id=' . $suid)->data($data)->save();
        if ($result) {
            $this->ajaxReturn(1);
        } else {
            $this->ajaxReturn(0);
        }
    }

    /**
     * 显示企业信息
     * 创建者：甘世凤
     * 2014-11-7上午11:16:59
     */
    public function showEnterprise()
    {
        //C('LAYOUT_ON',TRUE);
        //加载布局文件
        layout("admin");
        $company = M('company');
        $list = $company->select();
        $count = count($list);
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();// 分页显示输出
        $list = $company->order('id desc')->limit($Page->firstRow, $Page->listRows)->select();

        foreach ($list as $key => $val) {
            $sql = "select COUNT(id) num from 73go_employee where co_id=" . $val['id'];
            $count = $company->query($sql);
            $list[$key]['count'] = $count[0]['num'];

            $sql2 = "select register_time from 73go_user where id=" . $val['u_id'];
            $time = $company->query($sql2);
            $list[$key]['register_time'] = $time[0]['register_time'];
        };
        $this->assign('Page', $show);// 赋值分页输出
        $this->assign('company', $list);
        $this->theme('admin')->display('qsx_co_list');
    }

    /**
     * 查询企业详情
     * $id(企业id)
     * 创建者：甘世凤
     * 2014-11-7下午03:57:24
     */
    public function showEnterpriseDetail($id)
    {
        //C('LAYOUT_ON',TRUE);
        //加载布局文件
        layout("admin");
        $company = M('company');
        $result = $company->where("id =" . $id)->find();
        $this->assign('com', $result);
        $this->theme('admin')->display('qsx_co_detail');
    }


    /**
     * 更改企业的状态
     * 创建者：甘世凤
     * 2014-11-7下午12:56:26
     */
    public function updateECstatus()
    {
        $ecid = $_POST['ecid'];
        $status = $_POST['ecstatus'];
        $data['status'] = $status == 0 ? 99 : 0;
        $company = M('company');
        $result = $company->where('id=' . $ecid)->data($data)->save();
        echo $result;
    }

    /**
     * 根据企业id查询企业员工
     * $id（企业id）
     * 创建者：甘世凤
     * 2014-11-10上午09:46:23
     */
    public function showStaffByEcId($id)
    {
        //C('LAYOUT_ON',TRUE);
        //加载布局文件
        layout("admin");
//		$employee=M('employee');
//		$result=$employee->where('u_id='.$u_id)->select();
        $cond['type'] = 1;
        $cond['id'] = $id;
        $employee = D('Admin/EM', 'Logic');
        $result = $employee->queryEmployee($cond, 0);
        $this->assign('employee', $result);
        $this->theme('admin')->findComName();
        $this->theme('admin')->display('qsx_co_emp_list');
    }

    /**
     * 查询公司名
     * 创建者：甘世凤
     * 2014-11-24下午07:32:33
     */
    public function findComName()
    {
//    	header("Content-Type:text/html; charset=UTF-8");
        $company = M('company');
//    	$list = $company->getField('id,name');
        $sql = "select id,`name` from 73go_company";
        $list = $company->query($sql);
        $this->assign('cnames', $list);
    }

    /**
     * 显示企业相关的员工信息
     * 创建者：甘世凤
     * 创建时间：2014-11-7上午09:34:26
     */
    public function showStaff()
    {
        //C('LAYOUT_ON',TRUE);
        //加载布局文件
        layout("admin");
        $employee = D('Admin/EM', 'Logic');
        $result = $employee->queryEmployee('', 0);
        $count = count($result);
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();// 分页显示输出
        $result = $employee->queryEmployee('', $Page);
        $this->assign('Page', $show);// 赋值分页输出
        $this->assign('employee', $result);
        $this->theme('admin')->findComName();
        $this->theme("admin")->display("qsx_co_emp_list");
    }

    /**
     * 根据员工状态与公司查询
     * 创建者：甘世凤
     * 2014-11-10下午01:31:13
     */
    public function showStaffByCon()
    {
        $cond['type'] = 2;
        $cond['co_id'] = $_POST['co_id'];
        $cond['status'] = $_POST['status'];
        $employee = D('Admin/EM', 'Logic');
        $result = $employee->queryEmployee($cond, 0);
        $this->ajaxReturn($result);
    }

    /**
     * 输入框查询
     * 创建者：甘世凤
     * 2014-11-13下午03:56:25
     */
    public function inputfind()
    {
        $cond['type'] = 3;
        $cond['cname'] = $_POST['cname'];
        $cond['sta'] = $_POST['type'];
        $employee = D('Admin/EM', 'Logic');
        $result = $employee->queryEmployee($cond, 0);
        $this->ajaxReturn($result);
    }

    /**
     * 查询企业员工详情
     * 创建者：甘世凤
     * 2014-11-7下午03:57:24
     */
    public function showStaffDetail($id)
    {
        //C('LAYOUT_ON',TRUE);
        //加载布局文件
        layout("admin");
        $employee = M('employee');
        $result = $employee->where("id =" . $id)->find();
        $this->assign('emp', $result);
        $this->theme('admin')->display('qsx_co_emp_detail');

    }

    /**
     * 更改企业员工的状态
     * 创建者：甘世凤
     * 2014-11-7下午12:56:26
     */
    public function updateEmpStatus()
    {
        $eid = $_POST['eid'];
        $status = $_POST['estatus'];
        $data['status'] = $status == 99 ? 0 : 99;
        $employee = M('employee');
        $result = $employee->where('id=' . $eid)->data($data)->save();
        echo $result;
    }

    /**
     * 显示TMC企业信息
     * 创建者：甘世凤
     * 创建时间：2014-11-7上午09:33:54
     */
    public function showTMCEnterprise()
    {
        //C('LAYOUT_ON',TRUE);
        //加载布局文件
        layout("admin");
        $tmc = D('Admin/EM', 'Logic');
        $result = $tmc->queryTMCEnterprise('', 0);
        $count = count($result);
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();// 分页显示输出
        $result = $tmc->queryTMCEnterprise('', $Page);
        $this->assign('Page', $show);// 赋值分页输出
        $this->assign('tmc', $result);
        $this->theme('admin')->display('qsx_tmc_list');
    }

    /**
     * 根据认证排序TMC企业信息
     * 创建者：甘世凤
     * 创建时间：2014-11-7上午09:33:54
     */
    public function showTMCPaixu()
    {
        //C('LAYOUT_ON',TRUE);
        //加载布局文件
        layout("admin");
        $cond['type'] = 1;
        $tmc = D('Admin/EM', 'Logic');
        $result = $tmc->queryTMCEnterprise($cond, 0);
        $this->ajaxReturn($result);
    }

    /**
     * 显示TMC企业详情
     * $id(企业id)
     * 创建者：甘世凤
     * 创建时间：2014-11-7上午09:33:54
     */
    public function showTMCDetail($id)
    {
        layout("admin");
        $tmcone = M('tmc');
        $result = $tmcone->where("id =" . $id)->find();
        $this->assign('tmcone', $result);
        $this->theme('admin')->display('qsx_tmc_detail');
    }

    //添加tmc 评分
    public function addTMCEval()
    {
        $tmc_evaluation = M('tmc_evaluation');
        $data['tmc_id'] = $_POST['tmc_id'];
        $data['star'] = $_POST['star'];
        $id = $tmc_evaluation->data($data)->add();
        if ($id != 0) {
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }

    /**
     * 查询TMC名称
     * 创建者：甘世凤
     * 2014-11-11下午02:55:41
     */
    public function findTMCName()
    {
        $tmc = M('tmc');
        $sql = "select id,`name` from 73go_tmc";
        $result = $tmc->query($sql);
        $this->assign('tmcnames', $result);
    }

    /**
     * 更改TMC企业的状态
     * 创建者：甘世凤
     * 2014-11-7下午12:56:26
     */
    public function updateTMCStatus()
    {
        $tmcid = $_POST['tmcid'];
        $status = $_POST['status'];
        $data['status'] = $status == 0 ? 99 : 0;
        $tmc = M('tmc');
        $result = $tmc->where('id=' . $tmcid)->data($data)->save();
        echo $result;
    }

    /**
     * 根据TMC企业id查询相关的员工信息
     * $id(TMC企业id)
     * 创建者：甘世凤
     * 创建时间：2014-11-7上午09:34:26
     */
    public function showTMCStaffBytid($id)
    {
        layout("admin");
        $cond['type'] = 1;
        $cond['id'] = $id;
        $tmc_emp = D('Admin/EM', 'Logic');
        $result = $tmc_emp->queryTMCStaff($cond);
        $count = count($result);
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();// 分页显示输出
        $result = $tmc_emp->queryTMCStaff($cond, $Page);
        $this->assign('Page', $show);// 赋值分页输出
        $this->assign('tmcemp', $result);
        $this->theme('admin')->findTMCName();
        $this->theme('admin')->display('qsx_tmc_emp_list');
    }

    /**
     * TMC企业相关的员工信息
     * 创建者：甘世凤
     * 创建时间：2014-11-7上午09:34:26
     */
    public function showTMCStaff()
    {
        layout("admin");
        $tmc_emp = D('Admin/EM', 'Logic');
        $result = $tmc_emp->queryTMCStaff('', 0);
        $this->assign('tmcemp', $result);
        $this->theme('admin')->findTMCName();
        $this->theme('admin')->display('qsx_tmc_emp_list');
    }

    /**
     * 根据TMC查询员工
     * 创建者：甘世凤
     * 2014-11-11下午02:14:17
     */
    public function showTMCStaffByName()
    {
        $cond['type'] = 1;
        $cond['id'] = $_POST['id'];
        $tmc_emp = D('Admin/EM', 'Logic');
        $result = $tmc_emp->queryTMCStaff($cond);
        $this->assign('tmcemp', $result);
        $this->ajaxReturn($result);
    }

    /**
     * 输入查询
     * 创建者：甘世凤
     * 2014-11-26下午02:36:17
     */
    public function tmcInput()
    {
        $cond['type'] = 2;
        $cond['name'] = $_POST['tmcname'];
        $tmc_emp = D('Admin/EM', 'Logic');
        $result = $tmc_emp->queryTMCStaff($cond);
        $this->assign('tmcemp', $result);
        $this->ajaxReturn($result);
    }

    /**
     * 显示TMC企业员工详情
     * $id(企业员工id)
     * 创建者：甘世凤
     * 创建时间：2014-11-7上午09:33:54
     */
    public function showTMCStaffDetail($id)
    {
        layout("admin");
        $tmcempone = M('tmc_employee');
        $result = $tmcempone->where("id =" . $id)->find();
        $this->assign('tmcempone', $result);
        $this->theme('admin')->display('qsx_tmc_emp_detail');
    }

    /**
     * 更改TMC企业员工的状态
     * 创建者：甘世凤
     * 2014-11-7下午12:56:26
     */
    public function updateTMCStaffStatus()
    {
        $teid = $_POST['teid'];
        $status = $_POST['status'];
        $data['status'] = $status == 99 ? 0 : 99;
        $tmcemp = M('tmc_employee');
        $result = $tmcemp->where('id=' . $teid)->data($data)->save();
        echo $result;
    }

    /**
     * 根据TMC企业id查询相关的TMC协议客户信息
     * 创建者：甘世凤
     * 创建时间：2014-11-7上午09:34:26
     */
    public function showTMCLinkBytid($id)
    {     //这是tmcId
        layout("admin");
        $cond['type'] = 1;
        $cond['id'] = $id;
        $tmclink = D('Admin/EM', 'Logic');
        $result = $tmclink->queryTMCLink($cond, 0);
        $count = count($result);
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();// 分页显示输出
        $result = $tmclink->queryTMCLink($cond, $Page);
        $this->assign('Page', $show);// 赋值分页输出
        $this->assign('tmclink', $result);
        $this->theme('admin')->findTMCName();
        $this->theme('admin')->display('qsx_tmc_co_list');
    }

    /**
     * 查询TMC协议客户信息
     * 创建者：甘世凤
     * 创建时间：2014-11-7上午09:34:26
     */
    public function showTMCLink()
    {
        layout("admin");
        $tmclink = D('Admin/EM', 'Logic');
        $result = $tmclink->queryTMCLink();
        $this->assign('tmclink', $result);
        $this->theme('admin')->findTMCName();
        $this->theme('admin')->display('qsx_tmc_co_list');
    }

    /**
     * 根据TMC企业与状态查询相关的TMC协议客户信息
     * 创建者：甘世凤
     * 2014-11-11下午02:14:17
     */
    public function showTMCLinkByCon()
    {
        $tmcid = $_POST['tmcid'];
        $status = $_POST['status'];
        $cond['type'] = 2;
        $cond['id'] = $tmcid;
        $cond['status'] = $status;
        $tmclink = D('Admin/EM', 'Logic');
        $result = $tmclink->queryTMCLink($cond, 0);
        $this->ajaxReturn($result);
    }

    /**
     * 协议输入框查询
     * 创建者：甘世凤
     * 2014-11-14下午03:12:39
     */
    public function linkInput()
    {
        $tmcname = $_POST['tmcname'];
        $status = $_POST['status'];
        $cond['type'] = 3;
        $cond['name'] = $tmcname;
        $cond['status'] = $status;
        $tmclink = D('Admin/EM', 'Logic');
        $result = $tmclink->queryTMCLink($cond, 0);
        $this->ajaxReturn($result);
    }

    /**
     * 更改TMC认证结果为已认证
     * 创建者：甘世凤
     * 2014-11-11下午03:59:06
     */
    public function TMCCertPass()
    {
        //$str=$_POST['str'];
        $tmcid = $_REQUEST['tmcid'];
        $cert = $_REQUEST['cert'];
        $tmc = M('tmc');

        $data['cert_val'] = $cert;
        $result = $tmc->where('id=' . $tmcid)->save($data);

        //查询出TMC 公司的相关信息
        $user = M("user");
        $res = $tmc->where("id=" . $tmcid)->find();
        //查询出TMC公司是 否具有 wx_openid；
        $wx_openid = $user->where("id=" . $res['u_id'])->find();


        if ($result) {
            //TMC 公司认证通过的消息通知 cert=1
            //需要传的参数
            //TMC 邮箱$data['tmc_email'], TMC 电话 $data['tmc_phone']，TMC公司的 $data['wx_openid'];
            //TMC 公司的名字 $data['tmc_name'];
            //郭攀
            if ($cert == 1) {
                // 查询出TMC公司的邮箱，电话和对应的用户表中的wx_openid；
                $send_1 = D("Home/SendMessage", "Logic");
                $case = "TmcCertOk";
                $datt['tmc_phone'] = $res['contact_phone'];
                $datt['tmc_email'] = $res['contact_email'];
                $datt['tmc_name'] = $res['name'];
                $datt['wx_openid'] = $wx_openid['wx_openid'];
                $send_1->SendDetails($case, $datt);

            }
            //TMC 公司认证未通过的消息通知   cert=3
            //需要传的参数
            //TMC 邮箱$data['tmc_email'], TMC 电话 $data['tmc_phone']，TMC公司的 $data['wx_openid'];
            //TMC 公司的名字 $data['tmc_name'];
            if ($cert == 3) {
                $send_2 = D("Home/SendMessage", "Logic");
                $case = "TmcCertNo";
                $datt['tmc_phone'] = $res['contact_phone'];
                $datt['tmc_email'] = $res['contact_email'];
                $datt['tmc_name'] = $res['name'];
                $datt['wx_openid'] = $wx_openid['wx_openid'];
                $send_2->SendDetails($case, $datt);

            }

            $tcr = M('tmc_cert_request');

            $result2 = $tcr->where('tmc_id=' . $tmcid)->delete();

            $this->redirect('EM/showTMCDetail', array('id' => $tmcid));
        } else {
            $this->redirect('EM/showTMCDetail', array('id' => $tmcid));
        }

//    	$sql2="delete from 73go_tmc_cert_request  where tmc_id = ".$tmcid;
//    	
//    	$result2=$tcr->execute($sql2);

        //echo $result.$result2;

    }


}