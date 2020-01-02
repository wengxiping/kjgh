<?php
/**
 * @package        Joomla
 * @subpackage     Event Booking
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2010 - 2019 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

/**
 * HTML View class for the Event Booking component
 *
 * @static
 * @package        Joomla
 * @subpackage     Event Booking
 */
class EventbookingViewEventsHtml extends RADViewHtml
{
	/**
	 * Prepare the view before it's being rendered
	 *
	 * @throws Exception
	 */
	protected function prepareView()
	{
		parent::prepareView();

		$app    = JFactory::getApplication();
		$active = $app->getMenu()->getActive();

		$option = isset($active->query['option']) ? $active->query['option'] : '';
		$view   = isset($active->query['view']) ? $active->query['view'] : '';

		if ($option == 'com_eventbooking' && $view == 'events')
		{
			$returnUrl = 'index.php?Itemid=' . $active->id;
			$return    = JRoute::_($returnUrl);
		}
		else
		{
			$returnUrl = JUri::getInstance()->toString();
			$return    = $returnUrl;
		}

		if (JFactory::getUser()->get('guest'))
		{
			$redirectUrl = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($returnUrl));
			$app->redirect($redirectUrl);
		}

		$model       = $this->getModel();
		$state       = $model->getState();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();

		//Add categories filter
		$this->lists['filter_category_id'] = EventbookingHelperHtml::buildCategoryDropdown($state->filter_category_id, 'filter_category_id',
			'onchange="submit();"', $fieldSuffix);
		$this->lists['filter_search']      = $state->filter_search;

		$options                      = array();
		$options[]                    = JHtml::_('select.option', 0, JText::_('EB_EVENTS_FILTER'));
		$options[]                    = JHtml::_('select.option', 1, JText::_('EB_HIDE_PAST_EVENTS'));
		$options[]                    = JHtml::_('select.option', 2, JText::_('EBH_HIDE_CHILDREN_EVENTS'));
		$this->lists['filter_events'] = JHtml::_('select.genericlist', $options, 'filter_events', ' class="input-medium" onchange="submit();" ',
			'value', 'text', $state->filter_events);

		$this->findAndSetActiveMenuItem();

		$this->items      = $model->getData();
		$this->pagination = $model->getPagination();
		$this->config     = EventbookingHelper::getConfig();
		$this->nullDate   = JFactory::getDbo()->getNullDate();
		$this->return     = base64_encode($return);
		$this->state      = $state;

		EventbookingHelper::callOverridableHelperMethod('Html', 'antiXSS', [$this->items, ['title', 'price_text']]);

		// Force layout to default
		$this->setLayout('default');
	}
}
