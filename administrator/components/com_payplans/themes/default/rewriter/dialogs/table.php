<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<table class="table table-hover table-borderless">
	<tbody>
		<?php foreach ($data as $value) { ?>
		<tr>
			<td>
				[[<?php echo $value;?>]]
			</td>
			<td class="center" width="20%">
				<a href="javascript:void(0);" class="btn btn-pp-default-o" data-value="[[<?php echo $value;?>]]" data-copy-clipboard>
					<i class="far fa-clipboard"></i>
				</a>
			</td>
		</tr>
		<?php } ?>
	</tbody>
</table>