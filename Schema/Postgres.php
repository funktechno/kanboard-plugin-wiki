<?php

namespace Kanboard\Plugin\Wiki\Schema;

use PDO;

const VERSION = 3;

function version_3(PDO $pdo)
{
    $pdo->exec('ALTER TABLE wikipage_has_files ADD COLUMN "date" INTEGER NOT NULL DEFAULT 0');
    $pdo->exec('ALTER TABLE wikipage_has_files ADD COLUMN "user_id" INTEGER NOT NULL DEFAULT 0');
    $pdo->exec('ALTER TABLE wikipage_has_files ADD COLUMN "size" INTEGER NOT NULL DEFAULT 0');
}

function version_2(PDO $pdo)
{
    $pdo->exec('ALTER TABLE wikipage RENAME COLUMN "order" TO "ordercolumn"');

}

function version_1(PDO $pdo)
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS wikipage (
        id SERIAL PRIMARY KEY,
        project_id INTEGER NOT NULL,
        title varchar(255) NOT NULL,
        content TEXT,
        is_active smallint DEFAULT 1,
        creator_id INTEGER DEFAULT 0,
        modifier_id INTEGER DEFAULT 0,
        date_creation VARCHAR(10) DEFAULT NULL,
        date_modification VARCHAR(10) DEFAULT NULL,
        "order" INTEGER DEFAULT 1,
        editions INTEGER default 1,
        current_edition INTEGER default 1,
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
    );');

    $pdo->exec('CREATE TABLE wikipage_has_files (
        "id" SERIAL PRIMARY KEY,
        name VARCHAR(50),
        path VARCHAR(255),
        is_image smallint DEFAULT 0,
        wikipage_id INTEGER,
        FOREIGN KEY(wikipage_id) REFERENCES wikipage(id) ON DELETE CASCADE
    )');

    $pdo->exec('CREATE TABLE wikipage_editions (
    "edition" INT NOT NULL,
    "title" varchar(255) NOT NULL,
    "content" TEXT,
    "creator_id" INTEGER DEFAULT 0,
    "date_creation" VARCHAR(10) DEFAULT NULL,
    wikipage_id INTEGER NOT NULL,
    PRIMARY KEY ("edition","wikipage_id"),
    FOREIGN KEY(wikipage_id) REFERENCES wikipage(id) ON DELETE CASCADE
    )');

    $pdo->exec("INSERT INTO settings (option, value) VALUES ('persistEditions', '1');");

}
