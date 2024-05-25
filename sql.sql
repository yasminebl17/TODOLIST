
$host = 'localhost';
$db = 'todolist';  
$pass = '';
$charset = 'utf8mb4';


CREATE TABLE `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(255) NOT NULL,
    `password` varchar(255) NOT NULL,
    `role` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
);

CREATE TABLE `groups` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `leader_id` int(11) NOT NULL,
    PRIMARY KEY (`id`)
);

CREATE TABLE `group_members` (
    `group_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    PRIMARY KEY (`group_id`, `user_id`)
);

CREATE TABLE `tasks` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `group_id` int(11) NOT NULL,
    `description` varchar(255) NOT NULL,
    `assigned_by` int(11) NOT NULL,
    `assigned_to` int(11) NOT NULL,
    `deadline` date NOT NULL,
    `completed` boolean NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
);
