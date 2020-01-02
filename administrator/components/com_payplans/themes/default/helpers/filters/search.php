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
<span data-table-grid-search class="app-filter-bar__search-input-group">
	
	<input type="text" class="o-form-control app-filter-bar__search-input" name="<?php echo $name;?>" value="<?php echo $this->html('string.escape', $value);?>" placeholder="<?php echo JText::_('COM_PP_SEARCH', true);?>" data-table-grid-search-input />

	<button type="button" class="app-filter-bar__search-input-reset <?php echo !$value ? 't-hidden' : '';?>" data-table-grid-search-reset>
		<i class="fa fa-times"></i>
	</button>

	<span class="app-filter-bar__search-input-reset <?php echo $value ? 't-hidden' : '';?>" style="width:32px;" data-table-grid-search-spacer>
		&nbsp;
	</span>

</span>