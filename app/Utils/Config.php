<?php

namespace Athena\Utils;

/**
 * Class Config
 *
 * Handles returning all configuration arrays added to the config folder
 *
 * @version 2.0.0
 * @author erik@brightthought.co
 */
class Config {

	/**
	 * Preloads all the configurations
	 *
	 * @var array
	 */
	private static $_config = [];

	/**
	 * Gather all configuration files and store them for later use;
	 */
	public function __construct()
	{
		$configurations = glob(Constants::$ATHENA_DIR . '/config/*');
		$configurations = collect($configurations)->map(function( $file ) {
			preg_match('/..\/config\/([-a-zA-Z0-9_]+)/', $file, $match);
			return [ 'name' => $match[1], 'path' => $file];
		});

		self::$_config = $configurations->all();
	}


	/**
	 * Returns the setting file array from the config folder
	 *
	 * @param $name
	 *
	 * @return mixed|null
	 */
	public function get ( $name )
	{
		$name = explode('.', $name);
		$key = array_search($name[0], array_column(self::$_config, 'name'));

		if ($key !== false ) {
			$values = require(self::$_config[$key]['path']);
			return isset($name[1]) ?
				($values[$name[1]] ?? null) :
				($values ?? null);
		}

		return null;
	}
}

