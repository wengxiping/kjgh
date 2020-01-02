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
<?php if ($transaction_html) { 

		$newArray = array () ;
		foreach ($transaction_html as $key => $value) {
			if (is_array($value)) {
				foreach ($value as $key1 => $value1) {
					if (is_array($value1)) {
						foreach ($value1 as $key2 => $value2) {
							$newArray[$key."_".$key2] = $value2;
						}
					} else {
						$newArray[$key."_".$key1] = $value1;
					}
				}

			} else {
				$newArray[$key] = $value;
			}
		} 

		$data = array('card_display_number', 'card_scheme', 'card_issuing_country', 'card_expiry_month', 'card_expiry_year', 
						'card_name', 'card_address_line1', 'card_address_line2', 'card_address_city', 'card_address_postcode', 
						'card_address_state', 'card_address_country'); ?>

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

			<?php if (in_array($key, $data)) {
				continue;
			} ?>

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
