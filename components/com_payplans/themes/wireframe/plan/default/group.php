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

$suffix = 'group-id-' . $group->getId();
?>
<div class="pp-plans__item">
	<div class="pp-plan-card<?php echo $group->getPlanHighlighter() ? ' is-highlight' : '' ?><?php echo $group->getBadgeVisible() ? ' has-badges' : ''; ?>">
		<div class="pp-plan-card__hd">
			<div class="pp-plan-card__label pp-plan-card__label--<?php echo $group->getBadgePosition(); ?>">
				<div class="pp-plan-pop-label <?php echo $suffix; ?>">
					<span class="pp-plan-pop-label__txt <?php echo $suffix; ?>">
						<?php echo JText::_($group->getBadgeTitle()); ?>
					</span>
				</div>
			</div>
			<div class="pp-plan-card__title">
				<h4><?php echo JString::ucfirst(JText::_($group->getTitle())); ?></h4>
			</div>

			<div class="pp-plan-card__desc">
				<?php echo JString::ucfirst(JText::_($group->getTeasertext()));?>
			</div>
		</div>

		<?php if ($group->getDescription()) { ?>
		<div class="pp-plan-card__bd">
			<div class="pp-plan-card__features">
				<?php echo JText::_($group->getDescription()); ?>
			</div>
		</div>
		<?php } ?>

		<div class="pp-plan-card__ft">
			<div class="pp-plan-card__forms">
				<a href="<?php echo PPR::_('index.php?option=com_payplans&view=plan&task=subscribe&group_id=' . $group->getId());?>" class="btn btn-pp-primary">
					<?php echo JText::_('COM_PAYPLANS_GROUP_BUTTON')?>
				</a>
			</div>
		</div>
	</div>
</div>