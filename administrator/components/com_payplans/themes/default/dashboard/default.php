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
<form action="index.php" method="post" name="adminForm" id="adminForm">

	<div class="dashboard-stats" data-dashboard>
		<div class="db-stats t-lg-mb--lg">
			<?php echo $this->output('admin/dashboard/widgets/stats'); ?>
		</div>

		<?php echo $this->output('admin/dashboard/charts/default'); ?>

		<div class="row">
			<div class="col-lg-8">
				<?php echo $this->output('admin/dashboard/widgets/news'); ?>
			</div>

			<div class="col-lg-4">
				<?php echo $this->output('admin/dashboard/widgets/info'); ?>
			</div>
		</div>
	</div>

	<input type="hidden" name="boxchecked" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_payplans" />
	<input type="hidden" name="view" value="" />
	<input type="hidden" name="controller" value="payplans" />
	<?php echo $this->html('form.token'); ?>
</form>
