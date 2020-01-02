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

require_once(__DIR__ . '/abstract.php');

class PPFormRendererCustomDetails extends PPFormRendererAbstract
{
	private $sections = null;
	private $data = null;
	
	public function __construct($xml, $data)
	{
		$this->data = $data;
		$this->sections = $this->convert($xml);
	}

	/**
	 * Conversion of Payplans 3.x xml into proper object
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function convert($xml)
	{
		$sections = array();

		$fields = $xml->fields;

		foreach ($fields->fieldset as $fieldset) {
			
			$section = new stdClass();
			$section->attributes = array();
			
			foreach ($fieldset->attributes() as $key => $value) {
				$section->attributes[$key] = (string) $value;
			}

			$section->label = PP::normalize($section->attributes, 'label', '');
			$section->key = PP::normalize($section->attributes, 'name', '');
			$section->items = array();

			$children = $fieldset->children();

			foreach ($fieldset->children() as $child) {
				$item = new stdClass();
				$item->attributes = array();

				foreach ($child->attributes() as $key => $value) {

					if ($key == 'require') {
						$key = 'required';
					}

					$item->attributes[$key] = (string) $value;
				}

				$item->id = '';
				$item->name = PP::normalize($item->attributes, 'name', '');
				$item->type = PP::normalize($item->attributes, 'type', '');

				// Backward compatible with payplans 3.x
				if ($item->type == 'list') {
					$item->type = 'lists';
				}

				$class = PP::normalize($item->attributes, 'class', '');

				if ($item->type == 'radio' && $class == 'btn-group') {
					$item->type = 'toggler';
				}

				$item->title = PP::normalize($item->attributes, 'label', '');
				$item->tooltip = PP::normalize($item->attributes, 'description', '');
				$item->default = PP::normalize($item->attributes, 'default', '');
				$item->value = $this->data->get($item->name, '');

				$item->options = array();

				if ($child->option) {
					foreach ($child->option as $childOption) {
						$option = new stdClass();
						$option->value = (string) $childOption['value'];
						$option->title = (string) $childOption;

						$item->options[] = $option;
					}
				}
				
				$section->items[] = $item;
			}

			$sections[] = $section;
		}

		return $sections;
	}

	/**
	 * Renders the form's output
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function render($site = false, $title = '', $type = 'userparams')
	{
		if (!$this->sections) {
			return false;
		}

		$theme = PP::themes();
		$theme->set('title', $title);
		$theme->set('sections', $this->sections);
		$theme->set('type', $type);
		
		$namespace = 'admin/forms/renderer/customdetails';

		if ($site) {
			$namespace = 'site/forms/renderers/customdetails';
		}
		
		$contents = $theme->output($namespace);

		return $contents;
	}
}
