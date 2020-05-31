<?php //v1.1
//УСТАНОВКА НОДЫ
//Для сайта нужен IPv4 или IPv6, а также нужно создать MySQL базу данных. IP адрес и данные по базе данных нужно внести в этот файл: egold_settings.php
//В корневой папке размещения сайта, по IP которого будем обращаться должны лежать файлы и папки: egold.php, egold_settings.php, egold_crypto
//После размещения файлов  и папок и внесения настроек в egold_settings.php, нужно обратиться к скрипту egold.php через браузер по адресу http://[ip_адрес_ноды]/egold.php и посмотреть, что установка прошла успешна. В этом случае выдаётся сообщение в котором будет параметр install_bd со значением true.
//Для работы ноды необходимо добавить egold.php с параметром synch в cron вида '/[путь папки с php]/php ~/[путь папки с egold.php]/egold.php synch' с периодичснотью исполнения раз в 1 минуту (версия исполнения PHP скрипта в кроне должна быть от 7.1). Можно использовать и так: http://[ip_адрес_ноды]/egold.php?type=synch (не рекомендуется)

$noda_ip= ""; //Адрес ноды
$host_db = "localhost"; //Адрес сервера базы данных в большинстве случаев localhost
$database_db = ""; //Имя базы данных
$user_db = ""; //Имя пользователя базы данных
$password_db = ""; //Пароль базы данных
$prefix_db = ""; //Префикс для защиты базы данных (по умолчанию egold). Лучше для безопасности, задать свой произвольный из английских букв и цифр.

//Принадлежность ноды кошельку - даёт +1% к росту монет на указанном кошельке и 1 монету с каждой транзакции по ноде. Для работы ноды необходимо совершить любую операцию через данную ноду с помощью данного кошелька после его синхронизации с остальными нодами. 
//Любая транзакция с кошелька, привязанного к ноде, произведённая в другую ноду, деактивирует ноду. Если на ноде будет меньше 100 монет, она не будет учитываться при голосовании за верность транзакции и её IP может привязать к себе другой кошелёк, также не будет происходить её мгновенная синхронизация с другими нодами. Чтобы нода находилась в рабочем состоянии, кошелёк, привязанный к ноде, должен не реже 1 раза в месяц совершать любую транзакцию со своей ноды.
//Номера кошелька для ноды должен быть вида: G-1000-00000-0000-00000 или 100000000000000000
$noda_wallet= "";

//Для корректной работы ноды нужно добавить от 3 доверенных нод для первичной загрузки данных. После первичной загрузки, ноды уже будут браться из базы данных. Можно добавить ещё ноды ниже построчно: $noda_trust[]= "ip_адрес_ноды";
$noda_trust[]= "";
$noda_trust[]= "";
$noda_trust[]= "";
//$noda_trust[]= "91.106.203.179";//Пример использования IPv4
//$noda_trust[]= "2a00:f940:2:1:2::43c";//Пример использования IPv6

//Количество дней для хранения истории транзакции и истории начисления от рефералов
$history_day= 30;

//Для автоматической отправки уведомлений на электронную почту по транзакциям пользователей, использующим текущей ноду, нужно привязать к хостингу свой домен и указать его ниже, например egold.pro
$email_domain= "";
//Лимит отправляемых писем за раз для входящих и исходящих транзакций равен. Если установлено 10, то для исходящих 10 писем и для входящих 10.
$email_limit= 10;
//Задержка перед отправкой письма на электронную почту в секундах
$email_delay= 0.1;

//Домен вместе с http или https, по которому разрешено обращаться к ноде для информации. Например: https://www.egold.pro
//В любом случае, для работы ноды нужен IP адрес
$noda_site= "";

//Защита от ddos в виде блокировки ноды при превышении заданного количества подключений за последние 9 секунд
$ddos_protect= 1000;
?>