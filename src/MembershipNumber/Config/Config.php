<?php

namespace Dcg\Client\MembershipNumber\Config;
use Dcg\Client\MembershipNumber\Exception\ConfigFileNotFoundException;
use Dcg\Client\MembershipNumber\Exception\ConfigValueNotFoundException;

class Config {

	/**
	 * @var array
	 */
	protected static $configValues = [];

	/**
	 * @var self
	 */
	protected static $instance = null;

    /**
     * singleton: return self
     *
     * @param string $configFile (Optional) The absolute filename of the config file
     * @return self
     * @throws ConfigFileNotFoundException
     */
	public static function getInstance($configFile = null) {

		$configFile = $configFile ?: (self::getRootDir() ? self::getRootDir().'/config/membership-number-client.php' : __DIR__.'/../../../config.php');

		if (is_null(self::$instance)) {
			self::setInstance();
		}
		self::init($configFile);

		return self::$instance;
	}

	/**
	 *  Set singleton instance
	 */
	private static function setInstance ()
	{
		self::$instance = new self();
	}

    /**
     *  Get values from config file
     *
     * @param string $configFile
     * @throws ConfigFileNotFoundException
     */
	private static function init ($configFile)
	{
		self::configFileToArray($configFile);
	}

	/**
	 * Get values from config file
	 *
	 * @param string $configFile The config filename
	 * @throws ConfigFileNotFoundException
	 */
	private static function configFileToArray($configFile) {

		if (file_exists($configFile)) {
			self::$configValues = require $configFile;
		} else {
			throw new ConfigFileNotFoundException("Config file could not be found at: ".$configFile);
		}
	}

	/**
	 *  Gets the values that were in the config
	 * @return array
	 */
	public static function getConfigValues ()
	{
		return self::$configValues;
	}

	/**
	 * Gets specific key fom config
	 *
	 * @param string $key	The config value identifier
	 * @param string $default (Optional) The default value if the config value is not set
	 * @throws ConfigValueNotFoundException
	 * @return string
	 */
	public static function get($key, $default = null)
	{
		if (isset(self::$configValues[$key])) {
			return self::$configValues[$key];
		} elseif ($default !== null) {
			return $default;
		} else {
			throw new ConfigValueNotFoundException("The config value was not found: ".$key);
		}
	}

	/**
	 * Gets the root dir, assumed to be once level above vendor
	 * @return bool|string
	 */
	private static function getRootDir() {
		$dir = dirname(__FILE__);
		if (false !== ($position = strpos($dir, DIRECTORY_SEPARATOR . 'vendor'))) {
			return substr($dir, 0, $position);
		}
		return false;
	}
}