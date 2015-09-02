/*************** Prelude du bon fonctionnement de l'application ****************/

Pour que les scripts PHP fonctionnent correctement, il faut configurer les dossier "error_log", "user_log" et "images".

sudo chmod 777 user_log error_log images

Les scripts pourront donc ecrire correctement dans ces dossiers.

Mettre le contenu du dossier image en droit de lecture et ecriture :

sudo chmod 777 images/*.png

/!\ NE PAS OUBLIER QUE LES FICHIERS DE LOGS PEUVENT ETRE REMPLACE LORS DU TRANSFERT DES SOURCES DU SITE /!\

Commande pour mettre en place le CRON :

crontab -e

puiecrire en derniere ligne :

0 0 1 * * php -f ./sites/archivages/archivage.php

/*******************************************************************************/

/*********************** Fichiers et dossiers à part **************************/

/archivages/archivage.php

Est appellé par un CRON programmé pour lance le script PHP sur le serveur le 1er de tout les mois à minuit.
Permet l'archivages du contenu des tables `rooms` et `salle_possede` dans la table `archives_rooms`
Dossier protégé par un index.php redirigant le visiteur vers la page d'index de l'application

/error_log/

Dossier contenant les logs relatifs aux erreurs liés à mysql, un fichier est crée chaque jour pour une meilleure visibilité. Ces logs sont consultable par l'administrateur sur la page error_logs.php
Dossier protégé par un index.php redirigant le visiteur vers la page d'index de l'application

/mysql/mysql_connect.php

Permet la création de l'objet PDO d'accès à la BDD, la variable pointant vers l'objet sera transmise à toute les pages et mise à null en fin de page (fin de génération du HTML)
Dossier protégé par un index.php redirigant le visiteur vers la page d'index de l'application

/user_log/

Dossier contenant les logs relatifs aux actions des employés des entités de gestion des sites/salles. Un nouveau fichier est crée chaque jour pour une meilleur visibilité. Ces logs sont consultable par l'administrateur sur la page user_logs.php
Dossier protégé par un index.php redirigant le visiteur vers la page d'index de l'application

/render_graph/

Dossier contenant les 6 scripts PHP utilisant la librairie pChart pour la génération des images .png pour l'affichage des données sous forme de graphe

/top.php

Contient l'appel au fonction de la classe user pour affichage des informations user et des actions possibles

/footer.php

Affichage de pied de page et de l'indice de connexion (ou non) à la base de donnée lorsque la page a été générée.

/index.php

Index du site, affiche le formulaire de connexion si l'utilisateur n'est pas authentifié, ou les actions possible par l'utilisateur.

/login.php

Contient le formulaire de connexion à l'application

/logout.php

Contient la procédure d'expiration du cookies pour "déconnecter" l'utilisateur

/session.php

Contient la procédure de lecture de cookies et de vérification des informations en BDD pour authentification, création de l'objet user pour la connexion.

/connected.php

Gère le formulaire de login pour création du cookies pour établir la connexion à l'application

/error_404.php et .htacess

Permet la redirection de l'erreur 404 vers une page personnalisées

/******************************************************************************/

/************************ Fichiers d'administration ***************************/

/*** Sur les baies ***/

/baies.php

Affiche par défaut la liste des baies présente en BDD avec un lien pour la modification ou la suppression de la baie à la ligne selectionnée.
En bas du tableau on trouve un lien pour créer une nouvelle baie en BDD.
Si la variable "action" est renseignée dans l'url par une action reconnue elle est traitée, et envoyé vers class.baies.php pour action.

/new_baie.php

Affichage du formulaire pour la création d'une nouvelle baie (/!\ L'application affichera un message warning à l'administrateur si il existe plus d'un ou aucun type de baie par défaut)
Traitement du formulaire en envoi vers class.baies.php pour action

/class.baies.php

Fichier de classe de l'objet "baie", il contient toutes les fonctions permettant la gestion des baies (consulter le fichier en question pour la description des fonction)

/*********************/

/*** Sur les Employés ***/

/user.php

Affiche par défaut la liste des employés présent en BDD avec un lien pour la modification ou la suppression de la baie à la ligne selectionnée.
En bas du tableau on trouve un lien pour créer un nouvel employé en BDD.
Si la variable "action" est renseignée dans l'url par une action reconnue elle est traitée, et envoyé vers class.employee.php pour action

/new_user.php

Affichage du formulaire pour la création d'un nouvel employé (vérification sur le login (e-mail) pour vérifier l'unicité de l'employé en BDD.
Traitement du formulaire en envoi vers class.employee.php pour action

/class.employee.php

Fichier de classe de l'objet "employe", il contient toutes les fonctions permettant la gestion des employés (consulter le fichier en question pour la description des fonction)

/************************/

/*** Sur les Entités ***/

/entity.php

Affiche par défaut la liste des entités présent en BDD avec un lien pour la modification ou la suppression de la baie à la ligne selectionnée.
En bas du tableau on trouve un lien pour créer une nouvelle entité en BDD.
Si la variable "action" est renseignée dans l'url par une action reconnue elle est traitée, et envoyé vers class.entity.php pour action

/new_entity.php

Affichage du formulaire pour la création d'une nouvelle entité (vérification sur le nom court pour vérifier l'unicité de l'entité en BDD.
Traitement du formulaire en envoi vers class.entity.php pour action

/class.entity.php

Fichier de classe de l'objet "entity", il contient toutes les fonctions permettant la gestion des employés (consulter le fichier en question pour la description des fonction)

/***********************/

/*** Sur les Salles ***/

/rooms.php

Affiche par défaut la liste des salles présent en BDD avec un lien pour la modification ou la suppression de la salle à la ligne selectionnée.
En bas du tableau on trouve un lien pour créer une nouvelle entité en BDD.
Si la variable "action" est renseignée dans l'url par une action reconnue elle est traitée, et envoyé vers class.rooms.php pour action

/new_room.php

Affichage du formulaire pour la création d'une nouvelle salle (vérification sur le nom pour vérifier l'unicité de la salle en BDD.
Traitement du formulaire en envoi vers class.rooms.php pour action

/class.rooms.php

Fichier de classe de l'objet "room", il contient toutes les fonctions permettant la gestion des salles (consulter le fichier en question pour la description des fonction)

/***********************/

/*** Sur les Sites ***/

/sites.php

Affiche par défaut la liste des sites présent en BDD avec un lien pour la modification ou la suppression du site à la ligne selectionnée.
En bas du tableau on trouve un lien pour créer un nouveau site en BDD.
Si la variable "action" est renseignée dans l'url par une action reconnue elle est traitée, et envoyé vers class.sites.php pour action

/new_sites.php

Affichage du formulaire pour la création d'un nouveau site (vérification sur le nom court pour vérifier l'unicité du site en BDD.
Traitement du formulaire en envoi vers class.sites.php pour action

/class.sites.php

Fichier de classe de l'objet "site", il contient toutes les fonctions permettant la gestion des sites (consulter le fichier en question pour la description des fonction)

/*********************/

/*** Sur les utilisateurs ***/

/class.user.php

Fichier de classe de l'objet "user", il contient toutes les fonctions permettant la gestion des utilisateurs de l'application et du système d'authentification/connexion à l'application (consulter le fichier en question pour la description des fonction)

/****************************/

/*** Divers ***/

/error_log.php

Procédure d'ecriture des erreurs mysql catch dans le fichier de log nommé à la date du jour dans le dossier /error_log/

/error_logs.php

Liste les fichiers de logs présent dans le dossier /error_log/ puis affichage du fichier de log selectionné

/user_log_room.php / user_log_salle_possede.php

Procédure d'écriture des logs utilisateurs (lors de la modification d'une salle) dans un fichier nommé à la date du jour dans le dossier /user_log/

/user_logs.php

Liste les fichiers de logs présent dans le dossier /user_log/ puis affichage du fichier de log selectionné

/mdp.php

Permet de récupérer le hash d'une mot de passe par la fonction SHA1()

/edit_mdp.php

Comprend le formulaire de changement de mot de passe et le traitement de ce formulaire et la modification du mot de passe en BDD si les conditions sont remplies.

/**************/

/******************************************************************************/

/*************************** Fichiers de gestion ******************************/

/manage_rooms.php

Affiche les salles modifiables par l'utilisateur, après choix de l'utilisateur, affichage du formulaire de modification par le fichier "class.rooms.php" (après vérification que l'utilisateur soit bien autorisé à modifier cette salle.
Calcul des valeurs nécessaire pour stockage en BDD puis envoi des requêtes en BDD et écriture des requêtes (et des informations de l'utilisateur) dans les logs utilisateurs pour suivis.

/*****************************************************************************/

/************************* Fichiers de consultation **************************/

/data.php

Tableau de consolidation des données
Récupère les informations sur les salles en BDD, créer les objets "room" puis appels aux fonctions de class.rooms.php pour affichage des données sous forme de tableau.
Il est également demandé avant affichage si l'utilisateur veut consulter les données "live" (actuelle) ou les données des archives sur les mois précédents.

/raw_data.php

Accessible uniquement aux employés et aux administrateurs.
Récupère les informations sur les salles en BDD et créer les objets "room" (que l'entité de l'employé possède, ou toutes les salles pour l'administrateur), appels aux fonctions de class.rooms.php pour affichage des données (après vérification que les salles demandées sont bien possedé par l'entité de l'employé, sans vérification pour les administrateurs)

/histographes.php

Graphiques des données présente en base de donnée
On récupère les données en BDD sur chacun des scripts (6 au total, un par image), puis génération de l'image puis rendu et sauvegarde des images dans le dossier /images/ à la racine du site sous le format .png
Il est également demandé avant affichage si l'utilisateur veut consulter les données "live" (actuelle) ou les données des archives sur les mois précédents.

/*****************************************************************************/
