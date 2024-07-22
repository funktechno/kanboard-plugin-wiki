<?php

namespace Kanboard\Plugin\Wiki\Schema;

use PDO;

const VERSION = 4;

function version_4(PDO $pdo)
{
    $pdo->exec("ALTER TABLE wikipage ADD COLUMN parent_id INTEGER NULL");
    // FK
    $pdo->exec('
    PRAGMA foreign_keys = 0;

    CREATE TABLE wikipage_temp_table AS SELECT * FROM wikipage;
    DROP TABLE wikipage;

    CREATE TABLE wikipage (
        id                INTEGER,
        project_id        INTEGER       NOT NULL,
        title             VARCHAR (255) NOT NULL,
        content           TEXT          DEFAULT 1,
        is_active         INT (4)       DEFAULT 1,
        creator_id        INT (11)      DEFAULT 0,
        modifier_id       INT (11),
        date_creation     INTEGER,
        date_modification INTEGER,
        ordercolumn       INTEGER       DEFAULT 1,
        editions          INTEGER       DEFAULT 1,
        current_edition   INTEGER       DEFAULT 1,
        parent_id         INTEGER       REFERENCES wikipage (id),
        FOREIGN KEY (project_id) REFERENCES projects (id),
        PRIMARY KEY (id AUTOINCREMENT)
    );

    INSERT INTO wikipage (
        id,
        project_id,
        title,
        content,
        is_active,
        creator_id,
        modifier_id,
        date_creation,
        date_modification,
        ordercolumn,
        editions,
        current_edition,
        parent_id
    )
    SELECT id,
        project_id,
        title,
        content,
        is_active,
        creator_id,
        modifier_id,
        date_creation,
        date_modification,
        ordercolumn,
        editions,
        current_edition,
        parent_id
    FROM wikipage_temp_table;

    DROP TABLE wikipage_temp_table;

    PRAGMA foreign_keys = 1;');
}

function version_3(PDO $pdo)
{
    $pdo->exec('ALTER TABLE wikipage_has_files ADD COLUMN "date" INTEGER NOT NULL DEFAULT 0');
    $pdo->exec('ALTER TABLE wikipage_has_files ADD COLUMN "user_id" INTEGER NOT NULL DEFAULT 0');
    $pdo->exec('ALTER TABLE wikipage_has_files ADD COLUMN "size" INTEGER NOT NULL DEFAULT 0');
}

function version_2(PDO $pdo)
{

    // drop all tables
    $pdo->exec('drop table wikipage; drop table wikipage_has_files; drop table wikipage_editions; ');

    $pdo->exec("CREATE TABLE `wikipage` (
        `id`	INTEGER PRIMARY KEY AUTOINCREMENT,
        `project_id`	INTEGER NOT NULL,
        `title`	varchar(255) NOT NULL,
        `content`	TEXT DEFAULT 1,
        `is_active`	int(4) DEFAULT 1,
        `creator_id`	int(11) DEFAULT 0,
        `modifier_id`	int(11),
        `date_creation`	INTEGER,
        `date_modification`	INTEGER,
        `ordercolumn`	INTEGER DEFAULT 1,
        `editions`	INTEGER DEFAULT 1,
        `current_edition`	INTEGER DEFAULT 1,
        FOREIGN KEY(`project_id`) REFERENCES `projects`(`id`)
    );");

    $pdo->exec('CREATE TABLE wikipage_has_files (
        "id" INTEGER PRIMARY KEY,
        name VARCHAR(50),
        path VARCHAR(255),
        is_image INTEGER DEFAULT 0,
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
}

function version_1(PDO $pdo)
{
    $pdo->exec("CREATE TABLE `wikipage` (
	`id`	INTEGER PRIMARY KEY AUTOINCREMENT,
	`project_id`	INTEGER NOT NULL,
	`title`	varchar(255) NOT NULL,
	`content`	TEXT DEFAULT 1,
	`is_active`	int(4) DEFAULT 1,
	`creator_id`	int(11) DEFAULT 0,
	`modifier_id`	int(11),
	`date_creation`	INTEGER,
	`date_modification`	INTEGER,
	`order`	INTEGER DEFAULT 1,
	`editions`	INTEGER DEFAULT 1,
	`current_edition`	INTEGER DEFAULT 1,
	FOREIGN KEY(`project_id`) REFERENCES `projects`(`id`)
);");

    $pdo->exec('CREATE TABLE wikipage_has_files (
        "id" INTEGER PRIMARY KEY,
        name VARCHAR(50),
        path VARCHAR(255),
        is_image INTEGER DEFAULT 0,
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
