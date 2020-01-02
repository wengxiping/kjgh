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

$suffix = 'plan-id-' . $plan->getId();
?>
<div class="pp-plans__item">
	<div class="pp-plan-card<?php echo $plan->isHighlighted() ? ' is-highlight' : '';?><?php echo $plan->hasBadge() ? ' has-badges' : ''; ?>">
		<div class="pp-plan-card__hd">
			<div class="pp-plan-card__label pp-plan-card__label--<?php echo $plan->getBadgePosition(); ?>">
				<div class="pp-plan-pop-label <?php echo $suffix; ?>">
					<span class="pp-plan-pop-label__txt <?php echo $suffix; ?>">
						<?php echo JText::_($plan->getBadgeTitle()); ?>
					</span>
				</div>
			</div>
			<div class="pp-plan-card__title">
				<?php echo JString::ucfirst(JText::_($plan->getTitle()));?>
			</div>

			<div class="pp-plan-card__desc">
				<?php echo JString::ucfirst(JText::_($plan->getTeaser()));?>
			</div>
			
			<div class="pp-plan-card__price">
				<?php if ($plan->isFree()) { ?>
					<?php echo JText::_('COM_PAYPLANS_PLAN_PRICE_FREE');?>
				<?php } else { ?>
					<?php echo $this->html('html.amount', $plan->getPrice(), $plan->getCurrency()); ?>
				<?php } ?>
			</div>

			<div class="pp-plan-card__period">
				<?php if ($plan->isRecurring()) { ?>
					<?php echo JText::_('COM_PAYPLANS_PLAN_PRICE_TIME_SEPERATOR'); ?>
				<?php } else { ?>
					<?php echo JText::_('COM_PAYPLANS_PLAN_PRICE_TIME_SEPERATOR_FOR'); ?>
				<?php } ?>
				
				<?php echo $this->html('html.plantime', $plan->getExpiration()); ?>
			</div>
		</div>

		<?php if ($plan->getDescription(true)) { ?>
		<div class="pp-plan-card__bd">
			<div class="pp-plan-card__features">
				<?php echo JText::_($plan->getDescription(true));?>
			</div>
		</div>
		<?php } ?>

		<div class="pp-plan-card__ft" data-plan-footer>
			
			<div class="pp-plan-card__forms">
				
				<?php if ($columns == 1) { ?>
					<?php if ($plan->advancedpricing) { ?>
						<div class="t-border-radius--lg t-bg--shade t-lg-p--lg t-text--left t-lg-mb--lg" data-adv-pricing>
						<?php echo $this->output('site/plan/default/advancedpricing', array('advancedpricing' => $plan->advancedpricing, 'plan' => $plan)); ?>
						</div>
					<?php } ?>
				<?php } ?>
				
				<?php if ($plan->modifiers) { ?>
					<div class="t-border-radius--lg t-bg--shade t-lg-p--lg t-text--left t-lg-mb--lg" data-modifier>
					<?php foreach ($plan->modifiers as $modifier) { ?>
						<?php if ($modifier->options) { ?>
							<?php echo $this->output('site/plan/default/modifier', array('modifier' => $modifier, 'plan' => $plan)); ?>
						<?php } ?>
					<?php } ?>
					</div>
				<?php } ?>
				
				<?php
				//@TODO: Figure out a way to generate output from plugins
				//$position = 'plan-block-bottom_'.$plan->getId();
				//echo $this->output('site/partials/position',compact('plugin_result','position'));
				?>
				<a href="<?php echo $plan->getSelectPermalink();?>" class="btn btn-pp-primary" data-subscribe-button data-default-link="<?php echo $plan->getSelectPermalink();?>">
					<?php echo JText::_('COM_PAYPLANS_PLAN_SUBSCRIBE_BUTTON')?>
				</a>
			</div>
		</div>
	</div>
</div>