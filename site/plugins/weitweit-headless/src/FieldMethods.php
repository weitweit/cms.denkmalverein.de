<?php

use Kirby\Toolkit\Dom;
use Kirby\Uuid\Uuid;
use Kirby\Toolkit\A;

class FieldMethods
{
	public function __invoke()
	{
		return [
			"getBlocks" => function ($field, $blocksField = "blocks") {
				return getBlocks($field, $blocksField);
			},
			"getLinksArray" => function ($field) {
				return getLinkArray($field, multiple: true);
			},
			"getLinkArray" => function (
				$field,
				$customTitle = "",
				$multiple = false
			) {
				return getLinkArray($field, $customTitle, $multiple);
			},
			"getGroupHeadline" => function ($field, $level = "h2") {
				return getGroupHeadline($field, $level);
			},
			"getWriter" => function ($field) {
				return $field->isNotEmpty()
					? (string) $field->getPermalinksToUrls()
					: null;
			},
			"getString" => function ($field) {
				return $field->isNotEmpty() ? (string) $field : null;
			},
			"getText" => function ($field) {
				return $field->isNotEmpty() ? (string) $field : null;
			},
			"getFloat" => function ($field) {
				return $field->isNotEmpty() ? (float) $field->content()->value() : null;
			},
			"getNumber" => function ($field) {
				return $field->isNotEmpty() ? (int) $field->value() : null;
			},
			"getSelect" => function ($field, $default = null) {
				return $field->isNotEmpty() ? (string) $field : $default;
			},
			"getOption" => function ($field, $default = null) {
				return $field->isNotEmpty() ? (string) $field : $default;
			},
			"getToggles" => function ($field, $default = null) {
				return $field->isNotEmpty() ? (string) $field : $default;
			},
			"getToggle" => function ($field) {
				return $field->isNotEmpty() ? $field->toBool() : false;
			},
			"getStructure" => function ($field, $blueprint) {
				$content = [];
				foreach ($field->toStructure() as $row) {
					$contentRow = [];
					foreach ($blueprint as $name => $field) {
						$method = getMethodForField($field);
						$contentRow[$name] = $row->{$name}()->$method();
					}
					$content[] = $contentRow;
				}

				return $content;
			},
			"getCategory" => function ($field): ?array {
				$item = $field->split(",");

				if (empty($item)) {
					return null;
				}

				$categoryPage = page($item[0]);
				if (!$categoryPage) {
					return null;
				}

				return [
					"title" => $categoryPage->title()->getText(),
					"slug" => $categoryPage->slug(),
					"uuid" => (string)$categoryPage->uuid(),
				];
			},
			"getMp4" => function ($field) {
				if ($field->isEmpty()) {
					return null;
				}

				return $field->toFile()->url();
			},
			"getDocument" => function ($field) {
				if ($field->isEmpty()) {
					return null;
				}

				return $field->toFile()->url();
			},
			"getVideo" => function ($field) {
				if ($field->isEmpty()) {
					return null;
				}

				$id = preg_replace("/https:\/\/vimeo.com\//", "", $field->value);
				$id = preg_replace("/\?.*/", "", $id);

				if (!$id) {
					return null;
				}

				return $id;
			},
			"getImages" => function (
				$field,
				$sizes = [
					"width" => 100,
					"height" => 100,
					"manipulation" => "crop",
				]
			) {
				$images = [];
				foreach ($field->toFiles() as $image) {
					$images[] = getImage($image, $sizes);
				}

				return $images;
			},
			"getImage" => function (
				$field,
				$sizes = [
					"width" => 100,
					"height" => 100,
					"manipulation" => "crop",
				]
			) {
				$image = getImage($field, $sizes);

				return $image;
			},
			"getSvg" => function ($field) {
				$file = $field->toFile();
				if (!$file) {
					return null;
				}

				$svg = [
					"src" => $file->url(),
					"width" => $file->width(),
					"height" => $file->height(),
					"alt" => (string) $file->alt(),
				];

				return $svg;
			},
			"getPermalinksToUrls" => function ($field) {
				// kirby/config/methods.php permalinksToUrls()
				if ($field->isNotEmpty() === true) {
					$dom = new Dom($field->value);
					$attributes = ["href", "src"];
					$elements = $dom->query(
						"//*[" .
							implode(
								" | ",
								A::map($attributes, fn($attribute) => "@" . $attribute)
							) .
							"]"
					);

					foreach ($elements as $element) {
						foreach ($attributes as $attribute) {
							if (
								$element->hasAttribute($attribute) &&
								($url = $element->getAttribute($attribute))
							) {
								try {
									// if url does not contain /@/page/ or /@/file return null
									if (
										!str_starts_with($url, "/@/page/") &&
										!str_starts_with($url, "/@/file")
									) {
										continue;
									}

									if ($uuid = Uuid::for($url)) {
										if ($uuid->model() === null) {
											continue;
										}

										$url = $uuid->model()?->url();

										if (get_class($uuid) === "Kirby\Uuid\PageUuid") {
											$url = str_replace(kirby()->url(), "", $url);
										}

										$element->setAttribute($attribute, $url);
									}
								} catch (InvalidArgumentException) {
									// ignore anything else than permalinks
								}
							}
						}
					}

					$field->value = $dom->toString();
				}

				return $field;
			},
		];
	}
}

function getMethodForField($field)
{
	if (isset($field["type"]) && $field["type"] === "line") {
		return null;
	}

	$customMatches = [
		"fields/link-structure" => "getLinkArray",
		"fields/image-single-required" => "getImage",
		"fields/icon-single" => "getSvg",
		"fields/group-headline" => "getGroupHeadline",
	];

	if (is_string($field)) {
		$method = $customMatches[$field];
	} elseif (isset($field["extends"])) {
		$method = $customMatches[$field["extends"]];

		// custom for image field with option multiple = true
		if ($method === "getImage" && isset($field["multiple"])) {
			$method = "getImages";
		}
	} else {
		$method = "get" . ucfirst($field["type"]);
	}

	return $method;
}

function getGroupHeadline($field, $level)
{
	return [
		"headline" => $field->headline()->getWriter(),
		"level" => $level,
	];
}

function getOptionsForField($method, $name, $block)
{
	switch ($method) {
		case "getImage":
		case "getImages":
			return option("weitweit.blocks.images.{$block->type()}.{$name}", [
				"width" => 200,
				"height" => 200,
				"manipulation" => "crop",
			]);
			break;
		case "getStructure":
			$blueprint = Kirby\Cms\Blueprint::load("blocks/" . $block->type());
			return $blueprint["tabs"]["content"]["fields"][$name]["fields"];

			break;
		case "getGroupHeadline":
			return $block->headlineLevel()->getOption();
		default:
			return null;
	}
}

function getImage($field, $sizes)
{
	if ($field instanceof Kirby\Cms\File) {
		$file = $field;
	} else {
		$file = $field->toFile();
	}

	if (!$file) {
		return null;
	}

	$width = $sizes["width"] ? $sizes["width"] * 2 : null;
	$height = $sizes["height"] ? $sizes["height"] * 2 : null;

	if ($sizes["manipulation"] === "resize") {
		$manipulatedFile = $file->resize($width, $height);
	} else {
		$manipulatedFile = $file->crop($width, $height);
	}

	return [
		"src" => $manipulatedFile->url(),
		"alt" => (string) $manipulatedFile->alt(),
		"width" => $manipulatedFile->width(),
		"height" => $manipulatedFile->height(),
	];
}

function getLinkArray($field, $customTitle = "", $multiple = false): ?array
{
	if (!$field) {
		return null;
	}

	$items = $field->toStructure();
	if ($items->count() === 0) {
		return null;
	}

	$links = [];
	foreach ($items as $item) {
		$title = $item->title()->isNotEmpty() ? (string) $item->title() : null;
		$popup = $item->popup()->isTrue();
		$hash = $item->hash()->isNotEmpty() ? (string) $item->hash() : null;

		// get internal links
		$internal = getInternalUri($item->link());

		$links[] = [
			"title" => $title ? $title : null,
			"href" => $internal ? $internal["uri"] : $item->link()->toUrl(),
			"type" => $internal ? $internal["type"] : "absolute",
			"language" => $internal ? (string) kirby()->languageCode() : null,
			"popup" => $popup,
			"hash" => $hash,
		];
	}

	if ($multiple) {
		return $links;
	}

	// return first item of links
	return $links[0];
}

function getInternalUri($link): ?array
{
	if (!$link) {
		return null;
	}

	$uri = null;
	$url = (string) $link;
	$language = kirby()->languageCode();

	// if url does not contain page:// or file:// return null
	if (!str_starts_with($url, "page://") && !str_starts_with($url, "file://")) {
		return null;
	}

	try {
		if ($uuid = Uuid::for($url)) {
			$type = null;
			if (get_class($uuid) === "Kirby\Uuid\PageUuid") {
				$type = "page";
				$uri = $uuid->model()?->uri();
				$uri = $uri ? "/" . $uri : null;
			} elseif (get_class($uuid) === "Kirby\Uuid\FileUuid") {
				$type = "file";
				$uri = $uuid->model()?->url();
			}
		}
	} catch (InvalidArgumentException) {
		return null;
	}

	$resultUriHome = "/";
	$resultUri = $uri;

	if ($language) {
		$resultUriHome = "/{$language}";
		$resultUri = $type !== "file" ? "/{$language}{$uri}" : $uri;
	}

	return [
		"type" => $type,
		"uri" => $uri === "/home" ? $resultUriHome : $resultUri,
	];
}
