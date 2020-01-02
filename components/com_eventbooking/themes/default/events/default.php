<?php
/**
 * @package        Joomla
 * @subpackage     Event Booking
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2010 - 2019 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

JHtml::_('formbehavior.chosen', 'select');

$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
?>
<h1 class="eb-page-heading"><?php echo $this->escape(JText::_('EB_USER_EVENTS')); ?></h1>
<form method="post" name="adminForm" id="adminForm" action="<?php echo JRoute::_('index.php?option=com_eventbooking&view=events&Itemid='.$this->Itemid); ; ?>">
    <div class="filters btn-toolbar clearfix mt-2 mb-2">
	    <?php echo $this->loadTemplate('search_bar'); ?>
    </div>
	<?php
	if(count($this->items))
	{
	?>
		<table class="<?php echo $bootstrapHelper->getClassMapping('table table-striped table-bordered'); ?> table-considered">
			<thead>
				<tr>
					<th>
						<?php echo JText::_('EB_TITLE'); ?>
					</th>
					<th width="18%">
						<?php echo JText::_('EB_CATEGORY'); ?>
					</th>
					<th class="center" width="10%">
						<?php echo JText::_('EB_EVENT_DATE'); ?>
					</th>
					<th width="7%">
						<?php echo JText::_('EB_NUMBER_REGISTRANTS'); ?>
					</th>
					<th width="5%" nowrap="nowrap">
						<?php echo JText::_('EB_STATUS'); ?>
					</th>
					<th width="1%" nowrap="nowrap">
						<?php echo JText::_('EB_ID'); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="6">
						<div class="pagination"><?php echo $this->pagination->getPagesLinks(); ?></div>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php
				$k      = 0;
				$Itemid = EventbookingHelper::getItemid();

				for ($i=0, $n=count( $this->items ); $i < $n; $i++)
				{
					$row  = $this->items[$i];
					$link = JRoute::_(EventbookingHelperRoute::getEventRoute($row->id, 0, $Itemid));
					?>
					<tr class="<?php echo "row$k"; ?>">
						<td>
							<a href="<?php echo $link; ?>" target="_blank">
								<?php echo $row->title ; ?>
							</a>
							<span class="pull-right">
								<a class="btn" href="<?php echo JRoute::_('index.php?option=com_eventbooking&view=event&layout=form&id=' . $row->id . '&Itemid=' . $this->Itemid . '&return=' . $this->return); ?>"><i class="icon-pencil"></i><?php echo JText::_('EB_EDIT'); ?></a>
								<?php
                                if (EventbookingHelperAcl::canChangeEventStatus($row->id))
                                {
                                    if ($row->published == 1)
                                    {
                                        $link = JRoute::_('index.php?option=com_eventbooking&task=event.unpublish&id=' . $row->id . '&Itemid=' . $this->Itemid . '&return=' . $this->return, false);
                                        $text = JText::_('EB_UNPUBLISH');
                                        $class = 'icon-unpublish';
                                    }
                                    else
                                    {
                                        $link = JRoute::_('index.php?option=com_eventbooking&task=event.publish&id=' . $row->id . '&Itemid=' . $this->Itemid . '&return=' . $this->return, false);
                                        $text = JText::_('EB_PUBLISH');
                                        $class = 'icon-publish';
                                    }
								?>
                                    <a class="btn" href="<?php echo $link ; ?>"><i class="<?php echo $class;?>"></i><?php echo $text ; ?></a>
                                <?php
                                }
                                ?>
							</span>
						</td>
						<td>
							<?php echo $row->category_name ; ?>
						</td>
						<td class="center">
							<?php echo JHtml::_('date', $row->event_date, $this->config->date_format, null); ?>
						</td>
						<td class="center">
							<?php echo (int) $row->total_registrants ; ?>
						</td>
						<td class="center">
							<?php
								if ($row->published)
								{
									echo JText::_('EB_PUBLISHED');
								}
								else
								{
									echo JText::_('EB_UNPUBLISHED');
								}
							?>
						</td>
						<td class="center">
							<?php echo $row->id; ?>
						</td>
					</tr>
					<?php
					$k = 1 - $k;
				}
				?>
			</tbody>
		</table>
	<?php
	}
	?>
</form>