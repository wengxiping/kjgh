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

class PPHelperContentseller extends PPHelperStandardApp
{
	protected $_resource = 'contentseller.article';
	protected $_cat_resource = 'contentseller.category';
	protected $_tag_values = array(
					'article_id',
					'rightholder_id',
					'plan_id',
					'expiration_type',
					'expiration_time',
					'recurrence_count',
					'trial_price_1',
					'trial_time_1',
					'trial_price_2',
					'trial_time_2', 
					'price',
					'currency'
				);

	/**
	 * Initialize article property so it can be access everywhere
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function initialize(&$article)
	{
		$this->article_id = $article->id;
		$this->rightholder_id = $article->created_by;
		$this->cat_id = $article->catid;
	}

	/**
	 * Retrieve plugin params
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPluginParams()
	{
		$plugin = JPluginHelper::getPlugin('payplans', 'contentseller');
		$params = new JRegistry($plugin->params);
		return $params;
	}

	/**
	 * Process the articles
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processArticle(&$article)
	{
		// Initialize article property
		$this->initialize($article);

		// process subscribe button
		$regex = "#{pp-contentseller-subscribe(.*?)}(.*?){/pp-contentseller-subscribe}#s";
		$article->text = preg_replace_callback($regex, array($this, 'processSubscribe'), $article->text);

		// Process restriction content
		$regex = "#{pp-contentseller-restrict(.*?)}(.*?){/pp-contentseller-restrict}#s";
		$article->text = preg_replace_callback($regex, array($this, 'processRestrict'), $article->text);

		return true;
	}

	/**
	 * Process the article to add subscribe button
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processSubscribe($match)
	{
		$userId = isset($this->user_id) ? $this->user_id : PP::user()->user_id;

		if ($this->accessCheck($userId)) {
			return '';
		}

		$attributes = array();
		preg_match_all('/(?P<key>\w+)\=(?P<val>[^\s]+)/', $match[1], $attributes);
		$attributes = array_filter($attributes);

		if (empty($attributes)) {
			return '';
		}

		$values = array_combine($attributes['key'], $attributes['val']);
		$additional = array();
		$additional['article_id'] = isset($values['article_id']) ? $values['article_id'] : $this->article_id;
		$additional['rightholder_id'] = $this->rightholder_id;
		$values	= array_merge($values, $additional);

		if (!isset($values['purpose'])) {
			$this->doErrorLogForPurpose($this->article_id);
		}

		if (!isset($values['plan_id'])) {
			$this->doErrorLogForPlanid($this->article_id);
		}

		$purpose = $values['purpose'];
		$data = array();

		foreach ($this->_tag_values as $tag) {
			$key = $purpose . '_' . $tag;

			if (isset($values[$tag])) {
				$data[$key] = $values[$tag];
			}
		}

		$key = md5(serialize($data));

		$session = PP::session();
		$session->clear($key);
		$session->set($key, $data);

		$url = 'index.php?option=com_payplans&task=plan.subscribe&plan_id=' . $values['plan_id'] . '&tmpl=component';

		$html = '<form method="post" action="' . PPR::_($url) . '">';
		$html .= '<input type="hidden" name="purpose" value="' . $purpose . '">';
		$html .= '<input type="hidden" name="contentSellerKey" value="' . $key . '">';
		$html .= '<button type="submit" style="padding:5px 10px; border-radius:5px; font-weight:bold;">';
		$html .= (empty($match[2]) ? 'Subscribe' : $match[2]);
		$html .= '</button>';
		$html .= '</form>';

		return $html;
	}

	/**
	 * Process the article to remove restricted content from the user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processRestrict($match)
	{
		$ret = '';

		if (!isset($match[2])) {
			return $ret;
		}

		if (JFactory::getApplication()->isAdmin()) {
			return $match[2];
		}

		// Check for restriction
		if (empty($match[1])) {
			if ($this->accessCheck()) {
				return $match[2];
			}

			return $ret;
		}
	}

	/**
	 * Check if user has access to the content
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function accessCheck($userId = null)
	{
		if (is_null($userId)) {
			$userId = isset($this->user_id) ? $this->user_id : PP::user()->user_id;
		}

		if (!$userId) {
			return false;
		}

		// Check for category access
		$access  = PP::resource()->get($userId, $this->cat_id, $this->_cat_resource);
		if ($access && $access->value == $this->cat_id) {
			return true;
		}

		// Check for article access
		$access  = PP::resource()->get($userId, $this->article_id, $this->_resource);
		if ($access && $access->value == $this->article_id) {
			return true;
		}

		return false;
	}

	/**
	 * Process the params before subscription is save
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processBeforeSubscriptionSave(&$prev, &$new)
	{
		// do nothing if prev instance is not null
		if ($prev != null) {
			return true;
		}

		$sellerkey = $this->input->get('contentSellerKey', false);

		$session = PP::session();
		$data = $session->get($sellerkey, array());

		if (empty($data)) {
			return true;
		}

		$purpose = $this->input->get('purpose', false);

		foreach ($this->_tag_values as $tag) {
			$key = $purpose . '_' . $tag;

			if (!isset($data[$key])) {
				continue;
			}

			switch ($tag)
			{
				case "price":
					$new->setPrice($data[$key]);
					break;

				case "expiration_time":
					$new->setExpiration($data[$key]);
					break;

				case "trial_time_1":
					$new->setExpiration($data[$key], PAYPLANS_RECURRING_TRIAL_1);
					break;

				case "trial_time_2":
					$new->setExpiration($data[$key], PAYPLANS_RECURRING_TRIAL_2);
					break;

				default:
					$new->setParam($tag, $data[$key]);
					break;
			}
		}

		$new->setParam('purpose', $purpose);
		$session->clear($sellerkey);
	}

	/**
	 * Process the article resources after the params is saved
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processAfterSubscriptionSave(&$prev, &$new)
	{
		// no need to trigger if previous and current state is same
		if ($prev != null && $prev->getStatus() == $new->getStatus()) {
			return true;
		}

		$status = $new->getStatus();
		$sub_id = $new->getId();
		$user_id = $new->getBuyer()->getId();
		$article_id = $new->getParams()->get('article_id', false);

		if (!$article_id) {
			return true;
		}

		if ($status == PP_SUBSCRIPTION_ACTIVE) {
			PP::resource()->add($sub_id, $user_id, $article_id, $this->_resource);
		}

		if ($status == PP_SUBSCRIPTION_EXPIRED) {
			PP::resource()->remove($sub_id, $user_id, $article_id, $this->_resource);
		}

		return true;
	}

	public function doErrorLogForPlanid($article_id)
	{
		$title = JText::_('COM_PAYPLANS_APP_CONTENT_SELLER_PLAN_NOT_SET_LOG_TITLE');
		$content = array('Message' => JText::_('COM_PAYPLANS_APP_CONTENT_SELLER_PLAN_NOT_SET_LOG_DESC'), 'article_id' => $article_id);

		PP::logger()->log(PPLogger::LEVEL_ERROR, $title, null, $content, 'PayplansFormatter', 'PayplansContentSeller');

		return true;
	}
	
	public function doErrorLogForPurpose($article_id)
	{
		$title = JText::_('COM_PAYPLANS_APP_CONTENT_SELLER_PURPOSE_NOT_SET_LOG_TITLE');
		$content = array('Message' => JText::_('COM_PAYPLANS_APP_CONTENT_SELLER_PURPOSE_NOT_SET_LOG_DESC'), 'article_id' => $article_id);

		PP::logger()->log(PPLogger::LEVEL_ERROR, $title, null, $content, 'PayplansFormatter', 'PayplansContentSeller');

		return true;
	}
}
