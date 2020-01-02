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
<div class="row">
	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_GROUP_FORM_GROUP_DETAILS'); ?>

			<div class="panel-body">
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_GROUP_FORM_GROUP_TITLE', '', 3, true, true); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', 'title', $group->getTitle(), 'title', array()); ?>
					</div>
				</div>
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_GROUP_FORM_PARENT'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.lists', 'parent', $group->getParent(), 'parent', '', $parentSelection); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_GROUP_FORM_GROUP_TEASER_TEXT'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', 'params[teasertext]', $params->get('teasertext'), 'params[teasertext]', array()); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_GROUP_FORM_GROUP_CHILD_PLANS'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.plans', 'plans', $group->getPlans(), true, true, 'data-export-plans'); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_GROUP_FORM_PUBLISHED'); ?>
					<div class="o-control-input">
						<?php echo $this->html('form.toggler', 'published', $group->published, 'published', array()); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_GROUP_FORM_HIGHLIGHT_GROUP'); ?>
					<div class="o-control-input">
						<?php echo $this->html('form.toggler', 'params[planHighlighter]', $params->get('planHighlighter'), 'params[planHighlighter]', array()); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_GROUP_FORM_GROUP_VISIBLE'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.toggler', 'visible', $group->getVisible(), 'visible'); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_GROUP_FORM_GROUP_DESCRIPTION'); ?>

					<div class="o-control-input col-md-7">
						<?php if ($renderEditor) { ?>
							<?php echo $this->html('form.editor', 'description', $group->getDescription(), 'description', array(), array(), array(), false); ?>
						<?php } else { ?>
							<?php echo $this->html('form.textarea', 'description', $group->getDescription(true), 'description', array(), false); ?>
						<?php } ?>
					</div>
				</div>				
			</div>
		</div>
	</div>

	<!-- right floater -->
	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_PLAN_EDIT_BADGE_PARAMETER'); ?>
			<div class="panel-body">

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_EDIT_PLAN_APPLY_BADGE'); ?>
					<div class="o-control-input">
						<?php echo $this->html('form.toggler', 'params[badgeVisible]', $params->get('badgeVisible'), 'params[badgeVisible]', array()); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_EDIT_PLAN_BADGE_POSITION'); ?>
					<div class="o-control-input">
						<?php echo $this->html('form.lists', 'params[badgePosition]', $params->get('badgePosition'), 'params[badgePosition]', '', $badgePositions); ?>

					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_EDIT_PLAN_BADGE_BADGE_TITLE'); ?>
					<div class="o-control-input">
						<?php echo $this->html('form.text', 'params[badgeTitle]', $params->get('badgeTitle'), 'params[badgeTitle]'); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_EDIT_PLAN_BADGE_BADGE_TEXT_COLOR'); ?>
					<div class="o-control-input">
						<?php echo $this->html('form.colorpicker', 'params[badgeTitleColor]', $params->get('badgeTitleColor', '#ffffff'), '#ffffff'); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_PLAN_EDIT_PLAN_BADGE_BADGE_BACKGROUND_COLOR'); ?>
					<div class="o-control-input">
						<?php echo $this->html('form.colorpicker', 'params[badgebackgroundcolor]', $params->get('badgebackgroundcolor', '#707070'), '#707070'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
