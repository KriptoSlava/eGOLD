<?php
include 'settings.php';

if(session_status()!==PHP_SESSION_ACTIVE)session_start();

if(isset($_SESSION['password']) && isset($_REQUEST['id_checked']) && $_REQUEST['id_checked']){
	$id_checked= preg_replace("/[^0-9]/i",'',$_REQUEST['id_checked']);
	if($id_checked>0 && (
		!isset($_REQUEST['checked_true']) || $_REQUEST['checked_true']!=1
		|| 
		(isset($_REQUEST['checked_true']) && $_REQUEST['checked_true']==1 && isset($_SESSION['course']) && $_SESSION['course'])
		)){
		$user= array_search($_SESSION['password'], $password);
		if($user){
			$mysqli_connect = mysqli_connect($host_db_lightpurchase,$database_db_lightpurchase,$password_db_lightpurchase,$database_db_lightpurchase) or die("error_connect_db");
			
			function query_bd($query){
				global $mysqli_connect,$sqltbl;
				$GLOBALS['sqltbl']='';
				$result= mysqli_query($GLOBALS['mysqli_connect'],$query) or die("error_bd: ".$query);
				if($result!== FALSE && gettype($result)!= "boolean") $GLOBALS['sqltbl']= mysqli_fetch_assoc($result);
				else unset($GLOBALS['sqltbl']);
				if(isset($GLOBALS['sqltbl']))return $GLOBALS['sqltbl'];
			}
			
			if(isset($_REQUEST['checked_true']) && ($_REQUEST['checked_true']==1 || $_REQUEST['checked_true']==2)){//отмечаем выполнение и фиксируем прибыли
				query_bd("UPDATE `eGOLDlightpurchase_log` SET `user`= '".$user."',`status`= '".$_REQUEST['checked_true']."',`course`= '".$_SESSION['course']."',`deposit`= `course`*`egold`,`profit_percent`= '".$profit_percent."',`profit`= `profit_percent`/100*`deposit`,`pay`= `deposit`-`profit`,`date_change`= NOW() WHERE `id`= '".$id_checked."';");
				if(mysqli_affected_rows($mysqli_connect)>=1)echo gmdate('Y.m.d H:i:s',time()+$offset*60*60);
			} else {
				if(in_array($user, $admin)){//снимаем отметку выполнения
					query_bd("UPDATE `eGOLDlightpurchase_log` SET `user`= '".$user."',`status`= 0,`course`= '',`deposit`= '',`profit_percent`= '',`profit`= '',`pay`= '',`date_change`= NOW() WHERE `id`= '".$id_checked."';");
					if(mysqli_affected_rows($mysqli_connect)>=1)echo gmdate('Y.m.d H:i:s',time()+$offset*60*60);
				}
			}
			
			//Закрываем подключение к бд
			mysqli_close($mysqli_connect);
		}
	}
}

?>