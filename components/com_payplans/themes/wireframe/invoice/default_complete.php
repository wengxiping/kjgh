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
<div class="pp-invoice-thanks">
<h2 class="componentheading pp-primary pp-color pp-border pp-background">
	<?php echo JText::_('COM_PAYPLANS_PAYMENT_SUCCESS'); ?>	
</h2>

<?php if(!empty($redirecturl)):?>
	 <script type="text/javascript">
		window.onload = function(){
			setTimeout("payplans.url.redirect('<?php echo XiRoute::_($redirecturl); ?>')", 3000);
		}
	</script>
<?php endif;?>

	<div>
		<?php echo $this->loadTemplate('partial_invoice', compact('invoice', 'user')); ?>
	</div>
</div>
<?php 
