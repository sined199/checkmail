<?php

// правила для триггеров
	// доступные траиггеры:
	// 	subject - по теме письма
	// 	capslock - наличия слов с заглавными буквами
	// 	keywords - по ключевым словам в письме
	// 	sender - по адресу отправителя


// параметры
//обязательные параметры :
//	"pattern" => "***",  // ^ - начало строки; $ - конец строки // Шаблон
//	template_id,  название шаблона в $template_config
//  notification_template_id  название шаблона для уведомления
// необязательные
//	send (1/0),  отправлять письмо или нет (по умолчанию 1)
//	send_email  куда отправлять (по умолчанию отправителю)
//	notification (1/0) отправлять уведомления или нет (по умолчанию 1)
// noresp (1/0) нажатие на No Response Needed (по умолчанию 0)


// переменные
// count  кол-во сообщений от текущего покупателя (включая новое)
// username название текущего аккаунта
// email электронныей адрес покупателя
// mail письмо покупателя
// buyer_name имя покупателя

$notification_email_config = "denisbrah@gmail.com";

$rules_config = array(
	"subject" => array(
		"pattern" => "wqwq",
		"template_id" => "subject",
		"notification_template_id" => "notification_subject",
		"and" => array(
			
		)
	),
	"capslock" => array(
		"template_id" => "capslock",
		"notification_template_id" => "notification_capslock"
	),
	"keywords" => array(
		"pattern" => array("21"),
		"template_id" => "keywords",
		"notification_template_id" => "notification_keywords"
	),
	"sender" => array(
		"pattern" => array("sined199@gmail.com"),
		"template_id" => "sender",
		"notification_template_id" => "notification_sender"
	),
	"mes_number" => array(
		"pattern" => array(
			array("equal" => 1,"template_id" => "mes_number_equal"),
			array("more" => 3,"template_id" => "mes_number_more"),
			array("less" => 10,"template_id" => "mes_number_less")
		),
		"notification_template_id" => "notification_mesnumber"
	)
);


// шаблоны ответов

$template_config = array(
	"subject" => array(
		"subject" => "",
		"template" => "subject_1"
	),
	"sender" => array(
		"subject" => "",
		"template" => "sender_1"
	),
	"capslock" => array(
		"subject" => "",
		"template" => "capslock_1"
	),
	"keywords" => array(
		"subject" => "",
		"template" => "keywords_1"
	),
	"mes_number_equal" => array(
		"subject" => "",
		"template" => "mes_number_equal_1"
	),
	"mes_number_more" => array(
		"subject" => "",
		"template" => "mes_number_more_1"
	),
	"mes_number_less" => array(
		"subject" => "",
		"template" => "mes_number_less_1"
	),
	"notification_subject" => array(
		"subject" => "Сработал триггер subject",
		"template" => "notification/trigger_subject"
	),
	"notification_capslock" => array(
		"subject" => "Сработал триггер capslock",
		"template" => "notification/trigger_capslock"
	),
	"notification_keywords" => array(
		"subject" => "Сработал триггер keywords",
		"template" => "notification/trigger_keywords"
	),
	"notification_sender" => array(
		"subject" => "Сработал триггер sender",
		"template" => "notification/trigger_sender"
	),
	"notification_mesnumber" => array(
		"subject" => "Сработал триггер mes number",
		"template" => "notification/trigger_mesnumber"
	),
	//  Не удалять!
	"empty" => array(
		"subject" => "",
		"template" => "empty_1"
	),
	"hasone" => array(
		"subject" => "",
		"template" => "hasone_1"
	),
	"noresp" => array(
		"subject" => "Mail ".$count." No Resp, ".$username,
		"template" => "noresp_1"
	),
	"noresp_notification" => array(
		"subject" =>  $username." | No Response Needed",
		"template" => "noresp_notification_1"
	)
);