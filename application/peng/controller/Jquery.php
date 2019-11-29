<?php


namespace app\peng\controller;


class Jquery extends Base
{
    public function test() {
        return view('test');
    }
    /**
     * 时钟动画
     */
    public function clock() {
        return view('clock');
    }
    /**
     * 时钟动画1
     */
    public function clock1() {
        return view('clock1');
    }
    /**
     * 时钟动画2
     */
    public function clock2() {
        return view('clock2');
    }
    /**
     * 五子棋
     */
    public function goBang() {
        return view('gobang');
    }
    /**
     * 照片轮播图
     */
    public function album() {
        return view('album');
    }
    /**
     * HTML5的3d相册
     */
    public function album2() {
        return view('album2');
    }
    /**
     * 纯CSS3实现的立方体旋转相册
     */
    public function album3() {
        return view('album3');
    }
    /**
     * 堆雪人
     */
    public function snowman() {
        return view('snowman');
    }
}