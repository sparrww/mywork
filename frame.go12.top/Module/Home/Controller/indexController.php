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
        create_url('index2');
        //include $this->display('index.html');
    }

    public function index2()
    {
        setLog(1);

    }

}