<?php
/**
 * 真正的消息发送分发器。
 * 此类负责检查消息数据库，并按照不同的消息类型调用对应的接口进行实际的消息发送。
 *
 *
 * Created by PhpStorm.
 * User: Lanny Lee
 * Date: 2015/1/29
 * Time: 19:23
 */

namespace UMessage\Logic;

use Mfront\Controller\WPublicController;
use UMessage\Model\UnifyMessageModel;

class UnifyMessageDispatcher {

    public static function sendPendingMessages() {
        static $typeDesc;
        $typeDesc[C("73GO_UM_SMS")] = '短信';
        $typeDesc[C("73GO_UM_EMAIL")] = '邮件';
        $typeDesc[C("73GO_UM_WECHAT")] = '微信消息';

        $msg_queues[C("73GO_UM_SMS")] = array();
        $msg_queues[C("73GO_UM_EMAIL")] = array();
        $msg_queues[C("73GO_UM_WECHAT")] = array();

        //消息发送接口，需要返回true/false表示消息发送成功与否
        //短信
        $msgIntf[C("73GO_UM_SMS")] = function ($msg) {
            $sender = new SmsSender();
            $status = $sender->send($msg['msg_target'], $msg['msg_text']);
            return $status == '000';
        };

        //邮件
        $msgIntf[C("73GO_UM_EMAIL")] = function ($msg) {
            $sender = new EmailSender();
            $status = $sender->send($msg['msg_target'],$msg['msg_name'],$msg['msg_title'],$msg['msg_html']);
            return $status;
        };

        //微信消息
        $msgIntf[C("73GO_UM_WECHAT")] = function ($msg) {
            $sender = new WechatSender();
            $status = $sender->send($msg['msg_target'],$msg['msg_text']);

            return $status;
        };

        //echo "".date('Y-m-d H:i:s').'::消息发送分发器，执行消息检查'."\n";
        $m = new UnifyMessageModel();
        $msgs = $m->getPendingMessages();
        //分发到不同的队列中
        foreach($msgs as $msg) {
            $msg_queues[$msg['msg_type']][] = $msg;
        }

        foreach($msg_queues as $type=>$queue) {
            if (count($queue) > 0) {
                foreach($queue as $msg) {
                    try {
                        $m->messageSending($msg);
                        if ($msgIntf[$type]($msg)) {
                            $m->messageSent($msg);
                        }
                    } catch (\Exception $ex) {
                        echo "".date('Y-m-d H:i:s',getdate()).'::发送'.$typeDesc[$type].'异常，目标'.$msg['msg_target'],
                            '异常信息:'.$ex->getMessage()."\n";
                    }
                }
            }
        }
    }



}