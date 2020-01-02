<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

?>
<div class="row">
	<div class="col-lg-5">

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_APP_GENERAL'); ?>

			<div class="panel-body">
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_APP_GENERAL_TITLE', '', 3, true, true); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', 'title', $item->title); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_APP_GENERAL_PUBLISH_STATE'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.toggler', 'published', $item->published); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_ADV_PRICING_UNIT_TITLE', '', 3, true, true); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', 'units_title', $item->units_title, '', '', array('placeholder' => 'COM_PP_ADV_PRICING_UNIT_TITLE_PLACEHOLDER')); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_ADV_PRICING_UNIT_MIN', '', 3, true, true); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', 'units_min', $item->units_min); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_ADV_PRICING_UNIT_MAX', '', 3, true, true); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', 'units_max', $item->units_max); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_APP_GENERAL_DESCRIPTION'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.textarea', 'description', $item->description, '', array('rows' => 5)); ?>
					</div>
				</div>
			</div>

		</div>
	</div>

	<div class="col-lg-7">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_ADV_PRICING_OPTIONS'); ?>

			<div class="panel-body">

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_ADV_PRICING_ASSIGN_PLAN'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.plans', 'plans', $plans, true, true); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_ADV_PRICING_PRICE_OPTIONS'); ?>

					<div class="o-control-input is-column" data-select-container>
						<?php if ($priceSet) { ?>
							<?php $i = 1; ?>
							<?php foreach ($priceSet as $set) { ?>
							<div class="pp-fields-row t-lg-mb--lg" data-select-row>
								<div class="o-grid o-grid--gutters t-lg-mb--sm">
									
									<div class="o-grid__cell">
										<input type="text" name="price[]" class="o-form-control" placeholder="Price" value="<?php echo $this->html('string.escape', $set['price']);?>"  data-select-price />
									</div>
									<div class="o-grid__cell o-grid__cell--auto-size">
										<a href="javascript:void(0);" class="btn btn-pp-danger xbtn-sm text-center<?php echo ($item->getId() && $i > 1) ? '' : ' t-hidden'; ?>" data-select-remove><i class="fa fa-minus-circle"></i></a>
										<a href="javascript:void(0);" class="btn btn-pp-primary xbtn-sm text-center" data-select-add><i class="fa fa-plus-circle"></i></a>
									</div>
								</div>
								<div class="o-grid o-grid--gutters">
									<div class="o-grid__cell" >
										<?php echo $this->html('form.timer', 'duration[]', $set['duration'], ''); ?>
									</div>
								</div>
								
							</div>
							<?php $i++; ?>
							<?php } ?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>