<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

$bootstrapHelper = EventbookingHelperHtml::getAdminBootstrapHelper();
$span7Class = $bootstrapHelper->getClassMapping('span7');
$span5Class = $bootstrapHelper->getClassMapping('span5');
?>
<form action="index.php?option=com_eventbooking&view=theme" method="post" name="adminForm" id="adminForm" class="adminform form form-horizontal">
<div class="row-fluid">
<div class="<?php echo $span7Class; ?>">
	<fieldset class="adminform">
		<legend><?php echo JText::_('EB_THEME_DETAIL'); ?></legend>
				<div class="control-group">
					<div class="control-label">
						<?php echo  JText::_('EB_NAME'); ?>
					</div>
					<div class="controls">
						<?php echo $this->item->name ; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo  JText::_('EB_TITLE'); ?>
					</div>
					<div class="controls">
						<input class="text_area" type="text" name="title" id="title" size="40" maxlength="250" value="<?php echo $this->item->title;?>" />
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo JText::_('EB_AUTHOR'); ?>
					</div>
					<div class="controls">
						<input class="text_area" type="text" name="author" id="author" size="40" maxlength="250" value="<?php echo $this->item->author;?>" />
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo JText::_('Creation date'); ?>
					</div>
					<div class="controls">
						<?php echo $this->item->creation_date; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo JText::_('Copyright') ; ?>
					</div>
					<div class="controls">
						<?php echo $this->item->copyright; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo JText::_('License'); ?>
					</div>
					<div class="controls">
						<?php echo $this->item->license; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo JText::_('Author email'); ?>
					</div>
					<div class="controls">
						<?php echo $this->item->author_email; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo JText::_('Author URL'); ?>
					</div>
					<div class="controls">
						<?php echo $this->item->author_url; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo JText::_('Version'); ?>
					</div>
					<div class="controls">
						<?php echo $this->item->version; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo JText::_('Description'); ?>
					</div>
					<div class="controls">
						<?php echo $this->item->description; ?>
					</div>
				</div>
	</fieldset>
</div>
<div class="<?php echo $span5Class; ?>">
	<fieldset class="adminform">
		<legend><?php echo JText::_('Theme Parameters'); ?></legend>
		<?php
			foreach ($this->form->getFieldset('basic') as $field)
			{
			?>
			<div class="control-group">
				<div class="control-label">
					<?php echo $field->label ;?>
				</div>
				<div class="controls">
					<?php echo  $field->input ; ?>
				</div>
			</div>
			<?php
			}
		?>
	</fieldset>
</div>
</div>
<div class="clearfix"></div>
	<?php echo JHtml::_( 'form.token' ); ?>
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>"/>
	<input type="hidden" name="task" value="" />
</form>