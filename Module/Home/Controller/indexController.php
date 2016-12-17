<?php
// 首页

namespace Home\Controller;

use Lib\Controller as Controller;


class indexController extends Controller
{

    /**
     * 首页
     *
     * @return html
     */
    public function index()
    {

        echo 123;
        $this->display('index.html');  //调取模版
    }

}