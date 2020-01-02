<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	07 June 2012
 * @file name	:	helpers/integration/joombri/profile.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
 defined('_JEXEC') or die('Restricted access');

class JoombriAvatarJoombri extends JoombriAvatar {
	public function __construct(){
		$this->priority = 25;
	}

	public function getEditURL(){
		return JRoute::_('index.php?option=com_jblance&view=user&layout=editpicture');
	}

	protected function _getURL($userid, $folder){
		//get the JoomBri picture
		JblanceHelper::import('helper.user');
		$jbuser = new UserHelper();
		$jbpic = $jbuser->getUser($userid)->picture;
		
		/* $db = JFactory::getDbo();
		$query = "SELECT picture FROM #__jblance_user WHERE user_id=".$db->quote($userid);
		$db->setQuery($query);
		$jbpic = $db->loadResult(); */
		
		if($folder == ''){
			$imgpath = JBPROFILE_PIC_PATH.'/'.$jbpic;
			$imgurl  = JBPROFILE_PIC_URL.$jbpic;
		}
		elseif($folder == 'original'){
			$imgpath = JBPROFILE_PIC_PATH.'/original/'.$jbpic;
			$imgurl  = JBPROFILE_PIC_URL.'original/'.$jbpic;
		}
		
		if(JFile::exists($imgpath)){
			return $imgurl;
		}
		elseif($userid){
			if($folder == '')
				$imgurl = JURI::root().'components/com_jblance/images/nophoto_sm.png';
			elseif($folder == 'original')
				$imgurl = JURI::root().'components/com_jblance/images/nophoto_big.png';
			
			return  $imgurl;
		}
	}
}
