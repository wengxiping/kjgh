<?php
/**
* @copyright	Copyright (C) 2009 - 2015 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Braintree
* @contact 		support+payplans@readybytes.in
*/
if(defined('_JEXEC')===false) die();?>
<?php if (!empty($errors)) { ?>
		<?php foreach ($errors as $error) { ?>
			<div>
				<?php echo $error."<br/>"; ?>
			</div>
		<?php } ?>
<?php } ?>