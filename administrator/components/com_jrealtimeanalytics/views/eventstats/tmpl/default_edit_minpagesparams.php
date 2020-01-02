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
			<h4><span class="icon-pencil"></span><?php echo JText::_( 'COM_JREALTIME_EVENT_SETTINGS' ); ?></h4>
		</div>
	</div>
	
	<div id="minpagesparams" class="accordion-body collapse in">
		<div class="accordion-inner">
			<div class="control-group">
				<div class="control-label">
					<label for="minpages" class="hasTooltip" title="<?php echo JText::_('COM_JREALTIME_MINPAGES_DESC');?>" ><?php echo JText::_('COM_JREALTIME_MINPAGES');?></label>
				</div>
				<div class="controls">
					<div class="input-prepend active">
						<span class="add-on" style="min-width:20px;"><span class="icon-pencil-2"></span></span>
						<input type="text"  name="params[minpages]" id="minpages" data-validation="required numbers" style="width:40px" value="<?php echo $this->record->params->get('minpages', null);?>" />
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
