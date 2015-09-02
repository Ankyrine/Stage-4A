<?php

/******************************************************/
/* Classe de définition d'une baie dans l'application */
/******************************************************/
/* Fonction disponible :
- newBaie : création d'une baie en BDD (tout les attributs sont necessaire lors de la création de l'objet au préalable)
- dropBaie : suppression d'une baie en BDD (uniquement l'attribut "_id" est necessaire pour la réalisation de cette fonction)
- showModif : affichage d'un formulaire de modification d'une baie, avec valeur actuelle par défaut (tout les attributs sont necessaire lors de la création de l'objet au préalable)
- modifBaie : modification d'une baie en BDD, avec les données du formulaire précédent (tout les attributs sont necessaire lors de la création de l'objet au préalable) */

/****************************/
/* Tableau d'objet "Baie" */
/****************************/
/* Fonction disponible :
- addBaie : ajoute un objet "Baie" au tableau d'entité
- setBaieData : ajoute les valeurs des paramètres aux attributs de l'objet
- showData : Affiche le contenu du tableau d'objet "Baie" sous forme de ligne d'un tableau */

class Baie {

	/*** Attributs de l'objet Baie ***/

	public $_id;
	public $_typeName;
	public $_width;
	public $_depth;
	public $_groundArea;
	public $_baseModel;

	/*** Fonction de construction de l'objet de type "Baie" ***/

	public function __construct($id, $typeName, $width, $depth, $groundArea, $baseModel) {

		$this->_id = $id;
		$this->_typeName = $typeName;
		$this->_width = $width;
		$this->_depth = $depth;
		$this->_groundArea = $groundArea;
		$this->_baseModel = $baseModel;
	}

	/*** Création d'une nouvelle baie en BDD, objet crée et attribut ajouté au préalable ***/

	public function newBaie($db) {

		try {
			if($db != null) {

				$query = "SELECT baie_id FROM baies WHERE baie_type=:type_baie"; // On vérifie que le type de baie n'est pas déjà présente
				$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)); // On prépare la requête
				$sth->execute(array(':type_baie' => $this->_typeName)); // Injection des données dans la requête pour éviter les injections SQL

				$datas = $sth->fetchAll();

				if(count($datas) == 0) { // Si la requête ne retourne aucun résultat, on peut lancer la création de la baie.

					$query = "INSERT INTO baies (baie_type, width, depth, ground_area, base_model) VALUES (:type_baie, :width, :depth, :groundArea, :base_model)"; // Insertion dans la BDD de la nouvelle baie et de ses caractéristiques.
					$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$sth->execute(array(':type_baie' => $this->_typeName, ':width' => $this->_width, ':depth' => $this->_depth, ':groundArea' => $this->_groundArea, ':base_model' => $this->_base_model));
					header("Location: baies.php"); // redirection vers la page des baies.
				}
				else // Sinon ce modèle existe déjà
					echo "<font color=\"FE2E2E\">Ce nom de modèle existe déjà</font>";
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
			require('error_log.php');
		}
	}

	/*** Suppression d'une baie en BDD, objet crée et attribut ajouté au préalable (ID requis uniquement) ***/

	public function dropBaie($db) {

		try {
			if($db != null) {

				$query = "DELETE FROM baies WHERE baie_id=:id";
				$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':id' => $this->_id));

				header("Location: baies.php");
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
			require('error_log.php');
		}
	}

	/*** Affichage du formulaire de modification d'une baie, avec les informations actuelles de la baie, objet crée et attribut ajouté au préalable ***/
	
	public function showModif() {

		// Affichage des valeures actuelles présente en BDD par défaut dans le formulaire

		echo"<div id=\"body_content\">
			<div class=\"body\">
			<p> Modification de la baie : " . $this->_typeName . "</p>
			<form action=\"baies.php?modif_id=" . $this->_id . "\" method=\"POST\">
				<label>Type de la Baie : </label><input name=\"type_baie\" type=\"text\" class=\"type_baie\" placeholder=\"Type de Baie\" value=\"" . $this->_typeName . "\" required=\"required\" title=\"Type de Baie\"><br />
				<label>Largeur de la baie : </label><input name=\"width\" type=\"text\" class=\"width\" pattern=\"[0-9]+[.]{1}[0-9]+\" placeholder=\"Largeur\" value=\"" . $this->_width . "\" required=\"required\" title=\"Largeur de la Baie : Utiliser un point et non une virgule, rajouter .0 dans le cas d'un nombre entier\"><br />
				<label>Profondeur de la baie : </label><input name=\"depth\" type=\"text\" class=\"depth\" pattern=\"[0-9]+[.]{1}[0-9]+\" placeholder=\"Profondeur\" value=\"" . $this->_depth . "\" required=\"required\" title=\"Profondeur de la Baie : Utiliser un point et non une virgule, rajouter .0 pour un nombre entier\"><br />
				<label>Surface au sol de la baie : </label><input name=\"groundArea\" type=\"text\" class=\"groundArea\" pattern=\"[0-9]+[.]{1}[0-9]+\" placeholder=\"Surface au Sol\" value=\"" . $this->_groundArea . "\" required=\"required\" title=\"Surface au sol de la Baie : Utiliser un point et non une virgule, rajouter .0 dans le cas d'un nombre entier\"><br /><br />
				Surface de base retenue pour de nouvelles baies ? :<br />
				<input type=\"radio\" name=\"default\" value=\"1\""; if ($this->_baseModel == 1) echo "checked";

		echo ">Oui<input type=\"radio\" name=\"default\" value=\"0\""; if ($this->_baseModel == 0) echo "checked";
		echo ">Non<br /><input type=\"submit\" value=\"Créer\">
		</form>

		-><a href=\"baies.php\"> retour</a>
		</div>
		</div>";
	}

	/*** Modification d'une baie en BDD, objet crée et attribut ajouté au préalable ***/

	public function modifBaie($db) {

		try {
			if($db != null) {

				$query = "SELECT baie_id FROM baies WHERE baie_type=:type_baie AND baie_id!=:id"; // On vérifie que le type de baie n'existe pas déjà (hormis en dehors de l'id de la baie à modifier)
				$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':type_baie' => $this->_typeName, ':id' => $this->_id));

				$datas = $sth->fetchAll();

				if(count($datas) == 0) { // Si la requête ne retourne aucun résultat

					$query = "UPDATE baies SET baie_type=:type_baie, width=:width, depth=:depth, ground_area=:groundArea, base_model=:base_model WHERE baie_id=:id"; // On modifie les données de la ligne en base de donnée.
					$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$sth->execute(array(':type_baie' => $this->_typeName, ':width' => $this->_width, ':depth' => $this->_depth, ':groundArea' => $this->_groundArea, ':base_model' => $this->_baseModel, ':id' => $this->_id));
					header("Location: baies.php");
				}
				else // Sinon ce type de baie existe déjà
					echo "<font color=\"FE2E2E\">Un modèle de baie utilise déjà ce nom</font>";
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
			require('error_log.php');
		}
	}
}

class BaieArray {

	public static $baie_array = array(); // Création du tableau d'objet

	public static function addBaie(Baie $baie) { // Ajout d'une baie au tableau de baie

		if(!isset(self::$baie_array[$baie->_id])) {

			self::$baie_array[$baie->_id] = $baie;
		}
	}

	public static function setBaieData($id, $typeName, $width, $depth, $groundArea, $baseModel) { // On ajoute les données aux attributs de l'objet

		$baie = new Baie($id, $typeName, $width, $depth, $groundArea, $baseModel); // création de l'objet et de ses attributs
		self::addBaie($baie); // et on ajoute l'objet au tableau
	}

	public static function showData() { // Affichage du tableau d'objet

		foreach(self::$baie_array as $id => $baie) {

			// Pour chaque ligne on affiche chaque attribut correspondant au colonne du tableau

			echo "<tr><td>" . $baie->_typeName . "</td><td>" . $baie->_width . "</td><td>" . $baie->_depth . "</td><td>" . $baie->_groundArea . "</td>";
		
			if($baie->_baseModel == 1) // Affichage conditionnel sur le fait qu'une baie soit un modèle de base pour de future installation
				echo "<td> Oui </td>";
			else
				echo "<td> Non </td>";

			// Affichage des options de modification et de suppression des baies en passant l'ID (en base de donnée) de la baie grâce à la méthode GET
		
			echo "<td><a href=\"baies.php?action=modifier&id=" . $baie->_id . "\">modifier</a></td><td><a href=\"baies.php?action=supprimer&id=" . $baie->_id . "\">supprimer</a></td></tr>";

		}
	}
}

?>
