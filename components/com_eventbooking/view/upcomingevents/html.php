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

class EventbookingViewUpcomingeventsHtml extends EventbookingViewCategoryHtml
{
	/**
	 * Method to prepare document before it is rendered
	 *
	 * @return void
	 */
	protected function prepareDocument()
	{
		// Correct active menu item in case the URL is typed directly on browser
		$this->findAndSetActiveMenuItem();

		$this->params = $this->getParams();

		// Hide children categories if configured in menu parameter
		if ($this->params->get('hide_children_categories'))
		{
			$this->categories = [];
		}

		// Page title
		if (!$this->params->get('page_title'))
		{
			$pageTitle = JText::_('EB_UPCOMING_EVENTS_PAGE_TITLE');

			if ($this->category)
			{
				$pageTitle = str_replace('[CATEGORY_NAME]', $this->category->name, $pageTitle);
			}

			$this->params->set('page_title', $pageTitle);
		}

		// Page heading
		$this->params->def('page_heading', JText::_('EB_UPCOMING_EVENTS'));

		// Meta keywords and description
		if (!$this->params->get('menu-meta_keywords') && !empty($this->category->meta_keywords))
		{
			$this->params->set('menu-meta_keywords', $this->category->meta_keywords);
		}

		if (!$this->params->get('menu-meta_description') && !empty($this->category->meta_description))
		{
			$this->params->set('menu-meta_description', $this->category->meta_description);
		}

		// Load required assets for the view
		$this->loadAssets();

		// Set page meta data
		$this->setDocumentMetadata();

		// Add Feed links to document
		if ($this->config->get('show_feed_link', 1))
		{
			$this->addFeedLinks();
		}

		// Use override menu item
		if ($this->params->get('menu_item_id') > 0)
		{
			$this->Itemid = $this->params->get('menu_item_id');
		}

		// Intro text
		if (EventbookingHelper::isValidMessage($this->params->get('intro_text')))
		{
			$this->introText = $this->params->get('intro_text');
		}

		$config = EventbookingHelper::getConfig();

		if ($config->show_children_events_under_parent_event)
		{
			$db          = $this->model->getDbo();
			$query       = $db->getQuery(true);
			$currentDate = $db->quote(EventbookingHelper::getServerTimeFromGMTTime());

			foreach ($this->items as $item)
			{
				if ($item->event_type != 1 || $item->event_start_minutes < 0)
				{
					continue;
				}

				$query->clear()
					->select('event_date, event_end_date')
					->from('#__eb_events')
					->where('parent_id = ' . $item->id)
					->where('event_date >= ' . $currentDate)
					->order('event_date');
				$db->setQuery($query);

				$nextChildEvent = $db->loadObject();

				if ($nextChildEvent)
				{
					$item->event_date     = $nextChildEvent->event_date;
					$item->event_end_date = $nextChildEvent->event_end_date;
				}
			}
		}
	}
}
