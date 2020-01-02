<?php
/**
 * @package      JA Admin
 *
 * @author       JoomlArt
 * @copyright    Copyright (C) 2012-2013. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

class pkg_ja_builderInstallerScript
{
	function __construct() {
		$this->is_old_version = version_compare(JVERSION, '3.4', '<');
	}
	/**
	 * Called before any type of action
	 *
	 * @param     string              $route      Which action is happening (install|uninstall|discover_install)
	 * @param     jadapterinstance    $adapter    The object responsible for running this script
	 *
	 * @return    boolean                         True on success
	 */
	public function preflight($route, JAdapterInstance $adapter)
	{
		return true;
	}

	function uninstall($adapter) {
		define('JA_PACKAGE_UNINSTALL', 1);
		if ($this->is_old_version) {
			return;
		}
		$row = JTable::getInstance('extension');
		$row->load(array('element'=>'pkg_ja_builder'));
		$xml_file = JPATH_MANIFESTS . '/packages/' . $row->get('element') . '.xml';

		if (!file_exists($xml_file)) {
			return;
		}

		$xml = simplexml_load_file($xml_file);
		$adapter->setManifest($xml);
		JFactory::getApplication()->enqueueMessage($this->getScript('uninstall', $adapter));
	}

	/**
	 * Called after any type of action
	 *
	 * @param     string              $route      Which action is happening (install|uninstall|discover_install)
	 * @param     jadapterinstance    $adapter    The object responsible for running this script
	 *
	 * @return    boolean                         True on success
	 */
	public function postflight($route, JAdapterInstance $adapter)
	{
			// Enable the helper plugin right after install it
		if ( $route == 'install' ) 
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->update('#__extensions')
			->set('enabled=1')
			->where(array('element=' . $db->quote('jabuilder'), 'type=' . $db->quote('plugin')));
			$db->setQuery($query);   
			$db->execute();     
		}

		if ($this->is_old_version) {
			return;
		}
		echo $this->getScript($route, $adapter);
	}

	function getScript($route, $adapter) {
		$doc = JFactory::getDocument();
		$data = $this->getData($route, $adapter);
		$msg = $this->getStatsMessage($data);
		$script = $this->getStatsScript($data);
		$doc->addScriptDeclaration('var jabuilder_stats_tmpl = '.json_encode($msg).';');
		$result = "<script>var jabuilder_stats_tmpl =".json_encode($msg).";".$script."</script>";
		return $result;
	}

	function getStatsScript($data) {
		$script = "jQuery(document).ready(function ($) {
			var messageContainer = $('#system-message-container');
			messageContainer.append(jabuilder_stats_tmpl);

			var globalContainer = messageContainer.find('.js-jabuilder-stats-alert');
			var detailsContainer = messageContainer.find('.js-jabuilder-stats-data-details');
				
			globalContainer.show(300);
			
			messageContainer.on('click', '.js-jabuilder-stats-btn-details', function (e) {
				detailsContainer.toggle(200);
				e.preventDefault();
			});
			
			var yesbtn = globalContainer.find('.js-jabuilder-stats-btn-yes');
			var nobtn = globalContainer.find('.js-jabuilder-stats-btn-no');
			
			yesbtn.on('click', function(e) {
				e.preventDefault();
				globalContainer.hide(200);
				detailsContainer.remove();
				$.post('http://metrics.joomlart.com/',{ data: '".json_encode($data)."' } )
			});
			
			nobtn.on('click', function(e) {
				e.preventDefault();
				globalContainer.hide(200);
				detailsContainer.remove();
			});
		})";
		return $script;
	}

	function getStatsMessage($data) {
		$tmpl = '<div class="alert alert-info js-jabuilder-stats-alert hide" >
			<button data-dismiss="alert" class="close" type="button">×</button>
			<h2>JoomlArt would like your permission to collect some basic statistics.</h2>
			<p>
				This extension collects anonymous data comprising server and joomla environment.
				<a href="#" class="js-jabuilder-stats-btn-details alert-link">Click here to see the information that will be sent.</a>
			</p>
			<dl class="dl-horizontal js-jabuilder-stats-data-details hide">
				<dt>Identifier</dt>
				<dd>'. $data->identifier .'</dd>
				<dt>PHP</dt>
				<dd>'. $data->php .'</dd>
				<dt>Database Type</dt>
				<dd>'. $data->database_type .'</dd>
				<dt>Database Version</dt>
				<dd>'. $data->database_version .'</dd>
				<dt>Server</dt>
				<dd>'. $data->server .'</dd>
				<dt>Server Interface</dt>
				<dd>'. $data->server_interface .'</dd>
				<dt>Joomla</dt>
				<dd>'. $data->joomla .'</dd>
				<dt>Method</dt>
				<dd>'. $data->method .'</dd>
			</dl>';
			foreach($data->extension as $ext) {
				$tmpl .= '<dl class="dl-horizontal js-jabuilder-stats-data-details hide">
					<dt>Extension</dt>
					<dd>'. $ext->name .'</dd>
					<dt>Type</dt>
					<dd>'. $ext->type .'</dd>
					<dt>Element</dt>
					<dd>'. $ext->element .'</dd>
					<dt>Version</dt>
					<dd>'. $ext->version .'</dd>
				</dl>';
			}

			$tmpl .= '<p>Allow to send Statistics?</p>
			<p class="actions">
				<a href="#" class="btn js-jabuilder-stats-btn-yes">'. JText::_('JYES').'</a>
				<a href="#" class="btn js-jabuilder-stats-btn-no">'. JText::_('JNO').'</a>
			</p>
		</div> ';
		return $tmpl;
	}

	function getData($route, $adapter) {
		$configuration = JFactory::getConfig();
		$secret = version_compare(JVERSION, '2.5', 'ge') ? $configuration->get('secret') : $configuration->getValue('config.secret');
		$data = new stdClass();
		$data->identifier = md5($secret.$_SERVER['SERVER_ADDR']);
		$data->php = phpversion();
		$data->database_type = $this->getDbType();
		$data->database_version = $this->getDbVersion();
		$data->server = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : getenv('SERVER_SOFTWARE');
		$data->server_interface = php_sapi_name();
		$data->joomla = JVERSION;
		$data->method = $route;

		$extension = array();
		foreach ($adapter->getManifest()->files->children() as $child) {
			$ext = new stdClass();
			$attr = $child->attributes();
			$ext->element = (string) $attr['id'];
			$ext->type = (string) $attr['type'];

			if ($route === 'uninstall') {
				$cache = $this->getExtension($child);
				if (!empty($cache)) {
					$ext->name = $cache->name;
					$ext->version = $cache->version;
				} else {
					$ext->name = 'undifined';
					$ext->version = 'undifined';
				}
			} else {
				$child_xml = $this->getChildXml($child);
				if (!empty($child_xml)) {
					$ext->name = (string) $child_xml->name;
					$ext->version = (string) $child_xml->version;
				}
			}
			$extension[] = $ext;
		}
		
		$data->extension = $extension;
		return $data;
	}

	function getDbType() {
		$configuration = JFactory::getConfig();
		$type = version_compare(JVERSION, '2.5', 'ge') ? $configuration->get('dbtype') : $configuration->getValue('config.dbtype');
		if($type == 'mysql' || $type == 'mysqli' || $type == 'pdomysql')
		{
			$query = 'SELECT version();';
			$db = JFactory::getDbo()->setQuery($query);
			$result = $db->loadResult();
			$result = strtolower($result);
			if(strpos($result, 'mariadb') !== false)
			{
				$type = 'mariadb';
			}
		}
		return $type;
	}

	function getDbVersion() {
		$db = JFactory::getDbo();
		return $db->getVersion();
	}

	function getChildXml($child) {
		$attr = $child->attributes();
		switch ($attr['type']) {
			case 'plugin':
			case 'module':
				
				$xml_file = dirname(__FILE__) . '/' . $child[0] . '/' . $attr['id'] . '.xml';
				break;
			
			case 'component':
				$xml_file = dirname(__FILE__) . '/' . $child[0] . '/' . substr($attr['id'], 4) . '.xml';
				break;

			case 'template':
				$xml_file = dirname(__FILE__) . '/' . $child[0] . '/templateDetails.xml';
				break;

			default:
				$xml_file = '';
		}
		if (file_exists($xml_file)) {
			return simplexml_load_file($xml_file);
		}
		
		return;
	}

	function getExtension($child) {
		$attr = $child->attributes();
		$q = 'SELECT manifest_cache FROM `#__extensions` WHERE type="'.$attr['type'].'" AND element="'.$attr['id'].'"';
		$db = JFactory::getDbo()->setQuery($q);
		$manifest = $db->loadResult();
		return json_decode($manifest);
	}
}

