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
<div class="pp-checkout-container">
	<?php echo $this->output('site/checkout/default/header', array('step' => 'payment', 'title' => 'COM_PP_COMPLETE_ORDER')); ?>
	
	<div class="pp-checkout-wrapper">
		<div class="pp-checkout-wrapper__sub-content">
			
			<?php echo PP::info()->html(array('t-lg-mb--xl')); ?>

			<div class="pp-checkout-menu">

				<div class="t-lg-pt--lg">
					<?php foreach ($result as $html) { ?>
						<?php if (is_bool($html) == false) { ?>
							<?php echo $html;?>
						<?php } ?>
					<?php } ?>
				</div>
			</div>
		</div>		
	</div>
</div>