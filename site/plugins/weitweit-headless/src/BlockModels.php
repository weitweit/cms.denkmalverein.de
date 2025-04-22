<?php

use Kirby\Cms\Block;

class BlockModels
{
	public function __invoke()
	{
		return [
			"image" => ImageBlock::class,
			"text" => TextBlock::class,
			"images-text" => ImagesTextBlock::class,
			"image-text" => ImageTextBlock::class,
			"two-images" => TwoImagesBlock::class,
			"team" => TeamBlock::class,
			"teaser-large" => TeaserLargeBlock::class,
			"index" => IndexBlock::class,
		];
	}
}

class IndexBlock extends Block
{
	public function getBlockArray()
	{
		$content = [];
		$content["items"] = [];
		$items = site()->index()->filterBy('intendedTemplate', 'project')->sortBy('year', 'desc');

		foreach ($items as $item) {
			$content["items"][] = [
				"headline" => $item->title()->getString(),
				"customer" => $item->customer()->getString(),
				"year" => $item->year()->toDate('Y'),
				"uri" => "/" . $item->uri(),
				"image" => $item->teaserImage()->getImage([
					"width" => 360,
					"height" => 576,
					"manipulation" => "crop",
				]),
			];
		}

		$content["buttonLabel"] = "Alle Referenzen";
		return $content;
	}
}

class TeaserLargeBlock extends Block
{
	public function getBlockArray()
	{
		$content = [];
		$items = [];
		foreach ($this->items()->toStructure() as $item) {
			$items[] = [
				"image" => [
					"mobile" => $item->image()->getImage([
						"width" => 382,
						"height" => 611,
						"manipulation" => "crop",
					]),
					"desktop" => $item->image()->getImage([
						"width" => 1440,
						"height" => 900,
						"manipulation" => "crop",
					]),
				],
				"headline" => $item->headline()->getString(),
				"uri" => $item->page()->isNotEmpty() ? "/" . $item->page()->toPage()->uri() : null,
			];
		}

		$content["items"] = $items;
		return $content;
	}
}

class TeamBlock extends Block
{
	public function getBlockArray()
	{
		$content = [];
		$content["headline"] = $this->headline()->getGroupHeadline($this->headlineLevel()->getOption("h2"));
		$content["people"] = [];

		$first = $this->first()->toObject();

		$cv = [];
		foreach ($first->cv()->toStructure() as $cvItem) {
			$cv[] = [
				"year" => $cvItem->year()->getString(),
				"text" => $cvItem->text()->getText(),
			];
		}

		$content["first"] = [
			"image" => $first->image()->getImage([
				"width" => 720,
				"height" => 720,
				"manipulation" => "crop",
			]),
			"firstName" => $first->firstName()->getString(),
			"position" => $first->position()->getString(),
			"department" => $first->department()->getString(),
			"cv" => $cv,
			"cvFile" => $first->cvFile()->getDocument(),
		];
		$content["people"][] = $first->name()->getString();

		$employees = [];
		foreach ($this->employees()->toStructure() as $employee) {
			$employees[] = [
				"image" => $employee->image()->getImage([
					"width" => 342,
					"height" => 342,
					"manipulation" => "crop",
				]),
				"firstName" => $employee->firstName()->getString(),
				"position" => $employee->position()->getString(),
				"department" => $employee->department()->getString(),
			];

			$content["people"][] = $employee->name()->getString();
		}

		$content["employees"] = $employees;

		$content["image"] = $this->image()->getImage([
			"width" => 1440,
			"height" => 900,
			"manipulation" => "crop",
		]);

		return $content;
	}
}

class TwoImagesBlock extends Block
{
	public function getBlockArray()
	{
		$content = [];

		$sizePortrait = [
			"width" => 702,
			"height" => 1123,
			"manipulation" => "crop",
		];

		$sizeLandscape = [
			"width" => 702,
			"height" => 450,
			"manipulation" => "crop",
		];

		$isPortrait = $this->image1()->toFile()->isPortrait();
		$size = $isPortrait ? $sizePortrait : $sizeLandscape;
		$content["image1"] = $this->image1()->getImage($size);

		$isPortrait = $this->image2()->toFile()->isPortrait();
		$size = $isPortrait ? $sizePortrait : $sizeLandscape;
		$content["image2"] = $this->image2()->getImage($size);

		return $content;
	}
}

class ImagesTextBlock extends Block
{
	public function getBlockArray()
	{
		$content["text"] = $this->text()->getText();
		$content["imageFirst"] = $this->imageFirst()->getImage([
			"width" => 360,
			"height" => 576,
			"manipulation" => "crop",
		]);
		$content["imageSecond"] = $this->imageSecond()->getImage([
			"width" => 720,
			"height" => 1123,
			"manipulation" => "crop",
		]);

		return $content;
	}
}

class ImageTextBlock extends Block
{
	public function getBlockArray()
	{
		$content = [];
		$content["text"] = $this->text()->getText();
		$content["order"] = $this->order()->getOption("image-first");
		$content["image"] = $this->image()->getImage([
			"width" => 360,
			"height" => 576,
			"manipulation" => "crop",
		]);

		return $content;
	}
}


class ImageBlock extends Block
{
	public function getBlockArray()
	{
		$content = [];
		$alignment = $this->alignment()->getOption("full");

		$size = [
			"width" => 1080,
			"height" => 675 * 1.15,
			"manipulation" => "crop",
		];

		if ($alignment === "full") {
			$size = [
				"width" => 1440,
				"height" => 900 * 1.15,
				"manipulation" => "crop",
			];
		}

		$content["image"] = $this->image()->getImage($size);
		$content["alignment"] = $alignment;

		return $content;
	}
}


class TextBlock extends Block
{
	public function getBlockArray()
	{
		$content = [];
		$content["text"] = $this->text()->getText();
		$content["link"] = $this->link()->getLinkArray();
		$content["alignment"] = $this->alignment()->getOption("left");
		$content["textSize"] = $this->textSize()->getOption("large");
		return $content;
	}
}
