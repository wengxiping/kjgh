<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');


?>
<div id="es" class="mod-es mod-es-customfieldsearch <?php echo $lib->getSuffix();?>" data-mod-customfield-search data-submit-onclick="<?php echo $submitOnClick; ?>">
	<form method="get" action="<?php echo JRoute::_('index.php'); ?>" name="frmSearch" class="mod-es-dating-search-form <?php echo $lib->isMobile() ? 'is-mobile' : '';?>">
		<div class="es-list">

			<?php foreach ($fields as $field) { ?>
				<div class="es-list-item <?php echo $field->isConditional() ? 't-hidden' : ''; ?>" data-customfield-search-item>
					<div class="es-list-item__media">
						<i class="fa fa-folder"></i>
					</div>
					<div class="es-list-item__context">
						<div class="es-list-item__hd">
							<div class="es-list-item__content">

								<div class="es-list-item__title">
									<span><?php echo JText::_($field->title);?></span>
								</div>
							</div>

							<div class="es-list-item__action">
								<a class="t" data-bs-toggle="collapse" href="#es-field-<?php echo $field->id; ?>">
									<i class="fa es-mod-chevron"></i>
								</a>
							</div>
						</div>
						<div class="es-list-item__bd t-lg-pt--no">
							<div id="es-field-<?php echo $field->id; ?>" class="in <?php echo $field->isConditional() ? 't-hidden' : ''; ?>" data-field-item
								data-isconditional="<?php echo $field->isConditional(); ?>"
								data-conditions="<?php echo ES::string()->escape($field->getConditions(false)); ?>"
								data-conditions-logic="<?php echo $field->getConditionsLogic(); ?>"
								data-field-item="<?php echo $field->element; ?>"
								data-id="<?php echo $field->id; ?>"
								data-required="<?php echo $field->required; ?>"
								data-name="<?php echo SOCIAL_FIELDS_PREFIX . $field->id; ?>">

								<?php if ($field->element == 'boolean') { ?>
									<div class="o-select-group">
										<select name="<?php echo $field->element;?>" id="<?php echo $field->element;?>" class="o-form-control" data-dropdown-field>
											<option value=""><?php echo JText::_('Select value') ?></option>
											<option value="1" <?php echo $field->data == '1' ? 'selected' : ''; ?>><?php echo JText::_('COM_EASYSOCIAL_YES'); ?></option>
											<option value="0" <?php echo $field->data == '0' ? 'selected' : ''; ?>><?php echo JText::_('COM_EASYSOCIAL_NO'); ?></option>

										</select>
										<label for="<?php echo $field->element;?>" class="o-select-group__drop"></label>
									</div>
								<?php } ?>

								<?php if ($field->element == 'dropdown') { ?>
									<div class="o-select-group">
										<select name="<?php echo $field->element;?>" id="<?php echo $field->element;?>" class="o-form-control" data-dropdown-field>
											<option value=""><?php echo JText::_('Select value') ?></option>
											<?php foreach ($field->options as $option) { ?>
												<option value="<?php echo ES::string()->escape($option->value);?>" <?php echo $option->value == $field->data ? 'selected' : ''; ?>><?php echo $option->title; ?></option>
											<?php } ?>
										</select>
										<label for="<?php echo $field->element;?>" class="o-select-group__drop"></label>
									</div>
								<?php } ?>

								<?php if ($field->element == 'checkbox') { ?>
									<?php $i = 0; ?>
									<?php foreach ($field->options as $option) { ?>
										<div class="t-lg-mt--sm">
											<div class="o-checkbox">
												<input type="checkbox" id="<?php echo $option->value;?>" name="<?php echo $field->element;?>" value="<?php echo ES::string()->escape($option->value);?>" <?php echo in_array($option->value, $field->checkedItems) ? 'checked' : ''; ?> data-checkbox-option />
												<label for="<?php echo $option->value;?>"><?php echo JText::_($option->title);?></label>
											</div>
										</div>
										<?php $i++; ?>
									<?php } ?>
								<?php } ?>
							</div>
						</div>
					</div>
					<input class="o-form-control" type="hidden" value="<?php echo $field->unique_key;?>|<?php echo $field->element;?>" name="criterias[]" data-criterias />
					<input class="o-form-control" type="hidden" value="" name="datakeys[]" data-datakeys />
					<input class="o-form-control" type="hidden" value="<?php echo $filterMode; ?>" name="operators[]" data-operators />
					<input class="o-form-control" type="hidden" value="<?php echo $field->data;?>" name="conditions[]" data-condition />
				</div>
			<?php } ?>
		</div>

		<div class="t-lg-mt--lg">
			<button class="btn btn-es-primary btn-sm" type="submit" data-submit-button>
				<?php echo JText::_('Search');?>
			</button>
		</div>

		<?php echo $lib->html('form.token'); ?>
		<?php echo $lib->html('form.itemid'); ?>
		<input type="hidden" name="option" value="com_easysocial" />
		<input type="hidden" name="view" value="search" />
		<input type="hidden" name="layout" value="advanced" />
		<input type="hidden" name="type" value="<?php echo $type; ?>" />

		<?php if ($type == 'user') { ?>
			<input type="hidden" name="profile" value="<?php echo $params->get('profile_id', 0); ?>" />
		<?php } else { ?>
			<input type="hidden" name="clusterCategory" value="<?php echo $params->get($type . '_category', 0); ?>" />
		<?php } ?>


	</form>
</div>

