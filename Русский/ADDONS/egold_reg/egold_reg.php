<?php
header("Access-Control-Allow-Origin: *");

//Для отображения ошибок, раскомментировать то что ниже:
// ini_set('error_reporting', E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

include 'settings.php';

//----------------------------

//Проверка полученных значений
$type['email']="0-9a-zA-Z-@\.!#$%&*+=?_|~";
$type['pin']="0-9";
foreach($_REQUEST as $key=> $val) if(strlen($key)<100 && $val && strlen($val)<1440 && in_array($key,array_keys($type))) $request[$key]= preg_replace("/[^".$type[$key]."]/",'',$val);

if(isset($_REQUEST['email']) && (!isset($request['email']) || (isset($request['email']) && filter_var($request['email'], FILTER_VALIDATE_EMAIL) === false))){
	echo '{"error": "email"}'; //Неправильная почта
	exit;
}

if(!empty($_SERVER['HTTP_CLIENT_IP']))$host_ip=$_SERVER['HTTP_CLIENT_IP'];
else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))$host_ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
else $host_ip=$_SERVER['REMOTE_ADDR'];
if(!$host_ip){
	echo '{"error": "user_ip"}';//Не удалось определить IP пользователя
	exit;
}

$mysqli_connect= mysqli_connect($host_db_reg,$user_db_reg,$password_db_reg,$database_db_reg) or die("error_connect_bd");
function query_bd($query){
  global $mysqli_connect,$sqltbl;
  $GLOBALS['sqltbl']='';
  $result= mysqli_query($GLOBALS['mysqli_connect'],$query) or die("error_bd: ".$query);
  if($result!== FALSE && gettype($result)!= "boolean") $GLOBALS['sqltbl']= mysqli_fetch_assoc($result);
  else unset($GLOBALS['sqltbl']);
  if(isset($GLOBALS['sqltbl']))return $GLOBALS['sqltbl'];
}

function send_mail($TO_EMAIL,$subject,$message){
	$fromUserName = $GLOBALS['email_domain'];
	$fromUserEmail= "robot@".$GLOBALS['email_domain'];
	$ReplyToEmail = $fromUserEmail;
	$subject = "=?utf-8?b?" . base64_encode($subject) . "?=";
	$from = "=?utf-8?B?" . base64_encode($fromUserName) . "?= <" . $fromUserEmail . ">";
	$headers = "From: " . $from . "\r\nReply-To: " . $ReplyToEmail . "\"";
	$headers .= "\r\nContent-Type: text/html; charset=\"utf-8\"";
	
	$message.= "\r\n<br/>---
\r\n<br/><i>* Сообщение отправлено с технического адреса: robot@".$GLOBALS['email_domain'].". Технический адрес только для автоматического отправления писем!</i>
\r\n<br/>";

	if(@mail($TO_EMAIL, $subject, $message, $headers)) return 1;
	else return 0;
}

if(isset($request['email']) && isset($request['pin'])){
	if($period_ip>0)query_bd("SELECT * FROM `".$GLOBALS['database_db_reg']."`.`eGOLDreg` WHERE `ip`='".$GLOBALS['host_ip']."' and `wallet`!='' ORDER by `date` DESC LIMIT 1;");
	if(isset($sqltbl['date']) && time()-strtotime($sqltbl['date'])< $period_ip){
		echo '{"error": "limit_ip"}'; //Действует ограничение на период между запросами регистрации кошелька для одного IP
	} else {
		query_bd("SELECT * FROM `".$GLOBALS['database_db_reg']."`.`eGOLDreg` WHERE `email`='".$request['email']."' and `wallet`='' and `pin`>0 and `pin`='".$request['pin']."' ORDER by `id` DESC LIMIT 1;");
		if(isset($sqltbl['pin'])){
			$id= $sqltbl['id'];
			include  __DIR__ .'/../egold_settings.php';
			$mysqli_connect_egold = mysqli_connect($host_db,$user_db,$password_db,$database_db) or die("error_egold_db");
			$query= "SELECT `height` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `wallet`= '".$GLOBALS['wallet_egold_number']."' LIMIT 1;";
			$result= mysqli_query($mysqli_connect_egold,$query) or die("error_egold_wallets");
			$sqltbl = mysqli_fetch_assoc($result);
			if(isset($sqltbl['height'])){
				include  __DIR__ .'/../egold_crypto/falcon.php';
				//Генератор случайных паролей
				$chars="qazxswedcvfrtgbnhyujmkiop1234567890QAZXSWEDCVFRTGBNHYUJMKOLP";//Символы, которые будут использоваться в пароле
				$max=50000;//Количество символов в пароле
				$size=mb_strlen($chars)-1;
				$newpassword=null;
				$hashpassword=null;
				while($max--)$newpassword.=$chars[rand(0,$size)];//Создаём пароль
				list($falcon_k_reg,$falcon_p_reg)= Falcon\createKeyPair(128, uniqid().uniqid().uniqid().uniqid().uniqid().$newpassword);
				//Конец генерации пары ключей
				$wallet_height= $sqltbl['height']+1;
				function bchexdec($hex){//Длинные числа
					$dec = 0; $len = strlen($hex);
					for ($i = 1; $i <= $len; $i++)$dec = bcadd($dec, bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i))));
					return $dec;
				}
				function sha_dec($str){return substr(bchexdec(gen_sha3($str,19)),0,19);}//Генерация хеша из 19 чисел
				$str_s_reg='30'.sha_dec($falcon_p_reg);
				$falcon_s_reg= Falcon\sign($falcon_k_reg, $str_s_reg);
				$str_s= $GLOBALS['wallet_egold_number'].'00'.'3'.'0'.$wallet_height.$noda_ip.$falcon_p_reg.$falcon_s_reg;
				$falcon_s= Falcon\sign($GLOBALS['wallet_egold_key'], $str_s);
				$falcon_p= Falcon\createPublicKey($GLOBALS['wallet_egold_key']);
				
				function egold_send($params){
					global $noda_ip;
					$url = 'http://'.$noda_ip.'/egold.php';
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
					curl_setopt($ch, CURLOPT_TIMEOUT_MS, 15*1000);
					$json = curl_exec ($ch);
					curl_close ($ch);
					return json_decode($json, true);
				}
				
				if($falcon_s){
					$params = array(
						'type' => 'send',
						'wallet' => $GLOBALS['wallet_egold_number'],
						'recipient' => '00',
						'money' => '3',
						'pin' => '0',
						'height' => $wallet_height,
						'signpubreg' => $falcon_p_reg,
						'signreg' => $falcon_s_reg,
						'signpub' => $falcon_p,
						'sign' => $falcon_s
					);
					$json_send= egold_send($params);
				}
				
				if(isset($json_send['walletnew']) && strlen(preg_replace("/[^0-9]/i",'',$json_send['walletnew']))==18){
					$wallet_new= $json_send['walletnew'];
					query_bd("UPDATE `".$GLOBALS['database_db_reg']."`.`eGOLDreg` SET `wallet`='".$wallet_new."',`date`=NOW() WHERE `id`='".$id."';");
					
					$subject= "ВНИМАНИЕ! Зарегистрирован новый кошелёк: ".$wallet_new;
					$message= "Номер кошелька: <b>".$wallet_new."</b>
				\r\n<br/>Закрытый ключ: ".$falcon_k_reg."
				\r\n<br/>
				\r\n<br/>Скачать кошелёк можно по этой ссылке: ".$GLOBALS['wallet_url']."
				\r\n<br/>MD5 подпись для проверки архива: ".$GLOBALS['MD5']."
				\r\n<br/><i>MD5 подпись архива <b>ОБЯЗАТЕЛЬНО</b> необходимо сверить с другими официальными источниками! Как проверить MD5, можно найти в интернете.</i>
				\r\n<br/>Пароль на архив: MD5
				\r\n<br/>IP адрес ноды: ".$noda_ip."
				\r\n<br/>
				\r\n<br/><i>* Кошелёк станет доступен через 10 минут. Сам файл кошелька в архиве - это только файл <b>eGOLD.html</b>. Он автономный и его можно перенести из архива в любое место.</i>
				\r\n<br/>
				\r\n<br/><b>1.</b> Необходимо изменить закрытый ключ и только потом принимать транзакции на кошелёк и использовать его в других целях.
				\r\n<br/><b>2.</b> До момента смены закрытого ключа, кошелёк считается небезопасным.
				\r\n<br/><b>3.</b> В любом случае, ".$GLOBALS['email_domain']." только регистрирует новый кошелёк, а вся ответственность за его использование полностью лежит на Вас.
				\r\n<br/>
				\r\n<br/>Дата и время: ".date("H:i:s d.m.Y")." (+3 - Московское)
				\r\n<br/>";
				
					if(send_mail($request['email'],$subject,$message)==1){
						echo '{"send": "wallet"}'; //На почту отправлен номер кошелька
					} else {
						echo '{"error": "send_wallet"}'; //Не удалось отправить кошелёк на почту
					}
				} else {
					echo '{"error": "wallet_new"}'; //Ошибка при генерации нового кошелька
				}
			} else {
				echo '{"error": "wallet_egold_number"}'; //В ноде не найден кошелёк с которого будем регистрировать новый
			}
		} else {
			echo '{"error": "pin"}'; //Неправильно указан пинкод
		}
	}
} else if(isset($request['email'])){
	if($GLOBALS['period_pin']>0)query_bd("SELECT * FROM `".$GLOBALS['database_db_reg']."`.`eGOLDreg` WHERE (`email`='".$request['email']."' or `ip`='".$GLOBALS['host_ip']."') and `wallet`='' ORDER by `date` DESC LIMIT 1;");
	if(isset($sqltbl['date']) && time()-strtotime($sqltbl['date'])< $GLOBALS['period_pin']){
		echo '{"error": "limit_pin"}'; //Действует ограничение на период между запросами на пинкод для одной почты и одного IP
	} else {
		query_bd("SELECT * FROM `".$GLOBALS['database_db_reg']."`.`eGOLDreg` WHERE `email`='".$request['email']."' and `wallet`!='' ORDER by `id` DESC LIMIT 1;");
		if(!isset($sqltbl['date']) || (isset($sqltbl['date']) && ($GLOBALS['wallets_more_one']==2 || ($GLOBALS['wallets_more_one']==1 && time()-strtotime($sqltbl['date'])>$GLOBALS['period_reg']*60*60)))){
			//Генератор случайных пинкодов
			$chars_pin="1234567890";
			//Количество символов в пинкоде
			$max_pin=9;
			//Определяем количество символов в $chars
			$size_pin=mb_strlen($chars_pin)-1;
			//Определяем пустую переменную, в которую и будем записывать символы
			$new_pin=null;
			//Создаём пинкод
			while($max_pin--)$new_pin.=$chars_pin[rand(0,$size_pin)];
			//Конец генератора случайных пинкодов
			
			$subject= "Пинкод для подтверждения генерации нового кошелька";
			$message= "Для подтверждения генерации нового кошелька введите пинкод на странице: ".$GLOBALS['page_reg']."
		\r\n<br/>
		\r\n<br/>Пинкод: <b>".$new_pin."</b>
		\r\n<br/>
		\r\n<br/>Дата и время: ".date("H:i:s d.m.Y")." (+3 - Московское)
		\r\n<br/>Пинкод запрошен с IP: ".$GLOBALS['host_ip']."
		\r\n<br/>";

			if(send_mail($request['email'],$subject,$message)==1){
				//Сохраняем в базе данных
				query_bd("INSERT INTO `".$GLOBALS['database_db_reg']."`.`eGOLDreg` SET `email`='".$request['email']."', `pin`='".$new_pin."', `ip`='".$GLOBALS['host_ip']."', `date`=NOW();");
				echo '{"send": "pin"}'; //На почту отправлен пинкод
			} else {
				echo '{"error": "send_pin"}'; //Не удалось отправить пинкод
			}
			
			//Очистка базы данных
			query_bd("DELETE FROM `".$GLOBALS['database_db_reg']."`.`eGOLDreg` WHERE `wallet`='' and `date` < DATE_ADD(NOW(), INTERVAL -".$GLOBALS['period_clean']." DAY);");
		} else {
			echo '{"error": "limit_reg"}'; //Действует ограничение на количество регистраций для одной почты
		}
	}
}
if(isset($mysqli_connect_egold))mysqli_close($mysqli_connect_egold);
if(isset($mysqli_connect))mysqli_close($mysqli_connect);
exit;
?>