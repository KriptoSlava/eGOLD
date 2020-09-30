MD5 файла «eGOLD_v1.22.zip»: **BDF187A5BFA1646110779DFDCC3C2B67**

Изменения v1.22 (ВАЖНОЕ ОБНОВЛЕНИЕ): в кошелёк добавлено отображение рефералов по дате последней транзакции от большей к меньшей c их балансами и накопленными монетами для перечисления их по реферальной программе своему реферелу по любой входящей и исходящей транзакции (число со знаком +). В файле ноды egold.php оптимизировано несколько запросов к базе данных. Для обновления ноды, замените файл egold.php без остановки сервера, а для обновления кошелька, замените файл eGOLD.html.

MD5 файла «eGOLD_v1.21.zip»: **A99CAA4CBEE80917B7A525476CB4D8C5**

Изменения v1.21: изменения коснулись только egold.php. В новых версиях MySQL изменились настройки и подключенные модули. Поэтому было выпущено обновление, которое корректно работает с новыми версиями MySQL без её перенастройки. Для обновления ноды, замените файл egold.php без остановки сервера, а для обновления кошелька, замените файл eGOLD.html.

MD5 файла «eGOLD_v1.20.zip»: **7324E4406FBF4386F2513CC4C4B3D14E**

Изменения v1.20 (ВАЖНОЕ ОБНОВЛЕНИЕ): по многочисленным советам и просьбам людей было добавлено зачисление процентов с текущего баланса. То есть, с баланса на момент любой входящей и исходящей транзакции, а не только по последней исходящей транзакции. Также добавлено зачисление реферального вознаграждения на баланс кошелька не только с исходящей транзакции, но и с входящей. Теперь, если реферал не делает никаких транзакций, можно отправить 1 монетку на его кошелёк, и автоматически с него отправится реферальное вознаграждение своим реферерам и произойдёт зачисление этому рефералу пришедших реферальных вознаграждений от нижестоящих его рефералов с реферального баланса на основной. Для обновления ноды, замените файл egold.php без остановки сервера, а для кошелька eGOLD.html. Также в архив добавлена белая книга, обновлённая под текущую версию.

MD5 файла «eGOLD_v1.19.zip»: **EAE4B9FB34B8AA4F5ACFBBB7E4295EC9**

Изменения v1.19: в ноде переработан механизм хэширования для увеличения скорости работы и универсальности настройки на всех серверах. Теперь, все файлы кэша сохраняются в папку egold_temp, поэтому нужны права на запись в эту папку. Папка egold_temp создастся сама при любом обращении к файлу egold.php, в том числе по крону. Если что-то не так, при запуске в браузере скрипта ноды egold.php, он выдаст сообщения о ошибках ноды и что нужно сделать для их устранения. Изменения коснулись только ноды, а именно файла egold.php. Просто замените его. Останавливать ничего не нужно.

MD5 файла «eGOLD_v1.18.zip»: **2E88A473CFD84FED7A27898D36BD6192**

Изменения v1.18: изменения коснулись только ноды, а именно файла egold.php. Просто замените его. Останавливать ничего не нужно. В самом файле оптимизирована выборка нод, для моментального уведомления о новых транзакциях, а также запросы к базе данных для ситуации, когда идут многочисленные запросы шквалом. А также оптимизирована защита от DOS атак и оптимизирован способ кэширования, который увеличивает производительность ноды, а также помогает защите от  DDOS атак в следствии более быстрой обработки запросов, хотя отдельно DDOS защита тоже присутствует.

MD5 файла «eGOLD_v1.17.zip»: **B4A1CADF065F877FA713284D630C92F2**

Изменения v1.17:
1. Произведено значительное ускорение работы ноды, путём уменьшения количества обращений к базе данных и создания файла хэша транзакций, который автоматически помещается в оперативную память из-за количества обращений к нему, что значительно ускоряет работу ноды. Поэтому теперь необходимы права на запись на папку, где расположен файл ноды egold.php. Для обновления версии ноды достаточно заменить файл egold.php.
2. Доработана белая книга. Основное изменение в 18 ответе на вопрос на 31 странице о зачислении реферальных вознаграждений. Сам ответ: Начисление реферального вознаграждения с реферального баланса осуществляется по сложному проценту с округлением до целого числа в меньшую сторону в момент исходящей транзакции реферала и зависит от баланса реферала на момент предыдущей исходящей транзакции реферала, уровня реферала и прошедшего времени с момента предыдущей исходящей транзакции реферала до текущей транзакции. То есть время и баланс реферала по которым начисляются проценты фиксируются в момент исходящей транзакции реферала. Если реферального вознаграждения накопилось менее чем на одну монету, зачисление не происходит. Зачисление реферального вознаграждения происходит на накопительный баланс кошелька, который зачисляется на основной, при любой исходящей транзакции на любой другой кошелёк.

MD5 файла «eGOLD_v1.16.zip»: **B8BB1B1284B6F353C0250FC008329BB7**

Изменения v1.16 (ВАЖНОЕ ОБНОВЛЕНИЕ):
1. В файле кошелька eGOLD.html и файле APK (Android приложение) оптимизирована вёрстка под малые размеры дисплея, на странице реферальных начислений добавлена кнопка с указанием текущих не зачисленных реферальных начислений при нажатии на которую можно их зачислить, исправлена ошибка с отображением номера кошелька реферала на странице реферальных начислений. Для обновления eGOLD.html, просто замените его. Этот файл и является универсальным кошельком. Для установки APK приложения на Android нужно отправить его к себе на устройство или скачать через компьютер и запустить, попутно соглашаясь со всем и предоставив права для установке APK файлов из любых источников. Если запросов не было на разрешения и установка не происходит, необходимо найти в интернете в любом поисковике, как разрешить установку на Android файлов APK из любых источников. 
2. Работа ноды оптимизирована для работы на устройствах, находящихся за роутером, на котором прописан статический IP и нода работает через проброс порта. На ноде значительно увеличена скорость проведения транзакций и синхронизация с другими нодами. Для обновления версии ноды достаточно заменить файл egold.php.
3. Обновлен ответ на 18 вопрос страницы 31 белой книги, в котором подробно рассказывается о реферальных начислениях.

MD5 файла «eGOLD_v1.15.zip»: **09A4DE788D87546E5796CE9C0F1B0704**

Изменения v1.15: в файле кошелька eGOLD.html английской версии подкорректирован перевод фразы о ожидание 10 минут для генерации закрытого ключа. Для обновления, просто замените eGOLD.html, так как этот файл и является кошельком. Создан кошелёк для Android устройств в виде APK. Для его установке нужно отправить его к себе на устройство или скачать через компьютер и запустить, попутно соглашаясь со всем и предоставив права для установке APK файлов из любых источников. Если запросов не было на разрешения и установка не происходит, необходимо найти в интернете в любом поисковике, как разрешить установку на Android файлов APK из любых источников. Для обновления версии ноды достаточно заменить файл egold.php.

MD5 файла «eGOLD_v1.14.zip»: **1F5124D0A47B8C0BEA1267F44AD4F9CB**

Изменения v1.14: в ноде файла egold.php произведена незначительна оптимизация кода. Для обновления ноды достаточно заменить файл egold.php. В файл кошелька eGOLD.html добавлено сообщение при создании закрытого ключа. Для обновления, просто замените eGOLD.html, так как этот файл и является кошельком.

MD5 файла «eGOLD_v1.12.zip»: **1856291B528FB834E80CDEAD7DC23F01**

Изменения v1.12: добавлено пояснение в файл настроек egold_settings.php о том, что для первичной настройки адреса нод можно взять с любых доверенных нод с помощью запроса: http://[IP может быть любой рабочей ноды]/egold.php?type=nodas . Нода ещё была оптимизирована и в основном для работы в Windows. Инструкция по установке ноды на Windows: NODE_WINDOWS.zip Все обновления ноды коснулись файла egold.php. Поэтому, достаточно обновить только его.

MD5 файла «eGOLD_v1.11.zip»: **DBD7A818BB8DB1372B34C58F6F9C2D3C**

Изменения v1.11 (ВАЖНОЕ ГЛОБАЛЬНОЕ ОБНОВЛЕНИЕ):
1. Теперь все файлы кошелька и ноды в архиве будут соответствовать версии архива для избежания путаницы
2. В файле ноды egold.php 1.11 немного ускорена работа самой ноды и оптимизирована работа для нод с высоким пингом. Добавлено отображение MD5 архива и генерация ссылки на него при запросе [нода или домен]/egold.php?version. Для этого, нужно положить архив с одноимённой версией в корень сайта рядом со скриптом ноды egold.php. Пример: http://91.106.203.179/egold.php?version или https://www.egold.pro/egold.php?version Для обновления ноды достаточно заменить файл egold.php
3. В eGOLD.html 1.11 добавлено отображение отправляемой суммы при наведении курсора на кнопку подтверждения отправки монет, когда нужно ввести закрытый ключ и сумма отправления скрыта. При обновлении ноды, проверяется версия кошелька и если он устарел, если на ноде есть новый архив, даётся ссылка на скачивание ноды. Добавлено сообщение поясняющее невозможность совершения операции, если нода не активирована. При входе на страницу ставится, используемая нода, даже если поле ноды в настройках было изменено, но не сохранено. Для обновления, просто замените eGOLD.html, так как этот файл и является кошельком.
4. В файле egold_settings.php убрана версия
5. Подкорректирована белая книга на английском и русском языке


MD5 файла «eGOLD_v1.10.zip»: **1083A0B317369A5BB1C7CA589C2A4738**

Изменения v1.10 (ВАЖНОЕ ГЛОБАЛЬНОЕ ОБНОВЛЕНИЕ):
1. В кошельке eGOLD.html 1.8 добавлен **русский перевод** и доработан английский. В списке транзакций и реферальных начислений убрано подсвечивание строки при фокусе на сенсорном экране, которое приводило к фантомным подсвечиваниям транзакций. Теперь строка выделяется только при нажатии на любой элемент этой строки транзакции и на саму транзакцию. При нажатии на метку (пинкод-комментарий) в транзакциях, если доступно поле метки для отправки, цифры метки вставляются в поле метки для отправки. Сделано это для удобства. Так же присутствуют небольшие косметические правки для разных размеров экрана но в большом количестве. Для обновления кошелька, просто замените файл eGOLD.html и запускайте его. Кошелёк - это и есть этот файл.
2. В ноде файла egold.php 1.9 в E-mail уведомлениях теперь отображается отправитель письма eGOLD, так же в коде egold.php проведена оптимизация и код стал меньше. Для обновления ноды достаточно заменить файл egold.php
3. В файле настроек egold_settings.php 1.9 дано более подробное описание настроек и принципов работы ноды, а также добавлен перевод на английский язык.
4. В архив добавлена белая книга на русском и английском языках и сам архив разделён на две ветки с русским и английским языком.
5. В архив также добавлено дополнение, позволяющее добавить у себя на сайте форму для автоматического создания кошельков. При этом, созданные кошельки будут рефералами кошелька, который будет их создавать. Это дополнение тоже на двух языках.

MD5 файла «eGOLD_v1.9.zip»: **9B4295C6F475E50428C16CDD86A40139**

Изменения v1.9: внесены изменения в кошелёк eGOLD.html v1.7.2 и ноду egold.php v1.8. В обновлении кошелька сделано обновление данных после любой операции по ноде с вводом закрытого ключа с целью отображения верных данных, даже если что-то подвисло или ответ от ноды не пришёл (например, отключился интернет), но запрос на ноду был отправлен и операция прошла. В ноде в модуле отправки E-mail уведомлений проведена оптимизация и в целом в нём стало меньше кода, а работа стала лучше. Для оновление ноды нужно заменить только файл egold.php, а для кошелька заменить eGOLD.html.

MD5 файла «eGOLD_v1.8.1.zip»: **534F30466E4AEA64A00710633ADBB2E5**

Изменения v1.8.1: внесены изменения только в кошелёк. Добавлена проверка на корректный ответ от ноды, чтобы правильно кошелёк выдавал ошибку, если нода отвечает кошельку неверно. Файл кошелька eGOLD.html обновлён до версии v1.7.1.

MD5 файла «eGOLD_v1.8.zip»: **FEB542006620EB7158EC23E334E9E1C9**

Изменения v1.8 (**важное**): доработан алгоритм начисления процентов от рефералов в ноде (изменён файл egold.php до версии 1.7) и отображение начисленния процентов от рефералов в кошельке eGOLD.html v1.7. Обновление обязательно для всех держателей ноды и для этого нужно скачать и заменить только файл egold.php. А для использования обновлённого кошелька, достаточно скачать и запустить файл eGOLD.html.

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
