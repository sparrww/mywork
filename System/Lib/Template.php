<?php
//	模板基类

namespace Lib;

//定义模板路径

class Template extends Lib{

    private static $cplDir;// 编译目录
    private static $tplDir;// 编译目录
    private static $cplFilePostfix;// 编译文件扩展名

    /**
     * 构造函数
     */
    // function __construct($cplFilePostfix="php"){

    // }

    /**
     * 模板输出
     */
    public static function tpl($tpl_file){
        //定义模版路径
        self::$tplDir = VIEW_PATH.'/';              // 默认模板目录
        self::$cplDir = CACHE_VIEW_PATH.'/';        // 默认的编译目录
        self::$cplFilePostfix = "php";    // 默认后缀

        $tpl_path = self::getTplDir().$tpl_file;

        if(!file_exists($tpl_path)){
            throwexce(sprintf('Can not find the template file:'.$tpl_path.' Make sure the template file has been created.'));
        }
        return self::createFile($tpl_path);
    }

    /**
     * 设置模板目录
     */
    public static function setTplDir($dir){
        if(!self::mkDirs($dir)){
				throwexce(sprintf('Can not automatically create a template directory:'.$dir.' Can try to manually create or modify directory permissions.'));
        }
        self::$tplDir = $dir;
    }

    /**
     * 取得模板文件存放目录
     */
    public static function getTplDir(){
        return self::$tplDir;
    }

    /**
     * 设置编译目录
     */
    public static function setCplDir($dir){
        if(!self::mkDirs($dir)){
			throwexce(sprintf('Can not automatically create a build directory:'.$dir.', Can try to manually create or modify directory permissions'));
        }
        self::$cplDir = $dir;
    }

    /**
     * 取得编译文件存放目录
     */
    public static function getCplDir(){
        return self::$cplDir;
    }

    /**
     * 取无后缀的文件名
     */
    private static function cetFilePureName($file_name){
        $pfix_pos = strrpos($file_name, '.');
        return substr($file_name, 0, $pfix_pos);
    }

    /**
     * 设置编译文件后缀名
     */
    public static function setCplFilePostFix($postfix){
        if(!preg_match('/^[a-z][a-z0-9]*$/is', $postfix)){
			throwexce(sprintf('Compiled file extension errors, please use English letters and numbers to specify, and must begin with a letter in English'));
        }
        self::$cplFilePostfix = $postfix;
    }

    /**
     * 取得编译文件后缀名
     */
    public static function getCplFilePostFix(){
        if(empty(self::$cplFilePostfix)){
			throwexce(sprintf('Compile the file suffix is not specified, please call setCplFilePostFix specify the file extension is recommended to use php as the extension'));
        }
        return self::$cplFilePostfix;
    }

    /**
     * 按指定路径生成目录
     */
    private static function mkDirs($path){
        if(file_exists($path)){
            return $path;
        }
        $adir = explode('/',$path);
        $dir_list = '';
        foreach($adir as $k=>$v){
            if($v != '.' && $v != ".."){
                $sep = $k==0?'':'/';
                $dir_list .= $sep.$v;
            }else{
                $dir_list .= $v;
            }

            dump($dir_list);
            if(!file_exists($dir_list))
            {
                @mkdir($dir_list);
                @chmod($dir_list,0777);
            }
        }
        return $path;
    }

    /**
     * 写文件
     */
    private static function wFile($file_path, $str, $mode='w'){
        $oldmask = @umask(0);
        $fp = @fopen($file_path, $mode);
        @flock($fp, 3);
        if(!$fp){
            return false;
        }else{
            dump(fwrite($fp,$str));
            fclose($fp);
            @umask($oldmask);
            return true;
        }
    }

    /**
     * 生成文件
     */
    private static function createFile($tpl_path){
        global $_W,$_GPC;
        if(!file_exists($tpl_path)){
				throwexce(sprintf('Template file does not exist!'));
        }
        $info = pathinfo($tpl_path);
        $cpl_file = self::$cplDir.basename($tpl_path,'.'.$info['extension']).'.'.self::$cplFilePostfix;

        if(!file_exists($cpl_file) || (@filemtime($tpl_path) > @filemtime($cpl_file))){
            /**/
            $cplDir = self::getCplDir();// 取编译目录
            $tplDir = self::getTplDir();// 取模板目录

            $cpl_arr = array();
            $cpl_tmp_path = !empty($tplDir)
            && preg_match('~^'.preg_quote($tplDir).'(.*)~', $tpl_path, $cpl_arr)
                ?$cplDir.$cpl_arr[1]:$cplDir."/".$tpl_path;

            $cplDir_arr  = explode('/', $cpl_tmp_path);
            $file_name    = array_pop($cplDir_arr);// 文件名
            $file_pure_name   = self::cetFilePureName($file_name);
            $cplFilePostfix = self::getCplFilePostFix();

            // 建目录
            $cpl_file_dir = implode('/',$cplDir_arr);
            self::mkDirs($cpl_file_dir);
            $cpl_file = $cpl_file_dir."/".$file_pure_name.".".$cplFilePostfix;
            // 判断文件是否存在，不存在或过期就创建一个
            $parsed_str = self::parse($tpl_path);
            self::wFile($cpl_file, $parsed_str, 'w');
        }
        return $cpl_file;
    }

	/**
     * 解析文件
     */
    private static function parse($tpl_file){
        global $_W,$_GPC;

        if(!defined("TPL_INCLUDE")){
				throwexce(sprintf('For your security, please call before the procedure to define a template called TPL_INCLUDE constant'),'Template');
        }
        $d = "<?php defined('TPL_INCLUDE') OR exit('Access Denied'); ?>\r\n";

        $str = $d.file_get_contents($tpl_file);

        $str = preg_replace('/<!--{(.+?)}-->/s', '{$1}', $str);

        $str = preg_replace('/{php\s+(.+?)}/', '<?php $1?>', $str);
        $str = preg_replace('/{if\s+(.+?)}/', '<?php if($1) { ?>', $str);
        $str = preg_replace('/{else}/', '<?php } else { ?>', $str);
        $str = preg_replace('/{else ?if\s+(.+?)}/', '<?php } else if($1) { ?>', $str);
        $str = preg_replace('/{\/if}/', '<?php } ?>', $str);
        $str = preg_replace('/{loop\s+(\S+)\s+(\S+)}/', '<?php if(is_array($1)) { foreach($1 as $2) { ?>', $str);
        $str = preg_replace('/{loop\s+(\S+)\s+(\S+)\s+(\S+)}/', '<?php if(is_array($1)) { foreach($1 as $2 => $3) { ?>', $str);
        $str = preg_replace('/{\/loop}/', '<?php } } ?>', $str);
        $str = preg_replace('/{(\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)}/', '<?php echo $1;?>', $str);
        $str = preg_replace('/{(\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff\[\]\'\"\$]*)}/', '<?php echo $1;?>', $str);
        $str = preg_replace('/{([A-Z_\x7f-\xff][A-Z0-9_\x7f-\xff]*)}/s', '<?php echo $1;?>', $str);
        $str = str_replace('{##', '{', $str);
        $str = str_replace('##}', '}', $str);
        return $str;
    }

    /**
     *  过滤
     */
    private static function strip($expr, $statement){
        $expr = str_replace("\\\"", "\"", @preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr));
        $statement = str_replace("\\\"", "\"", $statement);
        return $expr.$statement;
    }
}



?>