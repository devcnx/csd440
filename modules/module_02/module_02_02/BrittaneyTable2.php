<?php
/**
 * Module 2.2 Programming Assignment
 * CSD440 Server-Side Scripting
 * 
 * This program creates an HTML table populated with randomly generated numbers
 * using nested PHP loop structures. The table tags are written in HTML while
 * the cell contents are generated through PHP loops.
 * 
 * @author Brittaney Perry-Morgan
 * @date 2026-04-05
 */

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
    <title>Brittaney Random Number Table</title>
    <style>
        body {
            font-family: 'DM Sans', sans-serif;
            margin: 40px;
            background-color: #f5f5f5;
        }
        h1 {
            color: #333;
            text-transform: uppercase;
            text-align: center;
            font-weight: bold;
        }
        p {
            text-align: center;
            font-weight: bold;
        }
        table {
            border-collapse: collapse;
            width: auto;
            margin-top: 20px;
            background-color: #fff;
            margin: 0 auto;
        }
        th, td {
            border: 1px solid #333;
            padding: 12px 20px;
            text-align: center;
        }
        th {
            background-color: #4a90d9;
            color: #fff;
            text-transform: uppercase;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
    <h1>Random Number Table</h1>
    <p>Generated using PHP nested loops</p>

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
                    // Generate a random number for each cell
                    $randomNumber = rand($min, $max);
?>
                <td><?php echo $randomNumber; ?></td>
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
