<?php

use Kirby\Filesystem\F;

require_once __DIR__ . "/../kirby3-dotenv/global.php";
loadenv([
	"dir" => realpath(__DIR__ . "/../../../"),
	"file" => ".env",
]);

F::loadClasses(
	[
		"Hooks" => "src/Hooks.php",
		"Routes" => "src/Routes.php",
		"SiteMethods" => "src/SiteMethods.php",
		"PageMethods" => "src/PageMethods.php",
		"FieldMethods" => "src/FieldMethods.php",
		"BlockModels" => "src/BlockModels.php",
		"BlockMethods" => "src/BlockMethods.php",
		"Collections" => "src/Collections.php",
	],
	__DIR__
);

ray("weitweit headless");

class WeitweitHeadless
{
	/**
	 * @var Hooks
	 */
	private $hooks;
	/**
	 * @var Routes
	 */
	private $routes;

	/**
	 * @var SiteMethods
	 */
	private $sitemethods;

	/**
	 * @var PageMethods
	 */
	private $pagemethods;

	/**
	 * @var FieldMethods
	 */
	private $fieldmethods;

	/**
	 * @var BlockModels
	 */
	private $blockmodels;

	/**
	 * @var BlockMethods
	 */
	private $blockmethods;

	/**
	 * @var Collections
	 */
	private $collections;

	public function __construct()
	{
		$hooks = new Hooks();
		$routes = new Routes();
		$sitemethods = new SiteMethods();
		$pagemethods = new PageMethods();
		$fieldmethods = new FieldMethods();
		$blockmodels = new BlockModels();
		$blockmethods = new BlockMethods();
		$collections = new Collections();

		$this->hooks = $hooks();
		$this->routes = $routes();
		$this->sitemethods = $sitemethods();
		$this->pagemethods = $pagemethods();
		$this->fieldmethods = $fieldmethods();
		$this->blockmodels = $blockmodels();
		$this->blockmethods = $blockmethods();
		$this->collections = $collections();
	}

	public function register()
	{
		Kirby::plugin("weitweit/headless", [
			"hooks" => $this->hooks,
			"routes" => $this->routes,
			"siteMethods" => $this->sitemethods,
			"pageMethods" => $this->pagemethods,
			"fieldMethods" => $this->fieldmethods,
			"blockModels" => $this->blockmodels,
			"blockMethods" => $this->blockmethods,
			"collections" => $this->collections,
		]);
	}
}

$plugin = new WeitweitHeadless();
$plugin->register();
