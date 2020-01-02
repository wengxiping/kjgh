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

class EasySocialControllerAudios extends EasySocialController
{
	public function __construct()
	{
		parent::__construct();

		$this->registerTask('apply', 'save');

		$this->registerTask('makeFeatured', 'toggleDefault');
		$this->registerTask('removeFeatured', 'toggleDefault');
		$this->registerTask('unpublish', 'unpublish');
	}

	/**
	 * Toggles an audio state
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function toggleDefault()
	{
		// Check for token
		ES::checkToken();

		// Get the list of audios
		$cid = $this->input->get('cid', array(), 'array');

		// Get the task
		$task = $this->getTask();

		$message = 'COM_ES_AUDIO_SELECTED_AUDIO_FEATURED';

		foreach ($cid as $id) {
			$id = (int) $id;

			$table = ES::table('Audio');
			$table->load($id);

			$audio = ES::audio($table);

			// If it's set to toggle default, we need to know the audio's featured state
			if ($task == 'toggleDefault') {

				if ($audio->isFeatured()) {
					$message = 'COM_ES_AUDIO_SELECTED_AUDIO_UNFEATURED';
					$audio->removeFeatured();
				} else {
					$audio->setFeatured();
				}
			}

			if ($task == 'makeFeatured') {
				$audio->setFeatured();
			}

			if ($task == 'removeFeatured') {
				$message = 'COM_ES_AUDIO_SELECTED_AUDIO_UNFEATURED';
				$audio->removeFeatured();
			}
		}

		$this->view->setMessage($message);
		return $this->view->call('redirectToAudios');
	}

	/**
	 * Publishes an audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function publish()
	{
		// Check for request forgeries
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'array');

		foreach ($ids as $id) {
			$id = (int) $id;

			$table = ES::table('Audio');
			$table->load($id);

			$audio = ES::audio($table);
			$audio->publish(array('createStream' => false));
		}

		$this->view->setMessage('COM_ES_AUDIO_SELECTED_AUDIO_PUBLISHED');
		return $this->view->call('redirectToAudios');
	}

	/**
	 * Unpublishes an audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function unpublish()
	{
		// Check for request forgeries
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'array');

		foreach ($ids as $id) {
			$id = (int) $id;

			$table = ES::table('Audio');
			$table->load($id);

			$audio = ES::audio($table);
			$audio->unpublish();
		}

		$this->view->setMessage('COM_ES_AUDIO_SELECTED_AUDIO_UNPUBLISHED');
		return $this->view->call('redirectToAudios');
	}

	/**
	 * Saves an audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function save()
	{
		// Check for request forgeries
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		// Get the file data
		$file = $this->input->files->get('audio');

		if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
			$file = null;
		}

		// Get the posted data
		$post = $this->input->post->getArray();

		$table = ES::table('Audio');
		$table->load($id);

		$audio = ES::audio($table);

		$options = array();

		// Audio upload will create stream once it is published.
		// We will only create a stream here when it is an external link.
		if ($post['source'] != SOCIAL_AUDIO_UPLOAD) {
			$options = array('createStream' => true);
		}

		// Save the audio
		$state = $audio->save($post, $file, $options);

		// Load up the session
		$session = JFactory::getSession();

		if (!$state) {

			// Store the data in the session so that we can repopulate the values again
			$data = json_encode($audio->export());

			$session->set('audios.form', $data, SOCIAL_SESSION_NAMESPACE);

			$this->view->setMessage($audio->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__, $audio, $this->getTask());
		}

		// Once an audio is created successfully, remove any data associated from the session
		$session->set('audios.form', null, SOCIAL_SESSION_NAMESPACE);
		$message = 'COM_ES_AUDIO_CREATED_SUCCESS';

		if ($id) {
			$message = 'COM_ES_AUDIO_UPDATED_SUCCESS';
		}

		$this->view->setMessage($message);

		return $this->view->call(__FUNCTION__, $audio, $this->getTask());
	}

	/**
	 * Deletes an audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function delete()
	{
		// Check for request forgeries
		ES::checkToken();

		// Get the list of ids
		$ids = $this->input->get('cid', array(), 'array');

		foreach ($ids as $id) {
			$id = (int) $id;

			$table = ES::table('Audio');
			$table->load($id);

			$audio = ES::audio($table);
			$audio->delete();
		}

		$this->view->setMessage('COM_ES_SELECTED_AUDIO_DELETED');
		return $this->view->call('redirectToAudios');
	}
}