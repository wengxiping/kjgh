<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die( 'Restricted access' );
?>
<script>
function show_page(r_link)
{
	window.location = r_link;
}
</script>
<?php
$layout = JFactory::getApplication()->input->get('layout','default');

if (JFactory::getApplication()->input->get('rout') != 'preview')
{
	$itemid = $this->invhelperObj->getitemid('index.php?option=com_invitex&view=invites');
	$title = JText::_('LOGIN_TITLE');
	$desc = '';

	if (JFactory::getApplication()->input->get('invite_anywhere') == '1')
	{
		$type_data = $this->typedata;
		$title = $type_data->name;
		$desc = $type_data->description;
		$user = JFactory::getUser();
	}

	if(JVERSION<3.0)
	{
	?>
	<div class=""></div>
	<?php
	}
	?>

	<div class="row-fluid page-header invitex_title_inv">
	<?php
		echo $desc;
	?>
	</div>
	<?php
	if (JFactory::getApplication()->input->get('invite_anywhere') != '1')
	{
		if ($this->oluser || $this->isguest == 1)
		{
			$limit_data = $this->limit_data;
		}

		$limit = 0;

		if (!isset($limit_data->limit))
		{
			$limit = $this->invitex_params->get("per_user_invitation_limit");
		}
		else
		{
			$limit = $limit_data->limit;
		}

		if (!empty($limit_data->invitations_sent) and $limit and $limit >= $limit_data->invitations_sent)
		{
			$invitestobesent = $limit-$limit_data->invitations_sent;
		?>
		<input type="hidden" name="invite_limit" id="invite_limit" value="<?php echo $invitestobesent;?>"/>
		<?php
		}
	}
}

