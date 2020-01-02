<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	12 March 2012
 * @file name	:	helpers/jblance.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

jimport('joomla.filesystem.file');
require_once (JPATH_ROOT.'/components/com_jblance/defines.jblance.php');
/**
 * Jblance helper.
 */

function jbimport($path){
	require_once(JPATH_ADMINISTRATOR.'/components/com_jblance/helpers/'.str_replace( '.', '/', $path).'.php');
}

class JblanceHelper {

	public static function get($path){
		list($group, $class) = explode('.', $path);
		include_once(JPATH_ADMINISTRATOR.'/components/com_jblance/helpers/'.$class.'.php');
		$className = ucfirst($class).ucfirst($group);
		$className = ($className == 'FieldsHelper') ? 'JBFieldsHelper' : $className;
		if(!class_exists($className)) return null;
		return new $className();
	}

	/**
	 * Loads a class from specified directories.
	 *
	 * @param   string  $path   The path to look for (dot notation).
	 */
	public static function import($path){
		list($group, $class) = explode('.', $path);
		$className = ucfirst($class).ucfirst($group);
		JLoader::register($className, JPATH_ADMINISTRATOR.'/components/com_jblance/helpers/'.$class.'.php');
	}

	public static function hasJBProfile($user_id){

		self::import('helper.user');
		$jbuser = new UserHelper();
		return $jbuser->hasJbprofile($user_id);

/* 		$db = JFactory::getDbo();

		//double check if user id > 0
		if($userid > 0){
			$query = "SELECT u.id FROM #__jblance_user u ".
					 "WHERE u.user_id = ".$db->quote($userid);
			$db->setQuery($query);

			if($db->loadResult())
				return 1;
			else
				return 0;
			}
		else {
			return 0;
		} */
	}

	/**
	 * Get a configuration object
	 *
	 * @return  Registry
	 */
	public static function getConfig(){

		self::import('helper.config');
		$config = ConfigHelper::getInstance();

		return $config;
	}

	public static function getLogo($userid, $att = '', $folder = ''){
		$avatars = self::getAvatarIntegration();
		return $avatars->getLink($userid, $att, $folder);
	}

	public static function getLogoUrl($userid, $folder){
		$avatars = self::getAvatarIntegration();
		return $avatars->getLinkOnly($userid, $folder);
	}

	//return true if the user group id is set to free mode.
	public static function isFreeMode($ugid){
		$db	= JFactory::getDbo();
		$query = "SELECT freeMode FROM #__jblance_usergroup WHERE id=".$db->quote($ugid);
		$db->setQuery($query);
		$freeMode = $db->loadResult();
		return $freeMode;
	}

	public static function getGwayName($gwCode){
		if($gwCode != 'byadmin'){
			$db = JFactory::getDbo();
			$query = "SELECT gateway_name FROM #__jblance_paymode WHERE gwcode=".$db->quote($gwCode);
			$db->setQuery($query);
			$gwayName = $db->loadResult();
		}
		else
			$gwayName = 'By Admin';

		return $gwayName;
	}

	public static function getPaymodeInfo($gwCode){
		if($gwCode != 'byadmin'){
			$db = JFactory::getDbo();
			$query = "SELECT * FROM #__jblance_paymode WHERE gwcode=".$db->quote($gwCode);
			$db->setQuery($query);
			$config = $db->loadObject();

			//convert the params to object
			$registry = new JRegistry;
			$registry->loadString($config->params);
			$params = $registry->toObject();

			//bind the $params object to $plan and make one object
			foreach($params as $k => $v){
				$config->$k = $v;
			}

			return $config;
		}
		else
			return 'By Admin';

	}

	/**
	 * Update the transaction of the users to the transaction table (#__jblance_transaction)
	 * @param int $userid
	 * @param string $transDtl
	 * @param int $amount
	 * @param int $plusMinus
	 */
	public static function updateTransaction($userid, $transDtl, $amount, $plusMinus){
		$app = JFactory::getApplication();
		$now = JFactory::getDate();
		//Insert the transaction into the transaction table in case the amount is greater than zero
		if($amount > 0){
			$row_trans	= JTable::getInstance('transaction', 'Table');
			$row_trans->date_trans  = $now->toSql();
			$row_trans->transaction = $transDtl;
			$row_trans->user_id  = $userid;

			if($plusMinus == 1)
				$row_trans->fund_plus = $amount;
			elseif($plusMinus == -1)
				$row_trans->fund_minus = $amount;

			// pre-save checks
			if(!$row_trans->check()) {
				JError::raiseError(500, $row_trans->getError());
			}
			if(!$row_trans->store()){
				JError::raiseError(500, $row_trans->getError());
			}
			$row_trans->checkin();
			return $row_trans;
		}
	}

	public static function getPaymentStatus($approved){
		$lang = JFactory::getLanguage();
		$lang->load('com_jblance', JPATH_SITE);

		if($approved == 0)
			$status = '<span class="label label-warning">'.JText::_('COM_JBLANCE_PAYMENT_PENDING').'</span>';
		elseif($approved == 1)
			$status = '<span class="label label-success">'.JText::_('COM_JBLANCE_COMPLETED').'</span>';
		elseif($approved == 2)
			$status = '<span class="label label-important">'.JText::_('COM_JBLANCE_CANCELLED').'</span>';
		elseif($approved == 3)
			$status = '<span class="label">'.JText::_('COM_JBLANCE_EXPIRED').'</span>';
		return $status;
	}

	public static function getEscrowPaymentStatus($status){
		//$lang = JFactory::getLanguage();
		//$lang->load('com_jblance', JPATH_SITE);
		$html = '';
		if($status == '' || empty($status))
			$html = '<span class="label label-warning">'.JText::_('COM_JBLANCE_PENDING').'</span>';
		elseif($status == 'COM_JBLANCE_RELEASED')
			$html = '<span class="label label-info">'.JText::_($status).'</span>';
		elseif($status == 'COM_JBLANCE_ACCEPTED')
			$html = '<span class="label label-success">'.JText::_($status).'</span>';
		elseif($status == 'COM_JBLANCE_CANCELLED')
			$html = '<span class="label label-important">'.JText::_($status).'</span>';
		return $html;
	}

	public static function getApproveStatus($approved){
		$lang = JFactory::getLanguage();
		$lang->load('com_jblance', JPATH_SITE);

		if($approved == 0)
			$status = '<span class="label label-warning">'.JText::_('COM_JBLANCE_PENDING').'</span>';
		elseif($approved == 1)
			$status = '<span class="label label-success">'.JText::_('COM_JBLANCE_APPROVED').'</span>';
		return $status;
	}

	//get the total available fund of the user
	public static function getTotalFund($userid){
		$db	= JFactory::getDbo();
		$total_fund = $total_withdraw = 0;
		$query = "SELECT (SUM(fund_plus)-SUM(fund_minus)) FROM #__jblance_transaction WHERE user_id = ".$db->quote($userid);
		$db->setQuery($query);
		$total_fund = $db->loadResult();
		$total_fund = empty($total_fund) ? 0 : $total_fund;

		//check if the user has withdraw request. If any, reduce the amount from the total fund so that the request fund is locked
		$query = "SELECT SUM(amount) FROM #__jblance_withdraw WHERE approved=0 AND user_id = ".$db->quote($userid);
		$db->setQuery($query);
		$total_withdraw = $db->loadResult();
		$total_withdraw = empty($total_withdraw) ? 0 : $total_withdraw;

		$total_available_fund = $total_fund - $total_withdraw;

		return $total_available_fund;
	}

	//check withdraw request for a user
	public static function getWithdrawRequest($userId){
		$db	= JFactory::getDbo();
		$query = "SELECT SUM(amount) FROM #__jblance_withdraw WHERE approved=0 AND user_id = ".$db->quote($userId);
		$db->setQuery($query);
		$total_withdraw = $db->loadResult();
		$total_withdraw = empty($total_withdraw) ? 0 : $total_withdraw;
		return $total_withdraw;
	}

	public static function isAuthenticated($userid, $layout){
		$app = JFactory::getApplication();
		$config = JblanceHelper::getConfig();
		$guestReporting = $config->enableGuestReporting;
		$profilePublic = $config->profilePublic;

		$noLoginLayouts = array('planadd', 'check_out', 'bank_transfer', 'listproject', 'detailproject', 'searchproject', 'userlist', 'viewservice', 'listservice');	//these are the layouts that doesn't require login

		//if the guest reporting is enabled, then set the report layout to nologin layouts
		if($guestReporting)
			$noLoginLayouts[] = 'report';

		//if profile is set to visible to public, then add it to no login layout
		if($profilePublic){
			$noLoginLayouts[] = 'viewprofile';
			$noLoginLayouts[] = 'viewportfolio';
		}

		if(in_array($layout, $noLoginLayouts)){
			return true;
		}

		//if the user is not logged in
		if($userid == 0){
			//return to same page after login
			$returnUrl = JUri::getInstance()->toString();
			$msg = JText::_('COM_JBLANCE_MUST_BE_LOGGED_IN_TO_ACCESS_THIS_PAGE');
			$link_login  = JRoute::_('index.php?option=com_users&view=login&return='.base64_encode($returnUrl), false);
			$app->enqueueMessage($msg, 'warning');
			$app->redirect($link_login);
		}
		if(self::hasJBProfile($userid)){
			//check if the user is authorized to do an action/section
			$isAuthorized = self::isAuthorized($userid, $layout);
			if(!$isAuthorized){
				$msg = JText::_('COM_JBLANCE_NOT_AUTHORIZED_TO_ACCESS_THIS_PAGE');
				$return	= JRoute::_('index.php?option=com_jblance&view=user&layout=dashboard', false);
				$app->enqueueMessage($msg, 'warning');
				$app->redirect($return);
			}
		}
		else {
			$msg = JText::_('COM_JBLANCE_NOT_AUTHORIZED_TO_ACCESS_THIS_PAGE_CHOOSE_YOUR_ROLE');
			$return	= JRoute::_('index.php?option=com_jblance&view=guest&layout=showfront', false);
			$app->enqueueMessage($msg, 'warning');
			$app->redirect($return);
		}

	}

	public static function isAuthorized($userid, $layout){
		$jbuser = self::get('helper.user');
		$ugInfo = $jbuser->getUserGroupInfo($userid, null);

		//get the array of layouts the current user is not authorized
		$denied = self::deniedLayouts($userid);

		if(in_array($layout, $denied))
			return 0;	//denied
		else
			return 1;	//allowed
	}

	public static function deniedLayouts($userid){
		$jbuser 				= self::get('helper.user');
		$ugInfo 				= $jbuser->getUserGroupInfo($userid, null);
		$config 				= JblanceHelper::getConfig();
		$enableEscrowPayment 	= $config->enableEscrowPayment;
 		$enableWithdrawFund 	= $config->enableWithdrawFund;
		$deniedLayouts 			= array();

		//check if escrow payment is enabled
		if(!$enableEscrowPayment){
			$deniedLayouts[] = 'escrow';
		}
		//check if fund withdraw is enabled
		if(!$enableWithdrawFund){
			$deniedLayouts[] = 'withdrawfund';
		}

		//for guests, none of the layouts are denied
		if($ugInfo == null)
			return $deniedLayouts;

		//get the array of layouts the current user is not authorized
		if(!$ugInfo->allowPostProjects){			//following layouts are denied for freelancers
			$deniedLayouts[] = 'showmyproject';
			$deniedLayouts[] = 'editproject';
			$deniedLayouts[] = 'pickuser';
			$deniedLayouts[] = 'servicebought';
		}
		if(!$ugInfo->allowBidProjects){				//following layouts are denied for buyers
			$deniedLayouts[] = 'showmybid';
			$deniedLayouts[] = 'placebid';
			$deniedLayouts[] = 'myservice';
			$deniedLayouts[] = 'editservice';
			$deniedLayouts[] = 'servicesold';
		}
		if(!$ugInfo->allowAddPortfolio){
			$deniedLayouts[] = 'editportfolio';
		}

		return $deniedLayouts;
	}

	public static function getUserType($user_id){
		self::import('helper.user');
		$jbuser = new UserHelper();
		$user_type = $jbuser->getUserType($user_id);

		return $user_type;

/*

		$db 				   = JFactory::getDbo();
		$user 				   = JFactory::getUser($user_id);
		$userType 			   = new stdClass();
		$userType->buyer 	   = false;
		$userType->freelancer  = false;
		$userType->guest 	   = false;
		$userType->joomlauser  = false;		//this means the user is only a Joomla user and doesn't have JoomBri Profile
		$userType->joombriuser = false;		//this means the user has JoomBri Profile

		if($user->guest){
			$userType->guest = true;
			return $userType;
		}
		else {
			if(!self::hasJBProfile($user_id)){
				$userType->joomlauser = true;
				return $userType;
			}
		}

		$query = "SELECT ug.id,ug.name,ug.approval,ug.params FROM #__jblance_user u ".
				 "LEFT JOIN #__jblance_usergroup ug ON u.ug_id = ug.id ".
				 "WHERE u.user_id = ".$db->quote($user_id)." AND ug.published=1";//echo $query;
		$db->setQuery($query);
		$userGroup = $db->loadObject();

		//convert the params to object
		$registry = new JRegistry;
		$registry->loadString($userGroup->params);
		$params = $registry->toObject();

		if($params->allowPostProjects)
			$userType->buyer = true;

		if($params->allowBidProjects)
			$userType->freelancer = true;

		if($params->allowPostProjects || $params->allowBidProjects)
			$userType->joombriuser = true;

		return $userType;	 */
	}

	/**
	 * Returns category names for the IDs passed as argument
	 *
	 * @param string $id_categs Comma separated categories IDs.
	 * @param string $renderType Deciding factor how the category names are returned; possible values - basic, tags-only and tags-link
	 * @param string $type This value decides the category links; possible values - project, user and service
	 * @return string Formatted category names.
	 */
	public static function getCategoryNames($id_categs, $renderType = 'basic', $type = ''){
		$db = JFactory::getDbo();
		$id_categs = JblanceHelper::sanitizeCsvString($id_categs);	//sanitize the comma separted string
		$query = "SELECT category,id FROM #__jblance_category c WHERE c.id IN (".$id_categs.")";
		$db->setQuery($query);

		if($renderType == 'basic'){
			$cats = $db->loadColumn();
			return implode(", ", $cats);
		}
		elseif($renderType == 'tags-only'){

		}
		elseif($renderType == 'tags-link'){
			$html = '<div class="category-wrapper dis-inl-blk">';
			$cats = $db->loadObjectList();

			foreach($cats as $cat){
				if($type == 'service'){
					$link = JRoute::_('index.php?option=com_jblance&view=service&layout=listservice&id_categ='.$cat->id);
				}
				elseif($type == 'project'){
					$link = JRoute::_('index.php?option=com_jblance&view=project&layout=searchproject&id_categ='.$cat->id);
				}
				elseif($type == 'user'){
					$link = JRoute::_('index.php?option=com_jblance&view=user&layout=userlist&id_category='.$cat->id);
				}
				$html .= "<a href='".$link."'><span class='label label-info'>".$cat->category."</span></a>";
			}
			$html .= "</div>";
			return $html;
		}
	}

	public static function getLocationNames($id_loc, $renderType = 'flag-location', $separator = ' &raquo; '){
		$db = JFactory::getDbo();
		$query = "SELECT parent.title,parent.alias FROM #__jblance_location AS node ".
				 "LEFT JOIN #__jblance_location AS parent ON node.lft BETWEEN parent.lft AND parent.rgt ".
				 "WHERE node.id = ".$db->quote($id_loc)." AND parent.extension='' ".
				 "ORDER BY node.lft";
		$db->setQuery($query);

		$locs = $db->loadColumn();

		if($locs){
			$flag = self::getCountryFlag($db->loadObjectList());

			if($renderType == 'only-location'){
				return implode($locs, $separator);
			}
			elseif($renderType == 'only-flag'){
				return $flag;
			}
			elseif($renderType == 'flag-location'){
				return $flag.implode($locs, $separator);
			}
			else{
                array_splice($locs,2);
                return implode($locs, $separator);
            }
		}
		else {
			if($renderType == 'only-flag') return '';

			return JText::_('COM_JBLANCE_NOT_MENTIONED');
		}

	}

	public static function getCountryFlag($locations){

		$doc = JFactory::getDocument();
		$doc->addStyleSheet(JURI::root()."components/com_jblance/css/flags.css");

		$location = $locations[0];
		//get the country css name from alias or from title.
		if(!empty($location->alias))
			$country = $location->alias;
		else {
			$country = preg_replace('/[[:space:]]/', '-',strtolower($location->title));		// replace space in the file name with - <img class="profile-location-flag small-flag brunei-darussalam" alt="Flag of Brunei Darussalam" title="Flag of Brunei Darussalam">
		}
		$flag = JHtml::_('image', '', '', array("class"=>"profile-location-flag small-flag ".$country, "title"=>"$location->title"));
		return $flag;
	}

	public static function getProjectDuration($id_duration){
		$db = JFactory::getDbo();
		$query = "SELECT * FROM #__jblance_duration d WHERE d.id=".$db->quote($id_duration);
		$db->setQuery($query);
		$duration = $db->loadObject();

		$format = self::formatProjectDuration($duration->duration_from, $duration->duration_from_type, $duration->duration_to, $duration->duration_to_type, $duration->less_great);
		return $format;
	}

	/**
	 * Method that returns the average rating of the user
	 * @param int $userid	id of the user
	 * @param boolean $html return html or not
	 * @return mixed
	 */
	public static function getAvarageRate($userid, $html = true){
		$db = JFactory::getDbo();
		$lang = JFactory::getLanguage();
		$lang->load('com_jblance', JPATH_SITE);

		//get the average rating value
		$query = "SELECT AVG((quality_clarity+communicate+expertise_payment+professional+hire_work_again)/5) AS rating FROM #__jblance_rating ".
				 "WHERE target=".$db->quote($userid)." AND quality_clarity <> 0";
		$db->setQuery($query);
		$avg = $db->loadResult();
		$avg = round($avg, 2);

		//get the no of rating the user has received
		$query = "SELECT COUNT(*) AS count FROM #__jblance_rating ".
				 "WHERE target=".$db->quote($userid)." AND quality_clarity <> 0";
		$db->setQuery($query);
		$count = $db->loadResult();

		if($html == false){
			return $avg;
		}
		else {
			JHtml::_('bootstrap.tooltip');
		?>
		<?php $tip = JHtml::tooltipText(JText::sprintf('COM_JBLANCE_RATING_VALUE_TOOLTIP', $avg)); ?>
		<span class="label label-warning" style="vertical-align: top;"><?php echo $avg; ?></span>
		<span class="rating_bar hasTooltip" title="<?php echo $tip; ?>">
			<span style="width:<?php echo $avg*10*2; ?>%"><!-- convert the rating into percent --></span>
		</span>
		<span class="small"><?php echo JText::sprintf('COM_JBLANCE_COUNT_REVIEWS', $count); ?></span>
		<?php
		return $avg;
		}
	}

	public static function getUserRateProject($userid, $projectid){
		$db = JFactory::getDbo();
		$query = "SELECT (quality_clarity+communicate+expertise_payment+professional+hire_work_again)/5 AS rating FROM #__jblance_rating ".
				 "WHERE target=".$db->quote($userid)." AND project_id = ".$db->quote($projectid);
		$db->setQuery($query);
		$rating = $db->loadResult();
		$rating = round($rating, 2);
		return $rating;
	}

	//svc is service order id
	public static function getUserRating($user_id, $svc_proj_id, $type){
		$db = JFactory::getDbo();
		$query = "SELECT (quality_clarity + communicate + expertise_payment + professional + hire_work_again) / 5 AS rating FROM #__jblance_rating ".
				 "WHERE target=".$db->quote($user_id)." AND project_id=".$db->quote($svc_proj_id)." AND type=".$db->quote($type);//echo $query;
		$db->setQuery($query);
		$rating = $db->loadResult();
		$rating = round($rating, 2);
		return $rating;
	}

	public static function getServiceRating($user_id, $service_id){
		$db = JFactory::getDbo();
		$query = "SELECT AVG((quality_clarity+communicate+expertise_payment+professional+hire_work_again)/5) AS rating FROM #__jblance_service s ".
				 "LEFT JOIN #__jblance_service_order so ON so.service_id = s.id ".
				 "LEFT JOIN #__jblance_rating r ON (r.project_id=so.id AND r.type='COM_JBLANCE_SERVICE') ".
				 "WHERE s.id=".$db->quote($service_id)." and r.target=".$db->quote($user_id);//echo $query;
		$db->setQuery($query);
		$rating = $db->loadResult();
		$rating = round($rating, 2);
		return $rating;
	}

	public static function getRatingHTML($rate, $tooltip=''){
		$rate = round($rate, 1);
		JHtml::_('bootstrap.tooltip');
	?>
		<span class="label label-warning" style="vertical-align: top;"><?php echo number_format($rate, 1); ?></span>
		<span class="rating_bar">
			<span style="width:<?php echo $rate*10*2; ?>%"></span>
		</span>
		<div class="clearfix"></div>
	<?php
	}

	//2.Which Plan to Use?
	public static function whichPlan($userid = null){
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$is_expired = false;

		$jbuser = self::get('helper.user');
		$ug_id = $jbuser->getUserGroupInfo($userid, null)->ug_id;

		$query = "SELECT MAX(id) FROM #__jblance_plan_subscr WHERE user_id = ".$db->quote($userid)." AND approved = 1";
		$db->setQuery($query);
		$id_max = $db->loadResult();

		if($id_max){
			//check if the plan is expired or not
			$query = "SELECT (TO_DAYS(s.date_expire) - TO_DAYS(NOW())) daysleft FROM  #__jblance_plan_subscr s WHERE s.id=".$db->quote($id_max);
			$db->setQuery($query);
			$days_left = $db->loadResult();

			if($days_left < 0)
				$is_expired = true;
		}

		if(!$id_max || $is_expired){	//user has no active plan or it is expired. so choose the default plan (free plan)
			$query = "SELECT * FROM #__jblance_plan WHERE default_plan=1 AND ug_id=".$db->quote($ug_id);
		}
		else {
			$query = "SELECT * FROM #__jblance_plan WHERE id = (
					 SELECT plan_id FROM #__jblance_plan_subscr WHERE id = ".$db->quote($id_max).")";
		}
		$db->setQuery($query);
		$plan = $db->loadObject();

		//convert the params to object
		$registry = new JRegistry;
		$registry->loadString($plan->params);
		$params = $registry->toObject();

		//bind the $params object to $plan and make one object
		foreach ($params as $k => $v){
			$plan->$k = $v;
		}

		return $plan;
	}

	public static function countUnreadMsg($msgid = 0){
		$db = JFactory::getDbo();
		$user	= JFactory::getUser();

		if($msgid > 0)
			$query = "SELECT COUNT(is_read) isRead FROM #__jblance_message WHERE idTo=".$db->quote($user->id)." AND (id=".$db->quote($msgid)." OR parent=".$db->quote($msgid).") AND is_read=0 AND deleted=0";
		else
			$query = "SELECT COUNT(is_read) isRead FROM #__jblance_message WHERE idTo=".$db->quote($user->id)." AND is_read=0 AND deleted=0";

		$db->setQuery($query);
		$total 	= $db->loadResult();
		return $total;
	}

	/**
	 * Get JoomBri profile integration object
	 *
	 * Returns the global {@link JoombriProfile} object, only creating it if it doesn't already exist.
	 *
	 * @return object JoombriProfile
	 */
	public static function getProfile(){
		jbimport('integration.profile');
		return JoombriProfile::getInstance();
	}

	/**
	 * Get Joombri avatar integration object
	 *
	 * Returns the global {@link JoombriAvatar} object, only creating it if it doesn't already exist.
	 *
	 * @return object JoombriAvatar
	 */
	public static function getAvatarIntegration(){
		jbimport('integration.avatar');
		return JoombriAvatar::getInstance();
	}

	/**
	 * Return the amount formatted with currency symbol and/or code
	 *
	 * @param float $amount Amount to be formatted
	 * @param boolean $setCurrencySymbol Prefix currency symbol
	 * @param boolean $setCurrencyCode Suffix currency code
	 * @param integer $decimal No of decimal points
	 * @return string Formatted currency
	 */
	public static function formatCurrency($amount, $setCurrencySymbol = true, $setCurrencyCode = false, $decimal = 2){

		$config 	= self::getConfig();
		$currencySym = $config->currencySymbol;
		$currencyCod = $config->currencyCode;
		$formatted = number_format(abs($amount), $decimal, '.', ',');

		if($setCurrencySymbol){
			if($amount >= 0)
				$formatted = $currencySym.''.$formatted;
			else
				$formatted = '-'.$currencySym.''.$formatted;
		}

		if($setCurrencyCode)
			$formatted .= ' '.$currencyCod;

		return $formatted;
	}

	public static function showRemainingDHM($endDate, $type = 'LONG', $runOutMsg){

		$now  = JFactory::getDate();
		$diff = self::dateTimeDiff($now, $endDate);

		if($now > $endDate)
			return JText::_($runOutMsg);

		if($diff->y > 0)
			$formatted = JText::sprintf('COM_JBLANCE_YEAR_MONTHS_'.$type, $diff->y, $diff->m);
		elseif($diff->m > 0)
			$formatted = JText::sprintf('COM_JBLANCE_MONTHS_DAYS_'.$type, $diff->m, $diff->d);
		elseif($diff->d > 0)
			$formatted = JText::sprintf('COM_JBLANCE_DAYS_HOURS_'.$type, $diff->d, $diff->h);
		elseif($diff->h > 0)
			$formatted = JText::sprintf('COM_JBLANCE_HOURS_MINUTES_'.$type, $diff->h, $diff->i);
		elseif($diff->i > 0)
			$formatted = JText::sprintf('COM_JBLANCE_MINUTES_SECS_'.$type, $diff->i, $diff->s);
		else
			$formatted = JText::sprintf('COM_JBLANCE_SECS_'.$type, $diff->s);

		return $formatted;
	}

	public static function showTimePastDHM($startDate, $type = 'LONG'){

		$now = JFactory::getDate();
		$diff = self::dateTimeDiff($now, $startDate);//print_r($diff);

		if($diff->y > 0)
			$formatted = JText::sprintf('COM_JBLANCE_YEAR_MONTHS_'.$type, $diff->y, $diff->m);
		elseif($diff->m > 0)
			$formatted = JText::sprintf('COM_JBLANCE_MONTHS_DAYS_'.$type, $diff->m, $diff->d);
		elseif($diff->d > 0)
			$formatted = JText::sprintf('COM_JBLANCE_DAYS_HOURS_'.$type, $diff->d, $diff->h);
		elseif($diff->h > 0)
			$formatted = JText::sprintf('COM_JBLANCE_HOURS_MINUTES_'.$type, $diff->h, $diff->i);
		elseif($diff->i > 0)
			$formatted = JText::sprintf('COM_JBLANCE_MINUTES_SECS_'.$type, $diff->i, $diff->s);
		else
			$formatted = JText::sprintf('COM_JBLANCE_SECS_'.$type, $diff->s);

		$formatted .= ' '.JText::_('COM_JBLANCE_AGO');

		return $formatted;
	}

	/**
	 * @param date $fromdate
	 * @param date $toDate
	 * @return stdClass
	 */
	public static function dateTimeDiff($fromdate, $todate){

		$alt_diff = new stdClass();
		$alt_diff->y =  floor(abs($fromdate->format('U') - $todate->format('U')) / (60*60*24*365));
		$alt_diff->m =  floor((floor(abs($fromdate->format('U') - $todate->format('U')) / (60*60*24)) - ($alt_diff->y * 365))/30);
		$alt_diff->d =  floor(floor(abs($fromdate->format('U') - $todate->format('U')) / (60*60*24)) - ($alt_diff->y * 365) - ($alt_diff->m * 30));
		$alt_diff->h =  floor( floor(abs($fromdate->format('U') - $todate->format('U')) / (60*60)) - ($alt_diff->y * 365*24) - ($alt_diff->m * 30 * 24 )  - ($alt_diff->d * 24) );
		$alt_diff->i = floor( floor(abs($fromdate->format('U') - $todate->format('U')) / (60)) - ($alt_diff->y * 365*24*60) - ($alt_diff->m * 30 * 24 *60)  - ($alt_diff->d * 24 * 60) -  ($alt_diff->h * 60) );
		$alt_diff->s =  floor( floor(abs($fromdate->format('U') - $todate->format('U'))) - ($alt_diff->y * 365*24*60*60) - ($alt_diff->m * 30 * 24 *60*60)  - ($alt_diff->d * 24 * 60*60) -  ($alt_diff->h * 60*60) -  ($alt_diff->i * 60) );
		$alt_diff->invert =  (($fromdate->format('U') - $todate->format('U')) > 0)? 0 : 1 ;

		return $alt_diff;
	}

	public static function getFeeds($limit, $notify = '', $offset = 0){

		$user 	= JFactory::getUser();
		$feeds  = self::get('helper.feeds');		// create an instance of the class FeedsHelper

		$feeds =  $feeds->getFeedsData($user->id, $limit, $notify, $offset);
		return $feeds;
	}

	public static function parseTitle($title){
		if(JFactory::getConfig()->get('unicodeslugs') == 1){
			$output = JFilterOutput::stringURLUnicodeSlug($title);
		}
		else {
			$output = JFilterOutput::stringURLSafe($title);
		}
		return $output;
	}

	/**
	 * Identify whether the order is deposit or plan purchase
	 *
	 * @param string $invoice_num
	 * @return string
	 */
	public static function identifyDepositOrPlan($invoice_num){
		$db = JFactory::getDbo();
		$type = '';
		$return = array();

		// search for invoice number in plan subscription table
		$query = "SELECT id FROM #__jblance_plan_subscr p WHERE p.invoiceNo = ".$db->quote($invoice_num);
		$db->setQuery($query);
		$result = $db->loadResult();

		//if result is empty search for invoice number in deposit table
		if($result){
			$return['type'] = 'plan';
			$return['id'] = $result;
		}
		else {
			$query = "SELECT id FROM #__jblance_deposit d WHERE d.invoiceNo = ".$db->quote($invoice_num);
			$db->setQuery($query);
			$result = $db->loadResult();
			if($result){
				$return['type'] = 'deposit';
				$return['id'] = $result;
			}
			else {
				$return = array();
			}
		}
		return $return;
	}

	public static function approveSubscription($subscrid){
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$row	= JTable::getInstance('plansubscr', 'Table');
		$row->load($subscrid);

		$query = "SELECT p.* FROM #__jblance_plan p WHERE p.id=".$db->quote($row->plan_id);
		$db->setQuery($query);
		$plan = $db->loadObject();

		// Update the transaction table if not approved
		if(!$row->approved){
			$transDtl = JText::_('COM_JBLANCE_BUY_SUBSCR').' - '.$plan->name;
			$row_trans = JblanceHelper::updateTransaction($row->user_id, $transDtl, $row->fund, 1);

			//save status subscription "approved"
			$now = JFactory::getDate();
			$date_approve = $now->toSql();
			$now->modify("+$plan->days $plan->days_type");
			$date_expires = $now->toSql();

			$row->approved = 1;
			$row->date_approval = $date_approve;
			$row->date_expire = $date_expires;
			$row->gateway_id = time();
			$row->trans_id = $row_trans->id;
			$row->access_count = 1;

			if(!$row->check())
				JError::raiseError(500, $row->getError());

			if(!$row->store())
				JError::raiseError(500, $row->getError());

			$row->checkin();

			$jbmail = JblanceHelper::get('helper.email');		// create an instance of the class EmailHelper
			$jbmail->alertAdminSubscr($row->id, $row->user_id);
			$jbmail->alertUserSubscr($row->id, $row->user_id);

			return $row;
		}
	}


	function approveFundDeposit($deposit_id){
		$row = JTable::getInstance('deposit', 'Table');
		$row->load($deposit_id);

		// Update the transaction table if not approved
		if(!$row->approved){
			$transDtl = JText::_('COM_JBLANCE_DEPOSIT_FUNDS');
			$row_trans = JblanceHelper::updateTransaction($row->user_id, $transDtl, $row->amount, 1);

			//save status billing "approved"
			$now = JFactory::getDate();
			$date_approve = $now->toSql();
			$row->approved = 1;
			$row->date_approval = $date_approve;
			$row->trans_id = $row_trans->id;

			if(!$row->check())
				JError::raiseError(500, $row->getError());

			if(!$row->store())
				JError::raiseError(500, $row->getError());

			$row->checkin();

			$jbmail = JblanceHelper::get('helper.email');		// create an instance of the class EmailHelper
			$jbmail->sendAdminDepositFund($row->id);

			return $row;
		}
	}

	//3.Status of member's plan
	public static function planStatus($userid = null){
		$app = JFactory::getApplication();
		$db  = JFactory::getDbo();
		$now = JFactory::getDate();

		$query = "SELECT MAX(id) FROM #__jblance_plan_subscr WHERE approved=1 AND user_id=".$db->quote($userid);
		$db->setQuery($query);
		$id_max = $db->loadResult();

		if(!$id_max)	//user has no active plan. so choose the default plan (free plan)
			return 2;

		$query = "SELECT * FROM #__jblance_plan_subscr WHERE id=".$db->quote($id_max);
		$db->setQuery($query);
		$last_subscr = $db->loadObject();

		$query = "SELECT * FROM #__jblance_plan WHERE id=".$db->quote($last_subscr->plan_id);
		$db->setQuery($query);
		$last_plan = $db->loadObject();

		if($now > $last_subscr->date_expire)
			return 1;	// The user's subscr has expired
		else
			return null;
	}

	public static function processMessage(){
		$app  	= JFactory::getApplication();
		$db 	= JFactory::getDbo();
		$msgid 	= $app->input->get('msgid', '', 'int');

		$query = "UPDATE #__jblance_message SET deleted=1 WHERE id=".$db->quote($msgid)." OR parent=".$db->quote($msgid);
		$db->setQuery($query);
		if($db->execute())
			echo 'OK';
		else
			echo 'NO';

		$app->close();
	}

	public static function getProgressBar($currStep=0){

		$totalStep = self::getTotalSteps();
		$width = intval(($currStep/$totalStep) * 100) ;

		$html = '<div class="progress progress-striped">'.
				'<div class="bar" style="width:'.$width.'%;"></div>'.
				'</div>';
		return $html;
	}

	public static function getTotalSteps(){
		$user = JFactory::getUser();
		$session 	= JFactory::getSession();
		$skipPlan 	= $session->get('skipPlan', 0, 'register');
		$total = 4;

		if($user->guest){
			if($skipPlan)
				$total -= 1;
		}
		else {
			if($skipPlan)
				$total -= 2;
			else
				$total -= 1;

		}
		return $total;
	}

	public static function formatProjectDuration($from, $fromType, $to, $toType, $less_great){
		if(empty($less_great)){
			return self::getDaysType($from, $fromType).' - '.self::getDaysType($to, $toType);
		}
		else {
			if($less_great == '<')
				return JText::_('COM_JBLANCE_LESS_THAN').' '.self::getDaysType($to, $toType);
			if($less_great == '>')
				return JText::_('COM_JBLANCE_OVER').' '.self::getDaysType($from, $fromType);
		}
	}

	public static function getDaysType($days, $daysType){
		if($daysType == 'days')
			$lang = JText::plural('COM_JBLANCE_N_DAYS', $days);
		elseif($daysType == 'weeks')
			$lang = JText::plural('COM_JBLANCE_N_WEEKS', $days);
		elseif($daysType == 'months')
			$lang = JText::plural('COM_JBLANCE_N_MONTHS', $days);
		elseif($daysType == 'years')
			$lang = JText::plural('COM_JBLANCE_N_YEARS', $days);
		return $lang;
	}

	public static function countUnapprovedMsg($msgid = 0){
		$db 	= JFactory::getDbo();
		$user	= JFactory::getUser();
		$query = '';

		if($msgid > 0)
			$query = "SELECT COUNT(approved) isApproved FROM #__jblance_message WHERE (id=".$db->quote($msgid)." OR parent=".$db->quote($msgid).") AND approved=0 AND deleted=0";
		else
			$query = "SELECT COUNT(approved) isApproved FROM #__jblance_message WHERE approved=0 AND deleted=0";

		$db->setQuery($query);//echo $query;
		$total 	= $db->loadResult();//echo $total;
		return $total;
	}

	public static function setJoomBriToken(){
		$doc = JFactory::getDocument();
		$addtoken = JSession::getFormToken();
		$addtokenjs = 'var JoomBriToken="'.$addtoken.'=1";';
		$doc->addScriptDeclaration($addtokenjs);
	}

	public static function cleanParams($params){

		$filter	= JFilterInput::getInstance();
		$clean = array();

		foreach($params as $key => $value){
			$clean[$key] = $filter->clean($value, 'string');
		}

		return $clean;
	}
	public static function cleanParams1($params){

		$filter	= JFilterInput::getInstance();
		$clean = array();

		foreach($params as $key => $value){
			$clean[$key] = $filter->clean($value, 'string');
		}

		return $clean;
	}

	/**
	 * This method sanitizes the comma separted string values.
	 *
	 * @param string $value String value to be sanitized
	 *
	 * @return  string  Sanitized comma separated string.
	 */
	public static function sanitizeCsvString($value){
		$arr = explode(",", $value);
		$arr = ArrayHelper::toInteger($arr);

		return implode(",", $arr);
	}

	public static function getHttpResponse($url, $referer = null, $_data = null, $method = 'post', $userAgent = null, $headers = null){

        // convert variables array to string:
        $data = '';
        if( is_array($_data) && count($_data) > 0 ) {
            // format --> test1=a&test2=b etc.
            $data = array();
            while( list($n, $v) = each($_data) ) {
                $data[] = "$n=$v";
            }
            $data = implode('&', $data);
            $contentType = "Content-type: application/x-www-form-urlencoded\r\n";
        }
        else {
            $data = $_data;
            $contentType = "Content-type: text/xml\r\n";
        }

        if( is_null($referer) ) {
            $referer = JURI::root();
        }

        // parse the given URL
        $url = parse_url($url);
        if( !isset($url['scheme']) ) {
            return false;
        }

        // extract host and path:
        $host = $url['host'];
        $path = isset($url['path']) ? $url['path'] : '/';

        // Prepare host and port to connect to
        $connhost = $host;
        $port = 80;

        // Workaround for some PHP versions, where fsockopen can't connect to
        // 'localhost' string on Windows servers
        if ($connhost == 'localhost') {
            $connhost = gethostbyname('localhost');
        }

        // Handle scheme
        if ($url['scheme'] == 'https') {
            $connhost = 'ssl://'.$connhost;
            $port = 443;
        }
        else if ($url['scheme'] != 'http') {
            return false;
        }

        // open a socket connection
        $errno = null;
        $errstr = null;
        $fp = @fsockopen($connhost, $port, $errno, $errstr, 5);
        if (!is_resource($fp) || ($fp === false)) {
            return false;
        }

        if (!is_null($userAgent)) {
            $userAgent = "User-Agent: ".$userAgent."\r\n";
        }

        // send the request
        if ($method == 'post') {
            // Check the first fputs, sometimes fsockopen doesn't fail, but fputs doesn't work
            if (!@fputs($fp, "POST $path HTTP/1.1\r\n")) {
                @fclose($fp);
                return false;
            }

            if (!is_null($userAgent)) {
                fputs($fp, $userAgent);
            }
            fputs($fp, "Host: $host\r\n");
            fputs($fp, "Referer: $referer\r\n");
            fputs($fp, $contentType);
            fputs($fp, "Content-length: ". strlen($data) ."\r\n");

            // Send additional headers if set
            if (is_array($headers)) {
                foreach ($headers as $h) {
                    $h = rtrim($h);
                    $h .= "\r\n";
                    fputs($fp, $h);
                }
            }

            fputs($fp, "Connection: close\r\n\r\n");
            fputs($fp, $data);
        }
        elseif ($method == 'get') {
            $query = '';
            if (isset($url['query'])) {
                $query = '?'.$url['query'];
            }
            // Check the first fputs, sometimes fsockopen doesn't fail, but fputs doesn't work
            if (!@fputs($fp, "GET {$path}{$query} HTTP/1.1\r\n")) {
                @fclose($fp);
                return false;
            }
            if (!is_null($userAgent)) {
                fputs($fp, $userAgent);
            }
            fputs($fp, "Host: $host\r\n");

            // Send additional headers if set
            if (is_array($headers)) {
                foreach ($headers as $h) {
                    $h = rtrim($h);
                    $h .= "\r\n";
                    fputs($fp, $h);
                }
            }

            fputs($fp, "Connection: close\r\n\r\n");
        }

        $result = '';
        while(!feof($fp)) {
            // receive the results of the request
            $result .= fgets($fp, 128);
        }

        // close the socket connection:
        fclose($fp);

        // split the result header from the content
        $result = explode("\r\n\r\n", $result, 2);

        $header = isset($result[0]) ? $result[0] : '';
        $content = isset($result[1]) ? $result[1] : '';

        $response = new stdClass();
        $response->header = $header;
        $response->content = $content;

        // Handle chunked transfer if needed
        if( strpos(strtolower($response->header), 'transfer-encoding: chunked') !== false ) {
            $parsed = '';
            $left = $response->content;

            while( true ) {
                $pos = strpos($left, "\r\n");
                if( $pos === false ) {
                    return $response;
                }

                $chunksize = substr($left, 0, $pos);
                $pos += strlen("\r\n");
                $left = substr($left, $pos);

                $pos = strpos($chunksize, ';');
                if( $pos !== false ) {
                    $chunksize = substr($chunksize, 0, $pos);
                }
                $chunksize = hexdec($chunksize);

                if( $chunksize == 0 ) {
                    break;
                }

                $parsed .= substr($left, 0, $chunksize);
                $left = substr($left, $chunksize + strlen("\r\n"));
            }

            $response->content = $parsed;
        }

        // Get the response code from header
        $headerLines = explode("\n", $response->header);
        $header1 = explode(' ', trim($headerLines[0]));
        $code = intval($header1[1]);
        $response->code = $code;

        return $response;

	}

	public static function checkFavorite($targetId, $type){
		$db 	= JFactory::getDbo();
		$user	= JFactory::getUser();
		$query = '';

		$query = "SELECT COUNT(*) FROM #__jblance_favourite ".
				 "WHERE actor=".$db->quote($user->id)." AND target=".$db->quote($targetId)." AND type=".$db->quote($type);
		$db->setQuery($query);
		$total 	= $db->loadResult();
		return $total;
	}

	public static function checkOwnershipOfOperation($id, $type){
		$user = JFactory::getUser();
		$db = JFactory::getDbo();

		if($type == 'project'){
			$project 	= JTable::getInstance('project', 'Table');
			$project->load($id);
			if($project->publisher_userid != $user->id)
				return false;
			else
				return true;
		}
		elseif($type == 'service'){
			$service = JTable::getInstance('service', 'Table');
			$service->load($id);
			if($service->user_id != $user->id)
				return false;
			else
				return true;
		}
		elseif($type == 'projectprogress' ){
			$query = "SELECT b.user_id assigned,p.publisher_userid publisher FROM #__jblance_bid b ".
					 "LEFT JOIN #__jblance_project p ON b.project_id=p.id ".
 				 	 "WHERE b.id = ".$db->quote($id);
 			$db->setQuery($query);
 			$bidInfo = $db->loadObject();
			if($bidInfo->assigned != $user->id && $bidInfo->publisher != $user->id)
				return false;
			else
				return true;
		}
		elseif($type == 'serviceprogress' || $type == 'servicerating'){
			$query = "SELECT s.user_id seller,so.user_id buyer FROM #__jblance_service_order so ".
					 "LEFT JOIN #__jblance_service s ON so.service_id=s.id ".
 				 	 "WHERE so.id = ".$db->quote($id);
 			$db->setQuery($query);
 			$orderInfo = $db->loadObject();
			if($orderInfo->seller != $user->id && $orderInfo->buyer != $user->id)
				return false;
			else
				return true;
		}
		elseif($type == 'projectrating'){
			$query = "SELECT p.publisher_userid,p.assigned_userid FROM #__jblance_project p ".
					 "WHERE p.id=".$db->quote($id);
			$db->setQuery($query);
			$projectInfo = $db->loadObject();
			if($projectInfo->assigned_userid != $user->id && $projectInfo->publisher_userid != $user->id)
				return false;
			else
				return true;
		}
	}

	public static function getLocation(){
		$app  			= JFactory::getApplication();
		$db				= JFactory::getDbo();
		$location_id 	= $app->input->get('location_id', 0, 'int');
		$cur_level 		= $app->input->get('cur_level', 0, 'int');
		$nxt_level 		= $app->input->get('nxt_level', 0, 'int');
		$task1	 		= $app->input->get('task_val', '', 'string');

		$query = "SELECT COUNT(*) FROM #__jblance_location WHERE parent_id=".$db->quote($location_id);
		$db->setQuery($query);
		$count = $db->loadResult();

		if($location_id > 0 && $count > 0){
			$query = "SELECT id AS value, title AS text FROM #__jblance_location ".
					 "WHERE published=1 AND parent_id=".$db->quote($location_id)." ".
					 "ORDER BY lft";
			$db->setQuery($query);
			$items = $db->loadObjectList();

			$types[] = JHtml::_('select.option', '', '- '.JText::_('COM_JBLANCE_PLEASE_SELECT').' -');
			foreach($items as $item){
				$types[] = JHtml::_('select.option', $item->value, JText::_($item->text));
			}

			$attribs = array('class' => 'input-medium required', 'data-level-id' => $nxt_level, 'onchange' => 'getLocation(this, \''.$task1.'\');', 'required' => 'required');

			$lists 	= JHtml::_('select.genericlist', $types, 'location_level[]', $attribs, 'value', 'text', '', 'level'.$nxt_level);
			echo $lists;
		}
		else {
			echo '0';
		}
		$app->close();
	}

	public static function getGoogleMap($lat, $long, $title){
		$doc = JFactory::getDocument();
		$config = JblanceHelper::getConfig();

		$gMapApiKey = JFilterInput::getInstance()->clean($config->gmapApikey);

		$doc->addScript("https://maps.googleapis.com/maps/api/js?key=$gMapApiKey");
		$doc->setMetaData('viewport', 'initial-scale=1.0, user-scalable=no');

		$script = "
			var map;
			function initialize() {
				var myLatlng = new google.maps.LatLng($lat, $long);
				var mapOptions = {
			    	zoom: 8,
			    	center: myLatlng
			  	};
				map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
			
				var marker = new google.maps.Marker({
		      		position: myLatlng,
					map: map,
					title: ''
		  		});
		  		
				var infowindow = new google.maps.InfoWindow({
					content: '".addslashes($title)."'
				});
				
				google.maps.event.addListener(marker, 'click', function() {
					infowindow.open(map,marker);
				});
			}
			
			google.maps.event.addDomListener(window, 'load', initialize);";

		$doc->addScriptDeclaration($script);

	}

	//check if the string is JSON and if not, convert it.
	public static function toJson($string){
		if(empty($string))
			return '';

			if(is_string($string) && is_array(json_decode($string, true))){
				return $string;	// this is json string
			}
			else {
				return json_encode(array('a' => $string.';0'));	// convert it and send
			}
	}


	/**
	 * Show the feature/unfeature links
	 *
	 * @param   int      $value      The state value
	 * @param   int      $i          Row number
	 * @param   boolean  $canChange  Is user allowed to change?
	 *
	 * @return  string       HTML code
	 */
	public static function boolean($value = 0, $i, $taskOn = null, $taskOff = null){
		JHtml::_('bootstrap.tooltip');

		$task = ($value) ? $taskOff : $taskOn;
		$toggle = (!$task) ? false : true;

		// Array of image, task, title, action
		$states	= array(
				0	=> array('unpublish', $taskOn, 'JNO', 'JGLOBAL_CLICK_TO_TOGGLE_STATE'),
				1	=> array('publish',	$taskOff, 'JYES', 'JGLOBAL_CLICK_TO_TOGGLE_STATE'),
		);
		$state	= ArrayHelper::getValue($states, (int)$value, $states[1]);
		$icon	= $state[0];

		if($toggle){
			$html	= '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="btn btn-micro hasTooltip' . ($value == 1 ? ' active' : '') . '" title="' . JHtml::tooltipText($state[3]) . '"><i class="icon-'. $icon . '"></i></a>';
		}
		else {
			$html	= '<a class="btn btn-micro hasTooltip disabled' . ($value == 1 ? ' active' : '') . '" title="' . JHtml::tooltipText($state[2]) . '"><i class="icon-'. $icon . '"></i></a>';
		}

		return $html;
	}

	/**
	 * Configure the Linkbar.
	 */
	public static function addSubmenu($vName = ''){

		JHtmlSidebar::addEntry(JText::_('COM_JBLANCE_TITLE_DASHBOARD'), 'index.php?option=com_jblance&view=admproject&layout=dashboard', $vName == 'dashboard');
		JHtmlSidebar::addEntry(JText::_('COM_JBLANCE_TITLE_PROJECTS'), 'index.php?option=com_jblance&view=admproject&layout=showproject', $vName == 'showproject');
		JHtmlSidebar::addEntry(JText::_('COM_JBLANCE_TITLE_SERVICES'), 'index.php?option=com_jblance&view=admproject&layout=showservice', $vName == 'showservice');
		JHtmlSidebar::addEntry(JText::_('COM_JBLANCE_TITLE_USERS'), 'index.php?option=com_jblance&view=admproject&layout=showuser', $vName == 'showuser');
		JHtmlSidebar::addEntry(JText::_('COM_JBLANCE_TITLE_SUBSCRIPTIONS'), 'index.php?option=com_jblance&view=admproject&layout=showsubscr', $vName == 'showsubscr');
		JHtmlSidebar::addEntry(JText::_('COM_JBLANCE_TITLE_DEPOSITS'), 'index.php?option=com_jblance&view=admproject&layout=showdeposit', $vName == 'showdeposit');
		JHtmlSidebar::addEntry(JText::_('COM_JBLANCE_TITLE_WITHDRAWALS'), 'index.php?option=com_jblance&view=admproject&layout=showwithdraw', $vName == 'showwithdraw');
		JHtmlSidebar::addEntry(JText::_('COM_JBLANCE_TITLE_ESCROWS'), 'index.php?option=com_jblance&view=admproject&layout=showescrow', $vName == 'showescrow');
		JHtmlSidebar::addEntry(JText::_('COM_JBLANCE_TITLE_REPORTINGS'), 'index.php?option=com_jblance&view=admproject&layout=showreporting', $vName == 'showreporting');
		JHtmlSidebar::addEntry(JText::_('COM_JBLANCE_TITLE_PRIVATE_MESSAGES'), 'index.php?option=com_jblance&view=admproject&layout=managemessage', $vName == 'managemessage');
		JHtmlSidebar::addEntry(JText::_('COM_JBLANCE_TITLE_CONFIGURATION'), 'index.php?option=com_jblance&view=admconfig&layout=configpanel', $vName == 'configpanel');
		JHtmlSidebar::addEntry(JText::_('COM_JBLANCE_TITLE_SUMMARY'), 'index.php?option=com_jblance&view=admproject&layout=showsummary', $vName == 'showsummary');
		JHtmlSidebar::addEntry(JText::_('COM_JBLANCE_TITLE_ABOUT'), 'index.php?option=com_jblance&view=admproject&layout=about', $vName == 'about');
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return	JObject
	 * @since	1.6
	 */
	public static function getActions(){
		$user	= JFactory::getUser();
		$result = new JObject;

		$assetName = 'com_jblance';

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}
}

class JBMediaHelper {

	public static function checkAndCreateFolder($type){
		jimport('joomla.filesystem.folder');

		$data = "<html>\n<body bgcolor=\"#FFFFFF\">\n</body>\n</html>";

		if($type == 'category'){
			$path = JBCATEGORY_PATH;
		}
		elseif($type == 'project'){
			$path = JBPROJECT_PATH;
		}
		elseif($type == 'portfolio'){
			$path = JBPORTFOLIO_PATH;
		}
		elseif($type == 'portfolio'){
			$path = JBPORTFOLIO_PATH;
		}
		elseif($type == 'service'){
			$path = JBSERVICE_PATH;
		}

		if(!JFolder::exists($path)){
			if(JFolder::create($path)){
				JFile::write($path."/index.html", $data);
			}
		}
		if(!JFolder::exists($path.'/thumb')){
			if(JFolder::create($path.'/thumb')){
				JFile::write($path."/thumb/index.html", $data);
			}
		}

/* 		if($type == 'project'){
			if(!JFolder::exists(JBPROJECT_PATH)){
				if(JFolder::create(JBPROJECT_PATH)){
					if(JFile::exists(JPATH_SITE.'/images/index.html')){
						JFile::copy(JPATH_SITE.'/images/index.html', JBPROJECT_PATH.'/index.html');
					}
				}
			}
			if(!JFolder::exists(JBPROJECT_PATH.'/thumb')){
				if(JFolder::create(JBPROJECT_PATH.'/thumb')){
					if(JFile::exists(JPATH_SITE.'/images/index.html')){
						JFile::copy(JPATH_SITE.'/images/index.html', JBPROJECT_PATH.'/thumb/index.html');
					}
				}
			}
		}
		elseif($type == 'portfolio'){
			if(!JFolder::exists(JBPORTFOLIO_PATH)){
				if(JFolder::create(JBPORTFOLIO_PATH)){
					if(JFile::exists(JPATH_SITE.'/images/index.html')){
						JFile::copy(JPATH_SITE.'/images/index.html', JBPORTFOLIO_PATH.'/index.html');
					}
				}
			}
			if(!JFolder::exists(JBPORTFOLIO_PATH.'/thumb')){
				if(JFolder::create(JBPORTFOLIO_PATH.'/thumb')){
					if(JFile::exists(JPATH_SITE.'/images/index.html')){
						JFile::copy(JPATH_SITE.'/images/index.html', JBPORTFOLIO_PATH.'/thumb/index.html');
					}
				}
			}
		} */
	}

	public static function uploadFile($post, $project){
		jimport('joomla.filesystem.folder');

		$app 	  = JFactory::getApplication();
		$projfile = JTable::getInstance('projectfile', 'Table');

		//check if path exists, else create
		if(!JFolder::exists(JBPROJECT_PATH)){
			if(JFolder::create(JBPROJECT_PATH)){
				if(JFile::exists(JPATH_SITE.'/images/index.html')){
					JFile::copy(JPATH_SITE.'/images/index.html', JBPROJECT_PATH.'/index.html');
				}
			}
		}

		//REMOVE THE FILES `IF` CHECKED
		$removeFiles = $app->input->get('file-id', null, 'array');
		if(!empty($removeFiles)){
			foreach($removeFiles as $removeFileId){
				$projfile->load($removeFileId);
				$old_doc = $projfile->file_name;
				$delete = JBPROJECT_PATH.'/'.$old_doc;
				unlink($delete);
				$projfile->delete($removeFileId);
			}
		}

		$uploadLimit = $post['uploadLimit'];
		for($i = 0; $i < $uploadLimit; $i++){
			$file = $app->input->files->get('uploadFile'.$i);

			if($file['size'] > 0){
				//check if the resume file can be uploaded
				$err = null;
				if(!self::canUpload($file, $err, 'project', $project->id)){
					// The file can't be upload
					$app->enqueueMessage(JText::_($err).' - '.JText::sprintf('COM_JBLANCE_ERROR_FILE_NAME', $file['name']), 'error');
					continue;	//continues goes to the for loop but break breaks the for loop
				}

				self::uploadEachFile($file, $project, $projfile);
			}	// end of file size
		}	//upload file loop end
	}

	function uploadEachFile($file, $project, $projfile){
		//get the new file name
		$new_doc = "proj_".$project->id."_".strtotime("now")."_".$file['name'];
		$new_doc = preg_replace('/[[:space:]]/', '_',$new_doc);	//replace space in the file name with _
		$new_doc = JFile::makeSafe($new_doc);
		$dest = JBPROJECT_PATH.'/'.$new_doc;
		$soure = $file['tmp_name'];
		// Move uploaded file
		$uploaded = JFile::upload($soure, $dest);

		$projfile->id = 0;
		$projfile->project_id = $project->id;
		$projfile->file_name = $new_doc;
		$projfile->show_name = JFile::makeSafe($file['name']);
		$projfile->hash = md5_file($file['tmp_name']);

		// pre-save checks
		if(!$projfile->check()){
			JError::raiseError(500, $projfile->getError());
		}
		// save the changes
		if(!$projfile->store()){
			JError::raiseError(500, $projfile->getError());
		}
		$projfile->checkin();
	}

	public static function messageAttachFile(){
		jimport('joomla.filesystem.folder');

		$response = array();
		$app  = JFactory::getApplication();
		$file = $app->input->files->get('uploadmessage');

		if($file['size'] > 0){
			//check if the resume file can be uploaded
			$err = null;
			if(!self::canUpload($file, $err, 'message', '')){
				// The file can't be upload
				$response['result'] = 'NO';
				$response['msg'] = $err;
				$response['elementID'] = 'uploadmessage';
				echo json_encode($response);
				$app->close();
			}

			if(!JFolder::exists(JBMESSAGE_PATH)){
				if(JFolder::create(JBMESSAGE_PATH)){
					if(JFile::exists(JPATH_SITE.'/images/index.html')){
						JFile::copy(JPATH_SITE.'/images/index.html', JBMESSAGE_PATH.'/index.html');
					}
				}
			}

			//get the new file name
			$new_doc = "msg_".strtotime("now")."_".$file['name'];
			$new_doc = preg_replace('/[[:space:]]/', '_', $new_doc);	//replace space in the file name with _
			$new_doc = JFile::makeSafe($new_doc);
			$dest = JBMESSAGE_PATH.'/'.$new_doc;
			$soure = $file['tmp_name'];
			// Move uploaded file
			$uploaded = JFile::upload($soure, $dest);

			$response['result'] = 'OK';
			$response['attachvalue'] = $file['name'].";".$new_doc;
			$response['attachname'] = $file['name'];
			$response['msg'] = JText::_('COM_JBLANCE_FILE_ATTACHED_SUCCESSFULLY');
			$response['elementID'] = 'uploadmessage';
			echo json_encode($response);
			$app->close();
		}
	}

	public static function dzUploadFile(){
		$app  		 = JFactory::getApplication();
		$element_id	 = $app->input->get('paramName', '', 'string');
		$upload_type = $app->input->get('type', '', 'string');
		$file 		 = $app->input->files->get($element_id);
		$registry 	 = new JRegistry();
		$response 	 = array();

		if($file['size'] > 0){

			//check and create required folder
			self::checkAndCreateFolder($upload_type);

			if($upload_type == 'category'){
				$prefix = 'categ_';
				$path 	= JBCATEGORY_PATH;
			}
			elseif($upload_type == 'project'){
				$prefix = 'proj_';
				$path 	= JBPROJECT_PATH;
			}
			elseif($upload_type == 'portfolio'){
				$prefix = 'port_';
				$path 	= JBPORTFOLIO_PATH;
			}
			elseif($upload_type == 'service'){
				$prefix = 'svc_';
				$path 	= JBSERVICE_PATH;
			}

			$file_name = $prefix.strtotime("now")."_".$file['name'];
			$file_name = preg_replace('/[[:space:]]/', '_', $file_name);	//replace space in the file name with _
			$file_name = JFile::makeSafe($file_name);

			self::resize($file, '100', '100', $path.'/thumb/'.$file_name);

			$dest = $path.'/'.$file_name;
			$soure = $file['tmp_name'];
			// Move uploaded file
			$uploaded = JFile::upload($soure, $dest);

			//create name, servername and size array and convert to json
			$return_val = array('name' => $file['name'], 'servername' => $file_name, 'size' => filesize($dest));
			$registry->loadArray($return_val);
			$return_val = $registry->toString();

			$response['result'] = 'OK';
			$response['attachvalue'] = $file['name'].";".$file_name.";".filesize($dest);
			$response['msg'] = JText::_('COM_JBLANCE_FILES_UPLOADED_SUCCESSFULLY');
			echo json_encode($response);
			$app->close();
		}
		else {
			$response['result'] = 'NO';
			$response['msg'] = 'File is empty';
			echo json_encode($response);
			$app->close();
		}
	}

	public static function dzRemoveFile(){
		$app  		 	 = JFactory::getApplication();
		$attach_value 	 = $app->input->get('attachvalue', '', 'string');
		$upload_type 	 = $app->input->get('type', '', 'string');
		$att_file 	 	 = explode(';', $attach_value);

		if($upload_type == 'category'){
			$path 	= JBCATEGORY_PATH;
		}
		elseif($upload_type == 'project'){
			$path 	= JBPROJECT_PATH;
		}
		elseif($upload_type == 'portfolio'){
			$prefix = 'port_';
			$path 	= JBPORTFOLIO_PATH;
		}
		elseif($upload_type == 'portfolio'){
			$prefix = 'port_';
			$path 	= JBPORTFOLIO_PATH;
		}
		elseif($upload_type == 'service'){
			$path 	= JBSERVICE_PATH;
		}

		$server_file_path  = $path.'/'.$att_file[1];
		$server_thumb_path = $path.'/thumb/'.$att_file[1];

		if(JFile::exists($server_file_path)){
			$delfile = unlink($server_file_path);
		}
		if(JFile::exists($server_thumb_path)){
			$delfile = unlink($server_thumb_path);
		}
		echo JText::_('COM_JBLANCE_FILE_REMOVED_SUCCESSFULLY');;
		$app->close();
	}

	/**
	 * @param File $file
	 * @param int $width
	 * @param int $height
	 * @param string $path This includes the path and file name of thumb image
	 * @return string
	 */
	public static function resize($file, $width, $height, $path){

		list($w, $h) = getimagesize($file['tmp_name']);			/* Get original image x y*/
		$ratio = max($width/$w, $height/$h);					/* calculate new image size with ratio */
		$h = ceil($height / $ratio);
		$x = ($w - $width / $ratio) / 2;
		$w = ceil($width / $ratio);

		$imgString = file_get_contents($file['tmp_name']);		/* read binary data from image file */
		$image = imagecreatefromstring($imgString);				/* create image from string */
		$tmp = imagecreatetruecolor($width, $height);
		imagecopyresampled($tmp, $image,
							0, 0,
							$x, 0,
							$width, $height,
							$w, $h);

		/* Save image */
		switch ($file['type']) {
			case 'image/jpeg':
				imagejpeg($tmp, $path, 100);
				break;
			case 'image/png':
				imagepng($tmp, $path, 0);
				break;
			case 'image/gif':
				imagegif($tmp, $path);
				break;
			default:
				exit;
			break;
		}
		/* cleanup memory */
		imagedestroy($image);
		imagedestroy($tmp);

		return $path;
	}

	public static function portfolioAttachFile(){
		jimport('joomla.filesystem.folder');

		$app  = JFactory::getApplication();
		$elementID = $app->input->get('elementID', '', 'string');

		$response = array();
		$file = $app->input->files->get($elementID);

		//get the type whether portfolio image or file
		if($elementID == 'portfoliopicture'){
			$type = 'picture';
			$docPrefix = 'pic_';
		}
		elseif((strpos($elementID, 'portfolioattachment') === 0)){
			$type = 'project';
			$docPrefix = 'file_';
		}

		if($file['size'] > 0){
			//check if the resume file can be uploaded
			$err = null;
			if(!self::canUpload($file, $err, $type, '')){
				// The file can't be upload
				$response['result'] = 'NO';
				$response['msg'] = $err;
				$response['elementID'] = $elementID;
				echo json_encode($response);
				$app->close();
			}

			if(!JFolder::exists(JBPORTFOLIO_PATH)){
				if(JFolder::create(JBPORTFOLIO_PATH)){
					if(JFile::exists(JPATH_SITE.'/images/index.html')){
						JFile::copy(JPATH_SITE.'/images/index.html', JBPORTFOLIO_PATH.'/index.html');
					}
				}
			}

			//get the new file name
			$new_doc = $docPrefix.strtotime("now")."_".$file['name'];
			$new_doc = preg_replace('/[[:space:]]/', '_', $new_doc);	//replace space in the file name with _
			$new_doc = JFile::makeSafe($new_doc);
			$dest = JBPORTFOLIO_PATH.'/'.$new_doc;
			$soure = $file['tmp_name'];
			// Move uploaded file
			$uploaded = JFile::upload($soure, $dest);

			$response['result'] = 'OK';
			$response['attachvalue'] = $file['name'].";".$new_doc;
			$response['attachname'] = $file['name'];
			$response['msg'] = JText::_('COM_JBLANCE_FILE_ATTACHED_SUCCESSFULLY');
			$response['elementID'] = $elementID;
			echo json_encode($response);
			$app->close();
		}
	}

	public static function uploadCustomFieldFile($field_id){
		jimport('joomla.filesystem.folder');

		$app 	  = JFactory::getApplication();
		$response = array();

		//check if path exists, else create
		if(!JFolder::exists(JBCUSTOMFILE_PATH)){
			if(JFolder::create(JBCUSTOMFILE_PATH)){
				if(JFile::exists(JPATH_SITE.'/images/index.html')){
					JFile::copy(JPATH_SITE.'/images/index.html', JBCUSTOMFILE_PATH.'/index.html');
				}
			}
		}

		//REMOVE THE CUSTOM FILE `IF` CHECKED
		$chkAttach = $app->input->get('chk-customfile-'.$field_id, '', 'string');
		if(!empty($chkAttach)){
			$attFile = explode(';', $chkAttach);
			$filename = $attFile[1];
			$delete = JBCUSTOMFILE_PATH.'/'.$filename;
			if(JFile::exists($delete)){
				unlink($delete);
				$response['result'] = 'NO';
			}
		}

		$file = $app->input->files->get('custom_field_'.$field_id);
		if($file['size'] > 0){
			//check if the custom file can be uploaded
			$err = null;
			if(!self::canUpload($file, $err, 'custom', '')){
				// The file can't be upload
				$app->enqueueMessage(JText::_($err).' - '.JText::sprintf('COM_JBLANCE_ERROR_FILE_NAME', $file['name']), 'error');
				$response['result'] = 'NO';
				$response['msg'] = $err;
				return $response;
			}

			//get the new file name
			$new_doc = "custom_".strtotime("now")."_".$file['name'];
			$new_doc = preg_replace('/[[:space:]]/', '_', $new_doc);	//replace space in the file name with _
			$new_doc = JFile::makeSafe($new_doc);
			$dest 	 = JBCUSTOMFILE_PATH.'/'.$new_doc;
			$soure 	 = $file['tmp_name'];

			// Move uploaded file
			$uploaded = JFile::upload($soure, $dest);

			$response['result'] = 'OK';
			$response['attachvalue'] = $file['name'].";".$new_doc;
			$response['attachname'] = $file['name'];
			return $response;
		}	// end of file size
		return $response;
	}

	public static function canUpload($file, &$err, $attachType, $projectId){

		$lang = JFactory::getLanguage();
		$lang->load('com_jblance', JPATH_SITE);
		$config = JblanceHelper::getConfig();
		$db 	= JFactory::getDbo();

		if($file['error'] != 0){
			$err = JText::_('COM_JBLANCE_UPLOAD_FILE_ERROR');
			return false;
		}

		if($attachType == 'project'){
			//check if the file type is allowed
			$type = $config->projectFileType;
			$allowed = explode(',', $type);
			$format = $file['type'];
			if(!preg_match('/(.*)\.(zip|docx)/', $file['name'])){
				if(!in_array($format, $allowed)){
					$err = JText::_('COM_JBLANCE_FILE_TYPE_NOT_ALLOWED');
					return false;
				}
			}
			//check for the maximum file size
			$maxSize = $config->projectMaxsize;
			if((int)$file['size'] / 1024 > $maxSize){
				$err = JText::sprintf('COM_JBLANCE_FILE_EXCEEDS_LIMIT', $maxSize);
				return false;
			}
			//check for max file count allowed per project
			$fileLimitConf = $config->projectMaxfileCount;
			$query = "SELECT COUNT(f.id) FROM #__jblance_project_file f WHERE f.project_id=".$db->quote($projectId)." AND f.is_nda_file=0";
			$db->setQuery($query);
			$fileCount = $db->loadResult();

			if($fileCount >= $fileLimitConf){
				$err = JText::sprintf('COM_JBLANCE_MAX_FILE_FOR_PROJECT_EXCEEDED_ALLOWED_COUNT', $fileLimitConf);
				return false;
			}
		}

		if($attachType == 'picture'){
			//check if the file type is allowed
			$type = $config->imgFileType;
			$allowed = explode(',', $type);
			$format = $file['type'];
			if(!in_array($format, $allowed)){
				$err = JText::_('COM_JBLANCE_FILE_TYPE_NOT_ALLOWED');
				return false;
			}
			//check for the maximum file size
			$maxSize = $config->imgMaxsize;
			if((int)$file['size'] / 1024 > $maxSize){
				$err = JText::sprintf('COM_JBLANCE_FILE_EXCEEDS_LIMIT', $maxSize);
				return false;
			}
		}

		if($attachType == 'message' || $attachType == 'custom'){
			//check if the file type is allowed
			$type = $config->projectFileType;
			$allowed = explode(',', $type);
			$format = $file['type'];
			if(!preg_match('/(.*)\.(zip|docx)/', $file['name'])){
				if(!in_array($format, $allowed)){
					$err = JText::_('COM_JBLANCE_FILE_TYPE_NOT_ALLOWED');
					return false;
				}
			}
			//check for the maximum file size
			$maxSize = $config->projectMaxsize;
			if((int)$file['size'] / 1024 > $maxSize){
				$err = JText::sprintf('COM_JBLANCE_FILE_EXCEEDS_LIMIT', $maxSize);
				return false;
			}
		}

		if($attachType == 'video'){
			$type = 'video/x-flv';
			$allowed = explode(',', $type);
			$format = $file['type'];

			if(!in_array($format, $allowed)){
				$err = 'COM_JBLANCE_YOUR_FILE_IS_IGNORED';
				return false;
			}

			$maxSize = $config->vidMaxsize;
			if((int)$file['size'] / 1024 > $maxSize){
				$err = JText::sprintf('COM_JBLANCE_FILE_EXCEEDS_LIMIT', $maxSize);
				return false;
			}
		}
		return true;
	}

	//3.Upload Photo
	public static function uploadPictureMedia(){
		jimport('joomla.filesystem.folder');

		$app  		= JFactory::getApplication();
		$lang 		= JFactory::getLanguage();
		$lang->load('com_jblance', JPATH_SITE);

		$file 	 	= $app->input->files->get('profile_file');
		$imgdata 	= $app->input->get('imageData', '', 'string');
		$userid  	= $app->input->get('user_id', 0, 'int');
		$uploadType = $app->input->get('upload_type', '', 'string');
		$response 	= array();

		$jbuser = JblanceHelper::get('helper.user');
		$jbuserid = $jbuser->getUser($userid)->id;
		$row	= JTable::getInstance('jbuser', 'Table');
		$row->load($jbuserid);

		$config  = JblanceHelper::getConfig();
		$type 	 = $config->imgFileType;
		$allowed = explode(',', $type);
		//$pwidth  = $config->imgWidth;
		//$pheight = $config->imgHeight;
		$maxsize = $config->imgMaxsize;

		if($uploadType == 'NO_UPLOAD_CROP'){
			$response['result'] = 'NO';
			$response['msg'] = JText::_('COM_JBLANCE_PLEASE_SELECT_NEW_PICTURE');
			echo json_encode($response);
			$app->close();
		}
		elseif($uploadType == 'UPLOAD_CROP'){
			//check if the profile picture can be uploaded
			$err = null;
			if(!self::canUpload($file, $err, 'picture', '')){
				// The file can't be upload
				$response['result'] = 'NO';
				$response['msg'] = $err;
				echo json_encode($response);
				$app->close();
			}

			if(!JFolder::exists(JBPROFILE_PIC_PATH)){
				if(JFolder::create(JBPROFILE_PIC_PATH)){
					if(JFile::exists(JPATH_SITE.'/images/index.html')){
						JFile::copy(JPATH_SITE.'/images/index.html', JBPROFILE_PIC_PATH.'/index.html');
						JFile::copy(JPATH_SITE.'/images/index.html', JPATH_SITE.'/images/jblance/index.html');
					}
				}
				if(JFolder::create(JBPROFILE_PIC_PATH.'/original')){
					if(JFile::exists(JPATH_SITE.'/images/index.html')){
						JFile::copy(JPATH_SITE.'/images/index.html', JBPROFILE_PIC_PATH.'/original/index.html');
					}
				}
			}

			$docPrefix = 'prof_';
			$new_doc = $docPrefix.$userid.'_'.strtotime("now").'.jpg';
			$new_doc = preg_replace('/[[:space:]]/', '_', $new_doc);	//replace space in the file name with _
			$new_doc = JFile::makeSafe($new_doc);

			$dest_original = JBPROFILE_PIC_PATH.'/original/'.$new_doc;
			$dest_pic	   = JBPROFILE_PIC_PATH.'/'.$new_doc;

			// remove new files
			$pictureFilePath = JBPROFILE_PIC_PATH.'/'.$row->picture;
			$pictureOrigPath = JBPROFILE_PIC_PATH.'/original/'.$row->picture;

			if(JFile::exists($pictureFilePath)){
				$delpic = unlink($pictureFilePath);
			}
			if(JFile::exists($pictureOrigPath)){
				$delpic = unlink($pictureOrigPath);
			}

			$soure = $file['tmp_name'];

			// upload orignal file
			$uploaded = JFile::upload($soure, $dest_original);

			// upload cropped file
			self::uploadCroppedPicture($dest_pic, $imgdata);

			$row->picture = $new_doc;
			$row->thumb = '';

			// pre-save checks
			if(!$row->check()){
				JError::raiseError(500, $row->getError());
			}
			// save the changes
			if(!$row->store()){
				JError::raiseError(500, $row->getError());
			}
			$row->checkin();

			$response['result'] =  'OK';
			$response['msg'] = JText::_('COM_JBLANCE_PICTURE_UPLOADED_SUCCESSFULLY');
			echo json_encode($response); $app->close();

		}
		elseif($uploadType == 'CROP_ONLY'){
			$dest_pic = JBPROFILE_PIC_PATH.'/'.$row->picture;
			self::uploadCroppedPicture($dest_pic, $imgdata);
			$response['result'] =  'OK';
			$response['msg'] = JText::_('COM_JBLANCE_THUMBNAIL_SAVED_SUCCESSFULLY');
			echo json_encode($response); $app->close();
		}
	}

	public static function uploadCroppedPicture($dest_pic, $imgdata){

		$filter   = JFilterInput::getInstance();
		$sanitize = $filter->clean(preg_replace('#^data:image/\w+;base64,#i', '', $imgdata), 'BASE64');
		$data 	  = base64_decode($sanitize);

		$res = JFile::write($dest_pic, $data);	//writing original file without compression

		return $res;
	}

	public static function removePictureMedia(){
		$app  = JFactory::getApplication();
		$jbuser = JblanceHelper::get('helper.user');
		$lang = JFactory::getLanguage();
		$lang->load('com_jblance', JPATH_SITE);
		$response = array();

		$userid = $app->input->get('userid', 0, 'int');
		$jbuserid = $jbuser->getUser($userid)->id;

		$row	= JTable::getInstance('jbuser', 'Table');
		$row->load($jbuserid);

		$destpic = JBPROFILE_PIC_PATH.'/'.$row->picture;
		$desttmb = JBPROFILE_PIC_PATH.'/'.$row->thumb;

		$pictureFilePath = JBPROFILE_PIC_PATH.'/'.$row->picture;
		$pictureOrigPath = JBPROFILE_PIC_PATH.'/original/'.$row->picture;

		if(JFile::exists($pictureFilePath)){
			$delpic = unlink($pictureFilePath);
			$delpic = unlink($pictureOrigPath);

			$row->picture = '';
			$row->thumb = '';

			// pre-save checks
			if (!$row->check()){
				JError::raiseError(500, $row->getError());
			}
			// save the changes
			if (!$row->store()){
				JError::raiseError(500, $row->getError());
			}
			$row->checkin();

			$response['result'] = 'OK';
			$response['msg'] = JText::_('COM_JBLANCE_PICTURE_REMOVED_SUCCESSFULLY');
			echo json_encode($response);
			$app->close();
		}
		else {
			$response['result'] = 'NO';
			$response['msg'] = JText::_('COM_JBLANCE_FILE_DOES_NOT_EXIST');
			echo json_encode($response);
			$app->close();
		}
	}

	public static function cropPictureMedia(){
		$lang = JFactory::getLanguage();
		$lang->load('com_jblance', JPATH_SITE);

		$app  	= JFactory::getApplication();
		$imgLoc	= $app->input->get('imgLoc', '', 'string');
		$tmbLoc	= $app->input->get('tmbLoc', '', 'string');

		$url = JBPROFILE_PIC_PATH.'/'.$imgLoc;
		$tmb = JBPROFILE_PIC_PATH.'/'.$tmbLoc;

		$response = array();

		if(JFile::exists($url)){

			$width 	= $app->input->get('cropW', 0, 'int');
			$height = $app->input->get('cropH', 0, 'int');
			$left 	= $app->input->get('cropX', 0, 'int');
			$top 	= $app->input->get('cropY', 0, 'int');

			header ("Content-type: image/jpg");
			$src 	= 	@imagecreatefromjpeg($url);
			$im	 	=	@imagecreatetruecolor($width, $height);

			imagecopy($im, $src, 0, 0, $left, $top, $width, $height);
			imagejpeg($im, $tmb, 100);
			imagedestroy($im);

			$response['result'] = 'OK';
			$response['msg'] = JText::_('COM_JBLANCE_THUMBNAIL_SAVED_SUCCESSFULLY');
			$response['return'] = JRoute::_('index.php?option=com_jblance&view=user&layout=editpicture', false);
		}
		else {
			$response['result'] = 'NO';
			$response['msg'] = JText::_('COM_JBLANCE_ERROR_SAVING_THUMBNAIL');
		}
		echo json_encode($response);
		$app->close();
	}

	public static function getFileInfo($type, $id){
		$db		= JFactory::getDbo();
		$fileInfo = array();
		if($type == 'portfolio'){
			$query = "SELECT attachment FROM #__jblance_portfolio WHERE id=".$db->quote($id);
			$db->setQuery($query);

			$attachment = explode(";", $db->loadResult());
			$showName = $attachment[0];
			$fileName = $attachment[1];

			$fileInfo['fileUrl'] = JBPORTFOLIO_URL.$fileName;
			$fileInfo['filePath'] = JBPORTFOLIO_PATH.'/'.$fileName;
			$fileInfo['fileName'] = $fileName;
			$fileInfo['showName'] = $showName;
		}
		elseif($type == 'project'){
			$query = "SELECT file_name,show_name FROM #__jblance_project_file WHERE id=".$db->quote($id);
			$db->setQuery($query);

			$projFile = $db->loadObject();
			$showName = $projFile->show_name;
			$fileName = $projFile->file_name;

			$fileInfo['fileUrl'] = JBPROJECT_URL.$fileName;
			$fileInfo['filePath'] = JBPROJECT_PATH.'/'.$fileName;
			$fileInfo['fileName'] = $fileName;
			$fileInfo['showName'] = $showName;
		}
		elseif($type == 'message'){
			$query = "SELECT attachment FROM #__jblance_message WHERE id=".$db->quote($id);
			$db->setQuery($query);

			$attachment = explode(";", $db->loadResult());
			$showName = $attachment[0];
			$fileName = $attachment[1];

			$fileInfo['fileUrl'] = JBMESSAGE_URL.$fileName;
			$fileInfo['filePath'] = JBMESSAGE_PATH.'/'.$fileName;
			$fileInfo['fileName'] = $fileName;
			$fileInfo['showName'] = $showName;
		}
		elseif($type == 'nda'){
			$query = "SELECT attachment FROM #__jblance_bid WHERE id=".$db->quote($id);
			$db->setQuery($query);

			$attachment = explode(";", $db->loadResult());
			$showName = $attachment[0];
			$fileName = $attachment[1];

			$fileInfo['fileUrl'] = JBBIDNDA_URL.$fileName;
			$fileInfo['filePath'] = JBBIDNDA_PATH.'/'.$fileName;
			$fileInfo['fileName'] = $fileName;
			$fileInfo['showName'] = $showName;
		}
		elseif($type == 'customfile'){
			$query = "SELECT value FROM #__jblance_custom_field_value WHERE id=".$db->quote($id);
			$db->setQuery($query);

			$attachment = explode(";", $db->loadResult());
			$showName = $attachment[0];
			$fileName = $attachment[1];

			$fileInfo['fileUrl'] = JBCUSTOMFILE_URL.$fileName;
			$fileInfo['filePath'] = JBCUSTOMFILE_PATH.'/'.$fileName;
			$fileInfo['fileName'] = $fileName;
			$fileInfo['showName'] = $showName;
		}

		return $fileInfo;
	}

	public static function getPorfolioFileInfo($type, $id, $attachmentColumnNum){
		$db		= JFactory::getDbo();
		$fileInfo = array();
		if($type == 'portfolio'){
			$query = "SELECT ".$db->quoteName($attachmentColumnNum)." FROM #__jblance_portfolio WHERE id=".$db->quote($id);
			$db->setQuery($query);

			$attachment = explode(";", $db->loadResult());
			$showName = $attachment[0];
			$fileName = $attachment[1];

			$fileInfo['fileUrl'] = JBPORTFOLIO_URL.$fileName;
			$fileInfo['filePath'] = JBPORTFOLIO_PATH.'/'.$fileName;
			$fileInfo['fileName'] = $fileName;
			$fileInfo['showName'] = $showName;
		}

		return $fileInfo;
	}

	public static function downloadFile(){
		$app  	= JFactory::getApplication();
		$db		= JFactory::getDbo();
		$type 	= $app->input->get('type', '', 'string');
		$id 	= $app->input->get('id', 0, 'int');
		$attach	= $app->input->get('attachment', '', 'string');

		if($type != 'portfolio')
			$fileInfo = self::getFileInfo($type, $id);
		else
			$fileInfo = self::getPorfolioFileInfo($type, $id, $attach);

		$filePath = $fileInfo['filePath'];
		$fileUrl = $fileInfo['fileUrl'];
		$showName = $fileInfo['showName'];

		self::setDownloadHeader($filePath, $fileUrl, $showName);
	}

	/**
	 * @param json $attachments Attachments stored in db as json format
	 * @param string $type Type can be service, project, portfolio
	 * @param boolean $defaultImg Yes to return default image location
	 * @return array $return Returns array containing the file name, servername, size and location
	 */
	public static function processAttachment($attachments, $type, $defaultImg = true){
		$return = array();
		$registry = new JRegistry;
		$registry->loadString($attachments);
		$files = $registry->toArray();
		$filePath = '';

		switch($type){
			case 'service':
				$fileUrl = JBSERVICE_URL;
				$filePath = JBSERVICE_PATH;
				break;
			case 'project':
				$fileUrl = JBPROJECT_URL;
				$filePath = JBPROJECT_PATH;
				break;
			case 'portfolio':
				$fileUrl = JBPORTFOLIO_URL;
				$filePath = JBPORTFOLIO_PATH;
				break;
			case 'category':
				$fileUrl = JBCATEGORY_URL;
				$filePath = JBCATEGORY_PATH;
				break;
		}

		//if there is no attachment, send an array with default image location
		if(!empty($files)){
			foreach($files as $file){
				$value = explode(';', $file);

				$obj['name'] = $value[0];
				$obj['servername'] = $value[1];
				$obj['size'] = $value[2];
				$obj['location'] = JFile::exists($filePath.'/'.$value[1]) ? $fileUrl.$value[1] : JPATH_COMPONENT.'/images/default_image.png';
				$obj['thumb'] = $fileUrl.'thumb/'.$value[1];
				//$obj['location'] = $fileUrl.$value[1];
				$return[] = $obj;
			}
		}
		else {
			if($defaultImg){
				$obj['location'] = $obj['thumb'] = 'components/com_jblance/images/default_image.png';
				$return[] = $obj;
			}
		}
		return $return;
	}

	public static function renderImageCarousel($attachments, $type){

		$files = self::processAttachment($attachments, $type, false);

		if(empty($files))
			return;

		$html = '';
		$html .= '<div id="myCarousel" class="carousel slide">';

		$html .= '<ol class="carousel-indicators">';
		foreach($files as $key=>$file){
			$active = ($key == 0) ? 'class="active"' : '';
			$html .= '<li data-target="#myCarousel" data-slide-to="'.$key.'" '.$active.'></li>';
		}
		$html .= '</ol>';

		$html .= '<div class="carousel-inner">';
		foreach($files as $key=>$file){
			$active = ($key == 0) ? 'active' : '';
			$html .= '<div class="item '.$active.'">';
			//$html .= '<img class="img-polaroid" style="margin: auto;" src="'.$file['location'].'" alt="">';
			$html .= '<div class="jbf-image big" style="background-image: url('.$file['location'].');"></div>';
			$html .= '</div>';
		}
		$html .= '</div>';

		$html .= '<a class="carousel-control left" href="#myCarousel" data-slide="prev">&lsaquo;</a>';
		$html .= '<a class="carousel-control right" href="#myCarousel" data-slide="next">&rsaquo;</a>';

		$html .= '</div>';

		return $html;
	}

	public static function renderDropzone($elementid){
		$html = '';

		$html .= "		<div class=\"upload-dropzone\" id=\"".$elementid."\">\n";
		$html .= 			JText::_('COM_JBLANCE_DRAG_DROP_FILES_HERE');
		$html .= "		</div>\n";
		$html .= "		<div id=\"".$elementid."-actions\" class=\"row-fluid\">\n";
		$html .= "			<div class=\"span7\">\n";
		$html .= "			\n";
		$html .= "				<!-- The fileinput-button span is used to style the file input field as button -->\n";
		$html .= "				<span class=\"btn btn-success ".$elementid."-fileinput-button dz-clickable\"> \n";
		$html .= "					<i class=\"jbf-icon-plus-circle\"></i> <span>".JText::_('COM_JBLANCE_ADD_FILES')."</span>\n";
		$html .= "				</span>\n";
		$html .= "				<button type=\"button\" class=\"btn btn-primary start\">\n";
		$html .= "					<i class=\"jbf-icon-upload\"></i> <span>".JText::_('COM_JBLANCE_START_UPLOAD')."</span>\n";
		$html .= "				</button>\n";
		$html .= "				<button type=\"reset\" class=\"btn btn-warning cancel\">\n";
		$html .= "					<i class=\"jbf-icon-minus-circle\"></i> <span>".JText::_('COM_JBLANCE_CANCEL_UPLOAD')."</span>\n";
		$html .= "				</button>\n";
		$html .= "			</div>\n";
		$html .= "			<div class=\"span5\">\n";
		$html .= "				<!-- The global file processing state -->\n";
		$html .= "				<span class=\"fileupload-process\">\n";
		$html .= "					<div id=\"".$elementid."-total-progress\" class=\"progress progress-striped active\" role=\"progressbar\" aria-valuemin=\"0\" aria-valuemax=\"100\" aria-valuenow=\"0\">\n";
		$html .= "						<div class=\"bar bar-success\" style=\"width: 0%;\" data-dz-uploadprogress=\"\"></div>\n";
		$html .= "					</div>\n";
		$html .= "				</span>\n";
		$html .= "			</div>\n";
		$html .= "		</div>\n";
		$html .= "		<div class=\"table table-striped\" class=\"files\" id=\"".$elementid."-previews\">\n";
		$html .= "			<div id=\"".$elementid."-template\" class=\"file-row\">\n";
		$html .= "				<!-- This is used as the file preview template -->\n";
		$html .= "				<div>\n";
		$html .= "					<span class=\"preview\"><img class=\"img-polaroid\" data-dz-thumbnail /> </span>\n";
		$html .= "				</div>\n";
		$html .= "				<div>\n";
		$html .= "					<p class=\"name\" data-dz-name></p>\n";
		$html .= "					<strong class=\"error text-danger\" data-dz-errormessage></strong>\n";
		$html .= "				</div>\n";
		$html .= "				<div style='visibility: hidden'>\n";
		$html .= "					<p class=\"size\" data-dz-size></p>\n";
		$html .= "					<div class=\"progress progress-striped active\" role=\"progressbar\" aria-valuemin=\"0\" aria-valuemax=\"100\" aria-valuenow=\"0\">\n";
		$html .= "						<div class=\"bar bar-success\" style=\"width: 0%;\" data-dz-uploadprogress></div>\n";
		$html .= "					</div>\n";
		$html .= "				</div>\n";
		$html .= "				<div>\n";
		$html .= "					<button type=\"button\" class=\"btn btn-primary start\">\n";
		$html .= "						<i class=\"jbf-icon-upload\"></i> <span>".JText::_('COM_JBLANCE_START')."</span>\n";
		$html .= "					</button>\n";
		$html .= "					<button data-dz-remove class=\"btn btn-warning cancel\">\n";
		$html .= "						<i class=\"jbf-icon-minus-circle\"></i> <span>".JText::_('COM_JBLANCE_CANCEL')."</span>\n";
		$html .= "					</button>\n";
		$html .= "					<button data-dz-remove class=\"btn btn-danger delete\">\n";
		$html .= "						<i class=\"jbf-icon-trash\"></i> <span>".JText::_('COM_JBLANCE_DELETE')."</span>\n";
		$html .= "					</button>\n";
		$html .= "				</div>\n";
		$html .= "			</div>\n";
		$html .= "		</div>\n";

		return $html;

	}

	function setDownloadHeader($filePath, $fileUrl, $fileName){
		$view_types = array();
		$view_types = explode(',', 'html,htm,txt,pdf,doc,jpg,jpeg,png,gif');

		clearstatcache();

		if (!file_exists($filePath))
			$len = 0;
		else
			$len = filesize($filePath);

		$filename = basename($filePath);
		$file_extension = strtolower(substr(strrchr($filename,"."),1));
		$ctype = self::datei_mime($file_extension);//$ctype = 'application/force-download';
		ob_end_clean();

		// needed for MS IE - otherwise content disposition is not used?
		if(ini_get('zlib.output_compression'))
			ini_set('zlib.output_compression', 'Off');

		header("Cache-Control: public, must-revalidate");
		header('Cache-Control: pre-check=0, post-check=0, max-age=0');
		// header("Pragma: no-cache");  // Problems with MS IE
		header("Expires: 0");
		header("Content-Description: File Transfer");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		header("Content-Type: " . $ctype);
		header("Content-Length: ".(string)$len);

		if(!in_array($file_extension, $view_types))
			header('Content-Disposition: attachment; filename="'.$fileName.'"');
		else
			header('Content-Disposition: inline; filename="'.$fileName.'"');	// view file in browser

		header("Content-Transfer-Encoding: binary\n");

		@readfile($filePath);
		exit;
	}

	function datei_mime($filetype) {

		switch ($filetype) {
			case "ez":  $mime="application/andrew-inset"; break;
			case "hqx": $mime="application/mac-binhex40"; break;
			case "cpt": $mime="application/mac-compactpro"; break;
			case "doc": $mime="application/msword"; break;
			case "bin": $mime="application/octet-stream"; break;
			case "dms": $mime="application/octet-stream"; break;
			case "lha": $mime="application/octet-stream"; break;
			case "lzh": $mime="application/octet-stream"; break;
			case "exe": $mime="application/octet-stream"; break;
			case "class": $mime="application/octet-stream"; break;
			case "dll": $mime="application/octet-stream"; break;
			case "oda": $mime="application/oda"; break;
			case "pdf": $mime="application/pdf"; break;
			case "ai":  $mime="application/postscript"; break;
			case "eps": $mime="application/postscript"; break;
			case "ps":  $mime="application/postscript"; break;
			case "xls": $mime="application/vnd.ms-excel"; break;
			case "ppt": $mime="application/vnd.ms-powerpoint"; break;
			case "wbxml": $mime="application/vnd.wap.wbxml"; break;
			case "wmlc": $mime="application/vnd.wap.wmlc"; break;
			case "wmlsc": $mime="application/vnd.wap.wmlscriptc"; break;
			case "vcd": $mime="application/x-cdlink"; break;
			case "pgn": $mime="application/x-chess-pgn"; break;
			case "csh": $mime="application/x-csh"; break;
			case "dvi": $mime="application/x-dvi"; break;
			case "spl": $mime="application/x-futuresplash"; break;
			case "gtar": $mime="application/x-gtar"; break;
			case "hdf": $mime="application/x-hdf"; break;
			case "js":  $mime="application/x-javascript"; break;
			case "nc":  $mime="application/x-netcdf"; break;
			case "cdf": $mime="application/x-netcdf"; break;
			case "swf": $mime="application/x-shockwave-flash"; break;
			case "tar": $mime="application/x-tar"; break;
			case "tcl": $mime="application/x-tcl"; break;
			case "tex": $mime="application/x-tex"; break;
			case "texinfo": $mime="application/x-texinfo"; break;
			case "texi": $mime="application/x-texinfo"; break;
			case "t":   $mime="application/x-troff"; break;
			case "tr":  $mime="application/x-troff"; break;
			case "roff": $mime="application/x-troff"; break;
			case "man": $mime="application/x-troff-man"; break;
			case "me":  $mime="application/x-troff-me"; break;
			case "ms":  $mime="application/x-troff-ms"; break;
			case "ustar": $mime="application/x-ustar"; break;
			case "src": $mime="application/x-wais-source"; break;
			case "zip": $mime="application/x-zip"; break;
			case "au":  $mime="audio/basic"; break;
			case "snd": $mime="audio/basic"; break;
			case "mid": $mime="audio/midi"; break;
			case "midi": $mime="audio/midi"; break;
			case "kar": $mime="audio/midi"; break;
			case "mpga": $mime="audio/mpeg"; break;
			case "mp2": $mime="audio/mpeg"; break;
			case "mp3": $mime="audio/mpeg"; break;
			case "aif": $mime="audio/x-aiff"; break;
			case "aiff": $mime="audio/x-aiff"; break;
			case "aifc": $mime="audio/x-aiff"; break;
			case "m3u": $mime="audio/x-mpegurl"; break;
			case "ram": $mime="audio/x-pn-realaudio"; break;
			case "rm":  $mime="audio/x-pn-realaudio"; break;
			case "rpm": $mime="audio/x-pn-realaudio-plugin"; break;
			case "ra":  $mime="audio/x-realaudio"; break;
			case "wav": $mime="audio/x-wav"; break;
			case "pdb": $mime="chemical/x-pdb"; break;
			case "xyz": $mime="chemical/x-xyz"; break;
			case "bmp": $mime="image/bmp"; break;
			case "gif": $mime="image/gif"; break;
			case "ief": $mime="image/ief"; break;
			case "jpeg": $mime="image/jpeg"; break;
			case "jpg": $mime="image/jpeg"; break;
			case "jpe": $mime="image/jpeg"; break;
			case "png": $mime="image/png"; break;
			case "tiff": $mime="image/tiff"; break;
			case "tif": $mime="image/tiff"; break;
			case "wbmp": $mime="image/vnd.wap.wbmp"; break;
			case "ras": $mime="image/x-cmu-raster"; break;
			case "pnm": $mime="image/x-portable-anymap"; break;
			case "pbm": $mime="image/x-portable-bitmap"; break;
			case "pgm": $mime="image/x-portable-graymap"; break;
			case "ppm": $mime="image/x-portable-pixmap"; break;
			case "rgb": $mime="image/x-rgb"; break;
			case "xbm": $mime="image/x-xbitmap"; break;
			case "xpm": $mime="image/x-xpixmap"; break;
			case "xwd": $mime="image/x-xwindowdump"; break;
			case "msh": $mime="model/mesh"; break;
			case "mesh": $mime="model/mesh"; break;
			case "silo": $mime="model/mesh"; break;
			case "wrl": $mime="model/vrml"; break;
			case "vrml": $mime="model/vrml"; break;
			case "css": $mime="text/css"; break;
			case "asc": $mime="text/plain"; break;
			case "txt": $mime="text/plain"; break;
			case "gpg": $mime="text/plain"; break;
			case "rtx": $mime="text/richtext"; break;
			case "rtf": $mime="text/rtf"; break;
			case "wml": $mime="text/vnd.wap.wml"; break;
			case "wmls": $mime="text/vnd.wap.wmlscript"; break;
			case "etx": $mime="text/x-setext"; break;
			case "xsl": $mime="text/xml"; break;
			case "flv": $mime="video/x-flv"; break;
			case "mpeg": $mime="video/mpeg"; break;
			case "mpg": $mime="video/mpeg"; break;
			case "mpe": $mime="video/mpeg"; break;
			case "qt":  $mime="video/quicktime"; break;
			case "mov": $mime="video/quicktime"; break;
			case "mxu": $mime="video/vnd.mpegurl"; break;
			case "avi": $mime="video/x-msvideo"; break;
			case "movie": $mime="video/x-sgi-movie"; break;
			case "asf": $mime="video/x-ms-asf"; break;
			case "asx": $mime="video/x-ms-asf"; break;
			case "wm":  $mime="video/x-ms-wm"; break;
			case "wmv": $mime="video/x-ms-wmv"; break;
			case "wvx": $mime="video/x-ms-wvx"; break;
			case "ice": $mime="x-conference/x-cooltalk"; break;
			case "rar": $mime="application/x-rar"; break;
			default:    $mime="application/octet-stream"; break;
		}
		return $mime;
	}
}
