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
<div class="t-text--center" data-grid-pagination>
	<ul class="o-pagination">
		<li class="<?php echo $data->start->link ? '' : ' disabled';?>" data-pagination-link data-limitstart="<?php echo $data->start->base;?>">
			<a href="<?php echo $data->start->link ? $data->start->link : 'javascript:void(0);';?>">
				<i class="fa fa-angle-double-left"></i>
			</a>
		</li>

		<?php if ($data->previous) { ?>
		<li class="<?php echo !$data->previous->link ? ' disabled' : '';?>" data-pagination-link data-limitstart="<?php echo $data->previous->base;?>">
			<a href="javascript:void(0);" class="previousItem">
				<i class="fa fa-angle-left"></i>
			</a>
		</li>
		<?php } ?>

		<?php foreach ($data->pages as $page) { ?>
		<li class="<?php echo !$page->link ? ' active' : '';?>" data-pagination-link data-limitstart="<?php echo $page->base ? $page->base : 0;?>">
			<a href="javascript:void(0);" class="pageItem">
				<?php echo $page->text;?>
			</a>
		</li>
		<?php } ?>

		<?php if ($data->next) { ?>
		<li class="<?php echo !$data->next->link ? ' disabled' :'';?>" data-pagination-link data-limitstart="<?php echo $data->next->base;?>">
			<a href="javascript:void(0);" class="nextItem">
				<i class="fa fa-angle-right"></i>
			</a>
		</li>
		<?php } ?>

		<li class="<?php echo $data->end->link ? '' : ' disabled';?>" data-pagination-link data-limitstart="<?php echo $data->end->base;?>">
			<a href="<?php echo $data->end->link ? $data->end->link : 'javascript:void(0);';?>">
				<i class="fa fa-angle-double-right"></i>
			</a>
		</li>
	</ul>

	<?php echo $this->html('form.hidden', 'limitstart', $pagination->limitstart, 'data-limitstart-value'); ?>
</div>
