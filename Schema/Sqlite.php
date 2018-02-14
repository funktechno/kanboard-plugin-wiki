<?php

namespace Kanboard\Plugin\Budget\Schema;

use PDO;

const VERSION = 1;

function version_1(PDO $pdo)
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS budget_lines (
        "id" INTEGER PRIMARY KEY,
        "project_id" INTEGER NOT NULL,
        "amount" REAL NOT NULL,
        "date" TEXT NOT NULL,
        "comment" TEXT,
        FOREIGN KEY(project_id) REFERENCES projects(id) ON DELETE CASCADE
    )');

    $pdo->exec("CREATE TABLE IF NOT EXISTS hourly_rates (
        id INTEGER PRIMARY KEY,
        user_id INTEGER NOT NULL,
        rate REAL DEFAULT 0,
        date_effective INTEGER NOT NULL,
        currency TEXT NOT NULL,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
}
