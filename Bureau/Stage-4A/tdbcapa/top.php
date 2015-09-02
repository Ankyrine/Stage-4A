<div id="log_information">
	<div class="top_information">
		<center><h2>Tableau de bord : Capacité Data-Centers</h2></center>
		<?php 
			if($user->isConnected()) { // Si l'utilisateur est connecté

				$user->showData(); // On montre le bandeau de connection
				$user->showPossibleAction(); // Les actions possibles en fonction du niveau d'autorisation de l'utilisateur
				echo "<div id=\"body_content\">
					<div class=\"body\">
						<a href=\"raw_data.php\">Données brutes</a>
						<a href=\"data.php\">Données consolidées</a>
						<a href=\"histographe.php\">Graphes et Histogrammes</a>
					</div>
				</div>";
			}
			else {// Si l'utilisateur n'est pas connecté
				echo "<p> Invité </p>"; // L'utilisateur est visiteur
				echo "<div id=\"body_content\">
					<div class=\"body\">
						<a href=\"data.php\">Données consolidées</a>
						<a href=\"histographe.php\">Graphes et Histogrammes</a>
					</div>
				</div>";
			}
		?>
	</div>
</div>
