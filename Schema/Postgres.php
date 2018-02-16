<?php

namespace Kanboard\Plugin\Wiki\Schema;

use PDO;

const VERSION = 1;

function version_1(PDO $pdo)
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS wikipage (
        `id` SERIAL PRIMARY KEY,
        `project_id` INTEGER NOT NULL,
        `title` varchar(255) NOT NULL,
        `content` TEXT,
        `is_active` tinyint(4) DEFAULT 1,
        `creator_id` int(11) DEFAULT 0,
        `modifier_id` int(11) DEFAULT 0;
        `date_creation` VARCHAR(10) DEFAULT NULL,
        `date_modification` VARCHAR(10) DEFAULT NULL;
        `order` int(11) DEFAULT 1,
        `editions` int default 1;
        `current_edition` int default 1;
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
    );");

    $pdo->exec('CREATE TABLE wikipage_has_files (
        "id" SERIAL PRIMARY KEY,
        name VARCHAR(50),
        path VARCHAR(255),
        is_image TINYINT(1) DEFAULT 0,
        wikipage_id INT,
        FOREIGN KEY(wikipage_id) REFERENCES wikipage(id) ON DELETE CASCADE
    )');

    $pdo->exec('CREATE TABLE wikipage_editions (
    `edition` INT NOT NULL,
    `title` varchar(255) NOT NULL,
    `content` TEXT,
    `creator_id` int(11) DEFAULT 0,
    `date_creation` VARCHAR(10) DEFAULT NULL,
    wikipage_id INT NOT NULL,
    PRIMARY KEY (`edition`,`wikipage_id`),
    FOREIGN KEY(wikipage_id) REFERENCES wikipage(id) ON DELETE CASCADE
    )');

    $pdo->exec("INSERT INTO `settings` (`option`, `value`) VALUES ('persistEditions', '1');");

}
