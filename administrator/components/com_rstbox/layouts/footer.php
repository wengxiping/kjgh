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
$ver = NRFramework\Functions::getExtensionVersion("com_rstbox");

?>

<div class="footer text-center">
    <?php echo JText::_('COM_RSTBOX') . " v" . $ver ?>
    <br>

    <?php if ($this->config->get("showcopyright", true)) { ?>
        <div class="footer_review">
            <?php echo JText::_("NR_LIKE_THIS_EXTENSION") ?>
            <a href="https://extensions.joomla.org/extensions/extension/style-a-design/popups-a-iframes/engage-box" target="_blank"><?php echo JText::_("NR_LEAVE_A_REVIEW") ?></a> 
            <a href="https://extensions.joomla.org/extensions/extension/style-a-design/popups-a-iframes/engage-box" target="_blank" class="stars"><span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span></a>
        </div>

        <Br>&copy; <?php echo JText::sprintf('NR_COPYRIGHT', date("Y")) ?><br>
        <?php echo JText::_("NR_NEED_SUPPORT") ?> 

    	<a href="https://www.tassos.gr/joomla-extensions/engagebox//docs" target="_blank"><?php echo JText::_("NR_READ_DOCUMENTATION") ?></a> or
        <a href="http://www.tassos.gr/contact?s=BackEndSupport-<?php echo $ver ?>" target="_blank"><?php echo JText::_("NR_DROP_EMAIL") ?></a>
    <?php } ?>
</div>