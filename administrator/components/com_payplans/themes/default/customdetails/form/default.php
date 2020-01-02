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
<form class="o-form-horizontal" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" data-pp-form>
	<div class="wrapper accordion">
		<div class="tab-box tab-box-alt">
			<div class="tabbable">
				<ul class="nav nav-tabs nav-tabs-icons">
					<li class="<?php echo !$activeTab ? 'active' : '';?>">
						<a href="#details" data-toggle="tab"><?php echo JText::_('COM_PP_DETAILS'); ?></a>
					</li>
				</ul>

				<div class="tab-content">
					<div id="details" class="tab-pane <?php echo !$activeTab ? 'active' : '';?>">
						<div class="row">
							<div class="col-lg-4">
								<div class="panel">
									<?php echo $this->html('panel.heading', 'COM_PP_CUSTOMDETAILS_GENERAL'); ?>

									<div class="panel-body">
										
										<div class="o-form-group">
											<?php echo $this->html('form.label', 'COM_PP_CUSTOMDETAILS_TITLE'); ?>

											<div class="o-control-input col-md-7">
												<?php echo $this->html('form.text', 'title', $table->title, ''); ?>
											</div>
										</div>

										<div class="o-form-group">
											<?php echo $this->html('form.label', 'COM_PP_CUSTOMDETAILS_PUBLISHED'); ?>

											<div class="o-control-input col-md-7">
												<?php echo $this->html('form.toggler', 'published', is_null($table->published) ? true : $table->published); ?>
											</div>
										</div>

										<div class="o-form-group">
											<?php echo $this->html('form.label', 'COM_PP_CUSTOMDETAILS_ALL_PLANS'); ?>

											<div class="o-control-input col-md-7">
												<?php echo $this->html('form.allPlans', 'params[applyAll]', $params->get('applyAll', true), '', array('[data-customdetails-plan]')); ?>
											</div>
										</div>

										<div class="o-form-group <?php echo $params->get('applyAll') ? 't-hidden' : '';?>" data-customdetails-plan>
											<?php echo $this->html('form.label', 'COM_PP_CUSTOMDETAILS_PLANS'); ?>

											<div class="o-control-input col-md-7">
												<?php $plans = $params->get('plans'); ?>
												<?php echo $this->html('form.plans', 'params[plans]', $plans, true, true, array('data-customdetails-plan' => '')); ?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-lg-8">
								<div class="panel">
									<?php echo $this->html('panel.heading', 'COM_PP_CUSTOMDETAILS_PARAMETERS'); ?>

									<div class="panel-body">
										<?php echo $editor->display("data", $table->getData(), '100%', '400px', 80, 20, false, null, null, null, array('syntax' => 'xml', 'filter' => 'raw')); ?>
									</div>
									<div class="panel-body">
										<div><?php echo JText::_('COM_PP_CUSTOMDETAILS_PARAMETERS_GUIDE');?></div>
										<div>
											<pre><code><?php echo $this->html('string.escape', JFile::read(JPATH_ADMINISTRATOR . '/components/com_payplans/defaults/' . $type . 'details.xml')); ?></code></pre>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php echo $this->html('form.action', 'customdetails', ''); ?>
	<?php echo $this->html('form.hidden', 'id', $id); ?>
	<?php echo $this->html('form.hidden', 'type', $view); ?>
</form>