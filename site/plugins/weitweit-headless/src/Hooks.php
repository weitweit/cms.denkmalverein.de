<?php

use Kirby\Http\Remote;

class Hooks
{
	public function __invoke()
	{
		/*$scheme = kirby()->request()->url()->scheme();
		$frontendUrl = env("FRONTEND_URL");
		$url = "{$frontendUrl}/api/revalidate";
		$validActions = ["update", "create", "changeNum"];

		if (strpos($url, "localhost") !== false) {
			return [];
		}*/

		/*return [
			"page.*:after" => function ($event, $newPage) use ($url, $validActions) {
				if ((string) $event->action() === "update") {
					$newPage = getYoutube($newPage);
				}

				if (!in_array((string) $event->action(), $validActions)) {
					return;
				}

				if (strpos($url, "localhost") !== false) {
					return;
				}

				$result = Remote::request($url, [
					"method" => "GET",
				]);

				if ($result->code() !== 200) {
					throw new Exception("Page revalidation failed");
				}
			},
			"site.update:after" => function ($event, $page) use ($url) {
				$result = Remote::request($url, [
					"method" => "GET",
				]);

				ray($result)->label("site.* - " . (string) $event->action());
				if ($result->code() !== 200) {
					throw new Exception("Site revalidation failed");
				}
			},
		];*/
	}
}
