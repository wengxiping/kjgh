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
<div class="panel">
	<?php echo $this->html('panel.heading', 'COM_PP_APP_BASICTAX_TITLE', 'COM_PP_APP_BASICTAX_TITLE_DESC'); ?>

	<div class="panel-body">
		<div class="o-form-group">
			<?php echo $this->html('form.label', 'COM_PP_COUNTRY'); ?>

			<div class="o-control-input">
				<div class="o-input-group">
					<?php echo $this->html('form.country', 'app_basictax_country_id', $country, 'app_basictax_country_id', array('data-pp-basictax-country' => '')); ?>
				</div>
			</div>
		</div>
	</div>
</div>
