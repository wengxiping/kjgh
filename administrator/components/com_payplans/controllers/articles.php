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

class PayPlansControllerArticles extends PayPlansController
{
	/**
	 * Allows users to search for joomla articles
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function suggest()
	{
		$q = $this->input->get('q', '', 'default');

		$data = new stdClass();

		$data->items = array();
		$data->total_count = 0;

		$model = PP::model('articles');

		$results = $model->searchArticles($q);

		if ($results) {
			$list = array();
			foreach ($results as $item) {

				$item->text = $item->title;

				unset($item->title);
				$list[] = $item;
			}

			$data->items = $list;
			$data->total_count = count($list);
		}

		$json = json_encode($data);

		echo $json;
		exit;
	}

	/**
	 * Allow users to search for joomla articles categories
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function suggestCategory()
	{
		$q = $this->input->get('q', '', 'default');

		$data = new stdClass();

		$data->items = array();
		$data->total_count = 0;

		$model = PP::model('articles');

		$results = $model->searchCategories($q);

		if ($results) {
			$list = array();
			foreach ($results as $item) {

				$item->text = $item->title;

				unset($item->title);
				$list[] = $item;
			}

			$data->items = $list;
			$data->total_count = count($list);
		}

		$json = json_encode($data);

		echo $json;
		exit;
	}
}

