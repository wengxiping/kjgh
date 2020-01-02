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
<?php if ($this->config->get('addons_enabled') && $addons) { ?>
<div class="o-card o-card--borderless t-lg-mt--lg">
	<div class="o-card__header o-card__header--nobg t-lg-pl--no"><?php echo JText::_('COM_PAYPLANS_ORDER_ADDONS');?></div>

	<div class="o-card__body">
		<div class="t-bg--shade t-lg-pl--lg t-lg-pr--lg">
			<table class="pp-checkout-table">
				<tbody>
				<?php foreach ($addons as $addon) { ?>
					<?php
						$purchased = array_key_exists($addon->getId(), $purchasedAddons);
						$params = $addon->getParams();

						$default = $params->get('default',0);
						$disabled = ($this->config->get('addons_forceful_default') && $default)? true: false;

						$inputType = 'checkbox';
						$inputName = 'purchaseServices[]';

						$multiple = $this->config->get('addons_select_multiple', 1);
						if (!$disabled && !$multiple) {
							$inputType = 'radio';
							$inputName = 'purchaseServices';
						}
					?>

					<tr>
						<td>
							<div class="o-form-group">
								<div class="o-<?php echo $inputType; ?>">

									<?php if ($disabled) { ?>
										<input id="item-addon-<?php echo $addon->getId(); ?>" type="checkbox" checked="checked" disabled="disabled">
									<?php } else { ?>

										<input id="item-addon-<?php echo $addon->getId(); ?>"
											type="<?php echo $inputType; ?>" name="<?php echo $inputName; ?>"
											value="<?php echo $addon->getId(); ?>"
											<?php echo $purchased ? ' checked="checked"': ''; ?>
											data-addons-item />

									<?php } ?>

									<label for="item-addon-<?php echo $addon->getId(); ?>">
										<?php echo $addon->getTitle(true, $invoice); ?><br />
										<?php echo $addon->getDescriptions(); ?>
									</label>
								</div>
							</div>
						</td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<?php } ?>
