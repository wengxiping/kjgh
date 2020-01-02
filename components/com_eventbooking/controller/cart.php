<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

class EventbookingControllerCart extends EventbookingController
{
	use EventbookingControllerCaptcha;

	/**
	 * Add the selected events to shopping cart
	 *
	 * @throws Exception
	 */
	public function add_cart()
	{
		$data = $this->input->getData();

		if (is_numeric($data['id']))
		{
			// Check if this is event is password protected
			$event = EventbookingHelperDatabase::getEvent((int) $data['id']);

			if ($event->event_password)
			{
				$passwordPassed = JFactory::getSession()->get('eb_passowrd_' . $event->id, 0);

				if (!$passwordPassed)
				{
					$return = base64_encode(JUri::getInstance()->toString());
					$this->app->redirect(JRoute::_('index.php?option=com_eventbooking&view=password&event_id=' . $event->id . '&return=' . $return . '&Itemid=' . $this->input->getInt('Itemid', 0), false));
				}
				else
				{
					// Add event to cart, then redirect to cart page

					/* @var EventbookingModelCart $model */
					$model = $this->getModel('cart');
					$model->processAddToCart($data);
					$Itemid = $this->input->getInt('Itemid', 0);
					$this->app->redirect(JRoute::_(EventbookingHelperRoute::getViewRoute('cart', $Itemid), false));
				}
			}
		}

		/* @var EventbookingModelCart $model */
		$model = $this->getModel('cart');
		$model->processAddToCart($data);

		$this->input->set('view', 'cart');
		$this->input->set('layout', 'mini');

		$this->reloadCartModule();

		$this->display();

		$this->app->close();
	}

	/**
	 * Add selected events to cart and redirect to checkout page
	 *
	 */
	public function add_events_to_cart()
	{
		$config   = EventbookingHelper::getConfig();
		$eventIds = $this->input->post->getString('event_ids');
		$eventIds = explode(',', $eventIds);
		$eventIds = array_filter(ArrayHelper::toInteger($eventIds));
		$Itemid   = $this->input->getInt('Itemid');

		$data['id'] = $eventIds;

		/* @var EventbookingModelCart $model */
		$model = $this->getModel('cart');
		$model->processAddToCart($data);

		// Redirect to checkout page

		if ($config->use_https)
		{
			$checkoutUrl = JRoute::_('index.php?option=com_eventbooking&task=view_checkout&Itemid=' . $Itemid, false, 1);
		}
		else
		{
			$checkoutUrl = JRoute::_('index.php?option=com_eventbooking&task=view_checkout&Itemid=' . $Itemid, false, 0);
		}

		$this->app->redirect($checkoutUrl);
	}

	/**
	 * Update the cart with new updated quantities
	 *
	 * @throws Exception
	 */
	public function update_cart()
	{
		$Itemid     = $this->input->getInt('Itemid', 0);
		$redirect   = $this->input->getInt('redirect', 1);
		$eventIds   = $this->input->get('event_id', '', 'none');
		$quantities = $this->input->get('quantity', '', 'none');

		/* @var EventbookingModelCart $model */
		$model = $this->getModel('cart');

		if (!$redirect)
		{
			$eventIds   = explode(',', $eventIds);
			$quantities = explode(',', $quantities);
		}

		$model->processUpdateCart($eventIds, $quantities);

		if ($redirect)
		{
			$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('cart', $Itemid), false));
		}
		else
		{
			$this->input->set('view', 'cart');
			$this->input->set('layout', 'mini');
			$this->reloadCartModule();
			$this->display();
			$this->app->close();
		}
	}

	/**
	 * Remove the selected event from shopping cart
	 */
	public function remove_cart()
	{
		$redirect = $this->input->getInt('redirect', 1);
		$Itemid   = $this->input->getInt('Itemid', 0);
		$id       = $this->input->getInt('id', 0);

		/* @var EventbookingModelCart $model */
		$model = $this->getModel('cart');
		$model->removeEvent($id);

		if ($redirect)
		{
			$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('cart', $Itemid), false));
		}
		else
		{
			$this->input->set('view', 'cart');
			$this->input->set('layout', 'mini');

			$this->reloadCartModule();

			$this->display();

			$this->app->close();
		}
	}

	/***
	 * Process checkout
	 *
	 * @throws Exception
	 */
	public function process_checkout()
	{
		$user   = JFactory::getUser();
		$config = EventbookingHelper::getConfig();
		$errors = array();

		if (!$this->validateCaptcha($this->input))
		{
			$errors[] = JText::_('EB_INVALID_CAPTCHA_ENTERED');
		}

		$cart  = new EventbookingHelperCart();
		$items = $cart->getItems();

		if (!count($items))
		{
			$this->app->enqueueMessage(JText::_('Sorry, your session was expired. Please try again!'), 'warning');
			$this->app->redirect(JUri::root());
		}

		// Validate username and password
		if (!$user->id && $config->user_registration)
		{
			$errors = array_merge($errors, EventbookingHelperRegistration::validateUsername($this->input->post->get('username', '', 'raw')));
			$errors = array_merge($errors, EventbookingHelperRegistration::validatePassword($this->input->post->get('password1', '', 'raw')));
		}

		// Check email
		$result = $this->validateRegistrantEmail($items, $this->input->get('email', '', 'none'));

		if (!$result['success'])
		{
			$errors[] = $result['message'];
		}

		$data = $this->input->post->getData();

		if ($formErrors = $this->validateFormData($data))
		{
			$errors = array_merge($errors, $formErrors);
		}

		if (count($errors))
		{
			// Enqueue the error message
			foreach ($errors as $error)
			{
				$this->app->enqueueMessage($error, 'error');
			}

			$this->input->set('captcha_invalid', 1);
			$this->input->set('view', 'register');
			$this->input->set('layout', 'cart');
			$this->display();

			return;
		}

		/* @var EventbookingModelCart $model */
		$model  = $this->getModel('cart');
		$return = $model->processCheckout($data);

		if ($return == 1)
		{
			// Redirect to registration complete page
			if (JPluginHelper::isEnabled('system', 'cache'))
			{
				$this->setRedirect(JRoute::_('index.php?option=com_eventbooking&view=complete&Itemid=' . $this->input->getInt('Itemid') . '&pt=' . time(), false, false));
			}
			else
			{
				$this->setRedirect(JRoute::_('index.php?option=com_eventbooking&view=complete&Itemid=' . $this->input->getInt('Itemid'), false, false));
			}
		}
	}

	/**
	 * Calculate registration fee, then update information on cart registration form
	 */
	public function calculate_cart_registration_fee()
	{
		$input               = $this->input;
		$config              = EventbookingHelper::getConfig();
		$paymentMethod       = $input->getString('payment_method', '');
		$data                = $input->post->getData();
		$data['coupon_code'] = $input->getString('coupon_code', '');
		$cart                = new EventbookingHelperCart();
		$response            = array();
		$rowFields           = EventbookingHelperRegistration::getFormFields(0, 4);
		$form                = new RADForm($rowFields);
		$form->bind($data);

		$fees = EventbookingHelper::callOverridableHelperMethod('Registration', 'calculateCartRegistrationFee', [$cart, $form, $data, $config, $paymentMethod], 'Helper');

		$response['total_amount']           = EventbookingHelper::formatAmount($fees['total_amount'], $config);
		$response['discount_amount']        = EventbookingHelper::formatAmount($fees['discount_amount'], $config);
		$response['tax_amount']             = EventbookingHelper::formatAmount($fees['tax_amount'], $config);
		$response['payment_processing_fee'] = EventbookingHelper::formatAmount($fees['payment_processing_fee'], $config);
		$response['amount']                 = EventbookingHelper::formatAmount($fees['amount'], $config);
		$response['deposit_amount']         = EventbookingHelper::formatAmount($fees['deposit_amount'], $config);
		$response['coupon_valid']           = $fees['coupon_valid'];
		$response['payment_amount']         = round($fees['amount'], 2);

		echo json_encode($response);

		$this->app->close();
	}

	/**
	 * Validate form data, make sure the required fields are entered
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	protected function validateFormData($data)
	{
		$config    = EventbookingHelper::getConfig();
		$rowFields = EventbookingHelperRegistration::getFormFields(0, 4);

		$form = new RADForm($rowFields);
		$form->bind($data)
			->buildFieldsDependency();
		$errors = [];

		// Validate members input
		if ($config->collect_member_information_in_cart)
		{
			$cart       = new EventbookingHelperCart();
			$items      = $cart->getItems();
			$quantities = $cart->getQuantities();
			$count      = 0;

			for ($i = 0, $n = count($items); $i < $n; $i++)
			{
				$eventId  = $items[$i];
				$quantity = $quantities[$i];
				$event    = EventbookingHelperDatabase::getEvent($eventId);

				$memberFormFields = EventbookingHelperRegistration::getFormFields($eventId, 2);

				for ($j = 0; $j < $quantity; $j++)
				{
					$count++;
					$currentMemberFormFields = EventbookingHelperRegistration::getGroupMemberFields($memberFormFields, $j + 1);
					$memberForm              = new RADForm($currentMemberFormFields);
					$memberForm->setFieldSuffix($count);
					$memberForm->bind($data);
					$memberForm->buildFieldsDependency();
					$memberErrors = $memberForm->validate();

					if (count($memberErrors))
					{
						foreach ($memberErrors as $memberError)
						{
							$errors[] = JText::sprintf('EB_MEMBER_VALIDATION_ERROR', $event->title, $j + 1) . ' ' . $memberError;
						}
					}
				}
			}
		}

		$errors = array_merge($errors, $form->validate());

		// Validate privacy policy
		if ($config->show_privacy_policy_checkbox && empty($data['agree_privacy_policy']))
		{
			$errors[] = JText::_('EB_AGREE_PRIVACY_POLICY_ERROR');
		}

		return $errors;
	}

	/**
	 * Validate to see whether this email can be used to register for this event or not
	 *
	 * @param array $eventIds
	 * @param       $email
	 *
	 * @return array
	 */
	protected function validateRegistrantEmail($eventIds, $email)
	{
		$user   = JFactory::getUser();
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$config = EventbookingHelper::getConfig();
		$result = array(
			'success' => true,
			'message' => '',
		);

		if ($config->prevent_duplicate_registration)
		{
			$query->clear()
				->select('event_id')
				->from('#__eb_registrants')
				->where('event_id IN (' . implode(',', $eventIds) . ')')
				->where('(published=1 OR (payment_method LIKE "os_offline%" AND published NOT IN (2,3)))');

			if ($user->id)
			{
				$query->where('(user_id = ' . $user->id . ' OR email = ' . $db->quote($email) . ')');
			}
			else
			{
				$query->where('email = ' . $db->quote($email));
			}

			$db->setQuery($query);
			$registeredEventIds = $db->loadColumn();

			if (count($registeredEventIds))
			{
				$result['success'] = false;

				$query->clear()
					->select('title')
					->from('#__eb_events')
					->where('id IN (' . implode(',', $registeredEventIds) . ')');
				$db->setQuery($query);

				$result['message'] = JText::sprintf('EB_YOU_REGISTERED_FOR_EVENTS', implode(' | ', $db->loadColumn()));
			}
		}

		if ($result['success'] && $config->user_registration && !$user->id)
		{
			$query->clear()
				->select('COUNT(*)')
				->from('#__users')
				->where('email = ' . $db->quote($email));
			$db->setQuery($query);
			$total = $db->loadResult();

			if ($total)
			{
				$result['success'] = false;
				$result['message'] = JText::_('EB_EMAIL_USED_BY_DIFFERENT_USER');
			}
		}

		return $result;
	}

	/**
	 * Refresh content of cart module so that data will be keep synchronized
	 */
	protected function reloadCartModule()
	{
		$module = JModuleHelper::isEnabled('mod_eb_cart');

		if (!$module)
		{
			return;
		}
		?>
        <script type="text/javascript">
            Eb.jQuery(function ($) {
                $(document).ready(function () {
                    $.ajax({
                        type: 'POST',
                        url: 'index.php?option=com_eventbooking&view=cart&layout=module&format=raw',
                        dataType: 'html',
                        success: function (html) {
                            $('#cart_result').html(html);
                        }
                    })
                })
            })
        </script>
		<?php
	}
}
