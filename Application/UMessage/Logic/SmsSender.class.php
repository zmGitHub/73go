<?php
/**
 * Created by PhpStorm.
 * 消息发送服务-短消息发送类
 * User: Lanny Lee
 * Date: 2015/1/21
 * Time: 17:40
 *
 *
 * History:
 * 2015/1/21        Created. by Lanny Lee.
 */

namespace UMessage\Logic;


class SmsSender {


    /**
     * 短消息发送，这里约定送来的内容是utf-8编码。
     * 我们需要发送GB18030的内容，由此发送函数负责执行编码转换。
     * @param $target
     * @param $content
     */
    public static function send($target, $content) {
        /**
         * 当前使用的短信网关的格式字符串。
         * 约定
         *      参数[1]=>用户名
         *      参数[2]=>密码
         *      参数[3]=>手机号列表
         *      参数[4]=>发送的内容
         * @var string
         */
        static $gateway = "http://service.winic.org/sys_port/gateway/?id=%s&pwd=%s&to=%s&content=%s&time=";
        static $sms_userid = "sz73go";
        static $sms_upsw = "Futian73go";

        //执行url encoding
        $target = urlencode($target);
        //转码并执行url encoding
        $content = urlencode(iconv('UTF-8', 'GB18030', $content));

        $url = sprintf($gateway, $sms_userid, $sms_upsw, $target, $content);
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_HEADER, 0 );
        curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
        $file_contents = curl_exec ( $ch );
        curl_close ( $ch );

        //返回消息的处理
        //000/Send:1/Consumption:0/Tmoney:0 / sid
        //状态码 / 发送条数 / 当次消费金额 / 总体余额 / 短信编号
        $status = '000';

        if ($file_contents) {
            $results = explode('/', $file_contents);
            //取出第一个内容
            $status = $results[0];
        }

        //返回发送的状态
        return $status;
    }

}