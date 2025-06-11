CREATE DATABASE todo;

CREATE TABLE `users` (
  `username` varchar(20) NOT NULL,
  `password` varchar(45) NOT NULL,
  PRIMARY KEY (`username`)
);

CREATE TABLE `tasks` (
  `taskid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `task` varchar(100) NOT NULL,
  `done` tinyint(4) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `priority_id` int(11) DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  PRIMARY KEY (`taskid`),
  CONSTRAINT `username` FOREIGN KEY (`username`) REFERENCES `users` (`username`),
  CONSTRAINT `category_fk` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  CONSTRAINT `priority_fk` FOREIGN KEY (`priority_id`) REFERENCES `priorities` (`priority_id`)
);

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `color` varchar(7) DEFAULT '#000000',
  PRIMARY KEY (`category_id`),
  CONSTRAINT `category_username_fk` FOREIGN KEY (`username`) REFERENCES `users` (`username`)
);

CREATE TABLE `priorities` (
  `priority_id` int(11) NOT NULL AUTO_INCREMENT,
  `priority_name` varchar(20) NOT NULL,
  `priority_level` int(11) NOT NULL,
  PRIMARY KEY (`priority_id`)
);

CREATE TABLE `task_history` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `taskid` int(11) NOT NULL,
  `action` varchar(20) NOT NULL,
  `action_date` datetime NOT NULL,
  `old_value` text,
  `new_value` text,
  PRIMARY KEY (`history_id`),
  CONSTRAINT `history_task_fk` FOREIGN KEY (`taskid`) REFERENCES `tasks` (`taskid`)
);

-- Insert default priorities
INSERT INTO `priorities` (`priority_name`, `priority_level`) VALUES
('Low', 1),
('Medium', 2),
('High', 3),
('Urgent', 4);