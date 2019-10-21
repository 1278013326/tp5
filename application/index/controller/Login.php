<?php
namespace app\index\controller;
use app\index\model\User;
use app\index\controller\Base;
use think\Db;
use think\Request;
use think\Controller;
class Login extends Base
{
    public function getCode(){
        import('Verification', EXTEND_PATH,'.php');
        $obj = new  \ValidateCode();
        $obj->doimg();
        $_SESSION['code'] = $obj->getCode();//验证码保存到SESSION中

    }
    public function index()
    {
        if($this->request->isPost()){
            //接值
//            1、print_r($_POST);die;
//            2、$param = $this->request->param();
//            3、$cid = input();
            if (empty($_POST['userinp'])) {
                return msg(1,'用户名不能为空');
            }
            if (empty($_POST['password'])) {
                return msg(1,'密码不能为空');
            }
            $name = trim($_POST['userinp']);
            $pwd = trim($_POST['password']);
            $model = new User();
            $userRes = $model->getUser($name);
            if (empty($userRes)) {
                return msg(1,'用户名错误');
            }
            $userInfoRes = $model->getUserInfo($name, $pwd);
            if (empty($userInfoRes)) {
                return msg(2,'密码错误');
            }
            return msg(0,'登录成功');
        }
        return view('index');
    }
}