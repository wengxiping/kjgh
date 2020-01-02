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

use NRFramework\Functions;

$boxes             = $displayData;
$p                 = JComponentHelper::getParams('com_rstbox');
$googleAnalyticsID = ($p->get("gaTrack", 0) && ($p->get("gaID") != "")) ? $p->get("gaID") : false;
$trackingAttr      = ($googleAnalyticsID) ? $googleAnalyticsID . ':' . $p->get('gaCategory') : false;
$version           = Functions::getExtensionVersion("com_rstbox");
$rootURL		   = Functions::getRootURL();

?>

<div class="rstboxes" data-t="<?php echo JSession::getFormToken() ?>" data-baseurl="<?php echo $rootURL ?>" data-site="<?php echo md5(JPATH_SITE) ?>" data-debug="<?php echo $p->get("debug", 0) ?>" <?php if ($trackingAttr) { ?> data-tracking="<?php echo $trackingAttr ?>" <?php } ?>>

	<?php if ($p->get("forceloadmedia")) { 
		$path = JURI::root(true) . "/media/com_rstbox/";
	?>
		<?php if ($p->get("loadCSS", true)) { ?>
			<link rel="stylesheet" href="<?php echo $path ?>css/engagebox.css?v=<?php echo $version; ?>" type="text/css" />
		<?php } ?>

		<?php if ($p->get("loadVelocity", true)) { ?>
			<script src="<?php echo $path ?>js/velocity.js?v=<?php echo $version; ?>" type="text/javascript"></script>
			<script src="<?php echo $path ?>js/velocity.ui.js?v=<?php echo $version; ?>" type="text/javascript"></script>
		<?php } ?>

		<script src="<?php echo $path ?>js/engagebox.js?v=<?php echo $version; ?>" type="text/javascript"></script>
	<?php } ?>

	<?php foreach ($boxes as $box) {
		$styles[] = trim($box->params->get("customcss", null));
	?>

	<div <?php echo $box->HTMLattributes ?> role="dialog" tabindex="-1">
		
		<?php include("closebutton.php"); ?>

		<div class="rstbox-container">
			<?php if ($box->params->get("showtitle", true)) { ?>
				<div class="rstbox-header">
					<div class="rstbox-heading"><?php echo $box->name ?></div>
				</div>
				<?php } ?>
			<div class="rstbox-content">
				<?php
					echo $box->content;
					echo $p->get("globalFooter");
				?>
			</div>
		</div>
		<?php echo $box->params->get("customcode"); ?>
	</div>	
	<?php } ?>
</div>

<?php 
	// Remove empty values
	$styles = array_filter($styles);

	if (count($styles) > 0)
	{
		JFactory::getDocument()->addStyleDeclaration(implode("", $styles));
	}
?>