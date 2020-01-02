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
	<!-- Data source details --> 
	<?php echo $this->loadTemplate('edit_details'); ?>
	
	<!-- View Itemid/Custom URL --> 
	<?php if($this->record->type == 'viewurl') {
		echo $this->loadTemplate('edit_viewurlparams');
	}?>
	
	<!-- Min pages visited for single visit --> 
	<?php if($this->record->type == 'minpages') {
		echo $this->loadTemplate('edit_minpagesparams');
	}?>
	
	<!-- Min time spent for single visit --> 
	<?php if ($this->record->type == 'mintime') {
		echo $this->loadTemplate('edit_mintimeparams');
	}?>  
	
	<!-- Min pages spent on single page --> 
	<?php if ($this->record->type == 'mintimeonpage') {
		echo $this->loadTemplate('edit_mintimeonpageparams');
	}?>
	
	<!-- View Itemid/Custom URL -->
	<?php if($this->record->id) {  
		echo $this->loadTemplate('edit_events');
	}?>
	
	<input type="hidden" name="option" value="<?php echo $this->option?>" />
	<input type="hidden" name="id" value="<?php echo $this->record->id; ?>" />
	<input type="hidden" name="ordering" value="<?php echo $this->record->ordering; ?>" />
	<input type="hidden" name="task" value="" />
</form>