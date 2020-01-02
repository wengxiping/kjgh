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
if(defined('_JEXEC')===false) die();
?>
<div class="row-fluid">
	<h2>
		<?php echo JText::_('COM_PAYPLANS_PAYMENT_PAY_HEADING');?>
	</h2>
	<div><hr ></div>
	
<?php foreach($result as $html):
	if(is_bool($html)==false):
		echo $html;
	endif;
endforeach;
?>

</div>