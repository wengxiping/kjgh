<?php
/**
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2019 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die ;
?>

<div id="eb-waiting-list-complete-page" class="eb-container">
    <h1 class="eb-page-heading"><?php echo JText::_('EB_WATIINGLIST_COMPLETE'); ?></h1>
    <div id="eb-message" class="eb-message"><?php echo JHtml::_('content.prepare', $this->message); ?></div>
</div>