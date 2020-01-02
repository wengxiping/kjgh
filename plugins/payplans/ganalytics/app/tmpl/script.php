<?php
/**
* @package    PayPlans
* @copyright  Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license    GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
var _gaq = _gaq || [];

_gaq.push(['_setAccount', '<?php echo $analyticsId; ?>']);
_gaq.push(['_trackPageview']);
_gaq.push(['_addTrans', '<?php echo $invoiceKey; ?>', 'Subscriptions Powered By PayPlans', '<?php echo $total; ?>']);
_gaq.push(['_addItem', '<?php echo $invoiceKey; ?>', '<?php echo $planTitle;?>', '<?php echo $invoiceTitle; ?>', '',  '<?php echo $price;?>', '1']);
_gaq.push(['_trackTrans']);

(function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(ga);
})();