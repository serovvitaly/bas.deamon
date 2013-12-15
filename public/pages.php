<?php

$db = new mysqli('localhost', 'root', 'Sy9YGKbG', 'test');

$res = $db->query('SELECT * FROM `final_sites_list` WHERE `status` >= 2 ORDER BY `domain_created` DESC LIMIT 50');

while ($row = $res->fetch_assoc()) {
    echo "<p>{$row['url']}</p>\n";
}