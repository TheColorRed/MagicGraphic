<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <h1>MagicGraphic</h1>
        <?php
        require_once __DIR__ . "/src/MagicGraphic.php";
        echo Color::getColor(0, 0, 0);
        ?>
    </body>
</html>
