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
<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="o-form-horizontal">
	<div class="wrapper accordion">
		<div class="tab-box tab-box-alt">
			<div class="tabbable">

				<ul class="nav nav-tabs nav-tabs-icons">
					<?php foreach ($tabs as $tab) { ?>
					<li class="<?php echo $tab->active ? 'active' : '';?>">
						<a href="#<?php echo $tab->id; ?>" data-toggle="tab" data-id="<?php echo $tab->id;?>"><?php echo $tab->title; ?></a>
					</li>
					<?php } ?>
				</ul>

				<div class="tab-content">
					<?php foreach ($tabs as $tab) { ?>
						<div id="<?php echo $tab->id; ?>" class="tab-pane <?php echo $tab->active ? 'active' : '';?>">
							<?php echo $tab->contents;?>
						</div>
					<?php } ?>
				</div>

			</div>
		</div>
	</div>

	<?php echo $this->html('form.hidden', 'activeTab', $activeTab, array('data-pp-active-tab' => '')); ?>
	<?php echo $this->html('form.hidden', 'page', $page); ?>
	<?php echo $this->html('form.action', 'config', 'save'); ?>
</form>