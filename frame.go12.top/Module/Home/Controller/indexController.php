<?php
// 首页

namespace Home\Controller;

use Lib\Controller as Controller;


class indexController extends Controller
{

    public $tabletags = 'tags';
    public $tablebook = 'book';
    public $tablearticle = 'article';


    /**
     * 首页
     *
     * @return html
     */
    public function index()
    {
        global $_W,$_GPC;
        $list['tags'] = pdo_fetchall('select * from '.tablename($this->tabletags));
        $tagUrl = create_url('tags');
        $arUrl = create_url('article');

        $list['tj'] = pdo_fetchall('select id,img,name from '.tablename($this->tablebook).' order by m_tip desc limit 42');
        $list['tj2'] = pdo_fetchall('select id,name from '.tablename($this->tablebook).' order by w_tip desc limit 13');

        $list['zx'] = pdo_fetchall('select id,name,utime from '.tablename($this->tablebook).' order by id desc limit 20');
        $list['zx2'] = pdo_fetchall('select id,name,bid,utime,zid from '.tablename($this->tablearticle).' order by id desc limit 30');
        foreach ($list['zx2'] as $k=>$v){
            $arr = pdo_fetch('select a.author,a.tid,a.name as b_name,b.name as t_name from '.tablename($this->tablebook).' as a left join '.tablename($this->tabletags).' as b on a.tid=b.id where a.id ='.$v['bid']);
            $list['zx2'][$k] = array_merge((array)$list['zx2'][$k],(array)$arr);
        }

        include $this->display('index.html');
    }

    /**
     * 浏览历史
     */
    public function ajaxHistory(){
        if(empty($_COOKIE['aid'])){
           $msg = '无最近阅读记录~~~,喜欢就收藏一下吧!';
        }else{
            $arr = pdo_fetch('select id,bid,zid,name from '.tablename($this->tablearticle).' where id=:id',[':id'=>intval($_COOKIE['aid'])]);
            $name = pdo_fetchcolumn('select name from '.tablename($this->tablebook).' where id='.$arr['bid']);
            $msg = '最近阅读：<a href='.create_url('article',['aid'=>$arr['id'],'bid'=>$arr['bid']]).' >【'.$name.'】 '.'第'.$arr['zid'].'章 '.$arr['name'].'</a>';
        }
        json_dump('',$msg);
    }

    /**
     * 标签页
     */

    public function tags(){
        global $_W,$_GPC;
        if(empty($_GPC['id'])) $_GPC['id'] = 0;
        $id = intval($_GPC['id']);

        $list['tags'] = pdo_fetchall('select * from '.tablename($this->tabletags));
        $tagUrl = create_url('tags');
        $arUrl = create_url('article');
        empty($_GPC['page'])?$page = 1:$page = $_GPC['page'];
        $pageNum = 35;
        $limit = ($page-1)*35;

        $list['tj'] = pdo_fetchall('select id,img,name from '.tablename($this->tablebook).' order by m_tip desc limit 42');

        $list['tj2'] = pdo_fetchall('select id,img,name,utime from '.tablename($this->tablebook).' order by tip desc limit 30');

        if(!empty($_GPC['flag'])){
            switch ($_GPC['flag']){
                case 'gx':
                    $list['title'] = '最近更新';
                    $list['list'] = pdo_fetchall('select id bid,name b_name,status,author from '.tablename($this->tablebook).' order by utime desc limit '.$pageNum);
                    break;
                case 'tj':
                    $list['title'] = '本月推荐';
                    $list['list'] = pdo_fetchall('select id bid,name b_name,status,author from '.tablename($this->tablebook).' order by m_tip desc limit '.$pageNum);
                    break;
                case 'z_tj':
                    $list['title'] = '本周推荐';
                    $list['list'] = pdo_fetchall('select id bid,name b_name,status,author from '.tablename($this->tablebook).' order by w_tip desc limit '.$pageNum);
                case 'a_tj':
                    $list['title'] = '推荐小说';
                    $list['list'] = pdo_fetchall('select id bid,name b_name,status,author from '.tablename($this->tablebook).' order by tip desc limit '.$pageNum);
            }
            foreach ($list['list'] as $k=>$v){
                $_GPC['bid'] = $v['bid'];
                $arr = pdo_fetch('select id,name,utime,zid from '.tablename($this->tablearticle).' where bid='.$v['bid'].' order by bid desc');
                $list['list'][$k]['aid'] = $arr['id'];
                $list['list'][$k]['a_name'] = $arr['name'];
                $list['list'][$k]['utime'] = date('y-m-d',$arr['utime']);
                $list['list'][$k]['zid'] = $arr['zid'];
            }

        }else{
            if($id == 0){
                $list['title'] = '全本小说';
                $total = pdo_fetchcolumn('select count(1) from '.tablename($this->tablebook).' where tid=0');
                $list['pagelist'] = pagination($total,$page,$pageNum);
            }elseif ($id == 1){
                $list['title'] = '其它类型';
            }else{
                foreach ($list['tags'] as $v){
                    if($v['id']==$id){
                        $list['title'] = $v['name'];
                    }
                }
                if(empty($list['title'])){
                    $id = 0;
                    $list['title'] = '全本小说';
                }
            }
            $total = pdo_fetchcolumn('select count(1) from '.tablename($this->tablebook).' where tid=:tid',[':tid'=>$id]);
            $list['pagelist'] = pagination($total,$page,$pageNum);

            $list['list'] = pdo_fetchall('select id bid,name b_name,status,author from '.tablename($this->tablebook).' where tid=:tid order by w_tip desc limit '.$limit.','.($limit+$pageNum),[':tid'=>$id]);

            foreach ($list['list'] as $k=>$v){
                $_GPC['bid'] = $v['bid'];
                $arr = pdo_fetch('select id,name,utime,zid from '.tablename($this->tablearticle).' where bid='.$v['bid'].' order by bid desc');
                $list['list'][$k]['aid'] = $arr['id'];
                $list['list'][$k]['a_name'] = $arr['name'];
                $list['list'][$k]['utime'] = date('y-m-d',$arr['utime']);
                $list['list'][$k]['zid'] = $arr['zid'];
            }

        }



        $list['data'] = pdo_fetchall('select id,name from '.tablename($this->tablebook).' where tid=:tid',[':tid'=>$id]);

        include $this->display('tags.html');
    }

    /**
     * 栏目页
     */

    public function article(){
        global $_W,$_GPC;
        if(empty($_GPC['id'])) die;
        $id = intval($_GPC['id']);

        $list['tags'] = pdo_fetchall('select * from '.tablename($this->tabletags));
        $tagUrl = create_url('tags');
        $arUrl = create_url('article');


        $_GPC['bid'] = $id;
        $list['tj'] = pdo_fetchall('select id,img,name from '.tablename($this->tablebook).' order by m_tip desc limit 42');

        $list['tj2'] = pdo_fetchall('select id,img,name,utime from '.tablename($this->tablebook).' order by tip desc limit 30');

        $list['list'] = pdo_fetch('select * from '.tablename($this->tablebook).' where id=:id',[':id'=>$id]);

        $list['last'] = pdo_fetch('select id,zid,name from '.tablename($this->tablearticle).' where bid=:bid order by zid desc',[':bid'=>$id]);

        foreach ($list['tags'] as $v){
            if($v['id'] == $list['list']['tid']){
                $tagsArr = $v;break;
            }
        }

        include $this->display('article.html');
    }

    /**
     * 列表页
     */

    public function articleList(){
        global $_W,$_GPC;
        if(empty($_GPC['id'])) $_GPC['id'] = 0;
        $id = intval($_GPC['id']);

        $_GPC['bid'] = $id;

        $list['tags'] = pdo_fetchall('select * from '.tablename($this->tabletags));
        $tagUrl = create_url('tags');
        $arUrl = create_url('article');

        $list['tj'] = pdo_fetchall('select id,img,name from '.tablename($this->tablebook).' order by m_tip desc limit 42');

        $list['tj2'] = pdo_fetchall('select id,img,name,utime from '.tablename($this->tablebook).' order by tip desc limit 30');

        $list['list'] = pdo_fetch('select * from '.tablename($this->tablebook).' where id=:id',[':id'=>$id]);

        $list['aList'] = pdo_fetchall('select id,name,zid from '.tablename($this->tablearticle).' where bid=:bid order by zid asc',[':bid'=>$id]);

        foreach ($list['tags'] as $v){
            if($v['id'] == $list['list']['tid']){
                $tagsArr = $v;break;
            }
        }

        include $this->display('articleList.html');

    }

    /**
     * 详情页
     */

    public function detail(){
        global $_W,$_GPC;
        if(empty($_GPC['id'])) $_GPC['id'] = 0;
        $id = intval($_GPC['id']);
        $bid = intval($_GPC['bid']);

        $list['tags'] = pdo_fetchall('select * from '.tablename($this->tabletags));
        $tagUrl = create_url('tags');
        $arUrl = create_url('articleList');


        $list['arr'] = pdo_fetch('select * from '.tablename($this->tablearticle).' where id=:id',[':id'=>$id]);

        $list['list'] = pdo_fetch('select * from '.tablename($this->tablebook).' where id=:bid',[':bid'=>$bid]);

        foreach ($list['tags'] as $v){
            if($v['id'] == $list['list']['tid']){
                $tagsArr = $v;break;
            }
        }
        if(empty($list['arr'])) die;

        $last = pdo_fetch('select * from '.tablename($this->tablearticle).' where bid='.$list['arr']['bid'].' and zid<'.$list['arr']['zid']);
        $next = pdo_fetch('select * from '.tablename($this->tablearticle).' where bid='.$list['arr']['bid'].' and zid>'.$list['arr']['zid']);

        include $this->display('detail.html');
    }

}