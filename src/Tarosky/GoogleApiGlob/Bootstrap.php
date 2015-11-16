<?php

namespace Tarosky\GoogleApiGlob;
use Tarosky\GoogleApiGlob\Commands\Glob;

/**
 * Entry point
 *
 * @package Tarosky\GoogleApiGlob
 */
class Bootstrap {

	/**
	 * Avoid new.
	 */
	private function __construct() {}

	/**
	 * Entry point
	 */
	public static function init() {
		Glob::register();
	}

}
