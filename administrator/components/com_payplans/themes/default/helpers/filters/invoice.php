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
<span data-table-grid-search>
	<input type="text" class="o-form-control app-filter-bar__search-input" name="<?php echo $name;?>" value="<?php echo $this->html('string.escape', $value);?>" placeholder="<?php echo JText::_('COM_PP_INVOICE', true);?>" data-table-grid-search-input />
	<span class="app-filter-bar__search-btn-group">
		<button class="btn btn-pp-default-o app-filter-bar__search-btn" data-table-grid-search-submit>
			<i class="fa fa-search"></i>
		</button>

		<button type="button" class="btn btn-pp-danger-o app-filter-bar__search-btn" data-table-grid-search-reset>
			<i class="fa fa-times"></i>
		</button>
	</span>
</span>