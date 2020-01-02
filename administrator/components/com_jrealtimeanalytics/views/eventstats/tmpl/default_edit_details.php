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
<div class="accordion-group">
	<div class="accordion-heading opened">
		<div class="accordion-toggle noaccordion">
			<h4><span class="icon-pencil"></span><?php echo JText::_( 'COM_JREALTIME_EVENT_DETAILS' ); ?></h4>
		</div>
	</div>
	<div id="details" class="accordion-body collapse in">
      	<div class="accordion-inner">
			<table class="admintable">
			<tbody>
				<tr>
					<td class="key left_title">
						<label for="type" data-content="<?php echo JText::_('COM_JREALTIME_EVENT_TYPE_DESC' ); ?>" class="hasPopover">
							<?php echo JText::_('COM_JREALTIME_EVENT_TYPE' ); ?>:
						</label>
					</td>
					<td class="right_details">
						<?php echo $this->lists['type']; ?>
					</td>
				</tr> 
				<tr>
					<td class="key left_title">
						<label for="name">
							<?php echo JText::_('COM_JREALTIME_NAME' ); ?>:
						</label>
					</td>
					<td class="right_details">
						<input class="inputbox" type="text" name="name" id="name" data-validation="required" size="50" value="<?php echo $this->record->name;?>" />
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
						<label for="category" data-content="<?php echo JText::_('COM_JREALTIME_CATEGORY_DESC' ); ?>" class="hasPopover">
							<?php echo JText::_('COM_JREALTIME_CATEGORY' ); ?>:
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
				<tr>
					<td class="key left_title">
						<label data-content="<?php echo JText::_('COM_JREALTIME_TRACK_GOAL_DESC' ); ?>" class="hasPopover">
							<?php echo JText::_('COM_JREALTIME_TRACK_GOAL' ); ?>:
						</label>
					</td>
					<td class="right_details">
						<fieldset class="radio btn-group">
							<?php echo $this->lists['hasgoal']; ?>
						</fieldset>
					</td>
				</tr>
				<tr>
					<td class="key left_title">
						<label for="goal_expectation" data-content="<?php echo JText::_('COM_JREALTIME_TRACK_GOAL_EXPECTATION_DESC' ); ?>" class="hasPopover">
							<?php echo JText::_('COM_JREALTIME_TRACK_GOAL_EXPECTATION' ); ?>:
						</label>
					</td>
					<td class="right_details">
						<fieldset class="radio btn-group">
							<input class="inputbox" type="text" data-validation="numbers" style="width:50px" name="goal_expectation" id="goal_expectation" value="<?php echo $this->record->goal_expectation;?>" />
						</fieldset>
					</td>
				</tr>
				<tr>
					<td class="key left_title">
						<label for="goal_expectation" data-content="<?php echo JText::_('COM_JREALTIME_TRACK_GOAL_NOTIFICATION_DESC' ); ?>" class="hasPopover">
							<?php echo JText::_('COM_JREALTIME_TRACK_GOAL_NOTIFICATION' ); ?>:
						</label>
					</td>
					<td class="right_details">
						<fieldset class="radio btn-group">
							<?php echo $this->lists['hasgoalnotification']; ?>
						</fieldset>
					</td>
				</tr>
				<tr>
					<td class="key left_title">
						<label for="goal_notification_emails" data-content="<?php echo JText::_('COM_JREALTIME_TRACK_GOAL_NOTIFICATION_EMAILS_DESC' ); ?>" class="hasPopover">
							<?php echo JText::_('COM_JREALTIME_TRACK_GOAL_NOTIFICATION_EMAILS' ); ?>:
						</label>
					</td>
					<td class="right_details">
						<fieldset class="radio btn-group">
							<input class="inputbox" type="text" style="width:350px" name="goal_notification_emails" id="goal_notification_emails" value="<?php echo $this->record->goal_notification_emails;?>" />
						</fieldset>
					</td>
				</tr>
			</tbody>
			</table>
		</div>
	</div>
</div>