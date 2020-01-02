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

// If the close button is hidden do nothing
if ($box->params->get("closebutton.hide", false))
{
	return;
}

$btnSource = $box->params->get("closebutton.source", "icon");
$color     = $box->params->get("closebutton.color", null);
$size      = $box->params->get("closebutton.size", null);
$cbStyles  = "";

if ($btnSource == "icon")
{
	$cbStyles = implode(";", array_filter(array(
		$color ? 'color:' . $color : null,
		$size ? 'font-size:' . $size . "px" : null
	)));
}

// Delay the display of the close button using CSS animation
$delay = (int) $box->params->get("closebutton.delay", 0);
if ($delay > 0)
{
	$styles[] = '
		#rstbox_' . $box->id . ' .rstbox-close {
			visibility: hidden;
		}

		#rstbox_' . $box->id . '.rstbox_visible .rstbox-close {
			animation: ' . $delay . 'ms ebFadeIn;
			animation-fill-mode: forwards;
		}
	';
}

// Add the hover color
if ($hoverColor = $box->params->get("closebutton.hover", null))
{
	$styles[] = '
		#rstbox_' . $box->id . ' .rstbox-close:hover {
			color: ' . $hoverColor . ' !important;
		}	
	';
}	

?>

<button type="button" data-ebox-cmd="closeKeep" class="rstbox-close" aria-label="Close" style="<?php echo $cbStyles; ?>">
	<?php if ($btnSource == "image") { ?>
		<img src="<?php echo JURI::base(true) . '/' . $box->params->get("closebutton.image") ?>"/>
	<?php } else { ?>
		<span aria-hidden="true">&times;</span>
	<?php } ?>
</button>
	
