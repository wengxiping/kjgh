<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-search-mini-result-list__item" data-search-suggestion
	data-search-suggestion-value="<?php echo $item->value; ?>"
	>
	<a href="javascript:void(0);">
		<span class="es-search-mini-result-name">
			<?php echo $item->text; ?>
		</span>
	</a>
</div>
