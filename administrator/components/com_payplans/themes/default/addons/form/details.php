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
			<?php echo $this->html('panel.heading', 'COM_PP_ADDONS_GENERAL'); ?>

			<div class="panel-body">

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_ADDONS_TITLE'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', 'title', $addon->title, '', array('placeholder' => JText::_('COM_PP_ADDONS_TITLE_PLACEHOLDER'))); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_ADDONS_DESCRIPTION'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.textarea', 'description', $addon->description, '', array('rows' => 5)); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_ADDONS_PUBLISHED'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.toggler', 'published', $addon->published); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_ADDONS_ALL_PLANS'); ?>

					<div class="o-control-input" data-applyon-input>
						<?php echo $this->html('form.allPlans', 'apply_on', $addon->getApplyOn(), '', array('[data-addons-plans]')); ?>
					</div>
				</div>

				<div class="o-form-group <?php echo $addon->getApplyOn() ? 't-hidden' : '';?>" data-addons-plans>
					<?php echo $this->html('form.label', 'COM_PP_ADDONS_PLANS'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.plans', 'plans', $addon->getPlans(), true, true, array('data-plans-input' => '')); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_ADDONS_CONDITION'); ?>

					<div class="o-control-input">
						<select name="addons_condition" class="o-form-control">
							<?php foreach ($conditions as $key => $value) { ?>
							<option value="<?php echo $key;?>" <?php echo $addon->addons_condition == $key ? 'selected="selected"' : '';?>><?php echo $value;?></option>
							<?php } ?>
						</select>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_ADDONS_PRICE'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', 'price', $addon->getPrice(), '', array('placeholder' => '0.00')); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_ADDONS_PRICETYPE'); ?>

					<div class="o-control-input">
						<select name="price_type" class="o-form-control">
							<?php foreach ($priceTypes as $key => $value) { ?>
							<option value="<?php echo $key;?>" <?php echo $addon->price_type == $key ? 'selected="selected"' : '';?>><?php echo $value;?></option>
							<?php } ?>
						</select>
					</div>
				</div>

			</div>

		</div>
	</div>

	<div class="col-lg-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_ADDONS_ADVANCED'); ?>

			<div class="panel-body">
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_ADDONS_START_DATE'); ?>

					<div class="o-control-input">
						<?php echo JHtml::_('calendar', $addon->getStartDate(), 'start_date', 'start_date', '%Y-%m-%d %H:%M:%S', array('class' => '')); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_ADDONS_END_DATE'); ?>

					<div class="o-control-input">
						<?php echo JHtml::_('calendar', $addon->getEndDate(), 'end_date', 'end_date', '%Y-%m-%d %H:%M:%S', array('class' => '')); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_ADDONS_APPLICABILITY'); ?>

					<div class="o-control-input">
						<select name="params[applicability]" class="o-form-control">
							<?php foreach ($taxesTypes as $key => $value) { ?>
							<option value="<?php echo $key;?>" <?php echo $params->get('applicability') == $key ? 'selected="selected"' : '';?>><?php echo $value;?></option>
							<?php } ?>
						</select>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_ADDONS_AVAILABILITY'); ?>

					<div class="o-control-input">
						<select name="params[availability]" class="o-form-control" data-availability>
							<?php foreach ($availabilityTypes as $key => $value) { ?>
							<option value="<?php echo $key;?>" <?php echo $params->get('availability') == $key ? 'selected="selected"' : '';?>><?php echo $value;?></option>
							<?php } ?>
						</select>
					</div>
				</div>

				<div class="o-form-group<?php echo ($params->get('availability', 0)) ? '' : ' t-hidden'; ?>" data-stock-container>
					<?php echo $this->html('form.label', 'COM_PP_ADDONS_STOCK'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', 'params[stock]', $params->get('stock'), '', 'data-stock-input'); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_ADDONS_TO_DEFAULT'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.toggler', 'params[default]', $params->get('default')); ?>
					</div>
				</div>

				<?php if ($addon->getId()) { ?>
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_ADDONS_CONSUMED'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', '', 0, '', 'disabled=true'); ?>
					</div>
				</div>
			<?php } ?>
			</div>

		</div>
	</div>
</div>
