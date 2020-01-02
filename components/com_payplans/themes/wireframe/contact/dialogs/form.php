<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<dialog>
	<width>600</width>
	<height>350</height>
	<selectors type="json">
	{
		"{cancelButton}"  : "[data-cancel-button]",
		"{sendButton}" : "[data-submit-button]",
		"{form}" : "[data-form]",
		"{subject}": "[data-contact-subject]",
		"{contents}": "[data-contact-contents]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		},

		"{sendButton} click": function() {
			var self = this;

			PayPlans.ajax('site/views/contact/send', {
				"subject": this.subject().val(),
				"contents": this.contents().val()
			}).done(function(){
				self.parent.close();
			});
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_PP_CONTACT_US'); ?></title>
	<content>
		<form action="<?php echo JRoute::_('index.php');?>" method="post" class="o-form-horizontal">
			<div class="o-form-group">

				<label class="o-control-label" for="subject">
					<?php echo JText::_('COM_PAYPLANS_SUPPORT_EMAILFORM_SUBJECT'); ?>
				</label>
				<div class="o-control-input">
					<?php echo $this->html('form.text', 'subject', '', '', array('data-contact-subject' => '', 'placeholder' => JText::_('COM_PP_CONTACT_SUBJECT_PLACEHOLDER'))); ?>
				</div>
			</div>

			<div class="o-form-group">
				<label class="o-control-label" for="contents">
					<?php echo JText::_('COM_PAYPLANS_SUPPORT_EMAILFORM_BODY'); ?>
				</label>
				<div class="o-control-input">
					<?php echo $this->html('form.textarea', 'contents', '', '', array('data-contact-contents' => '', 'rows' => '10', 'placeholder' => JText::_('COM_PP_CONTACT_CONTENTS_PLACEHOLDER'))); ?>
				</div>
			</div>
		</form>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-pp-warning btn-sm"><?php echo JText::_('COM_PP_CLOSE_BUTTON'); ?></button>
		<button data-submit-button type="button" class="btn btn-pp-primary-o btn-sm"><?php echo JText::_('COM_PP_SEND_BUTTON'); ?></button>
	</buttons>
</dialog>
