<?php


namespace app\index\controller;
use think\Controller;

class Base extends Controller
{

}
//返回值
function msg($code, $msg) {
    $res = array(
        'code' => $code,
        'msg'  => $msg,
    );
    return $res;
}