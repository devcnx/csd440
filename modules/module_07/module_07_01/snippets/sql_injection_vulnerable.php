<?php
/**
 * SQL Injection Vulnerable Example
 * Module 7.1 Discussion Board — CSD440
 * 
 * DO NOT USE IN PRODUCTION. This demonstrates unsafe concatenation
 * of user input directly into SQL queries.
 */

$username = $_POST["username"];
$query = "SELECT * FROM users WHERE username = '" . $username . "'";
$result = mysqli_query($conn, $query);

// Try entering: ' OR '1'='1 in the username field
// This closes the string, adds a condition that's always true,
// and returns every user in the table.
?>
