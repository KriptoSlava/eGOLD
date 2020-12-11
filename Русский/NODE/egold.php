<?php
$version= '1.33';
$error_log= 0;//=0 or =1 for egold_error.log
ini_set("memory_limit", "2048M");
if($error_log==1){
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	error_reporting(E_ALL);
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 0);
	ini_set('log_errors','on');
	ini_set('error_log', __DIR__ .'/../egold_error.log');
}
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
if(session_status()!==PHP_SESSION_ACTIVE)session_start();
$json_arr['timer_start']=microtime(true);
$delay_timer=0;
if(isset($_SESSION['timer_start']) && $_SESSION['timer_start']>0){
	$delay_timer_start= $json_arr['timer_start']-$_SESSION['timer_start'];
	if($delay_timer_start<0.5){echo '{"error":"delay"}';exit;}
	else if($delay_timer_start<0.1)$delay_timer= 0.1-$delay_timer_start;
}
$_SESSION['timer_start']=$json_arr['timer_start']+$delay_timer;
if(!isset($_SESSION['timer_start']) || !($_SESSION['timer_start']>0)){echo '{"error":"session"}';exit;}
function delay_now(){usleep(mt_rand(0.0001*1000000,0.01*1000000));}
delay_now();
include __DIR__ .'/egold_settings.php';
function convert_ipv6($ip){
  if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){
    if(strpos($ip, '::') === FALSE){
      $parts = explode(':', $ip);
      $new_parts = array();
      $ignore = FALSE;
      $done = FALSE;
      for($i = 0; $i < count($parts); $i++){
        if(intval(hexdec($parts[$i])) === 0 && $ignore == FALSE && $done == FALSE){
          $ignore = TRUE;
          $new_parts[] = '';
          if($i==0)$new_parts = '';
        } else if(intval(hexdec($parts[$i])) === 0 && $ignore == TRUE && $done == FALSE)continue;
        else if (intval(hexdec($parts[$i])) !== 0 && $ignore == TRUE){
          $done = TRUE;
          $ignore = FALSE;
          $new_parts[] = $parts[$i];
        } else $new_parts[] = $parts[$i];
      }
      $ip = implode(':', $new_parts);
    }
    if (substr($ip, -2) != ':0')$ip = preg_replace("/:0{1,3}/", ":", $ip);
    if(isset($new_parts) && count($new_parts)<8 && array_pop($new_parts) == '')$ip.= ':0';
  }
  return $ip;
}
$dir_temp= __DIR__ .'/egold_temp';
if(isset($argv[1]) || $_SERVER['SERVER_NAME']=='127.0.0.1' || $_SERVER['SERVER_NAME']=='localhost')$host_ip=$noda_ip;
else{
	if(!empty($_SERVER['HTTP_CLIENT_IP']))$host_ip=$_SERVER['HTTP_CLIENT_IP'];
	else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))$host_ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
	else $host_ip=$_SERVER['REMOTE_ADDR'];
	if(!$host_ip){echo '{"error": "user_ip"}';exit;}
	if(strpos($host_ip,',') !== false){
		$host_tmp= explode(',',$host_ip);
		$host_ip= $host_tmp[0];
		unset($host_tmp);
	}
	if($host_ip!=$noda_ip){
		$host_ip=preg_replace("/[^0-9a-z.:]/",'',$host_ip);
		$host_ip=convert_ipv6($host_ip);
		$ddos_check_file_tmp= $GLOBALS['dir_temp']."/ddos_";
		foreach(glob($ddos_check_file_tmp."*") as $file){
			$ddos_check= (int)str_replace($ddos_check_file_tmp, "",$file);
			if($ddos_check>=10000){echo '{"noda": "timeout"}';exit;}
			$ddos_check++;
			$ddos_check_file= $ddos_check_file_tmp.$ddos_check;
			if(@rename($file, $ddos_check_file)!== true)unset($ddos_check_file);
			break;
		}
		if(!isset($ddos_check_file)){
			$ddos_check_file= $ddos_check_file_tmp."1";
			if(!file_exists($ddos_check_file))file_put_contents($ddos_check_file, "");
		}
		$host_ip_check_file_tmp= $GLOBALS['dir_temp']."/ip_".$host_ip."_";
		foreach(glob($host_ip_check_file_tmp."*") as $file){
			$host_ip_check= (int)str_replace($host_ip_check_file_tmp, "",$file);
			if($host_ip_check>=32){echo '{"noda": "timeout"}';exit;}
			$host_ip_check++;
			$host_ip_check_file= $host_ip_check_file_tmp.$host_ip_check;
			if(@rename($file, $host_ip_check_file)!== true)unset($host_ip_check_file);
			break;
		}
		if(!isset($host_ip_check_file)){
			$host_ip_check_file= $host_ip_check_file_tmp."1";
			if(!file_exists($host_ip_check_file))file_put_contents($host_ip_check_file, "");
		}
	}
}
if((float)phpversion()<7.1){echo '{"message": "PHP version minimum 7.1, but your PHP: '.phpversion().'"}';exit;}
if(!extension_loaded('bcmath')){echo '{"message": "Require to install BCMATH"}';exit;}
if(!extension_loaded('gmp')){echo '{"message": "Require to install GMP"}';exit;}
if(!extension_loaded('curl')){echo '{"message": "Require to install CURL"}';exit;}
$dir_temp_index= $dir_temp.'/index.html';
if(!file_exists($dir_temp_index) || !(fileperms($dir_temp)>=16832)){
	if(!file_exists($dir_temp))mkdir($dir_temp, 0755);
	if(file_exists($dir_temp) && !file_exists($dir_temp_index))file_put_contents($dir_temp_index, "");
	if(!file_exists($dir_temp_index) || !(fileperms($dir_temp)>=16832)){echo '{"message": "Required to allow writing rights for a dir folder: '.$dir_temp.'"}';exit;}
}
if($delay_timer>0)usleep($delay_timer*1000000);
if(isset($_REQUEST['type']) && $_REQUEST['type']=="balanceall"){
	foreach(glob($GLOBALS['dir_temp']."/balanceall_*") as $file){echo '{"balanceall": "'.str_replace("balanceall_", "", basename($file)).'"}';exit;}
	echo '{"balanceall": "0"}';
	exit;
}else
if(isset($_REQUEST['type']) && $_REQUEST['type']=="walletscount"){
	foreach(glob($GLOBALS['dir_temp']."/walletscount_*") as $file){echo '{"walletscount": "'.str_replace("walletscount_", "", basename($file)).'"}';exit;}
}else
if(isset($_REQUEST['type']) && $_REQUEST['type']=="nodas"){
	$file_nodas= $dir_temp."/nodas";
	if(isset($_REQUEST['balancestart']))$file_nodas.= "_balancestart_".$_REQUEST['balancestart'];
  if(isset($_REQUEST['balancefinish']))$file_nodas.= "_balancefinish_".$_REQUEST['balancefinish'];
  if(isset($_REQUEST['nodausewalletstart']))$file_nodas.= "_nodausewalletstart_".$_REQUEST['nodausewalletstart'];
  if(isset($_REQUEST['nodausewalletfinish']))$file_nodas.= "_nodausewalletfinish_".$_REQUEST['nodausewalletfinish'];
	if(isset($_REQUEST['order']) && $_REQUEST['order']=='asc')$file_nodas.= "_order_date_asc";else if(isset($_REQUEST['order']) && $_REQUEST['order']=='balance')$file_nodas.= "_order_balance_desc";
	if(isset($_REQUEST['start']) && $_REQUEST['start']>0)$file_nodas.= "_start_".$_REQUEST['start'];
  if(isset($_REQUEST['limit']) && $_REQUEST['limit']>0 && $_REQUEST['limit']<100)$file_nodas.= "_limit_".$_REQUEST['limit'];
	if(file_exists($file_nodas)){
		$json_nodas= file_get_contents($file_nodas);
		if($json_nodas){echo $json_nodas;exit;}
	}
}else
if(isset($_REQUEST['version'])){
	$archive= 'eGOLD_v'.$version.'.zip';
	if(file_exists($archive)) $md5= ', "MD5": "'.strtoupper(hash_file('md5', $archive)).'"';
	echo '{"version": "'.$version.'"'.(isset($md5)?', "download": "'.(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']?'https':'http').'://'.$_SERVER['SERVER_NAME'].'/'.$archive.'"'.$md5:'').'}'; 
	exit;
}
if(isset($argv[1]) && $argv[1]=="synch"){$_REQUEST=[];$_REQUEST['type']="synch";}
if(isset($_REQUEST['type']) && ($_REQUEST['type']=="send" || $_REQUEST['type']=="synch")){
	$filename_tmp_synch= $dir_temp.'/synch_'.(int)date("i",$json_arr['timer_start']);
	if($_REQUEST['type']=="send"){
		if(isset($_REQUEST['wallet']) && $_REQUEST['wallet']>0 && isset($_REQUEST['height']) && $_REQUEST['height']>=0 && isset($_REQUEST['recipient']) && $_REQUEST['recipient'] && isset($_REQUEST['money']) && $_REQUEST['money'] && isset($_REQUEST['pin']) && $_REQUEST['pin']>=0 && isset($_REQUEST['signpub']) && $_REQUEST['signpub'] && isset($_REQUEST['sign']) && $_REQUEST['sign']){
			$filename_tmp_send= $dir_temp.'/'.$_REQUEST['wallet'].'_'.$_REQUEST['height'].'_'.hash('sha256', $_REQUEST['wallet'].$_REQUEST['height'].$_REQUEST['recipient'].$_REQUEST['money'].$_REQUEST['pin'].(isset($_REQUEST['signpubreg'])?$_REQUEST['signpubreg']:'').(isset($_REQUEST['signreg'])?$_REQUEST['signreg']:'').(isset($_REQUEST['signpubnew'])?$_REQUEST['signpubnew']:'').(isset($_REQUEST['signnew'])?$_REQUEST['signnew']:'').$_REQUEST['signpub'].$_REQUEST['sign']);
			if(file_exists($filename_tmp_send)){echo '{"send": "false"}';exit;}
		} else {echo '{"send": "false"}';exit;}
	} else if(file_exists($filename_tmp_synch)){echo '{"synch":"now"}';exit;}
}
if($email_domain && !function_exists('mail')){$email_domain= '';}
$limit_synch= 250;
$percent_4= 4;
foreach(glob($GLOBALS['dir_temp']."/balanceall_*") as $file){$balanceall= (int)str_replace("balanceall_", "", basename($file));break;}
if(isset($balanceall) && $balanceall>0){
	if($balanceall>=100000000000000)$percent_4= $percent_4/32;
	else if($balanceall>=10000000000000)$percent_4= $percent_4/16;
	else if($balanceall>=1000000000000)$percent_4= $percent_4/8;
	else if($balanceall>=100000000000)$percent_4= $percent_4/4;
	else if($balanceall>=10000000000)$percent_4= $percent_4/2;
}
$percent_4= round($percent_4/(100*30*24*60*60), 12, PHP_ROUND_HALF_DOWN);
$percent_5= 1+round($percent_4*1.25, 12, PHP_ROUND_HALF_DOWN);
$percent_4= 1+$percent_4;
if(!isset($noda_ip) || !$noda_ip){echo '{"error": "noda_ip in egold_settings.php"}';exit;}
$noda_ip=convert_ipv6(preg_replace("/[^0-9a-z.:]/",'',$noda_ip));
if(!isset($noda_wallet) || !$noda_wallet){echo '{"error": "noda_wallet in egold_settings.php"}';exit;}
$noda_wallet=preg_replace("/[^0-9]/",'',$noda_wallet);
if(!isset($host_db) || !$host_db){echo '{"error": "host_db in egold_settings.php"}';exit;}
if(!isset($database_db) || !$database_db){echo '{"error": "database_db in egold_settings.php"}';exit;}
if(!isset($user_db) || !$user_db){echo '{"error": "user_db in egold_settings.php"}';exit;}
if(!isset($password_db) || !$password_db){echo '{"error": "password_db in egold_settings.php"}';exit;}
if(!isset($prefix_db) || !$prefix_db)$prefix_db='egold';
if(isset($noda_trust))foreach($noda_trust as $key=> $val)if(!$val || (!filter_var($val, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && !filter_var($val, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)))unset($noda_trust[$key]);
if(!isset($noda_trust) || count($noda_trust)<3){echo '{"error": "noda_trust minimum 3 nodas in egold_settings.php"}';exit;}
if(($key=array_search($noda_ip,$noda_trust)) !== FALSE){array_splice($noda_trust, $key, 1);}
$stop=0;
$mysqli_connect= mysqli_connect($host_db,$user_db,$password_db,$database_db) or die("error_connect_bd");
function exit_now(){if(isset($mysqli_connect))mysqli_close($mysqli_connect);exit;}
function query_bd($query){
  global $mysqli_connect,$sqltbl;
  $GLOBALS['sqltbl']='';
  $result= mysqli_query($GLOBALS['mysqli_connect'],$query) or die("error_bd: ".$query);
  if($result!== FALSE && gettype($result)!= "boolean") $GLOBALS['sqltbl']= mysqli_fetch_assoc($result);
  else unset($GLOBALS['sqltbl']);
  if(isset($GLOBALS['sqltbl']))return $GLOBALS['sqltbl'];
}
query_bd("SHOW TABLES FROM `".$database_db."` LIKE '".$GLOBALS['prefix_db']."_wallets';");
if(!isset($sqltbl['Tables_in_'.$database_db.' ('.$GLOBALS['prefix_db'].'_wallets)'])){
  $query= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = '+00:00';
DROP TABLE IF EXISTS `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_contacts`;
CREATE TABLE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_contacts` (
  `wallet` bigint(18) UNSIGNED NOT NULL DEFAULT '0',
  `number` tinyint(3) UNSIGNED NOT NULL,
  `recipient` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history`;
CREATE TABLE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` (
  `wallet` bigint(18) UNSIGNED NOT NULL DEFAULT '0',
  `recipient` bigint(18) UNSIGNED NOT NULL DEFAULT '0',
  `money` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `pin` bigint(18) UNSIGNED NOT NULL DEFAULT '0',
  `height` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `nodawallet` bigint(18) UNSIGNED NOT NULL DEFAULT '0',
  `nodause` varchar(40) NOT NULL DEFAULT '',
  `nodaown` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `date` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `signpubreg` varchar(514) NOT NULL DEFAULT '',
  `signreg` varchar(1440) NOT NULL DEFAULT '',
  `signpubnew` varchar(19) NOT NULL DEFAULT '',
  `signnew` varchar(1440) NOT NULL DEFAULT '',
  `signpub` varchar(514) NOT NULL DEFAULT '',
  `sign` varchar(1440) NOT NULL DEFAULT '',
  `hash` varchar(64) NOT NULL DEFAULT '',
  `checkhistory` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `checkemail` tinyint(1) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_referrals`;
CREATE TABLE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_referrals` (
  `wallet` bigint(18) UNSIGNED NOT NULL DEFAULT '0',
  `ref1` bigint(18) UNSIGNED NOT NULL DEFAULT '0',
  `ref2` bigint(18) UNSIGNED NOT NULL DEFAULT '0',
  `ref3` bigint(18) UNSIGNED NOT NULL DEFAULT '0',
  `money1` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `money2` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `money3` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `height` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `date` bigint(20) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_settings`;
CREATE TABLE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_settings` (
  `name` varchar(100) NOT NULL DEFAULT '',
  `value` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_settings` (`name`, `value`) VALUES
('synch_now', '0'),
('synch_wallet', '1'),
('transactionscount', '0'),
('version', '".$version."');
DROP TABLE IF EXISTS `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_users`;
CREATE TABLE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_users` (
  `wallet` bigint(18) UNSIGNED NOT NULL DEFAULT '0',
  `password` varchar(128) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT '',
  `up` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `down` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `nodatrue` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `date` bigint(20) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets`;
CREATE TABLE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` (
  `wallet` bigint(18) UNSIGNED NOT NULL DEFAULT '0',
  `ref1` bigint(18) UNSIGNED NOT NULL DEFAULT '0',
  `ref2` bigint(18) UNSIGNED NOT NULL DEFAULT '0',
  `ref3` bigint(18) UNSIGNED NOT NULL DEFAULT '0',
  `noda` varchar(40) NOT NULL DEFAULT '',
  `nodause` varchar(40) NOT NULL DEFAULT '',
  `balance` bigint(20) NOT NULL DEFAULT '0',
  `date` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `percent_ref` bigint(20) NOT NULL DEFAULT '0',
  `date_ref` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `height` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `signpubnew` varchar(514) NOT NULL DEFAULT '',
  `signnew` varchar(1440) NOT NULL DEFAULT '',
  `signpub` varchar(514) NOT NULL DEFAULT '',
  `sign` varchar(1440) NOT NULL DEFAULT '',
  `checkbalance` varchar(20) NOT NULL DEFAULT '',
  `checkbalanceall` varchar(20) NOT NULL DEFAULT '',
  `checkwallet` varchar(20) NOT NULL DEFAULT '',
  `view` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `checknoda` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_contacts`
  ADD PRIMARY KEY (`wallet`,`number`) USING BTREE;
ALTER TABLE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history`
  ADD PRIMARY KEY (`wallet`,`recipient`,`date`,`hash`) USING BTREE,
  ADD UNIQUE KEY `recipient` (`recipient`,`wallet`,`date`,`hash`) USING BTREE,
  ADD UNIQUE KEY `checkhistory` (`checkhistory`,`wallet`,`date`,`hash`) USING BTREE,
  ADD UNIQUE KEY `date` (`date`,`wallet`,`hash`) USING BTREE,
  ADD UNIQUE KEY `height` (`height`,`wallet`,`date`,`hash`) USING BTREE,
  ADD UNIQUE KEY `pin` (`pin`,`wallet`,`date`,`hash`);
ALTER TABLE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_referrals`
  ADD PRIMARY KEY (`date`,`wallet`) USING BTREE,
  ADD UNIQUE KEY `wallet` (`wallet`,`date`) USING BTREE,
  ADD UNIQUE KEY `ref1` (`ref1`,`wallet`,`date`) USING BTREE,
  ADD UNIQUE KEY `ref2` (`ref2`,`wallet`,`date`) USING BTREE,
  ADD UNIQUE KEY `ref3` (`ref3`,`wallet`,`date`) USING BTREE;
ALTER TABLE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_settings`
  ADD PRIMARY KEY (`name`) USING BTREE;
ALTER TABLE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_users`
  ADD PRIMARY KEY (`wallet`) USING BTREE,
  ADD UNIQUE KEY `date` (`date`,`wallet`) USING BTREE,
  ADD UNIQUE KEY `email` (`email`,`wallet`) USING BTREE,
  ADD UNIQUE KEY `nodatrue` (`nodatrue`,`wallet`) USING BTREE;
ALTER TABLE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets`
  ADD PRIMARY KEY (`wallet`) USING BTREE,
  ADD UNIQUE KEY `balance` (`balance`,`wallet`) USING BTREE,
  ADD UNIQUE KEY `noda` (`noda`,`wallet`) USING BTREE,
  ADD UNIQUE KEY `nodause` (`nodause`,`date`,`wallet`) USING BTREE,
  ADD UNIQUE KEY `ref1` (`ref1`,`wallet`) USING BTREE,
  ADD UNIQUE KEY `ref2` (`ref2`,`wallet`) USING BTREE,
  ADD UNIQUE KEY `ref3` (`ref3`,`wallet`) USING BTREE,
  ADD UNIQUE KEY `date` (`date`,`wallet`) USING BTREE,
  ADD UNIQUE KEY `created` (`wallet`) USING BTREE,
  ADD UNIQUE KEY `checknoda` (`checknoda`,`wallet`) USING BTREE,
  ADD UNIQUE KEY `checkwallet` (`checkwallet`,`wallet`) USING BTREE,
  ADD UNIQUE KEY `view` (`view`,`wallet`) USING BTREE;
COMMIT;";
  $result= mysqli_multi_query($mysqli_connect,$query) or die("error_install_bd");
  $mysqli_affected_rows=0;
  while(true){
    if(mysqli_more_results($mysqli_connect)){
      mysqli_next_result($mysqli_connect);
      $mysqli_affected_rows++;
      } else break;
  }
  if($mysqli_affected_rows==23){
		echo '{"install_bd": "true", "message": "Setting a task cron to start php script \'/egold.php synch\' (it is recommended) or \'http://'.(filter_var($noda_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)?"[".$noda_ip."]":$noda_ip).'/egold.php?type=synch\' every minute and wait for synchronization"}';
  } else {
    echo '{"install_bd": "false"}';
    $query= "DROP TABLE IF EXISTS `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_contacts`;
DROP TABLE IF EXISTS `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history`;
DROP TABLE IF EXISTS `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_referrals`;
DROP TABLE IF EXISTS `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_settings`;
DROP TABLE IF EXISTS `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_users`;
DROP TABLE IF EXISTS `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets`;
COMMIT;";
    $result= mysqli_multi_query($mysqli_connect,$query) or die("error_del_bd");
  }
  exit_now();
}
if(isset($_REQUEST['type']) && $_REQUEST['type']=="synch"){
	query_bd("SELECT `value` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_settings` WHERE `name`='version'");
	$sqltbl['value']= (float)str_replace('1.','',$sqltbl['value']);
	if(!isset($sqltbl['value']) || $sqltbl['value']<(float)str_replace('1.','',$version)){
		query_bd("REPLACE INTO `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_settings` SET `value`='".$version."', `name`='version';");
	}
}
$type['type']="a-z";
$type['wallet']="0-9";
$type['recipient']=$type['wallet'];
$type['history']="0-9";
$type['ref1']=$type['wallet'];
$type['ref2']=$type['wallet'];
$type['ref3']=$type['wallet'];
$type['money']="0-9";
$type['pin']="0-9";
$type['height']="0-9";
$type['signpubnew']="0-9";
$type['signpub']="0-9a-z";
$type['signpubreg']=$type['signpub'];
$type['signpubnew_check']=$type['signpub'];
$type['sign']="0-9a-z:";
$type['signnew']=$type['sign'];
$type['signreg']=$type['sign'];
$type['date']="0-9";
$type['dateto']=$type['date'];
$type['dateview']="1";
$type['noda']="0-9a-z.:";
$type['nodawallet']=$type['wallet'];
$type['nodause']=$type['noda'];
$type['balancestart']="0-9";
$type['balancefinish']="0-9";
$type['nodausewalletstart']="0-9";
$type['nodausewalletfinish']="0-9";
$type['nodaown']="0-1";
$type['order']="a-z";
$type['start']="0-9";
$type['limit']="0-9";
$type['all']="0-3";
$type['password']="0-9a-z";
$type['up']="0-9";
$type['down']="0-9";
$type['ref']="0-9";
$type['ref1']="0-9";
$type['ref2']="0-9";
$type['ref3']="0-9";
$type['email']="0-9";
$type['wallets_with_noda_first']="1";
$type['synch_wallet']="0-9";
foreach($_REQUEST as $key=> $val) if(strlen($key)<100 && $val && strlen($val)<1440 && in_array($key,array_keys($type))) $request[$key]= preg_replace("/[^".$type[$key]."]/",'',$val);
include __DIR__ .'/egold_crypto/falcon.php';
function bchexdec($hex){
	$dec = 0; $len = strlen($hex);
	for ($i = 1; $i <= $len; $i++)$dec = bcadd($dec, bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i))));
	return $dec;
}
function sha_dec($str){return substr(bchexdec(gen_sha3($str,19)),0,19);}
function signcheck($str,$signpub,$sign){return ($signpub && $str && $sign && Falcon\verify($signpub, $str, $sign))? 1: 0;}
if(isset($request['signpubnew_check'])){
	if(signcheck($request['wallet'].$request['height'],$request['signpubnew_check'],$request['signnew'])!=1){
		$json_arr['signpubnew_check']= 'false';
		$stop=1;
	} else $json_arr['signpubnew_check']= 'true';
}
if(isset($request['email']) && isset($request['password'])){
	function intToChar($str){
		$intStr= str_split($str, 4);
		$outText= '';
		for($j= 0;$j<count($intStr);$j++){$outText.= chr($intStr[$j]);}
		return $outText;
	}
	function xor_this($text,$key){
		$outText= '';
		for($i=0;$i<strlen($text);){for($j=0;$j<strlen($key)&&$i<strlen($text);$j++,$i++){$outText.= $text{$i}^$key{$j};}}
		return $outText;
	}
	query_bd("SELECT `password` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_users` WHERE `wallet`= '".$request['wallet']."' and `nodatrue`=1 LIMIT 1;");
	if(isset($sqltbl['password']) && gen_sha3($sqltbl['password'],256)==$request['password']){
		$request['email']= xor_this(intToChar($request['email']),$sqltbl['password']);
		if(filter_var($request['email'], FILTER_VALIDATE_EMAIL) === false || mysqli_real_escape_string($mysqli_connect,$request['email'])!=$request['email']){
			$stop=1;
		}
	} else $stop=1;
}
if(isset($request['noda']) && !filter_var($request['noda'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && !filter_var($request['noda'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){
  $json_arr['noda_ip_check']= 'false';
  $stop=1;
}
if(isset($request['nodause']) && !filter_var($request['nodause'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && !filter_var($request['nodause'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){
  $json_arr['nodause_ip_check']= 'false';
  $stop=1;
}
if(!isset($request))$stop=1;
delay_now();
$json_arr['time']= strval(strtotime("now"));
$json_arr['noda']= $noda_ip;
function gold_wallet_view($wallet){return 'G-'.substr($wallet,0,4).'-'.substr($wallet,4,5).'-'.substr($wallet,9,4).'-'.substr($wallet,13,5);}
function timer($time){
	global $json_arr;
	$timer= microtime(true)-$json_arr['timer_start'];
	if($timer<$time){
		$timer=$time-$timer;
		if($timer<=0)$timer=0.1;
		usleep($timer*1000000);
	}
}
function wallet($wallet,$time,$checkhistory){
	global $sqltbl;
	if(strlen($wallet)==18){
		query_bd("SELECT * FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `wallet`='".$wallet."' and `view`>0  LIMIT 1;");
		if(isset($sqltbl['wallet']) && $sqltbl['wallet']) {
			$wallet_return= $sqltbl;
			$wallet_return['balancecheck']= 0;
			if($checkhistory==0){
				query_bd("SELECT SUM(`money`+2) as balancecheck FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` WHERE `wallet`='".$wallet."' and `checkhistory`=0 LIMIT 1;");
				if(isset($sqltbl['balancecheck']) && $sqltbl['balancecheck']){
					$wallet_return['balancecheck']= (int)$sqltbl['balancecheck'];
					$wallet_return['balance']= $wallet_return['balance']-$sqltbl['balancecheck'];
			 }
			} 
			$timepercent= $time-$wallet_return['date'];
			if($timepercent>0){
				if($timepercent>315360000)$timepercent= 315360000;
				$balance_tmp= $wallet_return['balance']+$wallet_return['balancecheck'];
				$wallet_return['percent_4']= (int)($balance_tmp*(POW($GLOBALS['percent_4'],$timepercent)-1));
				$wallet_return['percent_5']= (int)($balance_tmp*(POW($GLOBALS['percent_5'],$timepercent)-1));
			} else {
				$wallet_return['percent_4']=0;
				$wallet_return['percent_5']=0;
			}
			return $wallet_return;
		} else return 0;
	} else return -1;
}
if($stop==1){
	query_bd("SELECT `wallet` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `noda`='".$noda_ip."' ORDER BY `date` DESC LIMIT 1;");
	if(isset($sqltbl['wallet']) && $sqltbl['wallet'])$json_arr['owner']= gold_wallet_view($sqltbl['wallet']);
	else $json_arr['owner']=gold_wallet_view($noda_wallet);
	query_bd("SELECT `value` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_settings` WHERE `name`='transactionscount' LIMIT 1;");
	if(isset($sqltbl['value']) && $sqltbl['value'])$json_arr['transactionscount']= $sqltbl['value'];
	else $json_arr['transactionscount']= 0;
	query_bd("SELECT `date` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `view`>0 ORDER BY `date` DESC LIMIT 1;");
	if(isset($sqltbl['date']) && $sqltbl['date'])$json_arr['datelasttransaction']= $sqltbl['date'];
	else $json_arr['datelasttransaction']= 0;
}
if($stop==1 || $request['type']=="wallet"){
	if(isset($email_domain) && $email_domain)$json_arr['email_domain']= $email_domain;
	if(isset($noda_site) && $noda_site)$json_arr['noda_site']= $noda_site;
}
if($stop==1 && isset($noda_site) && $noda_site)$json_arr['noda_site']= $noda_site;
if((isset($request['type']) && $request['type']=="synch") || (isset($request['nodause']) && isset($request['date']) && isset($request['nodawallet'])))$json_arr['send_noda']= 1;
else $json_arr['send_noda']= 0;
if($stop!=1 && ($request['type']=="send" || $request['type']=="history") && (!isset($request['pin']) || !$request['pin'])){
	$request['pin']=0;
}
if($stop!=1 && ($request['type']=="height" || ($request['type']=="send" && $json_arr['send_noda']!=1))) {
  if(!isset($request['wallet']) || strlen($request['wallet'])!=18){echo '{"wallet":"false"}';exit_now();}
  if(isset($request['nodause']))$nodause= $request['nodause'];
  else $nodause= $noda_ip;
  $wallet= wallet($request['wallet'],$json_arr['time'],0);
  if(isset($wallet['height']) && isset($wallet['date']) && isset($wallet['view']) && ($wallet['view']==1 || $wallet['view']==3)){
    $json_arr['balance']= $wallet['balance']+($wallet['noda'] && $wallet['noda']==$noda_ip?$wallet['percent_5']:$wallet['percent_4']);
    $json_arr['height']= $wallet['height'];
    $json_arr['date']= $wallet['date'];
    query_bd("SELECT `height`,`date` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` WHERE `wallet`= '".$request['wallet']."' and `checkhistory`=0 and `height`>'".$wallet['height']."' ORDER BY `height` DESC,`date` DESC LIMIT 1;");
    if(isset($sqltbl['height'])){
      $json_arr['height']= $sqltbl['height'];
      $json_arr['date']= $sqltbl['date'];
    }
    if(isset($request['height']) && (int)$request['height']>=0 && (int)$request['height']==$request['height'] && $json_arr['height']!=$request['height']-1){echo '{"height":"false"}';exit_now();}
    if($request['type']=="height")$stop=1;
    else if($json_arr['time']-$json_arr['date']<=4){echo '{"send":"timeout"}';exit_now();}
  } else {echo '{"wallet":"unavailable"}';exit_now();}
  unset($nodause);
}
if($stop!=1){
  function connect_noda_multi($urls,$path,$post,$timer){
    global $mysqli_connect,$sqltbl,$noda_ip,$json_arr;
		usleep(mt_rand(0.5*1000000,0.55*1000000));
    if(!is_array($urls)){
      $url= $urls; 
      $urls= array();
      $urls[]= $url;
    }
    if(isset($urls) && count($urls)>=1){
      $multi= curl_multi_init();
      $channels= array();
      $json_get_arr= array();
      foreach ($urls as $url){
        $ch= curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://".(filter_var($url, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)?"[".$url."]":$url).'/egold.php'.$path);
        curl_setopt($ch, CURLOPT_HEADER, false);
        if($post) {
          curl_setopt($ch, CURLOPT_POST, true);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timer*1000);
        curl_setopt($ch, CURLOPT_IPRESOLVE, (filter_var($noda_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)?CURL_IPRESOLVE_V6:CURL_IPRESOLVE_V4));
        curl_multi_add_handle($multi, $ch);
        $channels[$url]= $ch;
      }
      $active= null;
      do $mrc= curl_multi_exec($multi, $active);
      while ($mrc== CURLM_CALL_MULTI_PERFORM);
      while ($active && $mrc== CURLM_OK){
        if (curl_multi_select($multi)== -1)continue;
        do $mrc= curl_multi_exec($multi, $active);
        while ($mrc== CURLM_CALL_MULTI_PERFORM);
      }
      foreach ($channels as $channel=> $val){
        $json_get_arr[$channel]= json_decode(trim(curl_multi_getcontent($val)),true);
        curl_multi_remove_handle($multi, $val);
      }
      curl_multi_close($multi);
      return $json_get_arr;
    }
  }
  function random($array,$count){
    if(is_array($array) && count($array)>$count){
      $keys = array_keys($array);
      shuffle($keys);
      foreach($keys as $key)$new[$key] = $array[$key];
      $array = array_slice($new,0,$count);
    } 
    return $array;
  }
  function wallet_check(){
    global $json_arr,$sqltbl,$mysqli_connect,$noda_ip;
    $query= "SELECT * FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` WHERE `checkhistory`=0 and `date`<".(strtotime(date("Y-m-d H:i:00",$json_arr['time']))-60)." ORDER BY `date`,`wallet`,`height` LIMIT 100000;";
    $result_arr= mysqli_query($mysqli_connect,$query) or die("error_synch_readsynch");
    while($sqltbl_arr= mysqli_fetch_array($result_arr,MYSQLI_ASSOC)){
			$wallet_update= 0;
      $wallet= wallet($sqltbl_arr['wallet'],$sqltbl_arr['date'],1);
			if(!isset($wallet['wallet']) || $wallet['wallet']!=$sqltbl_arr['wallet'] || $wallet['height']!=$sqltbl_arr['height']-1){
				usleep(100);
				$wallet= wallet($sqltbl_arr['wallet'],$sqltbl_arr['date'],1);
			}
			$wallet_percent=($sqltbl_arr['nodaown']==1?$wallet['percent_5']:$wallet['percent_4']);
      if($sqltbl_arr['checkhistory']==0 && isset($wallet['wallet']) && $wallet['view']>0 && $wallet['height']==$sqltbl_arr['height']-1 && (($wallet['signpubnew']=='' && $wallet['signpub']==$sqltbl_arr['signpub']) || ($wallet['signpubnew']== sha_dec($sqltbl_arr['signpub']) && signcheck($wallet['wallet'].$wallet['height'],$sqltbl_arr['signpub'],$wallet['signnew'])==1)) 
        && signcheck($wallet['wallet'].($sqltbl_arr['signpubreg'] && $sqltbl_arr['signreg']?'00':$sqltbl_arr['recipient']).$sqltbl_arr['money'].$sqltbl_arr['pin'].$sqltbl_arr['height'].$sqltbl_arr['nodause'].($sqltbl_arr['signpubreg'] && $sqltbl_arr['signreg']?$sqltbl_arr['signpubreg'].$sqltbl_arr['signreg']:'').($sqltbl_arr['signpubnew'] && $sqltbl_arr['signnew']?$sqltbl_arr['signpubnew'].$sqltbl_arr['signnew']:''),$sqltbl_arr['signpub'],$sqltbl_arr['sign'])==1){
        $wallet_balance= $wallet['balance']+$wallet['percent_ref']+$wallet_percent-($sqltbl_arr['money']+2);
        if($sqltbl_arr['checkhistory']==0 && $wallet_balance>=0){
					$recipient= wallet($sqltbl_arr['recipient'],$sqltbl_arr['date'],1);
					if(isset($recipient['wallet'])){
						$recipient_percent=($recipient['noda']==$recipient['nodause']?$recipient['percent_5']:$recipient['percent_4']);
						$recipient_balance= $recipient['balance']+$recipient['percent_ref']+$recipient_percent+$sqltbl_arr['money'];
						query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `balance`='".$recipient_balance."',`percent_ref`=0,`date`=IF(`date`<'".$sqltbl_arr['date']."','".$sqltbl_arr['date']."',`date`),`date_ref`=`date`,`view`=IF(`view`=1,3,`view`) WHERE `wallet`='".$recipient['wallet']."';");
					} else if($sqltbl_arr['signpubreg'] && $sqltbl_arr['signreg']){
						query_bd("INSERT IGNORE INTO `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `wallet`= '".$sqltbl_arr['recipient']."',`ref1`= '".$wallet['wallet']."',`ref2`= '".$wallet['ref1']."',`ref3`= '".$wallet['ref2']."',`balance`= '".$sqltbl_arr['money']."',`height`= '0',`date` = '".$sqltbl_arr['date']."',`percent_ref`=0,`date_ref`=`date`,`signpub`= '".$sqltbl_arr['signpubreg']."',`sign`= '".$sqltbl_arr['signreg']."',`checkwallet`='".$json_arr['time']."',`view`=3;");
					}
					query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `noda`='".($sqltbl_arr['nodaown']==1?$sqltbl_arr['nodause']:'')."',`nodause`='".$sqltbl_arr['nodause']."',`balance`='".$wallet_balance."',`percent_ref`=0,`height`='".$sqltbl_arr['height']."',`date`=IF(`date`<'".$sqltbl_arr['date']."','".$sqltbl_arr['date']."',`date`),`date_ref`=`date`,`view`=IF(`view`=1,3,`view`), `signpubnew`='".$sqltbl_arr['signpubnew']."',`signnew`='".$sqltbl_arr['signnew']."',`signpub`='".$sqltbl_arr['signpub']."',`sign`='".$sqltbl_arr['sign']."' WHERE `wallet`='".$wallet['wallet']."';");
          if(mysqli_affected_rows($mysqli_connect)>=1){
						$wallet_update= 1;
						if($wallet_percent/4>=1 && isset($wallet['ref1']) && $wallet['ref1']>1){
							query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `percent_ref`=`percent_ref`+'".(int)($wallet_percent/4)."', `view`=IF(`view`=1,3,`view`) WHERE `wallet`='".$wallet['ref1']."';");
						}
						if($wallet_percent/8>=1 && isset($wallet['ref2']) && $wallet['ref2']>1){
							query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `percent_ref`=`percent_ref`+'".(int)($wallet_percent/8)."', `view`=IF(`view`=1,3,`view`) WHERE `wallet`='".$wallet['ref2']."';");
						}
						if($wallet_percent/16>=1 && isset($wallet['ref3']) && $wallet['ref3']>1){
							query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `percent_ref`=`percent_ref`+'".(int)($wallet_percent/16)."', `view`=IF(`view`=1,3,`view`) WHERE `wallet`='".$wallet['ref3']."';");
						}
						if(isset($recipient['wallet']) && isset($recipient_percent)){
							if($recipient_percent/4>=1 && isset($recipient['ref1']) && $recipient['ref1']>1){
								query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `percent_ref`=`percent_ref`+'".(int)($recipient_percent/4)."', `view`=IF(`view`=1,3,`view`) WHERE `wallet`='".$recipient['ref1']."';");
							}
							if($recipient_percent/8>=1 && isset($recipient['ref2']) && $recipient['ref2']>1){
								query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `percent_ref`=`percent_ref`+'".(int)($recipient_percent/8)."', `view`=IF(`view`=1,3,`view`) WHERE `wallet`='".$recipient['ref2']."';");
							}
							if($recipient_percent/16>=1 && isset($recipient['ref3']) && $recipient['ref3']>1){
								query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `percent_ref`=`percent_ref`+'".(int)($recipient_percent/16)."', `view`=IF(`view`=1,3,`view`) WHERE `wallet`='".$recipient['ref3']."';");
							}
						}
            if($sqltbl_arr['nodaown']==1){
              query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `noda`='', `view`=IF(`view`=1,3,`view`) WHERE `noda`='".$sqltbl_arr['nodause']."' and `wallet`!='".$wallet['wallet']."';");
            }
						if($sqltbl_arr['nodawallet']!=$wallet['wallet'])$nodawallet= wallet($sqltbl_arr['nodawallet'],$sqltbl_arr['date'],1);
						if(isset($nodawallet) && isset($nodawallet['date']) && $nodawallet['noda']==$nodawallet['nodause'] && $nodawallet['percent_5']>1 && $sqltbl_arr['date']>$nodawallet['date']+24*60*60){
							query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `balance`=`balance`+'".$nodawallet['percent_5']."'+1, `date`=IF(`date`<'".$sqltbl_arr['date']."','".$sqltbl_arr['date']."',`date`), `view`=IF(`view`=1,3,`view`) WHERE `wallet`='".$sqltbl_arr['nodawallet']."';");
							unset($nodawallet);
						} else {
							query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `balance`=`balance`+1 ".(mt_rand(1,41)==1?', `view`=IF(`view`=1,3,`view`)':'')." WHERE `wallet`='".$sqltbl_arr['nodawallet']."';");
						}
            query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` SET `checkhistory`=1 WHERE `date`= '".$sqltbl_arr['date']."' and `hash`= '".$sqltbl_arr['hash']."' and `wallet`= '".$sqltbl_arr['wallet']."';");
            if(mysqli_affected_rows($mysqli_connect)>=1){
              if($sqltbl_arr['nodause']==$noda_ip){
                query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_users` SET `nodatrue`=1, `date`='".$sqltbl_arr['date']."' WHERE `wallet`= '".$sqltbl_arr['wallet']."';");
								query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_settings` SET `value`=`value`+1 WHERE `name`='transactionscount';");
								if(mysqli_affected_rows($mysqli_connect)>=1){}
              }else query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_users` SET `nodatrue`=0, `date`='".$sqltbl_arr['date']."' WHERE `wallet`= '".$sqltbl_arr['wallet']."' and `nodatrue`!='0';");
              if(mysqli_affected_rows($mysqli_connect)>=1){}
            }
          }
          query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` SET `checkhistory`=2 WHERE `checkhistory`=0 and `wallet`= '".$sqltbl_arr['wallet']."' and `height`<= '".$sqltbl_arr['height']."' and `date`<".(strtotime(date("Y-m-d H:i:00",$json_arr['time']))-60).";");
          if(mysqli_affected_rows($mysqli_connect)>=1 && $wallet['view']==1){
						query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `view`=IF(`view`=1,3,`view`) WHERE `wallet`='".$wallet['wallet']."' and `view`>0;");			
					}
          $sqltbl_arr['checkhistory']= 1;
        }
      } else if($sqltbl_arr['checkhistory']==0){
				query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` SET `checkhistory`=1 WHERE `date`= '".$sqltbl_arr['date']."' and `hash`= '".$sqltbl_arr['hash']."' and `wallet`= '".$wallet['wallet']."' and `height`= '".$wallet['height']."' and `sign`= '".$wallet['sign']."' and `checkhistory`!=1 LIMIT 1;");
				if(mysqli_affected_rows($mysqli_connect)>=1){
					$wallet_update=1;
					$recipient= wallet($sqltbl_arr['recipient'],$sqltbl_arr['date'],1);
					if(isset($recipient['wallet']))$recipient_percent=($recipient['noda']==$recipient['nodause']?$recipient['percent_5']:$recipient['percent_4']);
					$wallet_in= "'".$sqltbl_arr['nodawallet']."','".$wallet['ref1']."','".$wallet['ref2']."','".$wallet['ref3']."'".(isset($recipient['wallet'])?",'".$recipient['wallet']."','".$recipient['ref1']."','".$recipient['ref2']."','".$recipient['ref3']."'":"");
					query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `view`=IF(`view`=1,3,`view`) WHERE `wallet` IN (".$wallet_in.");");
				}
        query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` SET `checkhistory`=2 WHERE `checkhistory`=0 and `wallet`= '".$sqltbl_arr['wallet']."' and `height`>= '".$sqltbl_arr['height']."' and `date`>= '".$sqltbl_arr['date']."' and `date`<".(strtotime(date("Y-m-d H:i:00",$json_arr['time']))-60).";");
        if(mysqli_affected_rows($mysqli_connect)>=1){}
      }
			if($wallet_update==1){
				if(isset($wallet['wallet']) && isset($wallet['ref1']) && isset($wallet['ref2']) && isset($wallet['ref3']) && isset($wallet_percent) && $wallet_percent/4>=1 && $wallet['ref1']>1){
					query_bd("DELETE FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_referrals` WHERE `wallet`= '".$sqltbl_arr['wallet']."' and `height`>= '".$sqltbl_arr['height']."';");
					if(mysqli_affected_rows($mysqli_connect)>=1){}
					query_bd("REPLACE INTO `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_referrals` SET `wallet`= '".$sqltbl_arr['wallet']."',`ref1`= '".$wallet['ref1']."',`ref2`= '".$wallet['ref2']."',`ref3`= '".$wallet['ref3']."',`money1`= '".($wallet['ref1']>1?(int)($wallet_percent/4):'0')."',`money2`= '".($wallet['ref2']>1?(int)($wallet_percent/8):'0')."',`money3`= '".($wallet['ref3']>1?(int)($wallet_percent/16):'0')."',`height` = '".$sqltbl_arr['height']."',`date` = '".$sqltbl_arr['date']."';");
				}
				if(isset($recipient['wallet']) && isset($recipient['ref1']) && isset($recipient['ref2']) && isset($recipient['ref3']) && isset($recipient_percent) && $recipient_percent/4>=1 && $recipient['ref1']>1){
					query_bd("DELETE FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_referrals` WHERE `wallet`= '".$sqltbl_arr['recipient']."' and `height`>= '".$sqltbl_arr['height']."';");
					if(mysqli_affected_rows($mysqli_connect)>=1){}
					query_bd("REPLACE INTO `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_referrals` SET `wallet`= '".$sqltbl_arr['recipient']."',`ref1`= '".$recipient['ref1']."',`ref2`= '".$recipient['ref2']."',`ref3`= '".$recipient['ref3']."',`money1`= '".($recipient['ref1']>1?(int)($recipient_percent/4):'0')."',`money2`= '".($recipient['ref2']>1?(int)($recipient_percent/8):'0')."',`money3`= '".($recipient['ref3']>1?(int)($recipient_percent/16):'0')."',`height` = '".$sqltbl_arr['height']."',`date` = '".$sqltbl_arr['date']."';");
				}
			}
      unset($wallet);
      unset($recipient);
    }
    if(mysqli_affected_rows($mysqli_connect)>=1){}
  }
  function send($request,$synch){
    global $json_arr,$sqltbl,$mysqli_connect,$noda_wallet,$noda_ip,$host_ip;
		delay_now();
    $stop=0;
    unset($json_arr['walletnew']);
    unset($json_arr['recipient']);
    unset($json_arr['height']);
    unset($json_arr['transaction']);
    unset($json_arr['wallet']);
    unset($json_arr['balance']);
    unset($json_arr['send']);
    unset($walletnew);
    $request['height']= $request['height'];
    if($json_arr['send_noda']==1){
      $nodawallet= $request['nodawallet'];
      $nodause= $request['nodause'];
      $nodaown= (isset($request['nodaown']) && $request['nodaown']==1?1:0);
      $datecheck= $request['date'];
    } else {
      $nodawallet= $noda_wallet;
      $nodause= $noda_ip;
      $datecheck= $json_arr['time'];
    }
    $wallet= wallet($request['wallet'],$datecheck,0);
    if(!isset($nodaown)){
      $nodaown= ($noda_wallet==$wallet['wallet']?1:0);
    }
    if(isset($wallet['wallet'])){
      if($wallet['height']>=$request['height']){
        $json_arr['height']= 'false';
        $stop=1;
      } else if($json_arr['send_noda']!=1 && $wallet['view']!=1 && $wallet['view']!=3){
        $json_arr['wallet']= 'unavailable';
        $stop=1;
      }
    } else {
      $json_arr['wallet']= 'false';
      $stop=1;
    }
    if($wallet['date']>=$datecheck && $datecheck>$json_arr['time']){
      $json_arr['date']= 'false';
      $stop=1;
    }
    $request_sha_temp['wallet']= $request['wallet'];
    $request_sha_temp['recipient']= $request['recipient'];
    $request_sha_temp['money']= $request['money'];
		$request_sha_temp['pin']= $request['pin'];
    $request_sha_temp['height']= $request['height'];
    $request_sha_temp['nodawallet']= $nodawallet;
    $request_sha_temp['nodause']= $nodause;
    $request_sha_temp['nodaown']= $nodaown;
    $request_sha_temp['date']= $datecheck;
    $request_sha= gen_sha3(json_encode($request_sha_temp),64);
    $sqltbl= query_bd("SELECT `height` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` WHERE `wallet`= '".$request['wallet']."' and `height`= '".$request['height']."' and (`hash`= '".$request_sha."' or (`recipient`= '".$request['recipient']."' and `money`= '".$request['money']."' and `pin`= '".$request['pin']."' and `nodawallet`= '".$nodawallet."' and `nodause`= '".$nodause."' and `date`= '".$datecheck."')) LIMIT 1;");
    if(isset($sqltbl['height'])){
      $json_arr['height']= 'double0';
    } else {
      query_bd("INSERT IGNORE INTO `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` SET `wallet`= '".$request['wallet']."', `height`= '".$request['height']."', `pin`= '".$request['pin']."', `date`= '".$datecheck."', `nodause`= '".$nodause."', `hash`= '".$request_sha."', `checkhistory`=9;");
      if(mysqli_affected_rows($mysqli_connect)>=1){
        $sqltbl= query_bd("SELECT `wallet`,`balance` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `noda`= '".$nodause."' ORDER BY `date` DESC LIMIT 1;");
        if(isset($sqltbl['wallet']) && $sqltbl['wallet']){
          $nodawallet=$sqltbl['wallet'];
          if($nodawallet==$wallet['wallet'])$nodaown=1;
          else $nodaown=0;
        }
        if($request['recipient']==1)$recipient['wallet']=1;
        else if(isset($request['signpubreg']) && isset($request['signreg']) && signcheck($wallet['wallet'].'00'.$request['money'].$request['pin'].$request['height'].$nodause.$request['signpubreg'].$request['signreg'].(isset($request['signpubnew']) && isset($request['signnew'])?$request['signpubnew'].$request['signnew']:''),$request['signpub'],$request['sign'])==1 && signcheck('30'.sha_dec($request['signpubreg']),$request['signpubreg'],$request['signreg'])==1){
          if($request['recipient']=='00'){
            function genwallet(){
              global $sqltbl,$mysqli_connect;
              $wallet_temp= (string)mt_rand(100000000000000001,999999999999999999);
                if($wallet_temp && strlen((int)$wallet_temp)==18)query_bd("SELECT `wallet` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `wallet`= '".$wallet_temp."' LIMIT 1;");
                else genwallet();
                if(isset($sqltbl['wallet']))genwallet();
                else {
									$sqltbl= query_bd("SELECT `wallet` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` WHERE `wallet`= '".$wallet_temp."' or `recipient`= '".$wallet_temp."' LIMIT 1;");
									if(isset($sqltbl['wallet']))genwallet();
									else return $wallet_temp;
                }
            }
            $walletnew= genwallet();
            for($r=0;!$walletnew && $r<=10;$r++)$walletnew= genwallet();
            if($walletnew)$recipient['wallet']= $walletnew;
            else $json_arr['walletnew']= "false";
          } else if($request['recipient']>1 && strlen($request['recipient'])==18){
            $sqltbl= query_bd("SELECT `wallet` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `wallet`= '".$request['recipient']."' LIMIT 1;");
            if(isset($sqltbl['wallet']))$json_arr['walletnew']= "false";
            else {
              $sqltbl= query_bd("SELECT `wallet` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` WHERE `wallet`= '".$request['recipient']."' or `recipient`= '".$request['recipient']."' LIMIT 1;");
              if(isset($sqltbl['wallet']))$json_arr['walletnew']= "false";
              else {
                $walletnew= $request['recipient'];
                $recipient['wallet']= $walletnew;
              }
            }
          } else $json_arr['walletnew']= "false";
        } else if($request['recipient']>1)$recipient= wallet($request['recipient'],0,0);
        if(!isset($recipient['wallet']))$json_arr['recipient']= 'false';
        else if($recipient['wallet']>0 && signcheck($wallet['wallet'].(isset($request['signpubreg']) && isset($request['signreg'])?'00':$request['recipient']).$request['money'].$request['pin'].$request['height'].$nodause.(isset($request['signpubreg']) && isset($request['signreg'])?$request['signpubreg'].$request['signreg']:'').(isset($request['signpubnew']) && isset($request['signnew'])?$request['signpubnew'].$request['signnew']:''),$request['signpub'],$request['sign'])==1){
          $sqltbl= query_bd("SELECT `date`, `money`, `pin`, `height`, `signpubnew`, `signnew`, `signpub`, `sign` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` WHERE `wallet`= '".$wallet['wallet']."' and `height`> '".$wallet['height']."' and `checkhistory`=0 ORDER BY `height` DESC LIMIT 1;");
          if(isset($sqltbl['height']) && $sqltbl['height']){
            if($sqltbl['signpubnew']!=''){
              $wallet['signpubnew']=$sqltbl['signpubnew'];
              $wallet['signnew']=$sqltbl['signnew'];
              $wallet['signpub']=$sqltbl['signpub'];
              $wallet['sign']=$sqltbl['sign'];
            } else {
              $wallet['signpubnew']='';
              $wallet['signnew']='';
              $wallet['signpub']=$sqltbl['signpub'];
              $wallet['sign']=$sqltbl['sign'];
            }
            $wallet['height']=$sqltbl['height'];
            $wallet['date']=$sqltbl['date'];
            $wallet['money']=$sqltbl['money'];
						$wallet['pin']=$sqltbl['pin'];
         }
          if($synch==1 && $wallet['height']<$request['height']-1){
            $post['type']= 'history';
            $post['wallet']= $wallet['wallet'];
            $post['height']= $wallet['height']+1;
            $post['order']= 'asc';
            $post['limit']= 100;
            $post['all']= 3;
           $wallet= transaction_check($host_ip,$post,$wallet,$request);
          }
          if($wallet['height']<$request['height']-1)$checkhistory= -1;
          else if($wallet['height']==$request['height']-1 && (($wallet['signpubnew']=='' && $wallet['signpub']==$request['signpub']) || ($wallet['signpubnew']== sha_dec($request['signpub']) && signcheck($wallet['wallet'].$wallet['height'],$request['signpub'],$wallet['signnew'])==1))){
            $checkhistory= 0;
          } else {
            $sqltbl= query_bd("SELECT `nodause`, `height`, `signpubnew`, `signnew`, `signpub`, `checkhistory` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` WHERE `wallet`= '".$wallet['wallet']."' and `height`= '".($request['height']-1)."' and `signpubnew`=IF(`signpubnew`='','','".sha_dec($request['signpub'])."') and `signpub`=IF(`signpubnew`='','".$request['signpub']."',`signpub`) and `checkhistory`<2 ORDER BY `date` DESC LIMIT 1;");
           if(isset($sqltbl['height']) && ($sqltbl['signpubnew']=='' || signcheck($wallet['wallet'].$sqltbl['height'],$request['signpub'],$sqltbl['signnew'])==1)){
              if($sqltbl['checkhistory']<2)$checkhistory= 0;
              else $checkhistory= 2;
            } else {
              $checkhistory= -1;
            }
          }
          if($checkhistory>=0){
						delay_now();
						query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` SET `recipient`= '".$recipient['wallet']."',`money`= '".$request['money']."',`nodawallet`= '".$nodawallet."',`nodaown`= '".$nodaown."',`signpubreg`= '".(isset($request['signpubreg']) && isset($request['signreg'])?$request['signpubreg']:'')."',`signreg`= '".(isset($request['signpubreg']) && isset($request['signreg'])?$request['signreg']:'')."',`signpubnew`= '".(isset($request['signpubnew']) && isset($request['signnew'])?$request['signpubnew']:'')."',`signnew`= '".(isset($request['signpubnew']) && isset($request['signnew'])?$request['signnew']:'')."',`signpub`= '".$request['signpub']."',`sign`= '".$request['sign']."',`checkhistory`= '".$checkhistory."' WHERE `wallet` = (SELECT * FROM (SELECT `wallet` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` WHERE `wallet`='".$request['wallet']."' and `height`='".$request['height']."' and `hash`='".$request_sha."' LIMIT 1) as t) and `height`='".$request['height']."' and `hash`='".$request_sha."' and `sign`!= '".$request['sign']."' and `date`= '".$datecheck."' and `pin`= '".$request['pin']."' and `nodause`= '".$nodause."';");
						if(mysqli_affected_rows($mysqli_connect)>=1){
							delay_now();
							if(isset($GLOBALS['filename_tmp_send']) && !file_exists($GLOBALS['filename_tmp_send']))file_put_contents($GLOBALS['filename_tmp_send'], "");
							if(isset($GLOBALS['host_ip_check_file']) && file_exists($GLOBALS['host_ip_check_file']))@unlink($GLOBALS['host_ip_check_file']);
              if($checkhistory!=0){
                $json_arr['error']= 'send';
              } else {
								$sqltbl_count= query_bd("SELECT COUNT(`checkhistory`) as count FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` WHERE `wallet`= '".$request['wallet']."' and (`date`>= ".$datecheck."-3 and `date`<= ".$datecheck."+3) LIMIT 1;");
								if(isset($sqltbl_count['count']) && (int)$sqltbl_count['count']>1){
									$json_arr['error']= 'send';
									query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` SET `checkhistory`=2 WHERE `checkhistory`=0 and `wallet`= '".$request['wallet']."' and (`date`>= ".$datecheck."-3 and `date`<= ".$datecheck."+3);");
									if(mysqli_affected_rows($mysqli_connect)>=1){}
								} else {
									$json_arr['send']= 'true';
								}
              }
              delay_now();
              if($recipient['wallet']>1)$json_arr['recipient']= gold_wallet_view($recipient['wallet']);
              if(isset($walletnew))$json_arr['walletnew']= gold_wallet_view($walletnew);
              if($synch==1){
								$query= "SELECT `noda` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `noda`!='' and `noda`<'".$noda_ip."' and `noda`!='".$host_ip."' ORDER BY `noda` DESC LIMIT 3;";
                $result= mysqli_query($mysqli_connect,$query) or die("error_synchnow");
                while($sqltbl_arr= mysqli_fetch_array($result,MYSQLI_ASSOC))$nodas_synch_main[$sqltbl_arr['noda']]= $sqltbl_arr['noda'];
								$query= "SELECT `noda` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `noda`!='' and `noda`>'".$noda_ip."' and `noda`!='".$host_ip."' ORDER BY `noda` ASC LIMIT 3;";
                $result= mysqli_query($mysqli_connect,$query) or die("error_synchnow");
                while($sqltbl_arr= mysqli_fetch_array($result,MYSQLI_ASSOC))$nodas_synch_main[$sqltbl_arr['noda']]= $sqltbl_arr['noda'];
                $query= "SELECT `noda` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `noda`!='' and `noda`!='".$noda_ip."' and `noda`!='".$host_ip."' and `checknoda`<= '".$json_arr['time']."' and `balance`>=100 and `view`>0 ORDER BY RAND() LIMIT 64;";
                $result= mysqli_query($mysqli_connect,$query) or die("error_synchnow");
                while($sqltbl_arr= mysqli_fetch_array($result,MYSQLI_ASSOC))if(!in_array($sqltbl_arr['noda'],$nodas_synch_main))$nodas_synch[$sqltbl_arr['noda']]= $sqltbl_arr['noda'];
								$query= "SELECT `noda` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `noda`!='' and `noda`!='".$noda_ip."' and `noda`!='".$host_ip."' and `checknoda`<= '".$json_arr['time']."' and `balance`>=100 and `view`>0 ORDER BY `balance` DESC LIMIT 32;";
                $result= mysqli_query($mysqli_connect,$query) or die("error_synchnow");
                while($sqltbl_arr= mysqli_fetch_array($result,MYSQLI_ASSOC))if(!in_array($sqltbl_arr['noda'],$nodas_synch) && !in_array($sqltbl_arr['noda'],$nodas_synch_main))$nodas_synch[$sqltbl_arr['noda']]= $sqltbl_arr['noda'];
                if(isset($nodas_synch) && count($nodas_synch)>0){
                  $request['nodawallet']=$nodawallet;
                  $request['nodause']=$nodause;
                  $request['nodaown']=$nodaown;
                  $request['date']=$datecheck;
                  unset($request['password']);
                  if(isset($recipient['wallet']))$request['recipient']= $recipient['wallet'];
									$nodas_synch_random= random($nodas_synch,($nodause==$noda_ip?16:9));
									if(isset($nodas_synch_main) && count($nodas_synch_main)>0)$nodas_synch_all= array_merge($nodas_synch_main,$nodas_synch_random);
									$json= connect_noda_multi($nodas_synch_all,'',$request,3);
                }
              }
            } else $json_arr['transaction']= 'false';
          } else $json_arr['wallet']= 'false';
        } else $json_arr['sign']= 'false';
				delay_now();
				query_bd("DELETE FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` WHERE `wallet` = (SELECT * FROM (SELECT `wallet` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` WHERE `wallet`='".$request['wallet']."' and `height`='".$request['height']."' and `hash`='".$request_sha."' and `recipient`='' LIMIT 1) as t) and `height`='".$request['height']."' and `hash`='".$request_sha."' and `recipient`='';");
      }
    }
    if(mysqli_affected_rows($mysqli_connect)>=1){}
  } 
  function transaction_check($host_ip,$post,$wallet,$request){
    global $json_arr,$sqltbl,$mysqli_connect,$limit_synch;
    $wallet_temp= $wallet;
    $json= connect_noda_multi($host_ip,'',$post,5);
    if(is_array($json)){
      foreach ($json as $key1 => $value1){
        if(is_array($value1)){
          array_splice($value1, $limit_synch);
          foreach ($value1 as $key2 => $value2){
            if(isset($value2['wallet']) && (!isset($transaction) || !in_array($value2,$transaction)))$transaction[]=$value2;
          }
        }
      }
    }
    if(isset($transaction)){
      foreach ($transaction as $key => $value){
        if(($wallet=='' && $request=='') || (isset($value['wallet']) && $wallet['wallet']==$value['wallet'])){
          $send_arr['wallet']=$value['wallet'];
          $send_arr['recipient']=$value['recipient'];
          $send_arr['money']=$value['money'];
					$send_arr['pin']=$value['pin'];
          $send_arr['height']=$value['height'];
          $send_arr['nodawallet']=$value['nodawallet'];
          $send_arr['nodause']=$value['nodause'];
          $send_arr['nodaown']=$value['nodaown'];
          if(isset($value['signpubreg']) && $value['signpubreg'])$send_arr['signpubreg']=$value['signpubreg'];
          if(isset($value['signreg']) && $value['signreg'])$send_arr['signreg']=$value['signreg'];
          if(isset($value['signpubnew']) && $value['signpubnew'])$send_arr['signpubnew']=$value['signpubnew'];
          if(isset($value['signnew']) && $value['signnew'])$send_arr['signnew']=$value['signnew'];
          $send_arr['signpub']=$value['signpub'];
          $send_arr['sign']=$value['sign'];
          $send_arr['date']=$value['date'];
          send($send_arr,0);
          if(isset($json_arr['send']) && isset($wallet['height'])){
						$wallet['height']++;
            $wallet['date']=$value['date'];
            if($send_arr['nodaown']==1)$wallet['noda']=$value['nodause'];
            else $wallet['noda']='';
            $wallet['nodause']=$value['nodause'];
            $wallet['signpubnew']=$value['signpubnew'];
            $wallet['signnew']=$value['signnew'];
            $wallet['signpub']=$value['signpub'];
            $wallet['sign']=$value['sign'];
          }
          unset($send_arr);
          delay_now();
        }
      }  
      $json_arr['transaction_check']=count($transaction);
    } else $json_arr['transaction_check']=0;
    if(isset($json_arr['send']) && isset($wallet['height'])){
      return $wallet;
    } else {
      return $wallet_temp;
    }
    if(mysqli_affected_rows($mysqli_connect)>=1){}
  }
  function history_synch($second){
    global $json_arr,$sqltbl,$mysqli_connect,$noda_ip,$checkbalancenodatime,$noda_balance_noda_ip,$noda_balance_count,$noda_balance,$limit_synch;
    if($noda_balance_count>=1){
      $post_history['type']= 'history';
      $post_history['date']= strtotime(date("Y-m-d H:i:00",$json_arr['time']))-120+$second;
      $post_history['dateto']= $json_arr['time']-5;
      $post_history['order']= 'asc';
      $post_history['all']= 3;
      $post_history['limit']= $limit_synch;
      $query= "SELECT CONCAT('(',`wallet`,',',`height`,',',`hash`,')') as history_exception FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` WHERE `date`>='".$post_history['date']."' ORDER BY `wallet`,`date` LIMIT ".$limit_synch.";";
      $result= mysqli_query($mysqli_connect,$query) or die("error_history_synch");
      while($sqltbl_arr= mysqli_fetch_array($result,MYSQLI_ASSOC))$history[]= $sqltbl_arr['history_exception'];
      if(isset($history))$post_history['history_exception']= json_encode($history);
      $noda_history= random($noda_balance,9);
      transaction_check(array_keys($noda_history),$post_history,'','');
      unset($json_arr['walletnew']);
      unset($json_arr['recipient']);
      unset($json_arr['height']);
      unset($json_arr['transaction']);
      unset($json_arr['wallet']);
      unset($json_arr['balance']);
      unset($json_arr['send']);
    } 
    if(mysqli_affected_rows($mysqli_connect)>=1){}
  }
  function wallet_synch($post_synchwallets){
    global $stop,$json_arr,$sqltbl,$mysqli_connect,$noda_ip,$checkbalancenodatime,$noda_balance_noda_ip,$noda_balance_count,$noda_balance,$limit_synch,$wallet_synch_end,$noda_trust;
    if(!is_array($post_synchwallets))$post_synchwallets= [];
    if(isset($post_synchwallets['synch_wallet']) && $post_synchwallets['synch_wallet']>0)$synch_wallet=$post_synchwallets['synch_wallet'];
    else $synch_wallet=1;
    $noda_balance_rand= random($noda_balance,9);
    if($noda_balance_count>9){
      $query= "SELECT `noda`, SUBSTRING_INDEX(GROUP_CONCAT(`balance` ORDER BY `date` DESC), ',', 1) as balance FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `view`>0 and `noda`!='' and `noda`!='".$noda_ip."' and `balance`>=".(int)$noda_balance_noda_ip."/2 ".($noda_balance_count>0?"and `noda` NOT IN ('".implode("','",array_keys($noda_balance_rand))."')":'')." GROUP BY `noda` LIMIT 16;";
      $result= mysqli_query($mysqli_connect,$query) or die("error_noda_synch_for_last");
      while($sqltbl_arr= mysqli_fetch_array($result,MYSQLI_ASSOC))$noda_balance_big[$sqltbl_arr['noda']]= $sqltbl_arr['balance'];
      if(isset($noda_balance_big)){
        $noda_balance_big= random($noda_balance_big,3);
        foreach ($noda_balance_big as $key => $value)$noda_balance_rand[$key]=$value;
      }
    } 
    if($noda_balance_count<3 && isset($noda_trust) && count($noda_trust)>=1){
      $post_synchwallets['wallets_with_noda_first']= 1;
      foreach($noda_trust as $key => $value)if(!isset($noda_balance_rand[$value]))$noda_balance_rand[$value]= 1;
    }
    if(isset($noda_balance_rand) && count($noda_balance_rand)>=3){
      $nodas= array_keys($noda_balance_rand);
      $nodas_balance_sum=0;
    } else $stop=1;
    if($stop!=1 && isset($nodas) && count($nodas)>=3){
      $limit=$limit_synch;
      $wallets_bd= [];
      if($wallet_synch_end==0){
        $query= "SELECT `wallet`, `checkbalance`, `checkbalanceall` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `view`=3 and `checkwallet`<'".($checkbalancenodatime>0?$checkbalancenodatime:'')."' ORDER BY `checkwallet`,`date` LIMIT ".$limit.";";
        $result= mysqli_query($mysqli_connect,$query) or die("error_wallets_check");
        while($sqltbl_arr= mysqli_fetch_array($result,MYSQLI_ASSOC)){
          $wallets_bd[$sqltbl_arr['wallet']]= $sqltbl_arr;
        }
      }
      if(isset($wallets_bd))$limit= $limit-count($wallets_bd);
      if($limit>0){
        $query= "SELECT `wallet`, `checkbalance`, `checkbalanceall` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `view`!=1 and `view`!=3 and `checkwallet`<'".($checkbalancenodatime>0?$checkbalancenodatime:1)."' ORDER BY `view`=0,`view`=2, `checkwallet` LIMIT ".$limit.";";
        $result= mysqli_query($mysqli_connect,$query) or die("error_wallets_check");
        while($sqltbl_arr= mysqli_fetch_array($result,MYSQLI_ASSOC)){
          $wallets_bd[$sqltbl_arr['wallet']]= $sqltbl_arr;
        }
      }
      $post_synchwallets['type']= 'synchwallets';
      if(isset($wallets_bd)){
        $post_synchwallets['wallets']= json_encode(array_keys($wallets_bd));
        $wallet_synch_end=count($wallets_bd);
      } else $wallet_synch_end=0;
      $noda_json_arr= connect_noda_multi($nodas,'',$post_synchwallets,5);
      if(is_array($noda_json_arr) && count($noda_json_arr)>=3){
        $nodas_balance_sum_no_synch_wallet=0;
        foreach($noda_json_arr as $key => $value){
          if(isset($noda_balance_rand[$key]) && isset($value['noda']) && $value['noda']==$key){
            $nodas_balance_sum+=$noda_balance_rand[$key];
            if(isset($noda_json_arr[$key]['synchwallets'])){
              array_splice($noda_json_arr[$key]['synchwallets'], $limit_synch);
            }
           if(isset($noda_json_arr[$key]['synchwallets']['count_synch_wallet']) && $noda_json_arr[$key]['synchwallets']['count_synch_wallet']==0)$nodas_balance_sum_no_synch_wallet+=$noda_balance_rand[$key];
            unset($noda_json_arr[$key]['synchwallets']['count_synch_wallet']);
          } else unset($noda_json_arr[$key]);
        }
        $noda_json_arr_all_wallets= [];
        foreach($noda_json_arr as $key1 => $value1){
          if(isset($value1['noda']) && preg_replace("/[^0-9a-z.:]/i",'',$value1['noda'])==$value1['noda'] && $value1['noda']==$key1){
						if(isset($value1['time']) && (int)$value1['time']>0){
							$value1['time']= (int)$value1['time'];
							if($json_arr['time']>$value1['time']+5 || $json_arr['time']<$value1['time']-60){
								query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `checknoda`='".$json_arr['time']."'+1*24*60*60 WHERE `noda`= '".$key1."';");
								unset($noda_json_arr[$key1]); 
							} else {
								if(isset($value1['synchwallets'])){
									foreach($value1['synchwallets'] as $key2 => $value2){
										if(isset($value2['wallet']) && (int)$value2['wallet']==$value2['wallet'] && strlen($value2['wallet'])==18 
											 && isset($value2['ref1']) && (int)$value2['ref1']==$value2['ref1'] && strlen($value2['ref1'])<=18
											 && isset($value2['ref2']) && (int)$value2['ref2']==$value2['ref2'] && strlen($value2['ref2'])<=18
											 && isset($value2['ref3']) && (int)$value2['ref3']==$value2['ref3'] && strlen($value2['ref3'])<=18
											 && isset($value2['noda']) && preg_replace("/[^0-9a-z.:]/i",'',$value2['noda'])==$value2['noda'] && strlen($value2['noda'])<=40 
											 && isset($value2['nodause']) && preg_replace("/[^0-9a-z.:]/i",'',$value2['nodause'])==$value2['nodause'] && strlen($value2['nodause'])<=40 
											 && isset($value2['balance']) && preg_replace("/[^0-9]/i",'',$value2['balance'])==$value2['balance']
											 && isset($value2['date']) && preg_replace("/[^0-9]/i",'',$value2['date'])==$value2['date'] && $value2['date']<=$json_arr['time']+2
											 && isset($value2['percent_ref']) && preg_replace("/[^0-9]/i",'',$value2['percent_ref'])==$value2['percent_ref']
											 && isset($value2['date_ref']) && preg_replace("/[^0-9]/i",'',$value2['date_ref'])==$value2['date_ref'] && $value2['date_ref']<=$json_arr['time']+2
											 && isset($value2['height']) && preg_replace("/[^0-9]/i",'',$value2['height'])==$value2['height']
											 && isset($value2['signpubnew']) && preg_replace("/[^0-9]/i",'',$value2['signpubnew'])==$value2['signpubnew'] && strlen($value2['signpubnew'])<=19
											 && isset($value2['signnew']) && preg_replace("/[^0-9a-z:]/i",'',$value2['signnew'])==$value2['signnew'] && strlen($value2['signnew'])<=1440
											 && isset($value2['signpub']) && preg_replace("/[^0-9a-z]/i",'',$value2['signpub'])==$value2['signpub'] && strlen($value2['signpub'])<=514
											 && isset($value2['sign']) && preg_replace("/[^0-9a-z:]/i",'',$value2['sign'])==$value2['sign'] && strlen($value2['sign'])<=1440
												){
											if(!isset($balance_check[$value2['wallet']]) || $balance_check[$value2['wallet']]<=$nodas_balance_sum/2){
												$balance_check[$value2['wallet']]= 0;
												foreach($noda_balance_rand as $key3 => $value3){
													if(isset($noda_json_arr[$key3]['synchwallets']) && in_array($value2,$noda_json_arr[$key3]['synchwallets'])){
														$balance_check[$value2['wallet']]+= $noda_balance_rand[$key3];
													}
												}
											 if($balance_check[$value2['wallet']]<=$nodas_balance_sum/2){
													unset($balance_check[$value2['wallet']]); 
													continue;
												}
												$wallet_add['wallet']=$value2['wallet'];
												$wallet_add['ref1']=$value2['ref1'];
												$wallet_add['ref2']=$value2['ref2'];
												$wallet_add['ref3']=$value2['ref3'];
												$wallet_add['noda']=$value2['noda'];
												$wallet_add['nodause']=$value2['nodause'];
												$wallet_add['balance']=$value2['balance'];
												$wallet_add['date']=$value2['date'];
												$wallet_add['percent_ref']=$value2['percent_ref'];
												$wallet_add['date_ref']=$value2['date_ref'];
												$wallet_add['height']=$value2['height'];
												$wallet_add['signpubnew']=$value2['signpubnew'];
												$wallet_add['signnew']=$value2['signnew'];
												$wallet_add['signpub']=$value2['signpub'];
												$wallet_add['sign']=$value2['sign'];
												if(isset($post_synchwallets['synch_wallet']) && isset($value2['synch_wallet']) && $synch_wallet<$value2['wallet'])$synch_wallet=$value2['wallet'];
												if(!isset($noda_json_arr_all_wallets[$value2['wallet']]) || !in_array($wallet_add,$noda_json_arr_all_wallets[$value2['wallet']])){
													$noda_json_arr_all_wallets[$value2['wallet']][]=$wallet_add;
												}
												unset($wallet_add);
											}
										} else {
											query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `checknoda`='".$json_arr['time']."'+1*24*60*60 WHERE `noda`= '".$key1."';");
											unset($noda_json_arr[$key1]);
											break;
										}
									}
								}
							}
						 }
            } else {
              if(!isset($value1['history']))query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `checknoda`='".$json_arr['time']."'+1*60*60 WHERE `noda`= '".$key1."';");
              unset($noda_json_arr[$key1]);
           }
          }
          if(isset($noda_json_arr) && count(array_keys($noda_json_arr))){
            query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `checknoda`='".$json_arr['time']."' WHERE `noda` IN ('".implode("','",array_keys($noda_json_arr))."');");
         }
          if(!isset($post_synchwallets['wallets_with_noda_first'])){
            query_bd("SELECT IFNULL(SUM(`balance`),0) as balance FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `view`>0 and `noda`!='' and `checknoda`<= '".$json_arr['time']."' and `balance`>=100 LIMIT 1;");
            if(isset($sqltbl['balance']) && $sqltbl['balance']>0)$nodas_balance_sum_bd= $sqltbl['balance'];
            else $nodas_balance_sum_bd=$nodas_balance_sum;
          } else {
            $nodas_balance_sum_bd=$nodas_balance_sum;
          }
          if($wallet_synch_end>0){
            $query= "SELECT * FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `wallet` IN ('".implode("','",array_keys($wallets_bd))."') and `wallet` NOT IN ('".implode("','",array_keys($noda_json_arr_all_wallets))."');";
           $result= mysqli_query($mysqli_connect,$query) or die("error_noda_synch_no_wallet");
            while($sqltbl_arr= mysqli_fetch_array($result,MYSQLI_ASSOC)){
              if(((int)$sqltbl_arr['checkbalanceall']+$nodas_balance_sum)>=0.9*($nodas_balance_sum_bd-($sqltbl_arr['view']==0?$noda_balance_noda_ip:0)) && ((int)$sqltbl_arr['checkbalance']+($sqltbl_arr['view']>1 && (int)$sqltbl_arr['checkbalance']==0 && (int)$sqltbl_arr['checkbalanceall']==0?$noda_balance_noda_ip:0))<=0.5*($nodas_balance_sum_bd-($sqltbl_arr['view']==0?$noda_balance_noda_ip:0))){
                query_bd("DELETE FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `wallet`= '".$sqltbl_arr['wallet']."';");
              } else query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `checkbalanceall`=`checkbalanceall`+'".$nodas_balance_sum."', `checkwallet`='".$checkbalancenodatime."', `view`=IF(`view`=1 or `view`=3,2,`view`) WHERE `wallet`= '".$sqltbl_arr['wallet']."';");
            }
          }
          foreach($noda_json_arr_all_wallets as $key => $value){
            if(count($noda_json_arr_all_wallets[$key])==1)$noda_json_arr_all_wallets[$key]= $noda_json_arr_all_wallets[$key][0];
            else unset($noda_json_arr_all_wallets[$key]);
          }
          if(isset($noda_json_arr_all_wallets) && $noda_json_arr_all_wallets){
						if($wallet_synch_end>0){
							$wallets_check= $wallets_bd;
							foreach ($noda_json_arr_all_wallets as $key => $value) {
								if(!isset($wallets_check[$key]))$wallets_check[$key]= 1;
							}
						} else $wallets_check= $noda_json_arr_all_wallets;
						$query= "SELECT * FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `wallet` IN ('".implode("','",array_keys($wallets_check))."');";
						$result= mysqli_query($mysqli_connect,$query) or die("error_noda_synch_wallet_date_check");
						while($sqltbl_arr= mysqli_fetch_array($result,MYSQLI_ASSOC)){
							if(isset($noda_json_arr_all_wallets[$sqltbl_arr['wallet']])){
								if(isset($noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['wallet'])
									&& $noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['wallet']==$sqltbl_arr['wallet']
									&& $noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['ref1']==$sqltbl_arr['ref1']
									&& $noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['ref2']==$sqltbl_arr['ref2']
									&& $noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['ref3']==$sqltbl_arr['ref3']
									&& $noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['noda']==$sqltbl_arr['noda']
									&& $noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['nodause']==$sqltbl_arr['nodause']
									&& $noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['balance']==$sqltbl_arr['balance']
									&& $noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['date']==$sqltbl_arr['date']
									&& $noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['percent_ref']==$sqltbl_arr['percent_ref']
									&& $noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['date_ref']==$sqltbl_arr['date_ref']
									&& $noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['height']==$sqltbl_arr['height']
									&& $noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['signpubnew']==$sqltbl_arr['signpubnew']
									&& $noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['signnew']==$sqltbl_arr['signnew']
									&& $noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['signpub']==$sqltbl_arr['signpub']
									&& $noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['sign']==$sqltbl_arr['sign']
									){
									if($sqltbl_arr['view']==3 && ($noda_balance_noda_ip< $balance_check[$sqltbl_arr['wallet']] || ($noda_balance_noda_ip+$balance_check[$sqltbl_arr['wallet']])>0.5*$nodas_balance_sum_bd)){
										query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `checkbalance`='', `checkbalanceall`='', `checkwallet`='', `view`=1 WHERE `wallet`= '".$sqltbl_arr['wallet']."' and `view`=3;");
									} else if(((int)$sqltbl_arr['checkbalance']+$balance_check[$sqltbl_arr['wallet']]+($sqltbl_arr['view']>1 && (int)$sqltbl_arr['checkbalance']==0 && (int)$sqltbl_arr['checkbalanceall']==0?$noda_balance_noda_ip:0))>0.5*($nodas_balance_sum_bd-($sqltbl_arr['view']==0?$noda_balance_noda_ip:0))){
										query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `checkbalance`='', `checkbalanceall`='', `checkwallet`='', `view`=1 WHERE `wallet`= '".$sqltbl_arr['wallet']."';");
									} else if(((int)$sqltbl_arr['checkbalanceall']+$nodas_balance_sum)>=0.9*($nodas_balance_sum_bd-($sqltbl_arr['view']==0?$noda_balance_noda_ip:0))){
										if($sqltbl_arr['view']==0){
											query_bd("DELETE FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `wallet`= '".$sqltbl_arr['wallet']."' and `view`=0;");
										} else {
											query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `checkbalance`='', `checkbalanceall`='', `checkwallet`='".$json_arr['time']."', `view`=0, `checknoda`='' WHERE `wallet`= '".$sqltbl_arr['wallet']."' and `view`>1;");
										}
									} else if($sqltbl_arr['view']>1 && ((int)$sqltbl_arr['checkbalance']==0 && (int)$sqltbl_arr['checkbalanceall']==0)){
										query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `checkbalance`=`checkbalance`+'".($balance_check[$sqltbl_arr['wallet']]+$noda_balance_noda_ip)."', `checkbalanceall`=`checkbalanceall`+'".($nodas_balance_sum+$noda_balance_noda_ip)."', `checkwallet`='".$checkbalancenodatime."' WHERE `wallet`= '".$sqltbl_arr['wallet']."' and `view`>1;");
									} else {
										query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `checkbalance`=`checkbalance`+'".$balance_check[$sqltbl_arr['wallet']]."', `checkbalanceall`=`checkbalanceall`+'".$nodas_balance_sum."', `checkwallet`='".$checkbalancenodatime."' WHERE `wallet`= '".$sqltbl_arr['wallet']."' and `view`!=1;");
									}
								} else { 
									if(($sqltbl_arr['view']==1 || $sqltbl_arr['view']==3) && $noda_balance_noda_ip< $balance_check[$sqltbl_arr['wallet']]){
										query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `checkbalance`='', `checkbalanceall`='', `checkwallet`='".$json_arr['time']."', `view`=2, `checknoda`='' WHERE `wallet`= '".$sqltbl_arr['wallet']."' and (`view`=1 or `view`=3);");
										if(mysqli_affected_rows($mysqli_connect)>=1){
											$sqltbl_arr['checkbalance']='';
											$sqltbl_arr['checkbalanceall']='';
											$sqltbl_arr['checkwallet']='';
											$sqltbl_arr['view']=2;
											$sqltbl_arr['checknoda']='';
										}
									}
									if($sqltbl_arr['view']==0 || $sqltbl_arr['view']==2){
										if($balance_check[$sqltbl_arr['wallet']]>0.5*$nodas_balance_sum_bd)$wallet_replace=1;
										else $wallet_replace=0;
										if($wallet_replace==1 || ((int)$sqltbl_arr['checkbalanceall']+$nodas_balance_sum)>=0.9*($nodas_balance_sum_bd-($sqltbl_arr['view']==0?$noda_balance_noda_ip:0))){
										 if($sqltbl_arr['view']==2 || ($wallet_replace==1 && $sqltbl_arr['view']==0)){
												query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `wallet`= '".$noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['wallet']."', `ref1`= '".$noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['ref1']."', `ref2`= '".$noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['ref2']."', `ref3`= '".$noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['ref3']."', `noda`= '".$noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['noda']."', `nodause`= '".$noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['nodause']."', `balance`= '".$noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['balance']."', `date`= '".$noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['date']."', `percent_ref`= '".$noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['percent_ref']."', `date_ref`= '".$noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['date_ref']."', `height`= '".$noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['height']."', `signpubnew`= '".$noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['signpubnew']."', `signnew`= '".$noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['signnew']."', `signpub`= '".$noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['signpub']."', `sign`= '".$noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['sign']."', ".($wallet_replace==1?"`checkbalance`='', `checkbalanceall`='', `checkwallet`='', `view`=1":"`checkbalance`=`checkbalance`+'".$balance_check[$sqltbl_arr['wallet']]."', `checkbalanceall`=`checkbalanceall`+'".$nodas_balance_sum."', `checkwallet`='".$checkbalancenodatime."', `view`=0, `checknoda`=''")." WHERE `wallet`= '".$sqltbl_arr['wallet']."';");
												if(mysqli_affected_rows($mysqli_connect)>=1){
													query_bd("SELECT `recipient`,`height`,`money`,`nodawallet`,`date` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` WHERE `wallet`='".$sqltbl_arr['wallet']."' and `height`='".$noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['height']."' and `checkhistory`=1 and `sign`!= '".$noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['sign']."' LIMIT 1;");
													if(isset($sqltbl['recipient'])){
														$sqltbl_history= $sqltbl;
														query_bd("DELETE FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` WHERE `wallet`= '".$sqltbl_arr['wallet']."' and `height`>= '".$noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['height']."';");
														if(mysqli_affected_rows($mysqli_connect)>=1){
															if($sqltbl_history['nodawallet']!=$noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['nodawallet'] || $sqltbl_history['height']!=$noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['height'])query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `checkwallet`='".$json_arr['time']."', `view`=IF(`view`=1,3,`view`) WHERE `wallet`= '".$sqltbl_history['nodawallet']."' and (`view`=1 or `view`=3);");
															query_bd("SELECT * FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_referrals` WHERE `wallet`='".$sqltbl_arr['wallet']."' and `height`='".$noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['height']."' and `date`='".$sqltbl_history['date']."';");
															if(isset($sqltbl_history['wallet'])){
																if($sqltbl_history['money1']>0)query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET  `checkwallet`='".$json_arr['time']."', `view`=2 WHERE `wallet`= '".$sqltbl_history['ref1']."' and (`view`=1 or `view`=3);");
																if($sqltbl_history['money2']>0)query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET  `checkwallet`='".$json_arr['time']."', `view`=2 WHERE `wallet`= '".$sqltbl_history['ref2']."' and (`view`=1 or `view`=3);");
																if($sqltbl_history['money3']>0)query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET  `checkwallet`='".$json_arr['time']."', `view`=2 WHERE `wallet`= '".$sqltbl_history['ref3']."' and (`view`=1 or `view`=3);");
															}
															query_bd("DELETE FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_referrals` WHERE `wallet`= '".$sqltbl_arr['wallet']."' and `height`>= '".$noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['height']."';");
														}
													} else {
														query_bd("DELETE FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` WHERE `wallet`= '".$sqltbl_arr['wallet']."' and `height`> '".$noda_json_arr_all_wallets[$sqltbl_arr['wallet']]['height']."';");
													}
												}
											} else {
												query_bd("DELETE FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `wallet`= '".$sqltbl_arr['wallet']."' and `view`=0;");
											}
										} else {
											query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `checkbalanceall`=`checkbalanceall`+'".$nodas_balance_sum."', `checkwallet`='".$checkbalancenodatime."' WHERE `wallet`= '".$sqltbl_arr['wallet']."' and `view`!=1;");
										}
									} 
								}
								unset($noda_json_arr_all_wallets[$sqltbl_arr['wallet']]);
							} 
						}
						if(isset($noda_json_arr_all_wallets) && count($noda_json_arr_all_wallets)>0){
							$query= "SELECT * FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `wallet` IN ('".implode("','",array_keys($noda_json_arr_all_wallets))."');";
							$result= mysqli_query($mysqli_connect,$query) or die("error_noda_synch_wallet_date_check_dell");
							while($sqltbl_arr= mysqli_fetch_array($result,MYSQLI_ASSOC))unset($noda_json_arr_all_wallets[$sqltbl_arr['wallet']]);
							if(isset($noda_json_arr_all_wallets) && $noda_json_arr_all_wallets){
								foreach($noda_json_arr_all_wallets as $key => $value){
									if($balance_check[$value['wallet']]>0.5*($nodas_balance_sum_bd-$noda_balance_noda_ip))$wallets_bd_add[$value['wallet']]= "'".implode("','",$value)."','','','','1'";
									else $wallets_bd_add[$value['wallet']]= "'".implode("','",$value)."','".$balance_check[$sqltbl_arr['wallet']]."','".$nodas_balance_sum."','".$checkbalancenodatime."','0'";
								}
								query_bd("INSERT IGNORE INTO `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` (`wallet`, `ref1`, `ref2`, `ref3`, `noda`, `nodause`, `balance`, `date`, `percent_ref`, `date_ref`, `height`, `signpubnew`, `signnew`, `signpub`, `sign`, `checkbalance`, `checkbalanceall`, `checkwallet`, `view`) VALUES (".implode("),(",$wallets_bd_add).");");
								if(mysqli_affected_rows($mysqli_connect)>=1){}
								foreach ($wallets_bd_add as $key => $value) {
									if(isset($noda_json_arr_all_wallets[$key]) && isset($sqltbl_arr) && isset($sqltbl_arr['wallet'])){
										query_bd("SELECT `recipient`,`height`,`money`,`pin`,`nodawallet`,`date` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` WHERE `wallet`='".$sqltbl_arr['wallet']."' and `height`='".$sqltbl_arr['height']."' and `checkhistory`=1 and `sign`!= '".$noda_json_arr_all_wallets[$key]['sign']."' LIMIT 1;");
										if(isset($sqltbl['recipient'])){
											$sqltbl_history= $sqltbl;
											query_bd("DELETE FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` WHERE `wallet`= '".$sqltbl_arr['wallet']."' and `height`>= '".$sqltbl_history['height']."';");
											if(mysqli_affected_rows($mysqli_connect)>=1){
												query_bd("DELETE FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_referrals` WHERE `wallet`= '".$sqltbl_arr['wallet']."' and `height`>= '".$sqltbl_history['height']."';");
											}
										} else {
											query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` SET `checkhistory`=0 WHERE `wallet`= '".$sqltbl_arr['wallet']."' and `height`> '".$sqltbl_arr['height']."' and `checkhistory`=1;");
										}
									}
								}
							}
						}
          }
          if(isset($post_synchwallets['synch_wallet'])){
            if($synch_wallet==$post_synchwallets['synch_wallet'] && $nodas_balance_sum_no_synch_wallet>0 && $nodas_balance_sum>0 && $nodas_balance_sum_no_synch_wallet> 0.9*$nodas_balance_sum)query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_settings` SET `value`= 1 WHERE `name`= 'synch_wallet';");
            else if($synch_wallet>0) query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_settings` SET `value`= '".$synch_wallet."' WHERE `name`= 'synch_wallet';");
         }
      } 
    }
    if(mysqli_affected_rows($mysqli_connect)>=1){}
  }
}
if(isset($_REQUEST['type']) && $_REQUEST['type']=="walletscount"){
	query_bd("SELECT count(*) as walletscount FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` LIMIT 1;");
	if(isset($sqltbl['walletscount'])){
		echo '{"walletscount": "'.$sqltbl['walletscount'].'"}';
		file_put_contents($GLOBALS['dir_temp'].'/walletscount_'.$sqltbl['walletscount'], "");
	} else echo '{"walletscount": "0"}';
	exit_now();
}else
if($stop!=1 && $request['type']=="nodas"){
  $where= "";
  if(isset($request['balancestart']))$where.= " and n1.`balance`>= '".$request['balancestart']."'";
  if(isset($request['balancefinish']))$where.= " and n1.`balance`<= '".$request['balancefinish']."'";
  if(isset($request['nodausewalletstart']))$where.= " and n2.`walletsuse`>= '".$request['nodausewalletstart']."'";
  if(isset($request['nodausewalletfinish']))$where.= " and n2.`walletsuse`<= '".$request['nodausewalletfinish']."'";
	if(isset($request['order']) && $request['order']=='asc')$order= "n1.`date` ASC";else if(isset($request['order']) && $request['order']=='balance')$order= "n1.`balance` DESC";else $order= "n1.`date` DESC";
	if(isset($request['start']) && $request['start']>0)$start= $request['start'];else $start=0;
  if(isset($request['limit']) && $request['limit']>0 && $request['limit']<100)$limit= $request['limit'];else $limit=100;
	$query= "SELECT n1.`noda` as noda, n1.`wallet` as wallet, n1.`balance` as balance, n2.walletsuse as walletsuse, n2.datelastuse as datelastuse FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` as n1 LEFT JOIN (SELECT `nodause`, COUNT(`nodause`) as walletsuse, MAX(`date`) as datelastuse FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` GROUP BY `nodause`) as n2 ON n2.`nodause`=n1.`noda` WHERE n1.`view`>0 and n1.`noda`!='' ".$where." ORDER BY ".$order." LIMIT ".$start.",".$limit.";";
  $result= mysqli_query($mysqli_connect,$query) or die("error_nodas");
  while($sqltbl_arr= mysqli_fetch_array($result,MYSQLI_ASSOC))if($sqltbl_arr['noda'])$json_arr['nodas'][]= $sqltbl_arr;
	if(isset($json_arr['nodas']) && is_array($json_arr['nodas']))file_put_contents($GLOBALS['file_nodas'], json_encode($json_arr['nodas']));
  $stop=1;
} else
if($stop!=1 && $request['type']=="wallet"){
  if(isset($request['wallet']))$wallet= wallet($request['wallet'],$json_arr['time'],0);
  if(isset($wallet['wallet'])){
      $json_arr['wallet']= gold_wallet_view($wallet['wallet']);
      if(isset($wallet['ref1']) && $wallet['ref1']>0)$json_arr['ref1']= gold_wallet_view($wallet['ref1']);else $json_arr['ref1']= '0';
      if(isset($wallet['ref2']) && $wallet['ref2']>0)$json_arr['ref2']= gold_wallet_view($wallet['ref2']);else $json_arr['ref2']= '0';
      if(isset($wallet['ref3']) && $wallet['ref3']>0)$json_arr['ref3']= gold_wallet_view($wallet['ref3']);else $json_arr['ref3']= '0';
      $json_arr['nodawallet']=$wallet['noda'];
      $json_arr['nodawalletuse']=$wallet['nodause'];
      $json_arr['balance']= $wallet['balance'];
      if($wallet['balancecheck']!=0)$json_arr['balancetransactioncheck']= (string)$wallet['balancecheck'];
      $json_arr['percent_4']= (string)$wallet['percent_4'];
      $json_arr['percent_5']= (string)$wallet['percent_5'];
			$json_arr['percent_ref']= $wallet['percent_ref'];
			$json_arr['date_ref']= $wallet['date_ref'];
      $json_arr['height']= $wallet['height'];
      $json_arr['date']= $wallet['date'];
      if(isset($request['password'])){
        query_bd("SELECT `email`,`up`,`down`,`date`,`password` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_users` WHERE `wallet`= '".$request['wallet']."' and `nodatrue`=1 LIMIT 1;");
        if(isset($sqltbl['password']) && gen_sha3($sqltbl['password'],256)==$request['password']){
					if($sqltbl['email'])$json_arr['usersemail']= 'true';
					else $json_arr['usersemail']= 'false';
          $json_arr['usersemailup']= $sqltbl['up'];
          $json_arr['usersemaildown']= $sqltbl['down'];
          $json_arr['usersemaildateupdate']= $sqltbl['date'];
        }
      }
      $json_arr['signpubnew']= $wallet['signpubnew'];
      $json_arr['signnew']= $wallet['signnew'];
      $json_arr['signpub']= $wallet['signpub'];
      $json_arr['sign']= $wallet['sign'];
  } else $json_arr['wallet']= 'false';
  $stop=1;
} else
if($stop!=1 && $request['type']=="history"){
	if((!isset($request['history']) && !isset($request['wallet']) && !isset($request['recipient']) ) || (isset($request['history']) && strlen($request['history'])==18) || (isset($request['wallet']) && strlen($request['wallet'])==18) || (isset($request['recipient']) && strlen($request['recipient'])==18)){
		if(isset($request['all'])){if($request['all']==3) $where= "`checkhistory`<=2"; else if($request['all']==2) $where= "`checkhistory`<= 1"; else if($request['all']==1) $where= "`checkhistory`= 0"; else $where= "`checkhistory`= 1";}else $where= "`checkhistory`= 1";
		if(isset($request['pin']) && $request['pin']!='0') $where.= " and `pin`= '".$request['pin']."'";
		if(isset($request['date'])) $where.= " and `date`>= '".$request['date']."'";
		if(isset($request['dateto'])) $where.= " and `date`< '".$request['dateto']."'";
		if(isset($request['height']) && (isset($request['wallet']) or isset($request['recipient']))) $where.= " and `height`>= '".$request['height']."'";
		if(isset($request['nodause'])) $where.= " and `nodause`= '".$request['nodause']."'";
		if(isset($request['order']) && $request['order']=='asc') $order= "ASC";else $order= "DESC";
		if(isset($_POST['history_exception'])){
			$_POST['history_exception']= json_decode($_POST['history_exception'],true);
			if(is_array($_POST['history_exception']) && count($_POST['history_exception'])>0){
				array_splice($_POST['history_exception'], $limit_synch);
				foreach($_POST['history_exception'] as $key => $value)if(preg_replace("/[^0-9a-z\,\(\)]/",'',$value)==$value && (!isset($history_exception) || !in_array($value,$history_exception)))$history_exception[]= preg_replace('/\((.+?)\,(.+?)\,(.+?)\)/i', "('$1','$2','$3')",$value);
				if(isset($history_exception))$where.= " and (`wallet`,`height`,`hash`) NOT IN (".implode(",",($history_exception)).")";
			}
		} 
		if(isset($request['start']) && $request['start']>0) $start= $request['start'];else $start=0;
		if(isset($request['limit']) && $request['limit']>0 && $request['limit']<=$limit_synch) $limit= $request['limit'];else $limit=(isset($request['history'])?25:$limit_synch);
		if(isset($request['wallet']) && strlen($request['wallet'])==18 && isset($request['recipient']) && $request['wallet']==$request['recipient'])$request['history']=$request['wallet'];
		if(isset($request['history']) && strlen($request['history'])==18){
		if((!isset($request['all']) || $request['all']<3)){
			query_bd("SELECT count(*) as count FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` WHERE ".$where." and (`wallet`= '".$request['history']."' or `recipient`= '".$request['history']."') ".(isset($request['limit']) && $request['limit']==1?"and `date`< UNIX_TIMESTAMP()-3":'')." LIMIT 1;");
			if(isset($sqltbl['count']) && $sqltbl['count']>0)$history_count= (int)$sqltbl['count'];
		}
		if(isset($history_count) || (isset($request['all']) && $request['all']==3)){
				$query= "SELECT `wallet`, `recipient`, `money`, `pin`, `height`, `nodawallet`, `nodause`, `nodaown`, `date`, `signpubreg`, `signreg`, `signpubnew`, `signnew`, `signpub`, `sign`, `checkhistory` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` WHERE ".$where." and (`wallet`= '".$request['history']."' or `recipient`= '".$request['history']."') ".(isset($request['limit']) && $request['limit']==1?"and `date`< UNIX_TIMESTAMP()-3":'')." ORDER BY `date` ".$order." LIMIT ".$start.",".$limit.";";
				$result= mysqli_query($mysqli_connect,$query) or die("error_history");
				while($sqltbl_arr= mysqli_fetch_array($result,MYSQLI_ASSOC)){
					if($sqltbl_arr['wallet'])$json_arr['history'][$sqltbl_arr['date']]= $sqltbl_arr;
				}
			}
		} else {
			if(isset($request['wallet']) && strlen($request['wallet'])==18) $where.= " and `wallet`= '".$request['wallet']."'";
			if(isset($request['recipient']) && strlen($request['recipient'])==18) $where.= " and `recipient`= '".$request['recipient']."'";
			$query= "SELECT `wallet`, `recipient`, `money`, `pin`, `height`, `nodawallet`, `nodause`, `nodaown`, `date`, `signpubreg`, `signreg`, `signpubnew`, `signnew`, `signpub`, `sign`, `checkhistory` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` WHERE ".$where." ORDER BY `date` ".$order." LIMIT ".$start.",".$limit.";";
			$result= mysqli_query($mysqli_connect,$query) or die("error_history");
			while($sqltbl_arr= mysqli_fetch_array($result,MYSQLI_ASSOC)){
				if($sqltbl_arr['wallet'])$json_arr['history'][$sqltbl_arr['date']]= $sqltbl_arr;
			}
		}
		if(isset($request['history']) && isset($json_arr['history'])){
			if(isset($request['order']) && $request['order']=='asc')ksort($json_arr['history']);
			else krsort($json_arr['history']);
			array_splice($json_arr['history'], $limit);
		}
	}
  if(!isset($json_arr['history']) || !$json_arr['history']){
	  $json_arr['history']['history']='not_found_history_this_noda';
	  if(isset($history_count))$json_arr['history']['count']= $history_count;
  } else {
	  if(isset($history_count))$json_arr['history']['count']= $history_count;
	  $json_arr['history'] = array_values($json_arr['history']);
  }
  $stop=1;
} else
if($stop!=1 && $request['type']=="referrals"){
	if((!isset($request['ref']) && !isset($request['wallet']) && !isset($request['ref1'])  && !isset($request['ref2']) && !isset($request['ref3'])) || (isset($request['ref']) && strlen($request['ref'])==18) || (isset($request['wallet']) && strlen($request['wallet'])==18) || (isset($request['ref1']) && strlen($request['ref1'])==18) || (isset($request['ref2']) && strlen($request['ref2'])==18)|| (isset($request['ref3']) && strlen($request['ref3'])==18)){
		$where= "`wallet`!=''";
		if(isset($request['wallet']) && strlen($request['wallet'])==18) $where.= " and `wallet`= '".$request['wallet']."'";
		if(isset($request['ref']) && strlen($request['ref'])==18) $where.= " and ((`ref1`= '".$request['ref']."' and `money1`>0) or (`ref2`= '".$request['ref']."' and `money2`>0) or (`ref3`= '".$request['ref']."' and `money3`>0))";
		else {
			if(isset($request['ref1']) && strlen($request['ref1'])==18) $where.= " and (`ref1`= '".$request['ref1']."' and `money1`>0)";
			if(isset($request['ref2']) && strlen($request['ref2'])==18) $where.= " and (`ref2`= '".$request['ref2']."' and `money2`>0)";
			if(isset($request['ref3']) && strlen($request['ref2'])==18) $where.= " and (`ref2`= '".$request['ref3']."' and `money3`>0)";
		}
		if(isset($request['height'])) $where.= " and `height`>= '".$request['height']."'";
		if(isset($request['date'])) $where.= " and `date`>= '".$request['date']."'";
		if(isset($request['dateto'])) $where.= " and `date`< '".$request['dateto']."'";
		if(isset($request['order']) && $request['order']=='asc') $order= "ASC";else $order= "DESC";
		if(isset($request['start']) && $request['start']>0) $start= $request['start'];else $start=0;
		if(isset($request['limit']) && $request['limit']>0 && $request['limit']<100) $limit= $request['limit'];else $limit=100;
		query_bd("SELECT count(*) as count FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_referrals` WHERE ".$where." LIMIT 1;");
		if(isset($sqltbl['count']) && $sqltbl['count']>0){
			$referrals_count= (int)$sqltbl['count'];
			$query= "SELECT * FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_referrals` WHERE ".$where." ORDER BY `date` ".$order." LIMIT ".$start.",".$limit.";";
			$result= mysqli_query($mysqli_connect,$query) or die("error_referrals_transaction");
			while($sqltbl_arr= mysqli_fetch_array($result,MYSQLI_ASSOC)){
			if($sqltbl_arr['wallet'])$json_arr['referrals'][]= $sqltbl_arr;
			}
		}
	}
  if(!isset($json_arr['referrals']) || !$json_arr['referrals'] || !isset($referrals_count)){
	  $json_arr['referrals']['referrals']='not_found_referrals_this_noda';
	  if(isset($referrals_count))$json_arr['referrals']['count']= $referrals_count;
  } else {
	  if(isset($referrals_count))$json_arr['referrals']['count']= $referrals_count;
	  $json_arr['referrals'] = array_values($json_arr['referrals']);
  }
  $stop=1;
} else
if($stop!=1 && $request['type']=="referralwallets"){
	if((!isset($request['ref']) && !isset($request['wallet']) && !isset($request['ref1'])  && !isset($request['ref2']) && !isset($request['ref3'])) || (isset($request['ref']) && strlen($request['ref'])==18) || (isset($request['wallet']) && strlen($request['wallet'])==18) || (isset($request['ref1']) && strlen($request['ref1'])==18) || (isset($request['ref2']) && strlen($request['ref2'])==18)|| (isset($request['ref3']) && strlen($request['ref3'])==18)){
		$where= "`wallet`!=''";
		if(isset($request['wallet']) && strlen($request['wallet'])==18) $where.= " and `wallet`= '".$request['wallet']."'";
		if(isset($request['ref']) && strlen($request['ref'])==18) $where.= " and (`ref1`= '".$request['ref']."' or `ref2`= '".$request['ref']."' or `ref3`= '".$request['ref']."')";
		else {
			if(isset($request['ref1']) && strlen($request['ref1'])==18) $where.= " and `ref1`= '".$request['ref1']."'";
			if(isset($request['ref2']) && strlen($request['ref2'])==18) $where.= " and `ref2`= '".$request['ref2']."'";
			if(isset($request['ref3']) && strlen($request['ref3'])==18) $where.= " and `ref3`= '".$request['ref3']."'";
		}
		if(isset($request['height'])) $where.= " and `height`>= '".$request['height']."'";
		if(isset($request['date'])) $where.= " and `date`>= '".$request['date']."'";
		if(isset($request['dateto'])) $where.= " and `date`< '".$request['dateto']."'";
		if(isset($request['nodause'])) $where.= " and `nodause`= '".$request['nodause']."'";
		if(isset($request['order'])){
			if($request['order']=='asc')$order= "`date` ASC";
			else if($request['order']=='balanceasc')$order= "`balance` ASC, `date` DESC";
			else if($request['order']=='balancedesc')$order= "`balance` DESC, `date` DESC";
			else $order= "`date` DESC";
		} else $order= "`date` DESC";
		if(isset($request['start']) && $request['start']>0) $start= $request['start'];else $start=0;
		if(isset($request['limit']) && $request['limit']>0 && $request['limit']<100) $limit= $request['limit'];else $limit=100;
		query_bd("SELECT count(*) as count FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE ".$where." LIMIT 1;");
		if(isset($sqltbl['count']) && $sqltbl['count']>0){
			$referrals_count= (int)$sqltbl['count'];
			$query= "SELECT `wallet`, `ref1`, `ref2`, `ref3`, `noda`, `nodause`, `balance`, `date`, `height` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE  `view`>0 and ".$where." ORDER BY ".$order." LIMIT ".$start.",".$limit.";";
			$result= mysqli_query($mysqli_connect,$query) or die("error_wallets");
			while($sqltbl_arr= mysqli_fetch_array($result,MYSQLI_ASSOC)){
				if($sqltbl_arr['wallet'])$json_arr['referralwallets'][]= $sqltbl_arr;
			}
		}
	}
  if(!isset($json_arr['referralwallets']) || !$json_arr['referralwallets'] || !isset($referrals_count)){
		$json_arr['referralwallets']['referralwallets']='not_found_referral_wallets_this_noda';
	  if(isset($referrals_count))$json_arr['referralwallets']['count']= $referrals_count;
  } else {
	  if(isset($referrals_count))$json_arr['referralwallets']['count']= $referrals_count;
	  $json_arr['referralwallets'] = array_values($json_arr['referralwallets']);
  }
  $stop=1;
} else
if($stop!=1 && $request['type']=="referralresults"){
	if((isset($request['ref']) && strlen($request['ref'])==18) || (isset($request['ref1']) && strlen($request['ref1'])==18) || (isset($request['ref2']) && strlen($request['ref2'])==18)|| (isset($request['ref3']) && strlen($request['ref3'])==18)){
		$where= "`wallet`!=''";
		if(isset($request['ref']) && strlen($request['ref'])==18){
			$request['ref1']=$request['ref'];
			$request['ref2']=$request['ref'];
			$request['ref3']=$request['ref'];
		}
		if(isset($request['ref1']) && strlen($request['ref1'])==18){
			query_bd("SELECT count(*) as count,sum(`balance`) as balance FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `ref1`= '".$request['ref1']."' LIMIT 1;");
			if($sqltbl['count']>0){
				$json_arr['referralresults']['count1']= $sqltbl['count'];
				$json_arr['referralresults']['balance1']= $sqltbl['balance'];
			}
		}
		if(isset($request['ref2']) && strlen($request['ref2'])==18){
			query_bd("SELECT count(*) as count,sum(`balance`) as balance FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `ref2`= '".$request['ref2']."' LIMIT 1;");
			if($sqltbl['count']>0){
				$json_arr['referralresults']['count2']= $sqltbl['count'];
				$json_arr['referralresults']['balance2']= $sqltbl['balance'];
			}
		}
		if(isset($request['ref3']) && strlen($request['ref3'])==18){
			query_bd("SELECT count(*) as count,sum(`balance`) as balance FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `ref3`= '".$request['ref3']."' LIMIT 1;");
			if($sqltbl['count']>0){
				$json_arr['referralresults']['count3']= $sqltbl['count'];
				$json_arr['referralresults']['balance3']= $sqltbl['balance'];
			}
		}
	}
  if(!isset($json_arr['referralresults']) || !$json_arr['referralresults'])$json_arr['referralresults']['referralresults']='not_found_wallets_this_noda';
  $stop=1;
} else
if($stop!=1 && ($request['type']=="synch" || $request['type']=="send")){
  ignore_user_abort(1);
  set_time_limit(55);
  $skip=0;
	delay_now();
  if($request['type']=="synch" || (!file_exists($GLOBALS['filename_tmp_synch']) && (int)date("s",$json_arr['timer_start'])>5)){
    $query= "SELECT * FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_settings` WHERE `name` IN ('synch_now','synch_wallet');";
    $result= mysqli_query($mysqli_connect,$query) or die("error_noda_synch_settings");
    while($sqltbl_arr= mysqli_fetch_array($result,MYSQLI_ASSOC)){
      if($sqltbl_arr['name']=='synch_now' && date("Y-m-d H:i",$sqltbl_arr['value'])==date("Y-m-d H:i",$json_arr['timer_start'])){
        if($request['type']!="send"){echo '{"synch":"now"}';exit_now();}
        else $skip=1;
      } else if($sqltbl_arr['name']=='synch_wallet')$post_synchwallets['synch_wallet']= $sqltbl_arr['value'];
    }
  } else $skip=1;
	if($skip!=1){
		query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_settings` SET `value`= '".$json_arr['time']."' WHERE `name`= 'synch_now';");
		foreach(glob($GLOBALS['dir_temp']."/*") as $file){if(file_exists($file)){
				if(strpos($file, '/ip_') !== FALSE)@unlink($file);
				else if(time()-@filectime($file)>50 && $file!=$dir_temp_index)@unlink($file);
				else if(strpos($file, '/ddos_') !== FALSE && strpos($file, '/ddos_10000') === FALSE) if(@rename($file, $GLOBALS['dir_temp']."/ddos_0")!== true)@unlink($file);
		}}
		if(!file_exists($GLOBALS['filename_tmp_synch']))file_put_contents($GLOBALS['filename_tmp_synch'], "");
		query_bd("SELECT SUM(`balance`) as balanceall FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` LIMIT 1;");
		if(isset($sqltbl['balanceall']))file_put_contents($GLOBALS['dir_temp'].'/balanceall_'.$sqltbl['balanceall'], "");
		$checkbalancenodatime=0;
		$noda_balance_noda_ip=0;
		$query= "SELECT `noda`, SUBSTRING_INDEX(GROUP_CONCAT(`balance` ORDER BY `date` DESC), ',', 1) as balance, MAX(`checknoda`) as checknoda FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `view`>0 and `noda`!='' and `checknoda`<= '".$json_arr['time']."' and `balance`>=100 GROUP BY `noda` ORDER BY `noda`='".$noda_ip."' DESC,`checknoda` LIMIT 33;";
		$result= mysqli_query($mysqli_connect,$query) or die("error_noda_synch_for_last");
		while($sqltbl_arr= mysqli_fetch_array($result,MYSQLI_ASSOC)){
			if($sqltbl_arr['noda']!=$noda_ip){
				$noda_balance[$sqltbl_arr['noda']]= $sqltbl_arr['balance'];
				if(!isset($checkbalancenodatime) || $checkbalancenodatime< $sqltbl_arr['checknoda'])$checkbalancenodatime= $sqltbl_arr['checknoda'];
			} else $noda_balance_noda_ip= (int)$sqltbl_arr['balance'];
		}
		if(isset($noda_balance) && $noda_balance)$noda_balance_count= count($noda_balance);
		else $noda_balance_count=0;
		delay_now();
		history_synch(60);
		delay_now();
		wallet_check();
		timer(9);
		$wallet_synch_end=0;
		wallet_synch((isset($post_synchwallets)?$post_synchwallets:''));
		if($wallet_synch_end>0){
			delay_now();
			wallet_synch('');
		}
		timer(3);
		for($i=0,$j=1;$i<=60 && $j>=1 && (int)date("s")<50;$i=$i+10){
			history_synch(2*$i);
			if(isset($json_arr['transaction_check']) && $json_arr['transaction_check']>900){
				$j= $json_arr['transaction_check'];
				usleep(mt_rand(0.5*1000000,1*1000000));
			} else $j=0;
		}		
		if(isset($email_domain) && function_exists('mail') && $email_domain && isset($email_limit) && (int)$email_limit>0 && isset($email_delay) && (float)$email_delay>0){
			$query= "SELECT * FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_users` WHERE `nodatrue`=1 and `email`!='' ORDER BY `date` ASC;";
			$result= mysqli_query($mysqli_connect,$query) or die("error_noty_check_user");
			while($sqltbl_arr= mysqli_fetch_array($result,MYSQLI_ASSOC)){
				if(!isset($date_limit) || $date_limit<$sqltbl_arr['date'])$date_limit= $sqltbl_arr['date'];
				$user_mail[$sqltbl_arr['wallet']]=$sqltbl_arr;
			}
			if(isset($user_mail) && isset($date_limit)){
				$query= "SELECT `wallet`,`height`,`recipient`,`money`,`nodause`,`date`,`checkemail` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` WHERE `date`>".$json_arr['time']."-7*60 and `checkhistory`=1 and `checkemail`!=3 ORDER BY `date` ASC, `wallet` ASC, `height` ASC LIMIT 10000;";
				$result= mysqli_query($mysqli_connect,$query) or die("error_noty_check");
				while($sqltbl_arr= mysqli_fetch_array($result,MYSQLI_ASSOC))$user_mail_wallet[$sqltbl_arr['wallet']."_".$sqltbl_arr['height']]= $sqltbl_arr;
				if(isset($user_mail_wallet) && $user_mail_wallet && is_array($user_mail_wallet)){
					function send_mail($TO_EMAIL,$subject,$message){
						global $email_domain,$email_delay;
						usleep((float)$email_delay*1000000);
						$fromUserName = "eGOLD";
						$fromUserEmail= "egold@".$email_domain;
						$ReplyToEmail = $fromUserEmail;
						$subject = "=?utf-8?b?" . base64_encode($subject) . "?=";
						$from = "=?utf-8?B?" . base64_encode($fromUserName) . "?= <" . $fromUserEmail . ">";
						$headers = "From: " . $from . "\r\nReply-To: " . $ReplyToEmail . "\"";
						$headers .= "\r\nContent-Type: text/html; charset=\"utf-8\"";
						if(@mail($TO_EMAIL, $subject, $message, $headers)) return 1;
						else return 0;
					}
					$email_limit_up= (int)$email_limit;
					$email_limit_down= (int)$email_limit;
					if(isset($user_mail_wallet) && $user_mail_wallet && is_array($user_mail_wallet)){
						foreach ($user_mail_wallet as $key => $value) {
							if(!($email_limit_up>0) && !($email_limit_down>0))break;
							if($email_limit_up>0 && isset($user_mail[$value['wallet']]['email']) && $value['checkemail']!=3 && $value['checkemail']!=1){
								if($user_mail[$value['wallet']]['up']>0 && $user_mail[$value['wallet']]['up']<=$value['money']){
									$money_value= number_format($value['money'], 0, '.', ' ');
									$subject= "-".$money_value." | ".($value['recipient']==1?'eGOLD':gold_wallet_view($value['recipient']))." < ".substr(gold_wallet_view($value['wallet']),0,6);
									$message= "<b>-".$money_value." | <a href='http://".$noda_ip."/egold.php?type=history&history=".$value['recipient']."' target='_blank'>".($value['recipient']==1?'eGOLD':gold_wallet_view($value['recipient']))."</a> < <a href='http://".$noda_ip."/egold.php?type=history&history=".$value['wallet']."' target='_blank'>".gold_wallet_view($value['wallet'])."</a> | ".date("Y-m-d H:i:s",$value['date'])."</b>";
									if(send_mail($user_mail[$value['wallet']]['email'],$subject,$message)==1)$email_limit_up--;
								}
								$value['checkemail']= ($value['checkemail']==2?3:1);
								query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` SET `checkemail`=".$value['checkemail']." WHERE `wallet`= '".$value['wallet']."' and `height`= '".$value['height']."' and `checkhistory`=1;");
							}
							if($email_limit_down>0 && isset($user_mail[$value['recipient']]['email']) && $value['checkemail']!=3 && $value['checkemail']!=2){
								if($user_mail[$value['recipient']]['down']>0 && $user_mail[$value['recipient']]['down']<=$value['money']){
									$money_value= number_format($value['money'], 0, '.', ' ');
									$subject= "+".$money_value." | ".gold_wallet_view($value['wallet'])." > ".substr(gold_wallet_view($value['recipient']),0,6);
									$message= "<b>+".$money_value." | <a href='http://".$noda_ip."/egold.php?type=history&history=".$value['wallet']."' target='_blank'>".gold_wallet_view($value['wallet'])."</a> > <a href='http://".$noda_ip."/egold.php?type=history&history=".$value['recipient']."' target='_blank'>".gold_wallet_view($value['recipient'])."</a> | ".date("Y-m-d H:i:s",$value['date'])."</b>";
									if(send_mail($user_mail[$value['recipient']]['email'],$subject,$message)==1)$email_limit_down--;
								}
								$value['checkemail']= ($value['checkemail']==1?3:2);
								query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` SET `checkemail`=".$value['checkemail']." WHERE `wallet`= '".$value['wallet']."' and `height`= '".$value['height']."' and `checkhistory`=1;");
							}
						}
					}
				}
			}
		}
		if((int)date("i",$json_arr['timer_start'])==55 && (int)date("H",$json_arr['timer_start'])==5){
			$query= "SELECT `wallet` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE (`view`!=1 and `checkwallet`< UNIX_TIMESTAMP()-16*24*60*60 and `checkwallet`>0) or (`date`< UNIX_TIMESTAMP()-90*24*60*60 and (`balance`<10 or `date`< UNIX_TIMESTAMP()-10*365*24*60*60));";
			$result= mysqli_query($mysqli_connect,$query) or die("error_wallets_dell");
			while($sqltbl_arr= mysqli_fetch_array($result,MYSQLI_ASSOC)){
				query_bd("DELETE FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `wallet`= '".$sqltbl_arr['wallet']."';");
				query_bd("DELETE FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` WHERE `wallet`= '".$sqltbl_arr['wallet']."';");
				query_bd("DELETE FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_referrals` WHERE `wallet`= '".$sqltbl_arr['wallet']."';");
			}
			query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `noda`='', `view`=IF(`view`=1,3,`view`) WHERE `noda`!='' and `noda` NOT IN (SELECT * FROM (SELECT `nodause` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `nodause`!='' and `date` > UNIX_TIMESTAMP()-30*24*60*60 GROUP BY `nodause`) as t);");
			query_bd("DELETE FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_referrals` WHERE `date`< UNIX_TIMESTAMP()-".$history_day."*24*60*60;");
			query_bd("DELETE FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` WHERE `date`< UNIX_TIMESTAMP()-".$history_day."*24*60*60;");
			query_bd("DELETE FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_history` WHERE `checkhistory`!=1 and `date`< UNIX_TIMESTAMP()-1*60*60;");
			query_bd("DELETE FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_users` WHERE `date`< UNIX_TIMESTAMP()-365*24*60*60;");
			if(mysqli_affected_rows($mysqli_connect)>=1){
				query_bd("DELETE FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_contacts` WHERE `wallet` NOT IN (SELECT DISTINCT(`wallet`) FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_users`);");
			}
		} else if((int)date("i",$json_arr['timer_start'])==51 && (int)date("H",$json_arr['timer_start'])==5){
			$tables = mysqli_query($mysqli_connect,'SHOW TABLES');
			while ($table = mysqli_fetch_array ($tables))query_bd('OPTIMIZE TABLE ' . $table[0]);
		}
		query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` SET `checknoda`='' WHERE `view`>0 and `noda`='' and `checknoda`!='';");
	}  
  ignore_user_abort(0);
} 
if($stop!=1 && $request['type']=="send" && isset($request['wallet']) && isset($request['recipient']) && isset($request['money']) && isset($request['pin']) && isset($request['height']) && isset($request['signpub']) && isset($request['sign'])){
  ignore_user_abort(1);
  set_time_limit(15);
  if($request['wallet']==$request['recipient'])$json_arr['recipient']= 'false';
  else {
    $wallet= wallet($request['wallet'],(isset($request['date'])?$request['date']:$json_arr['time']),0);
    if(!isset($wallet['wallet']))$json_arr['wallet']= 'false';
    else if($wallet['height']>=$request['height'])$json_arr['height']= 'false';
    else if($json_arr['send_noda']!=1 && $wallet['balance']+$wallet['percent_4']< $request['money']+2)$json_arr['balance']= $wallet['balance']+$wallet['percent_4'];
    else if($wallet['view']==0 || ($wallet['view']==2 && $json_arr['send_noda']!=1))$json_arr['wallet']= 'synch';
    else{
			if($request['wallet']!=$noda_wallet){
				query_bd("SELECT `wallet` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `noda`= '".$noda_ip."' ORDER BY `date` DESC LIMIT 1;");
				if(!isset($sqltbl['wallet'])){echo '{"noda": "not_activated"}';exit_now();}
			}
      send($request,1);
      if(isset($json_arr['send']) && $json_arr['send']== 'true'){
        if(isset($request['password']) && $request['nodause']==$noda_ip){
          if(strlen($request['password'])==128){
            query_bd("SELECT `wallet`,`password` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_users` WHERE `wallet`= '".$wallet['wallet']."';");
            if(isset($sqltbl['wallet'])){
							if($sqltbl['password']!=$request['password']){
								query_bd("DELETE FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_contacts` WHERE `wallet`= '".$wallet['wallet']."';");
								query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_users` SET `password`= '".$request['password']."', `nodatrue`=1, `date`= '".$json_arr['time']."' WHERE `wallet`= '".$wallet['wallet']."';");
							} else $json_arr['password']= 'true';
						} else query_bd("INSERT IGNORE INTO `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_users` SET `wallet`= '".$wallet['wallet']."', `password`= '".$request['password']."', `nodatrue`=1, `date`= '".$json_arr['time']."';");
            if(mysqli_affected_rows($mysqli_connect)>=1)$json_arr['password']= 'true';
						else $json_arr['password']= 'false';
          } else $json_arr['password']= 'false';
        }
      }
    }
  }
  ignore_user_abort(0);
  $stop=1;
} else 
if($stop!=1 && $request['type']=="synchwallets"){
  $limit=$limit_synch;
  if(isset($_POST['wallets'])){
    $wallets_temp= json_decode($_POST['wallets'],true);
    if(is_array($wallets_temp) && count($wallets_temp)>0){
      array_splice($wallets_temp, $limit);
      foreach($wallets_temp as $key => $value)if((int)$value==$value)$wallets[]=$value;
      if(isset($wallets)){
        $query= "SELECT `wallet`, `ref1`, `ref2`, `ref3`, `noda`, `nodause`, `balance`, `date`, `percent_ref`, `date_ref`, `height`, `signpubnew`, `signnew`, `signpub`, `sign` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `wallet` IN ('".implode("','",$wallets)."') and `view`>0 ORDER BY `date`,`wallet` LIMIT ".$limit.";";
        $result= mysqli_query($mysqli_connect,$query) or die("error_synchwallets_wallets_check");
        while($sqltbl_arr= mysqli_fetch_array($result,MYSQLI_ASSOC)){
          $json_arr['synchwallets'][$sqltbl_arr['wallet']]= $sqltbl_arr;
        }
      } 
    }
  } 
  if(isset($request['synch_wallet']) && $request['synch_wallet']>=0){
    if($limit>0 && isset($json_arr['synchwallets']) && count($json_arr['synchwallets'])>0) $limit= $limit-count($json_arr['synchwallets']);
    if($limit<=0)$limit=1;
    $json_arr['synchwallets']['count_synch_wallet']=0;
    $query_wallets_synch= "SELECT `wallet`, `ref1`, `ref2`, `ref3`, `noda`, `nodause`, `balance`, `date`, `percent_ref`, `date_ref`, `height`, `signpubnew`, `signnew`, `signpub`, `sign` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_wallets` WHERE `wallet`>'".$request['synch_wallet']."' ".(isset($json_arr['synchwallets']) && count($json_arr['synchwallets'])>0?"and `wallet` NOT IN ('".implode("','",array_keys($json_arr['synchwallets']))."')":'')." and `view`>0 and `date`< UNIX_TIMESTAMP()-60 ORDER BY `wallet` LIMIT ".$limit.";";
    $result= mysqli_query($mysqli_connect,$query_wallets_synch) or die("error_synchwallets_wallets_synch");
    while($sqltbl_arr= mysqli_fetch_array($result,MYSQLI_ASSOC)){
      $json_arr['synchwallets'][$sqltbl_arr['wallet']]= $sqltbl_arr;
      $json_arr['synchwallets'][$sqltbl_arr['wallet']]['synch_wallet']=1;
      $json_arr['synchwallets']['count_synch_wallet']++;
    }
  }
  $stop=1;
} else 
if($stop!=1 && $request['type']=="contacts" && isset($request['wallet']) && isset($request['password'])){
  query_bd("SELECT `password` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_users` WHERE `wallet`= '".$request['wallet']."' and `nodatrue`=1 LIMIT 1;");
  if(isset($sqltbl['password']) && gen_sha3($sqltbl['password'],256)==$request['password']){
    ignore_user_abort(1);
    set_time_limit(5);
    if(isset($_POST['contacts'])){
      query_bd("DELETE FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_contacts` WHERE `wallet`= '".$request['wallet']."';");
      $json_arr['contact']= 'del';
      $_POST['contacts']= json_decode($_POST['contacts'],true);
      if(count($_POST['contacts'])>=1 && count($_POST['contacts'])<=100){
        delay_now();
				$index=1;
        foreach($_POST['contacts'] as $key => $value){
          if(isset($key) && preg_replace("/[^0-9a-zA-Z]/",'',$key)==$key 
					&& mysqli_real_escape_string($mysqli_connect,$key)==$key && strlen($key)<=255
          && isset($value) && preg_replace("/[^0-9a-zA-Z]/",'',$value)==$value 
					&& mysqli_real_escape_string($mysqli_connect,$value)==$value && strlen($value)<=255
          ){
            query_bd("INSERT IGNORE INTO `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_contacts` SET `wallet`= '".$request['wallet']."',`number`= '".$index."', `recipient`= '".$key."', `name`= '".$value."';");
            if(mysqli_affected_rows($mysqli_connect)==1){
							$json_arr['contact']= 'save';
							$index++;
            }else {$json_arr['contact']= 'false';break;}
          }
        }
      }
    } else {
      $query= "SELECT `recipient`,`name` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_contacts` WHERE `wallet`= '".$request['wallet']."' ".(isset($request['recipient'])?"and `recipient`= '".$request['recipient']."'":'')." ORDER BY `number` LIMIT 100;";
        $result= mysqli_query($mysqli_connect,$query) or die("error_contacts_view");
        while($sqltbl_arr= mysqli_fetch_array($result,MYSQLI_ASSOC))$json_arr['contacts'][]= $sqltbl_arr;
        if(!isset($json_arr['contacts']))$json_arr['contacts']['contacts']='not_found_contacts_this_noda';
				else $json_arr['contacts']= array_values($json_arr['contacts']);
    }
    ignore_user_abort(0);
  }
  $stop=1;
} else 
if($stop!=1 && $request['type']=="email" && isset($request['wallet']) && isset($request['password'])){
    query_bd("SELECT `password` FROM `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_users` WHERE `wallet`= '".$request['wallet']."' and `nodatrue`=1 LIMIT 1;");
    if(isset($sqltbl['password']) && gen_sha3($sqltbl['password'],256)==$request['password']){
      ignore_user_abort(1);
      set_time_limit(5);
      if(!isset($request['email'])){
        query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_users` SET `email`= '', `up`= '0', `down`= '0', `date`= '".$json_arr['time']."' WHERE `wallet`= '".$request['wallet']."' LIMIT 1;");
        $json_arr['emailwallet']= 'del';
      } else {
        query_bd("UPDATE `".$GLOBALS['database_db']."`.`".$GLOBALS['prefix_db']."_users` SET `email`= '".$request['email']."', `up`= '".(isset($request['up'])?$request['up']:'0')."', `down`= '".(isset($request['down'])?$request['down']:'0')."' WHERE `wallet`= '".$request['wallet']."' LIMIT 1;");
        $json_arr['emailwallet']= 'save';
      }
      ignore_user_abort(0);
    }
  $stop=1;
}
if($json_arr){
	if(isset($json_arr['timer_start']))unset($json_arr['timer_start']);
  if(isset($json_arr['countconnect']))unset($json_arr['countconnect']);
  if(isset($json_arr['nodas_send']))unset($json_arr['nodas_send']);
  if(isset($json_arr['send_noda']))unset($json_arr['send_noda']);
  if(isset($json_arr['transaction_check']))unset($json_arr['transaction_check']);
  if(isset($json_arr['walletnew']))unset($json_arr['recipient']);
  if(isset($json_arr['history']))$print= json_encode($json_arr['history']);
  else if(isset($json_arr['referrals']))$print= json_encode($json_arr['referrals']);
  else if(isset($json_arr['referralwallets']))$print= json_encode($json_arr['referralwallets']);
	else if(isset($json_arr['referralresults']))$print= json_encode($json_arr['referralresults']);
  else if(isset($json_arr['contacts']))$print= json_encode($json_arr['contacts']);
  else if(isset($json_arr['nodas']))$print= json_encode($json_arr['nodas']);
  else $print= json_encode($json_arr);
  if(isset($json_arr['noda_site'])) $print= str_replace("\/","/",$print);
  echo $print;
}
exit_now();
?>