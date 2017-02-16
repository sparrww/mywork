<?php
	class DB{
		//连接数据库
		static public function getDB(){
			//localhost--主机名
			//root--用户名
			//123--密码
			//wzq--数据库名
			//在下边修改为你设置的用户名和密码

			$db=new mysqli('127.0.0.1','root','s87161762','wzq');
			if(mysqli_connect_errno()){
				echo '数据库连接错误'.mysqli_connect_error();
				exit();
			}
			$db->set_charset('utf8');
			return $db;
		}

	
		//数据库销毁
		static public function unDB(&$result, &$db){
			if (is_object($result)) {
				$result->free();
				$result = null;
			}
			if (is_object($db)) {
				$db->close();
				$db = null;
			}
		}
		

	}
?>