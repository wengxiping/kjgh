<?php 
/** 
 * @package JREALTIMEANALYTICS::EVENTSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage eventstats
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 
?>
<form action="index.php" method="post" name="adminForm" id="adminForm"> 
	<div class="accordion-group">
		<div class="accordion-heading opened">
			<div class="accordion-toggle noaccordion">
				<h4><span class="icon-pencil"></span><?php echo JText::_( 'COM_JREALTIME_CAT_DETAILS' ); ?></h4>
			</div>
		</div>
		<div id="details" class="accordion-body collapse in">
	      	<div class="accordion-inner">
				<table class="admintable">
				<tbody>
					<tr>
						<td class="key left_title">
							<label for="name">
								<?php echo JText::_('COM_JREALTIME_TITLE' ); ?>:
							</label>
						</td>
						<td class="right_details">
							<input class="inputbox" type="text" name="title" id="title" data-validation="required" size="50" value="<?php echo $this->record->title;?>" />
						</td>
					</tr>
					<tr>
						<td class="key left_title">
							<label for="description">
								<?php echo JText::_('COM_JREALTIME_DESCRIPTION' ); ?>:
							</label>
						</td>
						<td class="right_details">
							<textarea class="inputbox" type="text" name="description" id="description" rows="5" cols="80" ><?php echo $this->record->description;?></textarea>
						</td>
					</tr> 
					<tr>
						<td class="key left_title">
							<label for="category" data-content="<?php echo JText::_('COM_JREALTIME_PARENT_DESC' ); ?>" class="hasPopover">
								<?php echo JText::_('COM_JREALTIME_PARENT' ); ?>:
							</label>
						</td>
						<td class="right_details">
							<?php echo $this->lists['categories'];?>
						</td>
					</tr> 
					<tr>
						<td class="key left_title">
							<label>
								<?php echo JText::_('COM_JREALTIME_PUBLISHED' ); ?>:
							</label>
						</td>
						<td class="right_details">
							<fieldset class="radio btn-group">
								<?php echo $this->lists['published']; ?>
							</fieldset>
						</td>
					</tr>
				</tbody>
				</table>
			</div>
		</div>
	</div>
	<input type="hidden" name="option" value="<?php echo $this->option?>" />
	<input type="hidden" name="id" value="<?php echo $this->record->id; ?>" />
	<input type="hidden" name="haschanged" value="" />
	<input type="hidden" name="task" value="" />
</form>