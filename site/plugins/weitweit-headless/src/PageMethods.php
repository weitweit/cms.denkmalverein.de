<?php

use Kirby\Http\Remote;

class PageMethods
{
	public function __invoke()
	{
		return [
			"getBlocks" => function ($blocksField = "blocks") {
				return getBlocks($this, $blocksField);
			},
			"getYoutube" => function () {
				return getYoutube($this);
			},
			"getMeta" => function () {
				return getMeta($this);
			},
			"getProjectsCategories" => function ($page = null) {
				$page = $page ?? $this;
				return getProjectsCategories($page);
			},
			"getProjects" => function ($page = null, $categoryId = null) {
				$page = $page ?? $this;
				return getProjects($page, $categoryId);
			},
			"getCategories" => function ($page = null) {
				$page = $page ?? $this;
				return getCategories($page);
			},
			"getServices" => function ($page = null) {
				$page = $page ?? $this;
				return getServices($page);
			},
		];
	}
}

function getCategories($page)
{
	$items = $page->categories()->split(",");
	if (empty($items)) {
		return null;
	}

	$categories = [];
	foreach ($items as $item) {
		$category = page($item);
		$categories[] = [
			"title" => $category->title()->getText(),
			"slug" => $category->slug(),
			"uuid" => (string)$category->uuid(),
		];
	}

	return $categories;
}

function getServices($page)
{
	$items = $page->services()->split(",");
	if (empty($items)) {
		return null;
	}

	$blocks = $page->parent()->services()->toBlocks();

	$services = [];
	foreach ($blocks as $block) {
		if (!in_array($block->id(), $items)) {
			continue;
		}

		$services[] = [
			"title" => $block->title()->getText(),
		];
	}

	return $services;
}

function getProjects($page, $categoryId = null)
{
	$items = [];
	$projectsQuery = $page->children()->filterBy('intendedTemplate', 'project')->sortBy('year', 'desc');

	if ($categoryId) {
		$projectsQuery = $projectsQuery->filterBy('categories', $categoryId, ',');
	}

	foreach ($projectsQuery as $item) {
		$items[] = $item->getTeaser();
	}

	return $items;
}

function getProjectsCategories($page)
{
	$items = [];
	foreach ($page->children()->filterBy('intendedTemplate', 'project-category') as $item) {
		$items[] = [
			"title" => $item->title()->getText(),
			"href" => "/{$item->uri()}",
			"type" => "internal",
			"language" => null,
			"popup" => false,
			"hash" => null,
		];
	}

	return $items;
}

function getYoutube($page)
{
	// get video data
	$newBlocks = new Kirby\Cms\Blocks();
	$blocks = $page->blocks()->toBlocks();
	foreach ($blocks as $block) {
		if ($block->type() !== "video") {
			$newBlocks->add($block);
			continue;
		}

		// get video url
		$youtubeUrl = $block->url()->isNotEmpty()
			? $block->url()->getString()
			: null;
		$youtubeId = null;

		if (
			preg_match(
				"/youtube.com\/watch\?v=([a-zA-Z0-9_-]+)/",
				$youtubeUrl,
				$matches
			)
		) {
			$youtubeId = $matches[1];
		} elseif (
			preg_match("/youtu.be\/([a-zA-Z0-9_-]+)/", $youtubeUrl, $matches)
		) {
			$youtubeId = $matches[1];
		}

		$content["youtubeId"] = $youtubeId;
		if ($youtubeId) {
			$videoData = Remote::request(
				"https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v={$youtubeId}",
				["method" => "GET"]
			);
			$videoData = json_decode($videoData->content(), true);
			$blockData = $block->toArray();
			$blockData["content"]["width"] = $videoData["width"];
			$blockData["content"]["height"] = $videoData["height"];
			$blockData["content"]["ratio"] =
				$videoData["width"] / $videoData["height"];
			$blockData["content"]["embed"] = "https://www.youtube.com/embed/{$youtubeId}";

			$newBlock = new \Kirby\Cms\Block($blockData);
			$newBlocks->add($newBlock);
		}
	}

	$kirby = kirby();
	$newPage = $kirby->impersonate("kirby", function ($kirby) use (
		$page,
		$newBlocks
	) {
		$newPage = $page->update([
			"blocks" => $newBlocks->toArray(),
		]);

		return $newPage;
	});

	return $newPage;
}

function getBlocks($item, $blocksField)
{
	$json["blocks"] = [];

	foreach ($item->{$blocksField}()->toBlocks() as $block) {
		$type = $block->type();

		$content = method_exists($block, "getBlockArray")
			? $block->getBlockArray()
			: $block->getBlockDefaultArray();
		$content["anchor"] = $block->getAnchor();

		$json["blocks"][] = [
			"id" => $block->id(),
			"type" => $type,
			"isFirst" => $block->isFirst(),
			"isLast" => $block->isLast(),
			"prevBlock" => $block->prev() ? $block->prev()->type() : null,
			"nextBlock" => $block->next() ? $block->next()->type() : null,
			"content" => $content,
		];
	}

	return !empty($json["blocks"]) ? $json["blocks"] : null;
}

function getMeta($page)
{
	$tags = $page->metadata()->snippetData();

	foreach ($tags as $key => $tag) {
		if (isset($tag["attributes"]["name"]) && strpos($tag["attributes"]["name"], "twitter") !== false) {
			unset($tags[$key]);
		}

		// Update og:image dimensions if they exist
		if (isset($tag["attributes"]["property"])) {
			if ($tag["attributes"]["property"] === "og:image:width") {
				$tags[$key]["attributes"]["content"] = "1200";
			}
			if ($tag["attributes"]["property"] === "og:image:height") {
				$tags[$key]["attributes"]["content"] = "630";
			}
		}
	}

	return $tags;
}
