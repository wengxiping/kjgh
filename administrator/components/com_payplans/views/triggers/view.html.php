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

class PayplansViewUser extends PayPlansAdminView
{
	public function display($tpl = null)
	{
		$this->heading('Users');

		JToolbarHelper::editList();
		JToolbarHelper::custom('selectPlan', 'assign.png', 'assign.png', 'COM_PAYPLANS_USER_TOOLBAR_APPLY_PLAN', true);

		$model = PP::model('User');
		$model->initStates();

		$users = $model->getItems();
		$pagination = $model->getPagination();

		$states = $this->getStates(array('username', 'plan_id', 'subscription_status', 'usertype', 'limit', 'limitstart', 'ordering', 'direction'));

		$this->set('states', $states);
		$this->set('users', $users);
		$this->set('pagination', $pagination);

		return parent::display('user/default/default');
	}

	/**
	 * Renders the edit user form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function form($tpl=null)
	{
		$model = PP::model('User');

		$itemId = $this->input->get('id', null, 'int');
		$itemId = ($itemId === null) ? $model->getState('id') : $itemId;

		JToolbarHelper::apply();
		JToolbarHelper::save();
		JToolbarHelper::save2new();
		JToolbarHelper::cancel();

		if ($itemId) {
			JToolbarHelper::deleteList();
		}

		// assert if user id is not available
		XiError::assert($itemId, JText::_('COM_PAYPLANS_ERROR_INVALID_USER_ID'));

		$orderRecords = PP::model('order')->loadRecords(array('buyer_id'=>$itemId));
		$logRecords = PP::model('log')->loadRecords(array('object_id'=>$itemId, 'class'=>'PayplansUser'));

		$user = PP::user( $itemId);

		$order = PayplansOrder::getInstance();
		$payment = PayplansPayment::getInstance();


		$form = $user->getModelform()->getForm($user);
		//load extra xml file
		$form->loadFile(PAYPLANS_PATH_XML.DS.'user.preference.xml', false, '//config');
		$preference = $user->getPreference();
		$data = array('preference'=>$preference->toArray());
		$form->bind($data);
		$this->set('form', $form );



		$this->set('user', $user);
		$this->set('order', $order);
		$this->set('payment', $payment);

		$this->set('order_records', $orderRecords);
		$this->set('log_records', $logRecords);

		parent::display('user/form');
		return true;
	}
}

