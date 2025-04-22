<?php

use Kirby\Http\Response;

class Routes
{
	public function __invoke()
	{
		return [
			[
				"pattern" => "index.json",
				"language" => "*",
				"method" => "GET",
				"action" => function () {
					$site = site();

					$index = [];
					$list = $site
						->index()
						->notTemplate("project-overview")
						->notTemplate("global-images")
						->notTemplate("global-videos")
						->notTemplate("global-documents")
						->notTemplate("global-icons");
					foreach ($list as $page) {
						$index[] = [
							"uri" => (string) $page->uri(),
							"intendedTemplate" => (string) $page->intendedTemplate()->name(),
						];
					}

					return response::json($index);
				},
			],
			[
				"pattern" => "global.json",
				"language" => "*",
				"method" => "GET",
				"action" => function () {
					$site = site();

					return response::json($site->getGlobalData());
				},
			],
		];
	}
}
