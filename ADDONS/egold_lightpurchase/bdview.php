<?php
//For error display uncomment the below:
// ini_set('error_reporting', E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

include 'settings.php';

if(session_status()!==PHP_SESSION_ACTIVE)session_start();

//User login and memorization
$logout=0;
if(isset($_REQUEST['exit'])){unset($_SESSION['password']);header('Location: '.$_SERVER['PHP_SELF']);exit;}
else if((!isset($_REQUEST['password']) || !$_REQUEST['password']) && !isset($_SESSION['password']))$logout=1;
else if((!isset($_REQUEST['course']) || !$_REQUEST['course'] || !(floatval($_REQUEST['course'])>0)) && !isset($_SESSION['course']))$logout=2;
else{
	if(!isset($_REQUEST['password']) && $_SESSION['password'])$_REQUEST['password']=$_SESSION['password'];
	else $_SESSION['password']=$_REQUEST['password'];
	if(!isset($_REQUEST['course']) && $_SESSION['course'])$_REQUEST['course']=$_SESSION['course'];
	else $_SESSION['course']=floatval($_REQUEST['course']);
	$user= array_search($_REQUEST['password'], $password);
	if(!$user || !$_SESSION['password'] || !$_SESSION['course'])$logout=3;
}

if(!($logout>0)){
	$mysqli_connect = mysqli_connect($host_db_lightpurchase,$database_db_lightpurchase,$password_db_lightpurchase,$database_db_lightpurchase) or die("error_connect_db");
	$query= "SELECT * FROM `eGOLDlightpurchase_log` ORDER by `status` ASC,`id` DESC;";
	$result_arr = mysqli_query($mysqli_connect,$query) or die("error_result_arr");
	$count = mysqli_num_rows($result_arr);
	
	if(in_array($user, $admin))$admin_true=1;
	else $admin_true=0;
} else unset($_SESSION['password']);

$csv_file= 'table_'.gmdate('Y.m.d_H.i.s',time()+$offset*60*60).'.csv';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title><?= ($user?'['.$user.'] ':'') ?>Deposit base eGOLD</title>
	<link rel="stylesheet" href="/egold_lightpurchase/js/style.css?15" />
	<script src="/egold_lightpurchase/js/jquery-3.2.1.min.js"></script>
</head>
<body>
<?php if($logout>0){ ?>
<form id="login" action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
	<input type="text" name="course" value="<?= (isset($_SESSION['course']) && $_SESSION['course']?$_SESSION['course']:'') ?>" placeholder="Course to 1 eGOLD...">
	<input type="text" name="password" value="" placeholder="ENTER PASSWORD...">
	<input type="submit" value="ENTER">
</form>
<?php } else { ?>
<h2>[<?= $user ?>] <a href='?exit'>EXIT</a></h2>
<h2><a class="csv_download" href="javascript:void(0);">Download table with all deposits (<?= number_format($count, 0, ',', ' ') ?>)</a></h2>
<table cellpadding="0" cellspacing="0" border="0" id="table" class="sortable">
	<thead>
		<tr>
			<th><h3>#</h3></th>
			<th><h3>ID</h3></th>
			<th><h3>Wallet</h3></th>
			<th><h3>Height</h3></th>
			<th><h3>Date</h3></th>
			<th><h3>PIN</h3></th>
			<th><h3>eGOLD</h3></th>
			<th><h3>Course</h3></th>
			<th><h3>Sum</h3></th>
			<th><h3>%</h3></th>
			<th><h3>Profit</h3></th>
			<th><h3>Withdraw</h3></th>
			<th><h3>Data</h3></th>
			<th><h3>Changed</h3></th>
			<th><h3>User</h3></th>
			<th><h3>&#128505;</h3></th>
		</tr>
	</thead>
	<tbody>
<?php


$i=0;
$allbalance=0;
$stobig=0;
$table= '';
function gold_wallet_view($wallet){return 'G-'.substr($wallet,0,4).'-'.substr($wallet,4,5).'-'.substr($wallet,9,4).'-'.substr($wallet,13,5);}
while ($sqltbl_arr = mysqli_fetch_array($result_arr)) {
	$i++;
	$sqltbl_arr['wallet']=gold_wallet_view($sqltbl_arr['wallet']);
	
	$sqltbl_arr['course']= floatval($sqltbl_arr['course']>0?$sqltbl_arr['course']:$_SESSION['course']);
	$sqltbl_arr['deposit']= floatval($sqltbl_arr['deposit']>0?$sqltbl_arr['deposit']:$_SESSION['course']*$sqltbl_arr['egold']);
	$sqltbl_arr['profit_percent']= floatval($sqltbl_arr['profit_percent']>0?$sqltbl_arr['profit_percent']:$profit_percent);
	$sqltbl_arr['profit']= floatval($sqltbl_arr['profit']>0?$sqltbl_arr['profit']:$sqltbl_arr['profit_percent']/100*$sqltbl_arr['deposit']);
	$sqltbl_arr['pay']= floatval($sqltbl_arr['pay']>0?$sqltbl_arr['pay']:$sqltbl_arr['deposit']-$sqltbl_arr['profit']);
	$sqltbl_arr['date']= gmdate('Y.m.d H:i:s',strtotime($sqltbl_arr['date'])+$offset*60*60);
	$sqltbl_arr['date_change']= ((int)(substr($sqltbl_arr['date_change'],0,1))>0?gmdate('Y.m.d H:i:s',strtotime($sqltbl_arr['date_change'])+$offset*60*60):'');
	
	$table .= "
	<tr>
		<td>".$i.",0</td>
		<td>".$sqltbl_arr['id']."</td>
		<td><a href='https://www.egold.pro/transactions#".$sqltbl_arr['wallet']."' target='_blank'>".$sqltbl_arr['wallet']."</a></td>
		<td>".$sqltbl_arr['height']."</td>
		<td>".$sqltbl_arr['date']."</td>
		<td>".$sqltbl_arr['pin']."</td>
		<td>".$sqltbl_arr['egold']."</td>
		<td>".$sqltbl_arr['course']."</td>
		<td><b>".$sqltbl_arr['deposit']."</b></td>
		<td>".$sqltbl_arr['profit_percent']."%</td>
		<td>".$sqltbl_arr['profit']."</td>
		<td><b>".$sqltbl_arr['pay']."</b></td>
		<td><b>".$sqltbl_arr['details']."</b></td>
		<td>".$sqltbl_arr['date_change']."</td>
		<td>".$sqltbl_arr['user']."</td>
		<td><input class='status".($admin_true==1?' admin_true':'')."' id='checkbox_".$sqltbl_arr['id']."' type='checkbox' name='status' ".($sqltbl_arr['status']==1?'checked':'')." ".($admin_true!=1 && $sqltbl_arr['status']>0?'disabled':'')."></td>
	</tr>
	";
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
			<option value="100" selected="selected">100</option>
			<option value="200">200</option>
			<option value="500">500</option>
			<option value="1000">1000</option>
			<option value="2000">2000</option>
			<option value="5000">5000</option>
			<option value="10000">10000</option>
			<option value="20000">20000</option>
			<option value="50000">50000</option>
			<option value="100000">100000</option>
		</select>
	</div>
	<div id="navigation">
		<img src="/egold_lightpurchase/images/first.gif" width="16" height="16" alt="First Page" onclick="sorter.move(-1,true)" />
		<img src="/egold_lightpurchase/images/previous.gif" width="16" height="16" alt="First Page" onclick="sorter.move(-1)" />
		<img src="/egold_lightpurchase/images/next.gif" width="16" height="16" alt="First Page" onclick="sorter.move(1)" />
		<img src="/egold_lightpurchase/images/last.gif" width="16" height="16" alt="Last Page" onclick="sorter.move(1,true)" />
	</div>
	<div id="text">Page displayed <span id="currentpage"></span> by <span id="pagelimit"></span></div>
</div>

<script>
function status_click(){
	$('.status').click(function functionName() {
		var element= $(this);
		var checked_true= (element.prop('checked')== true?1:0);
		$.ajax({
      async: true,
      url: "/egold_lightpurchase/checked.php",
      cache: false,
      type: "POST",
      data: {id_checked: element.attr('id').replace(/[^0-9]/g, ""),checked_true: checked_true},
      success: function(data){
        if(data.length==19 && data.replace(/[^0-9]/g, "").length==14){
					element.closest('tr').find('td:nth-last-child(3)').html(data);
					element.closest('tr').find('td:nth-last-child(2)').html('<?= $user ?>');
					<?= ($admin_true!=1?"if(element.prop('checked')== true)element.attr('disabled', '');":'') ?>
        } else {
					if(element.prop('checked')== true)element.removeAttr('checked');
					else element.attr('checked', '');
				}
      }
    });
	});
}
</script>
<script src="/egold_lightpurchase/js/script.js?6"></script>
<script>
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
<script>
status_click();
$(".csv_download").click(function() {
	var csv= "ID;Wallet;Height;Date;PIN;eGOLD;Course;Sum;Percent;Profit;Withdraw;Data;Changed;User;Status;\n";
	var csv1=[],csv2=[],csv3=[],csv4=[],csv5=[],csv6=[],csv7=[],csv8=[],csv9=[],csv10=[],csv11=[],csv12=[],csv13=[],csv14=[],csv15=[];
	$('.sortable tr td:nth-child(2)').each(function(){
		csv1.push($(this).html());
	});
	$('.sortable tr td:nth-child(3) a').each(function(){
		csv2.push($(this).html());
	});
	$('.sortable tr td:nth-child(4)').each(function(){
		csv3.push($(this).html());
	});
	$('.sortable tr td:nth-child(5)').each(function(){
		csv4.push($(this).html());
	});
	$('.sortable tr td:nth-child(6)').each(function(){
		csv5.push($(this).html());
	});
	$('.sortable tr td:nth-child(7)').each(function(){
		csv6.push($(this).html());
	});
	$('.sortable tr td:nth-child(8)').each(function(){
		csv7.push($(this).html());
	});
	$('.sortable tr td:nth-child(9) b').each(function(){
		csv8.push($(this).html());
	});
	$('.sortable tr td:nth-child(10)').each(function(){
		csv9.push($(this).html());
	});
	$('.sortable tr td:nth-child(11)').each(function(){
		csv10.push($(this).html());
	});
	$('.sortable tr td:nth-child(12) b').each(function(){
		csv11.push($(this).html());
	});
	$('.sortable tr td:nth-child(13) b').each(function(){
		csv12.push($(this).html());
	});
	$('.sortable tr td:nth-child(14)').each(function(){
		csv13.push($(this).html());
	});
	$('.sortable tr td:nth-child(15)').each(function(){
		csv14.push($(this).html());
	});
	$('.sortable tr td:nth-child(16) input').each(function(){
		if($(this).prop('checked')== true) csv15.push(1);
		else csv15.push(0);
	});
	
	for(var i=0;csv1[i] && csv2[i] && csv3[i];i++){
		csv= csv+csv1[i]+";"+csv2[i]+";"+csv3[i]+";"+csv4[i]+";"+csv5[i]+";"+csv6[i]+";"+csv7[i]+";"+csv8[i]+";"+csv9[i]+";"+csv10[i]+";"+csv11[i]+";"+csv12[i]+";"+csv13[i]+";"+csv14[i]+";"+csv15[i]+";\n";
	}

	var link = document.createElement("a");
	link.href = 'data:text/csv,' + encodeURIComponent(csv);
	link.download = "transactions_"+Date.now()+".csv";
	link.click();
});
</script>
</body>
</html>
<?php 
//Closing the connection to bd
mysqli_close($mysqli_connect);
}
?>