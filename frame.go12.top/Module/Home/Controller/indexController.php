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
        include_once SYSTEM_CLASS_PATH.'/pinyin.class.php';
        $pinyin = new \Pinyin();
        echo $pinyin->getPinyin('大苏打');
    }

}