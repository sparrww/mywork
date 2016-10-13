<?php
// 函数库

defined('TPL_INCLUDE') or die('Restricted access');

/**
 * 匹配图片路径
 */
function matchPic($content){
    $content = stripslashes(htmlspecialchars_decode($content));
    preg_match_all('/<img([ ]+)src="([^\"]+)"/i',$content,$matches);
    return $matches[2];
}

/**
 * 前后截取
 */
function matchQH($start,$end,$str){
    preg_match('/'.$start.'([\s\S]*?)'.$end.'/',$str,$data);

    return  $data[2];

}

/**
 * @param $html
 * @param $str
 * @return string
 * 匹配标签
 */
function matchHtml($html,$str){
    preg_match('/<'.$html.'(.*)>(.*?)<\/'.$html.'>/iS', $str, $matches);
    return trim($matches[1]);
}