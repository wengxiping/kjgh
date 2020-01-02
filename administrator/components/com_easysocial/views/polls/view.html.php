<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewPolls extends EasySocialAdminView
{
	/**
	 * Renders the list of polls on the site
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_POLLS');

		// Get the model
		$model = ES::model('Polls', array('initState' => true, 'namespace' => 'polls.listing'));

		$search = $model->getState( 'search' );
		$ordering 	= $model->getState( 'ordering' );
		$direction 	= $model->getState( 'direction' );

		// Add Joomla buttons
		JToolbarHelper::deleteList();

		// Get polls
		$polls = $model->getAllPolls();

		foreach ($polls as $poll) {
			$poll->creator = FD::user($poll->created_by);
		}

		// Get pagination
		$pagination	= $model->getPagination();

		$this->set('pagination', $pagination);
		$this->set('search', $search);
		$this->set('polls', $polls);
		$this->set('ordering', $ordering);
		$this->set('direction', $direction);

		parent::display('admin/polls/default/default');
	}

	/**
	 * Post processing after deleting a poll
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function remove()
	{
		return $this->redirect('index.php?option=com_easysocial&view=polls');
	}
}
