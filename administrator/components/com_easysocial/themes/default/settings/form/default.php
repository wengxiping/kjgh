<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<form id="adminForm" name="adminForm" class="adminForm" action="index.php" method="post" enctype="multipart/form-data">

	<div class="wrapper accordion">
		<div class="tab-box tab-box-alt">
			<div class="tabbable">

				<ul class="nav nav-tabs nav-tabs-icons">
					<?php foreach ($tabs as $tab) { ?>
					<li class="<?php echo $tab->active ? 'active' : '';?>">
						<a href="#<?php echo $tab->id; ?>" data-bs-toggle="tab"><?php echo $tab->title; ?></a>
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

	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="controller" value="settings" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="page" value="<?php echo $page;?>" />
	<input type="hidden" name="active" value="<?php echo $active;?>" data-active-tab/>

	<?php echo JHTML::_('form.token'); ?>
</form>
