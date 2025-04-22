<?php

class BlockMethods
{
	public function __invoke()
	{
		return [
			"getAnchor" => function () {
				$anchor = $this->anchor()->isNotEmpty()
					? [
						"title" => (string) $this->anchor(),
						"slug" => (string) $this->anchor()->slug(),
					]
					: null;
				return $anchor;
			},
			"getBlockDefaultArray" => function () {
				$blueprint = Kirby\Cms\Blueprint::load(
					"blocks/" . (string) $this->type()
				);
				if (!isset($blueprint["tabs"]["content"])) {
					return ["error" => "No content tab found in blueprint"];
				}

				$contentFields = $blueprint["tabs"]["content"]["fields"];

				$content = [];
				foreach ($contentFields as $name => $field) {
					$method = getMethodForField($field);

					if ($method === null) {
						continue;
					}

					$options = getOptionsForField($method, $name, $this);

					$content[$name] = $this->{$name}()->$method($options);
				}
				return $content;
			},
		];
	}
}
