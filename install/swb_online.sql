-- phpMyAdmin SQL Dump
-- version 4.6.0
-- http://www.phpmyadmin.net
--
-- Client :  192.168.2.42
-- Généré le :  Jeu 21 Juillet 2016 à 17:15
-- Version du serveur :  10.1.16-MariaDB-1~jessie
-- Version de PHP :  7.0.8-1~dotdeb+8.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `swb_online_b`
--

-- --------------------------------------------------------

--
-- Structure de la table `Contacts`
--

CREATE TABLE `Contacts` (
  `user_id` int(13) NOT NULL,
  `address` varchar(30) NOT NULL,
  `format_address` varchar(30) NOT NULL,
  `name` varchar(150) NOT NULL,
  `have_img` tinyint(1) NOT NULL,
  `image` blob NOT NULL,
  `last_message` int(20) NOT NULL,
  `device_id` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `Devices`
--

CREATE TABLE `Devices` (
  `user_id` int(13) NOT NULL,
  `device_id` varchar(50) NOT NULL,
  `resync_date` int(20) NOT NULL,
  `last_sync` int(20) NOT NULL,
  `model` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `Logs`
--

CREATE TABLE `Logs` (
  `id` int(13) NOT NULL,
  `time` int(13) NOT NULL,
  `date` varchar(20) NOT NULL,
  `function` varchar(50) NOT NULL,
  `json` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `Messages`
--

CREATE TABLE `Messages` (
  `user_id` int(13) NOT NULL,
  `message_id` varchar(50) NOT NULL,
  `mess_type` varchar(3) NOT NULL,
  `date_message` bigint(20) NOT NULL,
  `date_sync` int(13) NOT NULL,
  `date_sent` int(20) NOT NULL,
  `unread` tinyint(4) NOT NULL,
  `address` varchar(50) NOT NULL,
  `type` varchar(2) NOT NULL,
  `body` text NOT NULL,
  `device_id` varchar(50) NOT NULL,
  `format_address` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `Parts`
--

CREATE TABLE `Parts` (
  `user_id` int(13) NOT NULL,
  `device_id` varchar(50) NOT NULL,
  `message_id` varchar(50) NOT NULL,
  `part_nb` int(2) NOT NULL,
  `data_type` varchar(25) NOT NULL,
  `data` longblob
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `Tokens`
--

CREATE TABLE `Tokens` (
  `user_id` int(13) NOT NULL,
  `token` varchar(250) NOT NULL,
  `type` varchar(32) NOT NULL,
  `expire_date` int(13) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `Users`
--

CREATE TABLE `Users` (
  `id` int(13) NOT NULL,
  `email` varchar(128) NOT NULL,
  `password` varchar(128) NOT NULL,
  `creation_date` int(13) NOT NULL,
  `connexion_date` int(13) NOT NULL,
  `premium_expire` int(13) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Index pour les tables exportées
--

--
-- Index pour la table `Contacts`
--
ALTER TABLE `Contacts`
  ADD PRIMARY KEY (`user_id`,`format_address`,`device_id`) USING BTREE,
  ADD KEY `user_id_2` (`user_id`,`device_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `Devices`
--
ALTER TABLE `Devices`
  ADD PRIMARY KEY (`user_id`,`device_id`) USING BTREE,
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `Logs`
--
ALTER TABLE `Logs`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `Messages`
--
ALTER TABLE `Messages`
  ADD PRIMARY KEY (`user_id`,`message_id`,`device_id`,`mess_type`) USING BTREE,
  ADD KEY `date_sync` (`date_sync`),
  ADD KEY `user_id` (`user_id`,`device_id`),
  ADD KEY `user_id_2` (`user_id`,`device_id`,`type`),
  ADD KEY `date_sent` (`date_sent`),
  ADD KEY `user_id_3` (`user_id`,`format_address`,`device_id`);

--
-- Index pour la table `Parts`
--
ALTER TABLE `Parts`
  ADD PRIMARY KEY (`user_id`,`device_id`,`message_id`,`part_nb`);

--
-- Index pour la table `Tokens`
--
ALTER TABLE `Tokens`
  ADD PRIMARY KEY (`user_id`,`token`) USING BTREE,
  ADD UNIQUE KEY `token` (`token`);

--
-- Index pour la table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_2` (`email`) USING BTREE,
  ADD KEY `email` (`email`,`password`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `Logs`
--
ALTER TABLE `Logs`
  MODIFY `id` int(13) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46300;
--
-- AUTO_INCREMENT pour la table `Users`
--
ALTER TABLE `Users`
  MODIFY `id` int(13) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `Contacts`
--
ALTER TABLE `Contacts`
  ADD CONSTRAINT `contacts_device_id` FOREIGN KEY (`user_id`,`device_id`) REFERENCES `Devices` (`user_id`, `device_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `contacts_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `Devices`
--
ALTER TABLE `Devices`
  ADD CONSTRAINT `device_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `Messages`
--
ALTER TABLE `Messages`
  ADD CONSTRAINT `messages_device_id` FOREIGN KEY (`user_id`,`device_id`) REFERENCES `Devices` (`user_id`, `device_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `messages_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `Parts`
--
ALTER TABLE `Parts`
  ADD CONSTRAINT `part_device_id` FOREIGN KEY (`user_id`,`device_id`) REFERENCES `Devices` (`user_id`, `device_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `part_message_id` FOREIGN KEY (`user_id`,`device_id`,`message_id`) REFERENCES `Messages` (`user_id`, `device_id`, `message_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `part_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `Tokens`
--
ALTER TABLE `Tokens`
  ADD CONSTRAINT `token_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
