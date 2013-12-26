<?php

require_once __DIR__ . "/src/MagicGraphic.php";
header("content-type: image/jpeg");

// Initialize a new Image
$mg = new MagicGraphic();

// Create the base layer from an image
$sexy = $mg->createLayer("sexy");
$sexy->loadFromFile("images/sexy-as-hell-31.jpg");
$sexy->autoResizeWidth(500);

// Create a second layer from an image
$explosion = $mg->createLayer("expl1");
$explosion->loadFromFile("images/explosion.png");
$explosion->autoResizeWidth(150);
$explosion->setOffset(100, 315);

// Duplicate layer 2
$explosion2 = $mg->duplicateLayer($explosion, "expl2");
$explosion2->setOffset(220, 350);
$explosion2->rotate(90);
$explosion2->crop(50, 50, 20, 20);

// Crop the image and display
//$mg->crop(300, 300, 100, 200);
$mg->display(0, MagicGraphic::PNG);
