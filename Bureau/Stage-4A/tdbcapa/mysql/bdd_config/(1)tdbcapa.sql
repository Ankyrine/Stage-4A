-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2+deb7u1
-- http://www.phpmyadmin.net
--
-- Client: 10.0.0.12
-- Généré le: Mar 25 Août 2015 à 16:49
-- Version du serveur: 5.5.43
-- Version de PHP: 5.4.41-0+deb7u1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `[Votre nom de base de donnée]`
--

-- --------------------------------------------------------

--
-- Structure de la table `archives_rooms`
--

CREATE TABLE IF NOT EXISTS `archives_rooms` (
  `archives_room_id` int(11) NOT NULL AUTO_INCREMENT,
  `archives_room_entity_name` char(6) NOT NULL,
  `archives_room_site_name` char(6) NOT NULL,
  `archives_room_room_name` char(20) NOT NULL,
  `archives_room_surface_totale` int(11) DEFAULT NULL,
  `archives_room_surface_unusable` int(11) DEFAULT NULL,
  `archives_room_surface_baie` float DEFAULT NULL,
  `archives_room_nbr_total_baie` int(11) DEFAULT NULL,
  `archives_room_nbr_baies_possible` int(11) DEFAULT NULL,
  `archives_room_puissance_totale` int(11) DEFAULT NULL,
  `archives_room_puissance_utilise` int(11) DEFAULT NULL,
  `archives_room_puissance_moyenne_baie` float DEFAULT NULL,
  `archives_room_nbr_baie_installable` int(11) DEFAULT NULL,
  `archives_room_taux_moyen_remplissage` float DEFAULT NULL,
  `archives_room_comment` char(255) DEFAULT NULL,
  `archives_room_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`archives_room_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Structure de la table `baies`
--

CREATE TABLE IF NOT EXISTS `baies` (
  `baie_id` int(11) NOT NULL AUTO_INCREMENT,
  `baie_type` char(12) DEFAULT NULL,
  `depth` float DEFAULT NULL,
  `width` float DEFAULT NULL,
  `ground_area` float DEFAULT NULL,
  `base_model` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`baie_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Structure de la table `entity`
--

CREATE TABLE IF NOT EXISTS `entity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(100) DEFAULT NULL,
  `short_name` char(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Contenu de la table `entity`
--

INSERT INTO `entity` (`id`, `name`, `short_name`) VALUES
(1, '[votre entité administration]', '[nom court entité administration]');

-- --------------------------------------------------------

--
-- Structure de la table `entity_user`
--

CREATE TABLE IF NOT EXISTS `entity_user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_user_id` int(11) NOT NULL,
  `entity_user_firstname` char(20) DEFAULT NULL,
  `entity_user_lastname` char(20) DEFAULT NULL,
  `entity_user_email` char(255) DEFAULT NULL,
  `entity_user_password` char(255) DEFAULT NULL,
  `entity_user_auth` tinyint(1) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Contenu de la table `entity_user`
--

INSERT INTO `entity_user` (`user_id`, `entity_user_id`, `entity_user_firstname`, `entity_user_lastname`, `entity_user_email`, `entity_user_password`, `entity_user_auth`) VALUES
(1, 1, '[Votre Prénom]', '[Votre Nom]', '[Votre adresse e-mail]', '[votre mot de passe encrypté via sha1()]', 2);

-- --------------------------------------------------------

--
-- Structure de la table `rooms`
--

CREATE TABLE IF NOT EXISTS `rooms` (
  `room_id` int(11) NOT NULL AUTO_INCREMENT,
  `room_entity_id` int(11) DEFAULT NULL,
  `room_entity_site_id` int(11) DEFAULT NULL,
  `room_name` char(255) DEFAULT NULL,
  `room_total_area` int(11) DEFAULT '0',
  `room_unusable_area` int(11) DEFAULT '0',
  `room_baie_area` float DEFAULT '0',
  `nbr_baies_possible` int(11) DEFAULT '0',
  `nbr_baie_100` int(11) DEFAULT '0',
  `nbr_baie_75` int(11) DEFAULT '0',
  `nbr_baie_50` int(11) DEFAULT '0',
  `nbr_baie_25` int(11) DEFAULT '0',
  `nbr_baie_0` int(11) DEFAULT '0',
  `taux_moyen_remplissage` float DEFAULT '0',
  `nbr_total_baie` int(11) DEFAULT '0',
  `nbr_baies_installable` int(11) DEFAULT '0',
  `room_usable_power` int(11) DEFAULT '0',
  `room_used_power` int(11) DEFAULT '0',
  `room_baie_mean_power` float DEFAULT '0',
  `room_comment` varchar(255) DEFAULT NULL,
  `room_last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`room_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Structure de la table `salle_possede`
--

CREATE TABLE IF NOT EXISTS `salle_possede` (
  `room_id` int(11) NOT NULL,
  `baie_id` int(11) NOT NULL,
  `nbr_baie` int(11) DEFAULT NULL,
  PRIMARY KEY (`room_id`,`baie_id`),
  KEY `FK_salle_possede_baie_id` (`baie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Structure de la table `sites`
--

CREATE TABLE IF NOT EXISTS `sites` (
  `site_id` int(11) NOT NULL AUTO_INCREMENT,
  `site_entity_id` int(11) NOT NULL,
  `site_name` char(255) DEFAULT NULL,
  `site_short_name` char(20) DEFAULT NULL,
  PRIMARY KEY (`site_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
