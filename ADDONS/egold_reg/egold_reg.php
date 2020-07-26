<?php
//For error display uncomment the below:
// ini_set('error_reporting', E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

include 'settings.php';

//----------------------------

//Checking the received data
$type['email']="0-9a-zA-Z-@\.!#$%&*+=?_|~";
$type['pin']="0-9";
foreach($_REQUEST as $key=> $val) if(strlen($key)<100 && $val && strlen($val)<1440 && in_array($key,array_keys($type))) $request[$key]= preg_replace("/[^".$type[$key]."]/",'',$val);

if(isset($_REQUEST['email']) && (!isset($request['email']) || (isset($request['email']) && filter_var($request['email'], FILTER_VALIDATE_EMAIL) === false))){
	echo '{"error": "email"}'; //Wrong email
	exit;
}

if(!empty($_SERVER['HTTP_CLIENT_IP']))$host_ip=$_SERVER['HTTP_CLIENT_IP'];
else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))$host_ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
else $host_ip=$_SERVER['REMOTE_ADDR'];
if(!$host_ip){
	echo '{"error": "user_ip"}';//Failed to define IP user
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
	global $email_domain;
	$fromUserName = $email_domain;
	$fromUserEmail= "robot@".$email_domain;
	$ReplyToEmail = $fromUserEmail;
	$subject = "=?utf-8?b?" . base64_encode($subject) . "?=";
	$from = "=?utf-8?B?" . base64_encode($fromUserName) . "?= <" . $fromUserEmail . ">";
	$headers = "From: " . $from . "\r\nReply-To: " . $ReplyToEmail . "\"";
	$headers .= "\r\nContent-Type: text/html; charset=\"utf-8\"";
	
	$message.= "\r\n<br/>---
\r\n<br/><i>* Message sent from the technical address: robot@".$email_domain.". Technical address only for automatic message sending!</i>
\r\n<br/>";

	if(@mail($TO_EMAIL, $subject, $message, $headers)) return 1;
	else return 0;
}

if(isset($request['email']) && isset($request['pin'])){
	if($period_ip>0)query_bd("SELECT * FROM `".$GLOBALS['database_db_reg']."`.`eGOLDreg` WHERE `ip`='".$GLOBALS['host_ip']."' and `wallet`!='' ORDER by `date` DESC LIMIT 1;");
	if(isset($sqltbl['date']) && time()-strtotime($sqltbl['date'])< $period_ip){
		echo '{"error": "limit_ip"}'; //Interval between requesting for wallet registrations for one IP is limited
	} else {
		query_bd("SELECT * FROM `".$GLOBALS['database_db_reg']."`.`eGOLDreg` WHERE `email`='".$request['email']."' and `wallet`='' and `pin`>0 and `pin`='".$request['pin']."' ORDER by `id` DESC LIMIT 1;");
		if(isset($sqltbl['pin'])){
			$id= $sqltbl['id'];
			include  __DIR__ .'/../egold_settings.php';
			$mysqli_connect_egold = mysqli_connect($host_db,$user_db,$password_db,$database_db) or die("error_egold_db");
			$query= "SELECT `height` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `wallet`= '".$wallet_egold_number."' LIMIT 1;";
			$result= mysqli_query($mysqli_connect_egold,$query) or die("error_egold_wallets");
			$sqltbl = mysqli_fetch_assoc($result);
			if(isset($sqltbl['height'])){
				include  __DIR__ .'/../egold_crypto/falcon.php';
				//Password generator
				$chars="qazxswedcvfrtgbnhyujmkiop1234567890QAZXSWEDCVFRTGBNHYUJMKOLP";//Symbols that would be used in password
				$max=50000;//Number of symbols in password
				$size=mb_strlen($chars)-1;
				$newpassword=null;
				$hashpassword=null;
				while($max--)$newpassword.=$chars[rand(0,$size)];//Creating password
				list($falcon_k_reg,$falcon_p_reg)= Falcon\createKeyPair(128, uniqid().uniqid().uniqid().uniqid().uniqid().$newpassword);
				//End of generating pair key
				$wallet_height= $sqltbl['height']+1;
				function bchexdec($hex){//Long numbers
					$dec = 0; $len = strlen($hex);
					for ($i = 1; $i <= $len; $i++)$dec = bcadd($dec, bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i))));
					return $dec;
				}
				function sha_dec($str){return substr(bchexdec(gen_sha3($str,19)),0,19);}//Generation of hash consisting of 19 numbers
				$str_s_reg='30'.sha_dec($falcon_p_reg);
				$falcon_s_reg= Falcon\sign($falcon_k_reg, $str_s_reg);
				$str_s= $wallet_egold_number.'00'.'3'.'0'.$wallet_height.$noda_ip.$falcon_p_reg.$falcon_s_reg;
				$falcon_s= Falcon\sign($wallet_egold_key, $str_s);
				$falcon_p= Falcon\createPublicKey($wallet_egold_key);
				
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
						'wallet' => $wallet_egold_number,
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
					
					$subject= "ATTENTION! New wallet has been registered: ".$wallet_new;
					$message= "Wallet number: <b>".$wallet_new."</b>
				\r\n<br/>Private key: ".$falcon_k_reg."
				\r\n<br/>
				\r\n<br/>Download the wallet via the link: ".$wallet_url."
				\r\n<br/>MD5 signature for archive verification: ".$MD5."
				\r\n<br/><i>MD5 archive signature <b>MANDATORY</b> should be reconciled with other official sources! Find on the Internet how to check MD5.</i>
				\r\n<br/>Archive password: MD5
				\r\n<br/>IP node address: ".$noda_ip."
				\r\n<br/>
				\r\n<br/><i>* Wallet will be available in 10 minutes. The wallet’s file in the archive is <b>eGOLD.html</b> file only. It is autonomous and can be transferred to any place from the archive.</i>
				\r\n<br/>
				\r\n<br/><b>1.</b> Private key should be changed and only then it will be possible to accept transactions on the wallet and use it for other purposes.
				\r\n<br/><b>2.</b> Wallet is considered to be unsafe till the moment of changing private key.
				\r\n<br/><b>3.</b> In any case, ".$email_domain." only register a new wallet, and users bear full responsibility for its utilization.													
				\r\n<br/>
				\r\n<br/>Date and time: ".date("H:i:s d.m.Y")."
				\r\n<br/>";
				
					if(send_mail($request['email'],$subject,$message)==1){
						echo '{"send": "wallet"}'; //Wallet number sent to the email
					} else {
						echo '{"error": "send_wallet"}'; //Failed to send wallet number to email
					}
				} else {
					echo '{"error": "wallet_new"}'; //New wallet generation error
				}
			} else {
				echo '{"error": "wallet_egold_number"}'; //Wallet for registering a new one not found on the node
			}
		} else {
			echo '{"error": "pin"}'; //Wrong pin code
		}
	}
} else if(isset($request['email'])){
	if($period_pin>0)query_bd("SELECT * FROM `".$GLOBALS['database_db_reg']."`.`eGOLDreg` WHERE (`email`='".$request['email']."' or `ip`='".$GLOBALS['host_ip']."') and `wallet`='' ORDER by `date` DESC LIMIT 1;");
	if(isset($sqltbl['date']) && time()-strtotime($sqltbl['date'])< $period_pin){
		echo '{"error": "limit_pin"}'; //Interval for requesting pin code for one email and one IP is limited
	} else {
		query_bd("SELECT * FROM `".$GLOBALS['database_db_reg']."`.`eGOLDreg` WHERE `email`='".$request['email']."' and `wallet`!='' ORDER by `id` DESC LIMIT 1;");
		if(!isset($sqltbl['date']) || (isset($sqltbl['date']) && ($wallets_more_one==2 || ($wallets_more_one==1 && time()-strtotime($sqltbl['date'])>$period_reg*60*60)))){
			//Random pin code generator
			$chars_pin="1234567890";
			//Number of symbols in pin code
			$max_pin=9;
			//Defining the number of symbols in $chars
			$size_pin=mb_strlen($chars_pin)-1;
			//Defining free variable which would record symbols
			$new_pin=null;
			//Creating a pin code
			while($max_pin--)$new_pin.=$chars_pin[rand(0,$size_pin)];
			//End of random pin code generator
			
			$subject= "Pin code for confirming new wallet generation";
			$message= "For confirmation of the new wallet generating enter the pin code on the page: ".$GLOBALS['page_reg']."
		\r\n<br/>
		\r\n<br/>Pin code: <b>".$new_pin."</b>
		\r\n<br/>
		\r\n<br/>Date and time: ".date("H:i:s d.m.Y")."
		\r\n<br/>Pin code requested from IP: ".$GLOBALS['host_ip']."
		\r\n<br/>";

			if(send_mail($request['email'],$subject,$message)==1){
				//Saving to the database
				query_bd("INSERT INTO `".$GLOBALS['database_db_reg']."`.`eGOLDreg` SET `email`='".$request['email']."', `pin`='".$new_pin."', `ip`='".$GLOBALS['host_ip']."', `date`=NOW();");
				echo '{"send": "pin"}'; //Pin code sent to the email
			} else {
				echo '{"error": "send_pin"}'; //Failed to send pin code
			}
			
			//Database clearing
			query_bd("DELETE FROM `".$GLOBALS['database_db_reg']."`.`eGOLDreg` WHERE `wallet`='' and `date` < DATE_ADD(NOW(), INTERVAL -".$period_clean." DAY);");
		} else {
			echo '{"error": "limit_reg"}'; //Number of registrations for one email is limited 
		}
	}
}
if(isset($mysqli_connect_egold))mysqli_close($mysqli_connect_egold);
if(isset($mysqli_connect))mysqli_close($mysqli_connect);
exit;
?>