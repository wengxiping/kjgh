<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die ;

EventbookingHelperJquery::validateForm();
JHtml::_('behavior.modal', 'a.eb-modal');

/* @var EventbookingViewRegisterHtml $this */

if ($this->config->use_https)
{
	$formUrl = JRoute::_('index.php?option=com_eventbooking&task=cart.process_checkout&Itemid=' . $this->Itemid, false, 1);
}
else
{
	$formUrl = JRoute::_('index.php?option=com_eventbooking&task=cart.process_checkout&Itemid=' . $this->Itemid, 0);
}

$selectedState = '';

/* @var EventbookingHelperBootstrap $bootstrapHelper */
$bootstrapHelper     = $this->bootstrapHelper;
$controlGroupClass   = $bootstrapHelper->getClassMapping('control-group');
$inputPrependClass   = $bootstrapHelper->getClassMapping('input-prepend');
$inputAppendClass    = $bootstrapHelper->getClassMapping('input-append');
$addOnClass          = $bootstrapHelper->getClassMapping('add-on');
$controlLabelClass   = $bootstrapHelper->getClassMapping('control-label');
$controlsClass       = $bootstrapHelper->getClassMapping('controls');
$btnPrimary          = $bootstrapHelper->getClassMapping('btn btn-primary');
$formHorizontalClass = $bootstrapHelper->getClassMapping('form form-horizontal');

$layoutData = array(
	'controlGroupClass' => $controlGroupClass,
	'controlLabelClass' => $controlLabelClass,
	'controlsClass'     => $controlsClass,
);
?>
<div id="eb-cart-registration-page" class="eb-container row-fluid">
<h1 class="eb-page-heading"><?php echo JText::_('EB_CHECKOUT'); ?></h1>
<?php
	if (strlen(strip_tags($this->message->{'registration_form_message'.$this->fieldSuffix})))
	{
		$msg = $this->message->{'registration_form_message'.$this->fieldSuffix};
	}
	else
	{
		$msg = $this->message->registration_form_message;
	}

	if (strlen($msg))
	{
		$msg = str_replace('[EVENT_TITLE]', $this->eventTitle, $msg) ;
		$msg = str_replace('[AMOUNT]', EventbookingHelper::formatCurrency($this->amount, $this->config), $msg) ;
	?>
        <div class="eb-message"><?php echo JHtml::_('content.prepare', $msg); ?></div>
	<?php
	}

	echo $this->loadTemplate('items');
?>
<div class="clearfix"></div>
<?php
	if (!$this->userId && ($this->config->user_registration || $this->config->show_user_login_section))
	{
		$validateLoginForm = true;

		echo $this->loadCommonLayout('register/register_login.php', $layoutData);
	}
	else
	{
		$validateLoginForm = false;
	}
?>
	<form method="post" name="adminForm" id="adminForm" action="<?php echo $formUrl; ?>" autocomplete="off" class="<?php echo $formHorizontalClass; ?>" enctype="multipart/form-data">
	<?php
		if (!$this->userId && $this->config->user_registration)
		{
			echo $this->loadCommonLayout('register/register_user_registration.php', $layoutData);
		}

		$hasMembersFeeField = false;

		// Collect registrants information
		if ($this->config->collect_member_information_in_cart)
		{
			$count = 0;

			foreach($this->items as $item)
			{
				$rowFields    = EventbookingHelperRegistration::getFormFields($item->id, 2);
				$eventHeading = JText::sprintf('EB_EVENT_REGISTRANTS_INFORMATION', $item->title);
				$eventHeading = str_replace('[EVENT_DATE]', JHtml::_('date', $item->event_date, $this->config->event_date_format, null), $eventHeading);
			?>
				<h3 class="eb-heading"><?php echo $eventHeading; ?></h3>
			<?php
				for ($i = 0 ; $i < $item->quantity; $i++)
				{
					$count++;
					$currentMemberFormFields = EventbookingHelperRegistration::getGroupMemberFields($rowFields, $i + 1);
					$form      = new RADForm($currentMemberFormFields);
					$form->setFieldSuffix($count);

					if (!isset($this->formData['country_' . $count]))
					{
						$formData['country_' . $count] = $this->config->default_country;
					}

					$form->bind($this->formData, $this->useDefault);
					$form->prepareFormFields('calculateCartRegistrationFee();');
					$form->buildFieldsDependency();
					$fields = $form->getFields();

					//We don't need to use ajax validation for email field for group members
					if (isset($fields['email']))
					{
						$emailField = $fields['email'];
						$cssClass   = $emailField->getAttribute('class');
						$cssClass   = str_replace(',ajax[ajaxEmailCall]', '', $cssClass);
						$emailField->setAttribute('class', $cssClass);
					}
				?>
					<h4 class="eb-heading"><?php echo JText::sprintf('EB_MEMBER_INFORMATION', $i + 1); ?></h4>
				<?php

					/* @var RADFormField $field */
					foreach ($fields as $field)
					{
					    if ($field->row->fee_field)
                        {
                            $hasMembersFeeField = true;
                        }

						echo $field->getControlGroup($bootstrapHelper);
					}
				}
			}
		?>
			<h3 class="eb-heading"><?php echo JText::_('EB_BILLING_INFORMATION'); ?></h3>
		<?php
		}

		$fields = $this->form->getFields();

		if (isset($fields['state']))
		{
			$selectedState = $fields['state']->value;
		}

		foreach ($fields as $field)
		{
			echo $field->getControlGroup($bootstrapHelper);
		}

		if ($this->totalAmount > 0 || $this->form->containFeeFields() || $hasMembersFeeField)
		{
			$showPaymentInformation = true;
		?>
			<h3 class="eb-heading"><?php echo JText::_('EB_PAYMENT_INFORMATION'); ?></h3>
		<?php
		$layoutData['currencySymbol']     = $this->config->currency_symbol;
		$layoutData['onCouponChange']     = 'calculateCartRegistrationFee();';
		$layoutData['addOnClass']         = $addOnClass;
		$layoutData['inputPrependClass']  = $inputPrependClass;
		$layoutData['inputAppendClass']   = $inputAppendClass;
		$layoutData['showDiscountAmount'] = ($this->enableCoupon || $this->discountAmount > 0 || $this->bunldeDiscount > 0);
		$layoutData['showTaxAmount']      = ($this->taxAmount > 0);
		$layoutData['showGrossAmount']    = ($this->enableCoupon || $this->discountAmount > 0 || $this->bunldeDiscount > 0 || $this->taxAmount > 0 || $this->showPaymentFee);

		echo $this->loadCommonLayout('register/register_payment_amount.php', $layoutData);

		$layoutData['registrationType'] = 'cart';
		echo $this->loadCommonLayout('register/register_payment_methods.php', $layoutData);
	}

	if ($this->config->show_privacy_policy_checkbox || $this->config->show_subscribe_newsletter_checkbox)
    {
	    echo $this->loadCommonLayout('register/register_gdpr.php', $layoutData);
    }

	if ($this->config->accept_term ==1 && $this->config->article_id)
	{
		$layoutData['articleId'] = $this->config->article_id;
		echo $this->loadCommonLayout('register/register_terms_and_conditions.php', $layoutData);
	}

	if ($this->showCaptcha)
	{
		if ($this->captchaPlugin == 'recaptcha_invisible')
		{
			$style = ' style="display:none;"';
		}
		else
		{
			$style = '';
		}
	?>
		<div class="<?php echo $controlGroupClass;  ?>"<?php echo $style; ?>>
			<label class="<?php echo $controlLabelClass; ?>">
				<?php echo JText::_('EB_CAPTCHA'); ?><span class="required">*</span>
			</label>
			<div class="<?php echo $controlsClass; ?>">
				<?php echo $this->captcha; ?>
			</div>
		</div>
	<?php
	}
	?>
	<div class="form-actions">
		<input type="button" class="<?php echo $btnPrimary; ?>" name="btnBack" value="<?php echo  JText::_('EB_BACK') ;?>" onclick="window.history.go(-1);">
		<input type="submit" class="<?php echo $btnPrimary; ?>" name="btn-submit" id="btn-submit" value="<?php echo JText::_('EB_PROCESS_REGISTRATION');?>">
		<img id="ajax-loading-animation" src="<?php echo JUri::base(true);?>/media/com_eventbooking/ajax-loadding-animation.gif" style="display: none;"/>
	</div>
	<?php
		if (count($this->methods) == 1)
		{
		?>
			<input type="hidden" name="payment_method" value="<?php echo $this->methods[0]->getName(); ?>" />
		<?php
		}
	?>
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<input type="hidden" name="show_payment_fee" value="<?php echo (int)$this->showPaymentFee ; ?>" />
	<input type="hidden" id="card-nonce" name="nonce" />
		<script type="text/javascript">
			var eb_current_page = 'cart';
			Eb.jQuery(function($){
				$(document).ready(function(){
					$("#adminForm").validationEngine('attach', {
						onValidationComplete: function(form, status){
							if (status == true) {
								form.on('submit', function(e) {
									e.preventDefault();
								});

                                form.find('#btn-submit').prop('disabled', true);

                                var paymentMethod;

								if($('input:radio[name^=payment_method]').length)
								{
									paymentMethod = $('input:radio[name^=payment_method]:checked').val();
								}
								else
								{
									paymentMethod = $('input[name^=payment_method]').val();
								}

                                // Stripe payment method
                                if (paymentMethod.indexOf('os_stripe') == 0)
                                {
                                    // Old Stripe method
                                    if (typeof stripePublicKey !== 'undefined' && $('#tr_card_number').is(":visible"))
                                    {
                                        Stripe.card.createToken({
                                            number: $('#x_card_num').val(),
                                            cvc: $('#x_card_code').val(),
                                            exp_month: $('select[name^=exp_month]').val(),
                                            exp_year: $('select[name^=exp_year]').val(),
                                            name: $('#card_holder_name').val()
                                        }, stripeResponseHandler);

                                        return false;
                                    }

                                    // Stripe card element
                                    if (typeof stripe !== 'undefined' && $('#stripe-card-form').is(":visible"))
                                    {
                                        stripe.createToken(card).then(function(result) {
                                            if (result.error) {
                                                // Inform the customer that there was an error.
                                                //var errorElement = document.getElementById('card-errors');
                                                //errorElement.textContent = result.error.message;
                                                alert(result.error.message);
                                                $('#btn-submit').prop('disabled', false);
                                            } else {
                                                // Send the token to your server.
                                                stripeTokenHandler(result.token);
                                            }
                                        });

                                        return false;
                                    }
                                }

                                if (paymentMethod == 'os_squareup' && $('#tr_card_number').is(':visible'))
								{
									sqPaymentForm.requestCardNonce();

									return false;
								}

								return true;
							}
							return false;
						}
					});
					buildStateFields('state', 'country', '<?php echo $selectedState; ?>');

					<?php
					for ($i = 1; $i <= $count; $i++)
					{
					?>
                        buildStateFields('state_<?php echo $i; ?>', 'country_<?php echo $i; ?>', '');
					<?php
					}
					?>

                    if (typeof stripe !== 'undefined' && $('#stripe-card-element').length > 0)
                    {
                        var style = {
                            base: {
                                // Add your base input styles here. For example:
                                fontSize: '16px',
                                color: "#32325d",
                            }
                        };

                        // Create an instance of the card Element.
                        var card = elements.create('card', {style: style});

                        // Add an instance of the card Element into the `card-element` <div>.
                        card.mount('#stripe-card-element');
                    }

					if ($('#email').val())
					{
						$('#email').validationEngine('validate');
					}
					<?php
					if ($this->amount == 0 && !empty($showPaymentInformation))
					{
					//The event is free because of discount, so we need to hide payment information
					?>
					    $('.payment_information').css('display', 'none');
					<?php
					}
					?>
				})
			});

			<?php
				echo EventbookingHelperPayments::writeJavascriptObjects();
			?>

			function updateCart(){
				location.href = '<?php echo JRoute::_(EventbookingHelperRoute::getViewRoute('cart', $this->Itemid)); ?>' ;
			}
		</script>
		<?php echo JHtml::_( 'form.token' ); ?>
	</form>
</div>