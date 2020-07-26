<?php //v1.9
//УСТАНОВКА НОДЫ
//Для сайта нужен IP адрес, а также нужно создать MySQL базу данных. IP адрес и данные по базе данных нужно внести в этот файл: egold_settings.php
//В корневой папке размещения сайта, по IP которого будем обращаться должны лежать файлы и папки: egold.php, egold_settings.php, egold_crypto
//После размещения файлов  и папок и внесения настроек в egold_settings.php, нужно обратиться к скрипту egold.php через браузер по адресу http://[ip_адрес_ноды]/egold.php и посмотреть, что установка прошла успешна. В этом случае выдаётся сообщение в котором будет параметр install_bd со значением true.
//Для работы ноды необходимо добавить egold.php с параметром synch в cron вида '/[путь папки с php]/php ~/[путь папки с egold.php]/egold.php synch' с периодичснотью исполнения раз в 1 минуту (версия исполнения PHP скрипта в кроне должна быть от 7.1). Можно использовать и так: http://[ip_адрес_ноды]/egold.php?type=synch (не рекомендуется)
//После выполнения синхронизации, при запросе http://[ip_адрес_ноды]/egold.php, параметр datelasttransaction станет больше нуля. Синхронизация считается завершённой, когда число параметра datelasttransaction станет равно или близко к параметру datelasttransaction других синхронизированных нод.

$noda_ip= "";//Адрес ноды
$host_db = "localhost";//Адрес сервера базы данных в большинстве случаев localhost
$database_db = "";//Имя базы данных
$user_db = "";//Имя пользователя базы данных
$password_db = "";//Пароль базы данных
$prefix_db = "";//Префикс только из английских больших и маленьких букв и цифр для защиты базы данных (по умолчанию egold). Лучше для безопасности, задать свой произвольный

//Если нода привязана к кошельку, это даёт +1% к росту монет на указанном кошельке и 1 монету с каждой транзакции по ноде. Для работы ноды необходимо совершить любую операцию через данную ноду с помощью данного кошелька после её синхронизации с остальными нодами. При этом, при запросе http://[ip_адрес_ноды]/egold.php параметр owner будет с номером привязанного кошелька. Через несколько минут, после синхронизации, при запросе http://[ip_адрес_ноды]/egold.php?type=wallet&wallet=[номер привязанного кошелька] в текущей ноде и уже синхронизированных нодах в параметре nodawallet появится IP адрес ноды кошелька и в официальном кошельке eGOLD.html отобразится бонус G+ рядом с балансом и в настройках будет строка с IP адресом ноды.
//Зачисление бонуса G+ осуществляется при любой входящей или исходящей транзакции кошелька, привязанного к ноде, также как при зачислении процентов по кошельку, фактически это и есть зачисление 5%, вместо 4%. Зачисление бонуса также происходит при любой транзакции по ноде кошелька, но не реже 1-ого раза в 24 часа, если на балансе G+ за это время накопилось 1-а или более монет, так как любая транзакция по ноде приносит кошельку ноды 1-у монету и она сразу зачисляется на баланс кошелька. Это сделано для того, чтобы накопление процентов с баланса кошелька не сбивалось многочисленными транзакциями по ноде.
//Любая транзакция с кошелька, привязанного к ноде, произведённая в другую ноду, деактивирует ноду. Если на ноде будет меньше 100 монет, она не будет учитываться при голосовании за верность транзакции и её IP может привязать к себе другой кошелёк, также не будет происходить её мгновенная синхронизация с другими нодами. Чтобы нода находилась в рабочем состоянии, через эту ноду должна проходить, не реже 1-ого раза в месяц, хотя бы 1-а транзакция и баланс на кошельке ноды должен быть не менее 100 монет.

//Номер кошелька для ноды должен быть вида: G-1000-00000-0000-00000 или 100000000000000000
$noda_wallet= "";
//$noda_wallet= "G-1000-00000-0000-00000";//Пример

//Для корректной работы ноды нужно добавить от 3 доверенных нод для первичной загрузки данных. После первичной загрузки, ноды уже будут браться из базы данных. Также можно добавить ещё ноды ниже построчно: $noda_trust[]= "ip_адрес_ноды";
$noda_trust[]= "";
$noda_trust[]= "";
$noda_trust[]= "";
//$noda_trust[]= "91.106.203.179";//Пример использования IPv4 адреса ноды

//Количество дней для хранения истории транзакций и истории начислений от рефералов
$history_day= 30;

//Чтобы сделать автоматическую отправку уведомлений на электронную почту по транзакциям пользователей, использующим текущую ноду, нужно привязать к хостингу свой домен и указать его ниже, например egold.pro. Для корректной работы почты, потребуется свой домен не выше второго уровня. Что это такое и где его получить, можно найти в интернете.
$email_domain= "";
//$email_domain= "egold.pro";//Пример

//Лимит отправляемых писем за раз для входящих и исходящих транзакций. Если установлено 10, то для исходящих 10 писем и для входящих 10. При большом количестве писем за раз, сервер может заблокировать их отправку и почтовые сайты могут посчитать это спамом.
$email_limit= 10;

//Задержка перед отправкой письма на электронную почту в секундах, чтобы письма доходили до адресата. Иначе, может быть блокировка со стороны почтовых сайтов или сервера, где расположена нода
$email_delay= 0.1;

//Домен вместе с http или https, по которому разрешено обращаться к ноде для информации, также как по IP. Например: https://www.egold.pro
//В любом случае, для работы ноды нужен IP адрес
$noda_site= "";
//$email_domain= "https://www.egold.pro";//Пример

//Защита от ddos в виде блокировки ноды при превышении заданного количества подключений за последние 9 секунд
$ddos_protect= 1000;
?>