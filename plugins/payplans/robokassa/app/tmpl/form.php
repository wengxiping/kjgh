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
<div class="pp-result-container">
	<form action="<?php echo $formUrl ?>" method="post" data-pp-form-robokassa>
		<?php echo $this->html('html.redirection'); ?>

		<?php echo $this->html('form.hidden', 'MrchLogin', $merchantLogin); ?>
		<?php echo $this->html('form.hidden', 'OutSum', $outSum); ?>
		<?php echo $this->html('form.hidden', 'InvId', $invId); ?>
		<?php echo $this->html('form.hidden', 'Desc', $desc); ?>
		<?php echo $this->html('form.hidden', 'SignatureValue', $signature); ?>
		<?php echo $this->html('form.hidden', 'Shp_paymentKey', $Shp_paymentKey); ?>
		<?php echo $this->html('form.hidden', 'Encoding', 'utf-8'); ?>	

		<?php if ($sandbox) { ?>
			<?php echo $this->html('form.hidden', 'isTest', 1); ?>	
		<?php } ?>
	</form>
</div>