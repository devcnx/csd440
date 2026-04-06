<?php
/**
 * Module 3.2 Programming Assignment
 * CSD440 Server-Side Scripting
 * 
 * This program creates an HTML table populated with sums of randomly generated
 * numbers. The sum is calculated by calling an external function from functions.php.
 * The table structure uses HTML while cell contents are generated through PHP loops.
 * 
 * @author Brittaney Perry-Morgan
 * @date 2026-04-05
 */

// Include external function file
require_once 'functions.php';

// Configuration for the table dimensions
$rows = 5;       // Number of rows in the table
$cols = 4;       // Number of columns in the table
$min  = 1;       // Minimum random number value
$max  = 100;     // Maximum random number value
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brittaney Sum Table</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f5f5f5;
        }
        h1 {
            color: #333;
        }
        table {
            border-collapse: collapse;
            width: auto;
            margin-top: 20px;
            background-color: #fff;
        }
        th, td {
            border: 1px solid #333;
            padding: 12px 20px;
            text-align: center;
        }
        th {
            background-color: #4a90d9;
            color: #fff;
        }
        tr:nth-child(even) {
            background-color: #f0f0f0;
        }
        .operands {
            font-size: 0.8em;
            color: #666;
        }
    </style>
</head>
<body>
    <h1>Sum Table</h1>
    <p>Generated using PHP nested loops with external function</p>
    <p class="operands">Each cell displays the sum of two random numbers (1-100)</p>

    <table>
        <thead>
            <tr>
                <th>Row</th>
<?php
                // Column headers - loop through columns
                for ($col = 1; $col <= $cols; $col++) {
?>
                <th>Column <?php echo $col; ?></th>
<?php
                }
?>
            </tr>
        </thead>
        <tbody>
<?php
            // Outer loop - iterate through each row
            for ($row = 1; $row <= $rows; $row++) {
?>
            <tr>
                <td>Row <?php echo $row; ?></td>
<?php
                // Inner loop - iterate through each column in the current row
                for ($col = 1; $col <= $cols; $col++) {
                    // Generate two random numbers and calculate their sum
                    $num1 = rand($min, $max);
                    $num2 = rand($min, $max);
                    $sum = calculateSum($num1, $num2);
?>
                <td><?php echo $sum; ?></td>
<?php
                }
?>
            </tr>
<?php
            }
?>
        </tbody>
    </table>
</body>
</html>
