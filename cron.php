<?php
define("TPL_INCLUDE",1);
define('ROOT_PATH',dirname(__FILE__));
// 引入口文件
require ROOT_PATH.'/System/cgi.php';

ini_set('max_execution_time', 3600*4);

$typeArr = [
    '异界大陆'=>2,
    '东方玄幻'=>2,
    '玄幻仙侠'=>2,
    '古典仙侠'=>3,
    '都市激战'=>4,
    '都市异能'=>4,
    '总裁豪门'=>4,
    '都市生活'=>4,
    '商业大亨'=>4,
    '灵异悬疑'=>9,
    '架空历史'=>5,
    '唯美纯爱'=>10,
    '灵异奇谈'=>6,
    '悲伤青春'=>10,
    '探险揭秘'=>6
];

$host = 'http://www.17k.com';
$str = icurl($host.'/mianfei/');

$startLen = strpos($str,'<div class="NS">');

$endLen =  strpos($str,'document.getElementById(\'GX_TAB\')');

$arListArr = substr($str,$startLen,$endLen-$startLen);

unset($str);

preg_match_all('/(http:\/\/www.17k.com\/book)([\s\S]*?)(html)/',$arListArr,$matches);

$arColumnUrl = array_unique($matches[0]);

unset($arListArr);
unset($matches);

if(empty($arColumnUrl) || count($arColumnUrl)>600) die;
foreach($arColumnUrl as $k=>$v){
    $str = icurl($v);
    preg_match('/<h1>([\s\S]*?)\>([\s\S]*?)<\/a>/',$str,$name);

    $bookArr = pdo_fetch('select id,utime from '.tablename('book').' where name="'.$name['2'].'"');

    preg_match('/blank">第([\s\S]*?)章/',$str,$num);
    preg_match('/已写([\s\S]*?)red">([\s\S]*?)<\/em>/',$str,$len);
    preg_match('/更新时间：([\s\S]*?)<\/span>/',$str,$utime);

    if(empty($num[1])) continue;

    $date = date_create_from_format('D M j G:i:s T Y', $utime['1']);
    $utime = strtotime(date_format($date, 'Y-m-d H:i:s'));

    $num = CnToInt($num[1]);
    if($bookArr){
        pdo_update('book',[
            'utime'=>$utime,
            'num'=>$num,
            'len'=>$len[2]
        ],['id'=>$bookArr['id']]);
    }else{
        preg_match('/作品类别([\s\S]*?)blank">([\s\S]*?)<\/a>/',$str,$type);
        preg_match('/sign">作者([\s\S]*?)blank">([\s\S]*?)<\/a>/',$str,$author);
        preg_match('/作品信息([\s\S]*?)blank">([\s\S]*?)<\/a>/',$str,$info);
        preg_match('/class="book" src="([\s\S]*?)"/',$str,$img);
        isset($typeArr[$type[2]])?$tid = $typeArr[$type[2]] :$tid = 1;

        pdo_insert('book',[
            'name'=>$name['2'],
            'tid'=>$tid,
            'ctime'=>TIMESTAMP,
            'utime'=>$utime,
            'num'=>$num,
            'author'=>$author[2],
            'info'=>$info[2],
            'img'=>$img[1],
            'len'=>$len[2]
        ]);

        $bookArr['id'] = pdo_insertid();
    }
    $arListUrl = str_replace('book','list',$v);
    preg_match_all('/(\/chapter\/[\s\S]*?.html)[\s\S]*?第([\s\S]*?)章/',icurl($arListUrl),$matches);
    unset($matches[0]);
    unset($matches[1][0]);
    unset($matches[2][0]);
    krsort($matches[1]);
    krsort($matches[2]);

    $_GPC['bid'] = $bookArr['id'];
    foreach($matches[1] as $k2=>$v2){
        $zid = CnToInt($matches[2][$k2]);
        if(pdo_fetch('select id from '.tablename('article').' where bid=:bid and zid=:zid',[':bid'=>$_GPC['bid'],':zid'=>$zid])){
            break;
        }else{
            $str = icurl($host.$v2);
            preg_match('/<h1[\s\S]*?章([\s\S]*?)<\/h1>/',$str,$arName);

            if(empty($arName[1])) continue;

            preg_match('/更新日期">([\s\S]*?)<\/div>/',$str,$utime);

            $date = date_create_from_format('D M j G:i:s T Y', $utime['1']);
            $utime = strtotime(date_format($date, 'Y-m-d H:i:s'));

            preg_match('/chapterContentWapper">([\s\S]*?)本书首发来自17K小说网/',$str,$content);

            pdo_insert('article',[
                'name'=>trim($arName[1]),
                'bid'=>$_GPC['bid'],
                'content'=>$content[1],
                'zid'=>$zid,
                'ctime'=>TIMESTAMP,
                'utime'=>$utime,
            ]);
        }
    }
}





