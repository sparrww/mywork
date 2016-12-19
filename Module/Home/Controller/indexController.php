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
        dump($_W['host'].create_url('index2'));
        echo '<script>window.location.href='.create_url('index2').'</script>';
        //include $this->display('index.html');
    }

    public function index2()
    {
        setLog(1);

    }

}