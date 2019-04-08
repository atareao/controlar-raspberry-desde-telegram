<?php

$TOKEN = "";
$TELEGRAM = "https://api.telegram.org/bot$TOKEN";
$TEST_GROUP = '';

function mlog($texto='')
{
	$fecha = date('Y-m-d H:i:s');
	file_put_contents("webhook.log", "$fecha - $texto\n", FILE_APPEND);
}

function http_post($url, $json)
{
	try
	{
		$ans = null;
	 	$ch = curl_init($url);
	 	curl_setopt($ch, CURLOPT_URL, $url);
		$data_string = json_encode($json);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($data_string))
		);
		$ans = json_decode(curl_exec($ch));
		if($ans->ok !== TRUE)
		{
			$ans = null;
		}
	}
	catch(Exception $e)
	{
		mlog("Error: ".$e->getMessage());
	}
	curl_close($ch);
	return $ans;

}

function sendMessage($chat_id, $text)
{
	global $TELEGRAM;
	$json = ['chat_id'    => $chat_id,
		 'text'       => $text,
		 'parse_mode' => 'HTML'];
	return http_post($TELEGRAM.'/sendMessage', $json);
}

function sendMessageWithKeyboard($chat_id, $text, $keyboard)
{
	global $TELEGRAM;
	$json = ['chat_id'      => $chat_id,
		 'text'         => $text,
		 'parse_mode'   => 'HTML',
		 'reply_markup' => array('inline_keyboard' => $keyboard)];
	return http_post($TELEGRAM.'/sendMessage', $json);
}

$json = json_decode(file_get_contents("php://input"));
mlog(json_encode($json));
if(isset($json->message->text))
{
	if(preg_match('/^\/help/', $json->message->text) == 1)
	{
		$msg  = "info   - Muestra información\n";
		$msg .= "mumble - Gestiona Mumble\n";
		$msg .= "ngnix  - Gestiona Nginx\n";
		$msg .= "temp   - Devuelve la temperatura de la Raspberry\n";
		$msg .= "help   - Muestra esta ayuda\n";
		$ans = sendMessage($TEST_GROUP, $msg);
	}
	else if(preg_match('/^\/temp/', $json->message->text) == 1)
	{
		$ans = shell_exec('cat /sys/class/thermal/thermal_zone0/temp');
		$msg = "La temperatura en la Raspberry es de 🌡 ";
		$msg .= number_format(substr($ans, 0, -1)/1000.0, 1);
		$msg .= " ºC";
		$ans = sendMessage($TEST_GROUP, $msg);
	}
	else if(preg_match('/^\/info/', $json->message->text) == 1)
	{
		$msg = shell_exec('free -h');
		$ans = sendMessage($TEST_GROUP, $msg);
	}
	else if(preg_match('/^\/mumble/', $json->message->text) == 1)
	{
		$ans = shell_exec('/home/pi/bot/mumble_status.sh');
		if($ans == 'ON')
		{
			$msg = "El servidor Mumble está en <strong>marcha</strong>\n¿Quieres que lo detenga?";
			$keyboard = array(array(
			               array("text"          => "Si",
			                     "callback_data" => "detener_mumble"),
				       array("text"          => "No",
			       		     "callback_data" => "nada")));

		}
		else
		{
			$msg = "El servidor Mumble está <strong>parado</strong>\n¿Quieres que lo ponga en marcha?";
			$keyboard = array(array(
			               array("text"          => "Si",
			                     "callback_data" => "iniciar_mumble"),
				       array("text"          => "No",
			       		     "callback_data" => "nada")));
		}
		$ans = sendMessageWithKeyboard($TEST_GROUP, $msg, $keyboard);
	}
}
else if(isset($json->callback_query->data))
{
	log(1);
	if($json->callback_query->data == 'iniciar_mumble')
	{
		sendMessage($TEST_GROUP, 'Iniciando Mumble Server');
		$ans = shell_exec('/usr/bin/sudo /home/pi/bot/mumble_start.sh');
		sleep(3);
		$ans = shell_exec('/usr/bin/sudo /home/pi/bot/mumble_status.sh');
		if($ans == 'ON')
		{
			$msg = "El servidor Mumble se ha <strong>iniciado</strong>";
		}
		else
		{
			$msg = "El servidor Mumble <strong>NO</strong> se ha iniciado";
		}
		$ans = sendMessage($TEST_GROUP, $msg);
	}
}
