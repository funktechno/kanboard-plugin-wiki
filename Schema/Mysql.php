<?php

namespace Kanboard\Plugin\Wiki\Schema;

use PDO;

const VERSION = 8;

function version_8(PDO $pdo)
{
    $pdo->exec('ALTER TABLE wikipage_has_files ADD COLUMN `date` INT NOT NULL DEFAULT 0');
    $pdo->exec('ALTER TABLE wikipage_has_files ADD COLUMN `user_id` INT NOT NULL DEFAULT 0');
    $pdo->exec('ALTER TABLE wikipage_has_files ADD COLUMN `size` INT NOT NULL DEFAULT 0');
}

function version_7(PDO $pdo){
    $pdo->exec("ALTER TABLE wikipage CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin;");
    $pdo->exec("ALTER TABLE wikipage_editions CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin;");
}

function version_6(PDO $pdo){
    $pdo->exec("ALTER TABLE `wikipage` CHANGE COLUMN `order` `ordercolumn` int(11) NOT NULL;");
}

function version_5(PDO $pdo){
    // insert persistEditions into settings
    $pdo->exec("INSERT INTO `settings` (`option`, `value`) VALUES ('persistEditions', '1');");
}

function version_4(PDO $pdo){
    $pdo->exec("ALTER TABLE wikipage ADD `modifier_id` int(11) DEFAULT 0;");
}

function version_3(PDO $pdo)
{
    // future feature, track old editions, won't ever be modified, but could be viewed or restored
    $pdo->exec("CREATE TABLE wikipage_editions (
        `edition` INT NOT NULL,
        `title` varchar(255) NOT NULL,
        `content` TEXT,
        `creator_id` int(11) DEFAULT 0,
        `date_creation` VARCHAR(10) DEFAULT NULL,
        wikipage_id INT,
        PRIMARY KEY (`edition`,`wikipage_id`),
        FOREIGN KEY(wikipage_id) REFERENCES wikipage(id) ON DELETE CASCADE
    ) ENGINE=InnoDB CHARSET=utf8"
    );

    $pdo->exec("ALTER TABLE wikipage ADD `current_edition` int default 1;");
}

// add edition column
function version_2(PDO $pdo)
{
    $pdo->exec("ALTER TABLE wikipage ADD `editions` int default 1;");
    $pdo->exec("ALTER TABLE wikipage ADD `date_modification` VARCHAR(10) DEFAULT NULL;");
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
        `date_creation` VARCHAR(10) DEFAULT NULL,
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

    // $pdo->exec('CREATE TABLE IF NOT EXISTS wiki_lines (
    //     `id` INT NOT NULL AUTO_INCREMENT,
    //     `project_id` INT NOT NULL,
    //     `amount` FLOAT NOT NULL,
    //     `date` VARCHAR(10) NOT NULL,
    //     `comment` TEXT,
    //     FOREIGN KEY(project_id) REFERENCES projects(id) ON DELETE CASCADE,
    //     PRIMARY KEY(id)
    // ) ENGINE=InnoDB CHARSET=utf8');

    // $pdo->exec("CREATE TABLE IF NOT EXISTS hourly_rates (
    //     id INT NOT NULL AUTO_INCREMENT,
    //     user_id INT NOT NULL,
    //     rate FLOAT DEFAULT 0,
    //     date_effective INTEGER NOT NULL,
    //     currency CHAR(3) NOT NULL,
    //     FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
    //     PRIMARY KEY(id)
    // ) ENGINE=InnoDB CHARSET=utf8");
}
