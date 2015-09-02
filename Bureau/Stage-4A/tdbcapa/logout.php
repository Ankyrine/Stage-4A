<?php
	setcookie('login_info', '', time() - 1, null, null, false, true); // On fait expirer le cookies pour déconnecter l'utilisateur lors du clique sur le lien de déconnection
	header("Location: index.php");
?>
