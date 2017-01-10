<?php
// 首页

namespace Home\Controller;

use Lib\Controller as Controller;


class indexController extends Controller
{
    const appId = 'wx6dc27ecef35b9ceb';
    const secret = '69eb5a53cdfc62d0b9507a6718c320eb';

    public function __construct()
    {
        global $_W;
        $_W['config']['db']['master'] = [
            "host" =>  "qdm207196369.my3w.com",
            "username" => "qdm207196369",
            "password" => "197205169",
            "port" => "3306",
            "database" => "qdm207196369_db",
            'pconnect' => 0,
            "charset" => "utf8mb4",
        ];

        $_W['config']['db']['slave1'] = [
            "host" =>  "qdm207196369.my3w.com",
            "username" => "qdm207196369",
            "password" => "197205169",
            "port" => "3306",
            "database" => "qdm207196369_db",
            'pconnect' => 0,
            "charset" => "utf8mb4",
        ];
    }


    /**
     * 首页
     *
     * @return html
     */
    public function index()
    {
        global $_W;


    }


    //获取openid
    public function onLogin()
    {
        global $_GPC;
        if(!empty($_GPC['code'])){
            $url = "https://api.weixin.qq.com/sns/jscode2session?appid=".self::appId."&secret=".self::secret."&js_code={$_GPC['code']}&grant_type=authorization_code";
           message(Post($url,false,1),'ajax') ;
        }

    }

    /**
     * 日记列表
     *
     * @return html
     */
    public function diaryList()
    {
        global $_W,$_GPC;
        $list = pdo_fetchall('select ctime,utime,title,content from yzlr_diary where openid=:openid order by id desc',[
           ':openid'=>$_GPC['openid']
        ]);

        dump($list);
    }

}