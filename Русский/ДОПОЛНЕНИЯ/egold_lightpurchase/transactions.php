<?php
header("Access-Control-Allow-Origin: *");

//Для отображения ошибок, раскомментировать то что ниже:
// ini_set('error_reporting', E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

include 'settings.php';

if($cron_password){//если установлен пароль на запуск крона, то проверяем его
	if(isset($argv[1]) && $argv[1])$_REQUEST['cron_password']=$argv[1];
	if(!isset($_REQUEST['cron_password']) || $_REQUEST['cron_password']!=$cron_password)exit;
}

include  __DIR__ .'/../egold_settings.php';
$mysqli_connect_egold = mysqli_connect($host_db,$user_db,$password_db,$database_db) or die("error_egold_db1");

$query = "SELECT `wallet`, `recipient`, `money`, `pin`, `height`, `date` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` WHERE `date`< UNIX_TIMESTAMP()-5*60 and `date`> UNIX_TIMESTAMP()-3*24*60*60 and `recipient`='".$wallet_egold_number."' and `checkhistory`=1 and `pin`!=0 and `money`>0 ORDER BY `date` DESC LIMIT 100;";
$result = mysqli_query($mysqli_connect_egold,$query) or die("error_egold_history");
while($sqltbl_arr= mysqli_fetch_array($result,MYSQLI_ASSOC))$transaction["('".$sqltbl_arr['wallet']."','".$sqltbl_arr['height']."')"]=$sqltbl_arr;
mysqli_close($mysqli_connect_egold);
if(!isset($transaction)){exit_now();}

//убираем дубли пинов в транзакциях для проверки в бд
foreach ($transaction as $key => $value)if(!isset($pin_check[$value['pin']]))$pin_check[$value['pin']]=1;
if(isset($pin_check)){	
	$mysqli_connect = mysqli_connect($host_db_lightpurchase,$database_db_lightpurchase,$password_db_lightpurchase,$database_db_lightpurchase) or die("error_connect_db2");
	function exit_now(){if(isset($mysqli_connect))mysqli_close($mysqli_connect);exit;}
	
	//проверяем, есть ли такой пин для зачисления средств
	$query= "SELECT `pin`,`details` FROM `eGOLDlightpurchase` WHERE `pin` IN ('".implode("','", array_keys($pin_check))."');";
	$result= mysqli_query($mysqli_connect,$query) or die("error_transaction_check1");
	while($sqltbl_arr= mysqli_fetch_array($result,MYSQLI_ASSOC)){
	  $wallet_pin[$sqltbl_arr['pin']]=$sqltbl_arr['details'];
	}
}
if(!isset($wallet_pin) || !$wallet_pin){exit_now();}//если транзакций для кошельков сайта нет, тогда завершаем
//удаляем транзакции, у которых нет пина из базы
foreach ($transaction as $key => $value)if(!isset($wallet_pin[$value['pin']]) || !$wallet_pin[$value['pin']])unset($transaction[$key]);

//проверка уже обработанных транзакций
$query= "SELECT `wallet`,`height` FROM `eGOLDlightpurchase_log` WHERE (`wallet`,`height`) IN (".implode(",",array_keys($transaction)).");";
$result= mysqli_query($mysqli_connect,$query) or die("error_transaction_check2");
while($sqltbl_arr= mysqli_fetch_array($result,MYSQLI_ASSOC))unset($transaction["('".$sqltbl_arr['wallet']."','".$sqltbl_arr['height']."')"]);
if(!isset($transaction) || !$transaction){exit_now();}//если новых транзакций нет, тогда завершаем


function query_bd($query){
  global $mysqli_connect,$sqltbl;
  $GLOBALS['sqltbl']='';
  $result= mysqli_query($GLOBALS['mysqli_connect'],$query) or die("error_bd: ".$query);
  if($result!== FALSE && gettype($result)!= "boolean") $GLOBALS['sqltbl']= mysqli_fetch_assoc($result);
  else unset($GLOBALS['sqltbl']);
  if(isset($GLOBALS['sqltbl']))return $GLOBALS['sqltbl'];
}

//проводим транзакцию на пополнение
foreach ($transaction as $key => $value){
	query_bd("INSERT INTO `eGOLDlightpurchase_log` SET `pin`= '".$value['pin']."', `details`= '".$wallet_pin[$value['pin']]."', `wallet`= '".$value['wallet']."', `height`= '".$value['height']."', `egold`= '".$value['money']."', `date`= NOW(), `status`= 0;");
	if(mysqli_affected_rows($mysqli_connect)>=1){
		query_bd("UPDATE `eGOLDlightpurchase` SET `date_deposit`= NOW() WHERE `pin`= '".$value['pin']."';");
	}
}

//Очистка базы данных
query_bd("DELETE FROM `eGOLDlightpurchase` WHERE `date_deposit` < DATE_ADD(NOW(), INTERVAL -".$GLOBALS['period_clean']." DAY) and `date` < DATE_ADD(NOW(), INTERVAL -".$GLOBALS['period_clean']." DAY);");

exit_now();
?>