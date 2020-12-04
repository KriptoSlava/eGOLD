<?php
header("Access-Control-Allow-Origin: *");

//Для отображения ошибок, раскомментировать то что ниже:
// ini_set('error_reporting', E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

include 'settings.php';
//Проверка полученных значений
foreach($_REQUEST as $key=> $val) if(strlen($key)<100 && $val && strlen($val)<=25 && in_array($key,array_keys($type))) $request[$key]= preg_replace("/[^".$type[$key]."]/",'',$val);

if(!isset($request['details']) || mb_strlen($request['details'])<10){
	echo '{"error": "details"}'; //Неправильный номер карты
	exit;
}

if(!empty($_SERVER['HTTP_CLIENT_IP']))$host_ip=$_SERVER['HTTP_CLIENT_IP'];
else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))$host_ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
else $host_ip=$_SERVER['REMOTE_ADDR'];
if(!$host_ip){
	echo '{"error": "user_ip"}';//Не удалось определить IP пользователя
	exit;
}

$mysqli_connect = mysqli_connect($host_db_lightpurchase,$user_db_lightpurchase,$password_db_lightpurchase,$database_db_lightpurchase) or die("error_connect_db");
function query_bd($query){
  global $mysqli_connect,$sqltbl;
  $GLOBALS['sqltbl']='';
  $result= mysqli_query($GLOBALS['mysqli_connect'],$query) or die("error_bd: ".$query);
  if($result!== FALSE && gettype($result)!= "boolean") $GLOBALS['sqltbl']= mysqli_fetch_assoc($result);
  else unset($GLOBALS['sqltbl']);
  if(isset($GLOBALS['sqltbl']))return $GLOBALS['sqltbl'];
}

query_bd("SELECT IF(`date`>DATE_ADD(NOW(), INTERVAL -".$period_lightpurchase." SECOND),1,0) as date FROM `eGOLDlightpurchase` WHERE `ip`= '".$host_ip."' ORDER BY `date` DESC LIMIT 1;");
if(!isset($sqltbl['date']) || !$sqltbl['date'] || $sqltbl['date']!=1){
	$stop=0;
	while($stop<10){
		$rand_pin= mt_rand(100000000000000000, 999999999999999999);
		query_bd("SELECT `pin` FROM `eGOLDlightpurchase` WHERE `pin`='".$rand_pin."';");
		if(!isset($sqltbl['pin']) || !$sqltbl['pin']){
			query_bd("INSERT IGNORE INTO `eGOLDlightpurchase` SET `pin`= '".$rand_pin."', `details`= '".$request['details']."', `ip`= '".$host_ip."', `date`= NOW();");
			if(mysqli_affected_rows($mysqli_connect)>=1){
				$shortlink= $text_first.":".$wallet_egold_number.":0:".$rand_pin.":";
				//создание QR кода
				include 'qrcode.php';
				$qr= QRCode::getMinimumQRCode($shortlink, QR_ERROR_CORRECT_LEVEL_H);
				$im= $qr->createImage($qrsize, 4);
				ob_start();
				imagepng($im);
				$data = ob_get_contents();
				ob_end_clean();
				//вывод 
				echo '{"wallet": "'.$wallet_egold_number.'","pin": "'.$rand_pin.'","shortlink": "'.$shortlink.'","qr": "<img src=\"data:image/png;base64,'.base64_encode($data).'\" style=\"width:'.($qrsize*41+8).'px;height:'.($qrsize*41+8).'px;\"  alt=\"'.$shortlink.'\"/>"}';
				$stop=10;
			} else {
				usleep(mt_rand(0.001, 0.01)*1000000);
				$stop++;
			}
		} else {
			usleep(mt_rand(0.001, 0.01)*1000000);
			$stop++;
		}
	}
} else echo '{"error": "timeout"}';


if(isset($mysqli_connect))mysqli_close($mysqli_connect);
exit;
?>