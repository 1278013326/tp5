<?php
namespace app\index\controller;
//use app\index\model\Setting;
//use think\Db;

class Index
{
    public function index()
    {
        //链接数据库
//        $class_info = Db::name('classify')
//            ->field('name')
//            ->where('id',$id)
//            ->where('status','1')
//            ->find();
//        return $class_info;


        //接值
        //        if($this->request->isPost()){    //判断是否是post请求
        //1、  //$_POST

//     2\   $param = $this->request->param();
//          $curcate_id = isset($param['cate_id'])?$param['cate_id']:0;
        // 3、$cid = input('param.cid');


        //判断手机
//        if (\think\Request::instance()->isMobile()) {
//            $curcate_id = $curcate_id == 0 ? 11 : $curcate_id;
//        }

        //引用model
//        $setting = Setting::getSetting(8);

        //视图传值
//        $this->assign('title', $setting['title']);
        //跳转
        //$this->redirect('index/journalism/index');exit;
        return view('index');
    }

}
