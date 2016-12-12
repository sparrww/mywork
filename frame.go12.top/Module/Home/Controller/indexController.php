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
        pdo_insert('users',['first_name'=>'sww']);
        echo pdo_insertid();
    }

}