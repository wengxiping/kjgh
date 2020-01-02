<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
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
				<li class="active">
					<a href="#details" data-toggle="tab"><?php echo JText::_('COM_PP_DETAILS'); ?></a>
				</li>

				<li class="">
					<a href="#advance" data-toggle="tab"><?php echo JText::_('COM_PP_ADVANCE'); ?></a>
				</li>

				<li class="">
					<a href="#appearance" data-toggle="tab"><?php echo JText::_('COM_PP_APPEARANCE'); ?></a>
				</li>

				<?php if ($plan->getId()) { ?>
				<li class="">
					<a href="#logs" data-toggle="tab"><?php echo JText::_('COM_PP_LOGS'); ?></a>
				</li>
				<?php } ?>
			</ul>

			<div class="tab-content">
				<div id="details" class="tab-pane <?php echo !$activeTab ? 'active' : '';?>">
					<?php echo $this->output('admin/plan/form/details'); ?>
				</div>

				<div id="advance" class="tab-pane <?php echo $activeTab == 'advance' ? 'active' : '';?>">
					<?php echo $this->output('admin/plan/form/advance'); ?>
				</div>

				<div id="appearance" class="tab-pane <?php echo $activeTab == 'appearance' ? 'active' : '';?>">
					<?php echo $this->output('admin/plan/form/appearance'); ?>
				</div>

				<?php if ($plan->getId()) { ?>
				<div id="logs" class="tab-pane <?php echo $activeTab == 'logs' ? 'active' : '';?>">
					<?php echo $this->output('admin/logs/default/default', array('logs' => $logs, 'pagination' => false, 'editable' => false, 'renderFilterBar' => false, 'sortable' => false, 'form' => false)); ?>
				</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>


<?php echo $this->html('form.action', 'plan', 'store'); ?>
<?php echo $this->html('form.hidden', 'plan_id', $plan->getId()); ?>
</form>
