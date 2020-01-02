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
<div class="o-form-group">
	<div class="o-checkbox">
		<input id="tos-checkbox-<?php echo $appId;?>" name="tos-<?php echo $appId;?>" type="checkbox" data-tos-checkbox />
		<label for="tos-checkbox-<?php echo $appId;?>" data-tos-link data-id="<?php echo $appId;?>">
			<a href="javascript:void(0);"><?php echo $title;?></a>
		</label>
	</div>
</div>
