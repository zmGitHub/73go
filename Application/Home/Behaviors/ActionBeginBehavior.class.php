<?php
/**
 * Created by PhpStorm.
 * User: Lanny Lee
 * Date: 2015/1/15
 * Time: 16:04
 */

namespace Home\Behaviors;


use Think\Behavior;

class ActionBeginBehavior extends Behavior{

    //行为执行入口
    public function run(&$param){

        $isActionAllowed = function() {
            $_allowList = include APP_PATH.'Common/Conf/noneed_auth.php';
            foreach ($_allowList as $allowed) {
                if (MODULE_NAME === $allowed['m'] &&
                    ($allowed['c'] == '*' ||
                        (CONTROLLER_NAME === $allowed['c'] &&
                            ($allowed['a'] == '*' || ACTION_NAME === $allowed['a'])))) return true;
            }
            return false;
        };

        if ($isActionAllowed()) return;

        if (isServerOfHosting()) {
            //是TMC旗舰店模式的URL
            if (!getHostedTmcInfo() || !LI('userId')) {
                redirect(U('Home/Index/index'));
            }
        } else {
            //73GO模式
            if (!LI('userId'))
                redirect(U('Home/Index/index'));
        }
    }

}