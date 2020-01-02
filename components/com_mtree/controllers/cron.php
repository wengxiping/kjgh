<?php
namespace Mosets;

defined('_JEXEC') or die;

use \JPluginHelper;

class cron
{
	public function index()
	{
		// Get the dispatcher.
		$dispatcher	= \JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('mosetstree');

		// Trigger Mosets Tree's cron plugins
		$results = $dispatcher->trigger('onMTreeExecuteCron');

		return;
	}
}
