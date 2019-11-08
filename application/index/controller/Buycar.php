<?php


namespace app\index\controller;


class Buycar extends Base
{
    public function index()
    {
        return view('buycar');
    }
    public function buyCarTwo()
    {
        return view('buyCar_Two');
    }
    public function buyCarThree()
    {
        return view('buyCarThree');
    }
}