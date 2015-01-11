<?php
define('TITLE', 'iSay');
define('COPYRIGHT', 'Recommend Browser: Google Chrome<br>Powered by iSay © 2012 All Rights Reserved.');
define('SERVER', 'http://192.168.0.103/');
define('SERVERBACK', 'http://192.168.0.103:8080/');
define('FILE_PATH', '/var/www/isay/front/web/file/');

$image_formats = array("image/jpg", "image/png", "image/gif", "image/bmp", "image/jpeg");
$music_formats = array("audio/mp3");
/* database info */
$host = "localhost";$database = "isay";
$account = "root";
$passwd = "root";
$dsn = "mysql:host=$host;dbname=$database";

// /* mailer info */
// $email = "iSay.Team@gmail.com";
// $email_passwd = "iSay1234";
?>
