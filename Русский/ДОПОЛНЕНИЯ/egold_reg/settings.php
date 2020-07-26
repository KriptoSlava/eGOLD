<?php
//Все файлы данного дополнения для автоматической регистрации кошельков должны располагаться в папке /egold_reg/ от корня сайта и файл настроек ноды egold_settings.php с папкой /egold_crypto/ должны быть в корне этого же сайта

$password_bdview= "";//Пароль для просмотра страницы с результатами (например: 3teRl@Uk3): [сайт]/egold_reg/password_bdview.php?password=[пароль]

$host_db_reg= "localhost"; //Адрес сервера базы данных, который в большинстве случаев localhost
$database_db_reg= ""; //Имя базы данных
$user_db_reg= ""; //Имя пользователя базы данных
$password_db_reg= ""; //Пароль базы данных

$email_domain= "";//Домен с которого отправляем. Пример: yandex.ru
$page_reg= "";//Страница на которой производится регистрация нового кошелька. Пример: http://yandex.ru/registr_wallet
$wallet_url= "";//Ссылка на кошелёк для его скачивания. Пример: http://yandex.ru/egold_reg/eGOLD.zip
$MD5= "";//MD5 подпись архива с кошельком, который скачивают по ссылке $wallet_url. Пример: 9B4295C6F475E50428C16CDD86A40139

$wallet_egold_number= "";//Номер кошелька только цифрами. Пример: 111122222333344444
$wallet_egold_key= "";//Ключ. Пример: 0707000f00f7fff9ff00000b001100ffff0a000 (около 2050 символов)

$wallets_more_one= 1;//Если =0, то только 1 кошелёк для одного email адреса, если =1 то можно больше 1 кошелька регистрировать на 1 почту с заданной периодичностью, если =2 то можно больше одного кошелька без ограничения по времени
$period_reg= 24;//Период в часах через который можно зарегистрировать повторно кошелёк, если $wallets_more_one= 1
$period_pin= 10;//Период в секундах через который можно запросить повторно pin для одной и той же почты или IP адреса.
$period_ip= 24*60*60;//Период в секундах через который можно повторно зарегистрировать кошелёк для одного и того же IP адреса.
$period_clean= 7;//Период в днях через который удаляются записи без созданных кошельков, которые запросили пинкод на почту. Очищение всех записей осуществляется, если они старше 7 дней при запросе пинкода любым обращением к скрипту.

/*
//Для установки базы данных в консоли MySQL выполните запрос:
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