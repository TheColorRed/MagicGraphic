<?php

require_once __DIR__ . "/src/MagicGraphic.php";
header("content-type: image/jpeg");

// Initialize a new Image
$mg = new MagicGraphic();

$color = $mg->createLayer("color 1");
$color->loadColor(600, 600, Color::DARKGREEN);

$color2 = $mg->createLayer("color 2");
$color2->loadColor(420, 420, Color::GREEN);
$color2->rotate(45);
$mg->anchorLayer($color2, Layer::AnchorCenter);

$mg->display(0, MagicGraphic::PNG);
