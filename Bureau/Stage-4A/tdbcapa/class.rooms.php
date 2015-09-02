<?php

/*******************************************************/
/* Classe de définition d'une Salle dans l'application */
/*******************************************************/
/* Fonction disponible :
- newRoom : création d'une salle en BDD (Seul les attributs relatifs à l'appartenance de la salle et à son nom sont nécessaire)
- dropRoom : suppression d'une salle en BDD (uniquement l'attribut "_id" est necessaire pour la réalisation de cette fonction)
- showModif : affichage d'un formulaire de modification d'une salle, avec valeur actuelle par défaut (tout les attributs sont necessaire lors de la création de l'objet au préalable)
- modifRoom : modification d'une salle en BDD, avec les données du formulaire précédent (Seul les attributs relatifs à l'appartenance de la salle et à son nom sont nécessaire)
- manageRoom : modification d'une salle en BDD par un employé d'une entité de gestion (les attributs seront fixés dans la fonction) 
- seekData : récupère les informations en BDD (ID du site et de l'entité) et vérifie que l'user peut bien modifier la salle demandé (puis appel à manageRoom si c'est le cas) 
- checkID : Fonction qui vérifie que les informations envoyé par le formulaire modifie une salle que l'utilisateur à le droit de modifier (cas d'un formulaire extérieur qui pointerait vers l'id d'une salle n'appartenant pas à son entité (ID de la salle nécessaire, retourne "true" ou "false" sur le droit de modification 
- retrieveShortNames : Fonction qui retourne le nom court de l'entité et du site auxquels appartient la salle (ID de la salle nécessaire)*/

/**************************/
/* Tableau d'objet "Room" */
/**************************/
/* Fonction disponible :
- addRoom : ajoute un objet "Room" au tableau des salles
- setRoomData : ajoute les valeurs des paramètres aux attributs de l'objet
- showManageData : Affiche le contenu du tableau d'objet "Room" sous forme de ligne d'un tableau
- showRecapData : Affiche le tableau de consolidation 
- showRawData : Affichage des données brutes des salles d'un site (nécessite tout les attributs de l'objet Room au préalable et également le nombre de baie total de salle dans le site en paramètre */

class Room {

	/*** Attributs de l'objet "Room" ***/

	public $_id;
	public $_name;
	public $_siteShortName;
	public $_entityShortName;
	public $_groundArea;
	public $_unusableGroundArea;
	public $_availableGroundArea;
	public $_nbrBaiesInstallable;
	public $_roomUsablePower;
	public $_roomUsedPower;
	public $_remplissageMoyen;
	public $_nbrBaies;
	public $_nbrBaiesPossible;
	public $_pwMoyenBaie;
	public $_comment;
	
	public function __construct($id, $name, $siteShortName, $entityShortName, $room_total_area, $room_unusable_area, $room_baie_area, $room_usable_power, $room_used_power, $nbr_total_baie, $taux_moyen_remplissage, $nbr_baie_possible, $mean_powey_baie, $nbr_baie_installable, $room_comment) {

		$this->_id = $id;
		$this->_name = $name;
		$this->_siteShortName = $siteShortName;
		$this->_entityShortName = $entityShortName;
		$this->_groundArea = $room_total_area;
		$this->_unusableGroundArea = $room_unusable_area;
		$this->_availableGroundArea = $room_baie_area;
		$this->_roomUsablePower = $room_usable_power;
		$this->_roomUsedPower = $room_used_power;
		$this->_remplissageMoyen = $taux_moyen_remplissage;
		$this->_nbrBaies = $nbr_total_baie;
		$this->_nbrBaiesPossible = $nbr_baie_possible;
		$this->_pwMoyenBaie = $mean_powey_baie;
		$this->_nbrBaiesInstallable = $nbr_baie_installable;
		$this->_comment = $room_comment;
	}

	/*** Fonction de création d'une nouvelle salle en BDD, seul les attributs relatifs aux informations d'appartenance de la salle seront traité (pas de donnée 		     directement ***/

	public function newRoom($db) {

		try {
			if($db != null) {

				// On récupère l'ID de l'entité et l'ID du site auxquelles rattacher la salle

				$query = "SELECT site_id, id FROM entity, sites WHERE short_name=:short_name AND site_short_name=:site_short_name";
				$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':short_name' => $this->_entityShortName, ':site_short_name' => $this->_siteShortName));
				$datas = $sth->fetchAll();

				if(count($datas) > 0) {
		
					foreach($datas as $data) {

						$entity_id = $data['id'];
						$site_id = $data['site_id'];
					}

					// On vérifie qu'une salle n'existe pas déjà sous ce nom court

					$query = "SELECT * FROM rooms WHERE room_name=:room_name AND room_entity_id=:entity_id AND room_entity_site_id=:site_id";
					$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$sth->execute(array(':room_name' => $this->_name, ':entity_id' => $entity_id, ':site_id' => $site_id));
					$datas = $sth->fetchAll();

					if(count($datas) == 0) {

						/*** Ajout d'une nouvelle salle en BDD ***/

						$query = "INSERT INTO rooms (room_entity_id, room_entity_site_id, room_name) VALUES (:entity_id, :site_id, :room_name)";
						$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
						$sth->execute(array(':entity_id' => $entity_id, ':site_id' => $site_id, ':room_name' => $this->_name));

						header("location: rooms.php");
					}
					else
						echo "<font color=\"FE2E2E\">Une salle de ce site appartenant à cette entité existe déjà sous ce nom</font>";
				}
				else
					echo "<font color=\"FE2E2E\">Aucune entité ou site existant sous ce nom</font>";
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
			require('error_log.php');
		}
	}

	/*** Fonction de suppression de salle en BDD, seul l'attribut "_id" peut être renseigné ***/

	public function dropRoom($db) {

		try {
			if($db != null) {

				// On supprime les baies que possède cette salle

				$query = "DELETE FROM salle_possede WHERE room_id=:id";
				$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':id' => $this->_id));

				// On supprime ensuite la salle

				$query = "DELETE FROM rooms WHERE room_id=:id";
				$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':id' => $this->_id));

				header("Location: rooms.php");
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
			require('error_log.php');
		}
	}

	/*** Fonction de modification d'une salle en BDD, seuls les attributs d'information d'appartenance de la salle et son nom sont nécessaire ***/

	public function modifRoom($db) {

		try {
			if($db != null) {

				// Si le nom de la salle n'est pas déjà utilisé

				$query = "SELECT * FROM rooms WHERE room_entity_id=:entity_id AND room_entity_site_id=:site_id AND room_name=:name AND room_id!=:id";
				$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':entity_id' => $this->_entityShortName, ':site_id' => $this->_siteShortName, ':name' => $this->_name, ':id' => $this->_id));
				$datas = $sth->fetchAll();

				if(count($datas) == 0) {

					// On modifie les données de la salle en BDD

					$query = "UPDATE rooms SET room_name=:name, room_entity_id=:entity_id, room_entity_site_id=:site_id WHERE room_id=:id";
					$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$sth->execute(array(':name' => $this->_name, ':entity_id' => $this->_entityShortName, ':site_id' => $this->_siteShortName, ':id' => $this->_id));
					header("Location: rooms.php");
				}
				else
					echo "<font color=\"FE2E2E\">Ce nom de salle et de site existe déjà</font>";
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
			require('error_log.php');
		}
	}

	/*** Fonction d'affichage du formulaire de modification de la salle (côté administrateur) ***/

	public function showModif($db) {

		echo"<div id=\"body_content\">
			<div class=\"body\">
			<p> Modification de la salle : " . $this->_name. " (" . $this->_entityShortName . " - ";

		if($this->_siteShortName == null)
			echo "null";
		else
			echo $this->_siteShortName;
		
		echo ") </p>";

		try {
			if($db != null) {

				// On récupère la totalité des couples "entité - site"

				$query = "SELECT * FROM sites INNER JOIN entity ON sites.site_entity_id = entity.id ORDER BY id ASC, site_id ASC";
				$sth = $db->prepare($query);
				$sth->execute();

				$datas = $sth->fetchAll();

				if(count($datas) > 0) {

					// Début d'affichage du formulaire de modification

					echo "<form action=\"rooms.php?modif_id=" . $this->_id . "\" method=\"POST\">
					Entité et site de rattachement : <select name=\"entity\" class=\"entity\">";

					foreach($datas as $data) { // Début d'affichage du bandeau déroulant du choix de l'entité/site d'appartenance de la salle

						echo "<option ";

						if($this->_entityShortName == $data['short_name'] && $this->_siteShortName == $data['site_short_name'])
							echo "selected";			

						echo">" . $data['short_name'] . " - " . $data['site_short_name'] . "</option>";
					}

					echo "</select><br /><label>Nom de la salle : </label><input name=\"name\" type=\"text\" class=\"name\" placeholder=\"Nom de la Salle\" value=\"" . $this->_name . "\" required=\"required\" title=\"Nom de la salle\"><br /><input type=\"submit\" value=\"Créer\">
					</form>-><a href=\"rooms.php\"> retour</a></div></div>";

					// Fin d'affichage du formulaire
				}
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
			require('error_log.php');
		}
	}

	/*** Affichage du formulaire de modification d'une salle en BDD (côté employé d'entité de gestion) ***/

	public function manageRoom($db) {

		try {

			// Début d'affichage du formulaire

			echo "<div class=\"manage\"><form action=\"manage_rooms.php?id=" . $this->_id . "\" method=\"POST\">";

			if($db != null) {

				// On récupère les données relatives à la salle

				$query = "SELECT * FROM rooms WHERE room_id=:id";
				$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':id' => $this->_id));
				$datas = $sth->fetchAll();

				if(count($datas) > 0) {				

					foreach($datas as $data) {

						$room = new Room($data['room_id'], $data['room_name'], null, null, $data['room_total_area'], $data['room_unusable_area'], $data['room_baie_area'], $data['room_usable_power'], $data['room_used_power'], $data['nbr_total_baie'], $data['taux_moyen_remplissage'], $data['nbr_baies_possible'], $data['room_baie_mean_power'], $data['nbr_baies_installable'], $data['room_comment']); // création de l'objet "Room"		
					}

					/*** On affiche les valeurs actuelle présente dans la BDD comme valeur par défaut dans les champs du formulaire ***/
					/*** AFFICHAGE DES INFORMATIONS GENERIQUE DE LA SALLE ***/ 
					echo "Modification de la salle : " . $room->_name . "<br /><br />";
					echo "<label>Surface totale de la Salle : </label><input name=\"surface_salle\" type=\"text\" class=\"surface_salle\" placeholder=\"Surface de la salle\" required=\"required\" title=\"Surface de la salle\" value=\""; if($room->_groundArea == null) echo "0"; else echo $room->_groundArea; echo "\"><br />
						<label>Surface Inutilisable de la salle : </label><input name=\"unusable_surface_salle\" type=\"text\" class=\"unusable_surface_salle\" placeholder=\"Surface inutilisable de la salle\" required=\"required\" title=\"Surface inutilisable de la salle\" value=\""; if($room->_unusableGroundArea == null) echo "0"; else echo $room->_unusableGroundArea; echo "\"><br />";

					/********************************************************/
					/*** AFFICHAGE DES BAIES DE LA SALLE ***/

					// On récupère les données relatives aux baies (chaque type de baies présente dans la BDD

					$query_2 = "SELECT * FROM baies";
					$sth_2 = $db->prepare($query_2);
					$sth_2->execute();
					$datas_2 = $sth_2->fetchAll();

					if(count($datas_2) > 0) {				

						foreach($datas_2 as $data_2) { // Pour chacun des type de baie

							// On vérifie en BBD le nombre de ce type de baie présente dans cette salle

							$query_3 = "SELECT nbr_baie FROM salle_possede WHERE room_id=:room_id AND baie_id=:baie_id";
							$sth_3 = $db->prepare($query_3, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
							$sth_3->execute(array(':room_id' => $this->_id, ':baie_id' => $data_2['baie_id']));
							$datas_3 = $sth_3->fetchAll();

							echo "<label>Nombre de baie de type " . $data_2['baie_type'] . " (L = " . $data_2['width'] . " * P = " . $data_2['depth'] . "): </label><input name=\"baies[]\" type=\"text\" class=\"baies[]\" placeholder=\"Nombre de baie de type " . $data_2['baie_type'] . "\" required=\"required\" title=\"Nombre de baie de type " . $data_2['baie_type'] . "\"";

							if(count($datas_3) > 0) {
		
								foreach($datas_3 as $data_3) {

									echo " value=\"" . $data_3['nbr_baie'] . "\"><br />";
								}
							}
							else
								echo " value=\"0\"><br />";
						}
					}

					/********************************************/
					/*** RETOUR AFFICHAGE INFORMATIONS GENERIQUE DE LA SALLE ***/

					echo "<label>Nombre de baie remplie à 100% : </label><input name=\"baie_100p\" type=\"text\" class=\"baie_100p\" placeholder=\"baie remplie à 100%\" required=\"required\" title=\"Nombre de baie remplie à 100%\" value=\""; if($data['nbr_baie_100'] == null) echo "0"; else echo $data['nbr_baie_100']; echo "\"><br />
						<label>Nombre de baie remplie à 75% : </label><input name=\"baie_75p\" type=\"text\" class=\"baie_75p\" placeholder=\"baie remplie à 75%\" required=\"required\" title=\"Nombre de baie remplie à 75%\" value=\""; if($data['nbr_baie_75'] == null) echo "0"; else echo $data['nbr_baie_75']; echo "\"><br />
						<label>Nombre de baie remplie à 50% : </label><input name=\"baie_50p\" type=\"text\" class=\"baie_50p\" placeholder=\"baie remplie à 50%\" required=\"required\" title=\"Nombre de baie remplie à 50%\" value=\""; if($data['nbr_baie_50'] == null) echo "0"; else echo $data['nbr_baie_50']; echo "\"><br />
						<label>Nombre de baie remplie à 25% : </label><input name=\"baie_25p\" type=\"text\" class=\"baie_25p\" placeholder=\"baie remplie à 25%\" required=\"required\" title=\"Nombre de baie remplie à 25%\" value=\""; if($data['nbr_baie_25'] == null) echo "0"; else echo $data['nbr_baie_25']; echo "\"><br />
						<label>Nombre de baie remplie à 0% : </label><input name=\"baie_0p\" type=\"text\" class=\"baie_0p\" placeholder=\"baie remplie à 0%\" required=\"required\" title=\"Nombre de baie remplie à 0%\" value=\""; if($data['nbr_baie_0'] == null) echo "0"; else echo $data['nbr_baie_0']; echo "\"><br />";

						echo "<label>Puissance totale disponible pour la salle : </label><input name=\"pw_salle\" type=\"text\" class=\"pw_salle\" placeholder=\"Puissance totale disponible pour la salle\" required=\"required\" title=\"Puissance totale disponible pour la salle\" value=\""; if($room->_roomUsablePower == null) echo "0"; else echo $room->_roomUsablePower; echo "\"><br />
						<label>Puissance utilisé dans la salle : </label><input name=\"pw_used_salle\" type=\"text\" class=\"pw_used_salle\" placeholder=\"Puissance utilisé dans la salle\" required=\"required\" title=\"Puissance utilisé dans la salle\" value=\""; if($room->_roomUsedPower == null) echo "0"; else echo $room->_roomUsedPower; echo "\"><br /><br />
						<label>Commentaire sur la salle : </label><textarea name=\"comment\" class=\"comment\" rows=\"5\" cols=\"50\">" . $room->_comment . "</textarea><br />";
					echo "<input type=\"submit\" value=\"Envoyez\">
					</form></div>";

					// Fin d'affichage du formulaire de modification de salle	
				}
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
			require('error_log.php');
		}
	}

	/*** Fonction qui vérifie si l'utilisateur veut modifier une salle qui appartient bien a son entité d'appartenance ***/

	public function seekData($db, $short_name) {

		try {
			if($db != null) {

				// On récupère toutes les informations sur la salle (id du site et de l'entité)

				$query = "SELECT * FROM rooms LEFT JOIN entity ON rooms.room_entity_id = entity.id LEFT JOIN sites ON rooms.room_entity_site_id = sites.site_id WHERE room_id=:id";
				$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':id' => $this->_id));
				$datas = $sth->fetchAll();

				if(count($datas) > 0) {

					foreach($datas as $data) {

						/*** On vérifie que le nom court de l'entité de la salle à modifier correspond bien au nom court de l'entité d'appartenance de l'employé (nom court de l'entité étant unique) ***/

						if($short_name != $data['short_name'])
							echo "<font color=\"FE2E2E\">Vous n'avez pas accès aux salles de cette entité</font>"; // Erreur
						else { // Sinon la modification est légitime et on fixe les attributs

							$this->_name = $data['room_name'];
							$this->_entityShortName = $data['short_name'];
							$this->_siteShortName = $data['site_short_name'];

							self::manageRoom($db); // Affichage du formulaire de modification
						}					
					}
				}
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
			require('error_log.php');
		}
	}

	/*** Fonction qui vérifie que les informations envoyé par le formulaire modifie une salle que l'utilisateur à le droit de modifier (cas d'un formulaire extérieur qui pointerait vers l'id d'une salle n'appartenant pas à son entité ***/ 

	public function checkID($db, $short_name) {

		try {
			if($db != null) {

				// Récupération des informations d'appartenance de la salle

				$query = "SELECT * FROM rooms LEFT JOIN entity ON rooms.room_entity_id = entity.id LEFT JOIN sites ON rooms.room_entity_site_id = sites.site_id WHERE room_id=:id";
				$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':id' => $this->_id));
				$datas = $sth->fetchAll();

				if(count($datas) > 0) {

					foreach($datas as $data) {

						// On vérifie la légitimité de la modification

						if($short_name != $data['short_name'])
							return false; // On refuse et on stoppe le processing des données
						else {

							$this->_name = $data['room_name'];
							$this->_entityShortName = $data['short_name'];
							$this->_siteShortName = $data['site_short_name'];

							return true; // On valide et on lance le processing des données
						}					
					}
				}
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
			require('error_log.php');
		}
	}

	/*** Fonction qui retourne le nom court de l'entité et du site auxquels appartient la salle (ID de la salle nécessaire) ***/

	public function retrieveShortNames($db) {

		try {
			if($db != null) {

				// On récupère les noms courts de la salle d'id renseigné (attributs "_id" de l'objet déterminé hors fonction

				$query = "SELECT site_short_name, room_name FROM rooms LEFT JOIN sites ON rooms.room_entity_site_id = sites.site_id WHERE room_id=:id";
				$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':id' => $this->_id));
				$datas = $sth->fetchAll();

				if(count($datas) > 0) {

					foreach($datas as $data) {

						// Ajout des valeurs respectives en attributs

						$this->_siteShortName = $data['site_short_name'];
						$this->_name = $data['room_name'];
					}
				}
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
			require('error_log.php');
		}
	}
}

class RoomArray {

	public static $room_array = array(); // Création du tableau d'objet

	public static function addroom(Room $room) { // Ajout d'un site au tableau des sites

		if(!isset(self::$room_array[$room->_id])) {

			self::$room_array[$room->_id] = $room;
		}
	}

	public static function setRoomData($id, $name, $siteShortName, $entityShortName, $room_total_area, $room_unusable_area, $room_baie_area, $room_usable_power, $room_used_power, $nbr_total_baie, $taux_moyen_remplissage, $nbr_baie_possible, $mean_powey_baie, $nbr_baie_installable, $room_comment) { // On ajoute les données aux attributs de l'objet

		$room = new Room($id, $name, $siteShortName, $entityShortName, $room_total_area, $room_unusable_area, $room_baie_area, $room_usable_power, $room_used_power, $nbr_total_baie, $taux_moyen_remplissage, $nbr_baie_possible, $mean_powey_baie, $nbr_baie_installable, $room_comment); // création de l'objet et de ses attributs
		self::addroom($room); // et on ajoute l'objet au tableau
	}

	public static function showData() { // Affichage du tableau d'objet

		foreach(self::$room_array as $id => $room) {

			echo "<tr><td>";
			if($room->_entityShortName == null) // Cas ou l'entité a été supprimée par l'administrateur mais où la salle existe toujours
				echo "null";
			else 
				echo $room->_entityShortName;
			echo "</td><td>";
			if($room->_siteShortName == null) // Cas ou le site a été supprimé par l'administrateur mais où la salle existe toujours
				echo "null";
			else 
				echo $room->_siteShortName;
			echo "</td><td>" . $room->_name . "</td><td><a href=\"rooms.php?action=modifier&id=" . $room->_id . "\">modifier</a></td><td><a href=\"rooms.php?action=supprimer&id=" . $room->_id . "\">supprimer</a></td></tr>";

		}
	}

	/*** Affichage du tableau de consolidation ***/

	public static function showRecapData($db) {

		foreach(self::$room_array as $id => $room) {

			echo "<tr><td width=8%>";
			if($room->_entityShortName == null) 
				echo "null";
			else 
				echo $room->_entityShortName;

			echo "</td><td width=6%>";
			if($room->_siteShortName == null) 
				echo "null";
			else 
				echo $room->_siteShortName;

			echo "</td><td width=6%>";
			if($room->_name == null) 
				echo "null";
			else 
				echo $room->_name;

			echo "</td><td width=2%>" . $room->_groundArea . "</td><td width=2%>" . ($room->_groundArea - $room->_unusableGroundArea) . "</td><td width=2%>" . ($room->_groundArea - $room->_unusableGroundArea - $room->_availableGroundArea) . "</td><td width=2%>" . $room->_nbrBaiesPossible . "</td><td width=2%>" . ($room->_roomUsablePower - $room->_roomUsedPower) . "</td><td width=2%>" . $room->_pwMoyenBaie . "</td><td width=2%>" . $room->_nbrBaiesInstallable . "</td><td width=2%>" . $room->_remplissageMoyen . "</td><td width=64%>" . $room->_comment . "</td></tr>";

		}
	}

	/*** Affichage du tableau de salle modifiable par l'employé de l'entité de gestion, option de gestion ***/

	public static function showManageData() {

		foreach(self::$room_array as $id => $room) {

			echo "<tr><td>" . $room->_entityShortName . "</td><td>" . $room->_siteShortName . "</td><td>" . $room->_name . "</td><td><a href=\"manage_rooms.php?id=" . $room->_id . "\">Gestion</a></td></tr>";

		}
	}

	/*** Fonction d'affichage des données brutes des sites (site de l'entité pour le cas d'un employé, tout les sites pour l'administrateur ***/

	public static function showRawData($db, $nbr_row) {

		try {

			if($db != null) {

				// On récupère les informations sur les baies pour affichages des titres

				$query = "SELECT baie_type, width, depth FROM baies";
				$sth = $db->prepare($query);
				$sth->execute();
				$datas = $sth->fetchAll();

				$nbr_baies = count($datas);

				/*** Création d'une tableau d'une ligne, dont les cellules de la lignes sont en fait d'autres tableaux (d'une seule cellule mais de plusieurs lignes)
				     Cette astuce permet l'affichage vertical d'une ligne de la BDD
				     Synthaxe pour ce type de tableau :

				     <table> // tableau général
					<tr> // une ligne
					   <td> // première cellule
					      <table> // tableau dans cette cellule
						<tr> // première ligne
						   <td></td> // cellule première ligne
						</tr>
						<tr> // deuxième ligne
						   <td><td> // cellule deuxième ligne
						</tr>
					      </table> // fin du tableau de la première cellule du tableau général
					   </td> // fin première cellule du tableau général
					   <td> // deucième cellule du tableau général
						... // etc
					   </td> // fin
					</tr> // fin ligne tableau général
				     </table> // fin tableau général

				***/

				$height = 132.6 + (44.2 * $nbr_baies);

				echo "<table><tr><td><div class=\"table\"><table border=\"1\"><tr><td>Domaine</td></tr><tr><td height=$height>Espace physique</td></tr><tr><td height=130>Occupation des baies</td></tr><tr><td height=110>Electricité</td></tr><tr><td>Capacité disponible</td></tr></table></td>";

				// Fin premier tableau de la première cellule du tableau général (colonne des catégories d'informations)

				// Début du deuxième tableau (titre de chaque lignes)

				echo "<td><div class=\"table_2\"><table border=\"1\"><tr><td>Données</td></tr><tr><td>Surface totale</td></tr><tr><td>Surface condamnée</td></tr>";

				if(count($datas) > 0) {

					foreach($datas as $data)
						echo "<tr><td>Nombre de Baies " . $data['baie_type'] . " (L = " . $data['width'] . " * P = " . $data['depth'] . ")</td></tr><tr><td>Surface occupée Baies " . $data['baie_type'] . "</td></tr>";
				}

				echo "<tr><td>Nombre total de baies</td></tr><tr><td>Nombre de Baies Surface occupée</td></tr><tr><td>Nombre de Baies Surface disponible</td></tr><tr><td>Nombre de Baies pouvant être installées</td></tr><tr><td>Nombre de Baies remplies à 100%</td></tr><tr><td>Nombre de Baies remplies à 75%</td></tr><tr><td>Nombre de Baies remplies à 50%</td></tr><tr><td>Nombre de Baies remplies à 25%</td></tr><tr><td>Nombre de Baies vides</td></tr><tr><td>Taux moyen de remplissage</td></tr><tr><td>Puissance fournie</td></tr><tr><td>Puissance consommée</td></tr><tr><td>Puissance moyenne consommée par baie</td></tr><tr><td>Puissance disponible</td></tr><tr><td>Nombre de Baies pouvant être installées</td></tr><tr><td>Nombre de Baies pouvant au final être installées</td></tr>";

				echo "</table></div></td>";

				// Fin affichage du tableau de la 2ème cellule du tableau général

				// Début de l'affichage bouclé (le tableau d'une cellule du tableau général correspondant à une salle)

				foreach(self::$room_array as $id => $room) {

					// affichage des attributs de l'objet Room

					echo "<td><div class=\"table\"><table border=\"1\"><tr><td>" . $room->_name . "</td></tr><tr><td>" . $room->_groundArea . "</td></tr><tr><td>" . $room->_unusableGroundArea . "</td></tr>";

					/*** On récupère les informations sur le nombre de baie de chaque type présent dans la salle mais également leurs dimension pour caluler la surface prise ***/

					$query_2 = "SELECT * FROM salle_possede LEFT JOIN rooms ON salle_possede.room_id = rooms.room_id LEFT JOIN baies ON salle_possede.baie_id = baies.baie_id WHERE rooms.room_id=:id";
					$sth_2 = $db->prepare($query_2, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$sth_2->execute(array(':id' => $room->_id));
					$datas_2 = $sth_2->fetchAll();

					if(count($datas_2) > 0) {

						foreach($datas_2 as $data_2) {

							echo "<tr><td>" . $data_2['nbr_baie'] . "</td></tr><tr><td>" . number_format(($data_2['nbr_baie'] * $data_2['ground_area']), 2)  . "</td></tr>";
						}
					}
					else {

						for($i = 0; $i < $nbr_baies; $i++)
							echo "<tr><td>0</td></tr><tr><td>0</td></tr>";
					}

					// On continue l'affichage des attributs de l'objet Room

					echo "<tr><td>" . $room->_nbrBaies . "</td></tr><tr><td>" . $room->_availableGroundArea . "</td></tr><tr><td>" . ($room->_groundArea - $room->_unusableGroundArea - $room->_availableGroundArea) . "</td></tr><tr><td>" . $room->_nbrBaiesPossible . "</td></tr>";

					// On récupère des données liées à l'objet mais non présent en attributs

					$query_3 = "SELECT * FROM rooms WHERE room_id=:id";
					$sth_3 = $db->prepare($query_3, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$sth_3->execute(array(':id' => $room->_id));
					$datas_3 = $sth_3->fetchAll();

					if(count($datas_3) > 0) {

						foreach($datas_3 as $data_3) {

							echo "<tr><td>" . $data_3['nbr_baie_100'] . "</td></tr><tr><td>" . $data_3['nbr_baie_75'] . "</td></tr><tr><td>" . $data_3['nbr_baie_50'] . "</td></tr><tr><td>" . $data_3['nbr_baie_25'] . "</td></tr><tr><td>" . $data_3['nbr_baie_0'] . "</td></tr>";				
						}
					}
					else
						echo "<tr><td>0</td></tr><tr><td>0</td></tr><tr><td>0</td></tr><tr><td>0</td></tr><tr><td>0</td></tr>";

					// On continue à afficher les attributs de l'objet Room

					echo "<tr><td>" . $room->_remplissageMoyen . "</td></tr><tr><td>" . $room->_roomUsablePower . "</td></tr><tr><td>" . $room->_roomUsedPower . "</td></tr><tr><td>" . $room->_pwMoyenBaie . "</td></tr><tr><td>" . ($room->_roomUsablePower - $room->_roomUsedPower) . "</td></tr><tr><td>";


					if($room->_nbrBaies == 0)
						echo "0";
					else
						echo floor(($room->_roomUsablePower - $room->_roomUsedPower) / $room->_pwMoyenBaie);

					echo "</td></tr><tr><td>" . $room->_nbrBaiesInstallable . "</td></tr>";

					echo "</table></div></td>";

					// Fin de l'affichage de la salle, on continue le foreach
				}

				// Fin du foreach, chacune des salles du site sélectionné ont été affichés
				// Début de l'affichage du tableau comprenant le total de chaque ligne

				echo "<td><div class=\"table\"><table border=\"1\">";

				// On récupère la somme de chaque champ des salles d'un site 

				$query = "SELECT SUM(room_total_area) AS room_total_area, SUM(room_unusable_area) AS room_unusable_area, SUM(nbr_total_baie) AS nbr_total_baie, SUM(nbr_baies_possible) AS nbr_baies_possible, SUM(room_baie_area) AS room_baie_area, SUM(nbr_baie_100) AS nbr_baie_100, SUM(nbr_baie_75) AS nbr_baie_75, SUM(nbr_baie_50) AS nbr_baie_50, SUM(nbr_baie_25) AS nbr_baie_25, SUM(nbr_baie_0) AS nbr_baie_0, SUM(taux_moyen_remplissage) AS taux_moyen_remplissage, SUM(room_usable_power) AS room_usable_power, SUM(room_used_power) AS room_used_power, SUM(room_baie_mean_power) AS room_baie_mean_power, SUM(nbr_baies_installable) AS nbr_baies_installable FROM rooms LEFT JOIN sites ON rooms.room_entity_site_id = sites.site_id WHERE sites.site_short_name=:site_short_name";
				$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':site_short_name' => $room->_siteShortName));
				$datas = $sth->fetchAll();

				if(count($datas) > 0) {

					echo "<tr><td> Total </td></tr>";

					foreach($datas as $data) {

						// Affichage du total de chaque ligne

						echo "<tr><td>" . $data['room_total_area'] . "</td></tr><tr><td>" . $data['room_unusable_area'] . "</td></tr>";

						// On récupère les informations sur les baies

						$query_2 = "SELECT baie_id, ground_area FROM baies";
						$sth_2 = $db->prepare($query_2);
						$sth_2->execute();
						$datas_2 = $sth_2->fetchAll();

						if(count($datas_2) > 0) {

							foreach($datas_2 as $data_2) {

								// On récupèrele total du nombre de baie (par type) présente sur le site choisi

								$query_3 = "SELECT SUM(nbr_baie) AS nbr_baie FROM salle_possede LEFT JOIN rooms ON salle_possede.room_id = rooms.room_id LEFT JOIN baies ON salle_possede.baie_id = baies.baie_id LEFT JOIN sites ON rooms.room_entity_site_id = sites.site_id WHERE site_short_name=:site_short_name AND baies.baie_id=:baie_id";
								$sth_3 = $db->prepare($query_3, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
								$sth_3->execute(array(':site_short_name' => $room->_siteShortName, ':baie_id' => $data_2['baie_id']));
								$datas_3 = $sth_3->fetchAll();

								if(count($datas_3) > 0) {

									foreach($datas_3 as $data_3)
										echo "<tr><td>" . $data_3['nbr_baie'] . "</td></tr><tr><td>" . ($data_3['nbr_baie'] * $data_2['ground_area']) . "</td></tr>";
								}
								else
									echo "<tr><td>0</td></tr><tr><td>0</td></tr>";
							}
						}

					echo "<tr><td>" . $data['nbr_total_baie'] . "</td></tr><tr><td>" . number_format($data['room_baie_area'], 2) . "</td></tr><tr><td>" . number_format(($data['room_total_area'] - $data['room_unusable_area'] - $data['room_baie_area']), 2) . "</td></tr><tr><td>" . $data['nbr_baies_possible'] . "</td></tr><tr><td>" . $data['nbr_baie_100'] . "</td></tr><tr><td>" . $data['nbr_baie_75'] . "</td></tr><tr><td>" . $data['nbr_baie_50'] . "</td></tr><tr><td>" . $data['nbr_baie_25'] . "</td></tr><tr><td>" . $data['nbr_baie_0'] . "</td></tr><tr><td>" . number_format(($data['taux_moyen_remplissage'] / $nbr_row), 2) . "</td></tr><tr><td>" . $data['room_usable_power'] . "</td></tr><tr><td>" . $data['room_used_power'] . "</td></tr><tr><td>" . number_format(($data['room_baie_mean_power'] / $nbr_row), 2) . "</td></tr><tr><td>" . ($data['room_usable_power'] - $data['room_used_power']) . "</td></tr>";

					// On récupère les informations la consommation moyenne par baie de chaque site pour un calcul total en ne passant pas par "SUM" de mysql. 
					$query_2 = "SELECT room_baie_mean_power, room_usable_power, room_used_power FROM rooms LEFT JOIN sites ON rooms.room_entity_site_id = sites.site_id WHERE sites.site_short_name=:site_short_name";
					$sth_2 = $db->prepare($query_2, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$sth_2->execute(array(':site_short_name' => $room->_siteShortName));
					$datas_2 = $sth_2->fetchAll();

					$total_baie_installable_pw = 0;

					if(count($datas_2) > 0) {

						foreach($datas_2 as $data_2) {

							if($data_2['room_baie_mean_power'] != 0)
								$total_baie_installable_pw += floor(($data_2['room_usable_power'] - $data_2['room_used_power']) / $data_2['room_baie_mean_power']);
						}
					}

					echo "<tr><td>" . $total_baie_installable_pw . "</td></tr><tr><td>" . $data['nbr_baies_installable'] . "</td></tr>";

					}
				}
		
				echo "</table></div></td></tr></table>";

				// Fin d'affichage du tableau général
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
			require('error_log.php');
		}
	}
}

?>
