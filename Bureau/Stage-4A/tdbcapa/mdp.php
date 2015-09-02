<?php // Module de cryptage des mot de passe via la fonction sha1()
	if(isset($_POST['password'])) {

		echo sha1('!§' . $_POST['password']);
	}
	else {

		echo"<form action=\"\" method=\"POST\">
			<input name=\"password\" type=\"text\" class=\"password\" placeholder=\"Mot de passe à Hasher\" required=\"required\" title=\"7 caractères minimum\" pattern=\".{7,}\">
			<input type=\"submit\" value=\"Hashez\">
		</form>";
	}
?>
