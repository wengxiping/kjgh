<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialControllerComments extends EasySocialController
{
	/**
	 * Allows caller to save a comment.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function save()
	{
		ES::requireLogin();

		// Check for request forgeries.
		ES::checkToken();

		// Check for permission first
		$access = ES::access();

		// Ensure that the user is allowed to post comments
		if (!$access->allowed('comments.add')) {
			return $this->view->call(__FUNCTION__);
		}

		$element = $this->input->get('element', '', 'string');
		$group = $this->input->get('group', '', 'string');
		$verb = $this->input->get('verb', '', 'string');
		$uid = $this->input->get('uid', 0, 'int');

		$input = $this->input->get('input', '', 'raw');
		$data = $this->input->get('data', array(), 'array');
		$streamid = $this->input->get('streamid', 0, 'int');
		$parent = $this->input->get('parent', 0, 'int');

		$clusterid = $this->input->get('clusterid', 0, 'int');
		$postActor = $this->input->get('postActor', 'user', 'string');

		// We need to store the cluster to be used later
		if ($clusterid) {
			$data['clusterType'] = $group;
			$data['clusterId'] = $clusterid;
		}

		// Ensure that the current viewer is really allowed to post comments
		$comments = ES::comments($uid, $element, $verb, $group, $data, $streamid);

		if (!$comments->canComment()) {
			$this->view->setMessage('Not allowed to comment', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Determine if we should pre generate the stream for this action
		if ($element == 'photos') {

			$generatePhotoStream = false;

			// Batch upload through story form
			if (!$streamid && $verb == 'upload') {
				$generatePhotoStream = true;
			}

			// Batch upload inside albums page.
			if ($streamid && ($verb == 'add' || $verb == 'create')) {
				$generatePhotoStream = true;
			}

			if ($generatePhotoStream) {
				$photo = ES::table('Photo');
				$photo->load($uid);

				if ($photo->id) {

					// Get the date of when the photo was uploaded
					$createdDate = $photo->created;

					// Generate the stream now. #2575
					$streamItem = $photo->addPhotosStream('create', $createdDate, false, $verb);

					// Now get the stream id
					if ($streamItem) {
						$streamid = $streamItem->uid;

						if ($verb == 'upload') {
							$verb = 'add';
						}
					}
				}
			}
		}

		// Construct the composite key
		$composite = $element . '.' . $group . '.' . $verb;

		$table = ES::table('comments');
		$table->element = $composite;
		$table->uid = $uid;
		$table->comment = $input;
		$table->created_by = $this->my->id;
		$table->created = ES::date()->toSQL();
		$table->parent = $parent;
		$table->params = $data;
		$table->stream_id = $streamid;
		$table->post_as = $postActor;

		// Exclude stream id if stream element is albums. #4984
		if ($element == 'albums') {
			$table->stream_id = 0;
		}

		$state = $table->store();

		if (!$state) {
			return $this->view->call(__FUNCTION__, $table);
		}

		// Process attachments
		$attachments = $this->input->get('attachmentIds', array(), 'array');

		if ($attachments && $this->config->get('comments.attachments.enabled')) {

			foreach ($attachments as $attachmentId) {

				$attachmentId = (int) $attachmentId;

				$file = ES::table('File');
				$file->uid = $table->id;
				$file->type = SOCIAL_TYPE_COMMENTS;

				// Copy some of the data from the temporary table.
				$file->copyFromTemporary($attachmentId);

				// Save the file
				$file->store();

				// We need to resize it if necessary
				if ($this->config->get('comments.resize.enabled') && $this->config->get('comments.resize.width') && $this->config->get('comments.resize.height')) {
					$file->resize($this->config->get('comments.resize.width'), $this->config->get('comments.resize.height'));
				}
			}
		}

		$doStreamUpdate = true;

		if ($streamid) {
			if ($element == 'photos') {
				$sModel = ES::model('Stream');
				$totalItem = $sModel->getStreamItemsCount($streamid);

				if ($totalItem > 1) {
					$doStreamUpdate = false;
				}
			}
		} else {
			// no stream id.
			$doStreamUpdate = false;

			// special handling for new comment on album page. #5455
			if ($element == 'albums' && $verb == 'create') {
				// lets get the latest photo stream that tied to this album
				$albumsModel = ES::model('Albums');
				$streamid = $albumsModel->getStreamId($uid);

				if ($streamid) {

					$doStreamUpdate = true;

					$sModel = ES::model('Stream');
					$totalItem = $sModel->getStreamItemsCount($streamid);

					// Only update the stream if the album has more than one photo
					if ($totalItem == 1) {
						$doStreamUpdate = false;
					}
				}
			}
		}

		if ($doStreamUpdate) {
			$stream = ES::stream();
			$stream->updateModified( $streamid, $this->my->id, SOCIAL_STREAM_LAST_ACTION_COMMENT);
		}

		// Process mentions for this comment
		$mentions = isset($data['mentions']) && !empty($data['mentions']) ? $data['mentions'] : array();

		if ($mentions) {

			// Get the permalink to the comments
			$permalink  = $table->getPermalink();

			foreach ($mentions as $row) {

				$mention = json_decode($row);

				$tag = ES::table('Tag');
				$tag->offset = $mention->start;
				$tag->length = $mention->length;
				$tag->type = $mention->type;

				if ($tag->type == 'hashtag') {
					$tag->title = $mention->value;
				}

				if ($tag->type == 'emoticon') {
					$title = str_replace(array('(', ')'), '', trim($mention->value));

					// Check if the title exists in database
					$model = ES::model('Emoticons');

					$emoticons = $model->getItems(array('title' => $title));

					if (!$emoticons) {
						continue;
					}

					$tag->title = $mention->value;
				}

				// Name tagging
				if ($tag->type == 'entity') {

					$parts = explode(':', $mention->value);

					if (count($parts) != 2) {
						continue;
					}

					$entityType = $parts[0];
					$entityId = $parts[1];

					// Do not allow tagging to happen if they are not friends
					$tag->item_id = $entityId;
					$tag->item_type = $entityType;
				}

				$tag->creator_id = $this->my->id;
				$tag->creator_type = SOCIAL_TYPE_USER;

				$tag->target_id = $table->id;
				$tag->target_type = 'comments';

				$tag->store();

				if ($tag->type == 'entity') {

					// Notify recipients that they are mentioned in a comment
					$emailOptions   = array(
						'title' => 'COM_EASYSOCIAL_EMAILS_USER_MENTIONED_YOU_IN_A_COMMENT_SUBJECT',
						'template' => 'site/comments/mentions',
						'permalink' => $permalink,
						'actor' => $this->my->getName(),
						'actorAvatar' => $this->my->getAvatar(SOCIAL_AVATAR_SQUARE),
						'actorLink' => $this->my->getPermalink(false, true),
						'message' => $table->comment
					);

					$systemOptions  = array(
						'uid' => $table->stream_id,
						'context_type' => 'comments.user.tagged',
						'context_ids' => $table->id,
						'type' => 'comments',
						'url' => $permalink,
						'actor_id' => $this->my->id,
						'target_id' => $tag->item_id,
						'aggregate' => false,
						'content' => $table->comment
					);

					// Send notification to the target
					$state = ES::notify('comments.tagged', array($tag->item_id), $emailOptions, $systemOptions);
				}
			}
		}

		// Update goals progress
		$this->my->updateGoals('postcomment');

		$comments = array(&$table);
		$args = array(&$comments);

		// @trigger: onPrepareComments
		$dispatcher = ES::dispatcher();
		$dispatcher->trigger($group, 'onPrepareComments', $args);

		return $this->view->call(__FUNCTION__, $table);
	}

	/**
	 * Updates a comment
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function update()
	{
		ES::requireLogin();
		ES::checkToken();

		$access = ES::access();
		$id = $this->input->get('id', 0, 'int');

		$table = ES::table('Comments');
		$state = $table->load($id);

		if (!$state) {
			$this->view->setMessage($table->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		if (!$this->my->isSiteAdmin() && !($access->allowed('comments.edit') || ($access->allowed('comments.editown') && $table->isAuthor()))) {
			$this->view->setMessage('COM_EASYSOCIAL_COMMENTS_NOT_ALLOWED_TO_EDIT', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$input = JRequest::getVar('input', null, 'POST', 'none', JREQUEST_ALLOWRAW);
		$mentions = ES::input()->get('mentions', '', 'var');

		$newData = array('comment' => $input);

		$state = $table->update($newData);

		if (!$state) {
			$this->view->setMessage($table->getError(), ES_ERROR);
		}

		// Get existing tags and cross check
		$existingTags = ES::model('tags')->getTags($table->id, 'comments');

		// Store the currently used tags id in order to cross reference and delete from $existingTags later
		$usedTags = array();

		if (!empty($mentions)) {

			// Get the permalink to the comments
			$permalink = $table->getPermalink();

			foreach ($mentions as $row) {

				$mention = (object) $row;

				$tag = ES::table('Tag');

				$state = false;

				// Try to load existing tag first first
				if ($mention->type === 'entity') {
					list($entityType, $entityId) = explode(':', $mention->value);

					$state = $tag->load(array(
						'offset' => $mention->start,
						'length' => $mention->length,
						'type' => $mention->type,
						'target_id' => $table->id,
						'target_type' => 'comments',
						'item_type' => $entityType,
						'item_id' => $entityId
					));

					if (!$state) {
						$tag->item_id = $entityId;
						$tag->item_type = $entityType;
					}
				}

				if ($mention->type === 'hashtag' || $mention->type === 'emoticon') {

					$title = $mention->value;

					if ($mention->type == 'emoticon') {

						if (is_array($mention->value)) {
							$title = $mention->value['title'];
						}

						$title = str_replace(array('(', ')', ':'), '', trim($title));
						$title = '(' . $title .')';
					}

					$state = $tag->load(array(
						'offset' => $mention->start,
						'length' => $mention->length,
						'type' => $mention->type,
						'target_id' => $table->id,
						'target_type' => 'comments',
						'title' => $title
					));

					if (!$state) {
						$tag->title = $title;
					}
				}

				// If state is false, means this is a new tag
				$isNew = !$state;

				// Only assign this properties if it is a new tag
				if ($isNew) {
					$tag->offset = $mention->start;
					$tag->length = $mention->length;
					$tag->type = $mention->type;
					$tag->target_id = $table->id;
					$tag->target_type = 'comments';
				}

				// If this is not a new tag, then we store the id into $usedTags
				if (!$isNew) {
					$usedTags[] = $tag->id;
				}

				// Regardless of new or old, we reassign the creator because it might be the admin editing the comment
				$tag->creator_id = $this->my->id;
				$tag->creator_type = SOCIAL_TYPE_USER;

				$tag->store();

				if ($isNew) {
					if ($tag->type == 'entity') {
						// Notify recipients that they are mentioned in a comment
						$emailOptions = array(
							'title' => 'COM_EASYSOCIAL_EMAILS_USER_MENTIONED_YOU_IN_A_COMMENT_SUBJECT',
							'template' => 'site/comments/mentions',
							'permalink' => $permalink,
							'actor' => $this->my->getName(),
							'actorAvatar' => $this->my->getAvatar(SOCIAL_AVATAR_SQUARE),
							'actorLink' => $this->my->getPermalink(false, true),
							'message' => $table->comment
						);

						$systemOptions = array(
							'uid' => $table->stream_id,
							'context_type' => 'comments.user.tagged',
							'context_ids' => $table->id,
							'type' => 'comments',
							'url' => $permalink,
							'actor_id' => $this->my->id,
							'target_id' => $tag->item_id,
							'aggregate' => false,
							'content' => $table->comment
						);

						// Send notification to the target
						ES::notify('comments.tagged', array($tag->item_id), $emailOptions, $systemOptions);
					}
				}
			}
		}

		// Now we do a tag clean up to ensure tags that are not in used are deleted properly
		foreach ($existingTags as $existingTag) {
			if (!in_array($existingTag->id, $usedTags)) {
				$existingTag->delete();
			}
		}

		$this->view->call(__FUNCTION__, $table);
	}

	/**
	 * Renders the remaining comments after a comment has been paginated
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function load()
	{
		ES::requireLogin();
		ES::checkToken();

		// Determines if the user can really read / view comments
		$access = $this->my->getAccess();

		if (!$access->allowed('comments.read')) {
			return $this->view->exception('COM_EASYSOCIAL_COMMENTS_NOT_ALLOWED_TO_READ');
		}

		$element = $this->input->get('element', '', 'string');
		$group = $this->input->get('group', SOCIAL_APPS_GROUP_USER, 'string');
		$verb = $this->input->get('verb', null, 'string');
		$uid = $this->input->get('uid', 0, 'int');

		// Pagination
		$start = $this->input->get('start', 0, 'int');
		$limit = $this->input->get('length', 0, 'int');
		$parent = $this->input->get('parent', 0, 'int');


		$key = $element . '.' . $group . '.' . $verb;

		$options = array('element' => $key, 'uid' => $uid, 'start' => $start, 'limit' => $limit, 'parentid' => $parent);

		$model = ES::model('Comments');
		$comments = $model->getComments($options);

		if (!$comments) {
			$this->view->setMessage('COM_EASYSOCIAL_COMMENTS_ERROR_RETRIEVING_COMMENTS', ES_ERROR);
		}

		return $this->view->call(__FUNCTION__, $comments);
	}

	/**
	 * Removes a comment attachment on the site
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function deleteAttachment()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the attachment id
		$id = $this->input->get('id', 0, 'int');

		$file = ES::table('File');
		$file->load($id);

		// Check if the owner of the attachment is really correct
		if ($file->user_id != $this->my->id && !$this->my->isSiteAdmin()) {
			return JError::raiseError(500, JText::_('You are not allowed to remove this file.'));
		}

		// Delete the file
		$file->delete();

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Triggered to delete a comment
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function delete()
	{
		ES::requireLogin();
		ES::checkToken();

		// Check for permission first
		$access = ES::access();

		// Get the comment id
		$id = $this->input->get('id', 0, 'int');

		// Load the comment object
		$table = ES::table('Comments');
		$state = $table->load($id);

		if (!$state) {
			$this->view->setMessage($table->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// There are cases where the app may need to allow the user to delete the comments.
		$apps = ES::apps();
		$apps->load(SOCIAL_TYPE_USER);

		$args = array(&$table, &$this->my);
		$dispatcher = ES::dispatcher();
		$allowed = $dispatcher->trigger(SOCIAL_TYPE_USER, 'canDeleteComment', $args);

		if ($this->my->isSiteAdmin() || $access->allowed('comments.delete') || ($table->isAuthor() && $access->allowed('comments.deleteown')) || in_array(true, $allowed)) {

			$state = $table->delete();

			if (!$state) {
				$this->view->setMessage($table->getError(), ES_ERROR);
			}

		} else {
			$this->view->setMessage('COM_EASYSOCIAL_COMMENTS_NOT_ALLOWED_TO_DELETE', ES_ERROR);
		}

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Retrieves new updates for comments that should be updated on the page
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getUpdates()
	{
		$items = $this->input->get('data', '', 'default');

		// We should only be updating based on the limit
		$updateLimit = $this->config->get('comments.limit');

		$model = ES::model('Comments');
		$data = array();

		$disallowed = array('albums', 'photos');

		$string = ES::string();

		foreach ($items as $element => $blocks) {

			$data[$element] = array();

			foreach ($blocks as $blockKey => $block) {

				// Since the id's are always in the form of x.x, we need to get the difference between the id and the stream id
				$parts = explode('.', $blockKey);

				$streamid = isset($parts[0]) ? $parts[0] : '';
				$streamid = $string->escape($streamid);

				$uid = isset($parts[1]) ? $parts[1] : '';
				$uid = $string->escape($uid);

				// Construct mandatory options
				$element = $string->escape($element);
				$options = array('element' => $element, 'limit' => 0, 'parentid' => 0);

				// Ensure that the element for photos and albums doesn't check against the stream_id.
				// Because the albums and photos has a different method of retrieving the count.
				$elementTmp = explode('.', $element);

				if ($streamid && !in_array($elementTmp[0], $disallowed)) {
					$options['stream_id'] = $streamid;
				}

				if ($uid) {
					$options['uid'] = $uid;
				}

				// Initialize the start data
				$item = new stdClass();
				$item->ids = array();

				// Ids could be non-existent if the passed in array is empty
				$ids = array();

				if (array_key_exists('ids', $block) && is_array($block['ids'])) {
					$ids = $block['ids'];
				}

				// Current counters
				$currentTimestamp = $block['timestamp'];
				$options['since'] = ES::date($currentTimestamp)->toSql();

				// 1. We need to track new comments added since the "timestamp"
				$comments = $model->getComments($options);

				// Check for newly inserted comments
				if ($comments) {
					foreach ($comments as $comment) {
						// If newId is not in the list of ids, means it is a new comment
						if (!in_array($comment->id, $ids)) {
							$item->ids[$comment->id] = $comment->renderHTML();
						}
					}
				}

				// 2. We need to track removed comments. Simply by determining missing id's from the id's provided
				if ($ids) {
					$missing = $model->getMissingItems($ids);

					if ($missing) {
						foreach ($missing as $id) {
							$item->ids[$id] = false;
						}
					}
				}

				// Assign the new timestamp
				$item->timestamp = ES::date()->toUnix();

				$data[$element][$blockKey] = $item;
			}
		}

		return $this->view->call(__FUNCTION__, $data);
	}

	/**
	 * Renders the edit comment form
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function edit()
	{
		ES::requireLogin();

		// Check for request forgeries
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$comment = ES::table('Comments');
		$comment->load($id);

		if (!$comment->id || !$comment->canEdit()) {
			return $this->view->exception();
		}

		$this->view->call(__FUNCTION__, $comment);
	}
}
