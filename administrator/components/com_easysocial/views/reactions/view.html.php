<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/views/views');

class EasySocialViewReactions extends EasySocialAdminView
{
	public function display($tpl = null)
	{
		$this->setHeading('COM_ES_REACTIONS');

		$model = ES::model('Likes', array('initState' => true, 'namespace' => 'likes.listing'));
		$reactions = $model->getItemsWithState();

		$ordering = $model->getState('ordering');
		$direction = $model->getState('direction');
		$limit = $model->getState('limit');
		$state = $model->getState('state');

		if ($reactions) {
			foreach ($reactions as &$reaction) {
				$reaction->permalink = false;

				if ($reaction->stream_id) {
					$reaction->permalink = ESR::stream(array('layout' => 'item', 'id' => $reaction->stream_id, 'external' => true));
					continue;
				}

				if ($reaction->uri) {
					$reaction->permalink = base64_decode($reaction->uri);
				}
			}
		}
		$pagination = $model->getPagination();

		$this->set('pagination', $pagination);
		$this->set('ordering', $ordering);
		$this->set('direction', $direction);
		$this->set('limit', $limit);
		$this->set('state', $state);
		$this->set('reactions', $reactions);

		parent::display('admin/reactions/default/default');
	}
}
