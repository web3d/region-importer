<?php

// config
$host     = '127.0.0.1';
$dbname   = 'region_db';
$charset  = 'utf8';
$username = 'root';
$password = '';

set_time_limit(0);

$dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

$options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
);

$dbh = new PDO($dsn, $username, $password, $options);

$sth = $dbh->prepare('
    INSERT INTO region (id, parent_id, name)
    VALUES (:id, :parent_id, :name)
');

$parent = array(0);

$handle = fopen('data.txt', 'r');

while (!feof($handle)) {
    $row = trim(str_replace('　', ' ', fgets($handle)));

    if (!preg_match('/^(\d+)\s+(.+)$/', $row, $matches)) {
        continue;
    }

    list($row, $id, $name) = $matches;

    $level = strlen(preg_replace('/(00){1,2}$/', '', $id)) / 2;

    $parent_id = $parent[$level - 1];

    $parent[$level] = $id;

    $sth->bindValue(':id', $id, PDO::PARAM_INT);
    $sth->bindValue(':parent_id', $parent_id, PDO::PARAM_INT);
    $sth->bindValue(':name', $name);

    $sth->execute();
}

fclose($handle);
