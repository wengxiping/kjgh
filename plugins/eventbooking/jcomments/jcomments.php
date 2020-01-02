<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die;

class plgEventBookingJcomments extends JPlugin
{
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}

	public function onEventDisplay($row)
	{
		if (!file_exists(JPATH_ROOT . '/components/com_jcomments/jcomments.php'))
		{
			return;
		}

		ob_start();
		$this->displayCommentForm($row);

		return array('title'    => JText::_('EB_COMMENT'),
		             'form'     => ob_get_clean(),
		             'position' => $this->params->get('output_position', 'after_register_buttons'),
		);
	}

	/**
	 * Display form allows users to add comments about the event via JComments
	 *
	 * @param object $row
	 */
	private function displayCommentForm($row)
	{
		require_once JPATH_ROOT . '/components/com_jcomments/jcomments.php';
		echo '<div style="clear:both; padding-top: 10px;"></div>';
		echo JComments::showComments($row->id, 'com_eventbooking', $row->title);
	}
}
