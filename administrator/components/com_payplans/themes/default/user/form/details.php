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
?>
<div class="row">
	<div class="col-lg-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_USER_FORM_DETAILS'); ?>

			<div class="panel-body">
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PAYPLANS_USER_EDIT_USER_ID', '', 3, false); ?>

					<div class="o-control-input col-md-7">
						<?php echo $user->getId();?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PAYPLANS_USER_EDIT_USERNAME', '', 3, false); ?>

					<div class="o-control-input col-md-7">
						<a href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id=' . $user->getId());?>"><?php echo $user->getUsername();?></a>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PAYPLANS_USER_EDIT_USER_NAME', '', 3, false); ?>

					<div class="o-control-input col-md-7">
						<?php echo $user->getName();?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PAYPLANS_USER_EDIT_USER_EMAIL', '', 3, false); ?>

					<div class="o-control-input col-md-7">
						<?php echo $user->getEmail();?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PAYPLANS_USER_EDIT_USER_REGISTERDATE', '', 3, false); ?>

					<div class="o-control-input col-md-7">
						<?php echo $user->getRegisterDate();?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PAYPLANS_USER_EDIT_USER_LASTVISITDATE', '', 3, false); ?>

					<div class="o-control-input col-md-7">
						<?php echo $user->getLastvisitDate();?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_USER_FORM_COUNTRY_RESIDENCE'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.country', 'country', $user->getCountry());?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_USER_FORM_NOTES'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.textarea', 'params[user_notes]', $params->get('user_notes'));?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-lg-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_USER_FORM_BUSINESS'); ?>

			<div class="panel-body">
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_USER_FORM_BUSINESS_PURPOSE'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.lists', 'preference[business_purpose]', $preferences->get('business_purpose'), '', '', array(
								array('title' => 'COM_PP_USER_PURPOSE_PERSONAL', 'value' => 1),
								array('title' => 'COM_PP_USER_PURPOSE_BUSINESS', 'value' => 2)
							));?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_USER_FORM_BUSINESS_NAME'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.text', 'preference[business_name]', $preferences->get('business_name'));?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_USER_FORM_TIN'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.text', 'preference[tin]', $preferences->get('tin'));?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_USER_FORM_ADDRESS'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.textarea', 'preference[business_address]', $preferences->get('business_address'));?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_USER_FORM_SHIPPING_ADDRESS'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.textarea', 'preference[shipping_address]', $preferences->get('shipping_address'));?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>