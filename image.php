<?php

require_once __DIR__ . "/src/MagicGraphic.php";
header("content-type: image/jpeg");

$mg = new MagicGraphic();

$sexy = $mg->createLayer("sexy");
$sexy->loadFromFile("images/sexy-as-hell-31.jpg");
$sexy->autoResizeWidth(500);

$explosion = $mg->createLayer("expl1");
$explosion->loadFromFile("images/explosion.png");
$explosion->autoResizeWidth(150);
$explosion->setOffset(100, 315);

$explosion2 = $mg->duplicateLayer("expl2", $explosion->getData());
$explosion2->setOffset(220, 350);
$explosion2->rotate(90);

$mg->display(MagicGraphic::JPG);
