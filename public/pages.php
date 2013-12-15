<?php

$db = new mysqli('localhost', 'root', 'Sy9YGKbG', 'test');

$page = (isset($_GET['page']) AND $_GET['page'] > 0) ? $_GET['page'] : 1;

$limit = 50;
$start = ($page - 1) * $limit;

$res = $db->query("SELECT * FROM `final_sites_list` WHERE `status` >= 2 ORDER BY `domain_created` DESC LIMIT {$start},{$limit}");

while ($row = $res->fetch_assoc()) {
    echo "<p>{$row['url']}</p>\n";
}