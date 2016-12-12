<?phpnamespace Lib;class Lib{	static protected $_instance = NULL;	/**	 * 运行	 *	 * @access public     * @return bool	 */	public function run(){        global $_GPC,$_W;        $param = [];        $param['controller'] = '\\'.$_GPC['act'].'\\Controller\\'.$_GPC['name'].CONTROLLER;        $param['operate']    = $_GPC['op'];        //模版路径        $_W['mobile_cdn'] = $_W['config']['setting']['cdn']?$_W['config']['setting']['cdn'].'/Public/'.ucfirst($_GPC['act']).'/':$_W['config']['setting']['host'].'/Public/'.ucfirst($_GPC['act']).'/';        $_W['host'] = $_W['config']['setting']['host'];		spl_autoload_register(['Lib\Lib','__autoload']);		try {		    dump($param);die;            $controller = new $param['controller'];			if(class_exists($param['controller'])){			   if(method_exists($controller, $param['operate'])){					return $controller->$param['operate']();				}else{					throwexce(sprintf('%s is not found in the %s!',$param['operate'], $param['controller']));				}			}else{				throwexce(sprintf('%s Object does not exist!', $param['controller']));			}        } catch (Exceptions $e) {            systemerror($e);        }        self::_header();	}    /**     * 自动加载类方法     *     * @access	private     * @static     * @param	string	$class_name     * @return	string     */    final public static function __autoload($class_name) {        //系统文件调用        $sysyem_file = SYSTEM_PATH.'/'.$class_name.'.php';        $drive_file = SYSTEM_DRIVER_PATH.'/'.$class_name.'.php';        $controller_file = MODULE_PATH.'/'.$class_name.'.php';        $class_file = SYSTEM_CLASS_PATH.'/'.$class_name.'.php';        //验证系统文        if(file_exists($controller_file)) {            require_once($controller_file);        }elseif(file_exists($sysyem_file)){            require_once($sysyem_file);        }elseif(file_exists($sysyem_file)){            require_once($class_file);        }elseif(file_exists($drive_file)){            require_once($drive_file);        }else{            throwexce(sprintf('%s File does not exist!', $class_name));        }    }	/**	 * 程序结束输出 header	 *	 * @access private	 * @static	 * @return void	 */    final private static function _header(){			// 程序必须开启缓冲区    		ob_start();            header('Content-Type: text/html; charset=utf-8');            // 清理缓冲区，输出到浏览器            ob_end_flush(); flush();        }	/**	 * 程序结束输出 header	 *	 * @access private	 * @static	 * @return void	 * demo $aa=Lib\Lib::getinstance()->A('class',Null,'Controller')->index();	 */    public function A($funcName,$array="",$ext=CONTROLLER){    	$definition = $funcName.$ext;		$funcFile=ROOT_PATH.'/Module/'.str_replace('\\','/',$funcName.$ext).'.php';		 if(file_exists($funcFile)){			include_once $funcFile;			if(class_exists($definition)){				return new $definition($array);			}else{				throwexce(sprintf('%s Class does not exist!', $funcName));			}		}else{				return new model(implode(',',$array));		}    }	/**     *  静态方法, 单例统一访问入口	 * 	 *  @access	public 	 *  @static     *  @return  object  返回对象的唯一实例     */	static public function getinstance() {			if (is_null(self::$_instance) || !isset(self::$_instance)) {				self::$_instance = new self();			}			return self::$_instance;	}}