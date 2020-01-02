<?php
/**
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2019 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

$description = $this->category ? $this->category->description: $this->introText;
?>
<div id="eb-upcoming-events-page-table" class="eb-container">
	<?php
	if ($this->params->get('show_page_heading'))
	{
	?>
		<h1 class="eb-page-heading"><?php echo $this->escape($this->params->get('page_heading'));?></h1>
	<?php
	}

	if ($description)
	{
	?>
		<div class="eb-description"><?php echo $description;?></div>
	<?php
	}

	if ($this->config->get('show_search_bar', 0))
	{
		$layoutData = [
			'search'         => $this->state->search,
			'locationId'     => $this->state->location_id,
			'filterDuration' => $this->state->filter_duration,
		];

		echo EventbookingHelperHtml::loadCommonLayout('common/search.php', $layoutData);
	}

	if (count($this->items))
	{
		$layoutData = [
			'items'           => $this->items,
			'config'          => $this->config,
			'Itemid'          => $this->Itemid,
			'nullDate'        => $this->nullDate,
			'ssl'             => (int) $this->config->use_https,
			'viewLevels'      => $this->viewLevels,
			'categoryId'      => $this->categoryId,
			'bootstrapHelper' => $this->bootstrapHelper,
		];

		echo EventbookingHelperHtml::loadCommonLayout('common/events_table.php', $layoutData);
	}
	else
	{
	?>
		<p class="text-info"><?php echo JText::_('EB_NO_UPCOMING_EVENTS') ?></p>
	<?php
	}

	if ($this->pagination->total > $this->pagination->limit)
	{
	?>
		<div class="pagination">
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
	<?php
	}
	?>

	<form method="post" name="adminForm" id="adminForm" action="<?php echo JRoute::_('index.php?option=com_eventbooking&view=upcomingevents&layout=table&Itemid='.$this->Itemid); ?>">
		<input type="hidden" name="id" value="0" />
		<input type="hidden" name="task" value="" />
		<script type="text/javascript">
			function cancelRegistration(registrantId)
			{
				var form = document.adminForm ;

				if (confirm("<?php echo JText::_('EB_CANCEL_REGISTRATION_CONFIRM'); ?>"))
				{
					form.task.value = 'registrant.cancel' ;
					form.id.value = registrantId ;
					form.submit() ;
				}
			}
		</script>
	</form>
</div>