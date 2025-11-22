-- phpMyAdmin SQL Dump
-- version 4.5.4.1
-- http://www.phpmyadmin.net
--
-- Client :  localhost
-- Généré le :  Dim 16 Novembre 2025 à 09:38
-- Version du serveur :  5.7.11
-- Version de PHP :  7.0.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `move_and_go`
--

-- --------------------------------------------------------

--
-- Structure de la table `moves`
--

CREATE TABLE `moves` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `date_start` datetime DEFAULT NULL,
  `city_from` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city_to` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `housing_from` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `housing_to` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `volume_m3` int(11) NOT NULL DEFAULT '1',
  `needed` int(11) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Contenu de la table `moves`
--

INSERT INTO `moves` (`id`, `client_id`, `title`, `description`, `status`, `date_start`, `city_from`, `city_to`, `housing_from`, `housing_to`, `volume_m3`, `needed`, `created_at`, `is_active`) VALUES
(10, 3, 'Rouen - Bordeaux - Maison', 'Déménagement d\'objets très fragiles', 'open', '2025-11-24 10:45:00', 'Rouen', 'Bordeaux', 'Étage: 1, Ascenseur: oui, Portes étroites', 'Étage: 2, Ascenseur: oui, Accès camion', 200, 2, '2025-11-15 17:40:31', 0),
(11, 3, 'jfhcjdqjd', '', 'open', '2025-11-15 17:46:00', 'dfdf', 'qefqF', 'Étage: NC, Ascenseur: non', 'Étage: NC, Ascenseur: non', 0, 0, '2025-11-15 17:46:12', 1),
(12, 3, 'Test', 'test', 'open', '2025-11-15 17:55:00', 'c', 'c', 'Étage: NC, Ascenseur: non', 'Étage: NC, Ascenseur: non', 1, 1, '2025-11-15 17:56:00', 1);

-- --------------------------------------------------------

--
-- Structure de la table `move_images`
--

CREATE TABLE `move_images` (
  `id` int(11) NOT NULL,
  `move_id` int(11) NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Contenu de la table `move_images`
--

INSERT INTO `move_images` (`id`, `move_id`, `filename`, `created_at`) VALUES
(3, 10, 'move_10_45023a971a9b.jpg', '2025-11-15 17:40:31');

-- --------------------------------------------------------

--
-- Structure de la table `move_messages`
--

CREATE TABLE `move_messages` (
  `id` int(11) UNSIGNED NOT NULL,
  `offer_id` int(11) NOT NULL,
  `sender_role` varchar(20) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `body` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Contenu de la table `move_messages`
--

INSERT INTO `move_messages` (`id`, `offer_id`, `sender_role`, `sender_id`, `body`, `created_at`) VALUES
(1, 2, 'mover', 8, 'testttttttt', '2025-11-15 14:13:24'),
(2, 1, 'mover', 8, 'ttttttttfctgncb', '2025-11-15 14:16:13'),
(3, 1, 'client', 3, 'reponse', '2025-11-15 17:21:53'),
(4, 4, 'mover', 8, 'test', '2025-11-16 09:41:10');

-- --------------------------------------------------------

--
-- Structure de la table `offers`
--

CREATE TABLE `offers` (
  `id` int(11) NOT NULL,
  `move_id` int(11) NOT NULL,
  `mover_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `message` text,
  `status` enum('pending','accepted','rejected','withdrawn') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Contenu de la table `offers`
--

INSERT INTO `offers` (`id`, `move_id`, `mover_id`, `price`, `message`, `status`, `created_at`) VALUES
(3, 10, 8, '15000.00', '', 'accepted', '2025-11-15 17:41:42'),
(4, 11, 8, '450.00', '', 'rejected', '2025-11-15 17:47:13'),
(5, 12, 8, '560.00', '', 'pending', '2025-11-15 18:54:24');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nom` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('client','demenageur','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'client',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Contenu de la table `users`
--

INSERT INTO `users` (`id`, `nom`, `prenom`, `email`, `password_hash`, `role`, `is_active`, `created_at`) VALUES
(1, 'Alice Client', '0', 'alice@example.com', '$2y$10$3XZF5N4SSEnYc8qfY2kCxu3B6uYQ0tq9JkqvTq2cEoM1S8wXH9lLu', 'client', 1, '2025-11-08 10:52:57'),
(2, 'Bob Mover', '0', 'bob@example.com', '$2y$10$3XZF5N4SSEnYc8qfY2kCxu3B6uYQ0tq9JkqvTq2cEoM1S8wXH9lLu', 'demenageur', 1, '2025-11-08 10:52:57'),
(3, 'HOUEDANOU', 'Daren', 'chrislorys@gmail.com', '$2y$10$9VcYoaV/xQLpxySnftQugOWjsgwTmQEZDNy5KQTfBlCWS8bAJf8SC', 'client', 1, '2025-11-08 14:32:29'),
(4, 'Test', 'test', 'texte@gmail.com', '$2y$10$G/jWDTS7GZ.bVCm.TL6gf.SWyifNvEEplbId9Mp/1gbvSHEaui72e', 'demenageur', 1, '2025-11-08 18:17:57'),
(8, 'DADELE', 'Marvin', 'marvin@gmail.com', '$2y$10$sRzr4P36qmQyfelNr3xBvucz0M4fIsL3zIxQhkEf9HPVUDAI4p5hO', 'demenageur', 1, '2025-11-11 21:16:17');

--
-- Index pour les tables exportées
--

--
-- Index pour la table `moves`
--
ALTER TABLE `moves`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_moves_client` (`client_id`),
  ADD KEY `idx_moves_active_created` (`created_at`),
  ADD KEY `idx_moves_route` (`city_from`,`city_to`),
  ADD KEY `idx_moves_date` (`date_start`);

--
-- Index pour la table `move_images`
--
ALTER TABLE `move_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `move_id` (`move_id`);

--
-- Index pour la table `move_messages`
--
ALTER TABLE `move_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_offer` (`offer_id`);

--
-- Index pour la table `offers`
--
ALTER TABLE `offers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `move_id` (`move_id`),
  ADD KEY `mover_id` (`mover_id`),
  ADD KEY `status` (`status`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `moves`
--
ALTER TABLE `moves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT pour la table `move_images`
--
ALTER TABLE `move_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT pour la table `move_messages`
--
ALTER TABLE `move_messages`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT pour la table `offers`
--
ALTER TABLE `offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `moves`
--
ALTER TABLE `moves`
  ADD CONSTRAINT `fk_moves_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `move_images`
--
ALTER TABLE `move_images`
  ADD CONSTRAINT `move_images_ibfk_1` FOREIGN KEY (`move_id`) REFERENCES `moves` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `offers`
--
ALTER TABLE `offers`
  ADD CONSTRAINT `offers_ibfk_1` FOREIGN KEY (`move_id`) REFERENCES `moves` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `offers_ibfk_2` FOREIGN KEY (`mover_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
