<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST');
jimport( 'joomla.application.component.view');

$document   = JFactory::getDocument();

$sitepath = JURI::base();
$itemid = $this->itemid;

$jwigetview ='';
$profile_id= JFactory::getApplication()->input->get('pid');
$namecard_template= JFactory::getApplication()->input->get('namecard_template','','STRING');

// Do not show toolbar if namecard is only displayed on other sites
if (empty($namecard_template))
{
	echo $this->toolbarHtml;
}

if($profile_id)
{
	$directory = JPATH_SITE.'/components/com_invitex/views/namecard/namecard_templates';
	$jwigetview= JFile::read($directory.'/'.JFactory::getApplication()->input->get('namecard_template').'.html');

	$namecard_user	=	$this->model->getnamecardinfo($profile_id);

	if(isset($namecard_user['img_source'])){
			$var = '<a href="'.$namecard_user['user_link'].'" target="_blank">
										<img width="50" height="50" src="'.$namecard_user['img_source'].'" alt="petkaw"></a>';
		}
		else
			$var	=	'';

		$jwigetview     =	 str_replace("[IMG]", $var ,$jwigetview);

		$var='';
		if(isset($namecard_user['user_link'])){
		$var = 	'<a href="'.$namecard_user['user_link'].'" target="_blank">';
										}
		$var .=  $namecard_user['name'];
		if(isset($namecard_user['user_link'])){
		$var .=	'</a>';
		}

		$namecard_user['invURL'] = JRoute::_($namecard_user['invURL']);

		$jwigetview   =	 str_replace("[NAME]", $var ,$jwigetview );
		$namecard_user['invURL'] = "<a href='".$namecard_user['invURL']."'>".JText::_('INVITE_URL')."</a></a>";
		$jwigetview 	    =	 str_replace("[INVURL]", $namecard_user['invURL'],$jwigetview );

		echo $jwigetview;

		exit;
}

if($this->oluser)
{
	$uid = $this->oluser->id;
?>
 		<script language="JavaScript">

 		function displayUrlFormat() {

 					var form = document.namecardForm;
					var siteUrl = "<?php echo JURI::base();?>";

					for (index=0; index < form.templates.length; index++)
					{
						if (form.templates[index].checked)
						{
							var radioValue = form.templates[index].value;
							break;
						}
					}

					frameUrl = '<script src="'+siteUrl+'media/com_invitex/js/namecard.js';
					frameUrl +="?pid="+"<?php echo $uid ?>";
					frameUrl +='&namecard_template='+radioValue;
					frameUrl +="&itemid="+"<?php echo $itemid ?>";
					frameUrl +='&surl='+siteUrl;
					var urlFormat = document.getElementById('url_format');
					frameUrl +='" type="text/javascript">';
					frameUrl +='</';
					frameUrl +='script>';
					frameUrl +='<div id="example-widget-container">';
					frameUrl +='</';
					frameUrl +='div>';
					urlFormat.value = frameUrl;
			}

 		</script>


<div class="<?php echo INVITEX_WRAPPER_CLASS;?>">
 <form name="namecardForm" class="namecardForm" action="" method="post">
<?php
	$tmpls	=	$this->tmpls;
	$namecard_user	=		$this->model->getnamecardinfo($uid);

	$directory = JPATH_SITE.'/components/com_invitex/views/namecard/namecard_templates';
	$var	=	'';
	foreach($tmpls as $tmpl)
	{
			$raw_tmpl= JFile::read($directory.'/'.$tmpl);

			if(isset($namecard_user['img_source'])){
					$var = '<a  href="'.$namecard_user['user_link'].'" target="_blank">
						<img width="50" height="50" src="'.$namecard_user['img_source'].'" alt="petkaw"></a>';
				}
				else
					$var	=	'';

				$raw_tmpl	    =	 str_replace("[IMG]", $var ,$raw_tmpl);

				$var='';
				if(isset($namecard_user['user_link'])){

				$namecard_user['user_link'] = JRoute::_($namecard_user['user_link']);

				$var = 	'<a href="'.$namecard_user['user_link'].'" target="_blank">';
												}
				$var .=  $namecard_user['name'];
				if(isset($namecard_user['user_link'])){
				$var .=	'</a>';
				}

				$raw_tmpl  =	 str_replace("[NAME]", $var ,$raw_tmpl);

				$inv_url='<a href="'.$namecard_user['invURL'].'"target="_blank"> InvitationURL </a>';
				$raw_tmpl	    =	 str_replace("[INVURL]", $inv_url ,$raw_tmpl);
				$raw_tmpl	.=	"<div style='clear:both'></div><br />";
				$final[]	=	$raw_tmpl;
	}
	?>

	<div class='page-header'><h2><?php echo JText::_('INV_NAMECARD_HEADING');?></h2></div>
 	<?php
			foreach($final as $t)
			{
					echo $t;
			}

		?>
		<table width="100%">
			<tbody>
				<tr>
					<td>
						<input type="button" onclick="displayUrlFormat();" value="Generate Code" class="btn btn-primary btn-large">
					</td>
				</tr>
				<tr>
					<td>
						<div>
						<textarea rows="10" cols="80" id="url_format" name="url_format"></textarea>
						</div>
						</td>
				</tr>
			</tbody>
		</table>
	</form>
	</div>
 <?php
}
