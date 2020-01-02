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
?>
<?php if (!$userId) { ?>
	<?php echo $this->output('site/checkout/default/login'); ?>

	<div class="o-card o-card--borderless t-lg-mb--lg t-hidden" data-pp-register>
		<div class="o-card__header o-card__header--nobg t-lg-pl--no">
			<div class="o-grid">
				<div class="o-grid__cell">
					<?php echo JText::_('COM_PP_CHECKOUT_CREATE_NEW_ACCOUNT');?>
				</div>
				<div class="o-grid__cell t-text--right">
					<div style="font-weight: normal;">
						<?php echo JText::_('COM_PP_CHECKOUT_ALREADY_HAVE_ACCOUNT');?> <a href="javascript:void(0);" data-pp-login-link><?php echo JText::_('COM_PP_CHECKOUT_LOGIN');?></a>
					</div>
				</div>
			</div>
		</div>

		<div class="o-card__body">
			<p class="t-lg-mb--xl"><?php echo JText::_('COM_PP_REGISTER_FOR_NEW_ACCOUNT_INFO');?></p>

			<div class="t-text--center">
				<a href="<?php echo $url;?>" class="btn btn-lg btn-pp-primary">
					<i class="fa fa-lock"></i>&nbsp; <?php echo JText::_('COM_PP_REGISTER_FOR_A_NEW_ACCOUNT');?>
				</a>
			</div>

			<?php if ($this->config->get('registration_es_social')) { ?>
				<?php if ($sso->hasSocialButtons()) { ?>

					<?php ES::initialize('site'); ?>
					
					<div class="t-lg-mt--xl">
						<hr/>
						<p class="t-text--center t-lg-mb--xl"><?php echo JText::_('COM_PP_ES_SOCIAL_SIGNUP');?></p>
						<div id="es">
							<div class="es-login-social-container">

								<?php if ($sso->isEnabled('facebook')) { ?>
								<div class="es-login-social-container__cell">
									<?php echo $sso->getLoginButton('facebook', 'default'); ?>
								</div>
								<?php } ?><br>

								<?php if ($sso->isEnabled('twitter')) { ?>
								<div class="es-login-social-container__cell">
									<?php echo $sso->getLoginButton('twitter', 'default'); ?>
								</div>
								<?php } ?><br>

								<?php if ($sso->isEnabled('linkedin')) { ?>
								<div class="es-login-social-container__cell">
									<?php echo $sso->getLoginButton('linkedin', 'default'); ?>
								</div>
								<?php } ?><br>
							</div>
						</div>
					</div>
				<?php } ?>
			<?php } ?>
		</div>
	</div>
<?php } ?>