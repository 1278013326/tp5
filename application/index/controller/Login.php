<?php
namespace app\index\controller;
use app\index\model\User;
use think\Db;
use think\Request;
use think\Controller;
class Login extends Controller
{
    public function index()
    {
        if($this->request->isPost()){

            $model = new User();
            $a = $model->index();

        }
        return view('index');
    }
}