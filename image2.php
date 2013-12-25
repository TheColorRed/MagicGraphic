<?php

require_once __DIR__ . "/src/MagicGraphic.php";
header("content-type: image/jpeg");

// Initialize a new Image
$mg = new MagicGraphic();

$color = $mg->createLayer("color 1");
$color->loadColor(800, 600, Color::AQUA);

$color2 = $mg->createLayer("color 2");
$color2->loadColor(400, 300, Color::getColor(255, 123, 123));
//$color2->setOffset((800 - 400) / 2, (600 - 300) / 2);

$mg->anchorLayer($color2, Layer::AnchorBottomRight);

$mg->display(0, MagicGraphic::PNG);
