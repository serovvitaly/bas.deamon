<?php

$db = new mysqli('localhost', 'root', 'Sy9YGKbG', 'test');

$result = $db->query('SELECT * FROM `final_sites_list` LIMIT 50');

while ($row = $result->fetch_assoc()) {
    echo '<p>', $row->id, '</p>';
}