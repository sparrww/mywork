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

        $_W['config']['db']['tablepre'] = '';
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


        echo 123;
    }


    //获取openid
    public function onLogin()
    {
        global $_GPC;
        if(!empty($_GPC['code'])){
            $url = "https://api.weixin.qq.com/sns/jscode2session?appid=".self::appId."&secret=".self::secret."&js_code={$_GPC['code']}&grant_type=authorization_code";
            $data = json_decode(Post($url,false,1),true);
            message(['data'=>$data,'type'=>'success'],'ajax') ;
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
        if(!empty($_GPC['openid'])){
            $list = pdo_fetchall('select ctime,utime,title,content,label from yzlr_diary where openid=:openid order by id desc',[
                ':openid'=>$_GPC['openid']
            ]);

           message(['list'=>$list,'type'=>'success'],'ajax');
        }
    }


    /**
     * 日记添加/修改
     *
     * @return html
     */
    public function diaryAdd()
    {
        global $_W,$_GPC;
        if(!empty($_GPC['openid']) && !empty($_GPC['title'])){
            if(empty($_GPC['id'])){
                pdo_insert('yzlr_diary',[
                    'title'=> $_GPC['title'],
                    'nickname'=>$_GPC['nickname'],
                    'openid'=>$_GPC['openid'],
                    'content'=>$_GPC['content'],
                    'ctime'=>TIMESTAMP,
                    'utime'=>TIMESTAMP,
                    'label'=>$_GPC['label'],
                ]);
                if(pdo_insertid()){
                    message(['id'=>pdo_insertid(),'type'=>'success'],'ajax');
                }else{
                    message(['message'=>'添加失败','type'=>'success'],'ajax');
                }
            }else{
                $result = pdo_update('yzlr_diary',[
                    'title'=> $_GPC['title'],
                    'content'=>$_GPC['content'],
                    'label'=>$_GPC['label'],
                    'ctime'=>TIMESTAMP,
                    'utime'=>TIMESTAMP
                ],['id'=>$_GPC['id'],'openid'=>$_GPC['openid']]);
                if($result){
                    message(['type'=>'success'],'ajax');
                }else{
                    message(['message'=>'更新失败','type'=>'success'],'ajax');
                }
            }
        }
    }

}