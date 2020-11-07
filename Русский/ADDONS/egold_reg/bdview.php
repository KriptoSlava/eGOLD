<?php
//Для отображения ошибок, раскомментировать то что ниже:
// ini_set('error_reporting', E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

include 'settings.php';

if(!isset($_REQUEST['password']) || $_REQUEST['password']!=$password_bdview)exit;

$mysqli_connect= mysqli_connect($host_db_reg,$user_db_reg,$password_db_reg,$database_db_reg) or die("error_connect_bd");
//---
	
$query= "SELECT * FROM `".$GLOBALS['database_db_reg']."`.`eGOLDreg` ORDER by `id` DESC;";
$result_arr = mysqli_query($mysqli_connect,$query) or die("error_result_arr");
$count_wallets = mysqli_num_rows($result_arr);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>База кошельков eGOLD</title>
	<link rel="stylesheet" href="/egold_reg/js/style.css?6" />
	<script src="/egold_reg/js/jquery-3.2.1.min.js"></script>
</head>
<body>
<h2>База кошельков eGOLD (<?= number_format($count_wallets, 0, ',', ' ') ?>)</h2>
<h2><a class="csv_download" href="javascript:void(0);">Скачать таблицу cо всеми кошельками</a></h2>
<table cellpadding="0" cellspacing="0" border="0" id="table" class="sortable">
	<thead>
		<tr>
			<th><h3></h3></th>
			<th><h3>#</h3></th>
			<th><h3>Почта</h3></th>
			<th><h3>Кошелёк</h3></th>
			<th><h3>IP</h3></th>
			<th><h3>Дата</h3></th>
		</tr>
	</thead>
	<tbody>
<?php


$i=0;
$allbalance=0;
$stobig=0;
$table= '';
$csv='';
while ($sqltbl_arr = mysqli_fetch_array($result_arr)) {
	$i++;
	$table .= "
	<tr>
		<td>".$i.",0</td>
		<td>".$i."</td>
		<td><a href='mailto:".$sqltbl_arr['email']."' target='_blank'>".$sqltbl_arr['email']."</a></td>
		<td><a href='https://www.egold.pro/transactions#".$sqltbl_arr['wallet']."' target='_blank'>".$sqltbl_arr['wallet']."</a></td>
		<td>".$sqltbl_arr['ip']."</td>
		<td>".$sqltbl_arr['date']."</td>
	</tr>
	";
	
	$csv .= $i.';"'.$sqltbl_arr['email'].'";"'.$sqltbl_arr['wallet'].'";"'.$sqltbl_arr['ip'].'";"'.$sqltbl_arr['date'].'";'."\n";
}

echo $table;
?>
<tbody>
</table>

<div id="controls">
	<div id="perpage">
		<select onchange="sorter.size(this.value)">
			<option value="5">5</option>
			<option value="10">10</option>
			<option value="20">20</option>
			<option value="50">50</option>
			<option value="100">100</option>
			<option value="200">200</option>
			<option value="500">500</option>
			<option value="1000">1000</option>
			<option value="2000">2000</option>
			<option value="5000" selected="selected">5000</option>
			<option value="10000">10000</option>
			<option value="20000">20000</option>
			<option value="50000">50000</option>
			<option value="100000">100000</option>
		</select>
	</div>
	<div id="navigation">
		<img src="/egold_reg/images/first.gif" width="16" height="16" alt="First Page" onclick="sorter.move(-1,true)" />
		<img src="/egold_reg/images/previous.gif" width="16" height="16" alt="First Page" onclick="sorter.move(-1)" />
		<img src="/egold_reg/images/next.gif" width="16" height="16" alt="First Page" onclick="sorter.move(1)" />
		<img src="/egold_reg/images/last.gif" width="16" height="16" alt="Last Page" onclick="sorter.move(1,true)" />
	</div>
	<div id="text">Отображена страница <span id="currentpage"></span> из <span id="pagelimit"></span></div>
</div>

<script type="text/javascript" src="/egold_reg/js/script.js"></script>
<script type="text/javascript">
//Сортировка в таблице
var sorter = new TINY.table.sorter("sorter");
sorter.head = "head";
sorter.asc = "asc";
sorter.desc = "desc";
sorter.even = "evenrow";
sorter.odd = "oddrow";
sorter.evensel = "evenselected";
sorter.oddsel = "oddselected";
sorter.paginate = true;
sorter.currentid = "currentpage";
sorter.limitid = "pagelimit";
sorter.init("table",0);

$(".csv_download").click(function() {
	var csv= "ID;E-mail;Wallet;Ip;Date;\n";
	var csv1=[],csv2=[],csv3=[],csv4=[],csv5=[];
	$('.sortable tr td:nth-child(2)').each(function(){
		csv1.push($(this).html());
	});
	$('.sortable tr td:nth-child(3) a').each(function(){
		csv2.push($(this).html());
	});
	$('.sortable tr td:nth-child(4) a').each(function(){
		csv3.push($(this).html());
	});
	$('.sortable tr td:nth-child(5)').each(function(){
		csv4.push($(this).html());
	});
	$('.sortable tr td:nth-child(6)').each(function(){
		csv5.push($(this).html());
	});
	
	for(var i=0;csv1[i] && csv2[i];i++){
		csv= csv+csv1[i]+";"+csv2[i]+";"+csv3[i]+";"+csv4[i]+";"+csv5[i]+";\n";
	}

	var link = document.createElement("a");
	link.href = 'data:text/csv,' + encodeURIComponent(csv);
	link.download = "wallets_reg_"+Date.now()+".csv";
	link.click();
});
</script>

</body>
</html>
<?php 
//Закрываем подключение к бд
mysqli_close($mysqli_connect);
?>