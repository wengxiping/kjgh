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
<div style="background: #fff; cursor: pointer; width: 100%;min-width:250px;margin-left:10px;" data-pp-date-range-<?php echo $uid;?>>
	<i class="far fa-calendar"></i>&nbsp;
	<span data-pp-date-range-display><?php echo !$start && !$end ? $placeholder : '';?></span> <i class="fa fa-caret-down"></i>
</div>
<?php echo $this->html('form.hidden', 'daterange[start]', '', 'data-pp-date-start'); ?>
<?php echo $this->html('form.hidden', 'daterange[end]', '', 'data-pp-date-end'); ?>

<button type="button" class="app-filter-bar__search-input-reset <?php echo $start && $end ? '' : 't-hidden';?>" data-pp-date-range-reset>
	<i class="fa fa-times"></i>
</button>