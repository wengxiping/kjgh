<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/fields/dependencies');

class SocialFieldsUserHtml extends SocialFieldItem
{
	/**
	 * When admin edit the object, render an editable form
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function onAdminEdit(&$post, &$user, $errors)
	{
		// Get the value.
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : $this->value;

		// Get the error.
		$error = $this->getError($errors);

		// Set the value.
		$this->set('value', $this->escape($value));
		$this->set('error', $error);

		// Manually override the readonly parameter for admin
		$this->params->set('readonly', false);
		$this->set('params', $this->params);

		return $this->display('form');
	}

	/**
	 * Allow html codes since this is only being set by the admin
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function onAdminEditBeforeSave(&$post)
	{
		$post[$this->inputName] = $this->input->get($this->inputName, '', 'raw');
	}

	public function onRegister()
	{
		return $this->render();
	}

	public function onEdit()
	{
		return $this->render();
	}

	public function onDisplay()
	{
		return $this->render();
	}

	/**
	 * Returns formatted value for GDPR
	 *
	 * @since  2.2
	 * @access public
	 */
	public function onGDPRExport($user)
	{
		$content = $this->params->get('html');

		// If the admin specified a custom value
		if ($this->value) {
			$content = $this->value;
		}

		// retrieve field data
		$field = $this->field;

		$data = new stdClass;
		$data->fieldId = $field->id;
		$data->value = $content;

		return $data;
	}

	public function render()
	{
		$content = $this->params->get('html');

		// If the admin specified a custom value
		if ($this->value) {
			$content = $this->value;
		}
		
		$this->set('content', $content);

		return $this->display();
	}
}
