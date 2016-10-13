<?phpnamespace Lib;class Lib{	static protected $_instance = NULL;	/**	 * 运行	 *	 * @access public     * @return bool	 */	public function run(){        global $_GPC,$_W;        $param = [];        $param['controller'] = '\\'.$_GPC['act'].'\\Controller\\'.$_GPC['name'].CONTROLLER;        $param['operate']    = $_GPC['op'];        $_W['script_name'] = basename($_SERVER['SCRIPT_FILENAME']);        if(basename($_SERVER['SCRIPT_NAME']) === $_W['script_name']) {            $_W['script_name'] = $_SERVER['SCRIPT_NAME'];        } else if(basename($_SERVER['PHP_SELF']) === $_W['script_name']) {            $_W['script_name'] = $_SERVER['PHP_SELF'];        } else if(isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $_W['script_name']) {            $_W['script_name'] = $_SERVER['ORIG_SCRIPT_NAME'];        } else if(isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) === 0) {            $_W['script_name'] = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));        } else {            $_W['script_name'] = 'unknown';        }        $_W['script_name'] = htmlspecialchars($_W['script_name']);        //模版路径        $_W['mobile_cdn'] = $_W['config']['setting']['cdn']?$_W['config']['setting']['cdn'].'/Public/'.ucfirst($_GPC['act']).'/':$_W['config']['setting']['host'].'/Public/'.ucfirst($_GPC['act']).'/';        $_W['host'] = $_W['config']['setting']['host'];		spl_autoload_register(['Lib\Lib','__autoload']);		try {            $this->controller = new $param['controller'];			if(class_exists($param['controller'])){				//方法不存在，使用默认方法			   if(method_exists($this->controller, $param['operate'])){						return $this->controller->$param['operate']();				}else{					throwexce(sprintf('%s is not found in the %s!',$param['operate'], $param['controller']));				}			}else{				throwexce(sprintf('%s Object does not exist!', $param['controller']));			}        } catch (Exceptions $e) {            systemerror($e);        }                self::_header();	}    /**     * 自动加载类方法     *     * @access	private     * @static     * @param	string	$class_name     * @return	string     */    final public static function __autoload($class_name) {        $class_name = str_replace('\\','/',$class_name);        //系统文件调用        $sysyem_file = SYSTEM_PATH.'/'.$class_name.'.php';        $drive_file = SYSTEM_DRIVER_PATH.'/'.$class_name.'.php';        $controller_file = MODULE_PATH.'/'.$class_name.'.php';        //验证系统文        if(file_exists($controller_file)) {            require_once($controller_file);        }elseif(file_exists($sysyem_file)){            require_once($sysyem_file);        }elseif(file_exists($drive_file)){            require_once($drive_file);        }else{            throwexce(sprintf('%s File does not exist!', $class_name));        }    }	/**	 * 程序结束输出 header	 *	 * @access private	 * @static	 * @return void	 */    final private static function _header(){			// 程序必须开启缓冲区    		ob_start();            header('Content-Type: text/html; charset=utf-8');            // 清理缓冲区，输出到浏览器            ob_end_flush(); flush();        }	/**	 * 程序结束输出 header	 *	 * @access private	 * @static	 * @return void	 * demo $aa=Lib\Lib::getinstance()->A('class',Null,'Controller')->index();	 */    public function A($funcName,$array="",$ext=CONTROLLER){    	$definition = $funcName.$ext;		$funcFile=ROOT_PATH.'/Module/'.str_replace('\\','/',$funcName.$ext).'.php';		 if(file_exists($funcFile)){			include_once $funcFile;			if(class_exists($definition)){				return new $definition($array);			}else{				throwexce(sprintf('%s Class does not exist!', $funcName));			}		}else{				return new model(implode(',',$array));		}    }	/**     *  静态方法, 单例统一访问入口	 * 	 *  @access	public 	 *  @static     *  @return  object  返回对象的唯一实例     */	static public function getinstance() {			if (is_null(self::$_instance) || !isset(self::$_instance)) {				self::$_instance = new self();			}			return self::$_instance;	}}?>