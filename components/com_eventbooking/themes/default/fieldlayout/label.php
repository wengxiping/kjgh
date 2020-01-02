<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

$config     = EventbookingHelper::getConfig();
$class      = '';
$useTooltip = false;

if ($config->get('display_field_description', 'use_tooltip') == 'use_tooltip' && !empty($description))
{
	JHtml::_('bootstrap.tooltip');
	JFactory::getDocument()->addStyleDeclaration(".hasTip{display:block !important}");
	$useTooltip = true;
	$class = 'hasTooltip hasTip';
}
?>
<label id="<?php echo $name; ?>-lbl" for="<?php echo $name; ?>"<?php if ($class) echo ' class="' . $class . '"' ?> <?php if ($useTooltip) echo ' title="' . JHtml::tooltipText(trim($title, ':'), $description, 0) . '"'; ?>>
	<?php
	if ($row->required)
    	{
    	?>
<span class="star">&#160;*</span>
    	<?php
    	}
    	?>
    <?php
	echo $title;
	?>


</label>
