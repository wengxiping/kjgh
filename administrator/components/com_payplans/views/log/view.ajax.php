<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PayplansViewLog extends PayPlansAdminView
{
	/**
	 * Displays the form simulate for payment notification
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function purgeConfirmation()
	{
		$theme = PP::themes();

		$output = $theme->output('admin/logs/dialogs/purge.ipn');

		return $this->resolve($output);	
	}

	/**
	 * Displays the form simulate for payment notification
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function purge()
	{
		$theme = PP::themes();

		$output = $theme->output('admin/logs/dialogs/purge');

		return $this->resolve($output);	
	}

	/**
	 * Displays the form simulate for payment notification
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function simulatePaymentNotification()
	{
		$id = $this->input->get('id', 0, 'int');

		$ipn = PP::table('IPN');
		$ipn->load($id);

		$data = json_decode($ipn->json);
		
		$theme = PP::themes();
		$theme->set('data', $data);
		$theme->set('ipn', $ipn);

		// Refactor log dialog
		$output = $theme->output('admin/logs/dialogs/simulate.ipn');

		return $this->resolve($output);	
	}

	/**
	 * Renders the log dialog
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function view($tpl = null, $itemId = null)
	{
		$id = $this->input->get('id', 0, 'int');

		$log = PP::table('Log');
		$log->load($id);

		$lib = PP::log();

		if ($log->content) {
			list($className, $content) = $lib->readBaseEncodeLog($log);
		} else {
			list($className, $content) = $lib->readJsonEncodeLog($log);
		}

		$formatter = PP::getFormatter($className, $log->class);	

		if (!empty($content)) {
			$data = $formatter->formatter($content, $log->class);
		}

		$previous = $data['previous'];
		$current = $data['current'];

		// We'll use either the previous or the current as the base to rely on the columns
		$rows = array();

		$columns = $previous ? $previous : $current;
		$columns = is_array($columns) ? $columns : array($columns);

		foreach ($columns as $key => $value) {

			$row = new stdClass();
			$row->key = $key;
			
			$row->previous = $previous && isset($previous[$key]) ? $previous[$key] : '';
			$row->previous = $this->format($key, $row->previous);
			
			$row->current = $current && isset($current[$key]) ? $current[$key] : '';
			$row->current = $this->format($key, $row->current);

			$row->diff = ($row->current != $row->previous) && $row->previous;

			$rows[] = $row;
		}

		$theme = PP::themes();
		$theme->set('data', $data);
		$theme->set('rows', $rows);
		$theme->set('previous', $previous);
		$theme->set('current', $current);
		$theme->set('logId', $log->log_id);

		// Refactor log dialog
		$output = $theme->output('admin/logs/dialogs/view');

		return $this->resolve($output);
	}

	/**
	 * Displays the payment notification dialog
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function viewPaymentNotification()
	{
		$id = $this->input->get('id', 0, 'int');
		$type = $this->input->get('type', 'json', 'string');

		$ipn = PP::table('IPN');
		$ipn->load($id);

		$theme = PP::themes();
		$theme->set('type', $type);
		$theme->set('ipn', $ipn);

		// Refactor log dialog
		$output = $theme->output('admin/logs/dialogs/view.ipn');

		return $this->resolve($output);	
	}

	/**
	 * Formats the column values 
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function format($key, $value)
	{
		$emptyValue = '&mdash;';

		// Status columns
		if ($key == 'status') {
			if ($value && JString::trim($value) !== '') {
				return JText::_('COM_PAYPLANS_STATUS_' . PP::string()->getStatusName($value));
			}

			return $emptyValue;
		}

		// For params, we need to split them up into new lines
		if ($key == 'params') {
			if ($value == '') {
				return $emptyValue;
			}

			$registry = new JRegistry($value);
			$data = $registry->toArray();
			$result = array();

			if ($data) {
				foreach ($data as $dataKey => $dataValue) {
					$result[] = $dataKey . '=' . $dataValue;
				}
			}

			return implode('<br />', $result);
		}


		if (is_array($value) && !empty($value)) {
			
			// Check numeric keys exist in array(ie. sequential array)
			if (range(0, count($value) - 1) === array_keys($value)) {
				return implode('<br/>', $pre_value);
			}

			return http_build_query($value, '', '<br />');
		}
		
		if (!$value) {
			return $emptyValue;
		}

		return $value;
	}

	/**
	 * Fix legacy file and convert it into php equivalent
	 *
	 * @since	4.0.12
	 * @access	public
	 */
	public function fixLegacyFile()
	{
		$file = $this->input->get('file', '', 'default');

		// Since some web server may reject paths, files has been urlencoded. We need to decode it
		$file = urldecode($file);
		
		$log = PP::log();
		$log->fixLegacyFile($file);

		return $this->ajax->resolve();
	}
}