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


class WechatSender {

    /**
     * 获取access_token
     */
    public function initAccessToken() {
        $data = F("wechat/data");

        if(time() >= $data['expire']){
            $config = C('THINK_WECHAT');
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$config['APP_ID']}&secret={$config['APP_SECRET']}";

            $data['time'] = time();
            $curl = new \Org\Util\Curl();
            $result = $curl->request($url);
            $result = json_decode ( $result, true );

            $data['access_token'] = $result["access_token"];
            $data['expires_in'] = $result["expires_in"];
            $data['expire'] = $data['time'] + $result["expires_in"];

            F("wechat/data", $data);
        }

        return  $data['access_token'];
    }


    /**
     * 短消息发送，这里约定送来的内容是utf-8编码。
     * 我们需要发送GB18030的内容，由此发送函数负责执行编码转换。
     * @param $target
     * @param $content
     */
    public static function send($target, $content) {
        $access_token = self::initAccessToken();
        $json_test  = '{"touser": "'.$target.'","msgtype": "text",	"text": {"content": "'.$content.'"}}';
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;

        $result = self::http_requestscheme($url,$json_test);
        $result = json_decode ( $result, true );

        return $result['errcode'] == 0 ? true : false;
    }

    /**
     * 处理客服接口cul封装的数据
     */
    public static function http_requestscheme($url,$data){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        if (curl_errno($curl)) {
            return 'Errno'.curl_error($curl);
        }
        curl_close($curl);
        return $result;
    }

}