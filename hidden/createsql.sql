CREATE TABLE `groups` (
  `group_id` int(10) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL,
  PRIMARY KEY (`group_id`)
);

CREATE TABLE `users` (
  `user_id` int(10) NOT NULL AUTO_INCREMENT,
  `sel_group_id` int(10),
  `login` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nachname` varchar(255) NOT NULL,
  `vorname` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `perm_admin` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`),
  FOREIGN KEY (`sel_group_id`) REFERENCES `groups` (`group_id`)
);

CREATE TABLE `securitytokens` (
  `securitytoken_id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `securitytoken` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`securitytoken_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
);

CREATE TABLE `users_groups` (
  `users_groups_id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `group_id` int(10) NOT NULL,
  `perm_group_admin` tinyint(1) NOT NULL DEFAULT 0,
  `perm_poll` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`users_groups_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  FOREIGN KEY (`group_id`) REFERENCES `groups` (`group_id`)
);

CREATE TABLE `polls` (
  `poll_id` int(10) NOT NULL AUTO_INCREMENT,
  `group_id` int(10) NOT NULL,
  `poll_unique` varchar(255),
  `poll_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`poll_id`),
  FOREIGN KEY (`group_id`) REFERENCES `groups` (`group_id`)
);

CREATE TABLE `polls_users` (
  `poll_user_id` int(10) NOT NULL AUTO_INCREMENT,
  `password` varchar(255) NOT NULL,
  `poll_id` int(10) NOT NULL,
  `answered_current` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`poll_user_id`),
  FOREIGN KEY (`poll_id`) REFERENCES `polls` (`poll_id`)
);

CREATE TABLE `poll_securitytokens` (
  `poll_securitytoken_id` int(10) NOT NULL AUTO_INCREMENT,
  `poll_user_id` int(10) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `securitytoken` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`poll_securitytoken_id`),
  FOREIGN KEY (`poll_user_id`) REFERENCES `polls_users` (`poll_user_id`)
);

CREATE TABLE `questions` (
  `question_id` int(10) NOT NULL AUTO_INCREMENT,
  `poll_id` int(10) NOT NULL,
  `question` text NOT NULL,
  `options_amount` int(8) NOT NULL,
  `current` tinyint(1) NOT NULL DEFAULT 0,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`question_id`),
  FOREIGN KEY (`poll_id`) REFERENCES `polls` (`poll_id`)
);

CREATE TABLE `options` (
  `option_id` int(10) NOT NULL AUTO_INCREMENT,
  `question_id` int(10) NOT NULL,
  `option_name` text NOT NULL,
  `votes` int(8),
  PRIMARY KEY (`option_id`),
  FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`)
)