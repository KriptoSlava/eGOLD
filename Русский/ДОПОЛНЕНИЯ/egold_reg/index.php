<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	<meta name="format-detection" content="telephone=no">
	<meta http-equiv="x-rim-auto-match" content="none">
	<title>eGOLD - автоматическая регистрация кошельков</title>
	<script src="/egold_reg/js/jquery-3.2.1.min.js"></script>	
	<style>
	#egold_menu_reg .egold_email{
		width: 250px;
	}
	#egold_menu_reg .egold_pin{
		width: 100px;
	}
	#egold_reg_pin{
		display:none;
	}
	#egold_menu_reg input[type=button]{
		cursor:pointer;
	}
	</style>
</head>
<body>
	<div id="egold_menu_reg">
		<span id="egold_reg_email">
			<input class="egold_email" type="text" maxlength="100" placeholder="Введите почту: ____@____.__">
			<input id="egold_button_send_pin" type="button" value="Создать кошелёк eGOLD">
		</span>
		<span id="egold_reg_pin">
			<input id="egold_button_reg_cancel" type="button" value="X">
			<input class="egold_pin" type="text" maxlength="9" placeholder="Пинкод">
			<input id="egold_button_reg" type="button" value="Подтвердить">
		</span>
	</div>
	<div id="egold_sms"></div>
</body>
<script>
$('.egold_email').on('keyup', function() {
  $(this).val($(this).val().replace(/[^0-9a-zA-Z-@\.!#$%&*+=?_{|}~]/ig,'').toLowerCase());
});
$('.egold_pin').on('keyup', function() {
  $(this).val($(this).val().replace(/[^0-9]/ig,'').toLowerCase());
});

$('#egold_button_send_pin').on('click', function() {
	$('#egold_sms').html('');
	$('#egold_reg_email').hide();
	$.getJSON( "/egold_reg/egold_reg.php", { email: $('.egold_email').val()} )
	.done(function(json1) {
		if(json1.send=='pin'){
			$('#egold_sms').html("Пинкод отправлен на почту");
			$('#egold_reg_email').hide();
			$('#egold_reg_pin').fadeIn();
		}else if(json1.error=="limit_reg")$('#egold_sms').html("Действует ограничение на количество регистраций для одной почты");
		else if(json1.error=="send_pin")$('#egold_sms').html("Не удалось отправить пинкод");
		else if(json1.error=="limit_pin")$('#egold_sms').html("Действует ограничение на период между запросами на пинкод для одной почты и одного IP");
		else if(json1.error=="email")$('#egold_sms').html("Неправильная почта");
		else $('#egold_sms').html(json1.error);
		if(json1.error)$('#egold_reg_email').fadeIn();
	})
	.fail(function() {
		$('#egold_reg_email').fadeIn();
		$('#egold_sms').html("Не удалось подключиться к серверу. Попробуйте ещё раз.");
  });
});

$('#egold_button_reg').on('click', function() {
	$('#egold_reg_pin').hide();
	$('#egold_sms').html('Генерируется кошелёк...');
	$.getJSON( "/egold_reg/egold_reg.php", { email: $('.egold_email').val(), pin: $('.egold_pin').val()} )
	.done(function(json1) {
		if(json1.send=='wallet'){
			$('#egold_sms').html("Кошелёк зарегистрирован и его реквизиты отправлены на почту");
			$('#egold_reg_pin').hide();
			$('#egold_reg_email').fadeIn();
		} else if(json1.error=="pin")$('#egold_sms').html("Неправильно указан пинкод");
		else if(json1.error=="wallet_egold_number")$('#egold_sms').html("В ноде не найден кошелёк с которого будем регистрировать новый");
		else if(json1.error=="wallet_new")$('#egold_sms').html("Ошибка при генерации нового кошелька");
		else if(json1.error=="send_wallet")$('#egold_sms').html("Не удалось отправить кошелёк на почту");
		else if(json1.error=="limit_ip")$('#egold_sms').html("Действует ограничение на период между запросами регистрации кошелька для одного IP");
		else if(json1.error=="email")$('#egold_sms').html("Неправильная почта");
		else $('#egold_sms').html(json1.error);
		if(json1.error)$('#egold_reg_pin').fadeIn();
	})
	.fail(function() {
		$('#egold_reg_pin').fadeIn();
		$('#egold_sms').html("Не удалось подключиться к серверу. Попробуйте ещё раз.");
  });
});

$('#egold_button_reg_cancel').on('click', function() {
	$('#egold_sms').html('');
	$('#egold_reg_pin').hide();
	$('#egold_reg_email').fadeIn();
});
</script>
</html>