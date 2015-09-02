<?php require('session.php'); ?>
<?php
	include("pChart/class/pDraw.class.php"); 
	include("pChart/class/pImage.class.php"); 
	include("pChart/class/pData.class.php");
	include("pChart/class/pPie.class.php");
?>
<html>
	<head>
		<title> Histogrammes et Graphes des données des sites </title>
		<link rel="stylesheet" href="css/style_2.css" />
		<meta charset="UTF-8">
	</head>
	<body>
		<?php require('top.php'); ?>
		<div id="body_content">
			<div class="body_content">

			<?php

				try {

					if($db != null) {

						$query = "SELECT DISTINCT archives_room_date FROM archives_rooms ORDER BY archives_room_date ASC"; // On récupère la liste des archives (mois-année)
						$sth = $db->prepare($query);
						$sth->execute();
						$datas = $sth->fetchAll();

						if(count($datas) > 0) {

							$dates = array();

							foreach($datas as $data) {

								$array = explode(" ", $data['archives_room_date']); // On sépare la chaine de caractere "entité - site" du formulaire
								$dates[] = $array[0]; // On récupère uniquement la date du TIMESTAMP
							}

							$previous_date = null; // vérification de la date précédente, pour éviter de mettre deux fois la même date dans le bandeau déroulant si par exemple un archivages s'est effectué sur plusieurs seconde (donc plusieur resultat à cette date dans la BDD)

							echo "<form action=\"\" method=\"POST\"><select name=\"date_archive\">"; // Début du formulaire de selection de la date de l'archives à consulter

							foreach($dates as $id => $date_archive) {

								if($date_archive != $previous_date) {

									// Affichage des dates dans le bandeau déroulant

									echo "<option "; 
									if(isset($_POST['date_archive']) && $date_archive == $_POST['date_archive']) 
										echo "selected"; // Si la date donné en POST, on selectionne cette option par défaut
									echo">" . $date_archive . "</option>";
									$previous_date = $date_archive;
								}
							}

							echo "<option ";
							if(!isset($_POST['date_archive']) || $_POST['date_archive'] == "Donnée actuelle")
								echo "selected"; // Si la date donné en POST, on selectionne cette option par défaut
							echo "> Donnée actuelle </option>"; // Option pour consulter les données actuelles
							echo "</select>\t<input type=\"submit\" value=\"Consulter\"></form>"; // Fin formulaire
						}
					}
				}
				catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
					require('error_log.php');
				}

				if(isset($_POST['date_archive'])) {

					$consult_date = $_POST['date_archive']; // On récupère la date de l'archive a consulter ou les données actuelles
					$error = 0; // Permet de vérifier dans les rendus si la date est au bon format ou si elle existe

					/* Rendu en image .png des graphiques avec les données demandées (archives à la date demandé ou données actuelles) */

					if($consult_date != "Donnée actuelle") { // Si on consulte des archives

						/* Comme les archives ne sont pas modifiable, on vérifie que les images n'existent pas avant de lancer le rendus des images sinon on ne fait qu'afficher les images déjà existante (car les données d'archives ne changent pas, gain de temps sur la consultation des archives en évitant de systhématiquement regénérer une image qui sera toujours la même */

						if(!is_file("images/tr_graph_render_$consult_date.png")) { // Si l'image n'existe pas

							require('render_graph/tr_graph_render.php'); // On lance le rendu
						}

						if(!is_file("images/capae_graph_render_$consult_date.png")) {

							require('render_graph/capae_graph_render.php');
						}
				
						if(!is_file("images/consoe_graph_render_$consult_date.png")) {
		
							require('render_graph/consoe_graph_render.php');
						}

						if(!is_file("images/nbrbpbr_graph_render_$consult_date.png")) {

							require('render_graph/nbrbpbr_graph_render.php');
						}

						if(!is_file("images/tbipi_graph_render_$consult_date.png")) {

							require('render_graph/tbipi_graph_render.php');
						}

						if(!is_file("images/rds_graph_render_$consult_date.png")) {

							require('render_graph/rds_graph_render.php');
						}

						if($error == 0) {

							echo "<img src=\"images/nbrbpbr_graph_render_$consult_date.png\" alt=\"Graphique non disponible\"><br /><br />";
							echo "<img src=\"images/tbipi_graph_render_$consult_date.png\" alt=\"Graphique non disponible\"><br /><br />";
							echo "<img src=\"images/capae_graph_render_$consult_date.png\" alt=\"Graphique non disponible\"><br /><br />";
							echo "<img src=\"images/consoe_graph_render_$consult_date.png\" alt=\"Graphique non disponible\"><br /><br />";
							echo "<img src=\"images/tr_graph_render_$consult_date.png\" alt=\"Graphique non disponible\"><br /><br />";
							echo "<img src=\"images/rds_graph_render_$consult_date.png\" alt=\"Graphique non disponible\"><br /><br />";
						}
						else
							echo "Aucune archive pour la date demandé, ou mauvaise synthaxe.<br />";
					}
					else {
						require('render_graph/tr_graph_render.php');
						require('render_graph/capae_graph_render.php');
						require('render_graph/consoe_graph_render.php');
						require('render_graph/nbrbpbr_graph_render.php');
						require('render_graph/tbipi_graph_render.php');
						require('render_graph/rds_graph_render.php');

						if($error == 0) {

							echo "<img src=\"images/nbrbpbr_graph_render.png\" alt=\"Graphique non disponible\"><br /><br />";
							echo "<img src=\"images/tbipi_graph_render.png\" alt=\"Graphique non disponible\"><br /><br />";
							echo "<img src=\"images/capae_graph_render.png\" alt=\"Graphique non disponible\"><br /><br />";
							echo "<img src=\"images/consoe_graph_render.png\" alt=\"Graphique non disponible\"><br /><br />";
							echo "<img src=\"images/tr_graph_render.png\" alt=\"Graphique non disponible\"><br /><br />";
							echo "<img src=\"images/rds_graph_render.png\" alt=\"Graphique non disponible\"><br /><br />";
						}
						else
							echo "<font color=\"FE2E2E\">Aucune archive pour la date demandé, ou mauvaise synthaxe.</font><br />";
					}
				}
			?>

				-><a href="index.php"> retour</a>
			</div>
		</div>
		<?php require('footer.php'); ?>
	</body>
</html>
