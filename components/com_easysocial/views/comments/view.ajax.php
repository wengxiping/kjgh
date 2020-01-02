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

ES::import('site:/views/views');

class EasySocialViewComments extends EasySocialSiteView
{
	/**
	 * Post process after comment is stored
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function save($comment = null)
	{
		if ($comment->getError()) {
			return $this->ajax->reject($comment->getError());
		}

		$output = $comment->renderHTML();

		return $this->ajax->resolve($output);
	}

	public function update( $comment = null )
	{
		$ajax = FD::ajax();

		if( $this->hasErrors() )
		{
			return $ajax->reject( $this->getMessage() );
		}

		return $ajax->resolve( $comment->getComment() );
	}

	/**
	 * Renders a list of comments
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function load($comments = null)
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		$output = array();

		foreach ($comments as $comment) {
			
			if (!$comment instanceof SocialTableComments) {
				continue;
			}

			$output[] = $comment->renderHTML();
		}

		return $this->ajax->resolve($output);
	}

	/**
	 * Confirmation to delete a comment attachment item
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function confirmDeleteCommentAttachment()
	{
		$theme = ES::themes();
		$contents = $theme->output('site/comments/dialogs/delete.attachment');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Post processing after a comment attachment is deleted
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function deleteAttachment()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Post process after deleting a comment
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function delete()
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		return $this->ajax->resolve();
	}

	/**
	 * Notifies the caller if there are any updates on the comments
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getUpdates($data = null)
	{
		return $this->ajax->resolve($data);
	}

	/**
	 * Renders a dialog for delete confirmation
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmDelete()
	{
		$theme = ES::themes();

		$contents = $theme->output('site/comments/dialogs/delete');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Renders the edit comment form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function edit($comment)
	{
		$overlay = $comment->getOverlay();
		
		$theme = ES::themes();
		$theme->set('comment', $comment->comment);
		$theme->set('overlay', $overlay);

		$contents = $theme->output('site/comments/edit/default');

		return $this->ajax->resolve($contents);
	}
}
