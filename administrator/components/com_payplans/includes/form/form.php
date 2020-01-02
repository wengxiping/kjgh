<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PPForm
{
	private $path = null;
	private $renderer = null;
	private $type = null;

	public function __construct($type)
	{
		$this->type = $type;
	}

	/**
	 * Bind the params
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function bind($params = null)
	{
		if (is_object($params)) {
			$this->params = $params;

			return;
		}

		if (is_file($params)) {
			$params = JFile::read($params);
		}

		$this->params = PP::Registry($params);
	}

	/**
	 * Loads the form given the path to the file
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function load($path, $data)
	{
		$contents = JFile::read($path);
		$sections = json_decode($contents);

		if (!$sections) {
			return false;
		}

		$this->renderer = $this->getRenderer($sections, $data);
	}

	/**
	 * Retrieves the renderer for the specific type of form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRenderer($sections, $data)
	{
		static $renderers = array();

		if (!isset($renderers[$this->type])) {
			$path = __DIR__ . '/renderers/' . $this->type . '.php';
			require_once($path);

			$renderers[$this->type] = true;
		}

		$className = 'PPFormRenderer' . ucfirst($this->type);
		$renderer = new $className($sections, $data);

		return $renderer;
	}

	/**
	 * Process legacy xml file
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processLegacyXml($path)
	{
		$contents = JFile::read($path);

		$xml = simplexml_load_string($contents);
		$json = json_encode($xml);
		$data = json_decode($json);

		$fields = array();

		if (isset($data->fields->fieldset->field)) {
			foreach ($data->fields->fieldset->field as $field) {
				$fields[] = $field;
			}
		}

		dump($fields);
	}

	/**
	 * Renders the form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function render()
	{
		return $this->renderer->render();
	}
}
