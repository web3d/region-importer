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
    INSERT INTO region (id, parent_id, name, level)
    VALUES (:id, :parent_id, :name, :level)
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
	// 直辖市
	if ($level === 1 && mb_substr($name, -1) === '市') {
		$next_parent_id = substr($id, 0, 2) . '0100';

		// 最新发布的信息里，直辖市变成只有两级了 得补充一条二级的，保持三级结构的兼容
		$sth->bindValue(':id', $next_parent_id, PDO::PARAM_INT);
		$sth->bindValue(':parent_id', $id, PDO::PARAM_INT);
		$sth->bindValue(':name', $name);
		$sth->bindValue(':level', $level + 1);

		$sth->execute();

		$parent_id = 0;
		$parent[$level + 1] = $next_parent_id;
	} else {
		$parent[$level] = $id;

        $parent_id = (int) $parent[$level - 1];
	}

    $sth->bindValue(':id', $id, PDO::PARAM_INT);
    $sth->bindValue(':parent_id', $parent_id, PDO::PARAM_INT);
    $sth->bindValue(':name', $name);
    $sth->bindValue(':level', $level);

    $sth->execute();
}

fclose($handle);
