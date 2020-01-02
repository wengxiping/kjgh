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
		<?php echo $this->output('admin/app/generic/form', array('app' => $app)); ?>
	</div>

	<div class="col-lg-7">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_APP_PARAMETERS'); ?>

			<div class="panel-body">
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_MODIFIERS_OPTIONS'); ?>

					<div class="o-control-input is-column" data-select-container>
							<?php if ($options) { ?>
								<?php $i = 1; ?>
								<?php foreach ($options as $option) { ?>
								<div class="pp-fields-row t-lg-mb--lg" data-select-row>
									<div class="o-grid o-grid--gutters t-lg-mb--sm">
										<div class="o-grid__cell">
											<input type="text" name="app_params[time_price][title][]" class="o-form-control" placeholder="Title" value="<?php echo $this->html('string.escape', $option->title);?>"  data-select-title/>
										</div>
										<div class="o-grid__cell">
											<input type="text" name="app_params[time_price][price][]" class="o-form-control" placeholder="Price" value="<?php echo $this->html('string.escape', $option->price);?>"  data-select-price />
										</div>
										<div class="o-grid__cell o-grid__cell--auto-size">
											<a href="javascript:void(0);" class="btn btn-pp-danger xbtn-sm text-center<?php echo ($app->getId() && $i > 1) ? '' : ' t-hidden'; ?>" data-select-remove><i class="fa fa-minus-circle"></i></a>
											<a href="javascript:void(0);" class="btn btn-pp-primary xbtn-sm text-center" data-select-add><i class="fa fa-plus-circle"></i></a>
										</div>
									</div>
									<div class="o-grid o-grid--gutters">
										<div class="o-grid__cell" >
											<?php echo $this->html('form.timer', 'app_params[time_price][time][]', $option->time, ''); ?>
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