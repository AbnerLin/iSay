<?php
	session_start();
	$ara = explode("|", $_SESSION['vCode']);
	if($ara[0] == $_POST['code']){
		echo("Verify Success. code is ".$ara[0]);
	}else{
		echo("Verify failed. correct code is ".$ara[0].", you typed ".$_POST['code']);
	}
?>