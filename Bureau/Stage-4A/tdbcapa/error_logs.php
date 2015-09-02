<?php require('session.php'); ?>
<?php require('class.baies.php'); ?>
<?php
	if($user->userAuth() == 2) { // Niveau d'utilisation Administrateur de l'application
 
		// Affichage du header

		echo"<html>
			<head>
				<title> Logs d'erreurs </title>
				<link rel=\"stylesheet\" href=\"css/style_2.css\" />
				<meta charset=\"UTF-8\">
			</head>
			<body>";
		require('top.php');
		echo"<div id=\"body_content\">
			<div class=\"body\">";

		// début du formulaire

		echo "<form action=\"\" method=\"POST\"><ul>";

		if(isset($_GET['log'])) { // si la variable $_GET a été transmise sur error_log.php

			$logfile = $_GET['log']; // on récupère la variable GET

			if(file_exists("./error_log/$logfile")) { // On vérifie l'existance du fichier

				$content = file_get_contents("./error_log/$logfile"); // S'il existe on récupère son contenu
				$content = nl2br($content); // On ajoute les retours à la ligne HTML
				echo $content; // On affiche le contenu sur la page
			}
			else // Sinon le fichier n'existe pas
				echo "<font color=\"FE2E2E\">Fichier de log '$logfile' introuvable</font>"; // Message d'erreur correspondant affiché
		}
		else { // Sinon on affiche le formulaire pour obtenir la variable passé en GET
			if($dir = opendir('./error_log')) { // On ouvre le dossier des logs utilisateur

				while(false !== ($logfile = readdir($dir))) { // Tant qu'il y a un fichier "non lu" dans le répertoire

					if($logfile != '.' && $logfile != '..' && $logfile != 'index.php') { // Et que ce fichier n'est pas un lien relatif du dossier lui même, et n'est pas l'index du dossier

						echo "<li><a href=\"error_logs.php?log=" . $logfile . "\">" . $logfile . "</a></li>"; // On liste chaque élément du dossier (chaque log du répertoire)
					}
				}

				closedir($dir); // On ferme le dossier de log utilisateur
			}
			else // Sinon le dossier n'existe pas (ou les droits d'accès sont incorrects)
				echo "<font color=\"FE2E2E\">Le dossier de log n'a pas pu être ouvert ou les droits d'accès ne sont pas suffisants</font>"; // On affiche le message d'erreur correspondant

			echo "</ul></form>"; // fin du formulaire
		}

		// Affichage de la fin de la page HTML

		echo "</div>
		</div>";
		require('footer.php');
		echo"</body></html>";
	}
	else { // Si l'utilisateur n'est pas administrateur
		echo"<html><head><title>Erreur</title><link rel=\"stylesheet\" href=\"css/style_2.css\" /><meta charset=\"UTF-8\"></head><body>";
		require('top.php');
		echo"<p><font color=\"FE2E2E\">vous n'avez pas l'autorisation d'accéder à cette page</font></p><br />-><a href=\"index.php\"> retour</a>"; // Il n'a pas le droit d'accéder à cette page, redirection possible vers l'index du site
		require('footer.php');
		echo"</body></html>";
	}
?>
