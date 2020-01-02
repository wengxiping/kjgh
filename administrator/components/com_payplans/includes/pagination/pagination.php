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
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.html.pagination');

class PPPagination
{
	private $pagination = null;

	/**
	 * Class Constructor.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function __construct($total, $limitstart, $limit)
	{
		// Initialize the original pagination from Joomla.
		$this->pagination 	= new JPagination($total, $limitstart, $limit);
	}

	public static function factory($total, $limitstart, $limit)
	{
		$obj = new self($total, $limitstart, $limit);

		return $obj;
	}

	/**
	 * Allows caller to set additional url parameters
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function setVar($key, $value)
	{
		$this->pagination->setAdditionalUrlParam($key, $value);
	}

	/**
	 * Retrieves the html block for pagination codes
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getListFooter($path = 'admin', $url = '')
	{
		// Retrieve pages data from Joomla itself.
		$theme = PP::themes();

		// If there's nothing here, no point displaying the pagination
		if ($this->pagination->total == 0) {
			return;
		}

		$data = $this->pagination->getData();

		$theme->set('data', $data);
		$theme->set('pagination', $this->pagination);

		$contents = $theme->output($path . '/pagination/default');

		return $contents;
	}

	public function getCounter()
	{
		$start		= $this->limitstart + 1;
		$end		= $this->limitstart + $this->limit < $this->total ? $this->limitstart + $this->limit : $this->total;

		return PP::get( 'Themes' )
				->assign( 'start' 	, $start )
				->assign( 'end'		, $end )
				->assign( 'total'	, $this->total )
				->output( 'site.pagination.counter' );
	}

	/**
	 * Getter
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function __get( $key )
	{
		return $this->pagination->$key;
	}
}
