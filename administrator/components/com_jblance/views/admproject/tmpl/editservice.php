<?php


/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	14 November 2014
 * @file name	:	views/admproject/tmpl/editservice.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Edit Service (jblance)
 */
 defined('_JEXEC') or die('Restricted access');

 JHtml::_('jquery.framework');
 JHtml::_('behavior.formvalidator');
 JHtml::_('formbehavior.chosen', 'select');
 JHtml::_('bootstrap.tooltip');
 JHtml::_('behavior.tabstate');

 $doc = JFactory::getDocument();
 $doc->addScript(JURI::root()."components/com_jblance/js/dropzone.js");
 $doc->addScript(JURI::root()."components/com_jblance/js/utility.js");
 $doc->addScript(JURI::root()."components/com_jblance/js/autosize.js");
 $doc->addStyleSheet(JURI::root().'components/com_jblance/css/style.css');

 $config 	  = JblanceHelper::getConfig();
 $currencysym = $config->currencySymbol;
 $currencycod = $config->currencyCode;

 $select = JblanceHelper::get('helper.select');		// create an instance of the class SelectHelper
 $row = $this->row;

 $attachments = JBMediaHelper::processAttachment($row->attachment, 'service', false);
 $registry = new JRegistry();
 $registry->loadArray($attachments);
 $serviceImageMockFile = $registry->toString();

 JblanceHelper::setJoomBriToken();
?>
<script type="text/javascript">
<!--
Joomla.submitbutton = function(task){
	if (task == 'admproject.cancelservice' || document.formvalidator.isValid(document.getElementById('editservice-form'))){
		Joomla.submitform(task, document.getElementById('editservice-form'));
	}
	else{
		alert('<?php echo $this->escape(JText::_('COM_JBLANCE_FIEDS_HIGHLIGHTED_RED_COMPULSORY')); ?>');
	}
}

jQuery(document).ready(function($){
	var params = { 'maxFileSize' : 2, 'maxFiles' : 5, 'uploadTask' : 'ajax.dzuploadfile', 'removeTask' : 'ajax.dzremovefile', 'acceptedFiles' : 'image/*', 'uploadType' : 'service'};	//todo: add size and max files to config
	createDropzone('service-image', 'serviceImage', '<?php echo $serviceImageMockFile; ?>', params);

	autosize(jQuery("#description"));
});

jQuery(document).ready(function($){
	$("#btnSendEmail").on("click", function(e){
		e.preventDefault()
		var approved = $("input[name='approved']:checked").val();
		var reason	 = $("#disapprove_reason").val();

		//check if the reason is not entered and approved=0
		if(approved == 0 && (!reason.length=== 0 || !reason.trim())){
			alert('<?php echo JText::_('COM_JBLANCE_PLEASE_ENTER_REASON_FOR_DISAPPROVAL', true); ?>');
			$("#disapprove_reason").focus();
			return false;
		}

		var myRequest = $.ajax({
			url: "index.php?option=com_jblance&task=admproject.approveservice&<?php echo JSession::getFormToken()."=1"; ?>",
			data: {"approved":approved, "disapprove_reason":reason, "service_id":'<?php echo $row->id; ?>'},
			beforeSend: function(){ $("#btnSendEmail").prop({"disabled": true, "value": '<?php echo JText::_('COM_JBLANCE_SENDING', true); ?>'}); },
			success: function(response){
				var resp = $.parseJSON(response);

				if(resp["result"] == "OK"){
					$("#sendEmailResponse").html(resp["msg"]);
					$("#btnSendEmail").prop({"value": '<?php echo JText::_('COM_JBLANCE_SENT', true); ?>'});
		          }
			}
		});
	});
});
//-->
</script>

<form action="index.php" method="post" name="adminForm" id="editservice-form" enctype="multipart/form-data" class="form-validate">
    <div class="form-inline form-inline-header">
		<div class="control-group">
    		<label class="control-label" for="service_title"><?php echo JText::_('COM_JBLANCE_SERVICE_TITLE'); ?><span class="redfont">*</span>:</label>
			<div class="controls">
				<input type="text" class="input-xxlarge input-large-text required" name="service_title" id="service_title" value="<?php echo $row->service_title;?>">
			</div>
  		</div>
	</div>
	<div class="form-inline">
	<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'servceinfo')); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'servceinfo', JText::_('COM_JBLANCE_SERVICE_INFORMATION', true)); ?>
		<div class="row-fluid">
			<div class="span6">
				<fieldset>
					<legend><?php echo JText::_('COM_JBLANCE_SERVICE_DETAILS'); ?></legend>
					<div class="control-group">
			    		<label class="control-label" for="id_category"><?php echo JText::_('COM_JBLANCE_SKILLS'); ?> <span class="redfont">*</span>:</label>
						<div class="controls">
							<?php
							$attribs = "class='input-xxlarge required' size='5' MULTIPLE";
							echo $select->getSelectCategoryTree('id_category[]', explode(',', $this->row->id_category), '', $attribs, '', true); ?>
						</div>
			  		</div>

                    <div class="control-group">
                        <label class="control-label" for="service_company"><?php echo JText::_('COM_JBLANCE_COMPANY'); ?> <span class="redfont">*</span>:</label>
                        <div class="controls">
                            <input style="font-size: 14px;" type="text" class="input-xxlarge input-large-text required" name="service_company" id="service_company" value="<?php echo $row->service_company;?>">
                        </div>
                    </div>

					<div class="control-group">
						<label class="control-label" for="description"><?php echo JText::_('COM_JBLANCE_DESCRIPTION'); ?> :</label>
						<div class="controls">
							<textarea name="description" id="description" class="input-xxlarge required hasTooltip" rows="5" style="max-height: 300px;"><?php echo $row->description; ?></textarea>
						</div>
					</div>
					<div class="control-group">
			    		<label class="control-label" for="user_id"><?php echo JText::_('COM_JBLANCE_USERNAME'); ?> <span class="redfont">*</span>:</label>
						<div class="controls">
							<?php echo $this->lists['userlist']; ?>
						</div>
			  		</div>
					<div class="control-group">
						<label class="control-label" for="price"><?php echo JText::_('COM_JBLANCE_PRICE'); ?> :</label>
						<div class="controls">
							<div class="input-prepend">
								<span class="add-on"><?php echo $currencysym; ?></span>
								<input class="input-mini required validate-numeric" type="text" name="price" id="price" value="<?php echo $row->price; ?>" />
							</div>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="duration"><?php echo JText::_('COM_JBLANCE_DURATION'); ?> :</label>
						<div class="controls">
							<div class="input-append">
								<input class="input-mini required validate-numeric" type="text" name="duration" id="duration" value="<?php echo $row->duration; ?>" />
								<span class="add-on"><?php echo JText::_('COM_JBLANCE_BID_DAYS'); ?></span>
							</div>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="instruction"><?php echo JText::_('COM_JBLANCE_INSTRUCTIONS_TO_BUYERS'); ?> :</label>
						<div class="controls">
							<textarea name="instruction" id="instruction" class="input-xlarge hasTooltip" rows="5" title="<?php echo JHtml::tooltipText(JText::_('')); ?>"><?php echo $row->instruction; ?></textarea>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="extras"><?php echo JText::_('COM_JBLANCE_ADD_ONS'); ?> :</label>
						<div class="controls">
							<?php
							$options = 3;
							$registry = new JRegistry;
							$registry->loadString($row->extras);
							$extras = $registry->toObject();

							//if is set, then set the value else initialise
							if(!isset($extras->fast)){
								$checked  = '';
								$price 	  = '';
								$duration = '';
							}
							else {
								$checked  = ($extras->fast->enabled) ? 'checked' : '';
								$price 	  = $extras->fast->price;
								$duration = $extras->fast->duration;
							}
							?>
							<div class="well well-small">
								<div class="row-fluid">
									<div class="span6">
										<label class="checkbox">
											<input type="hidden" name="extras[fast][enabled]" value="0" /> <!-- this is added when checkbox is not checked -->
											<input type="checkbox" id="service-extra-fast" name="extras[fast][enabled]" value="1" <?php echo $checked; ?> />
											<span class="label label-warning"><?php echo JText::_('COM_JBLANCE_FAST_DELIVERY'); ?></span>
											<?php echo JText::_('COM_JBLANCE_FAST_DELIVERY_DESC'); ?>
										</label>
									</div>
									<div class="span6">
										<div class="input-prepend">
											<span class="add-on"><?php echo $currencysym; ?></span>
											<input class="input-mini validate-numeric" type="text" name="extras[fast][price]" id="" value="<?php echo $price; ?>" />
										</div>
										 <span><?php echo JText::_('COM_JBLANCE_IN'); ?></span>
										<div class="input-append">
											<input class="input-mini validate-numeric" type="text" name="extras[fast][duration]" id="" value="<?php echo $duration; ?>" />
											<span class="add-on"><?php echo JText::_('COM_JBLANCE_BID_DAYS'); ?></span>
										</div>
									</div>
								</div>
							</div>
							<?php
							for($i=0; $i < $options; $i++){
								if(!isset($extras->$i)){
									$checked	 = '';
									$description = '';
									$price 	  	 = '';
									$duration 	 = '';
								}
								else {
									$checked 	 = ($extras->$i->enabled) ? 'checked' : '';
									$description = $extras->$i->description;
									$price 		 = $extras->$i->price;
									$duration 	 = $extras->$i->duration;
								}
							?>
							<div class="well well-small">
								<div class="row-fluid">
									<div class="span6">
										<label class="checkbox" style="display: inline-table;">
											<input type="hidden" name="extras[<?php echo $i; ?>][enabled]" value="0" /> <!-- this is added when checkbox is not checked -->
											<input type="checkbox" id="service-extra-fast" name="extras[<?php echo $i; ?>][enabled]" value="1" <?php echo $checked; ?> />
										</label>
										<input type="text" class="extra-description" name="extras[<?php echo $i; ?>][description]" placeholder="<?php echo JText::_('COM_JBLANCE_I_WILL'); ?>" value="<?php echo $description; ?>">
									</div>
									<div class="span6">
										<div class="input-prepend">
											<span class="add-on"><?php echo $currencysym; ?></span>
											<input class="input-mini validate-numeric" type="text" name="extras[<?php echo $i; ?>][price]" id="" value="<?php echo $price; ?>" />
										</div>
										 <span><?php echo JText::_('COM_JBLANCE_IN'); ?></span>
										<div class="input-append">
											<input class="input-mini validate-numeric" type="text" name="extras[<?php echo $i; ?>][duration]" id="" value="<?php echo $duration; ?>" />
											<span class="add-on"><?php echo JText::_('COM_JBLANCE_BID_DAYS'); ?></span>
										</div>
									</div>
								</div>
							</div>
							<?php
							} ?>
						</div>
					</div>
				</fieldset>
		  	</div>
		  	<div class="span6">
		  		<fieldset>
					<legend><?php echo JText::_('COM_JBLANCE_APPROVAL'); ?></legend>
			  		<div class="control-group">
			    		<label class="control-label" for="approved"><?php echo JText::_('COM_JBLANCE_APPROVED'); ?> :</label>
						<div class="controls">
							<?php $approved = $select->YesNoBool('approved', $this->row->approved);
							echo  $approved; ?>
						</div>
			  		</div>
			  		<div class="control-group">
						<label class="control-label" for="disapprove_reason"><?php echo JText::_('COM_JBLANCE_REASON_FOR_DISAPPROVAL'); ?> :</label>
						<div class="controls">
							<textarea name="disapprove_reason" id="disapprove_reason" class="input-xlarge hasTooltip" rows="5" title="<?php echo JHtml::tooltipText(JText::_('')); ?>"><?php echo $row->disapprove_reason; ?></textarea>
						</div>
					</div>
					<div class="alert alert-info">
					<?php echo JText::_('COM_JBLANCE_APPROVE_STATUS_INFO'); ?>
					</div>
					<div class="form-actions">
						<input class="btn btn-primary" type="button" id="btnSendEmail" value="<?php echo JText::_('COM_JBLANCE_SEND_EMAIL'); ?>" />
						<span class="help-inline" id="sendEmailResponse"></span>
					</div>
				</fieldset>
				<fieldset>
					<legend><?php echo JText::_('COM_JBLANCE_SERVICE_IMAGES'); ?></legend>
					<?php echo JBMediaHelper::renderDropzone('service-image'); ?>
				</fieldset>
		  	</div>
		  </div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>		<!-- end of serviceinfo tab -->

	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'orders', JText::_('COM_JBLANCE_ORDERS', true)); ?>
		<div class="row-fluid">
			<div class="span8">
				<table class="table table-striped table-bordered">
					<thead>
						<tr>
							<th><?php echo JText::_('COM_JBLANCE_BUYER'); ?></th>
							<th class="center"><?php echo JText::_('COM_JBLANCE_TOTAL_PRICE'); ?></th>
							<th class="center"><?php echo JText::_('COM_JBLANCE_TOTAL_DURATION'); ?></th>
							<th class="center"><?php echo JText::_('COM_JBLANCE_PROGRESS'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						for($i=0, $n=count($this->orders); $i < $n; $i++){
							$order 		 = $this->orders[$i];
							$buyerInfo = JFactory::getUser($order->user_id);
						?>
						<tr>
							<td>
								<?php echo $buyerInfo->username; ?>
							</td>
							<td class="right">
								<?php echo JblanceHelper::formatCurrency($order->price); ?>
							</td>
							<td class="center">
								<?php echo JText::plural('COM_JBLANCE_N_DAYS', $order->duration); ?>
							</td>
							<td>
								<?php echo (!empty($order->p_status)) ? JText::_($order->p_status) : JText::_('COM_JBLANCE_NOT_YET_STARTED'); ?>
								<div class="progress progress-success progress-striped span6 pull-right" title="<?php echo $order->p_percent; ?>%" style="min-height: 10px;">
									<div class="bar" style="width: <?php echo $order->p_percent; ?>%"></div>
								</div>
							</td>
						</tr>
						<?php
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>		<!-- end of orders tab -->

	<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	</div>
	<input type="hidden" name="option" value="com_jblance" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
