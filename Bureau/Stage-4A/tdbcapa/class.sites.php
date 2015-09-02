<?php

/*****************************************************/
/* Classe de définition d'un Site dans l'application */
/*****************************************************/
/* Fonction disponible :
- newSite : création d'un site en BDD (tout les attributs sont necessaire lors de la création de l'objet au préalable)
- dropSite : suppression d'un site en BDD (uniquement l'attribut "_id" est necessaire pour la réalisation de cette fonction)
- showModif : affichage d'un formulaire de modification d'un site, avec valeur actuelle par défaut (tout les attributs sont necessaire lors de la création de l'objet au préalable)
- modifSite : modification d'un site en BDD, avec les données du formulaire précédent (tout les attributs sont necessaire lors de la création de l'objet au préalable) 
- retrieveSiteID : retourne l'ID en BDD correspondant au nom court du site (uniquement l'attribut "_shortName" est necessaire pour la réalisation de cette fonction) */

/**************************/
/* Tableau d'objet "Site" */
/**************************/
/* Fonction disponible :
- addSite : ajoute un objet "Site" au tableau des sites
- setSiteData : ajoute les valeurs des paramètres aux attributs de l'objet
- showData : Affiche le contenu du tableau d'objet "Site" sous forme de ligne d'un tableau */

class Site {

	/*** Attributs de l'objet Site ***/

	public $_id;
	public $_name;
	public $_shortName;
	public $_entityShortName;
	public $_nbrBaiesInstalable;
	public $_meanPowerConsumption;

	public function __construct($id, $name, $shortName, $entityShortName) {

		$this->_id = $id;
		$this->_name = $name;
		$this->_shortName = $shortName;
		$this->_entityShortName = $entityShortName;
	}

	/*** Création d'un nouveau site en BDD, objet crée et attribut ajouté au préalable ***/

	public function newSite($db) {

		try {
			if($db != null) {

				$query = "SELECT * FROM sites WHERE site_short_name=:short_name"; // On vérifie qu'un site n'est pas déjà présent avec ce nom court
				$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':short_name' => $this->_shortName));
				$datas = $sth->fetchAll();

				if(count($datas) == 0) { // Si le nom court est disponible

					/*** Création du nouveau site ***/

					$query = "INSERT INTO sites (site_name, site_short_name, site_entity_id) VALUES (:complete_name, :short_name, :entity_id)";
					$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$sth->execute(array(':complete_name' => $this->_name, ':short_name' => $this->_shortName, ':entity_id' => $this->_entityShortName));

					header("Location: sites.php");
				}
				else
					echo "<font color=\"FE2E2E\">Ce nom court de site est déjà utilisé</font>";
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
			require('error_log.php');
		}
	}

	/*** suppression d'un site en BDD, objet crée et attribut ajouté au préalable ***/

	public function dropSite($db) {

		try {
			if($db != null) {

				$query = "DELETE FROM sites WHERE site_id=:id";
				$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':id' => $this->_id));

				header("Location: sites.php");
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
			require('error_log.php');
		}	
	}

	/*** Affichage du formulaire de modification d'un site ***/

	public function showModif($db) {

		echo"<div id=\"body_content\">
			<div class=\"body\">
			<p> Modification du site : " . $this->_shortName. " (" . $this->_entityShortName . ") </p>";

		try {
			echo "<form action=\"sites.php?modif_id=" . $this->_id . "\" method=\"POST\">
			Entité de rattachement du site : <select name=\"entity\" class=\"entity\">";

			if($db != null) {

				$query = "SELECT * FROM entity";
				$sth = $db->prepare($query);
				$sth->execute();

				$datas = $sth->fetchAll();

				if(count($datas) > 0) {

					foreach($datas as $data) {

						$entity_array = new EntityArray;
						$entity_array->setEntityData($data['id'], null, $data['short_name']);
					}
				
					$entity_array->showModif($db, $this->_entityShortName); // Affichage du bandeau déroulant des entités auxquelles rattacher le site

					echo "</select><br />";
					echo "<label>Nom du site : </label><input name=\"complete_name\" type=\"text\" class=\"complete_name\" placeholder=\"Nom Complete\" value=\"" . $this->_name . "\" required=\"required\" title=\"Nom complet de l'entité\"><br />
						<label>Nom court du site : </label><input name=\"short_name\" type=\"text\" class=\"short_name\" placeholder=\"Nom Court\" value=\"" . $this->_shortName . "\" required=\"required\" title=\"Nom court de l'entité\"><br />
						<input type=\"submit\" value=\"Créer\">
					</form>

					-><a href=\"sites.php\"> retour</a>
					</div>
				</div>";
				}
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
			require('error_log.php');
		}
	}

	/*** Modification d'un site en BDD, objet crée et attribut ajouté au préalable ***/

	public function modifSite($db) {

		try {
			if($db != null) {

				$query = "SELECT * FROM sites WHERE site_short_name=:short_name AND site_id!=:id"; // On vérifie que le nom court est disponible
				$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':short_name' => $this->_shortName, ':id' => $this->_id));
				$datas = $sth->fetchAll();

				if(count($datas) == 0) { // Si c'est le cas

					/*** Modification du site ***/

					$query = "UPDATE sites SET site_name=:complete_name, site_short_name=:short_name, site_entity_id=:entity_id WHERE site_id=:id";
					$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$sth->execute(array(':complete_name' => $this->_name, ':short_name' => $this->_shortName, ':entity_id' => $this->_entityShortName,':id' => $this->_id));
					header("Location: sites.php");
				}
				else
					echo "<font color=\"FE2E2E\">Ce nom de salle et de site existe déjà</font>";
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
			require('error_log.php');
		}
	}

	/*** Fonction pour récupérer l'ID du site en BDD correspondant au nom court du site
	     On passe donc le nom court du site voulu en attribut de l'objet, on retournera l'ID
	     correspondante ***/

	public function retrieveSiteID($db) {

		try {
			if($db != null) {

				$query_id_entity = "SELECT site_id FROM sites WHERE site_short_name=:site";
				$sth = $db->prepare($query_id_entity, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':site' => $this->_shortName));
				$datas = $sth->fetchAll();

				if(count($datas) > 0) {

					foreach($datas as $data) {

						return $data['site_id'];		
					}
				}
				else
					echo "<font color=\"FE2E2E\">le site mentionné n'est pas présente dans la base</font>";
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
			require('error_log.php');
		}
	}
}

class SiteArray {

	public static $site_array = array(); // Création du tableau d'objet

	public static function addsite(site $site) { // Ajout d'un site au tableau des sites

		if(!isset(self::$site_array[$site->_id])) {

			self::$site_array[$site->_id] = $site;
		}
	}

	public static function setSiteData($id, $name, $shortName, $entityShortName) { // On ajoute les données aux attributs de l'objet

		$site = new site($id, $name, $shortName, $entityShortName); // création de l'objet et de ses attributs
		self::addsite($site); // et on ajoute l'objet au tableau
	}

	public static function showData() { // Affichage du tableau d'objet

		foreach(self::$site_array as $id => $site) {

			// Pour chaque ligne on affiche chaque attribut correspondant au colonne du tableau
			
			if($site->_entityShortName == null) // On vérifie l'état de l'entité à laquelle est rattaché le site pour un affichage correct dans le tableau (l'entité peut avoir été supprimé mais le site existe toujours)
				echo "<tr><td>null</td><td>" . $site->_name . "</td><td>" . $site->_shortName . "</td><td><a href=\"sites.php?action=modifier&id=" . $site->_id . "\">modifier</a></td><td><a href=\"sites.php?action=supprimer&id=" . $site->_id . "\">supprimer</a></td></tr>";			
			else
				echo "<tr><td>" . $site->_entityShortName . "</td><td>" . $site->_name . "</td><td>" . $site->_shortName . "</td><td><a href=\"sites.php?action=modifier&id=" . $site->_id . "\">modifier</a></td><td><a href=\"sites.php?action=supprimer&id=" . $site->_id . "\">supprimer</a></td></tr>";

			

		}
	}

	public static function showDataOneRow($id_site) {

		foreach(self::$site_array as $id => $site) {

			if($site->_id == $id_site) {

				echo "<tr><td>" . $site->_name . "</td><td>" . $site->_shortName . "</td><td><a href=\"site.php?action=modifier&id=" . $site->_id . "\">modifier</a></td><td><a href=\"site.php?action=supprimer&id=" . $site->_id . "\">supprimer</a></td></tr>";
			}
		}
	}
}

?>
