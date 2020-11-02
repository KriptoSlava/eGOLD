<?php //version 1.1
//All files of the supplement for automatic accepting incoming transactions should be located in the folder /egold_lightpurchase/ of the website root and the file for node settings egold_settings.php with the folder /egold_crypto/ should be located in the website root
//You can check the operation of the addon by running index.php file opening its URL in the address bar of the browser: http://[site]/egold_lightpurchase/index.php
//To embed an automatic wallet registration form under your wallet in the site page, you need to tag the code from index.php so, that it is:
//1. <head><script src="/egold_lightpurchase/js/jquery-3.2.1.min.js"></script><style>[style code]</style></head>
//2. <body>[html form and button code]</body>
//3. <script>[code of each script in the same sequence]</script>

$host_db_lightpurchase= "localhost"; //Database server address, in most cases localhost
$database_db_lightpurchase= ""; //Database name
$user_db_lightpurchase= ""; //Database username
$password_db_lightpurchase= ""; //Database password

$wallet_egold_number= "";//The number of the wallet to which funds will come only in numbers. Example:  111122222333344444

$period_lightpurchase= 5*60;//Period in seconds through which you can register
$period_clean= 365;//The period in days after which the records of the created labels are deleted. Records are purged if they are older than the specified number of days when requesting transaction validation by any script access.

$type['details']="0-9";//Filter on the input data in the registration form, you need to list the characters that can be entered and added to quotation marks to the numbers
//For example, for the Russian alphabet: абвгдеёжзийклмнопрстуфхцчшщъыьэюя
//Russian uppercase:  АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ
//English lowercase: abcdefghijklmnopqrstuvwxyz
//English uppercase:  ABCDEFGHIJKLMNOPQRSTUVWXYZ
//To insert characters, they must be shielded, putting in front of each character backslash: \,\.\[\]\-\_\(\)\+\=\:\;\#\%\?

$offset= 3;//Time zone with digit only
$profit_percent= 5;//Percentage of profit

//Users who have access to processing transactions in a line with a unique password for each user: $password['name_only_English_letters']='password_any_letters_numbers_characters';
$password['admin']='badPdkdMcR21';
$password['admin2']='badPdkdMcR22';

//Users who are allowed to change status to original status = 0 (transaction not processed). Each in quotation marks with a new string.
$admin[]="admin";

//Password to protect against unauthorized start of transaction verification. If empty, just skips the check. If full, you must send a POST or GET request to run a transaction check.
$cron_password= "";

//Start text for short link
$text_first= "eGOLD";
//The size QR of the code is calculated by the formula: $qrsize*41+8. For example: $qrsize=6, then: 6*41+8= 254px
//For example: 1 = 49px, 2 = 90px, 3 = 131px, 4 = 172px, 5 = 213px, 6 = 254px, 7 = 295px, 8 = 336px, 9 = 377px, 10 = 418px, 11 = 459px, 12 = 500px
$qrsize= 8;

/*
//For database installation in MySQL console fulfill the request:
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";
DROP TABLE IF EXISTS `eGOLDlightpurchase`;
CREATE TABLE `eGOLDlightpurchase` (
  `id` bigint(18) UNSIGNED NOT NULL,
  `pin` bigint(18) UNSIGNED NOT NULL,
  `details` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `date_deposit` datetime NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `eGOLDlightpurchase_log`;
CREATE TABLE `eGOLDlightpurchase_log` (
  `id` bigint(18) UNSIGNED NOT NULL,
  `pin` bigint(18) UNSIGNED NOT NULL,
  `details` varchar(255) NOT NULL,
  `wallet` bigint(18) UNSIGNED NOT NULL,
  `height` bigint(18) UNSIGNED NOT NULL,
  `egold` bigint(18) UNSIGNED NOT NULL,
  `course` decimal(20,10) UNSIGNED NOT NULL,
  `deposit` decimal(20,10) UNSIGNED NOT NULL,
  `profit_percent` decimal(7,2) UNSIGNED NOT NULL,
  `profit` decimal(20,10) UNSIGNED NOT NULL,
  `pay` decimal(20,10) UNSIGNED NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_change` datetime NOT NULL,
  `user` varchar(255) NOT NULL,
  `status` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `eGOLDlightpurchase`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);
ALTER TABLE `eGOLDlightpurchase_log`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);
ALTER TABLE `eGOLDlightpurchase`
  MODIFY `id` bigint(18) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `eGOLDlightpurchase_log`
  MODIFY `id` bigint(18) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;
*/
?>