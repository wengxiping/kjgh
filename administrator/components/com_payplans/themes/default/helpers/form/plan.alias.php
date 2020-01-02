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
<div data-plan-listing>
	<?php if ($totalValues > 0) { ?>
		<?php foreach ($value as $key => $item) { ?>
		<div data-plan-item>
			<div class="o-grid o-grid--gutters t-lg-mb--md">
				<div class="o-grid__cell">
					<div class="o-select-group">
						<select class="o-form-control" name="<?php echo $name;?>[<?php echo $key;?>][]">
							<option value=""><?php echo JText::_('Select a Plan');?></option>
							<?php foreach ($plans as $plan) { ?>
							<option value="<?php echo $plan->plan_id;?>" <?php echo $plan->plan_id == $value[$key][0] ? ' selected="selected"' : '';?>><?php echo $plan->title;?></option>
							<?php } ?>
						</select>
						<label for="" class="o-select-group__drop"></label>
					</div>
				</div>
				<div class="o-grid__cell">
					<div class="o-input-group">
						<input type="text" name="<?php echo $name;?>[<?php echo $key;?>][]" class="o-form-control" value="<?php echo isset($value[$key][1]) ? $value[$key][1] : '';?>" />

						<div class="o-input-group__append">
							<?php if ($key > 0) { ?>
							<a href="javascript:void(0);" class="btn btn-pp-danger-o" data-remove-row>
								<i class="fa fa-times"></i>
							</a>
							<?php } else { ?>
							<a href="javascript:void(0);" class="btn btn-pp-primary-o" data-insert-row>
								<i class="fa fa-plus"></i>
							</a>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
			

			
		</div>
		<?php } ?>
	<?php } else { ?>
		<div class="o-grid o-grid--gutters t-lg-mb--md">
			<div class="o-grid__cell">
				<div class="o-select-group">
					<select class="o-form-control" name="<?php echo $name;?>[0][]">
						<option value="" selected="selected"><?php echo JText::_('Select a Plan');?></option>
						<?php foreach ($plans as $plan) { ?>
						<option value="<?php echo $plan->plan_id;?>"><?php echo $plan->title;?></option>
						<?php } ?>
						<label for="" class="o-select-group__drop"></label>
					</select>
					<label for="" class="o-select-group__drop"></label>
				</div>
			</div>
			<div class="o-grid__cell">
				<div class="o-input-group">
					<input type="text" name="<?php echo $name;?>[0][]" class="o-form-control" value="" />

					<div class="o-input-group__append">
						<a href="javascript:void(0);" class="btn btn-pp-primary-o" data-insert-row>
							<i class="fa fa-plus"></i>
						</a>
					</div>
				</div>
			</div>
		</div>
		

		
	<?php } ?>
</div>

<div class="t-hidden" data-plan-template data-plan-item>
	<div class="o-grid o-grid--gutters t-lg-mb--md">
		<div class="o-grid__cell">
			<div class="o-select-group">
				<select class="o-form-control" name="">
					<option value="" selected="selected"><?php echo JText::_('Select a Plan');?></option>
					<?php foreach ($plans as $plan) { ?>
					<option value="<?php echo $plan->plan_id;?>"><?php echo $plan->title;?></option>
					<?php } ?>
				</select>
				<label for="" class="o-select-group__drop"></label>
			</div>
		</div>
		<div class="o-grid__cell">
			<div class="o-input-group">
				<input type="text" name="" class="o-form-control" />

				<div class="o-input-group__append">
					<a href="javascript:void(0);" class="btn btn-pp-danger-o" data-remove-row>
						<i class="fa fa-times"></i>
					</a>
				</div>
			</div>
		</div>
	
	
</div>