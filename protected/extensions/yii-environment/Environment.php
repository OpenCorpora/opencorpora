<?php

/**
 * @name Environment
 * @author Marco van 't Wout | Tremani
 * @version 3.1
 *
 * =Environment-class=
 *
 * Original sources: http://www.yiiframework.com/doc/cookbook/73/
 *
 * Simple class used to set configuration and debugging depending on environment.
 * Using this you can predefine configurations for use in different environments,
 * like _development, testing, staging and production_.
 *
 * The main config (main.php) is extended to include the Yii paths and debug flags.
 * There are mode_<environment>.php files for overriding and extending main.php for specific environments.
 * Additionally, you can overrride the resulting config by using a local.php config, to make
 * changes that will only apply to your specific installation.
 *
 * This class was designed to have minimal impact on the default Yii generated files.
 * Minimal changes to the index/bootstrap and existing config files are needed.
 *
 * The Environment is determined with PHP's getenv(), which searches $_SERVER and $_ENV.
 * There are multiple ways to set the environment depending on your preference.
 * Setting the environment variable is trivial on both Windows and Linux, instructions included.
 * You can optionally override the environment by creating a mode.php in the config directory.
 *
 * If you want to customize this class or its config and modes, extend it! (see ExampleEnvironment.php)
 *
 * ==Installation==
 *
 *  # Put the yii-environment directory in `protected/extensions/`
 *  # Modify your index.php (and other bootstrap files)
 *  # Modify your main.php config file and add mode specific configs
 *  # Set your local environment
 *
 * ==Setting environment==
 *
 * Here are some examples for setting your environment to DEVELOPMENT.
 *
 *  * Windows:
 *    # Go to: Control Panel > System > Advanced > Environment Variables
 *    # Add new SYSTEM variable: name = YII_ENVIRONMENT, value = DEVELOPMENT
 *    * Details: http://support.microsoft.com/kb/310519/en-us
 *  * Linux:
 *    # Modify your profile file:
 *      * Locally: ~/.profile or ~/.bash_profile (exact filename depends on your linux distro)
 *      * Globally: /etc/profile
 *      * Apache: /etc/apache2/envvars (if apache process doesn't use bash shell, try this file)
 *    # Add: export YII_ENVIRONMENT="DEVELOPMENT"
 *    * Details: http://www.cyberciti.biz/faq/linux-unix-set-java_home-path-variable/
 *  * Apache only: (cannot be used for console applications)
 *    # Check if mod_env is enabled
 *    # Modify your httpd.conf or create a .htaccess file
 *    # Add: SetEnv YII_ENVIRONMENT DEVELOPMENT
 *    * Details: http://httpd.apache.org/docs/1.3/mod/mod_env.html#setenv
 *  * Project only:
 *    # Create a file `mode.php` in the config directory of your application.
 *    # Set the contents of the file to: DEVELOPMENT
 *
 * Q: After setting environment var, I get "Environment cannot be determined" when accessing the web application.
 * A: Make sure that where the Apache process starts, it can access the environment variable (by setting it as a system/global var).
 *
 * ===Index.php usage example:===
 *
 * See `yii-environment/example-index/` or use the following code block:
 *
 * {{{
 * <?php
 * // set environment
 * require_once(dirname(__FILE__) . '/protected/extensions/yii-environment/Environment.php');
 * $env = new Environment();
 * //$env = new Environment('PRODUCTION'); //override mode
 *
 * // set debug and trace level
 * defined('YII_DEBUG') or define('YII_DEBUG', $env->yiiDebug);
 * defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', $env->yiiTraceLevel);
 *
 * // run Yii app
 * //$env->showDebug(); // show produced environment configuration
 * require_once($env->yiiPath);
 * $env->runYiiStatics(); // like Yii::setPathOfAlias()
 * Yii::createWebApplication($env->configWeb)->run();
 * }}}
 *
 * ===Structure of config directory===
 *
 * Your `protected/config/` directory will look like this:
 *
 *  * config/main.php                     (Global configuration)
 *  * config/mode_development.php         (Environment-specific configurations)
 *  * config/mode_test.php
 *  * config/mode_staging.php
 *  * config/mode_production.php
 *  * config/local.php                    (Optional, local override for mode-specific config. Don't put in your SVN!)
 *
 * ===Modify your config/main.php===
 *
 * See `yii-environment/example-config/` or use the following code block:
 * Optional: in configConsole you can copy settings from configWeb by
 * using value key `inherit` (see examples folder).
 *
 * {{{
 * <?php
 * return array(
 *     // Set yiiPath (relative to Environment.php)
 *     'yiiPath' => dirname(__FILE__) . '/../../../yii/framework/yii.php',
 *     'yiicPath' => dirname(__FILE__) . '/../../../yii/framework/yiic.php',
 *     'yiitPath' => dirname(__FILE__) . '/../../../yii/framework/yiit.php',
 *
 *     // Set YII_DEBUG and YII_TRACE_LEVEL flags
 *     'yiiDebug' => true,
 *     'yiiTraceLevel' => 0,
 *
 *     // Static function Yii::setPathOfAlias()
 *     'yiiSetPathOfAlias' => array(
 *         // uncomment the following to define a path alias
 *         //'local' => 'path/to/local-folder'
 *     ),
 *
 *     // This is the main Web application configuration. Any writable
 *     // CWebApplication properties can be configured here.
 *     'configWeb' => array(
 *         (...)
 *     ),
 *
 *     // This is the Console application configuration. Any writable
 *     // CConsoleApplication properties can be configured here.
 *     // Leave array empty if not used.
 *     // Use value 'inherit' to copy from generated configWeb.
 *     'configConsole' => array(
 *         (...)
 *     ),
 * );
 * }}}
 *
 * ===Create mode-specific config files===
 *
 * Create `config/mode_<mode>.php` files for the different modes
 * These will override or merge attributes that exist in the main config.
 * Optional: also create a `config/local.php` file for local overrides
 *
 * {{{
 * <?php
 * return array(
 *     // Set yiiPath (relative to Environment.php)
 *     //'yiiPath' => dirname(__FILE__) . '/../../../yii/framework/yii.php',
 *     //'yiicPath' => dirname(__FILE__) . '/../../../yii/framework/yiic.php',
 *     //'yiitPath' => dirname(__FILE__) . '/../../../yii/framework/yiit.php',
 *
 *     // Set YII_DEBUG and YII_TRACE_LEVEL flags
 *     'yiiDebug' => true,
 *     'yiiTraceLevel' => 0,
 *
 *     // Static function Yii::setPathOfAlias()
 *     'yiiSetPathOfAlias' => array(
 *         // uncomment the following to define a path alias
 *         //'local' => 'path/to/local-folder'
 *     ),
 *
 *     // This is the main Web application configuration. Any writable
 *     // CWebApplication properties can be configured here.
 *     'configWeb' => array(
 *         (...)
 *     ),
 *
 *     // This is the Console application configuration. Any writable
 *     // CConsoleApplication properties can be configured here.
 *     // Leave array empty if not used
 *     // Use value 'inherit' to copy from generated configWeb
 *     'configConsole' => array(
 *         (...)
 *     ),
 * );
 * }}}
 *
 */

class Environment
{
	/**
	 * Inherit key that can be used in configConsole
	 */
	const INHERIT_KEY = 'inherit';

	/**
	 * @var string name of env var to check
	 */
	protected $envVar = 'YII_ENVIRONMENT';

	/**
	 * @var string config dir (relative to Environment.php)
	 */
	protected $configDir = '../../config/';

	/**
	 * @var string selected environment mode
	 */
	protected $mode;

	/**
	 * @var string path to file (relative to Environment.php) that overrides environment, if exists
	 */
	protected $modeFile = '../../config/mode.php';

	/**
	 * @var string path to yii.php
	 */
	public $yiiPath;
	/**
	 * @var string path to yiic.php
	 */
	public $yiicPath;
	/**
	 * @var string path to yiit.php
	 */
	public $yiitPath;
	/**
	 * @var int debug level
	 */
	public $yiiDebug;
	/**
	 * @var int trace level
	 */
	public $yiiTraceLevel;
	/**
	 * @see http://www.yiiframework.com/doc/api/1.1/YiiBase#setPathOfAlias-detail
	 * @var array array with "$alias=>$path" elements
	 */
	public $yiiSetPathOfAlias = array();
	/**
	 * @var array web config array
	 */
	public $configWeb;
	/**
	 * @var array console config array
	 */
	public $configConsole;

	/**
	 * Extend Environment class and merge parent array if you want to modify/extend these
	 * @return array list of valid modes
	 */
	function getValidModes()
	{
		return array(
			100 => 'DEVELOPMENT',
			200 => 'TEST',
			300 => 'STAGING',
			400 => 'PRODUCTION'
		);
	}

	/**
	 * Initilizes the Environment class with the given mode
	 * @param constant $mode used to override automatically setting mode
	 */
	function __construct($mode = null)
	{
		$this->setMode($mode);
		$this->setEnvironment();
	}

	/**
	 * Set current environment mode depending on environment variable.
	 * Also checks if there is a mode file that might override this environment.
	 * Override this function if you want to change this method.
	 * @param string $mode if left empty, determine automatically
	 */
	protected function setMode($mode = null)
	{
		// If not overridden
		if ($mode === null)
		{
			$modeFilePath = dirname(__FILE__).DIRECTORY_SEPARATOR.$this->modeFile;
			if (file_exists($modeFilePath)) {
				// Is there a mode file?
				$mode = trim(file_get_contents($modeFilePath));
			} else {
				// Else, return mode based on environment var
				$mode = getenv($this->envVar);
				if ($mode === false)
					throw new Exception('"Environment mode cannot be determined, see class for instructions.');
			}
		}

		// Check if mode is valid
		$mode = strtoupper($mode);
		if (!in_array($mode, $this->getValidModes(), true))
			throw new Exception('Invalid environment mode supplied or selected.');

		$this->mode = $mode;
	}

	/**
	 * Get full config dir
	 * @return string absolute path to config dir with trailing slash
	 */
	protected function getConfigDir()
	{
		return dirname(__FILE__).DIRECTORY_SEPARATOR.$this->configDir.DIRECTORY_SEPARATOR;
	}

	/**
	 * Load and merge config files into one array.
	 * @return array $config array to be processed by setEnvironment.
	 */
	protected function getConfig()
	{
		// Load main config
		$fileMainConfig = $this->getConfigDir().'main.php';
		if (!file_exists($fileMainConfig))
			throw new Exception('Cannot find main config file "'.$fileMainConfig.'".');
		$configMain = require($fileMainConfig);

		// Load specific config
		$fileSpecificConfig = $this->getConfigDir().'mode_'.strtolower($this->mode).'.php';
		if (!file_exists($fileSpecificConfig))
			throw new Exception('Cannot find mode specific config file "'.$fileSpecificConfig.'".');
		$configSpecific = require($fileSpecificConfig);

		// Merge specific config into main config
		$config = self::mergeArray($configMain, $configSpecific);

		// If one exists, load and merge local config
		$fileLocalConfig = $this->getConfigDir().'local.php';
		if (file_exists($fileLocalConfig)) {
			$configLocal = require($fileLocalConfig);
			$config = self::mergeArray($config, $configLocal);
		}

		// Return
		return $config;
	}

	/**
	 * Sets the environment and configuration for the selected mode.
	 */
	protected function setEnvironment()
	{
		$config = $this->getConfig();

		// Set attributes
		$this->yiiPath = $config['yiiPath'];
		if (isset($config['yiicPath']))
			$this->yiicPath = $config['yiicPath'];
		if (isset($config['yiitPath']))
			$this->yiitPath = $config['yiitPath'];
		$this->yiiDebug = $config['yiiDebug'];
		$this->yiiTraceLevel = $config['yiiTraceLevel'];
		$this->configWeb = $config['configWeb'];
		$this->configWeb['params']['environment'] = strtolower($this->mode);

		// Set console attributes and related actions
		if (isset($config['configConsole']) && !empty($config['configConsole'])) {
			$this->configConsole = $config['configConsole'];
			$this->processInherits($this->configConsole); // Process configConsole for inherits
			$this->configConsole['params']['environment'] = strtolower($this->mode);
		}

		// Set Yii statics
		$this->yiiSetPathOfAlias = $config['yiiSetPathOfAlias'];
	}

	/**
	 * Run Yii static functions.
	 * Call this function after including the Yii framework in your bootstrap file.
	 */
	public function runYiiStatics()
	{
		// Yii::setPathOfAlias();
		foreach($this->yiiSetPathOfAlias as $alias => $path) {
			Yii::setPathOfAlias($alias, $path);
		}
	}

	/**
	 * Show current Environment class values
	 */
	public function showDebug()
	{
		echo '<div style="position: absolute; bottom: 0; left: 0; z-index: 99999; height: 250px; overflow: auto; background-color: #ddd; color: #000; border: 1px solid #000; margin: 5px; padding: 5px;">
			<pre>'.htmlspecialchars(print_r($this, true)).'</pre></div>';
	}

	/**
	 * Merges two arrays into one recursively.
	 * @param array $a array to be merged to
	 * @param array $b array to be merged from
	 * @return array the merged array (the original arrays are not changed.)
	 *
	 * Taken from Yii's CMap::mergeArray, since php does not supply a native
	 * function that produces the required result.
	 * @see http://www.yiiframework.com/doc/api/1.1/CMap#mergeArray-detail
	 */
	protected static function mergeArray($a,$b)
	{
		foreach($b as $k=>$v)
		{
			if(is_integer($k))
				$a[]=$v;
			else if(is_array($v) && isset($a[$k]) && is_array($a[$k]))
				$a[$k]=self::mergeArray($a[$k],$v);
			else
				$a[$k]=$v;
		}
		return $a;
	}

	/**
	 * Loop through console config array, replacing values called 'inherit' by values from $this->configWeb
	 * @param type $array target array
	 * @param type $path array that keeps track of current path
	 */
	private function processInherits(&$array, $path = array())
	{
		foreach($array as $key => &$value) {
			if (is_array($value))
				$this->processInherits($value, array_merge($path, array($key)));

			if ($value === self::INHERIT_KEY)
				$value = $this->getValueFromArray($this->configWeb, array_reverse(array_merge($path, array($key))));
		}
	}

	/**
	 * Walk $array through $path until the end, and return value
	 * @param array $array target
	 * @param array $path path array, from deep key to shallow key
	 * @return mixed
	 */
	private function getValueFromArray(&$array, $path)
	{
		if (count($path)>1) {
			$key = end($path);
			return $this->getValueFromArray($array[array_pop($path)], $path);
		} else {
			return $array[reset($path)];
		}

	}

}