<?php
// 首页

namespace Home\Controller;

use Lib\Controller as Controller;


class indexController extends Controller
{
    const appId = 'wx6dc27ecef35b9ceb';
    const secret = '69eb5a53cdfc62d0b9507a6718c320eb';
    /**
     * 首页
     *
     * @return html
     */
    public function index()
    {
        global $_W;


    }

    public function onLogin()
    {
        global $_GPC;
        if(!empty($_GPC['code'])){
            $url = "https://api.weixin.qq.com/sns/jscode2session?appid=".self::appId."&secret=".self::secret."&js_code={$_GPC['code']}&grant_type=authorization_code";
           message(Post($url,false,1),'ajax') ;
        }

    }

}