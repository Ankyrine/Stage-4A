<div class="footer">
	<div class="bdd_status">
		<?php if ($db == null) echo "<img src=\"images/disconnected.png\">"; else echo "<img src=\"images/connect.png\">"; // Affichage du status de la connexion mysql lors de l'appel aux pages php. Permet un debuggage des erreurs par ressentis/indice utilisateur (en plus des logs) ?>
	</div>
	<div class="container">
		<p>Copyrights &copy; 2015 MGMSIC - Projet de stage 4A</p>
	</div>

	<?php $db=null; // fermeture de la connexion à la BDD ?>
</div>
