<?php


namespace app\index\controller;


class Brand extends Base
{
    public function index()
    {
        return view('brand');
    }
    public function brandList()
    {
        return view('brandList');
    }
}