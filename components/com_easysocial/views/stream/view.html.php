<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('site:/views/views');

class EasySocialViewStream extends EasySocialSiteView
{
	/**
	 * Post processing after deleting a custom filter
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function deleteFilter()
	{
		$this->info->set($this->getMessage());

		$redirect = ESR::dashboard(array(), false);

		return $this->redirect($url);
	}

	/**
	 * Renders the single stream item layout
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function item()
	{
		// Check for user profile completeness
		ES::checkCompleteProfile();

		// Get the stream id from the request
		$id = $this->input->get('id', 0, 'int');

		if (!$id) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_STREAM_INVALID_STREAM_ID'));
		}

		// Load the stream table data first
		$streamTable = FD::table('Stream');
		$loadState = $streamTable->load($id);

		// If we are unable to find the record, just throw an error
		if (!$loadState) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_STREAM_INVALID_STREAM_ID'));
		}

		// check if user as the acccess to view cluster invite only stream item
		if ($streamTable->cluster_id && $streamTable->cluster_type) {
			$cluster = $streamTable->getCluster();

			if ($cluster->isInviteOnly() && !$cluster->canViewItem()) {
				ES::raiseError(404, JText::_('COM_EASYSOCIAL_STREAM_INVALID_STREAM_ID'));
			}
		}

		// Retrieve stream
		$lib = ES::stream();
		$stream = $lib->getItem($id, $streamTable->cluster_id, $streamTable->cluster_type, false, array('perspective' => 'dashboard'));

		// This could be due to permission issue if the stream item belongs to a closed or invite only group / event
		if ($stream === false) {

			if ($streamTable->cluster_id && $streamTable->cluster_type) {
				$template = 'site/stream/restricted';
				$this->set('cluster', $streamTable->getCluster());
				$this->set('streamTable', $streamTable);
				parent::display($template);
				return;
			} else {
				// stream from user group.
				ES::raiseError(404, JText::_('COM_EASYSOCIAL_STREAM_CONTENT_NOT_AVAILABLE'));
			}
		}

		// If the user is not allowed to view this stream, display the appropriate message
		if ($stream === true || count($stream) <= 0) {
			$type = $streamTable->cluster_type ? $streamTable->cluster_type : SOCIAL_TYPE_USER;
			$cluster = false;

			if ($streamTable->cluster_type) {
				$cluster = $streamTable->getCluster();
			} else {
				$cluster = ES::user($streamTable->actor_id);
			}

			$template = 'site/stream/restricted';
			$this->set('cluster', $cluster);
			$this->set('streamTable', $streamTable);
			parent::display($template);

			return;
		}

		$metaObj = new stdClass();

		// Get the first stream item
		$stream = $stream[0];


		// Strip off any html tags from the title
		$title = html_entity_decode($stream->title);
		$title = strip_tags($title);
		$title = trim($title);

		// Set the page attributes
		$this->page->title($title);

		$image = $lib->getContentImage($stream);

		if ($streamTable->cluster_type) {
			$cluster = $streamTable->getCluster();

			// Increment the hit counter
			$cluster->hit();
		}

		// if this is group/page/event's create/update stream, we use their cover as the og:image
		if ($streamTable->cluster_type == 'group' || $streamTable->cluster_type == 'page' || $streamTable->cluster_type == 'event') {
			if ($streamTable->verb == 'create' || $streamTable->verb == 'update') {
				$image->url = $cluster->getCover();
			}
		}

		if (!$image->url) {
			// Try to get user avatar image as an alternative.
			$image->url = ES::user($stream->actor->id)->getAvatar(SOCIAL_AVATAR_LARGE);
		}

		if ($image->url) {
			$metaObj->image = $image;
		}

		// Get the permalink of this stream
		$permalink = ESR::stream(array('id' => $stream->uid, 'layout' => 'item', 'external' => 1));

		// Append additional meta details
		$metaObj->url = $permalink;
		$metaObj->type = 'article';
		$metaObj->title = $title;

		// if that is group stream, only set the group title without user name
		if ($streamTable->cluster_type == 'group' || $streamTable->cluster_type == 'page') {
			$metaObj->title = $cluster->getTitle();
		}

		// if that is link type stream, show the link title
		if ($streamTable->context_type == 'links') {

			// Get the assets associated with this stream
			$assets = $stream->getAssets();
			$assets = $assets[0];

			// Retrieve the link that is stored.
			$hash = md5($assets->get('link'));

			// Load the link object
			$link = FD::table('Link');
			$link->load(array('hash' => $hash));

			// Get the link data
			$linkObj = json_decode($link->data);

			$metaObj->title = '';

			if (isset($linkObj->title) && $linkObj->title) {
				$metaObj->title = $linkObj->title;
			}
		}

		// render the meta tags here.
		$this->meta->setMetaObj($metaObj);

		// Get stream actions
		$actions = '';

		// Determines if we should display actions
		if ($stream->display == SOCIAL_STREAM_DISPLAY_FULL) {
			$actions = $lib->getActions($stream);
		}

		// Determines if we should display the translations.
		$language = $this->my->getLanguage();
		$siteLanguage = JFactory::getLanguage();
		$showTranslations = false;

		if (($language != $siteLanguage->getTag()) || $this->config->get('stream.translations.explicit')) {
			$showTranslations = true;
		}

		// Set timeline as default active filter
		$active = 'timeline';

		// Get the stream author object (group/event/page/user)
		$object = $stream->getActor();

		// Display cluster header if this stream is belong to cluster
		if ($streamTable->cluster_id) {
			$object = ES::cluster($streamTable->cluster_type, $streamTable->cluster_id);

			$clusterApps = array('feeds', 'news', 'discussions', 'polls');

			// Set apps as active state
			if (in_array($streamTable->context_type, $clusterApps)) {
				$active = 'apps';
			}
		}

		$streamDateDisplay = $this->config->get('stream.timestamp.style');
		$streamDate = $stream->lapsed;

		if ($streamDateDisplay == 'datetime') {
			$streamDate = $stream->created->toFormat($this->config->get('stream.timestamp.format'));
		}

		$view = $this->input->get('view', '');

		$this->set('active', $active);
		$this->set('streamDate', $streamDate);
		$this->set('object', $object);
		$this->set('showTranslations', $showTranslations);
		$this->set('actions', $actions);
		$this->set('stream', $stream);
		$this->set('view', $view);

		return parent::display('site/stream/item/default');
	}

	/**
	 * Post processing after saving a new filter
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function saveFilter($filter, $error = null)
	{
		$this->info->set($this->getMessage());

		// Construct the redirect url now
		if ($filter->utype == SOCIAL_TYPE_USER) {

			// Return to dashboard directly
			if ($error) {
				return $this->redirect(ESR::dashboard());
			}

			return $this->redirect(ESR::dashboard(array('type' => 'filter', 'filterid' => $filter->getAlias()), false));
		}

		$cluster = ES::cluster($filter->utype, $filter->uid);
		$redirect = $cluster->getPermalink(false);

		return $this->redirect($redirect);
	}


	public function form()
	{
		// Check for user profile completeness
		FD::checkCompleteProfile();

		// Unauthorized users should not be allowed to access this page.
		FD::requireLogin();

		$my 	= FD::user();
		$id 	= JRequest::getInt( 'id', 0 );

		$filter = FD::table( 'StreamFilter' );
		$filter->load( $id );

		$model = FD::model( 'Stream' );
		$items = $model->getFilters( $my->id );

		$this->set( 'filter', $filter );
		$this->set( 'items', $items );


		// Set page title
		if( $filter->id )
		{
			ES::document()->title( JText::sprintf( 'COM_EASYSOCIAL_STREAM_FILTER_EDIT_FILTER', $filter->title ) );
		}
		else
		{
			ES::document()->title( JText::_( 'COM_EASYSOCIAL_STREAM_FILTER_CREATE_NEW_FILTER' ) );
		}

		// Set the page breadcrumb
		ES::document()->breadcrumb( JText::_( 'COM_EASYSOCIAL_PAGE_TITLE_DASHBOARD' ) , FRoute::dashboard() );
		ES::document()->breadcrumb( JText::_( 'Filter' ) );


		echo parent::display( 'site/stream/filter.form' );
	}

	/**
	 * Post processing after load more in stream is clicked for guests
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function loadmoreGuest($stream)
	{
		$content = $stream->html(true);
		$startlimit = $stream->getNextStartLimit();

		if (empty($startlimit)) {
			$startlimit = '';
		}

		$data = new stdClass();
		$data->contents = $content;
		$data->nextlimit = $startlimit;

		echo json_encode($data);exit;
	}

	/**
	 * Post processing after load more.
	 * We are using "html" type output because we need to render modules
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function loadmore(SocialStream $stream)
	{
		// Get the content from the stream
		$content = $stream->html(true);

		$startlimit = $stream->getNextStartLimit();

		if (empty($startlimit)) {
			$startlimit = '';
		}

		$data = new stdClass();
		$data->contents = $content;
		$data->nextlimit = $startlimit;

		echo json_encode($data);exit;
	}
}
