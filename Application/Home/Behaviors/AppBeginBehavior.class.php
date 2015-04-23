<?php
/**
 * Created by PhpStorm.
 * User: Lanny Lee
 * Date: 2015/1/15
 * Time: 2:16
 */

namespace Home\Behaviors;


use Common\Logic\CommonLogic;
use Think\Behavior;

class AppBeginBehavior extends Behavior{

    //行为执行入口
    public function run(&$param){
        if (isServerOfHosting()) {
            C('TMC-HOSTING', 'Yes');
            if (preg_match_all('/\/([0-9]+)/', $_SERVER['REQUEST_URI'], $matches) ||
                preg_match_all('/\/([0-9]+)\/(.*)/', $_SERVER['REQUEST_URI'], $matches)) {
                $tmc_code = $matches[1][0];
                C('CURR-TMC', $tmc_code);
                $logic = new CommonLogic();
                $logic->obtainHostedTmcInfo($tmc_code);
            }
        }
    }
}