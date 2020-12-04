<?php //версия 1.3
//Все файлы данного дополнения для автоматического принятия входящих транзакций должны располагаться в папке /egold_lightpurchase/ от корня сайта и файл настроек ноды egold_settings.php с папкой /egold_lightpurchase/ должны быть в корне этого же сайта
//Проверить работу аддона можно, запустив index.php файл открыв его URL в адресной строке браузера: http://[сайт]/egold_lightpurchase/index.php
//Чтобы встроить форму автоматического принятия входящих транзакций в страницу сайта, нужно в тегах разместить код из index.php так, чтобы было:
//1. <head><script src="/egold_lightpurchase/js/jquery-3.2.1.min.js"></script><style>[код стиля]</style></head>
//2. <body>[html код форм и кнопок]</body>
//3. <script>[код каждого скрипта в той же последовательности]</script>
//4. Для периодической проверки транзакций необходимо добавить transactions.php в cron вида '/[путь папки с php]/php ~/[путь папки с egold.php]/egold_lightpurchase/transactions.php PDpMj4eBEDR54' (PDpMj4eBEDR54 - это пароль для обновления крона, который задаётся ниже в $cron_password и если его нет, то просто не пишем) с периодичснотью исполнения раз в минуту (версия исполнения PHP скрипта в кроне должна быть от 7.1). Можно использовать и так: http://[ip_адрес_ноды]/egold_lightpurchase/transactions.php?cron_password=PDpMj4eBEDR54 (не рекомендуется)

$host_db_lightpurchase= "localhost"; //Адрес сервера базы данных, который в большинстве случаев localhost
$database_db_lightpurchase= ""; //Имя базы данных
$user_db_lightpurchase= ""; //Имя пользователя базы данных
$password_db_lightpurchase= ""; //Пароль базы данных

$wallet_egold_number= "";//Номер кошелька на который будут приходить средства только цифрами. Пример: 111122222333344444

$period_lightpurchase= 5*60;//Период в секундах через который можно зарегистрироваться
$period_clean= 365;//Период в днях через который удаляются записи созданных меток. Очищение записей осуществляется, если они старше заданного количества дней при запросе проверки транзакций любым обращением к скрипту.

$type['details']="0-9";//Фильтр на вводимые данные в форму регистрации, нужно перечислить символы которые можно вводить и добавить их в кавычки к цифрам
//Например для русского алфавита: абвгдеёжзийклмнопрстуфхцчшщъыьэюя
//Для заглавных русских: АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ
//Английские строчные: abcdefghijklmnopqrstuvwxyz
//Английские прописные: ABCDEFGHIJKLMNOPQRSTUVWXYZ
//Для вставки символов их нужно экранировать, ставя перед каждым символом обратный слэш: \,\.\[\]\-\_\(\)\+\=\:\;\#\%\?

$profit_percent= 5;//Процент прибыли

//пользователи которые имеют доступ к обработке транзакций в строчу вместе с уникальным для каждого пользователя паролем: $password['имя_только_английские_буквы']='пароль_любые_буквы_цифры_символы';
$password['admin']='badPdkdMcR21';
$password['admin2']='badPdkdMcR22';

//Пользователи, которым разрешено изменять статус в первоначальное состояние =0 (транзакция не обработана). Каждый в кавычках с новой строки.
$admin[]="admin";

//Пароль для защиты от несанкционированного запуска проверки транзакций. Если пусто, просто пропускается проверка. Если заполнено, то нужно передавать POST или GET запрос для запуска проверки транзакций.
$cron_password= "";

//Начальный текст для короткой ссылки
$text_first= "eGOLD";
//Размер QR кода рассчитывается по формуле: $qrsize*41+8. Например: $qrsize=6, тогда: 6*41+8= 254px
//Пример: 1 = 49px, 2 = 90px, 3 = 131px, 4 = 172px, 5 = 213px, 6 = 254px, 7 = 295px, 8 = 336px, 9 = 377px, 10 = 418px, 11 = 459px, 12 = 500px
$qrsize= 8;

/*
//Для установки базы данных в консоли MySQL выполните запрос:
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