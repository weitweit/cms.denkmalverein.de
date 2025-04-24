<?php

class SiteMethods
{
	public function __invoke()
	{
		return [
			"getGlobalData" => function () {
				$site = site();

				$result = [
					"language" => (string) kirby()->language(),
					"title" => (string) $site->title(),
					"mainMenu" => $site->getMainMenu(),
					"footerMenu" => $site->footerMenu()->getLinksArray(),
					"socialMenu" => $site->socialLinks()->getLinksArray(),
					"legalLinks" => $site->legalLinks()->getLinksArray(),
					"address" => [
						"legalName" => $site->legalName()->getText(),
						"city" => $site->city()->getText(),
						"street" => $site->street()->getText(),
						"zip" => $site->zip()->getText(),
					],
					"telephone" => $site->telephone()->getText(),
					"email" => $site->email()->getText(),
					"footerContactLabel" => $site->footerContactLabel()->getText(),
				];

				if (kirby()->languages()->count() === 0) {
					unset($result["language"]);
				}

				return $result;
			},
			"getMainMenu" => function () {
				return getMainMenu();
			},
		];
	}
}

function getMainMenu()
{
	$items = site()->pages()->listed();

	if ($items->isEmpty()) {
		return null;
	}

	foreach ($items as $item) {
		$children = $item->children()->listed();
		ray($item, $children);
		$resultChildren = null;

		if ($children->isNotEmpty()) {
			$resultChildren = [];
			foreach ($children as $child) {
				$resultChildren[] = [
					"title" => $child->title()->getString(),
					"url" => "/{$child->uri()}",
				];
			}
		}

		$result[] = [
			"title" => $item->title()->getString(),
			"url" => "/{$item->uri()}",
			"isExpandable" => $children->isNotEmpty(),
			"children" => $resultChildren,
		];
	}

	return $result;
}
