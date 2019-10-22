<?php
namespace app\index\controller;
use app\index\model\User;
use think\Db;
use think\Request;
use think\Controller;
class Address extends Controller
{
    //地址三级联动
    public function index()
    {
        return view('index');
    }
}