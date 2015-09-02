<?php
	try {

		if($db != null) {

			if($consult_date == "Donnée actuelle") {

				$query = "SELECT taux_moyen_remplissage, room_name FROM rooms";
				$sth = $db->prepare($query);
				$sth->execute();
				$datas = $sth->fetchAll();
			}
			else {

				$query = "SELECT archives_room_taux_moyen_remplissage AS taux_moyen_remplissage, archives_room_room_name AS room_name FROM archives_rooms WHERE archives_room_date LIKE :consult_date";
				$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':consult_date' => ($consult_date . "%")));
				$datas = $sth->fetchAll();
			}

			if(count($datas) > 0) {

				$nbr_room = count($datas);

				/* Create the pChart object */ 
				$myPicture = new pImage(700, (($nbr_room * 30) + 30)); // Taille dynamique de l'image en fonction du nombre de réponse SQL

				/* Draw the background */ 
				$Settings = array("R"=>39, "G"=>43, "B"=>48, "Dash"=>1, "DashR"=>122, "DashG"=>130, "DashB"=>136); 
				$myPicture->drawFilledRectangle(0, 0, 700, (($nbr_room * 30) + 30), $Settings); 

				/* Overlay with a gradient */ 
				$Settings = array("StartR"=>39, "StartG"=>43, "StartB"=>48, "EndR"=>122, "EndG"=>130, "EndB"=>136, "Alpha"=>50); 
				$myPicture->drawGradientArea(0, 0, 700, (($nbr_room * 30) + 30), DIRECTION_VERTICAL, $Settings); 
				$myPicture->drawGradientArea(0, 0, 700, 20, DIRECTION_VERTICAL, array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>80)); 

				/* Add a border to the picture */
				$myPicture->drawRectangle(0, 0, 699, (($nbr_room * 30) + 29), array("R"=>0, "G"=>0, "B"=>0)); 
								  
				/* Write the picture title */  
				$myPicture->setFontProperties(array("FontName"=>"pChart/fonts/Silkscreen.ttf", "FontSize"=>6)); 
				$myPicture->drawText(10, 13, "Taux moyen de remplissage des baies", array("R"=>255, "G"=>255, "B"=>255)); 

				/* Set the font & shadow options */  
				$myPicture->setFontProperties(array("FontName"=>"pChart/fonts/verdana.ttf", "FontSize"=>10)); 
				$myPicture->setShadow(TRUE,array("X"=>1, "Y"=>1, "R"=>0, "G"=>0, "B"=>0, "Alpha"=>20));

				$i = 30;

				foreach($datas as $data) {

					$taux_moyen = $data['taux_moyen_remplissage'] * 100;

					/* Draw a progress bar */  
					$progressOptions = array("Width"=>500, "R"=>134, "G"=>209, "B"=>27, "Surrounding"=>20, "BoxBorderR"=>0, "BoxBorderG"=>0, "BoxBorderB"=>0, "BoxBackR"=>255, "BoxBackG"=>255, "BoxBackB"=>255, "RFade"=>206, "GFade"=>133, "BFade"=>30, "ShowLabel"=>TRUE, "LabelPos"=>LABEL_POS_CENTER); 
					$myPicture->drawProgress(160, $i, $taux_moyen, $progressOptions);
					$myPicture->drawText(50, $i + 13, $data['room_name'], array("R"=>255,"G"=>255,"B"=>255)); 

					$i = $i + 30;
				}

				if($consult_date != "Donnée actuelle")
					$myPicture->Render("images/tr_graph_render_$consult_date.png");
				else
					$myPicture->Render("images/tr_graph_render.png");
			}
			else
				$error = 1;
		}
	}
	catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
		require('error_log.php');
	}
?>
