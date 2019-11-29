<?php
namespace app\peng\controller;
use think\Controller;

class Base extends Controller
{
    /**
     * @param string $num 数字
     * @return mixed|string
     * 数字转中文，只支持正整数
     */
    public function numToWordBase($num)
    {
        $chiNum = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');
        $chiUni = array('','十', '百', '千', '万', '亿', '十', '百', '千');
        $num_str = (string)$num;
        $count = strlen($num_str);
        $last_flag = true; //上一个 是否为0
        $zero_flag = true; //是否第一个
        $temp_num = null; //临时数字

        $chiStr = '';//拼接结果
        if ($count == 2) {//两位数
            $temp_num = $num_str[0];
            $chiStr = $temp_num == 1 ? $chiUni[1] : $chiNum[$temp_num].$chiUni[1];
            $temp_num = $num_str[1];
            $chiStr .= $temp_num == 0 ? '' : $chiNum[$temp_num];
        }else if($count > 2){
            $index = 0;
            for ($i=$count-1; $i >= 0 ; $i--) {
                $temp_num = $num_str[$i];
                if ($temp_num == 0) {
                    if (!$zero_flag && !$last_flag ) {
                        $chiStr = $chiNum[$temp_num]. $chiStr;
                        $last_flag = true;
                    }
                }else{
                    $chiStr = $chiNum[$temp_num].$chiUni[$index%9] .$chiStr;

                    $zero_flag = false;
                    $last_flag = false;
                }
                $index ++;
            }
        }else{
            $chiStr = $chiNum[$num_str[0]];
        }
        return $chiStr;
    }

    /**
     * 获取腾讯视频源地址
     * @param $url 视频网址
     * @return video 视频源地址
     */
    public function qqVideoSourceBase($url){
        preg_match("/\/([0-9a-zA-Z]+).html/", $url, $match);
        $vid = $match[1];//视频ID
        $get_url = 'https://h5vv.video.qq.com/getinfo?';
        $real_url = 'http://ugcws.video.gtimg.com/%s?vkey=%s&br=56&platform=2&fmt=auto&level=0&sdtfrom=v5010&guid=a3527bbc8888951591bc3a67c2bc9c50';
        //获取真正的视频源地址
        $data = array(
            'platform' => 11001,
            'charge' => 0,
            'otype' => 'json',
            'ehost' => 'https://v.qq.com',
            'sphls' => 1,
            'sb' => 1,
            'nocache' => 0,
            '_rnd' => time(),
            'guid' => 'a3527bbc8888951591bc3a67c2bc9c50',
            'appVer' => 'V2.0Build9496',
            'vids' => $vid,
            'defaultfmt' => 'auto',
            '_qv_rmt' => 'jJPtBTyoA12993HPU=',
            '_qv_rmt2' => 'pS3QdOqZ150285Jdg=',
            'sdtfrom' => 'v5010'
        );
        $url = $get_url.http_build_query($data);
        $curl = curl_init ();
        if (stripos ($url, "https://" ) !== FALSE) {
            curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt ($curl, CURLOPT_URL, $url);
        curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec ($curl);
        $status = curl_getinfo ($curl);
        curl_close ($curl);
        if (intval ($status ["http_code"]) == 200) {
            $result = $content;
        } else {
            return false;
        }
        if(!empty($result)){
            $result = explode('=', $result);
            if(!empty($result) && !empty($result[1])){
                $json = substr($result[1], 0, strlen($result[1])-1);
                $json = json_decode($json, true);
                if(json_last_error() == 'JSON_ERROR_NONE'){
                    if(!empty($json['vl']['vi'][0]['fn']) && !empty($json['vl']['vi'][0]['fvkey'])){
                        $fn = $json['vl']['vi'][0]['fn'];
                        $fvkey = $json['vl']['vi'][0]['fvkey'];
                        $video = sprintf($real_url, $fn, $fvkey);
                    }
                }
            }
        }
        return $video;
    }
}