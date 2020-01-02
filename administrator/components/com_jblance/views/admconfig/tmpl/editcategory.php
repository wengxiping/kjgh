<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	20 March 2012
 * @file name	:	views/admconfig/tmpl/editcategory.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Edit category (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 JHtml::_('behavior.formvalidator');
 JHtml::_('formbehavior.chosen', 'select');
 
 $doc = JFactory::getDocument();
 $doc->addStyleSheet(JUri::root().'components/com_jblance/css/style.css');
 $doc->addScript(JUri::root()."components/com_jblance/js/utility.js");
 $doc->addScript(JUri::root()."components/com_jblance/js/autosize.js");
 $doc->addScript(JUri::root()."components/com_jblance/js/dropzone.js");
 
 $category_image = JBMediaHelper::processAttachment($this->row->category_image, 'category', false);
 $registry = new JRegistry();
 $registry->loadArray($category_image);
 $categoryImageMockFile = $registry->toString();
 
 JblanceHelper::setJoomBriToken();
?>
<script type="text/javascript">
<!--
Joomla.submitbutton = function(task){
	if (task == 'admconfig.cancelcategory' || document.formvalidator.isValid(document.getElementById('editcategory-form'))) {
		Joomla.submitform(task, document.getElementById('editcategory-form'));
	}
	else {
		alert('<?php echo $this->escape(JText::_('COM_JBLANCE_FIEDS_HIGHLIGHTED_RED_COMPULSORY'));?>');
	}
}

jQuery(document).ready(function($){
	var params = { 'maxFileSize' : 2, 'maxFiles' : 1, 'uploadTask' : 'ajax.dzuploadfile', 'removeTask' : 'ajax.dzremovefile', 'acceptedFiles' : 'image/*', 'uploadType' : 'category'};	//todo: add size and max files to config
	createDropzone('category_image', 'categoryImage', '<?php echo $categoryImageMockFile; ?>', params);

});

jQuery(document).ready(function($){
	
	$("#parent").on("change", hideCategoryImage);

	$("#parent").triggerHandler("change");

	function hideCategoryImage(){
		sel = jQuery("#parent option:selected").val();
		if(sel > 0){
			$("#div-category-image").hide();
		}
		else {
			$("#div-category-image").show();
		}
	}
	
});
//-->
</script>

<form action="index.php" method="post" id="editcategory-form" name="adminForm" class="form-validate form-horizontal">
	<div class="row-fluid">
		<div class="span8">
			<fieldset class="adminform">
				<legend><?php echo JText::_('COM_JBLANCE_DETAILS'); ?></legend>
				<div class="control-group">
		    		<label class="control-label" for="category"><?php echo JText::_('COM_JBLANCE_CATEGORY'); ?>:</label>
					<div class="controls">
						<input class="input-xlarge input-large-text required" type="text" name="category" id="category" value="<?php echo $this->row->category; ?>" />
					</div>
		  		</div>
				<div class="control-group">
		    		<label class="control-label" for="parent"><?php echo JText::_('COM_JBLANCE_PARENT_ITEM'); ?>:</label>
					<div class="controls">
						<?php 
						$select = JblanceHelper::get('helper.select');		// create an instance of the class SelectHelper
						$attribs = "class='input-xlarge' size='15'";
						$categtree = $select->getSelectCategoryTree('parent', $this->row->parent, 'COM_JBLANCE_ROOT_CATEGORY', $attribs, '', false, true);
						echo $categtree;
						?>
					</div>
		  		</div>
				<div class="control-group" id="div-category-image">
		    		<label class="control-label" for="category_image"><?php echo JText::_('COM_JBLANCE_IMAGE'); ?></label>
					<div class="controls">
						<?php echo JBMediaHelper::renderDropzone('category_image'); ?>
					</div>
		  		</div>
			</fieldset>	
		</div>
	</div>
	<input type="hidden" name="option" value="com_jblance" />
	<input type="hidden" name="task" value="savecategory" />
	<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
	<input type="hidden" name="boxchecked" value="0" />
    <?php echo JHtml::_('form.token'); ?>
</form>
