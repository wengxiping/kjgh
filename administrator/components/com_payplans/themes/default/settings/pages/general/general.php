<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="row">
	<div class="col-lg-6">

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_CONFIG_GENERAL_FEATURES'); ?>
	
			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'accessLoginBlock', 'COM_PAYPLANS_CONFIG_ACCESS_BLOCK_NON_SUBSCRIBERS'); ?>
				<?php echo $this->html('settings.toggle', 'microsubscription', 'COM_PAYPLANS_CONFIG_MICROSUBSCRIPTION'); ?>
			</div>
		</div>

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_CONFIG_MENU_ACCESS_CONFIGURATION'); ?>
	
			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'show404error', 'COM_PAYPLANS_CONFIG_MENU_ACCESS_SHOW404ERROR'); ?>
				<?php echo $this->html('settings.toggle', 'showOrhide', 'COM_PAYPLANS_CONFIG_MENU_ACCESS_SHOW_OR_HIDE_MENU'); ?>
			</div>
		</div>
	</div>

	<div class="col-lg-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PAYPLANS_CONFIG_LOCALIZATION'); ?>
			
			<div class="panel-body">
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PAYPLANS_CONFIG_CURRENCY_LABEL'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.currency', 'currency', $this->config->get('currency', 'USD')); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_GENERAL_LOCALIZATION_CURRENCY_AS'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.lists', 'show_currency_as', $this->config->get('show_currency_as', 'fullname'), '', '', array(
								array('title' => 'COM_PAYPLANS_CONFIG_SHOW_CURRENCY_AS_FULLNAME', 'value' => 'fullname'),
								array('title' => 'COM_PAYPLANS_CONFIG_SHOW_CURRENCY_AS_ISOCODE', 'value' => 'isocode'),
								array('title' => 'COM_PAYPLANS_CONFIG_SHOW_CURRENCY_AS_SYMBOL', 'value' => 'symbol'),
							)); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_GENERAL_LOCALIZATION_CURRENCY_LOCATION'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.lists', 'show_currency_at', $this->config->get('show_currency_at', 'before'), '', '', array(
								array('title' => 'COM_PAYPLANS_CONFIG_SHOW_CURRENCY_AS_BEFORE', 'value' => 'before'),
								array('title' => 'COM_PAYPLANS_CONFIG_SHOW_CURRENCY_AT_AFTER', 'value' => 'after')
							)); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_GENERAL_LOCALIZATION_DECIMAL_SEPARATOR'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.lists', 'price_decimal_separator', $this->config->get('price_decimal_separator', '.'), '', '', array(
								array('title' => 'COM_PAYPLANS_CONFIG_AMOUNT_DECIMAL_SEPARATOR_DOT', 'value' => '.'),
								array('title' => 'COM_PAYPLANS_CONFIG_AMOUNT_DECIMAL_SEPARATOR_COMMA', 'value' => ',')
							)); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PAYPLANS_CONFIG_FRACTION_DIGIT_COUNT'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.lists', 'fractionDigitCount', $this->config->get('fractionDigitCount', 2), '', '', array(
								array('title' => 'COM_PAYPLANS_CONFIG_FRACTION_DIGIT_COUNT_ZERO', 'value' => 0),
								array('title' => 'COM_PAYPLANS_CONFIG_FRACTION_DIGIT_COUNT_ONE', 'value' => 1),
								array('title' => 'COM_PAYPLANS_CONFIG_FRACTION_DIGIT_COUNT_TWO', 'value' => 2),
								array('title' => 'COM_PAYPLANS_CONFIG_FRACTION_DIGIT_COUNT_THREE', 'value' => 3),
								array('title' => 'COM_PAYPLANS_CONFIG_FRACTION_DIGIT_COUNT_FOUR', 'value' => 4),
								array('title' => 'COM_PAYPLANS_CONFIG_FRACTION_DIGIT_COUNT_FIVE', 'value' => 5)
							)); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PAYPLANS_DATE_FORMAT_LABEL'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.lists', 'date_format', $this->config->get('date_format'), '', '', array(
								array('title' => '%Y-%m-%d', 'value' => '%Y-%m-%d'),
								array("title" => "%m/%d/%Y", "value" => "%m/%d/%Y"),
								array("title" => "%m-%d-%Y", "value" => "%m-%d-%Y"),
								array("title" => "%d/%m/%Y", "value" => "%d/%m/%Y"),
								array("title" => "%d-%m-%Y", "value" => "%d-%m-%Y"),
								array("title" => "%d %B %y", "value" => "%d %B %y"),
								array("title" => "%d %B %Y", "value" => "%d %B %Y"),
								array("title" => "%B %d, %y", "value" => "%B %d, %y"),
								array("title" => "%B %d, %Y", "value" => "%B %d, %Y")
							)); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
