<?php
/**
 * Created by PhpStorm.
 * User: 小浪浪
 * Date: 2019/10/10
 * Time: 15:40
 */
namespace app\index\model;

use think\Db;
use think\Model;

class User extends Model
{
    /**
     * @查询用户
     * @return array
     */
    public  function getUser($name)
    {
        $userInfo = Db::name('user')
            ->where('name',$name)
            ->find();
        return $userInfo;
    }
    /**
     * @查询用户信息
     * @return array
     */
    public function getUserInfo($name, $pwd) {
        $userInfo = Db::name('user')
            ->field('name','pwd')
            ->where('name',$name)
            ->where('pwd',$pwd)
            ->find();
        return $userInfo;
    }

}