<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

class JMenuItem extends stdClass
{
	public $id;
	public $menutype;
	public $title;
	public $alias;
	public $note;
	public $route;
	public $link;
	public $type;
	public $level;
	public $language;
	public $browserNav;
	public $access;
	protected $params;
	public $home;
	public $img;
	public $template_style_id;
	public $component_id;
	public $parent_id;
	public $component;
	public $tree = array();
	public $query = array();
	
	public function __construct($data = array())
	{
		foreach ((array) $data as $key => $value)
		{
			$this->$key = $value;
		}
	}

	public function __get($name)
	{
		if ($name === 'params') {
			return $this->getParams();
		}

		return $this->get($name);
	}

	public function __set($name, $value)
	{
		if ($name === 'params') {
			$this->setParams($value);

			return;
		}

		$this->set($name, $value);
	}

	public function __isset($name)
	{
		if ($name === 'params') {
			return !($this->params instanceof Registry);
		}

		return $this->get($name) !== null;
	}

	public function getParams()
	{
		if (!($this->params instanceof Registry)) {
			try
			{
				$this->params = new Registry($this->params);
			}
			catch (RuntimeException $e)
			{
				$this->params = new Registry;
			}
		}

		return $this->params;
	}

	public function setParams($params)
	{
		$this->params = $params;
	}

	public function get($property, $default = null)
	{
		if (isset($this->$property)) {
			return $this->$property;
		}

		return $default;
	}

	public function set($property, $value = null)
	{
		$previous = isset($this->$property) ? $this->$property : null;
		$this->$property = $value;

		return $previous;
	}
}
