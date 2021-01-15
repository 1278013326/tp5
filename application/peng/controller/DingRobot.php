<?php
namespace app\peng\controller;

use think\Controller;
class DingRobot extends Controller
{
    /**
     * 钉钉机器人手册地址：https://ding-doc.dingtalk.com/document/app/custom-robot-access
     * http://dev-tp5.com/index.php/peng/Ding_Robot/index.html
     */
    public function index()
    {
        $webhook = "https://oapi.dingtalk.com/robot/send?access_token=7577416d81fde319f6c8b4738360f1db335651efca136730b7a377966e49b97b";
        $message="我就是我, 是不一样的烟火业务报警测试";

        $data = array (
            'msgtype' => 'text',
            'text' => array (
                'content' => $message
            ),
            'at' => array(
                'atMobiles' => array(18911417865,17611102400),
                'isAtAll'=> 'false'
            )
        );

        $data_string = json_encode($data);
//print_r($data_string);die;
        $result = $this->request_by_curl($webhook, $data_string);
        echo $result;
    }
    public function request_by_curl($remote_server, $post_string) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remote_server);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Content-Type: application/json;charset=utf-8'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 线下环境不用开启curl证书验证, 未调通情况可尝试添加该代码
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}