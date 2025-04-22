<?php

/** @var Page $page */

use function PHPSTORM_META\map;

/** @var Array $json */

if ($page->blocks()->isNotEmpty()) {
	$json["blocks"] = $page->getBlocks();
}

$json["content"] = $page->getJsonData();
echo json_encode($json);
