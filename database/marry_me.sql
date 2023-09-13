CREATE TABLE `users` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `full_name` varchar(255),
  `email` varchar(255),
  `password` varchar(255),
  `phone` varchar(255),
  `birth_day` datetime,
  `gender` varchar(255),
  `image` varchar(255),
  `reports` int,
  `ban` boolean,
  `ban_count` int,
  `certified` boolean,
  `VIP` boolean,
  `created_at` timestamp
);

CREATE TABLE `info_user` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int,
  `answer_id` int,
  `answer` varchar(255)
);

CREATE TABLE `questions` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `question` varchar(255)
);

CREATE TABLE `info_user_questions` (
  `question_id` int,
  `user_id` int
);

CREATE TABLE `suggested_answers` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `question_id` int,
  `answer` varchar(255)
);

CREATE TABLE `admin` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `username` varchar(255),
  `password` varchar(255),
  `super_admin` boolean
);

CREATE TABLE `requests` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `sender_id` int,
  `reciever_id` int,
  `status` int
);

CREATE TABLE `blocks` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `blocker_id` int,
  `blocked_id` int
);

CREATE TABLE `favs` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_1` int COMMENT 'The person who make the action',
  `user_2` int
);

CREATE TABLE `chat` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_1` int,
  `user_2` int
);

CREATE TABLE `messages` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `chat_id` int,
  `sender_id` int,
  `reciever_id` int,
  `content` varchar(255),
  `status` int
);

CREATE TABLE `message_image` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `message_id` int,
  `image` varchar(255)
);

CREATE TABLE `report` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `message_id` int,
  `details` varchar(255),
  `action` int
);

ALTER TABLE `info_user` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `info_user_questions` ADD FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`);

ALTER TABLE `info_user_questions` ADD FOREIGN KEY (`user_id`) REFERENCES `info_user` (`id`);

ALTER TABLE `suggested_answers` ADD FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`);

ALTER TABLE `suggested_answers` ADD FOREIGN KEY (`answer`) REFERENCES `info_user` (`answer`);

ALTER TABLE `requests` ADD FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`);

ALTER TABLE `requests` ADD FOREIGN KEY (`reciever_id`) REFERENCES `users` (`id`);

ALTER TABLE `blocks` ADD FOREIGN KEY (`blocker_id`) REFERENCES `users` (`id`);

ALTER TABLE `blocks` ADD FOREIGN KEY (`blocked_id`) REFERENCES `users` (`id`);

ALTER TABLE `favs` ADD FOREIGN KEY (`user_1`) REFERENCES `users` (`id`);

ALTER TABLE `favs` ADD FOREIGN KEY (`user_2`) REFERENCES `users` (`id`);

ALTER TABLE `chat` ADD FOREIGN KEY (`user_1`) REFERENCES `users` (`id`);

ALTER TABLE `chat` ADD FOREIGN KEY (`user_2`) REFERENCES `users` (`id`);

ALTER TABLE `messages` ADD FOREIGN KEY (`chat_id`) REFERENCES `chat` (`id`);

ALTER TABLE `messages` ADD FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`);

ALTER TABLE `messages` ADD FOREIGN KEY (`reciever_id`) REFERENCES `users` (`id`);

ALTER TABLE `message_image` ADD FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`);

ALTER TABLE `report` ADD FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`);
