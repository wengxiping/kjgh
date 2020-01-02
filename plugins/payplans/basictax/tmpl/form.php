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
<div class="o-card o-card--borderless t-lg-mt--lg">
	<div class="o-card__header o-card__header--nobg t-lg-pl--no"><?php echo JText::_('COM_PP_APP_BASICTAX_TITLE');?></div>

	<div class="o-card__body">
		<p><?php echo JText::_('COM_PP_APP_BASICTAX_TITLE_DESC'); ?></p>

		<div class="o-form-group t-lg-mt--xl" data-pp-basictax-wrapper>
			<div class="o-input-group">
				<?php echo $this->html('form.country', 'app_basictax_country_id', $country, 'app_basictax_country_id', array('data-pp-basictax-country' => '')); ?>
			</div>

			<div class="t-text--danger" data-pp-basictax-message></div>
		</div>
	</div>
</div>
