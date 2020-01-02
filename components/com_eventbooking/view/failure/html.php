<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class EventbookingViewFailureHtml extends RADViewHtml
{
	/**
	 * Prepare data for the view before it's being rendered
	 *
	 */
	protected function prepareView()
	{
		parent::prepareView();

		$this->setLayout('default');
		$reason = JFactory::getSession()->get('omnipay_payment_error_reason');

		if (!$reason)
		{
			$reason = $this->input->getString('failReason', '');
		}

		$this->reason = $reason;
	}
}
