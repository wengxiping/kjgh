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
	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_PLAN_EDIT_BADGE_PARAMETER'); ?>
			<div class="panel-body">
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_EDIT_PLAN_HIGHLIGHT'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.toggler', 'planHighlighter', $plan->getPlanHighlighter(), 'planHighlighter', array()); ?>
					</div>
				</div>
				
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_EDIT_PLAN_APPLY_BADGE'); ?>
					<div class="o-control-input">
						<?php echo $this->html('form.toggler', 'badgeVisible', $plan->getBadgeVisible(), 'planbadgeVisibleHighlighter', array()); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_EDIT_PLAN_BADGE_POSITION'); ?>
					<div class="o-control-input">
						<?php echo $this->html('form.lists', 'badgePosition', $plan->getBadgePosition(), 'badgePosition', '', $badgePositions); ?>

					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_EDIT_PLAN_BADGE_BADGE_TITLE'); ?>
					<div class="o-control-input">
						<?php echo $this->html('form.text', 'badgeTitle', $plan->getBadgeTitle(), 'badgeTitle'); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_EDIT_PLAN_BADGE_BADGE_TEXT_COLOR'); ?>
					<div class="o-control-input">
						<?php echo $this->html('form.colorpicker', 'badgeTitleColor', $plan->getBadgeTitleColor(), $plan->getBadgeTitleColor()); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_EDIT_PLAN_BADGE_BADGE_BACKGROUND_COLOR'); ?>
					<div class="o-control-input">
						<?php echo $this->html('form.colorpicker', 'badgebackgroundcolor', $plan->getBadgebackgroundcolor(), $plan->getBadgebackgroundcolor()); ?>
					</div>
				</div>

			</div>
		</div>
	</div>

	<div class="col-lg-6">
	</div>
</div>