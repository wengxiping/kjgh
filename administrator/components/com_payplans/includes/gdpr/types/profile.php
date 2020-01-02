<?php
/**
* @package		Payplans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PayplansGdprProfile extends PayplansGdprAbstract
{
	public $type = 'profile';
	public $tab = null;

	/**
	 * Process user profile data downloads in accordance to GDPR rules
	 *
	 * @since	3.7
	 * @access	public
	 */
	public function execute(PayplansGdprSection &$section)
	{
		$this->tab = $section->createTab($this);

		$profile = PP::user($this->userId);

		// Nothing else to process, finalize it now.
		if (!$profile) {
			return $this->tab->finalize();
		}

		$item = $this->getTemplate($profile->getId(), $this->type);

		$item->view = false;
		$item->title = '';
		$item->created = $profile->getRegisterDate();
		$item->intro = $this->getIntro($profile);
		$this->tab->addItem($item);
		
	}

	/**
	 * Display the intro content on the first page 
	 *
	 * @since  2.2
	 * @access public
	 */
	public function getIntro($profile)
	{
		$subscriptions = $profile->getSubscriptions(PP_SUBSCRIPTION_ACTIVE);
		$preferences = $profile->getPreferences();

		ob_start();
		?>

		<table class="gdpr-table" style="width:520px;">
			<thead>
			   <th colspan="2" style="float:left;">
					<?php echo JText::_('COM_PAYPLANS_GDPR_PROFILE_TAB_TITLE_BASIC_INFORMATION');?>
			   </th>
			</thead>
			<tbody>
				<tr>
					<td width="180"><?php echo JText::_('COM_PAYPLANS_GDPR_PROFILE_TAB_NAME') . ' : ';?></td>
					<td style="text-align:left;"><?php echo $profile->getName(); ?></td>
				</tr>

				<tr>
					<td width="180"><?php echo JText::_('COM_PAYPLANS_GDPR_PROFILE_TAB_EMAIL') . ' : ';?></td>
					<td style="text-align:left;"><?php echo $profile->getEmail(); ?></td>
				</tr>

				<tr>
					<td width="180"><?php echo JText::_('COM_PAYPLANS_GDPR_PROFILE_TAB_REGISTER_DATE') . ' : ';?></td>
					<td style="text-align:left;"><?php echo $profile->getRegisterDate(); ?></td>
				</tr>

				<tr>
					<td width="180"><?php echo JText::_('COM_PAYPLANS_GDPR_PROFILE_TAB_COUNTRY') . ' : ';?></td>
					<td style="text-align:left;">
						<?php echo $profile->getCountryLabel(); ?>
					</td>
				</tr>

				<tr>
					<td width="180"><?php echo JText::_('COM_PAYPLANS_GDPR_PROFILE_TAB_ACTIVE_SUBSCRIPTION') . ' : ';?></td>
					<td style="text-align:left;"><?php echo count($subscriptions); ?></td>
				</tr>

			</tbody>
		</table>


		<table class="gdpr-table" style="width:520px;">
			<thead>
			   <th colspan="2" style="float:left;">
					<?php echo JText::_('COM_PAYPLANS_GDPR_PROFILE_TAB_TITLE_USER_PREFERENCES');?>
			   </th>
			</thead>
			<tbody>
				<tr>
					<td width="180"><?php echo JText::_('COM_PAYPLANS_GDPR_PROFILE_TAB_BUSINESS_PURPOSE') . ' : ';?></td>
					<td style="text-align:left;"><?php echo ($preferences->get('business_purpose') == 1) ? "Personal" : "Business" ; ?></td>
				</tr>

				<tr>
					<td width="180"><?php echo JText::_('COM_PAYPLANS_GDPR_PROFILE_TAB_BUSINESS_NAME') . ' : ';?></td>
					<td style="text-align:left;"><?php echo $preferences->get('business_name'); ?></td>
				</tr>

				<tr>
					<td width="180"><?php echo JText::_('COM_PAYPLANS_GDPR_PROFILE_TAB_TIN_NUMBER') . ' : ';?></td>
					<td style="text-align:left;"><?php echo $preferences->get('tin'); ?></td>
				</tr>

			</tbody>
		</table>

		<?php 	$userParams = $profile->getParams();
				$userParams = $userParams->toArray();
				if (!empty($userParams)) { ?>
					<table class="gdpr-table" style="width:520px;">
						<thead>
						   <th colspan="2" style="float:left;">
								<?php echo JText::_('COM_PAYPLANS_GDPR_PROFILE_TAB_TITLE_USER_DETAILS');?>
						   </th>
						</thead>
						<tbody>
							<?php foreach ($userParams as $key => $value) { ?>
								<tr>
									<td width="180"><?php echo $key;?></td>
									<td style="text-align:left;"><?php echo $value; ?></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
		<?php } ?>

		<?php
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}
}	