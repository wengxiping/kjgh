<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @copyright  Copyright (C) 2005 - 2015. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.database.database.mysql');
jimport('joomla.html.parameter');

use Joomla\CMS\Factory;

/**
 * Methods supporting a to create of Ticket Types
 *
 * @since  1.0.0
 */
class InvitexModelInvites extends JModelLegacy
{
    /**
     * Constructor
     *
     * @since 1.5
     */
    public function __construct()
    {
        $this->invhelperObj = new cominvitexHelper;
        $this->invitex_params = $this->invhelperObj->getconfigData();

        if ($this->invitex_params->get('reg_direct') == 'JomSocial') {
            $jspath = JPATH_ROOT . '/components/com_community';

            if (JFolder::exists($jspath)) {
                include_once $jspath . '/libraries/core.php';
                require_once JPATH_ROOT . '/components/com_community/models/inbox.php';
            }
        }

        $mainframe = Factory::getApplication();

        // Get the pagination request variables
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');

        $input = Factory::getApplication()->input;
        $limitstart = $input->get('limitstart', '0', 'INT');

        // In case limit has been changed, adjust limitstart accordingly
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        parent::__construct();
        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
    }

    /**
     * Function to validate email id
     *
     * @param STRING $email email id.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function validate_email($email)
    {
        $valid_domains = array();
        $emaild = explode("@", trim($email));

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if ($this->invitex_params->get('allow_domain_validation') == '1') {
                $valid_domains = $this->getValiddomains();

                if ($valid_domains) {
                    if (in_array($emaild[1], $valid_domains)) {
                        return 1;
                    } else {
                        return -1;
                    }
                } else {
                    return 1;
                }
            } else {
                return 1;
            }
        } else {
            return -1;
        }
    }

    /**
     * FUnction to get list of valid domains
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function getValiddomains()
    {
        if ($this->invitex_params->get('allow_domain_validation') == '1' and $this->invitex_params->get('invite_domains')) {
            $invite_domain_str = $this->invitex_params->get('invite_domains');
            $invite_domain_str = str_replace(" ", "", $invite_domain_str);

            return $invite_domain_array = explode(",", $invite_domain_str);
        }
    }

    // START APIs added by manoj

    /**
     * Function to get API icons
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function getRenderAPIicons()
    {
        $api_config = array();

        if ($this->invitex_params->get('invite_apis')) {
            $input = Factory::getApplication()->input;

            $show_compact_view = $input->get('show_compact_view');
            $invite_method = $input->get('invite_method');
            $api_used = $input->get('api_used');

            if ($api_used) {
                $api_config = array(
                    $api_used
                );
            } else {
                $api_config = $this->invitex_params->get('invite_apis');
            }

            $dispatcher = JDispatcher::getInstance();
            JPluginHelper::importPlugin('techjoomlaAPI');
            $result_old = $dispatcher->trigger('renderPluginHTML', array($api_config));

            foreach ($result_old as $key => $value) {
                if (!empty($value['message_type'])) {
                    $msg_type = $value['message_type'];

                    if ($show_compact_view) {
                        if ($msg_type == 'pm' and $invite_method == 'social_apis') {
                            $result['social_apis'][] = $value;
                        } else {
                            $result[$msg_type . '_apis'][] = $value;
                        }
                    } else {
                        if ($msg_type == 'email' or $msg_type == 'sms') {
                            $result[$msg_type . '_apis'][] = $value;
                        } elseif ($msg_type == 'pm') {
                            $result['social_apis'][] = $value;
                        }
                    }
                }
            }
            return $result;
        } else {
            return;
        }
    }

    /**
     * Function to request token
     *
     * @param STRING $api_used api used.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function getRequestToken($api_used)
    {
        $session = Factory::getSession();
        $input = Factory::getApplication()->input;
        $post = $input->getArray($_POST);

        if (isset($post['guest'])) {
            $session->set('guest_user', $post['guest']);
            $in_itemid = $this->invhelperObj->getitemid('index.php?option=com_invitex&view=invites');
        }

        // Pass the link for which you want the ItemId.

        // Pass without JRoute as Some API plugins adds its own parameter to the link
        $callback = 'index.php?option=com_invitex&controller=invites&task=get_access_token&Itemid=' . $in_itemid;

        /* $callback =
		 *  JUri::root().substr(JRoute::_('index.php?option=com_invitex&controller=invites&task=get_a
		 * ccess_token&Itemid='.$in_itemid,false),strlen(JUri::base(true))+1); */

        /* $callback = JRoute::_('index.php?option=com_invitex&controller=invites&task=get_access_token&Itemid=' . $in_itemid, false, -1);*/
        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('techjoomlaAPI', $api_used);
        $grt_response = $dispatcher->trigger('get_request_token', array($callback));

        if (!$grt_response[0]) {
            return false;
        }
    }

    /**
     * FUnction to get access token
     *
     * @param STRING $get token.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function getAccessToken($get)
    {
        // Pass the link for which you want the ItemId.
        $in_itemid = $this->invhelperObj->getitemid('index.php?option=com_invitex&view=invites');
        $client = "invitex";
        $urlsubstring = "index.php?option=com_invitex&controller=invites&task=get_access_token&Itemid=";
        $callback = JUri::root() . substr(JRoute::_($urlsubstring . $in_itemid, false), strlen(JUri::base(true)) + 1);

        /* $callback = JRoute::_("index.php?option=com_invitex&controller=invites&task=get_access_token&Itemid=" . $in_itemid, false, -1);*/
        $session = Factory::getSession();
        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('techjoomlaAPI', $session->get('api_used'));
        $grt_response = $dispatcher->trigger('get_access_token', array($get, $client, $callback));

        if (!$grt_response[0]) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Function to get contacts
     *
     * @param INT $offset offset number.
     *
     * @param INT $limit import limit.
     *
     * @param INT $get get.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function getContacts($offset = 0, $limit = 0, $get = '')
    {
        $session = Factory::getSession();
        $session->set('rout', '');
        $session->set('invite_mails', '');
        $session->set('unsubscribe_mails', '');
        $session->set('registered_mails', '');
        $session->set('already_invited_mails', '');

        if ($this->invitex_params->get('enb_load_more')) {
            $limit = $this->invitex_params->get('contacts_at_first_instance');
        }

        $mainframe = Factory::getApplication();
        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('techjoomlaAPI', $session->get('api_used'));
        $contacts = $dispatcher->trigger($session->get('api_used') . 'get_contacts', array($offset, $limit, $get));

        if ($session->get('api_message_type') == 'email') {
            $seperatedmails = $this->seperate_APIemails($contacts[0]);
            $invite_mail = $seperatedmails['invite_mails'];
            $b_mail = $seperatedmails['b_mail'];
            $r_mail = $seperatedmails['r_mail'];
            $i_mail = $seperatedmails['i_mail'];

            $cnt_b_mail = count($b_mail);
            $cnt_r_mail = count($r_mail);
            $cnt_i_mail = count($i_mail);

            $cnt_invite_mail = count($invite_mail);

            if ($cnt_invite_mail > 0) {
                $session->set('invite_mails', $invite_mail);
            }

            if ($cnt_b_mail > 0) {
                $session->set('unsubscribe_mails', $b_mail);
            }

            if ($cnt_r_mail > 0) {
                $session->set('registered_mails', $r_mail);
            }

            if ($cnt_i_mail > 0) {
                $session->set('already_invited_mails', $i_mail);
            }

            if ($session->get('invite_mails')) {
                $this->manageImportedAPIEmails();
            }

            return 1;
        }

        return $contacts[0];
    }

    /**
     * Function to seperate API emails
     *
     * @param STRING $imported_data imported data.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function seperate_APIemails($imported_data)
    {
        $session = Factory::getSession();
        $r_mail = $invite_mail = $b_mail = $i_mail = $contacts = array();
        $registered_emails = array_map('trim', $this->getRegistered());
        $unsubscribe_emails = array_map('trim', $this->getBlocked());
        $already_invited_emails = array_map('trim', $this->getalreadyInvited());

        if ($session->get('invite_anywhere')) {
            $contacts['invite_mails'] = $imported_data;
        } else {
            $k = $p = $r = $i = 0;

            foreach ($imported_data as $ind => $obj) {
                $flag = 0;
                $flag1 = 0;
                $flag2 = 0;

                if ($this->validate_email($obj->id) == '1') {
                    if (in_array($obj->id, $registered_emails)) {
                        $r_mail[$k++] = $obj->id;
                        $flag = 1;
                    } elseif (in_array($obj->id, $unsubscribe_emails)) {
                        $b_mail[$p++] = $obj->id;
                        $flag1 = 1;
                    } elseif (in_array($obj->id, $already_invited_emails)) {
                        $i_mail[$r++] = $obj->id;
                        $flag2 = 1;
                    }
                } else {
                    $flag = 1;
                    $flag1 = 1;
                    $flag2 = 0;
                }

                if ($flag == 0 && $flag1 == 0 && $flag2 == 0) {
                    $invite_mail[$i] = new StdClass;
                    $invite_mail[$i]->id = $obj->id;
                    $invite_mail[$i]->name = '';

                    if (isset($obj->name)) {
                        $invite_mail[$i]->name = $obj->name;
                    }

                    $invite_mail[$i++]->picture_url = $obj->picture_url;
                }
            }

            $contacts['invite_mails'] = $invite_mail;
        }

        $contacts['b_mail'] = $b_mail;
        $contacts['r_mail'] = $r_mail;
        $contacts['i_mail'] = $i_mail;

        return $contacts;
    }

    /**
     * FUnction to manage imported emial via API
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function manageImportedAPIEmails()
    {
        $session = Factory::getSession();

        if ($this->invitex_params->get('store_contact')) {
            $invitemails = $session->get('invite_mails');
            $db = Factory::getDbo();
            $ol_uid = $this->invhelperObj->getUserID();

            foreach ($invitemails as $ind => $obj) {
                // Get a db connection.
                $db = Factory::getDbo();

                // Create a new query object.
                $query = $db->getQuery(true);

                // Select all records from the user profile table where key begins with "custom.".
                // Order it by the ordering field.
                $query->select($db->quoteName(array('id', 'importedcount', 'importedby')));
                $query->from($db->quoteName('#__invitex_stored_emails'));
                $query->where($db->quoteName('email') . ' = ' . $db->quote($obj->id));

                // Reset the query using our newly populated query object.
                $db->setQuery($query);

                // Load the results as a list of stdClass objects (see later for more options on retrieving data).
                $res = $db->loadObject();

                if ($res) {
                    $importedby = '';
                    $importedcount = $res->importedcount;

                    if ($res->importedby) {
                        $importedby = $res->importedby;

                        if (!in_array($ol_uid, explode(',', $res->importedby))) {
                            $importedby .= ',' . $ol_uid;
                            $importedcount = $res->importedcount + 1;
                        }
                    }

                    $update_data = new stdClass;
                    $update_data->id = $res->id;
                    $update_data->name = $obj->name;
                    $update_data->importedby = $importedby;
                    $update_data->importedcount = $importedcount;
                    $db->updateObject('#__invitex_stored_emails', $update_data, 'id');
                } else {
                    $insert_obj = new stdClass;
                    $insert_obj->email = $obj->id;
                    $insert_obj->name = $obj->name;
                    $insert_obj->importedby = $ol_uid;
                    $insert_obj->importedcount = 1;
                    $db->insertObject('#__invitex_stored_emails', $insert_obj);
                }
            }

            return true;
        }
    }

    /**
     * Function to get message limit
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function get_send_message_limit()
    {
        $session = Factory::getSession();
        $plugin = JPluginHelper::getPlugin('techjoomlaAPI', $session->get('api_used'));

        if (isset($plugin->params)) {
            $params = json_decode($plugin->params);
            $param = $params->no_allowed_invites;

            return $param;
        }
    }

    /**
     * FUnction to store data in inports table
     *
     * @param STRING $data data.
     *
     * @param INT $contact_count count of contacts.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function store_imports_table($data, $contact_count)
    {
        $db = Factory::getDbo();
        $session = Factory::getSession();
        $ol_uid = $this->invhelperObj->getUserID();
        $user = Factory::getUser();
        $mainframe = Factory::getApplication();

        // Check if user has given consent to store contact detail and send invitation
        $tncAccepted = $session->get('tj_send_invitations_consent');
        $invitationTermsAndConditions = $this->invitex_params->get('invitationTermsAndConditions', '0');
        $tNcArticleId = $this->invitex_params->get('tNcArticleId', '0');

        if (!empty($invitationTermsAndConditions) && !empty($tNcArticleId)) {
            if (empty($tncAccepted)) {
                $mainframe->enqueueMessage(JText::_("COM_INVITEX_PRIVACY_CONSENT_ERROR_MSG"), 'error');

                return false;
            }
        }

        $invite_type = $invite_url = $invite_type_tag = $catch_act = "";
        $row = $this->getTable();

        if ($ol_uid) {
            $row->inviter_id = $ol_uid;
        } else {
            $row->inviter_id = 0;
        }

        if ($session->get('api_used')) {
            $row->provider = "api";

            // $data['email_box'];
            $row->provider_email = $session->get('api_used');
            $row->message_type = $session->get('api_message_type');
        } else {
            $row->provider_email = $session->get('email_box');
            $row->provider = $session->get('provider_box');
            $row->message_type = $data['message_type'];

            if (empty($row->message_type)) {
                $row->message_type = $session->get('message_type');
            }
        }

        if (isset($data['personal_message'])) {
            $row->message = stripslashes($data['personal_message']);
        }

        if ($session->get('invite_anywhere')) {
            $invite_type = (INT)$session->get('invite_type');
            $invite_url = $session->get('invite_url');
            $catch_act = $session->get('catch_act');
            $invite_type_tag = $session->get('invite_tag');
        }

        $row->invites_count = $contact_count;
        $row->date = time();

        // Added for invite anywhere
        $row->invite_type = $invite_type;

        // Added for invite anywhere
        $row->invite_url = $invite_url;

        // Added for invite anywhere
        $row->catch_act = $catch_act;

        // Added for invite anywhere
        $row->invite_type_tag = $invite_type_tag;

        if (!$row->bind($data)) {
            echo $this->setError($this->_db->getErrorMsg());

            return false;
        }

        if (!$row->check()) {
            echo $this->setError($this->_db->getErrorMsg());

            return false;
        }

        if (!$row->store()) {
            echo $this->setError($this->_db->getErrorMsg());

            return false;
        }

        // Add consent entry
        if (!empty($invitationTermsAndConditions) && !empty($tNcArticleId) && !empty($tncAccepted)) {
            // Load privacy and model
            JLoader::import('tjprivacy', JPATH_SITE . '/components/com_tjprivacy/models');

            $userPrivacyData = array();
            $userPrivacyData['client'] = 'com_invitex.invites';
            $userPrivacyData['client_id'] = $row->id;
            $userPrivacyData['user_id'] = $user->id;
            $userPrivacyData['purpose'] = JText::_('COM_INVITEX_USER_PRIVACY_TERMS_PURPOSE_FOR_SENDING_INVITES');
            $userPrivacyData['accepted'] = 1;
            $userPrivacyData['date'] = Factory::getDate('now')->toSQL();

            $tjprivacyModelObj = JModelLegacy::getInstance('tjprivacy', 'TjprivacyModel');
            $result = $tjprivacyModelObj->save($userPrivacyData);

            if (!empty($result)) {
                $session->set('tj_send_invitations_consent', 0);
            }
        }

        return $row->id;
    }

    /**
     * function to queue invites
     *
     * @param STRING $data invites list.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function queup_invites($data)
    {
        $mainframe = Factory::getApplication();
        $db = Factory::getDbo();
        $session = Factory::getSession();
        $ol_uid = $this->invhelperObj->getUserID();

        if ($ol_uid) {
            $invitor = $ol_uid;
        } else {
            $invitor = 0;
        }

        $plug_type = "";
        $resends = '';
        $additional_msg = '';

        $import_id = $this->store_imports_table($data, count($data['contacts']));

        if (empty($import_id)) {
            return false;
        }

        // Store token for particular user in a new table
        if ($session->get('api_message_type') == 'pm') {
            $token_id = $this->save_token_for_paticular_user($import_id, $invitor);
        } elseif ($session->get('api_message_type') == 'sms') {
            // Get contact in format required
            $formated_contact = $this->formated_contact($data['contacts']);
            $data['contacts'] = $formated_contact;
        }

        $invitee_data = $data['contacts'];
        $datetime = time();
        $validity = $this->invitex_params->get('expiry');
        $expiry = $datetime + ($validity * 60 * 60 * 24);
        $new_invite_mails = $session->get('new_invite_mails');
        $phone = 0;
        foreach ($invitee_data as $name => $id) {
            $emailtable = new TableInvitesEmails($db);

            foreach ($new_invite_mails as $val) {
                if ($val['email'] == $id) {
                    $phone = $val['phone'];
                }
            }
            $insert_data = array(
                'import_id' => $import_id,
                'inviter_id' => $invitor,
                'invitee_email' => $id,
                'invitee_phone' => $phone,
                'guest' => $data['guest'],
                'invitee_name' => $name,
                'resend_count' => 0,
                'expires' => $expiry
            );

            if (!$emailtable->bind($insert_data)) {
                $this->setError($this->_db->getErrorMsg());

                return false;
            }

            if (!$emailtable->check()) {
                $this->setError($this->_db->getErrorMsg());

                return false;
            }

            if (!$emailtable->store()) {
                $this->setError($this->_db->getErrorMsg());

                return false;
            }

            // On after queup_invites

            $dispatcher = JDispatcher::getInstance();
            JPluginHelper::importPlugin('system');
            $result = $dispatcher->trigger('onAfterQueupInvities', array($emailtable->id));
        }

        // End Of For

        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('system');
        $result = $dispatcher->trigger('onAfterQueupDone', array($import_id));

        return true;
    }

    /**
     * Function called to get the contact array in required syantax for sms
     *
     * @param bool $contact_sms SMS content.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function formated_contact($contact_sms)
    {
        $formated_contact = array();

        foreach ($contact_sms as $data_contact) {
            $sms_user_name = $data_contact['sms_user_name'];
            $phno = $data_contact['sms_user_phno_code'] . "-" . $data_contact['sms_user_phno'];
            $formated_contact[$sms_user_name] = $phno;
        }

        return $formated_contact;
    }

    /**
     * Function to store invites
     *
     * @param STRING $data Data to be stored.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function store_invites($data)
    {
        $session = Factory::getSession();
        $data['guest'] = $session->get('guest_user');


        if (!isset($data['captcha'])) {
            $return = $this->queup_invites($data);

            if (empty($return)) {
                return false;
            }
        } else {
            $invitee_data = $session->get('inv_orkut_contacts');
        }

        if (($session->get('rout') == 'OI_import'
                && $session->get('import_type') == 'social')
            || ($session->get('api_used')
                && $session->get('api_message_type') == 'pm')
            || ($session->get('rout') == 'inv_js_messaging')) {
            if ($session->get('rout') == 'inv_js_messaging') {
                $pm_invite_method = 'inv_js_messaging';
            } elseif ($session->get('import_type') == 'social') {
                $pm_invite_method = 'OI_social';
            } else {
                $pm_invite_method = 'API';
            }

            $send_chk = $this->sendPM($data, $data['contacts'], $pm_invite_method);

            if ($send_chk > 0) {
                // If($this->invitex_params->get('allow_point_after_invite')==1)
                {
                    // To allocate points after invitation sent
                    $ol_uid = $this->invhelperObj->getUserID();

                    if ($ol_uid) {
                        $dispatcher = JDispatcher::getInstance();
                        JPluginHelper::importPlugin('system');

                        // Call the plugin and get the result
                        $result = $dispatcher->trigger('onAfterinvitesent', array(
                                $ol_uid, $this->invitex_params->get('pt_option'), $this->invitex_params->get('inviter_point_after_invite'))
                        );
                    }
                }

                return 1;
            } else {
                return -1;
            }
        }

        return true;
    }

    /**
     * Function to post invite on wall
     *
     * @param bool $noofcontacts True if you want to return children recursively.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function postOnwall($noofcontacts)
    {
        $jspath = JPATH_ROOT . '/components/com_community';

        if (JFolder::exists($jspath)) {
            $app = Factory::getApplication();
            $sitename = $app->getCfg('sitename');
            $act = new stdClass;
            $act->cmd = 'wall.write';
            $act->actor = $this->invhelperObj->getUserID();
            $act->target = 0;
            $act->title = JText::sprintf('ACTIVITY_STREAM_MESSAGE', $noofcontacts, $sitename);
            $act->content = '';
            $act->app = 'wall';
            $act->cid = 0;
            $act->params = '';

            CFactory::load('libraries', 'activities');

            if (defined('CActivities::COMMENT_SELF')) {
                $act->comment_id = CActivities::COMMENT_SELF;
                $act->comment_type = 'profile.location';
            }

            if (defined('CActivities::LIKE_SELF')) {
                $act->like_id = CActivities::LIKE_SELF;
                $act->like_type = 'profile.location';
            }

            CActivityStream::add($act);
        }
    }

    /**
     * Function to send personal message
     *
     * @param STRING $data message data.
     *
     * @param STRING $invitee_data invitation data.
     *
     * @param STRING $pm_invite_method invite method.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function sendPM($data, $invitee_data, $pm_invite_method)
    {
        $mainframe = Factory::getApplication();
        $session = Factory::getSession();
        $attached_msg = '';
        $invite_type_tag = '';
        $db = Factory::getDbo();

        if (isset($data['personal_message'])) {
            $attached_msg = $data['personal_message'];
        }

        $ol_uid = $this->invhelperObj->getUserID();

        if ($ol_uid) {
            $invitor = $ol_uid;
        } else {
            $invitor = 0;
        }

        if ($session->get('invite_tag')) {
            $invite_type_tag = $session->get('invite_tag');
        }

        /*** as the inviter,attached masg,expiry,invitetype name is going to be same for all
         * create them first and then give to each specific public function **/
        $mail = $this->invhelperObj->buildCommonPM($attached_msg, $invitor, $invite_type_tag);

        if ($pm_invite_method == 'inv_js_messaging') {
            $js_messaging = $this->send_JS_msg($mail, $invitee_data);

            return $js_messaging;
        } elseif ($pm_invite_method == 'OI_social') {
            $oi_response = $this->send_OI_msg($mail, $invitee_data);

            return $oi_response;
        } else {
            $sm_response = 1;
            $dispatcher = JDispatcher::getInstance();
            JPluginHelper::importPlugin('techjoomlaAPI', $session->get('api_used'));

            if ($session->get('api_used') == "plug_techjoomlaAPI_orkut") {
                $cap = array();

                if (isset($data['textcaptcha'])) {
                    $cap['textcaptcha'] = $data['textcaptcha'];
                    $cap['tokencaptcha'] = $data['tokencaptcha'];
                }

                $sm_api_response = $dispatcher->trigger($session->get('api_used') . 'send_message', array($mail, $invitee_data, $cap));

                if ($sm_api_response[0][0]['id'] == '1') {
                    $mainframe->redirect('index.php', "Error sending message");
                } elseif ($sm_api_response[0][0]['id'] == '2') {
                    $itemid = $this->invhelperObj->getitemid('index.php?option=com_invitex&view=invites');
                    $session->set('inv_orkut_contacts', $invitee_data);
                    $session->set('inv_orkut_captcha_token', $sm_api_response[0][0]['captchaToken']);
                    $session->set('inv_orkut_captcha_url', $sm_api_response[0][0]['captchaUrl']);
                    $mainframe->redirect(JRoute::_('index.php?option=com_invitex&view=invites&layout=captcha&Itemid=' . $itemid, false));
                } else {
                    $sm_response = 1;
                }
            } else {
                // Remove this...
                if ($session->get('api_used') == "plug_techjoomlaAPI_facebook") {
                    $sm_response = $dispatcher->trigger($session->get('api_used') . 'send_message', array($mail, $invitee_data));
                }
            }

            return $sm_response;
        }
    }

    // Public function is used to send messang for linkedin and twitter in batches...
    // It runs when the cron job is run.

    /**
     * FUnction to get captcha url
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function getcaptchaURL()
    {
        $session = Factory::getSession();
        $captchaToken = $session->get('inv_orkut_captcha_token');
        $captchaUrl = $session->get('inv_orkut_captcha_url');

        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('techjoomlaAPI', $session->get('api_used'));
        $captcha_image = $dispatcher->trigger('getCaptchaURL', array($captchaUrl));
    }

    /**
     * Function to send jom social message
     *
     * @param STRING $raw_mail raw mail.
     *
     * @param STRING $invitee_data invitation data.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function send_JS_msg($raw_mail, $invitee_data)
    {
        $session = Factory::getSession();
        $db = Factory::getDbo();

        if ($session->get('invite_anywhere')) {
            $inviteType = (INT)$session->get('invite_type');
            $types_res = $this->invhelperObj->types_data($inviteType);
            $template = $types_res->template_text;
        } else {
            $template = $this->invitex_params->get('pm_message_body');
        }

        $sent_cnt = 0;

        foreach ($invitee_data as $invitee_name => $id) {
            $query = "select id from #__invitex_imports_emails
											WHERE invitee_email='$id' AND invitee_name='$invitee_name' order by id DESC LIMIT 1";
            $db->setQuery($query);
            $res = trim($db->loadResult());

            $invite_id = $res;

            $mail = $this->invhelperObj->buildPM($raw_mail, $invitee_name, $invite_id);
            $mail['msg_body'] = $template;
            $msg_PM = $this->invhelperObj->tagreplace($mail);
            $pattern = "#<br\s*/?>#i";
            $replacement = "\n";
            $data['body'] = preg_replace($pattern, $replacement, $msg_PM);

            /* Replacers for subject **/
            $inviteType = (INT)$session->get('invite_type');
            $types_res_data = $this->invhelperObj->types_data($inviteType);
            $raw_subject = $types_res_data->template_text_subject;
            $raw_mail['msg_body'] = $raw_subject;
            $message_subject = $this->invhelperObj->tagreplace($raw_mail);

            $data['subject'] = preg_replace($pattern, $replacement, $message_subject);
            $data['to'] = $id;

            if ($this->invitex_params->get('reg_direct') == 'JomSocial') {
                $msgid = $this->send($data);

                if ($msgid) {
                    // Add user points
                    CFactory::load('libraries', 'userpoints');
                    CFactory::load('libraries', 'notification');
                    CUserPoints::assignPoint('inbox.message.send');

                    // Add notification
                    $params = new CParameter('');
                    $params->set('url', 'index.php?option=com_community&view=inbox&task=read&msgid=' . $msgid);
                    $params->set('message', $data['body']);
                    $params->set('title', $data['subject']);
                    $my = CFactory::getUser();
                    CNotificationLibrary::add(
                        'etype_inbox_create_message', $my->id, $data['to'], JText::sprintf(
                        'COM_COMMUNITY_SENT_YOU_MESSAGE', $my->getDisplayName()
                    ), '', 'inbox.sent', $params
                    );
                }
            }

            if ($this->invitex_params->get('reg_direct') == 'EasySocial') {
                $sender = Factory::getUser();
                $receiver = Factory::getUser($id);

                // Internal notification options
                $systemOptions = array(
                    'uid' => 'accepted_invite',
                    'actor_id' => $sender->id,
                    'target_id' => $id,
                    'type' => 'Invite',
                    'title' => $data['body'],
                    'image' => '',
                    'cmd' => 'notify_invite.abcd',
                    'url' => $mail['message_join']
                );
                $msgid = $this->invhelperObj->sociallibraryobj->sendNotification($sender, $receiver, $data['body'], $systemOptions);
            }

            if ($msgid) {
                $update_data = new stdClass;
                $update_data->invitee_email = $id;
                $update_data->sent = '1';
                $update_data->sent_at = time();
                $update_data->modified = time();
                $db->updateObject('#__invitex_imports_emails', $update_data, 'invitee_email');
                $sent_cnt++;
            }
        }

        if ($sent_cnt > 0) {
            return $sent_cnt;
        } else {
            return -1;
        }
    }

    /**
     * Function to send online message
     *
     * @param STRING $raw_mail raw mail.
     *
     * @param STRING $invitee_data invitation data.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function send_OI_msg($raw_mail, $invitee_data)
    {
        require_once JPATH_SITE . '/components/com_invitex/invitex/openinviter.php';
        $inviter = new openinviter;
        $session = Factory::getSession();
        $db = Factory::getDbo();
        $this->invitex_params = $this->invitex_params;

        if ($session->get('invite_anywhere')) {
            $inviteType = (INT)$session->get('invite_type');
            $types_res = $this->invhelperObj->types_data($inviteType);

            if (($session->get('provider_box') == 'twitter') || ($session->get('api_used') == 'plug_techjoomlaAPI_twitter')) {
                $template = stripslashes($types_res->template_twitter);
            } else {
                $template = stripslashes($types_res->template_text);
            }
        } else {
            if (($session->get('provider_box') == 'twitter') || ($session->get('api_used') == 'plug_techjoomlaAPI_twitter')) {
                $template = stripslashes($this->invitex_params->get('twitter_message_body'));
            } else {
                $template = stripslashes($this->invitex_params->get('pm_message_body'));
            }
        }

        $sent_cnt = 0;

        foreach ($invitee_data as $name => $id) {
            $invitee_email = $id;

            $query = "select id from #__invitex_imports_emails
										WHERE invitee_email='$invitee_email' order by id DESC LIMIT 1";
            $db->setQuery($query);
            $res = trim($db->loadResult());
            $invite_id = md5($res);

            $mail = $this->invhelperObj->buildPM($raw_mail, $name, $invite_id);
            $mail['msg_body'] = $template;
            $msg_PM = $this->invhelperObj->tagreplace($mail);

            if (isset($msg_PM['message'])) {
                $message = array(
                    'subject' => '',
                    'body' => $msg_PM,
                    'attachment' => "\n\rAttached message: \n\r" . $msg_PM['message']
                );
            } else {
                $message = array(
                    'subject' => '',
                    'body' => $msg_PM,
                    'attachment' => "\n\rAttached message:"
                );
            }

            $selected_contact[$id] = $name;
            $oi_session_id = $session->get('oi_session_id');
            $inviter->startPlugin($session->get('provider_box'));
            $sendMessage = $inviter->sendMessage($oi_session_id, $message, $selected_contact);

            if (!($sendMessage === false)) {
                $update_data = new stdClass;
                $update_data->invitee_email = $invitee_email;
                $update_data->sent = '1';
                $update_data->sent_at = time();
                $update_data->modified = time();
                $db->updateObject('#__invitex_imports_emails', $update_data, 'invitee_email');
                $sent_cnt++;
            }
        }

        if ($sent_cnt > 0) {
            return $sent_cnt;
        } else {
            return -1;
        }
    }

    /**
     * Function to get personal message template
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function getTemplate_PM()
    {
        $session = Factory::getSession();
        $this->invitex_params = $this->invitex_params;
        $input = Factory::getApplication()->input;

        $message_type = stripslashes($input->get('msg_type', '', 'STRING'));

        if ($session->get('invite_anywhere')) {
            $inviteType = $session->get('invite_type');
            $types_res = $this->invhelperObj->types_data($inviteType);

            if ($session->get('api_used') == 'plug_techjoomlaAPI_facebook') {
                $plugin = JPluginHelper::getPlugin('techjoomlaAPI', 'plug_techjoomlaAPI_facebook');
                $params = new JParameter($plugin->params);

                if ($params->get('fb_api_action') == '0') {
                    $template = stripslashes($types_res->template_text);
                } else {
                    $template = stripslashes($types_res->common_template_text);
                }
            } elseif ($input->get('api_used') == 'plug_techjoomlaAPI_orkut') {
                $template = stripslashes($types_res->common_template_text);
            } elseif ($message_type == 'sms') {
                $template = stripslashes($types_res->template_sms);
            } else {
                if (($input->get('provider_box') == 'twitter') || ($input->get('api_used') == 'plug_techjoomlaAPI_twitter')) {
                    $template = stripslashes($types_res->template_twitter);
                } else {
                    $template = stripslashes($types_res->template_text);
                }
            }
        } else {
            if ($input->get('api_used') == 'plug_techjoomlaAPI_facebook') {
                $template = stripslashes($this->invitex_params->get('fb_request_body'));
            } elseif ($session->get('api_used') == 'plug_techjoomlaAPI_orkut') {
                $template = stripslashes($this->invitex_params->get('pm_message_body_no_replace'));
            } elseif ($message_type == 'sms') {
                $template = stripslashes($this->invitex_params->get('sms_message_body'));
            } else {
                if (($input->get('provider_box') == 'twitter') || ($input->get('api_used') == 'plug_techjoomlaAPI_twitter')) {
                    $template = stripslashes($this->invitex_params->get('twitter_message_body'));
                } else {
                    $template = stripslashes($this->invitex_params->get('pm_message_body'));
                }
            }
        }

        return nl2br($template);
    }

    /**
     * Redefine the function an add some properties to make the styling more easy
     *
     * @param STRING $email Object-email id of the person who is invited
     *
     * @param STRING $to_direct The Integration component
     *
     * @param STRING $idof the id of invitee user
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function Getpplwhohaveinvitedbefore($email, $to_direct, $idof)
    {
        $db = Factory::getDbo();

        // IF GUEST USER
        $where = '';

        if ($idof != 0) {
            $where = " AND u.id <> $idof ";
        }

        $base = str_replace('administrator/', '', JUri::base());
        $img_path = $base . "/images/comprofiler";
        $q_mid = '';

        $q = "SELECT DISTINCT iie.inviter_id, u.name, u.username   FROM #__invitex_imports_emails as iie
					LEFT JOIN  #__users as u  ON iie.inviter_id = u.id
					WHERE iie.invitee_email = " . $db->quote($email) . $where;
        $db->setQuery($q);
        $pplinviters = $db->loadObjectList();

        $Itemid = '';
        $return = '';

        if (!empty($pplinviters)) {
            $return = JText::_('PWIU') . ': <br><br><table><tr>';

            foreach ($pplinviters as $ppl) {
                $name = "";

                if ($ppl->name) {
                    $name = $ppl->name;
                } else {
                    $name = $ppl->username;
                }

                if (!empty($ppl->inviter_id)) {
                    $link = $this->invhelperObj->getprofilelink($to_direct, Factory::getUser($ppl->inviter_id));
                } else {
                    $link = "#";
                    $name = $ppl->guest;
                }

                $avatar = $this->invhelperObj->getprofileavatar($to_direct, Factory::getUser($ppl->inviter_id));

                $uimage = "<img src=" . $avatar . " height='60' width='60'/>";

                $return .= '<td style="margin: 0px; padding: 0px 3px 10px 0px; font-family: arial,sans-serif;">
															<div>
																<a href="' . $link . '" target="_blank">
																' . $uimage . '</a>
															</div>
												</td>
												<td style="margin: 0px; padding: 0px 0px 10px; font-family: arial,sans-serif; font-size: 11px; color: #999999;" width="95">
													<span style="font-size: 11px; color: #3b5998;">' . $name . '</span>
												</td>';
            }

            $return .= '</tr></table>';
        }

        return $return;
    }

    /**
     * Cron function
     *
     * @param bool $plug_call plug call
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function mailto($plug_call)
    {
        jimport('joomla.filesystem.file');

        $database = Factory::getDbo();
        $private_key = $this->invitex_params->get("private_key_cronjob");

        $sociallibraryobj = $this->invhelperObj->getSocialLibraryObject();

        $input = Factory::getApplication()->input;
        $private_keyinurl = $input->get('pkey', '', 'STRING');

        if ($private_key != $private_keyinurl) {
            if ($plug_call != 1) {
                echo "You are Not authorized To send mails";
            }
        } else {
            if ($plug_call != 1) {
                echo "*****************************<br />";
                echo "Sending Email Invites <br />";
                echo "----------------------------- <br />";
            }

            $numberofmails = $this->invitex_params->get("inviter_percent");
            $enable_batch = $this->invitex_params->get("enb_batch");

            $query = "SELECT e.id as
			refid,e.guest,e.invitee_email,e.invitee_name,e.resend_count,e.inviter_id as id,
			u.email as inviter_email, i.message,
			e.expires,i.invite_type,i.invite_url,i.invite_type_tag
						FROM #__invitex_imports_emails AS e
						LEFT JOIN #__invitex_imports AS i ON e.import_id = i.id
						LEFT JOIN #__users AS u ON i.inviter_id = u.id
						WHERE i.message_type='email' AND e.unsubscribe=0 AND e.sent=0 OR e.resend=1";

            if ($enable_batch == 1) {
                $query .= " LIMIT {$numberofmails}";
            }

            $database->setQuery($query);
            $connections_invited = $database->loadObjectList();
            $inviter_array = array();

            $registered_emails = array_map('trim', $this->getRegistered());

            if ($connections_invited) {
                if ($this->invitex_params->get("enable_log") == 1) {
                    // OPEN log.php to write
                    $logfile_path = JPATH_ADMINISTRATOR . "/components/com_invitex/log.txt";
                    $old_content = file_get_contents($logfile_path);
                    $today = 'Start ' . date('Y-m-d H:i:s');
                    $this->log[] = JText::sprintf($today);
                }

                $i = 0;

                foreach ($connections_invited as $index => $connection_data) {
                    $invitee_mail = trim($connection_data->invitee_email);

                    if (!filter_var($invitee_mail, FILTER_VALIDATE_EMAIL)) {
                        $this->log[] = JText::sprintf($invitee_mail . "\t" . "Nonvalid email address");

                        if ($plug_call != 1) {
                            echo $invitee_mail . " " . "Nonvalid email address" . "<br />";
                        }
                    } else {
                        if ($connection_data->invite_type == 0 || $connection_data->invite_type == '') {
                            if (($invitee_mail) and ($registered_emails) and in_array($invitee_mail, $registered_emails)) {
                                continue;
                            }
                        }

                        $refid = $connection_data->refid;

                        if ($connection_data->id) {
                            $inviter_id = $connection_data->id;

                            if (!array_key_exists($inviter_id, $inviter_array)) {
                                $inviter_array[$inviter_id] = 0;
                            }
                        } else {
                            // If GUEST USER
                            $inviter_id = 0;
                        }

                        $invitee_name = $connection_data->invitee_name;
                        $expires = $connection_data->expires;
                        $message = $connection_data->message;
                        $invite_type = $connection_data->invite_type;
                        $invite_type_tag = $connection_data->invite_type_tag;

                        // If GUEST USER
                        if ($inviter_id != 0) {
                            $fromname = Factory::getUser($inviter_id)->name;
                        } else {
                            $fromname = $connection_data->guest;
                        }

                        $mail = $this->invhelperObj->getMailtagsinarray(
                            $inviter_id, $refid, $message, $invitee_mail, $invitee_name, $expires,
                            $invite_type, $invite_type_tag
                        );

                        // If Guest user
                        if ($inviter_id == 0) {
                            $mail['inviter_name'] = $connection_data->guest;
                            $mail['inviter_uname'] = $connection_data->guest;
                        } else {
                            // $mail['inviter_profileurl'] = $sociallibraryobj->getProfileUrl(Factory::getUser($inviter_id));
                        }

                        $mail['msg_body'] = $this->invhelperObj->get_message_template($connection_data->invite_type, 'mail');
                        $message_body = $this->invhelperObj->tagreplace($mail);

                        /* replacers for subject **/
                        /*$mail=$this->invhelperObj->getMailtagsinarray($inviter_id,$refid,$messag
						 * e,$invitee_mail,$invitee_name,$expires,$invite_type,$invite_type_tag);*/
                        $mail['msg_body'] = $this->invhelperObj->get_message_subject($connection_data->invite_type, 'mail');
                        $message_subject = $this->invhelperObj->tagreplace($mail);

                        /* adding JUri::root to the src of the img
						$img_path = 'img src="'.JUri::root();
						$message_body=str_replace( 'img src="', $img_path, $message_body );
						$message_body=str_replace( "background: url('", "background: url('".JUri::root(), $message_body ); */

                        // START changing content using plugin
                        $dispatcher = JDispatcher::getInstance();
                        JPluginHelper::importPlugin('invitex');

                        // Call the plugin and get the result
                        $result = $dispatcher->trigger('onPrepareInvitexEmail', array($message_body, $connection_data));

                        if (!empty($result)) {
                            $message_body = $result[0];
                        }
                        // END changing content using plugin

                        // START Invitex Sample development
                        $dispatcher = JDispatcher::getInstance();
                        JPluginHelper::importPlugin('system');

                        // Call the plugin and get the result
                        $result = $dispatcher->trigger('onPrepareInvitexEmail', array($message_body, $connection_data));

                        if (!empty($result)) {
                            $message_body = $result[0];
                        }

                        // END Invitex Sample development

                        $cssdata = "";
                        $cssfile = JPATH_SITE . "/media/com_invitex/css/invitex_mail.css";
                        $cssdata .= file_get_contents($cssfile);

                        // START JMA PLUGIN SUPPORT CODE
                        $dispatcher = JDispatcher::getInstance();
                        JPluginHelper::importPlugin('system');
                        $result = $dispatcher->trigger('onPrepareEmailJmaIntegration', array($message_body, $cssdata, $inviter_id));

                        if ($result) {
                            $message_body = $result[0]['message_body'];
                            $cssdata = $result[0]['cssdata'];
                        }

                        // END JMA PLUGIN SUPPORT CODE
                        $emorgdata = $this->getEmorgify($message_body, $cssdata);
                        $from_address = $this->invitex_params->get('from_address');

                        if ($from_address) {
                            $frommail = $from_address;
                        } else {
                            $frommail = trim($connection_data->inviter_email);
                        }

                        $timeofevt = gmdate(DATE_RFC822);

                        $mail = Factory::getMailer();

                        try {
                            $boolean = $mail->sendMail($frommail, $fromname, $invitee_mail, $message_subject, $emorgdata, 1, null, null, null, $frommail, $fromname);
                        } catch (Exception $e) {
                            echo $e->getMessage() . "\n";
                        }

                        // If mail is sent i.e.no smtp connection error
                        if ($boolean == "1") {
                            $data = new stdClass;
                            $data->invitee_email = $invitee_mail;
                            $data->sent = 1;
                            $data->sent_at = time();
                            $data->modified = time();
                            $data->resend = 0;
                            $data->unsubscribe = 0;
                            $data->resend_count = $connection_data->resend_count;

                            $database->updateObject('#__invitex_imports_emails', $data, 'invitee_email');

                            if ($plug_call != 1) {
                                echo " " . $timeofevt . " " . $invitee_mail . "<br>";
                            }

                            // START Invitex Sample development
                            // TODO need to fix this in next version refer to plugin 'plug_sys_invitex' code

                            /*$dispatcher = JDispatcher::getInstance();
							JPluginHelper::importPlugin('system');
							$result = $dispatcher->trigger('onAfterinvitesent', array());
							*/

                            // END Invitex Sample development

                            $this->log[] = JText::sprintf($timeofevt . "\t" . $invitee_mail . "\t" . '1');
                            $i++;

                            if ($inviter_id != 0) {
                                $inviter_array[$inviter_id] = $inviter_array[$inviter_id] + 1;
                            }
                        } else {
                            $this->log[] = JText::sprintf($timeofevt . "\t" . $invitee_mail . "\t" . '0');
                        }
                    }
                }

                // Write to log file only if log is enabled from configuration
                if ($this->invitex_params->get("enable_log") == 1) {
                    // START:write to log file - moved out of loop BY MANOJ
                    $file_log = implode("\n", $this->log);
                    $file_log = $old_content . "\n" . $file_log;
                    JFile::write($logfile_path, $file_log);
                }

                // END:write to log file - moved out of loop BY MANOJ
                if ($plug_call != 1) {
                    echo "Mail has been sent to " . $i . " recipients.<br />";
                }

                if ($i > 0) {
                    // Alocate point for the inviter
                    // If($this->invitex_params->get('allow_point_after_invite')==1)
                    {
                        if ($inviter_array) {
                            foreach ($inviter_array as $inviter => $count_people) {
                                if ($inviter != 0 || $inviter != '') {
                                    $dispatcher = JDispatcher::getInstance();
                                    JPluginHelper::importPlugin('system');
                                    $result = $dispatcher->trigger('onAfterinvitesent', array(
                                            $inviter, $this->invitex_params->get("pt_option"), $this->invitex_params->get("inviter_point_after_invite"), $count_people
                                        )
                                    );
                                }
                            }
                        }
                    }
                }
            } else {
                if ($plug_call != 1) {
                    echo JText::_('NO_INVITES') . "<br />";
                }
            }

            // For integration stream
            $this->call_activity_stream($inviter_array);
            $this->send_message_cron($plug_call);
            $this->send_reminder($plug_call, 'manual');
            $this->send_reminder($plug_call, 'automate');
            $this->send_friendsEmail($plug_call);
        }

        return 1;
    }

    /**
     * Function to send messages from cron
     *
     * @param STRING $plug_call plug call.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function send_message_cron($plug_call)
    {
        require JPATH_SITE . '/components/com_invitex/helper.php';
        $cominvitexHelper = new cominvitexHelper;

        /* Get invitee data as per original format.
		api used ---get that from table....
		step 1 => get details from import table.
		*/
        $inviter_array = array();
        $remaining_msg_to_send = array();
        $send_cron_array = array();
        $errors_cron_array = array();
        $this->invitex_params = $this->invitex_params;

        $db = Factory::getDbo();

        $apis_with_cron[] = 'plug_techjoomlaAPI_twitter';
        $apis_with_cron[] = 'plug_techjoomlaAPI_linkedin';
        $apis_with_cron[] = 'plug_techjoomlaAPI_sms';

        $api_availabel_array = "'" . implode("','", $apis_with_cron) . "'";

        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('techjoomlaAPI');

        $query = 'SELECT i.id,i.provider_email FROM #__invitex_imports_emails as ie ,
		#__invitex_imports as i WHERE i.provider_email IN (' . $api_availabel_array . ') AND
		ie.sent<>1 AND ie.import_id=i.id group by i.id,i.provider_email';
        $db->setQuery($query);
        $imports = $db->loadObjectList();

        $today = date("Y-m-d");

        foreach ($imports as $import) {
            $limit = '';
            $api = $import->provider_email;
            $indid = $import->id;

            if (!array_key_exists($api, $send_cron_array)) {
                $send_cron_array[$api] = array();
            }

            $plugin = JPluginHelper::getPlugin('techjoomlaAPI', $api);

            $pluginParams = json_decode($plugin->params);

            if (property_exists($pluginParams, 'throttle_limit')) {
                $limit = $pluginParams->throttle_limit;
            }

            if ($limit) {
                // $errors_cron_array[$api]=array();
                $query = "SELECT count(ie.invitee_email) as sent_count FROM
				#__invitex_imports_emails as ie  WHERE DATE(FROM_UNIXTIME(ie.sent_at))= '" .
                    $today . "' AND ie.import_id=" . $indid;
                $db->setQuery($query);
                $sent_cnt_api = $db->loadObject();

                if ($sent_cnt_api->sent_count < $limit) {
                    $msg_sent_to_cnt = $limit - $sent_cnt_api->sent_count;

                    if ($import->provider_email != 'plug_techjoomlaAPI_sms') {
                        $query = "SELECT ie.id as invite_id,ie.inviter_id,ie.invitee_email,ie.invitee_name,i.*,t.token
								FROM #__invitex_imports as i, #__invitex_imports_emails as ie,
								#__invitex_stored_tokens as t
								WHERE ie.sent=0 AND ie.import_id=" . $indid . " AND
								i.provider_email='" . $api . "' AND ie.import_id=i.id AND i.id=t.import_id limit 0," . $msg_sent_to_cnt;
                    } else {
                        $query = "SELECT ie.id as invite_id,ie.inviter_id,ie.invitee_email,ie.invitee_name,i.*
							FROM #__invitex_imports as i, #__invitex_imports_emails as ie
							WHERE ie.sent=0 AND ie.import_id=" . $indid . " AND i.provider_email='" . $api . "' AND ie.import_id=i.id limit 0," . $msg_sent_to_cnt;
                    }

                    $db->setQuery($query);

                    $remaining_msg_to_send = $db->loadobjectlist();

                    $post = array();

                    if ($remaining_msg_to_send) {
                        foreach ($remaining_msg_to_send as $invitee) {
                            $attached_msg = $invitee->message;
                            $inviter_id = $invitee->inviter_id;
                            $invite_type_tag = $invitee->invite_type_tag;

                            $raw_mail = $this->invhelperObj->buildCommonPM($attached_msg, $inviter_id, $invite_type_tag);
                            $mail = $this->invhelperObj->buildPM($raw_mail, $invitee->invitee_name, $invitee->invite_id);

                            $post['invite_type'] = $invitee->invite_type;
                            $post['invitee_email'] = $invitee->invitee_email;

                            // For common sms plugin invitee_email id mapped to mobile_no
                            $post['mobile_no'] = $invitee->invitee_email;

                            if ($import->provider_email != 'plug_techjoomlaAPI_sms') {
                                $post['token'] = $invitee->token;
                            }

                            $res = $dispatcher->trigger($api . 'send_message', array($mail, $post));

                            if (!empty($res[0])) {
                                $response = $res[0];

                                if ($response[0] == 1) {
                                    $update_data = new stdClass;
                                    $update_data->id = $invitee->invite_id;
                                    $update_data->invitee_email = $invitee->invitee_email;
                                    $update_data->sent = '1';
                                    $update_data->sent_at = time();
                                    $db->updateObject('#__invitex_imports_emails', $update_data, 'id');

                                    // If sms plugins...entery in sms_delivery table
                                    if ($import->provider_email == 'plug_techjoomlaAPI_sms') {
                                        $sms_deliver_id = $this->sms_delivery_status($invitee->id, $response[1]);
                                    }

                                    if (!array_key_exists($invitee->inviter_id, $inviter_array)) {
                                        $inviter_array[$invitee->inviter_id] = 0;
                                    }

                                    $inviter_array[$invitee->inviter_id] = $inviter_array[$invitee->inviter_id] + 1;

                                    $send_cron_array[$api][$invitee->inviter_id][] = $invitee->invitee_name;
                                } elseif ($response[0] == -1) {
                                    $errors_cron_array[$api][$invitee->inviter_id][$invitee->invitee_name] = $response[1];
                                }
                            }
                        }
                    }
                }
            }
        }

        // Alocate point for the inviter
        // If($this->invitex_params->get('allow_point_after_invite')==1)
        {
            if ($inviter_array) {
                foreach ($inviter_array as $inviter => $count_people) {
                    if ($inviter != 0 || $inviter != '') {
                        $dispatcher = JDispatcher::getInstance();
                        JPluginHelper::importPlugin('system');
                        $parempointafter = $this->invitex_params->get('inviter_point_after_invite');
                        $result = $dispatcher->trigger('onAfterinvitesent', array($inviter, $this->invitex_params->get('pt_option'), $parempointafter, $count_people));
                    }
                }
            }
        }

        if ($plug_call != 1) {
            if ($send_cron_array) {
                foreach ($send_cron_array as $api => $inviter_details) {
                    if (!empty($send_cron_array[$api])) {
                        echo "**********************************<br />";
                        echo "Sending Invites for " . $api . "<br />";
                        echo "----------------------------- <br />";

                        foreach ($inviter_details as $inviter => $invitee_det) {
                            $inviter_name = Factory::getuser($inviter)->username;

                            foreach ($invitee_det as $invitee) {
                                echo "Invitation message sent to $invitee from " . $inviter_name . " <br />";
                            }
                        }
                    }
                }
            }

            if ($errors_cron_array) {
                foreach ($errors_cron_array as $api => $inviter_details) {
                    echo "**********************************<br />";
                    echo "Sending Invites for " . $api . "<br />";
                    echo "----------------------------- <br />";

                    foreach ($inviter_details as $inviter => $invitee_det) {
                        $inviter_name = Factory::getuser($inviter)->username;

                        foreach ($invitee_det as $invitee => $res) {
                            echo "Error in sending message to  $invitee from " . $inviter_name . " : " . $res . "<br />";
                        }
                    }
                }
            }
        }

        $inviter_array = array_filter($inviter_array, 'trim');
        $this->call_activity_stream($inviter_array);

        return 1;
    }

    /**
     * Function to send reminder
     *
     * @param STRING $plug_call plug call.
     *
     * @param STRING $type plugin type.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function send_reminder($plug_call, $type)
    {
        if ($plug_call != 1) {
            echo "*****************************<br />";
            echo "Sending " . $type . " Reminders<br />";
            echo "----------------------------- <br />";
        }

        $rem_after_days = $this->invitex_params->get('rem_after_days');
        $rem_repeat_times = $this->invitex_params->get('rem_repeat_times');
        $rem_every = $this->invitex_params->get('rem_every');
        $to_direct = $this->invitex_params->get('reg_direct');
        $automated_reminder = $this->invitex_params->get('send_auto_remind');
        $db = Factory::getDbo();
        $query = "SELECT e.id as refid,e.invitee_email,e.invitee_name,e.* , u.id as
		inviter_id, u.name as inviter_name, u.email as inviter_email,
		i.message,i.invite_type,i.invite_url,i.invite_type_tag
						FROM #__invitex_imports_emails AS e
						LEFT JOIN #__invitex_imports AS i ON e.import_id = i.id
						LEFT JOIN #__users AS u ON i.inviter_id = u.id
						WHERE i.message_type='email' AND e.unsubscribe=0 AND e.sent=1 AND e.invitee_id=0 ";

        if ($type == 'manual') {
            $query .= " AND e.remind=1 ";
        }

        $query .= " ORDER BY e.id DESC";
        $db->setQuery($query);
        $temp = $db->loadObjectList();
        $email_array = array();
        $connections_remind = array();

        foreach ($temp as $key => $arr) {
            if (in_array($arr->invitee_email, $email_array)) {
                continue;
            } else {
                $email_array[] = $arr->invitee_email;
                $connections_remind[] = $arr;
            }
        }

        $app = Factory::getApplication();
        $sitename = $app->getCfg('sitename');

        if ($this->invitex_params->get("enable_log") == 1) {
            // OPEN log.php to write
            $logfile_path = JPATH_ADMINISTRATOR . "/components/com_invitex/log.txt";
            $old_content = file_get_contents($logfile_path);
            $today = 'Start ' . date('Y-m-d H:i:s') . "\n" . "Reminders";
            $this->log[] = JText::sprintf($today);
        }

        $i = 0;

        $registered_emails = array_map('trim', $this->getRegistered());

        foreach ($connections_remind as $ind => $rem_obj) {
            $flag = 0;
            $invitee_mail = trim($rem_obj->invitee_email);
            $invitee = explode("@", $invitee_mail);

            if (in_array($invitee_mail, $registered_emails)) {
                continue;
            }

            if ($type == 'manual') {
                $flag = 1;
            } elseif ($automated_reminder) {
                if (!filter_var($invitee_mail, FILTER_VALIDATE_EMAIL)) {
                    // $this->log[]=JText::sprintf($invitee_mail."\t"."Nonvalid email address");
                    if ($plug_call != 1) {
                        echo $invitee_mail . " " . "Nonvalid email address" . "<br />";
                    }

                    continue;
                }

                if ($rem_obj->remind_count == 0) {
                    $days = (int)(abs(time() - $rem_obj->sent_at) / (60 * 60 * 24));

                    if ($days >= $rem_after_days) {
                        $flag = 1;
                    }
                } else {
                    if ($rem_obj->remind_count >= (int)($rem_repeat_times) + 1) {
                        if ($plug_call != 1) {
                            echo "Can not set more reminders to " . $invitee_mail;
                        }

                        $flag = 0;
                        continue;
                    } else {
                        if ($rem_obj->modified) {
                            $interval = $rem_obj->modified + ($rem_every * 60 * 60 * 24);

                            if (time() >= $interval) {
                                $flag = 1;
                            }
                        }
                    }
                }
            }

            if ($flag == 1) {
                $inviter_id = $rem_obj->inviter_id;
                $refid = $rem_obj->refid;
                $expires = $rem_obj->expires;
                $message = $rem_obj->message;
                $invitee_name = $rem_obj->invitee_name;
                $invite_type = $rem_obj->invite_type;
                $invite_type_tag = $rem_obj->invite_type_tag;

                $mail = $this->invhelperObj->getMailtagsinarray(
                    $inviter_id, $refid, $message, $invitee_mail, $invitee_name, $expires, $invite_type, $invite_type_tag
                );
                $mail['msg_body'] = $this->invhelperObj->get_message_template($rem_obj->invite_type, 'reminder');
                $full_message = $this->invhelperObj->tagreplace($mail);

                /* adding JUri::root to the src of the img
				$img_path = 'img src="' . JUri::root();
				$full_message = str_replace('img src="', $img_path, $full_message);
				$full_message = str_replace("background: url('", "background: url('" . JUri::root(), $full_message);*/

                $mail['msg_body'] = $this->invitex_params->get('reminder_subject');
                $reminder_subject = $this->invhelperObj->tagreplace($mail);

                // START Invitex Sample development
                $dispatcher = JDispatcher::getInstance();
                JPluginHelper::importPlugin('system');

                // Call the plugin and get the result
                $result = $dispatcher->trigger('onPrepareInvitexEmail', array($full_message));

                if (!empty($result)) {
                    $full_message = $result[0];
                }

                // END Invitex Sample development

                $cssdata = "";
                $cssfile = JPATH_SITE . "/media/com_invitex/css/invitex_mail.css";
                $cssdata .= file_get_contents($cssfile);

                // START JMA PLUGIN SUPPORT CODE
                $dispatcher = JDispatcher::getInstance();
                JPluginHelper::importPlugin('system');

                // Call the plugin and get the result
                $result = $dispatcher->trigger('onPrepareEmailJmaIntegration', array($full_message, $cssdata, $inviter_id));

                if ($result) {
                    $full_message = $result[0]['message_body'];
                    $cssdata = $result[0]['cssdata'];
                }

                // END JMA PLUGIN SUPPORT CODE

                $emorgdata = $this->getEmorgify($full_message, $cssdata);

                $from_address = $this->invitex_params->get('from_address');

                if ($from_address) {
                    $frommail = $from_address;
                } else {
                    $frommail = trim($rem_obj->inviter_email);
                }

                $mail = Factory::getMailer();

                try {
                    $boolean = $mail->sendMail(
                        $frommail, $rem_obj->inviter_name, $invitee_mail, $reminder_subject, $emorgdata, 1, null, null, null, $frommail, $rem_obj->inviter_name
                    );
                } catch (Exception $e) {
                    echo $e->getMessage() . "\n";
                }

                $timeofevt = gmdate(DATE_RFC822);

                // If mail is sent i.e.no smtp connection error
                if ($boolean == "1") {
                    $data = new stdClass;
                    $data->id = $rem_obj->refid;

                    if ($type == 'manual') {
                        $data->remind = 0;
                        $data->modified = time();
                    } else {
                        $data->modified = time();
                        $data->remind_count = $rem_obj->remind_count + 1;
                    }

                    $db->updateObject('#__invitex_imports_emails', $data, 'id');

                    if ($plug_call != 1) {
                        echo " " . $timeofevt . " " . $invitee_mail . "<br />";
                    }

                    $this->log[] = JText::sprintf($timeofevt . "\t" . $invitee_mail . "\t" . '1');
                    $i++;
                } else {
                    $this->log[] = JText::sprintf($timeofevt . "\t" . $invitee_mail . "\t" . '0');
                }
            }
        }

        if ($i == 0) {
            $this->log[] = JText::sprintf("Reminder has been sent to " . $i . " recipients");
        }

        if ($this->invitex_params->get("enable_log") == 1) {
            $file_log = implode("\n", $this->log);
            $file_log = $old_content . "\n" . $file_log;
            JFile::write($logfile_path, $file_log);
        }

        // END:write to log file - moved out of loop BY MANOJ

        if ($plug_call != 1) {
            echo $type . " reminder has been sent to " . $i . " recipients.<br />";
        }
    }

    /**
     * Function to send Intelligent automated Emails to friend
     *
     * @param STRING $plug_call plugin  call.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function send_friendsEmail($plug_call = 0)
    {
        $this->invitex_params = $this->invhelperObj->getconfigData();

        $db = Factory::getDbo();
        $unsubscribe_emails = array_map('trim', $this->getBlocked());
        $unsubscribe_emails = implode("','", $unsubscribe_emails);
        $unsubscribe_emails = "'" . $unsubscribe_emails . "'";

        // Get the Email addresses who are Imported by more than 3 site users and not registered on site
        $query = "select * from #__invitex_stored_emails where importedcount >=3 and
		sent_count=0 AND email NOT IN (" . $unsubscribe_emails . ") AND email NOT IN(SELECT email from #__users)";
        $db->setQuery($query);
        $res = $db->loadObjectList();

        $cominvitexHelper = new cominvitexHelper;

        // Pass the link for which you want the ItemId.
        $in_itemid = $cominvitexHelper->getitemid('index.php?option=com_invitex&view=invites');

        if ($res) {
            $to_direct = $this->invitex_params->get('reg_direct');
            $app = Factory::getApplication();
            $sitename = $app->getCfg('sitename');
            $from_address = $this->invitex_params->get('from_address');

            if ($from_address) {
                $frommail = $from_address;
            } else {
                $frommail = $app->getCfg('mailfrom');
            }

            $fromname = $app->getCfg('fromname');

            if ($this->invitex_params->get("enable_log") == 1) {
                $logfile_path = JPATH_ADMINISTRATOR . "/components/com_invitex/log.txt";
                $old_content = file_get_contents($logfile_path);
                $today = 'Start ' . date('Y-m-d H:i:s') . "\n" . "'Friends on sitename.com' type Invitations ";
                $this->log[] = JText::sprintf($today);
            }

            $i = 0;

            foreach ($res as $uobj) {
                $subject = $this->invitex_params->get('friendsonsite_subject');

                $noname = 0;

                if ($uobj->name) {
                    if (strpos($uobj->name, '@') != false) {
                        $noname = 1;
                    } else {
                        $uobjname = $uobj->name;
                    }
                } else {
                    $noname = 1;
                }

                if ($noname == 1) {
                    $invitee = explode("@", $uobj->email);
                    $uobjname = $invitee[0];
                }

                $subject = str_replace("[SITENAME]", $sitename, $subject);
                $subject = str_replace("[NAME]", $uobjname, $subject);

                $fs = explode(',', $uobj->importedby);
                $mail['msg_body'] = $this->invitex_params->get('friendsonsite_body');
                $mail['nooffriends'] = $uobj->importedcount;
                $mail['name'] = $uobjname;
                $mail['friendsonsite'] = $this->getFriendsInfo($fs, $to_direct);
                $mail['inviter_id'] = '';

                // Get link with email
                $encoded_id = MD5($uobj->email);
                $url_block = "index.php?option=com_invitex&task=unSubscribe&Itemid=" . $in_itemid . "&email={$encoded_id}";

                if ($this->invitex_params->get('ga_campaign_enable')) {
                    $url_block .= "&utm_campaign=" . $this->invitex_params->get(
                            'ga_campaign_name') . "&utm_source=" . $this->invitex_params->get(
                            'ga_campaign_source') . "&utm_medium=" . $this->invitex_params->get('ga_campaign_medium');
                }

                $url_block = JUri::root() . substr(JRoute::_($url_block, false), strlen(JUri::base(true)) + 1);

                // $links['message_unsubscribe']=$url_block;
                $mail['message_unsubscribe'] = "<a href='" . $url_block . "' target=\"_blank\">" . JText::_('UNSUBSCRIBE') . "</a>";

                $message_body = $this->invhelperObj->tagreplace($mail);

                $img_path = 'img src="' . JUri::root();
                $message_body = str_replace('img src="', $img_path, $message_body);
                $message_body = str_replace("background: url('", "background: url('" . JUri::root(), $message_body);

                $jmail = Factory::getMailer();

                try {
                    $boolean = $jmail->sendMail($frommail, $fromname, $uobj->email, $subject, $message_body, 1, null, null, null, $frommail, $fromname);
                } catch (Exception $e) {
                    echo $e->getMessage() . "\n";
                }

                $timeofevt = gmdate(DATE_RFC822);

                // If mail is sent i.e.no smtp connection error
                if ($boolean == "1") {
                    $data = new stdClass;
                    $data->email = $uobj->email;
                    $data->sent_count = 1;
                    $data->last_sent_date = time();
                    $db->updateObject('#__invitex_stored_emails', $data, 'email');

                    if ($plug_call != 1) {
                        echo " " . $timeofevt . " " . $uobj->email . "<br>";
                    }

                    $this->log[] = JText::sprintf($timeofevt . "\t" . $uobj->email . "\t" . '1');
                    $i++;
                } else {
                    // Smtp error
                    $this->log[] = JText::sprintf($timeofevt . "\t" . $uobj->email . "\t" . '0');
                }
            }

            if ($plug_call != 1) {
                echo "'Friends on sitename.com' type Invitations has been sent to " . $i . " recipients.<br />";
            }
        }
    }

    /**
     * Function to get plugin name
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function getPluginNames()
    {
        // FIRST GET THE EMAIL-ALERTS RELATED PLUGINS FRM THE `jos_plugins` TABLE

        $this->_db->setQuery('SELECT element FROM #__extensions WHERE folder = \'emailalerts\'  AND enabled = 1');

        // Get the plugin names and store in an array

        $email_alert_plugins_array = $this->_db->loadColumn();

        // Return the array

        return $email_alert_plugins_array;
    }

    // GetPluginTags() ends

    /**
     * Method to show the preview
     *
     * @access public
     *
     * @return state
     */
    public function getPreview()
    {
        Factory::getApplication()->input->set('tmpl', 'component');
        $session = Factory::getSession();
        $invite_type = "";

        $input = Factory::getApplication()->input;
        $message_type = stripslashes($input->get('msg_type', '', 'STRING'));

        $this->invitex_params = $this->invitex_params;

        if ($message_type == 'social' || $message_type == 'pm' || $message_type == 'sms') {
            $msg_body = $this->getTemplate_PM();
        } else {
            if ($input->get('invite_anywhere')) {
                $types_res = $this->invhelperObj->types_data($input->get('invite_type', '', 'INT'));
                $msg_body = stripslashes($types_res->template_html);
            } else {
                $msg_body = stripslashes($this->invitex_params->get('message_body'));
            }
        }

        $attached_message = !empty($_COOKIE["MessageCookie"]) ? $_COOKIE["MessageCookie"] : '';
        $refid = '';
        $invitee_mail = '';
        $inviter_id = $this->invhelperObj->getUserID();
        $invitee_name = '';

        $datetime = time();
        $validity = $this->invitex_params->get('expiry');
        $expires = $datetime + ($validity * 60 * 60 * 24);

        $message = htmlspecialchars($attached_message, ENT_COMPAT, 'UTF-8');
        $invite_type = (INT)$session->get('invite_type');
        $invite_type_tag = $session->get('invite_tag');
        $fromname = Factory::getUser($inviter_id)->name;

        $preview = $this->invhelperObj->getMailtagsinarrayPreview(
            $inviter_id, $refid, $message, $invitee_mail, $invitee_name, $expires, $invite_type, $invite_type_tag
        );
        $preview['msg_body'] = $msg_body;
        $prev = $this->invhelperObj->tagreplace($preview);

        // START changing content using plugin
        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('invitex');

        $email_alert_plugin_names = $this->getPluginNames();

        foreach ($email_alert_plugin_names as $email_alert_plugin_name) {
            $pattern = '[' . $email_alert_plugin_name . ']';

            if (preg_match($pattern, $prev)) {
                $prev = str_replace($pattern, $pattern . " " . JText::_("REPLACE_LATER"), $prev);
            }
        }

        if ($message_type == 'social' || $message_type == 'pm') {
            $html_css = $prev;
        } elseif ($message_type == 'email') {
            $connection_data = new stdclass;
            $connection_data->invite_type = $input->get('invite_type', '', 'INT');
            $connection_data->invite_url = urldecode(base64_decode($input->get('invite_url')));
            $connection_data->invite_type_tag = $invite_type_tag;
            $connection_data->id = $inviter_id;

            $html_css = $prev;

            // Call the plugin and get the result
            $result = $dispatcher->trigger('onPrepareInvitexEmail', array($prev, $connection_data));

            if (!empty($result[0])) {
                $html_css = $result[0];
            }
        } else {
            $cssdata = "";
            $cssfile = JPATH_SITE . "/media/com_invitex/css/invitex_mail.css";
            $cssdata .= file_get_contents($cssfile);
            $html_css = $this->getEmorgify($prev, $cssdata);
        }

        return $html_css;
    }

    /**
     * Function to get css data
     *
     * @param STRING $prev prev data.
     *
     * @param STRING $cssdata css data
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function getEmorgify($prev, $cssdata)
    {
        $path = JPATH_SITE . "/components/com_invitex/models/emogrifier.php";

        if (!class_exists('Emogrifier')) {
            JLoader::register('Emogrifier', $path);
            JLoader::load('Emogrifier');
        }

        // Condition to check if mbstring is enabled

        if (!function_exists('mb_convert_encoding')) {
            echo JText::_("MB_EXT");

            return $prev;
        }

        $emogr = new Emogrifier($prev, $cssdata);
        $emorg_data = $emogr->emogrify();

        return $emorg_data;
    }

    /**
     * Function to sign up
     *
     * @return signup state
     */
    public function sign_up()
    {
        $mainframe = Factory::getApplication();
        $database = Factory::getDbo();

        $input = Factory::getApplication()->input;

        $refid = $input->get->get('id', '', 'STRING');
        $invite_anywhere = $input->get->get('invite_anywhere', '', 'STRING');
        $inviter_id = $input->get->get('inviter_id', '', 'STRING');
        $custom_landing_page_visited = $input->get->get('custom_landing_page_visited', '', 'INT');
        $method_of_invite = $input->get('method_of_invite', '', 'STRING');

        $this->invitex_params = $this->invitex_params;

        setcookie("refid", '', -time(), "/");
        setcookie("inviter_id", '', -time(), "/");

        $joom_reg_itemid = $this->invhelperObj->getitemid('index.php?option=com_users&view=registration');

        $cb_itemid = $this->invhelperObj->getitemid('index.php?option=com_comprofiler&task=registers');
        $es_itemid = $this->invhelperObj->getitemid('index.php?option=com_easysocial&view=registration');

        $to_direct = $this->invitex_params->get('reg_direct');

        if ($this->invitex_params->get('any_invitation_url') && $custom_landing_page_visited != 1) {
            if (strpos($this->invitex_params->get('any_invitation_url'), '?') === false) {
                $red_url = $this->invitex_params->get('any_invitation_url') . "?cleancache=asdf";
            } else {
                $red_url = $this->invitex_params->get('any_invitation_url') . "&cleancache=asdf";
            }
        } elseif ((strcmp($to_direct, "Joomla") == 0) || (strcmp($to_direct, "Jomwall") == 0)) {
            $red_url = "index.php?option=com_users&view=registration&Itemid=" . $joom_reg_itemid;
        } elseif (strcmp($to_direct, "JomSocial") == 0) {
            $red_url = "index.php?option=com_community&view=register";
        } elseif (strcmp($to_direct, "Community Builder") == 0) {
            $red_url = "index.php?option=com_comprofiler&task=registers&Itemid=" . $cb_itemid;
        } elseif (strcmp($to_direct, "EasySocial") == 0) {
            $red_url = "index.php?option=com_easysocial&view=registration&Itemid=" . $es_itemid;
        }

        if (isset($refid) && $refid != '') {
            $query = "SELECT * FROM `#__invitex_imports_emails` as ie WHERE MD5(ie.id) = '$refid' ";
            $database->setQuery($query);
            $result = $database->loadObject();

            if (!$result) {
                $msg = JText::_("REFID_INCORRECT_MSG");
                $mainframe->redirect(JUri::base(), $msg);

                return;
            } elseif ($result->invitee_id != 0 && !$invite_anywhere) {
                $msg = JText::_("REFID_ALREADY_USED_MSG");
                $mainframe->redirect(JUri::base(), $msg);

                return;
            }

            $query = "SELECT e.expires, i.date FROM #__invitex_imports_emails AS
			e,#__invitex_imports AS i WHERE MD5(e.id) = '$refid' && i.id=e.import_id ORDER BY e.id DESC LIMIT 1";
            $database->setQuery($query);
            $expi = $database->loadObject();

            $v = ((int)$expi->expires) - ((int)($expi->date));

            if (($expi->expires < time()) && ($v != 0)) {
                $msg = "Your invitation has expired. We apologize for the inconvience.";
                $mainframe->redirect(JUri::base(), $msg);

                return;
            }

            $expire = time() + (86400 * 365);
            setcookie("refid", $refid, $expire, "/");

            $query = "UPDATE `#__invitex_imports_emails`  set `click_count`= 1 WHERE MD5(`id`) = '$refid'";
            $database->setQuery($query);

            if (JVERSION < '3.0') {
                $database->query();
            } else {
                $database->execute();
            }

            // START Invitex Sample development

            $dispatcher = JDispatcher::getInstance();
            JPluginHelper::importPlugin('system');
            $result = $dispatcher->trigger('OnInvitexLinkClick', array($refid));

            // Call the plugin and get the result

            // END Invitex Sample development

            if ($invite_anywhere) {
                $query = "SELECT i.invite_url,type.internal_name
						FROM `#__invitex_imports` as i,`#__invitex_imports_emails` as ie,`#__invitex_types` as type
						WHERE MD5(ie.id) = '$refid' AND i.id=ie.import_id AND i.invite_type	=	type.id";
                $database->setQuery($query);
                $imports_res = $database->loadObject();

                $invite_url = $imports_res->invite_url;

                if (strpos($invite_url, '?') !== true) {
                    $invite_url = $invite_url . '?';
                } else {
                    $invite_url = $invite_url . '&';
                }

                if ($imports_res->internal_name == 'JSEvent' || $imports_res->internal_name == 'JSGroup') {
                    $mainframe->redirect(CRoute::_($invite_url . "reference_id={$refid}", false));
                } else {
                    $mainframe->redirect(JRoute::_($invite_url . "reference_id={$refid}", false));
                }
            } else {
                // Append reference_id to URL
                // Fix to avoid generating incorrect urls
                if (strpos($red_url, '?') === false) {
                    $red_url .= "?reference_id={$refid}";
                } else {
                    $red_url .= "&reference_id={$refid}";
                }
            }
        } elseif (isset($inviter_id) && $inviter_id != '') {
            $query = "SELECT name,id FROM `#__users` WHERE md5(id) = '$inviter_id' ";
            $database->setQuery($query);
            $result = $database->loadObject();

            if (!$result) {
                $msg = JText::_("User does not Exists");
                $mainframe->redirect(JUri::base(), $msg);

                return;
            }

            $expire = time() + (86400 * 365);
            setcookie("inviter_id", $inviter_id, $expire, "/");

            // Append inviter_id to URL
            // Fix to avoid generating incorrect urls
            if (strpos($red_url, '?') === false) {
                $red_url .= "?inviter_id={$inviter_id}";
            } else {
                $red_url .= "&inviter_id={$inviter_id}";
            }

            // START Invitex Sample development
            $dispatcher = JDispatcher::getInstance();
            JPluginHelper::importPlugin('system');

            // Call the plugin and get the result
            $result = $dispatcher->trigger('OnInviteURLClk', array($inviter_id));

            // END Invitex Sample development
        }

        /*if (strcmp($to_direct, "JomSocial") == 0)
		{
			$red_url = CRoute::_($red_url);
		}
		else
		{
			$red_url = JRoute::_($red_url, false);
		}*/

        if ($method_of_invite != "") {
            if (strpos($red_url, '?') === false) {
                $red_url .= "?method_of_invite=invite_by_url";
            } else {
                $red_url .= "&method_of_invite=invite_by_url";
            }
        }

        $mainframe->redirect($red_url);
    }
    // Public function sign_up en'/'

    /**
     * Function to unsubscribe user
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function unSubscribe()
    {
        $mainframe = Factory::getApplication();

        $input = Factory::getApplication()->input;
        $refid = $input->get('id', '', 'STRING');
        $refemail = $input->get('email', '', 'STRING');

        if ($refid || $refemail) {
            if ($refid) {
                $attached_var = '&refid=' . $refid;
            } elseif ($refemail) {
                $attached_var = '&refemail=' . $refemail;
            }

            $unsubscribeurlz = "index.php?option=com_invitex&view=invites&layout=unsubscribe&action=confirm";
            $mainframe->redirect(JRoute::_($unsubscribeurlz . $attached_var . "&Itemid=" . $itemid, false));
        }
    }

    /**
     * Function to unsubscribe user
     *
     * @param bool $post post data.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function unSubscribeConfirm($post)
    {
        $db = Factory::getDbo();
        $ip_add = Factory::getApplication()->input->get('REMOTE_ADDR');
        $datetime = time();
        $user = Factory::getUser();

        $variable = $post->get('variable', '', 'STRING');
        $value = $post->get('value', '', 'STRING');

        if ($variable == 'refid') {
            $sql = "UPDATE #__invitex_imports_emails " . " SET unsubscribe=1, ip='$ip_add', modified='$datetime' WHERE " . " md5(`id`)='$value'";
        } elseif ($variable == 'refemail') {
            $sql = "UPDATE #__invitex_stored_emails " . " SET unsubscribe=1 WHERE " . " md5(`email`)='$value'";
        }

        $db->setQuery($sql);

        // Plugin trigger on before user unsubscribe
        JPluginHelper::importPlugin('actionlog');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('invitexOnBeforeUserUnsubscribe', array($user->id));

        if (JVERSION < '3.0') {
            $db->query();
        } else {
            $db->execute();
        }

        // Plugin trigger on after user unsubscribe
        JPluginHelper::importPlugin('actionlog');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('invitexOnAfterUserUnsubscribe', array($user->id));

        return 1;
    }

    /**
     * Function to get registered users
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function getRegistered()
    {
        $database = Factory::getDBo();

        // Get lower case email_id for comparison
        $sql = "select LOWER(email) from #__users";
        $database->setQuery($sql);

        $registered_email = $database->loadColumn();

        return ($registered_email);
    }

    /**
     * Function to get blocked people
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function getBlocked()
    {
        $database = Factory::getDBo();

        // Get lower case email_id for comparison
        $sql = "select distinct LOWER(invitee_email) from #__invitex_imports_emails where unsubscribe=1";
        $database->setQuery($sql);
        $unsubscribe_email1 = $database->loadColumn();

        // Get lower case email_id for comparison
        $sql = "select distinct LOWER(email) from #__invitex_stored_emails where unsubscribe=1";
        $database->setQuery($sql);
        $unsubscribe_email2 = $database->loadColumn();

        $unsubscribe_email = array_unique(array_merge($unsubscribe_email2, $unsubscribe_email1));

        return ($unsubscribe_email);
    }

    /**
     * Function to get already invited people
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function getalreadyInvited()
    {
        $database = Factory::getDBo();

        // Get lower case email_id for comparison
        $sql = "select distinct LOWER(invitee_email) from #__invitex_imports_emails where
		unsubscribe=0 && invitee_id=0 && inviter_id='" . Factory::getUser()->id . "'";
        $database->setQuery($sql);

        $invited_emails = $database->loadColumn();

        return ($invited_emails);
    }

    /**
     * Redefine the function an add some properties to make the styling more easy
     *
     * @param STRING $imported_data imported data.
     *
     * @param STRING $plug_type plugin type.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function seperate_emails($imported_data, $plug_type = '')
    {
        $session = Factory::getSession();
        $r_mail = $invite_mail = $b_mail = $i_mail = $contacts = array();
        $registered_emails = array_map('trim', $this->getRegistered());
        $unsubscribe_emails = array_map('trim', $this->getBlocked());
        $already_invited_emails = array_map('trim', $this->getalreadyInvited());

        if ($session->get('invite_anywhere')) {
            $contacts['invite_mails'] = $imported_data;
        } else {
            $k = $p = $r = 0;

            foreach ($imported_data as $email => $name) {
                // Convert the email address to lower case before adding
                $email = strtolower($email);

                $flag = 0;
                $flag1 = 0;
                $flag2 = 0;

                if ($plug_type == 'social') {
                    $flag = 0;
                    $flag1 = 0;
                    $flag2 = 0;
                } else {
                    if ($this->validate_email($email) == '1') {
                        if (in_array($email, $registered_emails)) {
                            $r_mail[$k++] = $email;
                            $flag = 1;
                        } elseif (in_array($email, $unsubscribe_emails)) {
                            $b_mail[$p++] = $email;
                            $flag1 = 1;
                        } elseif (in_array($email, $already_invited_emails)) {
                            $i_mail[$r++] = $email;
                            $flag2 = 1;
                        }
                    } else {
                        $flag = 1;
                        $flag1 = 1;
                        $flag2 = 0;
                    }
                }

                if ($flag == 0 && $flag1 == 0 && $flag2 == 0) {
                    $invite_mail[$email] = $name;
                }
            }

            $contacts['invite_mails'] = $invite_mail;
        }

        $contacts['b_mail'] = $b_mail;
        $contacts['r_mail'] = $r_mail;
        $contacts['i_mail'] = $i_mail;

        //dump($contacts);
        //die;

        return $contacts;
    }

    /**
     * Function to send invites using quick invite module
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   3.0
     */
    public function sendQuickInvites()
    {
        $user = Factory::getUser();
        $result = new stdclass;
        $result->status = 'success';
        $result->message = JText::_('INVITE_SUCESS');
        $email_str = $_POST['invitex_mod_correct_mails'];
        $email_array = explode(',', $email_str);
        $email_array = array_filter($email_array);
        $email_str = implode(',', $email_array);

        // Check privacy consent
        $invitexParams = JComponentHelper::getParams('com_invitex');
        $invitationTermsAndConditions = $invitexParams->get('invitationTermsAndConditions', '0');
        $tNcArticleId = $invitexParams->get('tNcArticleId', '0');
        $session = Factory::getSession();
        $tncAccepted = $session->get('tj_send_invitations_consent');

        if ($invitationTermsAndConditions && $tNcArticleId) {
            if (empty($tncAccepted)) {
                $result->status = 'no_consent';
                $result->message = JText::_('COM_INVITEX_PRIVACY_CONSENT_ERROR_MSG');

                return $result;
            }
        }

        $contacts = array();

        foreach ($email_array as $email) {
            $email = trim($email);
            $contacts[$email] = $email;
        }

        if (!empty($email_array)) {
            $seperatedmails = $this->seperate_emails($contacts, '');
        } else {
            $result->status = 'no_emails';
            $result->message = JText::_('COM_INVITEX_ALL_INCORRECT_EMAIL');

            return $result;
        }

        if (!empty($seperatedmails)) {
            if (!empty($seperatedmails['r_mail']) || !empty($seperatedmails['i_mail'])) {
                $result->status = 'ri_mail';

                if (!empty($seperatedmails['r_mail']) && !empty($seperatedmails['i_mail'])) {
                    $result->message = JText::sprintf(
                        'COM_INVITEX_ALREADY_INVITED_AND_REGISTERED', implode(',', $seperatedmails['i_mail']), explode(',', $seperatedmails['r_mail'])
                    );

                    return $result;
                } elseif (!empty($seperatedmails['r_mail'])) {
                    $result->message = JText::sprintf('COM_INVITEX_ALREADY_REGISTERED', implode(',', $seperatedmails['r_mail']));

                    return $result;
                } elseif (!empty($seperatedmails['i_mail'])) {
                    $result->message = JText::sprintf('COM_INVITEX_ALREADY_INVITED', implode(',', $seperatedmails['i_mail']));

                    return $result;
                }
            }
        }

        if (!empty($user->id)) {
            if ($email_str == $user->email) {
                $result->status = 'self_invitaion';
                $result->message = JText::_('COM_INVITEX_SELF_INVITAION');

                return $result;
            }
        }

        $this->sort_mail();

        return $result;
    }

    /**
     * Function to sort mail
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function sort_mail()
    {
        $mainframe = Factory::getApplication();
        $this->invitex_params = $this->invitex_params;
        $session = Factory::getSession();
        $session->set('invite_mails', '');
        $session->set('unsubscribe_mails', '');
        $session->set('registered_mails', '');
        $session->set('already_invited_mails', '');
        $session->set('email_box', '');
        $session->set('provider_box', '');
        $session->set('plugType', '');
        $session->set('rout', '');
        $session->set('import_type', '');
        $session->set('oi_session_id', '');
        $session->set('OI_plugType', '');
        $session->set('api_message_type', '');
        $session->set('api_used', '');

        $on_error_redirect = '';
        $reg_direct = $this->invitex_params->get('reg_direct');

        if (isset($post['guest'])) {
            $session->set('guest_user', $post['guest']);
        }

        $ers = $invite_friends = array();
        $l = 0;
        $k = 0;

        $input = Factory::getApplication()->input;

        $post = $input->getArray($_POST);

        // For quick module correct email ids are stored in invitex_mod_correct_mails
        if (!empty($post['invitex_mod_correct_mails'])) {
            $moduleInvites = $post['invitex_mod_correct_mails'];

            if (!empty($moduleInvites)) {
                $post['invitex_correct_mails'] = $moduleInvites;
            }
        }

        $rout = $post['rout'];
        $session->set('rout', $rout);

        if (!empty($post['personal_message'])) {
            $session->set('personal_message', $post['personal_message']);
        } else {
            $post['personal_message'] = $this->invitex_params->get('invitex_default_message');
            $session->set('personal_message', $post['personal_message']);
        }

        // FOR GUEST USER
        if (isset($post['guest'])) {
            $session->set('guest_user', $post['guest']);
        }

        $inv_itemid = $this->invhelperObj->getitemid('index.php?option=com_invitex&view=invites');

        if ($session->get('invite_anywhere')) {
            $on_error_redirect = "index.php?option=com_invitex&view=invites&inv_redirect=" . $session->get('rout') . "&tmpl=component&Itemid=" . $inv_itemid;
        } else {
            $on_error_redirect = "index.php?option=com_invitex&view=invites&inv_redirect=" . $session->get('rout') . "&Itemid=" . $inv_itemid;
        }

        if ($rout == 'inv_js_messaging') {
            $invite_friends = $post['inv_js_friends'];

            foreach ($invite_friends as $inv_f) {
                $friend_user = Factory::getUser($inv_f);

                if ($this->invitex_params->get('reg_direct') == 'JomSocial') {
                    $friend_usernm = $this->invhelperObj->sociallibraryobj->getProfileData($friend_user)->name;
                } elseif ($this->invitex_params->get('reg_direct') == 'EasySocial') {
                    $friend_usernm = $this->invhelperObj->sociallibraryobj->getProfileData($friend_user)->name;
                }

                $invite_mail[$friend_usernm] = $inv_f;
            }

            $session->set('invite_mails', $invite_mail);
            $session->set('email_box', 'JS_MESSAGING');
            $session->set('provider_box', 'JS_MESSAGING');
            $session->set('personal_message', $post['personal_message']);
            $invite_type = $input->get('invite_type', '', 'INT');
            $redirectlink = "index.php?option=com_invitex&controller=invites&task=save&" . JSession::getFormToken() . '=1';
            $mainframe->redirect(JRoute::_($redirectlink . $inv_itemid . '&invite_type=' . $invite_type, false));
        } elseif ($rout == 'manual') {
            $session->set('email_box', 'SEND_MANUAL');
            $session->set('provider_box', 'manual');
            $session->set('personal_message', $post['personal_message']);

            $post['manual_method_type'] = (empty($post['manual_method_type'])) ? '' : $post['manual_method_type'];

            // If advanced manual method is used
            if ($post['manual_method_type'] == 'advanced') {
                foreach ($post['invitee_email'] AS $key => $invitee_email) {
                    $name = $post['invitee_name'][$key];
                    $contacts[$invitee_email] = $name;
                }

                $seperatedmails = $this->seperate_emails($contacts, '');

                $seperatedmails['invite_mails'] = array_flip($seperatedmails['invite_mails']);
            } else {
                // Email ids entered in manual box
                $email_str = $post['invitex_correct_mails'];
                $data = explode(',', $email_str);
                $data = array_filter($data);
                $both_name_email = 0;

                foreach ($data as $ind => $a) {
                    $a = trim($a);
                    $pos = -1;
                    $pos = strpos($a, "(");
                    $new_contacts = array();

                    if ($pos > -1) {
                        $both_name_email = 1;
                        $new_contacts = explode("(", $a);
                        $new_contacts_index = $new_contacts[0];
                        $new_contacts_val = str_replace(")", "", $new_contacts[1]);
                        $new_contacts_val = str_replace(")", "", $new_contacts_val);
                        $new_contacts_val = trim($new_contacts_val);
                        $contacts[$new_contacts_val] = $new_contacts_index;
                    } else {
                        $contacts[$a] = $a;
                    }
                }

                $seperatedmails = $this->seperate_emails($contacts, '');
                $invite_mails_both = $seperatedmails['invite_mails'];

                if (is_array($invite_mails_both) and $both_name_email == 1) {
                    $seperatedmails['invite_mails'] = array_flip($invite_mails_both);
                }
            }
        } elseif ($rout == 'OI_import') {
            require JPATH_SITE . "/components/com_invitex/openinviter/config.php";
            require_once JPATH_SITE . '/components/com_invitex/openinviter/openinviter.php';
            $inviter = new openinviter;
            $session->set('import_type', $post['import_type']);

            if ($post['import_type'] == 'email') {
                $provider = $post['provider_box'];
                $emailid = $post['email_box'];
                $password = $post['password_box'];
            } else {
                $provider = $post['social_provider'];
                $emailid = $post['social_email'];
                $password = $post['social_password'];
            }

            $session->set('email_box', $emailid);
            $session->set('provider_box', $provider);
            $oi_services = $inviter->getPlugins();

            if ($provider) {
                if (isset($oi_services['email'][$provider])) {
                    $plugType = 'email';
                } elseif (isset($oi_services['social'][$provider])) {
                    $plugType = 'social';
                } else {
                    $plugType = '';
                }
            } else {
                $plugType = '';
            }

            $session->set('OI_plugType', $plugType);

            $inviter->startPlugin($provider);
            $internal = $inviter->getInternalError();

            if ($internal) {
                $ers['inviter'] = $internal;
            } elseif (!$inviter->login($emailid, $password)) {
                $internal = $inviter->getInternalError();
                $ers['login'] = ($internal ? $internal : JText::_('OI_LOGIN_ERROR'));
            } elseif (false === $contacts = $inviter->getMyContacts()) {
                $ers['contacts'] = JText::_('OI_CONNECT_ERROR');
            } elseif (count($inviter->getMyContacts()) == 0) {
                $ers['no_contacs'] = JText::_('OI_NO_CONTATS');
            } else {
                $import_ok = true;
                $session->set('oi_session_id', $inviter->plugin->getSessionID());
                $session->set('personal_message', '');
            }

            if ($ers) {
                $mainframe->redirect(JRoute::_($on_error_redirect, false), "<b>" . $this->ers($ers) . "</b>");

                return;
            }

            if ($contacts) {
                $seperatedmails = $this->seperate_emails($contacts, $plugType);
            }
        } elseif ($rout == 'other_tools') {

            $session->set('email_box', 'SEND_CSV');
            $session->set('provider_box', 'csv');
            $session->set('message_type', 'email');
            $ers = array();
            $oks = array();
            $import_ok = false;
            $done = false;

            $rs1 = @mkdir(JPATH_COMPONENT_SITE . '/csv', 0777);


            // Start file heandling public functionality *
            $fname = $_FILES['csvfile']['name'];
            $uploads_dir = JPATH_COMPONENT_SITE . '/csv/' . $fname;
            move_uploaded_file($_FILES['csvfile']['tmp_name'], $uploads_dir);

            $file = fopen($uploads_dir, "r");
            $contentsc = [];
            $new_contentsc = [];
            $info = pathinfo($uploads_dir);

            if ($info['extension'] != 'csv') {
                $msg = JText::_('NOT_CSV_MSG');

                $application = Factory::getApplication();

                // Add a message to the message queue
                $application->enqueueMessage($msg, 'error');

                $mainframe->redirect(JRoute::_($on_error_redirect, false));

                return;
            }

            $least_data = 0;
            $i = 0;
            while (!feof($file)) {
                $temp = (array)fgetcsv($file, 1000, ",");
                if (empty($temp) && $least_data == 0) {
                    $msg = JText::_('WRONG_CONTENT_CSV_ERROR');
                    $mainframe->redirect(JRoute::_($on_error_redirect, false), "<b>" . $msg . "</b>");
                    return;
                } else {
                    if (count($temp) > 1) {
                        if (!empty($temp[0])) {

                            $temp[0] = strip_tags($temp[0]);
                            $temp[1] = strip_tags($temp[1]);
                            $temp[2] = strip_tags($temp[2]);
                            $contentsc[$temp[1]] = $temp[0];
                            $new_contentsc[$i]['name'] = isset($temp[0]) ? $temp[0] : "";
                            $new_contentsc[$i]['email'] = isset($temp[1]) ? $temp[1] : "";
                            $new_contentsc[$i]['phone'] = isset($temp[2]) ? $temp[2] : "";
                        }
                        $i++;
                        $least_data = 1;
                    }
                }
            }

            fclose($file);

            // End file heandling public functionality
            if (!$contentsc) {

                $msg = JText::_('ZERO_CONTACTS_CSV_ERROR');
                $mainframe->redirect(JRoute::_($on_error_redirect, false), "<b>" . $msg . "</b>");

                return;
            } else {
                $seperatedmails = $this->seperate_emails($contentsc, '');
            }
        }

        $invite_mail = $seperatedmails['invite_mails'];
        $b_mail = $seperatedmails['b_mail'];
        $r_mail = $seperatedmails['r_mail'];
        $i_mail = $seperatedmails['i_mail'];

        $cnt_b_mail = count($b_mail);
        $cnt_r_mail = count($r_mail);
        $cnt_i_mail = count($i_mail);


        // START Invitex Sample development
        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('system');
        $result = $dispatcher->trigger('OnAfterInvitexImport', array($invite_mail, $rout));

        // Call the plugin and get the result
        if ($result) {
            $invite_mail = $result[0];
        }

        // END Invitex Sample development
        $cnt_invite_mail = count($invite_mail);


        if ($cnt_invite_mail > 0) {
            if ($cnt_invite_mail > 500) {
                $res = http_build_query($invite_mail);
                $session->set('invite_mails', $res);
                $session->set('new_invite_mails', $new_contentsc);
            } else {
                $session->set('invite_mails', $invite_mail);
                $session->set('new_invite_mails', $new_contentsc);
            }
        }
//		echo "<pre>";
//        var_dump($cnt_invite_mail);
//		die;
        if ($cnt_b_mail > 0) {
            $session->set('unsubscribe_mails', $b_mail);
        }


        if ($cnt_r_mail > 0) {
            $session->set('registered_mails', $r_mail);

            // Add imported contacts for people suggest
            if ($this->invitex_params->get('store_contact')) {
                if (!empty($r_mail)) {
                    foreach ($r_mail as $u_email) {
                        $db = Factory::getDbo();
                        $query = $db->getQuery(true);
                        $query->select('*');
                        $query->from($db->quoteName('#__users'));
                        $query->where($db->quoteName('email') . " = '" . $u_email . "'");
                        $db->setQuery($query);

                        $userDetails = $db->loadObject();

                        $query = $db->getQuery(true);
                        $query->select($db->quoteName('id'));
                        $query->from($db->quoteName('#__invitex_stored_contacts'));
                        $query->where($db->quoteName('inviter') . " = '" . $id . "'");
                        $query->where($db->quoteName('invitee') . " = '" . $userDetails->id . "'");
                        $db->setQuery($query);
                        $once_done = $db->loadResult();

                        if (empty($once_done)) {
                            $insert = new stdClass;
                            $insert->inviter = $id;
                            $insert->invitee = $userDetails->id;
                            $db->insertObject('#__invitex_stored_contacts', $insert, 'id');
                        }
                    }
                }
            }

            // Activity Stream
            if (!empty($r_mail)) {
                $only_for_find_friends = 1;
                $inviter_array = array();
                $user = Factory::getUser();
                $inviter_array[$user->id] = count($r_mail);
                $this->call_activity_stream($inviter_array, $only_for_find_friends);
            }
        }

        if ($cnt_i_mail > 0) {
            $session->set('already_invited_mails', $i_mail);
        }

        $this->manageImportedEmails();

        $this->handleredirect($cnt_invite_mail, $cnt_r_mail, $cnt_b_mail, $rout);
    }

    /**
     * Redefine the function an add some properties to make the styling more easy
     *
     * @param INT $cnt_invite_mail count of invitations.
     *
     * @param INT $cnt_r_mail count of reminider mails.
     *
     * @param INT $cnt_b_mail count of blocked mail.
     *
     * @param INT $rout redirect link.
     *
     * @return  void.
     *
     * @since   1.6
     */
    public function handleredirect($cnt_invite_mail, $cnt_r_mail, $cnt_b_mail, $rout)
    {
        $session = Factory::getSession();
        $mainframe = Factory::getApplication();
        $inv_itemid = $this->invhelperObj->getitemid('index.php?option=com_invitex&view=invites');
        $ia_url_params = '';

        if ($session->get('invite_anywhere')) {
            $ia_url_params = '&invite_type=' . (INT)$session->get('invite_type') . '&catch_act=&invite_anywhere=1';
        }

        $msg = '';

        if ($cnt_invite_mail > 0) {
            if ($rout == 'manual') {
                $link = 'index.php?option=com_invitex&controller=invites&task=save&Itemid=' . $inv_itemid . '&' . JSession::getFormToken() . '=1';
            } else {
                $link = 'index.php?option=com_invitex&view=invites&layout=send_invites' . $ia_url_params . '&Itemid=' . $inv_itemid;
            }
        } else {
            $resend_link = "<a href='" . JRoute::_('index.php?option=com_invitex&view=resend&Itemid=' . $itemid, false) . "'>" . JText::_('RE_SEND') . "</a>";
            $msg = JText::sprintf('INV_ALREADY_INVITED_MSG', 'All', $resend_link);
            $link = 'index.php?option=com_invitex&view=invites&Itemid=' . $inv_itemid;
        }

        $mainframe->redirect(JRoute::_($link, false), $msg);
    }

    // For easy social friends and invted firends

    /**
     * Redefine the function an add some properties to make the styling more easy
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function getesfriend()
    {
        $eaysocialfolder = JPATH_SITE . '/components/com_easysocial';

        if (JFolder::exists($eaysocialfolder)) {
            $uid = $this->invhelperObj->getUserID();
            $user = Factory::getUser($uid);
            $db = Factory::getDbo();
            $sql = "select target_id from #__social_friends where actor_id=" . $uid . "";
            $db->setQuery($sql);
            $tempfriends[] = $db->loadColumn();
            $sql = "select actor_id from #__social_friends where target_id=" . $uid . "";
            $db->setQuery($sql);
            $tempfriends[] = $db->loadColumn();

            return ($this->umerge($tempfriends));
        } else {
            return false;
        }
    }

    /**
     * Function to get invited friends of easy social
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function getesinvitedfriend()
    {
        $eaysocialfolder = JPATH_SITE . '/components/com_easysocial';

        if (JFolder::exists($eaysocialfolder)) {
            $uid = $this->invhelperObj->getUserID();
            $user = Factory::getUser($uid);
            $db = Factory::getDbo();
            $sql = "select target_id from #__social_friends where actor_id=" . $uid . " AND state=-1";
            $db->setQuery($sql);
            $tempinvited[] = $db->loadColumn();

            $sql = "select actor_id from #__social_friends where target_id=" . $uid . " AND state=-1";
            $db->setQuery($sql);
            $tempinvited[] = $db->loadColumn();

            return ($this->umerge($tempinvited));
        } else {
            return false;
        }
    }

    /**
     * Function to get jom social friends
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function getJsfriend()
    {
        $communityfolder = JPATH_SITE . '/components/com_community';

        if (JFolder::exists($communityfolder)) {
            $uid = $this->invhelperObj->getUserID();
            $user = Factory::getUser($uid);

            if ($user->id) {
                $db = Factory::getDbo();
                $sql = "select connect_to from #__community_connection where status 	=	1 AND connect_from=" . $user->id;
                $db->setQuery($sql);

                $temp[] = $db->loadColumn();

                $sql = "select connect_from from #__community_connection where status 	=	1 AND connect_to=" . $user->id;
                $db->setQuery($sql);

                $temp[] = $db->loadColumn();

                return ($this->umerge($temp));
            }
        } else {
            return false;
        }
    }

    /**
     * Function to get list of jomsocial invited friends
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function getJsInvitedfriend()
    {
        $communityfolder = JPATH_SITE . '/components/com_community';

        if (JFolder::exists($communityfolder)) {
            $uid = $this->invhelperObj->getUserID();
            $user = Factory::getUser($uid);
            $db = Factory::getDbo();

            if ($user->id) {
                $sql = "select connect_to from #__community_connection where status 	<>	1 and connect_from=" . $user->id;
                $db->setQuery($sql);

                $temp[] = $db->loadColumn();

                $sql = "select connect_from from #__community_connection where status 	<>	1 and connect_to=" . $user->id;
                $db->setQuery($sql);

                $temp[] = $db->loadColumn();

                return ($this->umerge($temp));
            }
        } else {
            return false;
        }
    }

    /**
     * Function to merge results
     *
     * @param bool $arrays data to merge.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function umerge($arrays)
    {
        $result = array();

        foreach ($arrays as $array) {
            $array = (array)$array;

            foreach ($array as $value) {
                if (array_search($value, $result) === false) {
                    $result[] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * Function to return CB friends
     *
     * @return  cd friends.
     *
     * @since   1.6
     */
    public function getCbfriend()
    {
        $cbfolder = JPATH_SITE . '/components/com_comprofiler';

        if (JFolder::exists($cbfolder)) {
            $uid = $this->invhelperObj->getUserID();
            $user = Factory::getUser($uid);

            if ($user->id) {
                $db = Factory::getDbo();

                $sql = "select memberid from #__comprofiler_members where referenceid=" . $user->id . " AND accepted=1";
                $db->setQuery($sql);

                return ($db->loadColumn());
            }
        } else {
            return false;
        }
    }

    /**
     * Function to show errors in formatted way
     *
     * @param bool $ers errors.
     *
     * @return  STRING  contents.
     *
     * @since   1.6
     */
    public function ers($ers)
    {
        if (!empty($ers)) {
            $contents = "<table cellspacing='0' cellpadding='0' style='border:0px solid
			#5897FE;' align='left' class='tbErrorMsgGrad'><tr><td valign='middle'
			style='padding:3px' valign='middle' class='tbErrorMsg'><img src='" . JUri::root() .
                "components/com_invitex/images/ers.gif" . "' ></td><td valign='left' style='color:#5897FE;padding:5px;'><strong>";

            foreach ($ers as $key => $error) {
                $contents .= "{$error}<br >";
            }

            $contents .= "</td></tr></table><br >";

            return $contents;
        }
    }

    /**
     * Function to return content if status is ok
     *
     * @param INT $oks status
     *
     * @return  mixed  An array of data items on success
     *
     * @since   1.6
     */
    public function oks($oks)
    {
        if (!empty($oks)) {
            $contents = "<table border='0' cellspacing='0' cellpadding='10' style='border:0px
			solid #5897FE;' align='left' class='tbInfoMsgGrad'><tr><td valign='middle'
			valign='middle' class='tbInfoMsg'><img src='" . JUri::root() .
                "components/com_invitex/images/oks.gif" . "' ></td><td valign='middle' style='color:#5897FE;padding:5px;'><strong>	";

            foreach ($oks as $key => $msg) {
                $contents .= "{$msg}<br >";
            }

            $contents .= "</td></tr></table><br >";

            return $contents;
        }
    }

    /**
     * Function to add friend
     *
     * @return  void
     *
     * @since   1.6
     */
    public function add_friend()
    {
        $to_direct = $this->invitex_params->get('reg_direct');
        $input = Factory::getApplication()->input;
        $action = $input->get('action', '');
        $friend = $input->get('fuid', '0', 'INT');
        $inviter_user = Factory::getUser();

        if ($action == 'add_friend') {
            $invitee_user = Factory::getUser($friend);

            // For JomSocial
            if ((strcmp($to_direct, "JomSocial") == 0)) {
                $sociallibraryclass = new JSocialJomsocial;
            }

            // For JomSocial
            if ((strcmp($to_direct, "EasySocial") == 0)) {
                $sociallibraryclass = new JSocialEasySocial;
            }

            // For JomSocial
            if ((strcmp($to_direct, "Community Builder") == 0)) {
                $sociallibraryclass = new JSocialCB;
            }

            // Set frind count to 1
            // Make inviter and invitees friends
            $sociallibraryclass->addFriend($inviter_user, $invitee_user);
        }

        if (!empty($inviter_user->id) && !empty($invitee_user->id)) {
            $result = new stdclass;

            $result->inviter_user = $inviter_user->id;
            $result->invitee_user = $invitee_user->id;
            $result->msg = "success";

            return json_encode($result);
        }
    }

    /**
     * Function to send mail
     *
     * @param STRING $vars variable.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function send($vars)
    {
        $db = $this->getDbo();
        $my = Factory::getUser();

        // Get the time without any offset!
        $date = Factory::getDate();
        $cDate = $date->toSQL();
        $obj = new stdClass;
        $obj->id = null;
        $obj->from = $my->id;
        $obj->posted_on = $date->toSQL();
        $obj->from_name = $my->name;
        $obj->subject = $vars['subject'];
        $obj->body = $vars['body'];

        // Don't add message if user is sending message to themselve
        if ($vars['to'] != $my->id) {
            $db->insertObject('#__community_msg', $obj, 'id');

            // Update the parent
            $obj->parent = $obj->id;
            $db->updateObject('#__community_msg', $obj, 'id');
        }

        if (is_array($vars['to'])) {
            // Multiple recepint
            foreach ($vars['to'] as $sToId) {
                if ($vars['to'] != $my->id) {
                    $this->addReceipient($obj, $sToId);
                }
            }
        } else {
            // Single recepient
            if ($vars['to'] != $my->id) {
                $this->addReceipient($obj, $vars['to']);
            }
        }

        return $obj->id;
    }

    /**
     * Function to add recepient
     *
     * @param STRING $msgObj message object.
     *
     * @param INT $recepientId recepients id.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function addReceipient($msgObj, $recepientId)
    {
        $db = $this->getDbo();
        $my = Factory::getUser();

        $recepient = new stdClass;
        $recepient->msg_id = $msgObj->id;
        $recepient->msg_parent = $msgObj->parent;
        $recepient->msg_from = $msgObj->from;
        $recepient->to = $recepientId;

        if ($my->id != $recepientId) {
            $db->insertObject('#__community_msg_recepient', $recepient);
        }

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }

        return $this;
    }

    /**
     * Function to get friends information
     *
     * @param STRING $friends Friends list.
     *
     * @param STRING $to_direct link.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function getFriendsInfo($friends, $to_direct)
    {
        $Itemid = '';
        $return = '<table>';

        $cominvitexHelper = new cominvitexHelper;
        $this->sociallibraryobj = $cominvitexHelper->getSocialLibraryObject();

        if (!empty($friends)) {
            $k = 1;

            foreach ($friends as $friend) {
                if ($k == 1) {
                    $return .= "<tr>";
                }

                /*if(strcmp($to_direct,"JomSocial") == 0)
				{
				$link=JUri::root().substr(CRoute::_('index.php?option=com_community&view=profile&userid='. $friend),strlen(JUri::base(true))+1);
				}

				if(strcmp($to_direct,"Community Builder") == 0)
				{
				$cb_itemid	=	$this->invhelperObj->getitemid('index.php?option=com_comprofiler');
				$link 	= $link=JUri::root().substr(
				JRoute::_('index.php?option=com_comprofiler&task=viewprofile&user=' . $friend . '&Itemid=' . $cb_itemid,false),strlen(JUri::base(true))+1);
				}*/

                $frienduser = Factory::getUser($friend);
                $avatar = $this->sociallibraryobj->getAvatar($frienduser);
                $link = $this->sociallibraryobj->getProfileUrl($frienduser);

                $uimage = "<img src=" . $avatar . " height='60' width='60'/>";
                $return .= '<td style="padding-left:90px">
						<div style="width:60px;height:60px">
							<a href="' . $link . '" target="_blank">' . $uimage . '</a>
						</div>
						<span style="font-size: 11px; color: #3b5998;">' . Factory::getUser($friend)->name . '</span>
					</td>';
                $k++;

                if ($k > 3) {
                    $k = 1;
                    $return .= "</tr>";
                }
            }

            $return .= '</table>';
        }

        return $return;
    }

    /**
     * Method to review FB request
     *
     * @return  void
     *
     * @since  1.0
     */
    public function FBRequestReview()
    {
        header('Content-Type: text/html; charset=utf-8');
        $mainframe = Factory::getApplication();
        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('techjoomlaAPI', 'plug_techjoomlaAPI_facebook');
        $fb_user = $dispatcher->trigger('plug_techjoomlaAPI_facebook_getUser');
        $this->invitex_params = $this->invitex_params;

        if (!$fb_user) {
            $plugin = JPluginHelper::getPlugin('techjoomlaAPI', 'plug_techjoomlaAPI_facebook');
            $params = json_decode($plugin->params);
            $app_id = $params->appKey;
            $invitexurl = 'index.php?option=com_invitex&view=invites&layout=fb_connect&tmpl=component&app_id=';
            $mainframe->redirect(JUri::root() . $invitexurl . $app_id . '&request_ids=' . $_GET['request_ids']);
        }

        $request_ids = explode(',', $_REQUEST['request_ids']);
        $res = '';
        $database = Factory::getDbo();

        if (count($request_ids) > 1) {
            foreach ($request_ids as $request) {
                $q = "SELECT e.id as refid,e.invitee_email, u.id, u.name, u.email as
				inviter_email, i.message, e.expires,i.invite_type,i.invite_url,i.invite_type_tag
					FROM #__invitex_imports_emails AS e
					LEFT JOIN #__invitex_imports AS i ON e.import_id = i.id
					LEFT JOIN #__users AS u ON i.inviter_id = u.id
					WHERE e.invitee_email = '" . $request . '_' . $fb_user[0]['id'] . "' order by e.id DESC limit 1";
                $database->setQuery($q);
                $dat = $database->loadObject();

                if ($dat) {
                    $arr[$dat->refid] = $dat;
                }
            }

            $res = $arr[max(array_keys($arr))];
        } else {
            $request = $request_ids[0];
            $q = "SELECT e.id as refid,e.invitee_email, u.id, u.name, u.email as
			inviter_email, i.message, e.expires,i.invite_type,i.invite_url,i.invite_type_tag
				FROM #__invitex_imports_emails AS e
				LEFT JOIN #__invitex_imports AS i ON e.import_id = i.id
				LEFT JOIN #__users AS u ON i.inviter_id = u.id
				WHERE e.invitee_email = '" . $request . '_' . $fb_user[0]['id'] . "' order by e.id DESC limit 1";
            $database->setQuery($q);
            $res = $database->loadObject();
        }

        if ($res) {
            $invitee_mail = '';

            /* get optional message as [MESSAGE] tag*/
            $message = $this->invitex_params->get('invitex_default_message');
            $inviter_id = $res->id;
            $refid = $res->refid;
            $expires = $res->expires;
            $invitee_name = $fb_user[0]['name'];
            $invite_type = $res->invite_type;
            $invite_type_tag = $res->invite_type_tag;
            $mail = $this->invhelperObj->getMailtagsinarray(
                $inviter_id, $refid, $message, $invitee_mail, $invitee_name, $expires, $invite_type, $invite_type_tag
            );
            $mail['msg_body'] = $this->invhelperObj->get_message_template($invite_type, 'fbrequest');
            $message_body = $this->invhelperObj->tagreplace($mail);
        }

        print_r($message_body);
        die;
    }

    /**
     * Method to manage imported id
     *
     * @return  boolean
     *
     * @since  1.0
     */
    public function manageImportedEmails()
    {
        $session = Factory::getSession();
        $this->invitex_params = $this->invitex_params;

        if ($this->invitex_params->get('store_contact')) {
            $invitemails = $session->get('invite_mails');
            $db = Factory::getDbo();
            $ol_uid = $this->invhelperObj->getUserID();

            foreach ($invitemails as $email => $name) {
                if ($email == $name) {
                    $name = '';
                }

                // Get a db connection.
                $db = Factory::getDbo();

                // Create a new query object.
                $query = $db->getQuery(true);

                // Select all records from the user profile table where key begins with "custom.".
                // Order it by the ordering field.
                $query->select($db->quoteName(array('id', 'importedcount', 'importedby')));
                $query->from($db->quoteName('#__invitex_stored_emails'));
                $query->where($db->quoteName('email') . ' = ' . $db->quote($email));

                // Reset the query using our newly populated query object.
                $db->setQuery($query);

                // Load the results as a list of stdClass objects (see later for more options on retrieving data).
                $res = $db->loadObject();

                if ($res) {
                    $importedby = '';
                    $importedcount = $res->importedcount;

                    if ($res->importedby) {
                        $importedby = $res->importedby;

                        if (!in_array($ol_uid, explode(',', $res->importedby))) {
                            $importedby .= ',' . $ol_uid;
                            $importedcount = $res->importedcount + 1;
                        }
                    }

                    $update_data = new stdClass;
                    $update_data->id = $res->id;
                    $update_data->name = $name;
                    $update_data->importedby = $importedby;
                    $update_data->importedcount = $importedcount;

                    $db->updateObject('#__invitex_stored_emails', $update_data, 'id');
                } else {
                    $insert_obj = new stdClass;
                    $insert_obj->email = $email;
                    $insert_obj->name = $name;
                    $insert_obj->importedby = $ol_uid;
                    $insert_obj->importedcount = 1;
                    $db->insertObject('#__invitex_stored_emails', $insert_obj);
                }
            }

            return true;
        }
    }

    /**
     * Method to get invite type id
     *
     * @param INT $import_id inviter arrey
     *
     * @param STRING $invitor inviter
     *
     * @return  array of the replacements
     *
     * @since  1.0
     */
    public function save_token_for_paticular_user($import_id, $invitor)
    {
        $session = Factory::getSession();
        $api_used = $session->get('api_used');

        if ($api_used == 'plug_techjoomlaAPI_linkedin') {
            $user_token = $session->get("['oauth']['linkedin']['access']");
        } elseif ($api_used == 'plug_techjoomlaAPI_twitter') {
            $user_token = $session->get("['oauth']['twitter']['access']");
        }

        $user_token = json_encode($user_token);

        $db = Factory::getDbo();
        $insert_obj = new stdClass;
        $insert_obj->id = '';
        $insert_obj->import_id = $import_id;
        $insert_obj->user_id = $invitor;
        $insert_obj->token = $user_token;
        $db->insertObject('#__invitex_stored_tokens', $insert_obj);

        return $insert_obj->id;
    }

    /**
     * Method to get invite type id
     *
     * @param STRING $inviter_array inviter arrey
     *
     * @param STRING $only_for_find_friends find friends
     *
     * @return  array of the replacements
     *
     * @since  1.0
     */
    public function call_activity_stream($inviter_array, $only_for_find_friends = '')
    {
        $this->invitex_params = $this->invitex_params;

        if ($inviter_array) {
            // Integration code for jomsocial cb and jomwall
            foreach ($inviter_array as $inviter => $count_people) {
                if ($this->invitex_params->get('integrate_activity_stream')) {
                    $integrate_activity_stream = $this->invitex_params->get('integrate_activity_stream');

                    $contentdata = array();
                    $invitee_count = $count_people;
                    $contentdata['user_id'] = $inviter;
                    $contentdata['integration_option'] = $this->invitex_params->get('reg_direct');

                    if (in_array(1, $integrate_activity_stream) && $only_for_find_friends == '') {
                        $contentdata['act_description'] = JText::sprintf("INV_ACTIVITY_MSG", $invitee_count);
                        $cominvitexHelper = new cominvitexHelper;

                        if (!empty($invitee_count)) {
                            $cominvitexHelper->pushtoactivitystream($contentdata);
                        }
                    }

                    if (in_array(0, $integrate_activity_stream) && $only_for_find_friends == 1) {
                        $contentdata['act_description'] = JText::sprintf("INV_ACTIVITY_MSG_FIND_FRIEND", $invitee_count);
                        $cominvitexHelper = new cominvitexHelper;

                        if (!empty($invitee_count)) {
                            $cominvitexHelper->pushtoactivitystream($contentdata);
                        }
                    }
                }

                if ($this->invitex_params->get('broadcast_activity_stream') == 1) {
                    $if_broadcast = JPATH_SITE . '/components/com_broadcast';
                    $boradcast_hepler = JPATH_SITE . '/components/com_broadcast/helper.php';

                    if (JFolder::exists($if_broadcast)) {
                        require_once $boradcast_hepler;
                        $userid = $inviter;
                        $invitee_count = $count_people;

                        if ($only_for_find_friends == '') {
                            $newstatus = JText::sprintf("INV_ACTIVITY_MSG", $invitee_count);
                        } else {
                            $newstatus = JText::sprintf("INV_ACTIVITY_MSG_FIND_FRIEND", $invitee_count);
                        }

                        $count = 1;
                        $interval = 0;
                        $supplier = '';
                        $media[] = '';
                        $combroadcastHelper = new combroadcastHelper;
                        $broadcast_int = $combroadcastHelper->inQueue($userid, $newstatus, $count, $interval, $supplier, $media);
                    }
                }
            }
        }
    }

    /**
     * Method to get invite type id
     *
     * @param STRING $internal_name internal name
     *
     * @return  array of the replacements
     *
     * @since  1.0
     */
    public function getInvite_typeID($internal_name = '')
    {
        $db = $this->getDbo();
        $query = $sql = "select id  from #__invitex_types where internal_name LIKE '" . $internal_name . "'";
        $db->setQuery($query);

        return $db->loadResult();
    }

    /**
     * Method for sms delivery status
     *
     * @param STRING $import_email_id import email id
     * @param INT $sms_id sms id
     *
     * @return  array of the replacements
     *
     * @since  1.0
     */
    public function sms_delivery_status($import_email_id, $sms_id)
    {
        $db = Factory::getDbo();
        $insert_obj = new stdClass;
        $insert_obj->id = '';
        $insert_obj->import_email_id = $import_email_id;
        $insert_obj->apisms_id = trim($sms_id);
        $db->insertObject('#__invite_sms_delivery', $insert_obj);

        return $insert_obj->id;
    }
}
