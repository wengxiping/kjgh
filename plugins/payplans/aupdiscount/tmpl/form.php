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

<hr class="hr--light" />

<h5><?php echo JText::_("COM_PAYPLANS_AUPDISCOUNT_AUP_POINTS");?></h5>

<p>
	<?php echo JText::_('COM_PP_AUPDISCOUNT_USE_POINTS_FOR_DISCOUNT');?>
</p>

<table class="table t-lg-mt--xl t-lg-mb--xl">
	<thead>
		<tr>
			<th>
				Rule
			</th>
			<th width="30%" class="t-text--center">
				&nbsp;
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<?php echo JText::_('COM_PP_AUPDISCOUNT_REMAINING_POINTS');?>
			</td>
			<td class="t-text--center">
				<b><?php echo JText::sprintf('COM_PP_POINTS_COUNT', $points); ?></b>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_('COM_PP_AUPDISCOUNT_CONVERSION_TO_DOLLAR');?>
			</td>
			<td class="t-text--center">
				<b><?php echo JText::sprintf('COM_PP_POINTS_COUNT', $ratio); ?></b>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_('COM_PP_AUPDISCOUNT_MINIMUM_POINTS');?>
			</td>
			<td class="t-text--center">
				<b><?php echo JText::sprintf('COM_PP_POINTS_COUNT', $minimum); ?></b>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_('COM_PP_AUPDISCOUNT_MAXIMUM_POINTS'); ?>
			</td>
			<td class="t-text--center">
				<b><?php echo JText::sprintf('COM_PP_POINTS_COUNT', $maximum); ?></b>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_('COM_PP_AUPDISCOUNT_ROUNDING_BEHAVIOR'); ?>
			</td>
			<td class="t-text--center">
				<b>
				<?php if ($rounded) { ?>
					<?php echo JText::_('COM_PP_ROUNDED_DOWN'); ?>
				<?php } else { ?>
					<?php echo JText::_('COM_PP_ROUNDED_UP'); ?>
				<?php } ?>
				</b>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_('COM_PP_AUPDISCOUNT_AVAILABILITY'); ?>
			</td>
			<td class="t-text--center">
				<b>
				<?php if (!$end) { ?>
					<?php echo JText::_('Unlimited Use'); ?>
				<?php } else { ?>
					<?php echo $end;?>
				<?php } ?>
				</b>
			</td>
		</tr>
	</tbody>
</table>

<div class="pp-discount t-lg-mt--md">
	<div class="pp-discount__form" style="flex: 100%;">
		<div class="o-form-group" data-pp-aupdiscount-wrapper>
			<div class="o-input-group">
				<input name="app_aupdiscount_code" type="number" max="<?php echo $maximum;?>" min="<?php echo $minimum;?>" class="o-form-control" placeholder="<?php echo JText::_('Enter points to use');?>" data-pp-aupdiscount-points />
				<span class="o-input-group__append">
					<button class="btn btn-pp-default-o" type="button" data-pp-aupdiscount-apply data-id="<?php echo $invoice->getId();?>">
						<?php echo JText::_('COM_PP_APPLY_BUTTON');?>
					</button>
				</span>
			</div>

			<div class="t-text--danger" data-pp-aupdiscount-message></div>
		</div>
	</div>
</div>

<div class="t-lg-mb--xl">
	<span class="o-label o-label--danger"><?php echo JText::_('COM_PP_NOTE');?></span>
	<div><?php echo JText::_('COM_PP_AUPDISCOUNT_DISCLAIMER');?></div>
</div>