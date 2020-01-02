<?php

/**
 * @package         EngageBox
 * @version         3.5.2 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2019 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

$box     = $displayData;

$height  = $box->params->get("iframeheight", "500px");
$url     = $box->params->get("iframeurl");
$scroll  = $box->params->get("iframescrolling", "no");
$params  = $box->params->get("iframeparams");
$async   = $box->params->get("iframeasync", "afterOpen") == "dom" ? false : $box->params->get("iframeasync", "afterOpen");
$header  = $box->params->get("iframeheader", null);
$class   = ($height == "100%") ? "eboxFitFrame" : "";
$content = '<div class="iframeWrapper">' .
				'<iframe width="100%" height="' . $height . '" src="' . $url . '" scrolling="' . $scroll . '" frameborder="0" allowtransparency="true" ' . $params . ' class="' . $class . '"></iframe>' .
			'</div>';

?>

<?php if (!empty($header)) ?>
	<div class="rstbox-content-header">
		<?php echo $box->params->get("iframeheader"); ?>
	</div>
<?php ?>

<div class="rstbox-content-wrap">
	<?php if (!$async) { echo $content; } ?> 
</div>

<?php 

if ($async)
{ 
	JFactory::getDocument()->addScriptDeclaration('
		jQuery(function($) {
			var box        = $("#rstbox_' . $box->id . '");
			var container  = box.find(".rstbox-content-wrap");
			var content    = ' . json_encode($content) .';
			var async      = ' . json_encode($async) .';

			if (async == "pageLoad") {
				$(window).on("load", function() {
					container.html(content);
				})
			} else {
				box.on(async, function() {
					if (!container.find("iframe").length) {
						container.html(content);
					}
				});
			}
		});'
	);
}

if ($box->params->get("removeonclose", false))
{ 
	JFactory::getDocument()->addScriptDeclaration('
		jQuery(function($) {
			var box        = $("#rstbox_' . $box->id . '");
			var container  = box.find(".rstbox-content-wrap");

			box.on("afterClose", function() {
				container.empty();
			})
		});'
	);
}


?>