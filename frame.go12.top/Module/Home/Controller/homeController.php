<?php
/*******************************************************************
 * @authors Air
 * @date    2014-09-06
 * @copy    Copyright © 2013-2018 Powered by Air Web Studio
 *******************************************************************/
// 首页

namespace Home\Controller;

use Lib\Controller as Controller;

class homeController extends Controller
{


    /**
     * 首页
     *
     * @return html
     */
    public function index()
    {
        echo checkIp();
    }

}