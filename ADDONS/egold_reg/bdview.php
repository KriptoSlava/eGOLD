<?php
//For error display uncomment the below:
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
	<title>eGOLD wallet base</title>
	<link rel="stylesheet" href="/egold_reg/js/style.css?3" />
	<script src="/egold_reg/js/jquery-3.2.1.min.js"></script>
	<style>	
		h2{
			text-align: center;
			margin-bottom: 15px;
		}
		table{
			margin: 0 auto;
		}
		td,th{
			padding: 4px 10px;
			text-align: center;
		}
	</style>
</head>
<body>
<h2>eGOLD wallet base (<?= number_format($count_wallets, 0, ',', ' ') ?>)</h2>
<h2><a href="table.csv" download>Download the wallet chart</a></h2>
<table cellpadding="0" cellspacing="0" border="0" id="table" class="sortable">
	<thead>
		<tr>
			<th><h3>#</h3></th>
			<th><h3>Email</h3></th>
			<th><h3>Wallet</h3></th>
			<th><h3>IP</h3></th>
			<th><h3>Date</h3></th>
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
	
$csv = "#;Email;Wallet;IP;Data;\n".$csv;
$csv = iconv("UTF-8", "windows-1251", $csv);
file_put_contents("table.csv",$csv);
	
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
	<div id="text">Page displayed <span id="currentpage"></span> by <span id="pagelimit"></span></div>
</div>

<script type="text/javascript" src="/egold_reg/js/script.js"></script>
<script type="text/javascript">
//Sorting in the table
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
</script>

</body>
</html>
<?php 
//Closing the connection to bd
mysqli_close($mysqli_connect);
?>