<?php
namespace Home\Behaviors;
use Think\Behavior;
use Think\Log;

class A73goBehavior extends Behavior {

    /**
     * 不需要经过身份检验的PATH_INFO列表
     * @var array
     */
    private static $allowList = array(
        '/^\/$/',
        '/\/Home\/Index\/index([\.html]?)([\/]?)(.*)/',
        '/\/Home\/Index0\/(.*)/',
        '/\/Public\/index0\/(.*)'
    );

    //行为执行入口
    public function run(&$param){
        $path_info = $param;

        //在允许列表中的PATH_INFO，直接通过。
        foreach(A73goBehavior::$allowList as $allowed) {
            if (preg_match_all($allowed, $path_info, $matches)) {
                $a=1;
                return;
            }
        }

        if (isTMCHosting() && !getHostedTmcInfo() ||
            !LI('userId')) {
            $param = '/Home/Index/index';
        }
    }
}