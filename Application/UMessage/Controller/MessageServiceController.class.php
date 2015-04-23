<?php
/**
 * 消息服务控制器。
 * 主要利用Controller可以响应请求的特性，用以实现消息。
 *
 * 使用命令行运行方式，运行 UMessage/MessageService/start开启消息服务。
 *
 * 运行UMessage/MessageService/stop可关闭消息服务
 *
 * Created by PhpStorm.
 * User: Lanny Lee
 * Date: 2015/1/22
 * Time: 17:59
 *
 * History:
 * 2015/1/22        Created. by Lanny Lee
 */

namespace UMessage\Controller;


use UMessage\Logic\UnifyMessage;
use UMessage\Logic\UnifyMessageDispatcher;

class MessageServiceController {

    private $running = false;

    //启动消息发送服务
    public function start() {
        $checkInterval = C('73GO_MESSAGE_CHECK_INTERVAL') | '5';
        echo '获取配置，间隔='.$checkInterval."\r\n";
        $checkInterval = (int) $checkInterval;
        //$dispatcher = new UnifyMessageDispatcher();
        $this->running = true;
        while ($this->running) {
            flush();
            ob_end_flush();
            try {
                UnifyMessageDispatcher::sendPendingMessages();
            } catch (\Exception $ex) {
                echo '发生错误：'.$ex->getMessage();
            }
            //停止指定的间隔时间
            sleep($checkInterval);
        }
    }

    //停止消息服务
    public function stop() {
        $this->running = false;
    }

}