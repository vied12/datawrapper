<?php

class DatawrapperPlugin {

	private $__name;
	private $__packageJson;

	function __construct($name = null) {
		if (isset($name)) $this->__name = $name;
	}

	/** register events */
	public function init() {
		return true;
	}

	/**
	* Enable the plugin,
	* Check if there is a static folder,
	* Copy the content to www/static/plugins/<plugin_name>/
	*/
	public function install() {
		$plugin = PluginQuery::create()->findPK($this->getName());
		if (empty($plugin)) {
			$plugin = new Plugin();
			$plugin->setId($this->getName());
		}
		$plugin->setEnabled(true);
		$plugin->setInstalledAt(time());
		$plugin->save();

		$this->copyStaticFiles();
		$this->copyTemplates();
	}

	/*
	 * copys all files from a plugins "static" directory to
	 * the publicly visible /www/static/plugins/%PLUGIN%/
	 */
	private function copyStaticFiles() {
		// check if there's a /static in plugin directory
		$source_path = ROOT_PATH . 'plugins/' . $this->getName() . '/static';
		if (!file_exists($source_path)) return;

		// create directory in www/static/plugins/ if not exists
		$plugin_static_path = ROOT_PATH . 'www/static/plugins/' . $this->getName();
		if (!file_exists($plugin_static_path)) {
			mkdir($plugin_static_path);
		}
		// copy static files to that directory
		copy_recursively($source_path, $plugin_static_path);
	}

	/*
	 * copys all plugin templates to /templates/plugin/%PLUGIN%/
	 */
	private function copyTemplates() {
		// check if there's a /templates in plugin directory
		$source_path = ROOT_PATH . 'plugins/' . $this->getName() . '/templates';
		if (!file_exists($source_path)) return;

		// create directory in /templates/plugins/ if not exists
		$plugin_template_path = ROOT_PATH . 'templates/plugins/' . $this->getName();
		if (!file_exists($plugin_template_path)) {
			mkdir($plugin_template_path);
		}
		copy_recursively($source_path, $plugin_template_path);
	}

	private function getPluginOM() {
		return PluginQuery::create()->findPK($this->getName());
	}

	/**
	* Disable the plugin
	*/
	public function uninstall() {
		$plugin = $this->getPluginOM();
		if ($plugin) {
			$plugin->delete();
			// TODO:
			// $this->removeStaticFiles();
			// $this->removeTemplates();
		}
	}

	public function disable() {
		$plugin = PluginQuery::create()->findPK($this->getName());
		if ($plugin) {
			$plugin->setEnabled(false);
			$plugin->save();
		}
	}

	/*
	 * loads and caches the plugins package.json
	 */
	private function getPackageJSON() {
		if (!empty($this->__packageJson)) return $this->__packageJson;
		$reflector = new ReflectionClass(get_class($this));
		$dirname   = dirname($reflector->getFileName());
		$meta      = json_decode(file_get_contents($dirname . "/package.json"),true);
		$this->__packageJson = $meta;
		return $meta;
	}

	/*
	 * returns the version of the plugin
	 */
	public function getVersion() {
		$meta = $this->getPackageJSON();
		return $meta['version'];
	}

	/*
	 * returns the name (id) of this plugin
	 */
	public function getName() {
		if (!isset($this->__name)) {
			$reflector = new ReflectionClass(get_class($this));
			$dirname   = dirname($reflector->getFileName());
			$this->__name = substr($dirname, strrpos($dirname, DIRECTORY_SEPARATOR)+1);
		}
		return $this->__name;
	}

	/*
	 * returns the plugin specific configuration (from config.yaml)
	 */
	public function getConfig() {
		if (isset($GLOBALS['dw_config']['plugins'][$this->getName()])) {
			return $GLOBALS['dw_config']['plugins'][$this->getName()];
		}
		return array();
	}

	/*
	 * returns a list of PHP files that needs to be included
	 */
	public function getRequiredLibraries() {
		return array();
	}

	/**
	 * allows the plugin to persistently store arbitrary data
     *
	 * @param key     string           a key
	 * @param data    json_seriazable  the data thats being stored. must be json serializable
	 * @param single  boolean          if set, any existing value with the same key will be overwritten
	 */
	public function storeData($key, $data, $single = true) {
		$pd = PluginDataQuery::create()
			->filterByPlugin($this->getPluginOM())
			->filterByKey($key)
			->find();

		if ($single) {
			// remove any existing value
			PluginDataQuery::create()
			  ->filterByPlugin($this->getPluginOM())
			  ->filterByKey($key)
			  ->delete();
		}
		$pd = new PluginData();
		$pd->setPlugin($this->getPluginOM());
		$pd->setKey($key);
		$pd->setData($data);
		$pd->setStoredAt(time());
		$pd->save();
	}

	/**
	 * Read data from persistant plugin data store
	 *
	 * @param key   string   the key
	 * @param single  boolean   if set true readData will only return the last inserted first row
	 */
	public function readData($key, $single=true) {
		$q = PluginDataQuery::create()
			->filterByPlugin($this->getPluginOM())
			->filterByKey($key)
			->orderByStoredAt('desc')
			->find();

		if (empty($q)) return null;
		if (!$single) {
			$res = array();
			foreach ($q as $pd) {
				$res[] = $pd->getData();
			}
		} else $res = $q[0]->getData();
		return $res;
	}

	/**
	 * Remove data from persistant plugin data store
	 *
	 * @param key   string   the key
	 * @param data  json_seriazable   if set only matching items will be removed
	 */
	public function deleteData($key, $data = null) {
		$q = PluginDataQuery::create()
		  ->filterByPlugin($this->getPluginOM())
		  ->filterByKey($key);
		if ($data !== null) {
			$q->filterByData(json_encode($data));
		}
		$q->delete();
	}
}

