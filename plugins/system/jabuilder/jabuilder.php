<?php
/**
 *------------------------------------------------------------------------------
 * @package       JoomlArt Uber Builder
 *------------------------------------------------------------------------------
 * @copyright     Copyright (C) 2004-2013 JoomlArt.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       JoomlArt, JoomlaBamboo, (contribute to this project at github
 *                & Google group to become co-author)
 * @Google group: https://groups.google.com/forum/#!forum/t3fw
 * @Link:         http://t3-framework.org
 *------------------------------------------------------------------------------
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * JUB plugin class
 *
 * @package        JUB
 */

define ('JUB_PROVIDER', 'joomlart');

require_once dirname(__FILE__) . '/helper.php';

class plgSystemJabuilder extends JPlugin
{
	var $helper;
	var $siteid;

	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->helper = new JUBHelper($this->params);
	}

	/**
	 * Switch template for thememagic
	 */
	function onAfterDispatch()
	{
		// Builder Url
		if (!defined('JUB_BUILDER_URL')) {
			$https = false;
			if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
				$https = true; 
			}
			define('JUB_BUILDER_URL', ($https ? 'https' : 'http') . '://themepro.com/builder');
		}

		if (!$this->helper->isJUB()) return;

		$app = JFactory::getApplication();
		if ($app->isAdmin()) return;

		$action = $this->helper->jubMode();

		if ($action) {
			// admin action
			if ($this->helper->isEditable()) {
				$func = 'do' . ucfirst($action);
				// create table revision if not existed
				$this->helper->checkRevisionsTableExisted();
				switch ($action) {
					case 'edit':
						$this->siteid = $this->helper->getSiteId();
						$html = JLayoutHelper::render('jabuilder.edit', array('helper'=>$this->helper), __DIR__ . '/html');
						$dispatcher = JEventDispatcher::getInstance();
						$dispatcher->trigger('onJubEditRender', array (&$html));
						echo $html;
						$app->close();
						break;

					default:
						if (method_exists($this->helper, $func)) {
							$result = $this->helper->$func();

							if (is_array($result)) {
								$this->helper->outputJson ($result);
							} else if (is_string($result)) {
								echo $result;
							}
							$app->close();
						}
						break;
				}
			}

			// user action
			$func = 'doUser' . ucfirst($action);
			if (method_exists($this->helper, $func)) {
				$result = $this->helper->$func();

				if (is_array($result)) {
					$this->helper->outputJson ($result);
				} else if (is_string($result)) {
					echo $result;
				}
				$app->close();
			}
		}

	}

	function onBeforeRender() {
		$app = JFactory::getApplication();
		if ($app->isAdmin()) return;
		// if in popup, ignore
		// if ($app->input->request->get('tmpl') == 'component') return;
		
		$doc = JFactory::getDocument();
		$plg_url = JUri::root(true) . '/plugins/system/jabuilder';
		$doc->addStyleSheet($plg_url . '/assets/css/jabuilder.css');
		$doc->addScript($plg_url . '/assets/js/jabuilder.js');
		if ($this->helper->isJUB() && !$this->helper->inEditMode() && $this->helper->hasPermission() && $app->input->get('preview') != 1) {
			$editbtn = JLayoutHelper::render('jabuilder.edit-button', null, __DIR__ . '/html');
			$script = 'var JUB_EDIT_BUTTON = \'' . json_encode($editbtn) . '\';';
			$doc->addScriptDeclaration($script);
		}

		if (!$this->helper->inEditMode()) {
			// material fonts
			$doc->addStyleSheet('https://fonts.googleapis.com/icon?family=Material+Icons');
			// add JUB assets
			if ($this->helper->isJUB()) {
				$this->helper->addJUBPageAssets();
			}

			if ($this->helper->isJUBPage()) {
				// Parse JUB render
				$this->helper->parseJUBPage();
			}

		}
	}

	function onAfterRender() {
		// inject custom code
		$this->helper->addCustomCode();

		// remove joomla bootstrap if there jub bootstrap
		$app = JFactory::getApplication();
		$body = $app->getBody();
		if (preg_match('#bootstrap/js/bootstrap\.min\.js#', $body)) {
			// remove jub bootstrap
			$body = preg_replace('#<script[^>]*/media/jui/js/bootstrap.min.js"[^<]*</script>#', '', $body);
			$app->setBody($body);
		}
	}

	function onExtensionBeforeSave ($context, $table, $isNew) {
		if ($context == 'com_templates.style' && $isNew && $table->template == 'ja_builder') {
			// reset jub-layout params
			$params = @json_decode($table->params, true);
			if (isset($params['jub-layout'])) unset($params['jub-layout']);
			$table->params = json_encode($params);
		}

		if ($context == 'com_config.component' && $table->element == 'com_jabuilder') {
			// var_dump($table->params);die;
			// get current jabuilder component params
			jimport('joomla.application.component.helper');
			// Load the current component params.
			$oldParams = JComponentHelper::getParams('com_jabuilder')->toArray();

			// update custom params, which is store by the plugin
			$params = @json_decode($table->params, true);
			foreach ($oldParams as $name => $value) {
				if (!isset($params[$name])) $params[$name] = $value;
			}
			// store to table
			$table->params = json_encode($params);
		}
	}

}
