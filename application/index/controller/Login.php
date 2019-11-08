<?php
namespace app\index\controller;
use app\index\model\User;
use app\index\controller\Base;
use think\Db;
use think\Request;
use think\Controller;
use think\Session;
class Login extends Base
{
    /*
     * 登录
     */
    public function index()
    {
        if($this->request->isPost()){
            if (empty($_POST['name'])) {
                return msg(1,'用户名不能为空');
            }
            if (empty($_POST['pwd'])) {
                return msg(1,'密码不能为空');
            }
            $name = trim($_POST['name']);
            $pwd  = trim($_POST['pwd']);
            $model   = new User();
            $userRes = $model->getUser($name);
            if (empty($userRes)) {
                return msg(1,'用户名错误');
            }
            $userInfoRes = $model->getUserInfo($name, $pwd);
            if (empty($userInfoRes)) {
                return msg(2,'密码错误');
            }
            //登录验证码
            if (!empty($_POST['code'])) {
                if (session('code') !== trim($_POST['code'])) {
                    return msg(3,'验证码错误');
                }
            }
            return msg(0,'登录成功');
        }
        return view('login');
    }
    /*
     * 获取登录验证码
     */
    public function getCode(){
        import('verification/Verification', EXTEND_PATH,'.php');
        $obj = new  \ValidateCode();
        $obj->doimg();
        session('code', $obj->getCode());
    }
    /*
     *注册
     */
    public function register() {
        return view('reg');
    }
    /*
     * 列表展示
     */
    public function showList() {

    }
}