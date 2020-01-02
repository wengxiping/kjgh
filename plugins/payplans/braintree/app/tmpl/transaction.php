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
<?php if ($transaction_html) { 

		$newArray = array () ;
		foreach ($transaction_html as $key => $value) {
			if (is_array($value)) {
				foreach ($value as $key1 => $value1) {
					if (is_array($value1)) {
						foreach ($value1 as $key2 => $value2) {
							if (is_array($value2)) {
								foreach ($value2 as $key3 => $value3) {
									if (is_array($value3)) {
										foreach ($value3 as $key4 => $value4) {
											$newArray[$key."_".$key4] = $value4;
										}
									} else {
										$newArray[$key."_".$key3] = $value3;
									}
								}
							} else {
								$newArray[$key."_".$key2] = $value2;
							}
						}
					} else {
						$newArray[$key."_".$key1] = $value1;
					}
				}

			} else {
				$newArray[$key] = $value;
			} 
		}

		$data = array('transaction_createdAt', 'transaction_updatedAt','transaction_creditCard',
						'transaction_statusHistory','transaction_descriptor','transaction_customerDetails', 'transaction_bin',
						'transaction_last4', 'transaction_cardType', 'transaction_expirationMonth', 'transaction_expirationYear',
						'transaction_customerLocation', 'transaction_cardholderName');

		$newArray = array_diff_key($newArray,array_flip($data)); 
		unset($newArray['subscription__attributes']);
		unset($newArray['transaction__attributes']); ?>

		<table class="app-table table table-hover">
			<thead>
				<tr>
					<td width="20%">
						<?php echo JText::_('Key'); ?>
					</td>
					<td>
						<?php echo JText::_('Value'); ?>
					</td>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($newArray as $key => $value) { ?>
					<tr>
						<td>
							<?php echo $key;?>
						</td>
						<td>
							<?php echo $value ? $value : '&mdash;'; ?>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
<?php } ?>
      
	      