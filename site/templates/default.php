<?php

use Kirby\Toolkit\Config;

$url = $kirby->request()->url()->toString();

if (!str_contains($url, ".json")) {
	$scheme = $kirby->request()->url()->scheme();
	$frontendUrl = Config::get("frontendUrl");
	$token = (string)$kirby->request()->query("token");

	$language =
		$kirby->languages()->count() > 1 ? "{$kirby->languageCode()}/" : "";
	$uri = $page->uri() !== "home" ? "{$page->uri()}" : "home";

	$targetUrl = "{$scheme}://{$frontendUrl}/{$language}preview?slug={$uri}";
	if (!empty($token)) {
		$targetUrl .= "&{$token}";
	}

	go($targetUrl);
}
