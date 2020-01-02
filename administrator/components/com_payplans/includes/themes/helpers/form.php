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

class PPThemesHelperForm extends PPThemesHelperAbstract
{
	/**
	 * Renders a hidden form inputs on generic forms
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function action($controller = '', $task = '', $view = '')
	{
		$theme = PP::themes();

		if ($task) {
			$task = $controller ? $controller . '.' . $task : $task;
		}

		$theme->set('controller', $controller);
		$theme->set('task', $task);
		$theme->set('view', $view);

		$output = $theme->output('admin/helpers/form/action');

		return $output;
	}

	/**
	 * Renders a hidden form inputs on generic forms
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function activeTab($active = '')
	{
		$theme = PP::themes();
		$theme->set('active', $active);

		$output = $theme->output('admin/helpers/form/activetab');

		return $output;
	}

	/**
	 * Renders an amount form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function amount($amount, $currency, $id = '', $attributes = '', $options = array())
	{
		$config = PP::config();
		$fractionDigitCount = $config->get('fractionDigitCount');
		$separator = $config->get('price_decimal_separator');
		$currencyBeforeAfter = $config->get('show_currency_at');

		$amount = number_format(round($amount, $fractionDigitCount), $fractionDigitCount, $separator, '');

		$theme = PP::themes();
		$theme->set('amount', $amount);
		$theme->set('currency', $currency);
		$theme->set('currencyBeforeAfter', $currencyBeforeAfter);
		$theme->set('id', $id);
		$theme->set('attributes', $attributes);

		$output = $theme->output('admin/helpers/form/amount');

		return $output;
	}

	/**
	 * This is a unique form.toggler option that would show / hide a dependent form.plans
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function allPlans($name, $value, $id = '', $dependencies = array())
	{
		$theme = PP::themes();

		if (!$id) {
			$id = $name;
		}

		// Generate a unique id for itself
		$uid = 'data-allplans-' . uniqid();

		$dependencies = implode(',', $dependencies);

		$theme->set('uid', $uid);
		$theme->set('dependencies', $dependencies);
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);

		$output = $theme->output('admin/helpers/form/allplans');

		return $output;
	}

	/**
	 * Renders an autocomplete form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function autocomplete($name, $selected = null, $id = '', $attributes = '', $options = array())
	{
		$theme = PP::themes();

		$attributes = $this->formatAttributes($attributes);

		if (!$id) {
			$id = $name;
		}

		// Ensure that options are all objects
		if ($options) {
			foreach ($options as &$option) {
				$option = (object) $option;
			}
		}

		JHtml::_('formbehavior.chosen', '.pp-autocomplete', null);

		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('selected', $selected);
		$theme->set('attributes', $attributes);
		$theme->set('options', $options);

		$output = $theme->output('admin/helpers/form/autocomplete');

		return $output;
	}

	/**
	 * Renders a calendar input
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function calendar($name, $value, $id = '', $attributes = '', $options = array())
	{
		if (is_array($attributes)) {
			$attributes	= implode(' ', $attributes);
		}

		$theme = PP::themes();
		$uuid = uniqid();
		$language = JFactory::getDocument()->getLanguage();

		JHtml::_('behavior.calendar');

		$fullWidth = PP::normalize($options, 'fullWidth', true);

		$theme->set('fullWidth', $fullWidth);
		$theme->set('language', $language);
		$theme->set('uuid', $uuid);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('id', $id);
		$theme->set('attributes', $attributes);

		return $theme->output('admin/helpers/form/calendar');
	}

	/**
	 * Renders a credit card input form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function card($names, $value = '', $id = '', $attributes = '', $options = array())
	{
		if (is_array($attributes)) {
			$attributes	= implode(' ', $attributes);
		}

		$inputNames = new stdClass;
		$inputNames->name = false;
		$inputNames->nameValue = '';
		$inputNames->card = 'card';
		$inputNames->cardValue = '';
		$inputNames->expireMonth = 'exp_month';
		$inputNames->expireMonthValue = '';
		$inputNames->expireYear = 'exp_year';
		$inputNames->expireYearValue = '';
		$inputNames->code = 'cvv';
		$inputNames->codeValue = '';

		if (isset($names['name'])) {
			$inputNames->name = $names['name'];
		}

		if (isset($names['card'])) {
			$inputNames->card = $names['card'];
		}

		if (isset($names['expire_month'])) {
			$inputNames->expireMonth = $names['expire_month'];
		}

		if (isset($names['expire_year'])) {
			$inputNames->expireYear = $names['expire_year'];
		}

		if (isset($names['code'])) {
			$inputNames->code = $names['code'];
		}

		foreach ($inputNames as $key => $property) {
			if (isset($value[$property])) {
				$variable = $key . 'Value';

				if (isset($inputNames->$variable)) {
					$inputNames->$variable = $value[$property];
				}
			}
		}

		$theme = PP::themes();
		$uuid = uniqid();

		$theme->set('inputNames', $inputNames);
		$theme->set('uuid', $uuid);
		$theme->set('value', $value);
		$theme->set('id', $id);
		$theme->set('attributes', $attributes);

		return $theme->output('site/helpers/form/card');
	}

	/**
	 * Renders a list of currencies
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function currency($name, $value, $id = '', $attributes = '', $options = array())
	{
		$items = PP::getCurrency();
		$currencies = array();
		$attributes = $this->formatAttributes($attributes);

		if ($items) {
			foreach ($items as $item){

				$currency = new stdClass();
				$currency->title = PPFormats::currency($item, array(), 'fullname');
				$currency->value = $item->currency_id;

				$currencies[] = $currency;
			}
		}

		$theme = PP::themes();
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);
		$theme->set('currencies', $currencies);

		$output = $theme->output('admin/helpers/form/currency');

		return $output;
	}

	/**
	 * Renders the registration type form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function dependency($name, $value, $id = '', $attributes = '', $options = array())
	{
		$attributes = $this->formatAttributes($attributes);

		if (!$id) {
			$id = $name;
		}

		// Ensure that options are all objects
		if ($options) {
			foreach ($options as &$option) {
				$option = (object) $option;
			}
		}

		$uid = uniqid();

		$theme = PP::themes();
		$theme->set('options', $options);
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);
		$theme->set('uid', $uid);

		$output = $theme->output('admin/helpers/form/dependency');

		return $output;
	}

	/**
	 * Generates a text input
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function email($name, $value, $id = '', $attributes = '')
	{
		$attributes = $this->formatAttributes($attributes);

		if (!$id) {
			$id = $name;
		}

		$theme = PP::themes();
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);

		$output = $theme->output('admin/helpers/form/email');

		return $output;
	}

	/**
	 * Generates a WYSIWYG editor
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function editor($name, $value, $id = '', $attributes = '', $options = array(), $dependents = array(), $decode = true)
	{
		$attributes = $this->formatAttributes($attributes);

		if (!$id) {
			$id = $name;
		}

		$editor = JFactory::getEditor();

		if ($decode) {
			$value = base64_decode($value);
		}

		$theme = PP::themes();
		$theme->set('id', $id);
		$theme->set('editor', $editor);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);

		$output = $theme->output('admin/helpers/form/editor');

		return $output;
	}

	/**
	 * Renders an expiration type form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function expiration($name, $value, $id = '', $attributes = '', $options = array())
	{
		$attributes = $this->formatAttributes($attributes);

		$theme = PP::themes();
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);

		$output = $theme->output('admin/helpers/form/expiration');

		return $output;
	}

	/**
	 * Formats a list of attributes
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function formatAttributes($data)
	{
		if (!$data) {
			return '';
		}

		// If attributes is already a string, we shouldn't need to format anything
		if (!is_array($data) && is_string($data)) {
			return $data;
		}

		$attributes = '';

		foreach ($data as $key => $value) {
			$attributes .= ' ' . $key . '="' . $value . '"';
		}

		return $attributes;
	}

	/**
	 * Renders a hidden input
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function hidden($name, $value = '', $attributes = '')
	{
		$attributes = $this->formatAttributes($attributes);

		$theme = PP::themes();
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);

		$output = $theme->output('admin/helpers/form/hidden');

		return $output;
	}

	/**
	 * Renders a hidden input that is normally used for storing ids
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function ids($name, $ids = array())
	{
		if (!$ids) {
			return;
		}

		$theme = PP::themes();
		$theme->set('name', $name);
		$theme->set('ids', $ids);

		$output = $theme->output('admin/helpers/form/ids');

		return $output;
	}

	/**
	 * Generates a password input
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function password($name, $value, $id = '', $attributes = '')
	{
		$attributes = $this->formatAttributes($attributes);

		if (!$id) {
			$id = $name;
		}

		$theme = PP::themes();
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);

		$output = $theme->output('admin/helpers/form/password');

		return $output;
	}

	/**
	 * Renders a plans dropdown
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function plans($name, $value, $editable = true, $multiple = false, $attributes = '', $exclusion = array(), $allowEmpty = false)
	{
		static $allPlans = null;

		if (isset($attributes['multiple']) && $attributes['multiple']) {
			$multiple = true;
		}

		$attributes = $this->formatAttributes($attributes);

		$value = (array) $value;
		$selectedPlan = false;

		if (is_null($allPlans)) {
			$model = PP::model('Plan');
			$items = $model->loadRecords();

			if ($items) {
				foreach ($items as $item) {
					$plan = PP::plan($item);
					$allPlans[] = $plan;
				}
			}
		}

		// container
		$plans = $allPlans;

		// if exclusion is needed
		if ($exclusion && $allPlans) {
			// reset the container here. we will exclude manually here.
			$plans = array();

			foreach ($allPlans as $p) {
				if (! in_array($p->plan_id, $exclusion)) {
					$plans[] = $p;
				}
			}
		}

		if ($plans) {
			foreach ($plans as &$plan) {
				$plan->isSelected = false;

				if (in_array($plan->plan_id, $value)) {
					$plan->isSelected = true;
				}
			}
		}

		if ($multiple) {
			$name = $name . '[]';
		}

		$theme = PP::themes();
		$theme->set('editable', $editable);
		$theme->set('plans', $plans);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('multiple', $multiple);
		$theme->set('attributes', $attributes);
		$theme->set('allowEmpty', $allowEmpty);

		$output = $theme->output('admin/helpers/form/plans');

		return $output;
	}

	/**
	 * Renders a plans dropdown
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function plansgroup($name, $value, $editable = true, $attributes = '')
	{
		static $plans = null;

		$multiple = false;

		if (isset($attributes['multiple']) && $attributes['multiple']) {
			$multiple = true;
		}

		$attributes = $this->formatAttributes($attributes);
		$selectedGroup = false;

		if (is_null($plans)) {
			$model = PP::model('Group');
			$groups = $model->loadRecords();

			if ($groups) {
				foreach ($groups as &$group) {
					$group = PP::group($group);

					$group->isSelected = false;

					if ($group->group_id == $value) {
						$group->isSelected = true;
						$slectedGroup = $group;
					}
				}
			}
		}

		if ($multiple) {
			$name = $name . '[]';
		}

		$theme = PP::themes();
		$theme->set('selectedGroup', $selectedGroup);
		$theme->set('editable', $editable);
		$theme->set('groups', $groups);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('multiple', $multiple);
		$theme->set('attributes', $attributes);

		$output = $theme->output('admin/helpers/form/plansgroup');

		return $output;
	}

	/**
	 * Plan to alias mapping. Currently only being used by Fastspring
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function planAlias($name, $value, $id = '', $attributes = '')
	{
		$attributes = $this->formatAttributes($attributes);

		if (!$id) {
			$id = $name;
		}

		$model = PP::model('Plan');
		$plans = $model->getItems();

		$totalValues = is_array($value) ? count($value) : 0;
// dump($totalValues, $value);
		$theme = PP::themes();
		$theme->set('plans', $plans);
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('totalValues', $totalValues);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);

		$output = $theme->output('admin/helpers/form/plan.alias');

		return $output;
	}

	/**
	 * Generates a file input
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function file($name, $value, $id = '', $attributes = '')
	{
		$attributes = $this->formatAttributes($attributes);

		if (!$id) {
			$id = $name;
		}

		$theme = PP::themes();
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);

		$output = $theme->output('admin/helpers/form/file');

		return $output;
	}

	/**
	 * Generates a dropdown list for file input
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function filelist($name, $value, $id = '', $attributes = '', $folder = '', $pattern = '.php', $exclude = array(), $stripExtension = true)
	{
		if (!$folder) {
			return;
		}

		// If the folder is a namespace, we need to resolve to the proper path
		$isNamespace = PP::isNamespace($folder);

		if ($isNamespace) {
			$namespace = $folder;

			$resolver = PP::resolver();
			$folder = $resolver->resolve($folder, '');
		}

		if (!$isNamespace) {
			$folder = JPATH_ROOT . '/' . $folder;
		}

		$options = array();

		$files = JFolder::files($folder, $pattern, true, true, $exclude);

		// Default empty option
		$option = new stdClass();
		$option->title = JText::_('Select a File');
		$option->value = '';

		$options[] = $option;

		if ($files) {
			foreach ($files as $file) {
				$option = new stdClass();
				$option->title = basename($file);
				$option->value = $stripExtension ? JFile::stripExt($option->title) : $option->title;

				$options[] = $option;
			}
		}

		$theme = PP::themes();
		$theme->set('options', $options);
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);

		$output = $theme->output('admin/helpers/form/list');

		return $output;
	}

	/**
	 * Renders an autocomplete form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function discounts($name, $value, $id = '', $attributes = '')
	{
		$theme = PP::themes();

		$attributes = $this->formatAttributes($attributes);

		if (!$id) {
			$id = $name;
		}

		// Get a list of coupon codes
		$model = PP::model('Discount');
		$options = $model->getCouponCodes();

		JHtml::_('formbehavior.chosen', '.pp-autocomplete', null);

		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);
		$theme->set('options', $options);

		$output = $theme->output('admin/helpers/form/discounts');

		return $output;
	}

	/**
	 * Generates a file input with image
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function imagefile($name, $value, $id = '', $attributes = '')
	{
		$attributes = $this->formatAttributes($attributes);

		if (!$id) {
			$id = $name;
		}

		$image = PP::config()->get($name, false);

		$theme = PP::themes();
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);
		$theme->set('image', $image);

		$output = $theme->output('admin/helpers/form/imagefile');

		return $output;
	}

	/**
	 * Renders an autocomplete form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function easyblogCategories($name, $value, $id = '', $attributes = '')
	{
		$theme = PP::themes();

		$attributes = $this->formatAttributes($attributes);

		if (!$id) {
			$id = $name;
		}

		$lib = PP::easyblog();

		if (!$lib->exists()) {
			return JText::_('COM_PAYPLANS_PLEASE_INSTALL_EASYBLOG_BEFORE_USING_THIS_APPLICATION');
		}

		$categories = $lib->getCategories();

		JHtml::_('formbehavior.chosen', '.pp-autocomplete', null);

		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);
		$theme->set('categories', $categories);

		$output = $theme->output('admin/helpers/form/easyblog.categories');

		return $output;
	}

	/**
	 * Renders an autocomplete form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function easydiscussAcl($name, $value, $id = '', $attributes = '')
	{
		$theme = PP::themes();

		$attributes = $this->formatAttributes($attributes);

		if (!$id) {
			$id = $name;
		}

		$lib = PP::easydiscuss();

		if (!$lib->exists()) {
			return JText::_('COM_PAYPLANS_PLEASE_INSTALL_EASYDISCUSS_BEFORE_USING_THIS_APPLICATION');
		}

		$rules = $lib->getAclRules();

		JHtml::_('formbehavior.chosen', '.pp-autocomplete', null);

		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);
		$theme->set('rules', $rules);

		$output = $theme->output('admin/helpers/form/easydiscuss.acl');

		return $output;
	}

	/**
	 * Renders an autocomplete form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function easydiscussBadges($name, $value, $id = '', $attributes = '')
	{
		$theme = PP::themes();

		$attributes = $this->formatAttributes($attributes);

		if (!$id) {
			$id = $name;
		}

		$lib = PP::easydiscuss();

		if (!$lib->exists()) {
			return JText::_('COM_PAYPLANS_PLEASE_INSTALL_EASYDISCUSS_BEFORE_USING_THIS_APPLICATION');
		}

		$badges = $lib->getBadges();

		JHtml::_('formbehavior.chosen', '.pp-autocomplete', null);

		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);
		$theme->set('badges', $badges);

		$output = $theme->output('admin/helpers/form/easydiscuss.badges');

		return $output;
	}

	/**
	 * Renders an autocomplete form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function easydiscussCategories($name, $value, $id = '', $attributes = '')
	{
		$theme = PP::themes();

		$attributes = $this->formatAttributes($attributes);

		if (!$id) {
			$id = $name;
		}

		$lib = PP::easydiscuss();

		if (!$lib->exists()) {
			return JText::_('COM_PAYPLANS_PLEASE_INSTALL_EASYDISCUSS_BEFORE_USING_THIS_APPLICATION');
		}

		$categories = $lib->getCategories();

		JHtml::_('formbehavior.chosen', '.pp-autocomplete', null);

		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);
		$theme->set('categories', $categories);

		$output = $theme->output('admin/helpers/form/easydiscuss.categories');

		return $output;
	}

	/**
	 * Renders an autocomplete form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function kunenaCategories($name, $value, $id = '', $attributes = '')
	{
		$theme = PP::themes();

		$attributes = $this->formatAttributes($attributes);

		if (!$id) {
			$id = $name;
		}

		$lib = PP::kunena();

		if (!$lib->exists()) {
			return JText::_('COM_PAYPLANS_PLEASE_INSTALL_KUNENA_BEFORE_USING_THIS_APPLICATION');
		}

		$categories = $lib->getCategories();

		JHtml::_('formbehavior.chosen', '.pp-autocomplete', null);

		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);
		$theme->set('categories', $categories);

		$output = $theme->output('admin/helpers/form/kunena.categories');

		return $output;
	}

	/**
	 * Renders an autocomplete form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function mailchimplist($name, $value, $id = '', $attributes = '')
	{
		$theme = PP::themes();

		$attributes = $this->formatAttributes($attributes);

		if (!$id) {
			$id = $name;
		}

		// Get current app id from the query
		$appId = $this->input->get('id', '');

		if (!$appId) {
			return JText::_('COM_PP_PLEASE_SAVE_MAILCHIMP_APP_FIRST');
		}

		$app = PP::app()->getAppInstance($appId);
		$params = $app->getAppParams();
		$apiKey = $params->get('mailchimpApiKey', '');
		$email = $params->get('mailchimpMerchantEmail', '');

		if (!$apiKey || !$email) {
			return JText::_('COM_PP_PLEASE_SET_MAILCHIMP_APIKEY_AND_EMAIL');
		}

		$lib = PP::mailchimp();
		$lists = $lib->getLists($apiKey, $email);

		JHtml::_('formbehavior.chosen', '.pp-autocomplete', null);

		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);
		$theme->set('lists', $lists);

		$output = $theme->output('admin/helpers/form/mailchimp');

		return $output;
	}

	/**
	 * Renders the popover html contents
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function label($label, $desc = '', $columns = 3, $help = true, $required = false)
	{
		if (!$desc) {
			$desc = $label . '_DESC';
			$desc = JText::_($desc);
		}

		$label = JText::_($label);

		$theme = PP::themes();
		$theme->set('columns', $columns);
		$theme->set('help', $help);
		$theme->set('label', $label);
		$theme->set('desc', $desc);
		$theme->set('required', $required);

		return $theme->output('admin/helpers/form/label');
	}

	/**
	 * Renders the registration type form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function lists($name, $value, $id = '', $attributes = '', $options = array())
	{
		$attributes = $this->formatAttributes($attributes);

		if (!$id) {
			$id = $name;
		}

		// Ensure that options are all objects
		if ($options) {
			foreach ($options as &$option) {
				$option = (object) $option;
			}
		}

		$theme = PP::themes();
		$theme->set('options', $options);
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);

		$output = $theme->output('admin/helpers/form/list');

		return $output;
	}

	/**
	 * Renders checkbox
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function checkbox($name, $value, $id = '', $attributes = '', $options = array())
	{
		$attributes = $this->formatAttributes($attributes);

		if (!$id) {
			$id = $name;
		}

		$theme = PP::themes();
		$theme->set('options', $options);
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);

		$output = $theme->output('admin/helpers/form/checkbox');

		return $output;
	}

	/**
	 * Renders the ordering hidden inputs
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function ordering($ordering, $direction)
	{
		$theme = PP::themes();
		$theme->set('ordering', $ordering);
		$theme->set('direction', $direction);

		$content = $theme->output('admin/helpers/form/ordering');
		return $content;
	}

	/**
	 * Renders a hidden input for tokens
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function token()
	{
		$token = PP::token();

		$theme = PP::themes();
		$theme->set('token', $token);

		$content = $theme->output('admin/helpers/form/token');

		return $content;
	}

	/**
	 * Generates a text input
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function text($name, $value, $id = '', $attributes = '', $options = array())
	{
		$attributes = $this->formatAttributes($attributes);

		if (!$id) {
			$id = $name;
		}

		if (is_object($options)) {
			$options = (array) $options;
		}

		$size = PP::normalize($options, 'size', '');
		$postfix = PP::normalize($options, 'postfix', '');
		$prefix = PP::normalize($options, 'prefix', '');
		$classes = PP::normalize($options, 'class', '');
		$placeholder = PP::normalize($options, 'placeholder', '');

		$theme = PP::themes();
		$theme->set('classes', $classes);
		$theme->set('placeholder', $placeholder);
		$theme->set('size', $size);
		$theme->set('postfix', $postfix);
		$theme->set('prefix', $prefix);
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);

		$output = $theme->output('admin/helpers/form/text');

		return $output;
	}

	/**
	 * Generates a textarea input
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function textarea($name, $value, $id = '', $attributes = '')
	{
		$attributes = $this->formatAttributes($attributes);

		if (!$id) {
			$id = $name;
		}

		$theme = PP::themes();
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);

		$output = $theme->output('admin/helpers/form/textarea');

		return $output;
	}

	/**
	 * Generates an on/off switch
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function toggler($name, $value, $id = '', $attributes = '', $options = array(), $dependents = array())
	{
		$attributes = $this->formatAttributes($attributes);

		if (!$id) {
			$id = $name;
		}

		$theme = PP::themes();
		$theme->set('dependents', $dependents);
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);

		$output = $theme->output('admin/helpers/form/toggler');

		return $output;
	}

	/**
	 * Renders the status dropdown selection
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function status($name, $selected, $type, $id = '', $multiple = false, $attributes = '', $excludeOptions = array())
	{
		$options = array();
		$attributes = $this->formatAttributes($attributes);

		if ($type == 'subscription') {
			$options[PP_SUBSCRIPTION_ACTIVE] = JText::_('COM_PP_SUBSCRIPTION_ACTIVE');
			$options[PP_SUBSCRIPTION_HOLD] = JText::_('COM_PP_SUBSCRIPTION_HOLD');
			$options[PP_SUBSCRIPTION_EXPIRED] = JText::_('COM_PP_SUBSCRIPTION_EXPIRED');
			$options[PP_SUBSCRIPTION_NONE] = JText::_('COM_PP_SUBSCRIPTION_NONE');
		}

		if ($type == 'invoice') {
			$options[PP_INVOICE_CONFIRMED] = JText::_('COM_PP_INVOICE_CONFIRMED');
			$options[PP_INVOICE_PAID] = JText::_('COM_PP_INVOICE_PAID');
			$options[PP_INVOICE_REFUNDED] = JText::_('COM_PP_INVOICE_REFUNDED');
			$options[PP_INVOICE_WALLET_RECHARGE] = JText::_('COM_PP_INVOICE_WALLET_RECHARGE');
		}

		if ($type == 'both') {
			$options[PP_SUBSCRIPTION_ACTIVE] = JText::_('Subscriptions') . ' (' . JText::_('COM_PP_SUBSCRIPTION_ACTIVE') . ')';
			$options[PP_SUBSCRIPTION_HOLD] = JText::_('Subscriptions') . ' (' . JText::_('COM_PP_SUBSCRIPTION_HOLD') . ')';
			$options[PP_SUBSCRIPTION_EXPIRED] = JText::_('Subscriptions') . ' (' . JText::_('COM_PP_SUBSCRIPTION_EXPIRED') . ')';
			$options[PP_SUBSCRIPTION_NONE] = JText::_('Subscriptions') . ' (' . JText::_('COM_PP_SUBSCRIPTION_NONE') . ')';
			$options[PP_INVOICE_CONFIRMED] = JText::_('Invoice') . ' (' . JText::_('COM_PP_INVOICE_CONFIRMED') . ')';
			$options[PP_INVOICE_PAID] = JText::_('Invoice') . ' (' . JText::_('COM_PP_INVOICE_PAID') . ')';
			$options[PP_INVOICE_REFUNDED] = JText::_('Invoice') . ' (' . JText::_('COM_PP_INVOICE_REFUNDED') . ')';
		}

		if ($excludeOptions) {
			foreach ($excludeOptions as $excludeOption) {
				if (isset($options[$excludeOption])) {
					unset($options[$excludeOption]);
				}
			}
		}

		if (!is_array($selected)) {
			$selected = PP::makeArray($selected);
		}

		$theme = PP::themes();
		$theme->set('selected', $selected);
		$theme->set('name', $name);
		$theme->set('options', $options);
		$theme->set('attributes', $attributes);
		$theme->set('multiple', $multiple);
		$output = $theme->output('admin/helpers/form/status');

		return $output;
	}


	/**
	 * Renders a colour picker input
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function colorpicker($name, $value = '', $revert = '')
	{
		static $script = null;
		$loadScript = false;

		if (is_null($script)) {
			$loadScript = true;
			$script = true;
		}

		JHTML::_('behavior.colorpicker');

		$theme = PP::themes();
		$theme->set('loadScript', $loadScript);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('revert', $revert);

		$output = $theme->output('admin/helpers/form/colorpicker');

		return $output;
	}

	/**
	 * Renders a dropdown of countries
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function country($name, $value = '', $id = '', $attributes = '', $options = array())
	{
		$attributes = $this->formatAttributes($attributes);

		$multiple = false;
		$allowAll = false;

		if (isset($options['multiple']) && $options['multiple']) {
			$multiple = true;
		}

		if (isset($options['allowAll']) && $options['allowAll']) {
			$allowAll = true;
		}

		$options = array();
		$model = PP::model('Country');
		$countries = $model->loadRecords();

		$floatLabel = false;

		if (isset($options['floatLabel']) && $options['floatLabel']) {
			$floatLabel = true;
		}

		$theme = PP::themes();
		$theme->set('floatLabel', $floatLabel);
		$theme->set('attributes', $attributes);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('countries', $countries);
		$theme->set('multiple', $multiple);
		$theme->set('allowAll', $allowAll);
		$output = $theme->output('admin/helpers/form/country');

		return $output;
	}

	/**
	 * Renders the registration type form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function timer($name, $value, $id = '', $attributes = '', $options = array())
	{
		$dateSegments = array(
			'year' => 10,
			'month' => 11,
			'day' => 30,
			'hour' => 23,
			'minute' => 59,
			'second' => 59
		);

		if (!$value) {
			$value = '000000000000';
		} else if (stripos($value, 'NaN') !== false) {
			// fix legacy value.
			$value = str_replace('NaN', '00', $value);
		}

		$values = str_split($value, 2);

		// split the values into correct segments
		list($year,$month,$day,$hour,$minute,$second) = $values;

		$segments = array();

		$displayTitle = '';

		foreach ($dateSegments as $key => $limit) {
			$options = array();

			for ($i = 0; $i <= $limit; $i++) {
				$obj = new stdClass();
				$obj->title = $i;
				// $obj->value = str_pad($i, 2, '0', STR_PAD_LEFT);
				$obj->value = $i;
				$obj->selected = false;

				$val = $$key;
				$val = (int) $val;

				if ($val == $i) {
					$obj->selected = true;
				}

				$options[] = $obj;
			}

			$segments[$key] = $options;
		}

		$displayTitle = PP::string()->formatTimer($value);

		$theme = PP::themes();
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('segments', $segments);
		$theme->set('attributes', $attributes);
		$theme->set('displayTitle', $displayTitle);

		$output = $theme->output('admin/helpers/form/timer');

		return $output;
	}

	/**
	 * Renders a textbox with the ability to browse users
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function user($name, $selectedUser = null, $id = '', $attributes = '', $options = array())
	{
		if (!$id) {
			$id = str_ireplace(array('.', ' ', '_'), '-', $name);
		}

		$theme = PP::themes();
		$theme->set('name', $name);
		$theme->set('id', $id);
		$theme->set('selectedUser', $selectedUser);
		$theme->set('attributes', $attributes);

		$output = $theme->output('admin/helpers/form/user');

		return $output;
	}

	/**
	 * Renders a textbox with the ability to browse users
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function usersubscriptions($name, $value, $id = '', $attributes = '', $userId = null)
	{
		$theme = PP::themes();

		$attributes = $this->formatAttributes($attributes);

		if (!$id) {
			$id = $name;
		}

		if ($value) {
			$value = ltrim($value, ',');
			$value = rtrim($value, ',');

			$value = explode(',', $value);
		}

		// Ensure that options are all objects
		$user = PP::user($userId);
		$subscriptions = $user->getSubscriptions();
		$options = array();

		if ($subscriptions) {
			foreach ($subscriptions as $subscription) {
				$option = new stdClass();
				$option->title = $subscription->getId();
				$option->value = $subscription->getId();

				$options[] = $option;
			}
		}

		JHtml::_('formbehavior.chosen', '.pp-autocomplete', null);

		$attributes = 'multiple="true"';

		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);
		$theme->set('options', $options);

		$output = $theme->output('admin/helpers/form/usersubscriptions');

		return $output;
	}

	/**
	 * Generates a radio input
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function radio($name, $value, $checked, $label, $id = '', $attributes = array())
	{
		$attributes = $this->formatAttributes($attributes);
		$label = JText::_($label);

		$theme = PP::themes();
		$theme->set('name', $name);
		$theme->set('id', $id);
		$theme->set('checked', $checked);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);
		$theme->set('label', $label);

		$output = $theme->output('admin/helpers/form/radio');

		return $output;
	}

	/**
	 * Generates a hidden input for return url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function returnUrl($name = 'return', $value = '')
	{
		if (!$value) {
			$value = base64_encode(JRequest::getUri());
		}

		return $this->hidden($name, $value);
	}

	/**
	 * Renders the registration type form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function registrationType($name, $value, $id = '', $attributes = '')
	{
		$attributes = $this->formatAttributes($attributes);

		if (!$id) {
			$id = $name;
		}

		$registration = PP::registration();

		$adapters = $registration->getAdapters();

		foreach ($adapters as $adapter) {

			$plugin = new stdClass();
			$plugin->element = $adapter;
			$plugin->title = JText::_('COM_PAYPLANS_REGISTRATION_TYPE_' . strtoupper($adapter));

			$plugins[] = $plugin;
		}

		$theme = PP::themes();
		$theme->set('plugins', $plugins);
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);

		$output = $theme->output('admin/helpers/form/registration.type');

		return $output;
	}

	/**
	 * Renders the rewriter list
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function rewriter()
	{
		$theme = PP::themes();

		$output = $theme->output('admin/helpers/form/rewriter');

		return $output;
	}

	/**
	 * Renders a apps dropdown
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function apps($name, $apps = array(), $editable = true, $attributes = '')
	{
		static $applist = null;

		if (is_null($applist)) {
			$applist = PP::model('app')->loadRecords();
		}

		$selections = array();
		$appIds = array();

		if ($apps) {
			foreach ($apps as $app) {
				$appIds[] = $app->app_id;
			}
		}

		if ($applist) {
			foreach ($applist as $app) {
				if ($app->published) {
					$obj = new stdClass();

					$obj->id = $app->app_id;
					$obj->title = $app->title;
					$obj->type = $app->type;
					$obj->selected = false;
					if (in_array($app->app_id, $appIds)) {
						$obj->selected = true;
					}

					$selections[] = $obj;
				}
			}
		} else if ($apps) {
			// just use the provided app as selections
			foreach ($apps as $app) {

				$obj = new stdClass();

				$obj->id = $app->app_id;
				$obj->title = $app->title;
				$obj->type = $app->type;
				$obj->selected = true;

				$selections[] = $obj;
			}
		}


		$theme = PP::themes();
		$theme->set('apps', $apps);
		$theme->set('name', $name);
		$theme->set('attributes', $attributes);
		$theme->set('editable', $editable);
		$theme->set('selections', $selections);

		$output = $theme->output('admin/helpers/form/apps');
		return $output;
	}

	/**
	 * Renders the groups dropdown
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function groups($name, $groups = array(), $editable = true, $attributes = '', $multiple = true)
	{
		static $grouplist = null;

		if (is_null($grouplist)) {
			$grouplist = PP::model('group')->loadRecords();
		}

		$selections = array();
		$groupIds = array();

		if ($groups) {
			if (!is_array($groups)) {
				$groups = array($groups);
			}

			foreach ($groups as $group) {

				if (is_object($group)) {
					$groupIds[] = $group->group_id;

					continue;
				}

				$groupIds[] = $group;
			}
		}

		if ($grouplist) {
			foreach ($grouplist as $group) {
				if ($group->published && $group->visible) {
					$obj = new stdClass();

					$obj->id = $group->group_id;
					$obj->title = $group->title;
					$obj->selected = false;
					if (in_array($group->group_id, $groupIds)) {
						$obj->selected = true;
					}

					$selections[] = $obj;
				}
			}
		} else if ($groups) {
			// just use the provided group as selections
			foreach ($groups as $group) {

				$obj = new stdClass();

				$obj->id = $group->group_id;
				$obj->title = $group->title;
				$obj->selected = true;

				$selections[] = $obj;
			}
		}

		$theme = PP::themes();
		$theme->set('multiple', $multiple);
		$theme->set('groups', $groups);
		$theme->set('name', $name);
		$theme->set('attributes', $attributes);
		$theme->set('editable', $editable);
		$theme->set('selections', $selections);

		$output = $theme->output('admin/helpers/form/groups');
		return $output;
	}

	/**
	 * Inserts a validation block
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function validate($message)
	{
		$theme = PP::themes();
		$theme->set('message', $message);

		$output = $theme->output('admin/helpers/form/validate');

		return $output;
	}

	/**
	 * Renders a form to browse user group
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function usergroups($name, $selected = array(), $id = null, $attributes = '', $options = array())
	{
		$model = PP::model('User');
		$groups = $model->getAllUserGroups();

		$multiple = isset($options['multiple']) ? $options['multiple'] : true;

		if (is_null($id)) {
			$id = self::normalizeId($name);
		}

		if (!is_array($selected)) {
			$selected = (array) $selected;
		}

		$readOnly = isset($options['readOnly']) ? $options['readOnly'] : false;

		if ($readOnly) {
			$readOnly = 'disabled="disabled"';
		}

		// Default width and height
		$minWidth = 350;
		$minHeight = 220;

		if (isset($options['minWidth'])) {
			$minWidth = $options['minWidth'];
		}

		if (isset($options['minHeight'])) {
			$minWidth = $options['minHeight'];
		}

		if ($multiple) {
			$name = $name . '[]';
		}

		$theme = PP::themes();
		$theme->set('minHeight', $minHeight);
		$theme->set('readOnly', $readOnly);
		$theme->set('id', $id);
		$theme->set('minWidth', $minWidth);
		$theme->set('name', $name);
		$theme->set('groups', $groups);
		$theme->set('selected', $selected);

		$output = $theme->output('admin/helpers/form/usergroups');

		return $output;
	}

	/**
	 * Render Lists of Joomla Article
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function joomlaArticle($name, $selected = array(), $id = null, $attributes = '', $options = array())
	{
		$articles = false;

		if ($options) {

			if (is_object($options)) {
				$options = JArrayHelper::fromObject($options);
			}
		}

		$multiple = isset($options['multiple']) ? $options['multiple'] : true;

		if ($selected != '' && !is_array($selected)) {
			$selected = (array)$selected;
		}

		if ($selected) {
			$db = PP::db();
			$query = 'SELECT `id`, `title` FROM ' . $db->qn('#__content') . ' WHERE `id` IN(' . implode(',', $selected) . ')';

			$db->setQuery($query);
			$articles = $db->loadObjectList();
		}

		if ($multiple) {
			$name = $name . '[]';
		}

		$theme = PP::themes();
		$theme->set('articles', $articles);
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('attributes', $attributes);
		$theme->set('multiple', $multiple);

		$output = $theme->output('admin/helpers/form/jarticle');

		return $output;
	}

	/**
	 * Render lists of joomla category
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function joomlaCategory($name, $selected = array(), $id = null, $attributes = '', $options = array())
	{
		if ($options) {
			if (is_array($options)) {
				$options = $options[0];
			}
			if (is_object($options)) {
				$options = JArrayHelper::fromObject($options);
			}
		}

		$multiple = isset($options['multiple']) ? $options['multiple'] : true;

		$categories = false;

		if ($selected) {
			$db = PP::db();
			$query = 'SELECT `id`, `title` FROM ' . $db->qn('#__categories') . ' WHERE `id` IN(' . implode(',', $selected) . ')';
			$query .= ' AND `extension` = ' . $db->Quote('com_content');

			$db->setQuery($query);
			$categories = $db->loadObjectList();
		}

		if ($multiple) {
			$name = $name . '[]';
		}

		$theme = PP::themes();
		$theme->set('categories', $categories);
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('attributes', $attributes);

		$output = $theme->output('admin/helpers/form/jcategory');

		return $output;
	}

	/**
	 * Renders modules selection
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function menus($name, $value = array(), $id = null, $attributes = '')
	{
		$db = PP::db();

		require_once(realpath(JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php'));

		$menus = MenusHelper::getMenuLinks();

		JHtml::_('formbehavior.chosen', '.pp-autocomplete', null);

		if (!is_array($value)) {
			$value = array($value);
		}

		$theme = PP::themes();
		$theme->set('value', $value);
		$theme->set('menus', $menus);
		$theme->set('name', $name);
		$theme->set('attributes', $attributes);

		$output = $theme->output('admin/helpers/form/menus');

		return $output;

	}


	/**
	 * Renders modules selection
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function menulist($name, $value = array(), $id = null)
	{
		$db = PP::db();

		require_once(realpath(JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php'));

		$menus = MenusHelper::getMenuLinks();

		// foreach($menus as $menu) {
		// 	dump($menu->links);
		// }

		if (!is_array($value)) {
			$value = array($value);
		}

		$theme = PP::themes();
		$theme->set('selected', $value);
		$theme->set('menus', $menus);
		$theme->set('name', $name);

		$output = $theme->output('admin/helpers/form/menulist');

		return $output;

	}


	/**
	 * Renders modules selection
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function modules($name, $selected = array(), $id = null, $attributes = '', $options = array())
	{
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT m.`id`, m.`title` FROM ' . $db->qn('#__modules') . ' as m';
		$query[] = 'LEFT JOIN ' . $db->qn('#__extensions') . ' as e ON e.`element` = m.`module`';
		$query[] = 'AND e.`client_id` = m.`client_id`';
		$query[] = 'WHERE m.`published` = ' . $db->Quote(1);
		$query[] = 'AND e.`enabled` = ' . $db->Quote(1);

		// $now = PP::date();
		// $nullDate = $db->getNullDate();

		// $query[] = 'AND (m.`publish_up` = ' . $db->Quote($nullDate) . ' OR m.`publish_up` <= ' . $db->Quote($now) . ')';
		// $query[] = 'AND (m.`publish_down` = ' . $db->Quote($nullDate) . ' OR m.`publish_down` >= ' . $db->Quote($now) . ')';
		$query[] = 'AND m.`client_id` = ' . $db->Quote(0);
		$query[] = 'ORDER BY m.`title`';

		$db->setQuery($query);
		$modules = $db->loadObjectList('id');

		if (is_null($id)) {
			$id = self::normalizeId($name);
		}

		$theme = PP::themes();
		$theme->set('modules', $modules);
		$theme->set('name', $name);
		$theme->set('attributes', $attributes);
		$theme->set('selected', $selected);
		$theme->set('id', $id);

		$output = $theme->output('admin/helpers/form/modules');

		return $output;
	}

	/**
	 * Given a string, generate a valid id that can be used in forms
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function normalizeId($value)
	{
		$value = str_ireplace(array(' ', '.'), '-', $value);

		return $value;
	}

	/**
	* Render autocomplete form for Easysocial profile types
	*
	* @since	4.0.0
	* @access	public
	*/
	public function easysocialBadges($name, $value, $id = '', $attributes = '')
	{
		$theme = PP::themes();

		$attributes = $this->formatAttributes($attributes);

		if(!$id) {
			$id = $name;
		}

		$lib = PP::easysocial();

		if (!$lib->exists()) {
			return JText::_('COM_PAYPLANS_PLEASE_INSTALL_EASYSOCIAL_BEFORE_USING_THIS_APPLICATION');
		}

		ES::language()->loadSite();

		$badges = $lib->getBadges();

		JHtml::_('formbehavior.chosen', '.pp-autocomplete', null);

		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);
		$theme->set('badges', $badges);

		$output = $theme->output('admin/helpers/form/easysocial.badges');

		return $output;
	}

	/**
	* Render autocomplete form for Easysocial profile types
	*
	* @since	4.0.0
	* @access	public
	*/
	public function easysocialProfileType($name, $value, $id = '', $attributes = '')
	{
		$theme = PP::themes();

		$attributes = $this->formatAttributes($attributes);

		if(!$id) {
			$id = $name;
		}

		$lib = PP::easysocial();

		if (!$lib->exists()) {
			return JText::_('COM_PAYPLANS_PLEASE_INSTALL_EASYSOCIAL_BEFORE_USING_THIS_APPLICATION');
		}

		$profileTypes = $lib->getProfileTypes();

		JHtml::_('formbehavior.chosen', '.pp-autocomplete', null);

		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);
		$theme->set('profileTypes', $profileTypes);

		$output = $theme->output('admin/helpers/form/easysocial.profiletypes');

		return $output;
	}

	/**
	 * Renders Acymailing list
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function acymailingLists($name, $value, $id = '', $attributes = '')
	{
		$theme = PP::themes();

		$attributes = $this->formatAttributes($attributes);

		if (!$id) {
			$id = $name;
		}

		$lib = PP::acymailing();

		if (!$lib->exists()) {
			return JText::_('COM_PAYPLANS_PLEASE_INSTALL_ACYMAILING_BEFORE_USING_THIS_APPLICATION');
		}

		$lists = $lib->getLists();

		JHtml::_('formbehavior.chosen', '.pp-autocomplete', null);

		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);
		$theme->set('lists', $lists);

		$output = $theme->output('admin/helpers/form/acymailing.lists');

		return $output;
	}

	/**
	 * Renders an Vitruemart Shoppers group
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function shoppersGroups($name, $value, $id = '', $attributes = '')
	{
		$theme = PP::themes();

		$attributes = $this->formatAttributes($attributes);

		if (!$id) {
			$id = $name;
		}

		$lib = PP::virtuemart();

		if (!$lib->exists()) {
			return JText::_('COM_PAYPLANS_PLEASE_INSTALL_VIRTUE_MART_BEFORE_USING_THIS_APPLICATION');
		}

		$groups = $lib->getGroups();

		JHtml::_('formbehavior.chosen', '.pp-autocomplete', null);

		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);
		$theme->set('groups', $groups);

		$output = $theme->output('admin/helpers/form/vitruemart.groups');

		return $output;
	}

	/**
	 * Renders the date range form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function dateRange($selected = '', $name = 'dateRange', $placeholder = '')
	{
		if (!$placeholder) {
			$placeholder = 'COM_PP_SELECT_DATE_RANGE';
		}

		$placeholder = JText::_($placeholder);

		// Get today
		$start = false;
		$end = false;

		if ($selected && is_array($selected)) {
			$start = $selected['start'];
			$end = $selected['end'];
		}

		$uid = uniqid();

		$theme = PP::themes();
		$theme->set('uid', $uid);
		$theme->set('start', $start);
		$theme->set('end', $end);
		$theme->set('name', $name);
		$theme->set('placeholder', $placeholder);
		$theme->set('selected', $selected);
		$output = $theme->output('admin/helpers/form/daterange');

		return $output;
	}

	/**
	* Render autocomplete form for Easysocial profile types
	*
	* @since	4.0.0
	* @access	public
	*/
	public function jomsocialMultiprofile($name, $value, $id = '', $attributes = '')
	{
		$theme = PP::themes();

		$attributes = $this->formatAttributes($attributes);

		if(!$id) {
			$id = $name;
		}

		$lib = PP::jomsocial();

		if (!$lib->exists()) {
			return JText::_('COM_PP_PLEASE_INSTALL_JOMSOCIAL_BEFORE_USING_THIS_APPLICATION');
		}

		$profileTypes = $lib->getProfiles();

		JHtml::_('formbehavior.chosen', '.pp-autocomplete', null);

		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);
		$theme->set('profileTypes', $profileTypes);

		$output = $theme->output('admin/helpers/form/jomsocial.multiprofile');

		return $output;
	}

	/**
	 * Renders offline payment methods
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function offlinePayment($name, $value = '', $id = '', $attributes = '', $options = array())
	{
		$attributes = $this->formatAttributes($attributes);

		$multiple = false;

		if (isset($options['multiple']) && $options['multiple']) {
			$multiple = true;
		}

		$options = array();

		// currently only 3 available payment method for offline
		$paymentMethods = array(
			array('value' => 'Cash', 'title' => 'COM_PAYPLANS_CASH'),
			array('value' => 'Cheque', 'title' => 'COM_PP_CHEQUE'),
			array('value' => 'Wiretransfer', 'title' => 'COM_PP_WIRETRANSFER'));

		$floatLabel = false;

		if (isset($options['floatLabel']) && $options['floatLabel']) {
			$floatLabel = true;
		}

		$theme = PP::themes();
		$theme->set('floatLabel', $floatLabel);
		$theme->set('attributes', $attributes);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('paymentMethods', $paymentMethods);
		$theme->set('multiple', $multiple);
		$output = $theme->output('admin/helpers/form/offlinepayment');

		return $output;
	}	
}
