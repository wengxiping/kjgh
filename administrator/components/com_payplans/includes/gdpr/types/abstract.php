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

abstract class PayplansGdprAbstract
{
	public $userId = null;
	public $type = null;
	public $params = null;
	public $user = null;

	public $path = null;

	abstract protected function execute(PayplansGdprSection &$section);

	public function __construct(PPUser $user, $params)
	{
		$this->user = $user;
		$this->userId = $user->getId();
		$this->params = $params;
	}

	/**
	 * Determines the date format that should be used
	 *
	 * @since	3.7
	 * @access	public
	 */
	public function getDateFormat()
	{
		$format = JText::_('DATE_FORMAT_LC2');

		return $format;
	}


	/**
	 * Creates a new template instance
	 *
	 * @since	3.7
	 * @access	public
	 */
	public function getTemplate($id, $type)
	{
		$item = new PayplansGdprTemplate();
		$item->id = $id;
		$item->type = $type;
		
		return $item;
	}

	/**
	 * Retrieve params from the adapter
	 *
	 * @since	3.7
	 * @access	public
	 */
	public function getParams($name, $default = false)
	{
		$name = $this->type . '.' . $name;
		return $this->params->get($name, $default);
	}

	/**
	 * Set params on the adapter
	 *
	 * @since	3.7
	 * @access	public
	 */
	public function setParams($name, $value)
	{
		$name = $this->type . '.' . $name;

		return $this->params->set($name, $value);
	}

}
