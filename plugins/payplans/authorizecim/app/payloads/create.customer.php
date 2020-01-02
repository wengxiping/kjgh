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
<createCustomerProfileRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">

	<merchantAuthentication>
		<name><?php echo $data->loginId;?></name>
		<transactionKey><?php echo $data->transactionKey;?></transactionKey>
	</merchantAuthentication>

	<refId><?php echo $data->paymentKey;?></refId>

	<profile>
		<description><?php echo $data->title;?></description>
		<email><?php echo $data->post['email'];?></email>

		<paymentProfiles>
			<customerType>individual</customerType>
			<billTo>
				<firstName><?php echo $data->post['first_name']; ?></firstName>
				<lastName><?php echo $data->post['last_name'];?></lastName>
				<company><?php echo PP::normalize($data->post, 'company', '');?></company>
				<address><?php echo $data->post['address'];?></address>
				<city><?php echo $data->post['city'];?></city>
				<state><?php echo $data->post['state'];?></state>
				<zip><?php echo $data->post['zip'];?></zip>
				<country><?php echo $data->post['country'];?></country>
				<phoneNumber><?php echo $data->post['mobile'];?></phoneNumber>
			</billTo>
			<payment>
				<creditCard>
					<cardNumber><?php echo $data->post['card_num'];?></cardNumber>
					<expirationDate><?php echo $data->expiration;?></expirationDate>
				</creditCard>
			</payment>
		</paymentProfiles>
	</profile>
</createCustomerProfileRequest>