<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class EventbookingViewLocationsHtml extends RADViewHtml
{
	protected function prepareView()
	{
		parent::prepareView();

		if (!JFactory::getUser()->authorise('eventbooking.addlocation', 'com_eventbooking'))
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('EB_NO_PERMISSION'), 'error');
			$app->redirect(JUri::root(), 403);

			return;
		}

		$this->findAndSetActiveMenuItem();

		$model            = $this->getModel();
		$this->items      = $model->getData();
		$this->pagination = $model->getPagination();

		$this->setLayout('default');
	}
}
