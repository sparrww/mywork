<?php
/*******************************************************************
 * @authors Air
 * @date    2014-09-06
 * @copy    Copyright © 2013-2018 Powered by Air Web Studio  
 *******************************************************************/
// 网站基本信息

namespace  Admin\Controller;

use Lib\Controller as Controller,Lib\Lib as Lib;

class baseController extends controlController{


	const WEB_ERROR_MESSAGE		=	'请填写完整信息！';
	const WEB_SUCCESS_MESSAGE	=	'网站信息保存成功！';
	const WEB_FAILURE_MESSAGE	=	'网站信息保存失败！';

	public $m;

    /**
     * 重定义构造器
     *
     * @param string $message
     * @param int $code
     * @return void
     */
	public function __construct(){
        parent::__construct();
		$this->m = Lib::getinstance()->A('Admin\Model\base',Null,'Model');
    }

	/*
	网站基本信息设置
	*/
	public function index(){
        $array['article'] = $this->m->getOne();
		include $this->display('Base/index.html',$array);
	}


	/*
	保存设置
	*/
	public function save(){

		if($this->post(['name','title','domran','template'])){

			if($this->m->edit($this->post())){
                $this->r_log('修改网站基本信息');
				$data = [
					'status' 	=>	'y',
					'message'	=>	self::WEB_SUCCESS_MESSAGE
				];
			}else{
				$data = [
					'status' 	=>	'n',
					'message'	=>	self::WEB_FAILURE_MESSAGE
				];
			}
		}else{
			$data = [
				'status' 	=>	'c',
				'message'	=>	self::WEB_ERROR_MESSAGE
			];
		}

		echo json_encode($data);
	}


	/*
	执行结果
	*/
	public function execution(){
		include $this->display('Base/execution.html');
	}

}

?>