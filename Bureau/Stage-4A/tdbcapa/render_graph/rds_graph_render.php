<?php
	try {

		if($db != null) {

			if($consult_date == "Donnée actuelle") {

				$query = "SELECT SUM(room_total_area) AS room_total_area, SUM(room_unusable_area) AS room_unusable_area, SUM(room_baie_area) AS room_baie_area FROM rooms";
				$sth = $db->prepare($query);
				$sth->execute();
				$datas = $sth->fetchAll();
			}
			else {

				$query = "SELECT SUM(archives_room_surface_totale) AS room_total_area, SUM(archives_room_surface_unusable) AS room_unusable_area, SUM(archives_room_surface_baie) AS room_baie_area FROM archives_rooms WHERE archives_room_date LIKE :consult_date";
				$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':consult_date' => ($consult_date . "%")));
				$datas = $sth->fetchAll();
			}

			if(count($datas) > 0) {

				foreach($datas as $data) {

					$value1 = $data['room_total_area'] - $data['room_unusable_area'];
					$value2 = floor($data['room_baie_area']);
					$value3 = floor($value1 - $value2);				}

				/* Create and populate the pData object */
				$MyData = new pData();
				$MyData->addPoints(array($value1, $value2, $value3), "ScoreA");
				$MyData->setSerieDescription("ScoreA","Application A");

				$MyData->addPoints(array("Surface utile totale ($value1)", "Dont surface utilise ($value2)", "Dont surface disponible ($value3)"),"Labels"); 
				$MyData->setAbscissa("Labels");

				/* Create the pChart object */ 
				$myPicture = new pImage(700, 330, $MyData); // Taille dynamique de l'image en fonction du nombre de réponse SQL

				/* Draw the background */				
				$Settings = array("R"=>39, "G"=>43, "B"=>48, "Dash"=>1, "DashR"=>122, "DashG"=>130, "DashB"=>136); 
				$myPicture->drawFilledRectangle(0, 0, 700, 330, $Settings); 

				/* Overlay with a gradient */
				$Settings = array("StartR"=>39, "StartG"=>43, "StartB"=>48, "EndR"=>122, "EndG"=>130, "EndB"=>136, "Alpha"=>50); 
				$myPicture->drawGradientArea(0, 0, 700, 330, DIRECTION_VERTICAL, $Settings); 
				$myPicture->drawGradientArea(0, 0, 700, 20, DIRECTION_VERTICAL, array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>80));

				/* Add a border to the picture */
				$myPicture->drawRectangle(0, 0, 699, 329, array("R"=>0, "G"=>0, "B"=>0)); 

				/* Write the chart title */  
				$myPicture->setFontProperties(array("FontName"=>"pChart/fonts/Silkscreen.ttf", "FontSize"=>6,"R"=>255,"G"=>255,"B"=>255)); 
				$myPicture->drawText(10, 13, "Repartition des surfaces (m carre)", array("R"=>255, "G"=>255, "B"=>255)); 

				/* Define the default font */  
				$myPicture->setFontProperties(array("FontName"=>"pChart/fonts/verdana.ttf", "FontSize"=>10)); 
				$myPicture->setShadow(TRUE, array("X"=>1, "Y"=>1, "R"=>0, "G"=>0, "B"=>0, "Alpha"=>20));

				/* Draw the Pie Chart */
				$PieChart = new pPie($myPicture, $MyData);
				$PieChart->draw2DPie(350, 165, array("DrawLabels"=>TRUE,"LabelStacked"=>TRUE,"Border"=>TRUE));

				if($consult_date != "Donnée actuelle")
					$myPicture->Render("images/rds_graph_render_$consult_date.png");
				else
					$myPicture->Render("images/rds_graph_render.png");
			}
			else 
				$error = 1;
		}
	}
	catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
		require('error_log.php');
	}
?>
