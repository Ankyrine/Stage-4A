<?php
	try {

		if($db != null) {

			if($consult_date == "Donnée actuelle") {

				$query = "SELECT room_baie_mean_power, room_name FROM rooms";
				$sth = $db->prepare($query);
				$sth->execute();
				$datas = $sth->fetchAll();
			}
			else {

				$query = "SELECT archives_room_puissance_moyenne_baie AS room_baie_mean_power, archives_room_room_name AS room_name FROM archives_rooms WHERE archives_room_date LIKE :consult_date";
				$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':consult_date' => ($consult_date . "%")));
				$datas = $sth->fetchAll();
			}

			if(count($datas) > 0) {

				$room_value = array();
				$room_name = array();
				$room_objectif = array();
				$nbr_room = count($datas);
				$room_objectif_mean_power = 3.5;

				foreach($datas as $data) {

					$room_value[] = $data['room_baie_mean_power'];
					$room_name[] = $data['room_name'];
					$room_objectif[] = $room_objectif_mean_power;
				}    

				/* Create and populate the pData object */ 
				$MyData = new pData();   
				$MyData->addPoints($room_value, "Consomation moyenne des baies (kW)"); 
				$MyData->setAxisName(0, "Consomation moyenne des baies (kW)");
				$MyData->addPoints($room_name, "Salles"); 
				$MyData->setAbscissa("Salles");
				$MyData->addPoints($room_objectif, "Objectif");

				/* Create the pChart object */ 
				$myPicture = new pImage(((65 * $nbr_room) + 65), 330, $MyData); // Taille dynamique de l'image en fonction du nombre de réponse SQL

				/* Draw the background */				
				$Settings = array("R"=>39, "G"=>43, "B"=>48, "Dash"=>1, "DashR"=>122, "DashG"=>130, "DashB"=>136); 
				$myPicture->drawFilledRectangle(0, 0, ((65 * $nbr_room) + 65), 330, $Settings); 

				/* Overlay with a gradient */
				$Settings = array("StartR"=>39, "StartG"=>43, "StartB"=>48, "EndR"=>122, "EndG"=>130, "EndB"=>136, "Alpha"=>50); 
				$myPicture->drawGradientArea(0, 0, ((65 * $nbr_room) + 65), 330, DIRECTION_VERTICAL, $Settings); 
				$myPicture->drawGradientArea(0, 0, ((65 * $nbr_room) + 65), 20, DIRECTION_VERTICAL, array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>80));

				/* Add a border to the picture */
				$myPicture->drawRectangle(0, 0, ((65 * $nbr_room) + 64), 329, array("R"=>0, "G"=>0, "B"=>0)); 

				/* Write the chart title */  
				$myPicture->setFontProperties(array("FontName"=>"pChart/fonts/Silkscreen.ttf", "FontSize"=>6,"R"=>255,"G"=>255,"B"=>255)); 
				$myPicture->drawText(10, 13, "Consomation moyenne des baies avec objectif a 3,5 kW", array("R"=>255, "G"=>255, "B"=>255)); 

				/* Define the default font */  
				$myPicture->setFontProperties(array("FontName"=>"pChart/fonts/verdana.ttf", "FontSize"=>10)); 
				$myPicture->setShadow(TRUE, array("X"=>1, "Y"=>1, "R"=>0, "G"=>0, "B"=>0, "Alpha"=>20));

				/* Set the graph area */  
				$myPicture->setGraphArea(50, 40, ((65 * $nbr_room) + 35), 290); 
				$myPicture->drawGradientArea(50, 40, ((65 * $nbr_room) + 35), 290, DIRECTION_VERTICAL, array("StartR"=>200,"StartG"=>200,"StartB"=>200,"EndR"=>255,"EndG"=>255,"EndB"=>255,"Alpha"=>30)); 

				/* Draw the chart scale */  
				$scaleSettings = array("AxisAlpha"=>10,"TickAlpha"=>10,"DrawXLines"=>FALSE,"Mode"=>SCALE_MODE_START0,"GridR"=>0,"GridG"=>0,"GridB"=>0,"GridAlpha"=>10);
				$myPicture->drawScale($scaleSettings);

				/* Draw the chart */
				$MyData->setSerieDrawable("Objectif", FALSE);
				$myPicture->drawBarChart(array("DisplayValues"=>TRUE, "DisplayPos"=>LABEL_POS_INSIDE, "Surrounding"=>30));

				$MyData->setSerieDrawable("Objectif", TRUE);
				$MyData->setSerieDrawable("Consomation moyenne des baies (kW)", FALSE); 
 				$MyData->setSerieDrawable("Salles", FALSE);
				$serieSettings = array("R"=>229,"G"=>11,"B"=>11);
 				$MyData->setPalette("Objectif", $serieSettings);
				$myPicture->setShadow(TRUE, array("X"=>2,"Y"=>2,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10)); 
 				$myPicture->drawSplineChart();

				if($consult_date != "Donnée actuelle")
					$myPicture->Render("images/consoe_graph_render_$consult_date.png");
				else
					$myPicture->Render("images/consoe_graph_render.png");
			}
			else
				$error = 1;
		}
	}
	catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
		require('error_log.php');
	}
?>
