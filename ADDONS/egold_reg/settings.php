<?php //version 1.2
//All files of the supplement for automatic registration of wallets should be located in the folder /egold_reg/ of the website root and the file for node settings egold_settings.php with the folder /egold_crypto/ should be located in the website root
//You can check the operation of the addon by running index.php file opening its URL in the address bar of the browser: http://[site]/egold_reg/index.php
//To embed an automatic wallet registration form under your wallet in the site page, you need to tag the code from index.php so, that it is:
//1. <head><script src="/egold_reg/js/jquery-3.2.1.min.js"></script><style>[style code]</style></head>
//2. <body>[html form and button code]</body>
//3. <script>[script code]</script>

$password_bdview= "";//Password for viewing pages with results (example: 3teRl@Uk3): [website]/egold_reg/bdview.php?password=[password]

$host_db_reg= "localhost"; //Database server address, in most cases localhost
$database_db_reg= ""; //Database name
$user_db_reg= ""; //Database username
$password_db_reg= ""; //Database password

$email_domain= "";//Domain of the sender. Example: yandex.ru
$page_reg= "";//Page of new wallet registration. Example: http://yandex.ru/registr_wallet

$wallet_egold_number= "";//Wallet number, only numbers. Example: 111122222333344444
$wallet_egold_key= "";//Key. Example: 0707000f00f7fff9ff00000b001100ffff0a000 (about 2050 symbols)

$wallets_more_one= 1;//If =0, then only one wallet for one email address, if =1 then more than 1 wallet can be registered for 1 email address with a given frequency, if =2, then more than one wallet can be registered for 1 email address without time restrictions
$period_reg= 24;//Interval in hours after which a wallet can be registered again, if $wallets_more_one= 1
$period_pin= 10;//Interval I’m seconds after which you can request pin again for one and the same email and IP address.
$period_ip= 24*60*60;//Interval in seconds in which  user can register a wallet for the same IP address again.
$period_clean= 7;//Interval in days after which records with no registered wallets that requested sending pin codes to emails. Clearing of all records occurs if they are older than specified quantity days at requesting pin code by any reference to script.

/*
//For database installation in MySQL console fulfill the request:
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";
DROP TABLE IF EXISTS `eGOLDreg`;
CREATE TABLE `eGOLDreg` (
  `id` int(11) NOT NULL,
  `email` varchar(250) NOT NULL DEFAULT '',
  `pin` int(11) NOT NULL DEFAULT '0',
  `wallet` varchar(100) NOT NULL DEFAULT '',
  `ip` varchar(250) NOT NULL DEFAULT '',
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `eGOLDreg`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `eGOLDreg`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
*/
?>