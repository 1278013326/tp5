<?php
namespace app\index\controller;
use app\index\model\User;
use think\Db;
use think\Request;
use think\Controller;
class Address extends Controller
{
    public function index()
    {
        return view('index');
    }
}