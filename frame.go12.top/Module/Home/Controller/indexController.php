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
        global $_W;
        require_once SYSTEM_CLASS_PATH.'/api.class.php';
        $api = new \Api();
        dump($api->getIp('117.136.25.231'));
    }

    public function index2()
    {
        setLog(1);

    }

}