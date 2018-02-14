<?php

namespace Kanboard\Plugin\Wiki\Schema;

use PDO;

const VERSION = 2;

// dummy data
function version_2(PDO $pdo)
{
    $pdo->exec("INSERT INTO `funktech_kanb738`.`wikipage` (`project_id`, `title`, `content`, `is_active`, `creator_id`, `order`) VALUES ('1', 'Intro	', 'bla bla', '1', '2', '1');
INSERT INTO `funktech_kanb738`.`wikipage` (`project_id`, `title`, `content`, `is_active`, `creator_id`, `order`) VALUES ('1', 'Security', 'yes', '1', '2', '2');
INSERT INTO `funktech_kanb738`.`wikipage` (`project_id`, `title`, `content`, `is_active`, `creator_id`, `order`) VALUES ('1', 'Front end', 'asdf', '1', '2', '3');
");

    $pdo->exec("ALTER TABLE wikipage DROP COLUMN category_id;");

}

function version_1(PDO $pdo)
{

    // `category_id` int(11) DEFAULT 0,
    $pdo->exec("CREATE TABLE IF NOT EXISTS wikipage (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `project_id` INTEGER NOT NULL,
        `title` varchar(255) NOT NULL,
        `content` TEXT,
        `is_active` tinyint(4) DEFAULT 1,
        `creator_id` int(11) DEFAULT 0,
        `date_creation` bigint(20) DEFAULT NULL,
        `order` int(11) DEFAULT 1,
        PRIMARY KEY (`id`),
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
    );");

    $pdo->exec("CREATE TABLE wikipage_has_files (
        id INT NOT NULL AUTO_INCREMENT,
        name VARCHAR(50),
        path VARCHAR(255),
        is_image TINYINT(1) DEFAULT 0,
        wikipage_id INT,
        PRIMARY KEY (id),
        FOREIGN KEY(wikipage_id) REFERENCES wikipage(id) ON DELETE CASCADE
    ) ENGINE=InnoDB CHARSET=utf8"
    );

    $pdo->exec('CREATE TABLE IF NOT EXISTS wiki_lines (
        `id` INT NOT NULL AUTO_INCREMENT,
        `project_id` INT NOT NULL,
        `amount` FLOAT NOT NULL,
        `date` VARCHAR(10) NOT NULL,
        `comment` TEXT,
        FOREIGN KEY(project_id) REFERENCES projects(id) ON DELETE CASCADE,
        PRIMARY KEY(id)
    ) ENGINE=InnoDB CHARSET=utf8');

    $pdo->exec("CREATE TABLE IF NOT EXISTS hourly_rates (
        id INT NOT NULL AUTO_INCREMENT,
        user_id INT NOT NULL,
        rate FLOAT DEFAULT 0,
        date_effective INTEGER NOT NULL,
        currency CHAR(3) NOT NULL,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
        PRIMARY KEY(id)
    ) ENGINE=InnoDB CHARSET=utf8");
}
