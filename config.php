<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

#File configuration
define("DIR", __DIR__);

#Database
/**
	Доступ к Базе данных

	Меняем все необходимое
*/
define("DB_NAME", "checkmail"); // имя базы
define("DB_HOST", "localhost"); // размещение базы
define("DB_USER_NAME", "root"); // имя пользователя
define("DB_USER_PASSWORD", ""); // пароль пользователя

#Mail
/**
	Доступ к аккаунту GMAIL для отправки писем

	Меняем только MAIL_USER и MAIL_PASSWORD
*/
	
define("MAIL_IMAP_HOST", "{imap.gmail.com:993/imap/ssl/novalidate-cert}Inbox"); // путь подключения к IMAP серверу
define("MAIL_SMTP_HOST", "ssl://smtp.gmail.com");
define("MAIL_USER", "");
define("MAIL_PASSWORD", "");


