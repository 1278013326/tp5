<?php
namespace app\peng\controller;

class Common extends Base
{
    /**
     * @param string $num
     * @return mixed|string
     * 数字转中文，只支持正整数
     */
    public function numToWord($num='19002') {
        return $this->numToWordBase($num);
    }

    /**
     * 获取腾讯视频源地址
     * @param $url 视频网址
     * @return video 视频源地址
     */
    public function qqVideoSource($url = 'https://v.qq.com/x/page/u3027w60ikp.html'){
        $video = $this->qqVideoSourceBase($url);
        $this->assign('video',$video);
        return view('entertainment/video');
    }
    /**
     * 音乐播放
     */
    public function music() {
        return view('entertainment/music');
    }

    /**
     *获取ip地址
     */
    public function getIp() {
        //ip是否来自共享互联网
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip_address = $_SERVER['HTTP_CLIENT_IP'];
        }
        //ip是否来自代理
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        //ip是否来自远程地址
        else {
            $ip_address = $_SERVER['REMOTE_ADDR'];
        }
        echo $ip_address;
    }

    /**
     * 爱奇艺
     */

}