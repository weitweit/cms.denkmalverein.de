<?php

class Collections
{
	public function __invoke()
	{
		return [
			"global-images" => function ($site) {
				$files = $site->find("global-images")->images();

				return $files;
			},
			"global-icons" => function ($site) {
				$files = $site->find("global-icons")->images();

				return $files;
			},
			"global-documents" => function ($site) {
				$files = $site->find("global-documents")->documents();

				return $files;
			},
			"global-videos" => function ($site) {
				$files = $site->find("global-videos")->videos();

				return $files;
			},
		];
	}
}
