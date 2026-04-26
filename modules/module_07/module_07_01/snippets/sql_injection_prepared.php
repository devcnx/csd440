<?php
/**
 * SQL Injection — Prepared Statement Fix
 * Module 7.1 Discussion Board — CSD440
 * 
 * Prepared statements separate query structure from user data.
 * Input is treated as data, not SQL. Quote characters are escaped
 * automatically by the PDO driver.
 */

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
$stmt->execute(["username" => $_POST["username"]]);
$user = $stmt->fetch();
?>
