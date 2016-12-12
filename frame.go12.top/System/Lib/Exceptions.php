<?php
//	异常处理

namespace Lib;

class Exceptions extends \Exception{

    protected $eval = '';
    protected $code = 0;

    public    $errorInfo = null; 
    protected $message;

    /**
     * 重定义构造器
     *
     * @param string $message
     * @param int $code
     * @return void
     */
	public function __construct($message = null, $code = 0){
        if (is_numeric($code)) {
            $this->code = $code;
        } else {
            $this->eval = $code;
        }

        parent::__construct($message, $this->code);
    }

    /**
     * 异常堆栈
     *
     * @return string
     */
	 final public function getStackTrace() {

        $string = $file = null;
        $traces = $this->getTrace(); unset($traces[0]);
        array_splice($traces, count($traces)-4, -1);
        foreach ($traces as $i => $trace) {
            $file = isset($trace['file']) ? replpath($trace['file']) : $file;
            $line = isset($trace['line']) ? $trace['line'] : null;
            $class = isset($trace['class']) ? $trace['class'] : null;
            $type = isset($trace['type']) ? $trace['type'] : null;
            $args = isset($trace['args']) ? $trace['args'] : null;
            $function = isset($trace['function']) ? $trace['function'] : null;
            $string .= "\t#" . $i . ' [' . date("y-m-d H:i:s") . '] ' . $file . ($line ? '(' . $line . ') ' : ' ');
            $string .= $class . $type . $function . '(';
            if (is_array($args)) {
                $arrs = array();
                foreach ($args as $v) {
                    if (is_object($v)) {
                        $arrs[] = implode(' ', get_object_vars($v));
                    } else {
                        $error_level = error_reporting(0);
                        $vars = print_r($v, true);
                        error_reporting($error_level);
                        while (strpos($vars, chr(32) . chr(32)) !== false) {
                            $vars = str_replace(chr(32) . chr(32), chr(32), $vars);
                        }
                        $arrs[] = $vars;
                    }
                }
                $string .= str_replace("\n", '', implode(', ', $arrs));
            }
            $string .= ")\r\n";
        }
        return $string;
    }

    /**
     * get eval
     *
     * @return string
     */
    final public function getEval() {
        return $this->eval;
    }

    /**
     * 自定义公用错误
     *
     * @return string
     */
	public static function error($message){
		return sprintf('%s: [%d]: %s', __CLASS__, 200, $message);
	}

    /**
     * 自定义输出
     *
     * @return string
     */
    public function __toString() {
        return sprintf('%s: [%d]: %s', __CLASS__, $this->code, $this->message);
    }
}

	/**
	 * 异常信息载入
	 *
     * @param object &$e
     * @return void
	 */
	function systemerror(&$e){
	    global $_W;

		$path=ERROR_PATH;

		if($_W['config']['setting']['debug']){

        $trace = $e->getTrace();
        krsort($trace);
        $trace[] = array('file' => $e->getFile(), 'line' => $e->getLine(), 'function' => 'break');
        $phpMsg = array();
            foreach ($trace as $error){
            if (!empty($error['function'])){
                $fun = '';
                if (!empty($error['class'])){
                    $fun .= $error['class'] . $error['type'];
                }
                $fun .= $error['function'] . '(';
                if (!empty($error['args'])){
                    $mark = '';
                    foreach ($error['args'] as $arg){
                        $fun .= $mark;
                        $fun .= var_export(str_replace(array("\t", "\r", "\n"), " ", $arg),true) . '\'';
                        $mark = ', ';
                    }
                }
                $fun .= ')';
                $error['function'] = $fun;
            }
            if (!isset($error['line'])) {
            continue;
            }
            $phpMsg[] = array('file' => str_replace(array(ROOT_PATH, '\\'), array('', '/'), $error['file']), 'line' => $error['line'], 'function' => $error['function']);
        }

            $trace = $e->getTrace(); $error = $trace[0];
            $Message=replPath($e->getMessage());$File=ReplPath($error['file']);$Line=$error['line'];
            $log = sprintf("[Message]:\r\n\t%s\r\n", $Message);
            $log.= sprintf("[File]:\r\n\t%s (%d)\r\n", $File, $Line);
            $log.= sprintf("[Trace]:\r\n%s\r\n", $e->getStackTrace());
            error_log($log, 3, LOG_PATH.'/'.date('Y-m-d').'_error.log');
            
            include_once $path. '/Error.php';
		}else{
			include_once $path. '/500.html';
		}
	}


	/**
	 * 异常信息抛出
	 *
     * @param string $error
     * @param num $errno
     * @return void
	 */
	function throwexce($error, $errno = 500){
		throw new Exceptions($error, $errno);
	}
