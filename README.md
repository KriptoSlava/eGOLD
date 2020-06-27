MD5 файла «eGOLD_v1.8.zip»: **FEB542006620EB7158EC23E334E9E1C9**

Изменения v1.8 (**важное**): доработан алгоритм начисления процентов в ноде (изменён файл egold.php до версии 1.7) и отображение начисленния процентов от рефералов в кошельке eGOLD.html v1.7. Обновление обязательно для всех держателей ноды и для этого нужно скачать и заменить только файл egold.php. А для использования обновлённого кошелька, достаточно скачать и запустить файл eGOLD.html.

MD5 файла «eGOLD_v1.7.zip»: **FEB6C8610C19FE25B426B0B81B51C1E2**

Изменения v1.7 (**важное**): оптимизирована работа с обновлениями у ноды и оптимизированы начисления реферальных процентов в ноде. Также добавлена команда для просмотра текущей версии ноды: **http://[IP ноды]/egold.php?version**. Обновление обязательно для всех держателей ноды и для этого нужно скачать и заменить только файл egold.php. Он стал версии 1.6.

MD5 файла «eGOLD_v1.6.2.zip»: **5E70EF72AED98D894B87C350B9833A25**

Изменения v1.6.2: в файл кошелька eGOLD.html добавлена проверка, что не используется протокол HTTPS, потому что браузеры блокируют обращение к адресам HTTP, если открыта страница по HTTPS. Подключение к нодам из кошелька eGOLD.html происходит по HTTP.

MD5 файла «eGOLD_v1.6.1.zip»: **90AA3B80FBCADD78851A3A1C52C01F6F**

Изменения v1.6.1: изменения коснулись только файла кошелька eGOLD.html: при небольших значения бонуса от ноды могло прыгать значение то +1 то +0, теперь же сделано плавно и не будет такого, также были внесены небольшие правки в вёрстку кошелька страницы смены закрытого ключа и генерации нового кошелька для мобильных устройств.

MD5 файла «eGOLD_v1.6.zip»: **EF25320CE7E5776712C3956D0A731E73**

Изменения v1.6 (важные): изменения в файле кошелька eGOLD.html версии 1.6: изменены страницы смены закрытого ключа и создания нового кошелька, новый закрытый ключ теперь показывается до смены закрытого ключа кошелька, услажнён метод генерации закрытого ключа, добавлена дополнительная проверка на валидность нового закрытого ключа, работающая на новой ноды версии от 1.5. В файл ноды egold.php версии 1.5 добавлена проверка нового закрытого ключа по его открытому, но для этого требуется новая версия кошелька 1.6. Для обновления ноды, нужно просто заменить файл egold.php.

MD5 файла «eGOLD_v1.5.zip»: **AC29E889917CA0CAD371A1157EAC0550**

Изменения v1.5: изменения коснулись только файла кошелька **eGOLD.html**: 1) произведена оптимизация запросов к ноде при первом запуске кошелька и переходам по меню; 2) если посылаемая сумма больше баланса, то после уведомления о превышении баланса, сумма перевода автоматически устанавливается максимально возможной с учётом комиссии в 2 монеты; 3) бонус не показывается, пока идёт подтверждение исходящей транзакции кошелька; 4) если не разрешено хранение данных в **localStorage**, сверху выдаётся сообщение, что необходимо разрешить.

MD5 файла «eGOLD_v1.4.3.zip»: **75345757DDF6F96E549DD518263C6459**

Изменения v1.4.3: никаких изменений в файлах нет. Архив перепакован и файлы ноды помещены в папку **NODA** для лучшего понимания других людей. Файл кошелька **eGOLD.html** лежит отдельно. Архив сделан в связи с просьбами других людей.

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

IP адреса доверенных нод (включая ноды партнёров) для первичной загрузки базы данных:

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
$noda_trust[]= "5.181.110.217";
$noda_trust[]= "5.189.177.111";

Полный список всех нод, доступен на любой ноде по запросу http://[IP ноды]/egold.php?type=nodas. Например: https://www.egold.pro/egold.php?type=nodas (noda- IP ноды, wallet- кошелёк ноды, balance- баланс кошелька ноды, walletsuse- количество кошельков, использующих ноду, datelastuse- дата и время последней транзакции в юникоде)

Видео инструкция по проверке MD5 архива «eGOLD.rar»: https://www.egold.pro/doc/eGOLD_MD5.mp4

---
_**Может свободно использоваться и распространяться согласно лицензии GNU GPL: https://ru.wikipedia.org/wiki/GNU_General_Public_License**_
