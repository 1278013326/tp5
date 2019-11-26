<?php


namespace app\index\controller;


use think\Controller;

class Base extends Controller
{
    public static function msg($code, $data) {
        return array(
            'code' => $code,
            'data' => $data,
        );
    }
}
