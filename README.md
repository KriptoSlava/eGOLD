MD5 файла «eGOLD_v1.4.2.zip»: **740E9CED79485EEF412A4F9C00463627**

Изменения v1.4.2: в egold.php добавлена дополнительная обработка введёных данных по IPv6 если неправильно указан IP адрес, а также проверка на корректность данных базы данных при первой установке. Обновление незначительное и требуется только для тех, кто впервые устанавливает ноду. Для обновления, достаточно заменить файл egold.php

MD5 файла «eGOLD_v1.4.1.zip»: **152C51B86FA56234E14792ECF91FD5C1**

Изменения v1.4.1: в egold.php добавлено пояснение, если не правильно указан IP адрес или имя сайта в файле настроек egold_settings.php. Для обновления, достаточно заменить файл egold.php

MD5 файла «eGOLD_v1.4.zip»: **1626FAE68493530A5703340A7CE8C4DD**

MD5 файла «eGOLD_v1.3.zip»: **FF5444F6D7961E2DAC2BD5E0E0C78BDF**

MD5 файла «eGOLD_v1.2.rar»: **5BA4C3D8BF05A8C217AD34C49E5B4B70**

MD5 файла «eGOLD.rar»: **1835428FEF6E7F5A625E220C588B054A**

Перед распаковкой **eGOLD.rar**, нужно обязательно посмотреть видео инструкцию по проверке подписи MD5 (ссылка ниже в тексте).

Инструкция по кошельку и ноде находится в файле данного репозитория **eGOLD.docx**

Видео инструкция по кошельку eGOLD: https://www.egold.pro/doc/eGOLD_instruction.mp4

eGOLD кошелёк, как приложение для Android: https://www.egold.pro/doc/eGOLD_Android.mp4

eGOLD кошелёк, как приложение для iOS: https://www.egold.pro/doc/eGOLD_iOS.mp4

Видео инструкция по настройке ноды eGOLD: https://www.egold.pro/doc/eGOLD_noda.mp4

IP адреса доверенных нод для первичной загрузки:

$noda_trust[]= "95.169.185.90";
$noda_trust[]= "95.169.184.90";
$noda_trust[]= "95.169.185.32";
$noda_trust[]= "95.169.184.35";
$noda_trust[]= "95.169.184.32";
$noda_trust[]= "91.106.203.179";
$noda_trust[]= "91.106.203.180";
$noda_trust[]= "91.106.203.181";
$noda_trust[]= "91.106.203.202";
$noda_trust[]= "185.50.26.220";

Полный список всех нод, доступен на любой ноде по запросу http://[IP ноды]/egold.php?type=nodas. Например: https://www.egold.pro/egold.php?type=nodas (noda- IP ноды, wallet- кошелёк ноды, balance- баланс кошелька ноды, walletsuse- количество кошельков, использующих ноду, datelastuse- дата и время последней транзакции в юникоде)

Видео инструкция по проверке MD5 архива «eGOLD.rar»: https://www.egold.pro/doc/eGOLD_MD5.mp4

---
_**Может свободно использоваться и распространяться согласно лицензии GNU GPL: https://ru.wikipedia.org/wiki/GNU_General_Public_License**_
