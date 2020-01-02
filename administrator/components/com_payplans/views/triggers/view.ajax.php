<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PayPlansViewTriggers extends PayPlansAdminView
{
	/**
	 * Proxy for rendering a dialog to trigger plugins
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function view()
	{
		$event = $this->input->get('event', '', 'word');
		$data = $this->input->post->getArray();

		// Remove unwanted data from the POST request
		unset($data['no_html']);
		unset($data['tmpl']);
		unset($data['format']);
		unset($data['option']);
		unset($data['namespace']);
		unset($data[PP::token()]);

		$result = PPEvent::trigger($event, $data);

		if (isset($result[0])) {
			$result = $result[0];
		}
		// dump($result);
		// $theme = PP::themes();
		// $theme->set('event', $event);
		// $output = $theme->output('admin/triggers/dialogs/view');

		return $this->resolve($result);
	}
}