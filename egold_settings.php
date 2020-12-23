<?php
//NODE SETTING
//Website requires IP address and establishing MySQL database. IP address and data from database should be added to the file: egold_settings.php
//The root folder of web-hosting addressed by IP should store files and folders: egold.php, egold_settings.php, egold_crypto
//After placing files and folders and adjusting setting to egold_settings.php, you should address egold.php script via browser using http://[ip_address_node]/egold.php check if the installation is successful. In this case you get a message with install_bd parameter marked true.
//To run the node add egold.php with synch parameter in the form of cron '/[folder path with php]/php ~/[folder path with egold.php]/egold.php synch' at a performing interval of 1 per minute (PHP script corn version should be at least 7.1). Alternative usage: http://[ip_address_node]/egold.php?type=synch (not recommended)
//Once the synchronization is completed, in request http://[ip_address_node]/egold.php, datelasttransaction parameter will surpass zero. Synchronization is completed when the datelasttransaction parameter value becomes equal or close to datelasttransaction parameter of synchronized nodes.

$noda_ip= ""; //Node address
$host_db = "localhost";//Database server address in most cases localhost
$database_db = "";//Database name
$user_db = "";//Database user name
$password_db = "";//Database password
$prefix_db = "";//Prefix should consist of Latin capital and small letters for databases protection (egold by default). For added security, set an arbitrary 

//In case node is tied to a wallet it adds +1% to growth in coin amount on a certain wallet and 1 coin from each node transaction. Node activation requires making any transaction through the node via certain wallet after its synchronization with other nodes. In the process, in requesting http://[ip_address_node]/egold.php holder will be indicated with the number of the tied wallet. In a few minutes after the synchronization in requesting http://[ip_address_node]/egold.php?type=wallet&wallet=[number of related wallet] at a current node and at synchronized nodes in nodawallet parameter the IP address of the wallet’s node will be shown and the official eGOLD.html will display G+ bonus next to the balance, and the settings will demonstrate a line with IP address of the node.
//Assessment of G+ bonus is carried out during any inbound and outbound transaction of a wallet tied to node as well as during enrolment of percentage at a wallet, and actually this is 5% assessment instead of 4%. The assessment of bonus also occurs at any transaction within a wallet’s node but at least once every 24 hours if G+balance accounts 1 or more coins as each transaction within a node brings the wallet tied to it 1 coin, and this coin immediately goes to the account of the wallet. This is done so that the assessment of percentages on a wallet’s account would not be interfered with numerous transactions on node. 
//Any transaction sent from a wallet tied to a certain node and directed to some other node deactivates the node. If a node comprises less than 100 coins it will not be taken into account while voting for the transaction verification and its IP may be tied to another wallet, and there will be no its instant synchronization with other nodes. In order to maintain the node’s operating condition, at least one transaction a month should pass through the node, and the wallet balance should be at least 100 coins.

//Number of a node’s wallet should be of the following type: G-1000-00000-0000-00000 or 100000000000000000
$noda_wallet= "";
//$noda_wallet= "G-1000-00000-0000-00000";//Example

//Proper operation of a node requires adding at least 3 trusted nodes for initial data loads. After the initial data loads nodes will be taken from database. Nodes can also be added below line by line: $noda_trust[]= "ip_address_node";
//The list of nodes can be obtained from any trusted node request in the address bar of the browser: http://[ip_address_or_site_trusted_node]/egold.php?type=nodas из параметра "noda"
$noda_trust[]= "";
$noda_trust[]= "";
$noda_trust[]= "";
//$noda_trust[]= "91.106.203.179";//Example of using IPv4 node address

//The transaction history is cleared when any of the following conditions are met:
$history_day= 365;//Specified in days by an integer of at least 1. If less, then not taken into account.
$history_size= 0;//Specified in the number of transactions with an integer of at least 1. If less, it is not taken into account.
//Transaction retention period cannot be less 1 day, even if the specified number of transactions is exceeded. The number of transactions to delete is configured, not the size in megabytes, due to the impossibility of accurately determining the exact size of the database at any given time due to compression, indexing and caching in MySQL. However, the quantity helps to empirically find the correct size of the database.

//In order to set automatic sending of notifications to emails according to transactions of users dealing with a certain node you should link your domain to hosting and specify it below, e.g. egold.pro. Hosting should support php function mail(). For proper email notification system operating, you need a domain not higher than of a second level. What is it and how to get it you may find on the Internet.
//To verify support for the mail() function, after the domain fits into the $email_domain parameter, make a request in the browser http://[ip_ address_nodes]/egold.php and if there is a parameter email_domain with your domain, then the function works.
$email_domain= "";
//$email_domain= "egold.pro";//Example

//Limit of messages being sent at once for inbound and outbound transactions. In case 10 messages are determined, it means 10 for incoming and outgoing messages. If there are too many messages at once, server may block their sending and mail services may consider them as spam.
$email_limit= 10;

//Delay prior to sending message to email is calculated in seconds so that message gets to addressee. Otherwise, mail services or node server may block it.
$email_delay= 0.1;

//Domain which can be used for addressing node for information is indicated with http or https, and this can be done via IP. Example: https://www.egold.pro
//To check the configuration of your site on the node, after you fit the site into the $noda_site parameter, make a request in the browser [site from the $noda_site parameter]/egold.php and if there is a parameter noda_site with your site, then the site is added.
//In any case, node operating requires IP address
$noda_site= "";
//$noda_site= "https://www.egold.pro";//Example
?>