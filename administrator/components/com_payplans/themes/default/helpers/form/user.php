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
<div class="" data-pp-form-user>
	<div class="o-input-group" >
		<input type="text" class="o-form-control" disabled="disabled" size="35" placeholder="<?php echo JText::_('Browse User');?>" data-pp-form-user-preview />
		
		<span class="o-input-group__append">
			<a href="javascript:void(0);" class="btn btn-pp-default-o" data-pp-form-user-browse>
				<i class="fa fa-user"></i> <?php echo JText::_('COM_PP_BROWSE_BUTTON'); ?>
			</a>
			<a href="javascript:void(0);" class="btn btn-pp-default-o" data-pp-form-user-clear>
				<i class="fa fa-times"></i>
			</a>
		</span>
	</div>
	<input type="hidden" id="<?php echo $id;?>_id" name="<?php echo $name;?>" value="" data-pp-form-user-input />
</div>

