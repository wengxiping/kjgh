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
<?php echo "<?";?>xml version="1.0" encoding="utf-8"<?php echo "?>";?>
<deleteCustomerPaymentProfileRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">

	<merchantAuthentication>
		<name><?php echo $data->loginId;?></name>
		<transactionKey><?php echo $data->transactionKey;?></transactionKey>
	</merchantAuthentication>

	<customerProfileId><?php echo $data->customerProfileId;?></customerProfileId>
	<customerPaymentProfileId><?php echo $data->customerPaymentId;?></customerPaymentProfileId>
</deleteCustomerPaymentProfileRequest>