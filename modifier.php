<?php
// require 'env.php';
/**
 * Rapid Modifier
 */
$DBhost = "localhost";
$DBuser = "root";
$DBpass = "";
$DBname = "movies";

$DBcon = new MySQLi($DBhost,$DBuser,$DBpass,$DBname);
$st = "John";
if ($DBcon->connect_errno) {
    die("ERROR : -> ".$DBcon->connect_error);
}
$query = $DBcon->query("SELECT * FROM music WHERE (`music_name` LIKE '%$st')");
    while ($row=$query->fetch_assoc()) {
            // $id = $row["id"];
            // $name = str_replace(".", "-", str_replace(" ", "-", $row['music_name']."-".mt_rand()));
            // //$date = date_create();
            // //$setd = date_timestamp_set($date, $stamp);
            // if($DBcon->query("UPDATE `music` SET `short_url` = '$name' WHERE `music`.`id` = '$id'")){
            //     echo "succes \n";
            // }
            // else {
            //     echo "wrong \n";
            // }
             "$row[id]  $row[music_name] $row[music_details]\n\n\r";
    }

require 'bootstrap.php';

use Src\TableGateways\SearchGateway;

$search = new SearchGateway($dbConnection);
echo json_encode($search->handleSearchQuery("John"));