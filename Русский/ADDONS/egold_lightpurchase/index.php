<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	<meta name="format-detection" content="telephone=no">
	<meta http-equiv="x-rim-auto-match" content="none">
	<title>База поступлений eGOLD</title>
	<script src="/js/jquery-3.2.1.min.js"></script>	
	<style>
	#egold_menu_reg .egold_email{
		width: 250px;
	}
	#egold_menu_reg .egold_pin{
		width: 100px;
	}
	#egold_reg_pin, #egold_sms, #egold_qr{
		display:none;
	}
	#egold_menu_reg input[type=button]{
		cursor:pointer;
	}
	</style>
</head>
<body>
	<div id="egold_menu_reg">
		<span id="egold_reg_card">
			<input class="egold_card" type="text" maxlength="25" placeholder="Введите данные">
			<input id="egold_button_reg_pin" type="button" value="Добавить данные">
		</span>
	</div>
	<div id="egold_qr"></div>
	<div id="egold_sms"></div>
	<div id="egold_error"></div>
</body>
<script>
$('.egold_card').on('keyup', function() {
  $(this).val($(this).val().replace(/[^0-9]/ig,''));
});

$('#egold_button_reg_pin').on('click', function() {
	$('#egold_error').html("Загрузка...");
	$('#egold_qr').hide();
	$('#egold_qr').html('');
	$('#egold_sms').hide();
	$('#egold_sms').html('');
	if($('.egold_card').val().length>=10 && $('.egold_card').val().length<=25){
		$('#egold_button_reg_pin').attr('disabled','');
		$.post("/egold_lightpurchase/reg.php", { details: $('.egold_card').val()})
		.done(function(json1) {
			json_decode= JSON.parse(json1);
			if(json_decode.pin && json_decode.pin>0){
				$('#egold_qr').html(json_decode.qr);
				$('#egold_sms').html(json_decode.shortlink);
				$('#egold_sms,#egold_qr').fadeIn();
			}else if(json_decode.error=="user_ip")$('#egold_error').html("Попробуйте ещё раз");
			else if(json_decode.error=="timeout")$('#egold_error').html("Так часто регистрироваться нельзя");
			else if(json_decode.error=="details")$('#egold_error').html("Неправильно заполнены данные");
			else $('#egold_error').html(json_decode.error);
		}, "json")
		.fail(function() {
			$('#egold_error').html("Не удалось подключиться к серверу");
		})
		.always(function() {
			$('#egold_button_reg_pin').removeAttr('disabled');
			if($('#egold_error').html())$('#egold_error').fadeIn();
			if($('#egold_sms').html()){
				$('#egold_error').html('');
				$('#egold_error').hide();
				$('#egold_sms,#egold_qr').fadeIn();
			}
		});
	} else {
		$('#egold_button_reg_pin').removeAttr('disabled');
		$('#egold_error').html("Неправильно заполнено поле с вводом данных");
		$('#egold_error').fadeIn();
	}
});
</script>
</html>