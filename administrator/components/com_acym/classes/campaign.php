<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.4.0
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php

class acymcampaignClass extends acymClass
{
    var $table = 'campaign';
    var $pkey = 'id';
    const SENDING_TYPE_NOW = 'now';
    const SENDING_TYPE_SCHEDULED = 'scheduled';
    const SENDING_TYPE_AUTO = 'auto';
    const SENDING_TYPES = [
        self::SENDING_TYPE_NOW,
        self::SENDING_TYPE_SCHEDULED,
        self::SENDING_TYPE_AUTO,
    ];
    var $encodedColumns = ['sending_params'];

    public function decode($campaign, $decodeMail = true)
    {
        if (empty($campaign)) return $campaign;

        if (is_array($campaign)) {
            foreach ($campaign as $i => $oneCampaign) {
                $campaign[$i] = $this->decode($oneCampaign, false);
            }
        }

        foreach ($this->encodedColumns as $oneColumn) {
            if (!isset($campaign->$oneColumn)) continue;

            $campaign->$oneColumn = empty($campaign->$oneColumn) ? [] : json_decode($campaign->$oneColumn, true);
        }

        if ($decodeMail) {
            $mailClass = acym_get('class.mail');
            $campaign = $mailClass->decode($campaign);
        }

        return $campaign;
    }

    public function getAll()
    {
        $query = 'SELECT * FROM #__acym_campaign';

        return $this->decode(acym_loadObjectList($query));
    }

    public function getMatchingElements($settings = [])
    {
        $tagClass = acym_get('class.tag');
        $mailClass = acym_get('class.mail');
        $statClass = acym_get('class.mailstat');
        $query = 'SELECT campaign.*, mail.name FROM #__acym_campaign AS campaign';
        $queryCount = 'SELECT campaign.* FROM #__acym_campaign AS campaign';
        $filters = [];
        $mailIds = [];

        $query .= ' JOIN #__acym_mail AS mail ON campaign.mail_id = mail.id';
        $queryCount .= ' JOIN #__acym_mail AS mail ON campaign.mail_id = mail.id';

        if (!empty($settings['tag'])) {
            $tagJoin = ' JOIN #__acym_tag AS tag ON campaign.mail_id = tag.id_element';
            $query .= $tagJoin;
            $queryCount .= $tagJoin;
            $filters[] = 'tag.name = '.acym_escapeDB($settings['tag']);
            $filters[] = 'tag.type = "mail"';
        }

        if (!empty($settings['search'])) {
            $filters[] = 'mail.name LIKE '.acym_escapeDB('%'.$settings['search'].'%');
        }

        if (!empty($filters)) {
            $query .= ' WHERE ('.implode(') AND (', $filters).')';
            $queryCount .= ' WHERE ('.implode(') AND (', $filters).')';
        }

        $statusRequests = [
            'all' => '(campaign.parent_id IS NULL OR campaign.parent_id = 0)',
            'scheduled' => 'campaign.sending_type = '.acym_escapeDB(self::SENDING_TYPE_SCHEDULED).' AND (campaign.parent_id IS NULL OR campaign.parent_id = 0)',
            'sent' => 'campaign.sent = 1 AND (campaign.parent_id IS NULL OR campaign.parent_id = 0)',
            'draft' => 'campaign.draft = 1 AND (campaign.parent_id IS NULL OR campaign.parent_id = 0)',
            'auto' => 'campaign.sending_type = '.acym_escapeDB(self::SENDING_TYPE_AUTO).' AND (campaign.parent_id IS NULL OR campaign.parent_id = 0)',
            'generated' => 'campaign.sending_type = '.acym_escapeDB(self::SENDING_TYPE_NOW).' AND campaign.parent_id  > 0',
        ];

        if (empty($settings['status'])) $settings['status'] = 'all';
        $query .= empty($filters) ? ' WHERE ' : ' AND ';
        $query .= $statusRequests[$settings['status']];

        if (!empty($settings['ordering']) && !empty($settings['ordering_sort_order'])) {
            $table = in_array($settings['ordering'], ['name', 'creation_date']) ? 'mail' : 'campaign';
            $query .= ' ORDER BY '.$table.'.'.acym_secureDBColumn($settings['ordering']).' '.acym_secureDBColumn(strtoupper($settings['ordering_sort_order']));
        }


        if (empty($settings['offset']) || $settings['offset'] < 0) {
            $settings['offset'] = 0;
        }

        if (empty($settings['elementsPerPage']) || $settings['elementsPerPage'] < 1) {
            $settings['elementsPerPage'] = acym_getCMSConfig('list_limit', 20);
        }

        $results['elements'] = $this->decode(acym_loadObjectList($query, '', $settings['offset'], $settings['elementsPerPage']));

        foreach ($results['elements'] as $oneCampaign) {
            array_push($mailIds, $oneCampaign->mail_id);
            $oneCampaign->tags = '';
        }

        $tags = $tagClass->getAllTagsByTypeAndElementIds('mail', $mailIds);
        $lists = $mailClass->getAllListsWithCountSubscribersByMailIds($mailIds);
        $totalStats = $statClass->getAllFromMailIds($mailIds);

        foreach ($results['elements'] as $i => $oneCampaign) {
            $results['elements'][$i]->tags = [];
            $results['elements'][$i]->lists = [];
            $results['elements'][$i]->automation_id = null;

            foreach ($tags as $tag) {
                if ($oneCampaign->id == $tag->id_element) {
                    $results['elements'][$i]->tags[] = $tag;
                }
            }

            foreach ($lists as $list) {
                if ($oneCampaign->mail_id == $list->mail_id) {
                    array_push($results['elements'][$i]->lists, $list);
                }
            }

            if (isset($totalStats[$oneCampaign->mail_id])) {
                $oneCampaignStats = $totalStats[$oneCampaign->mail_id];
                $results['elements'][$i]->subscribers = $oneCampaignStats->total_subscribers;
                $results['elements'][$i]->open = 0;
                if (!empty($oneCampaignStats->total_subscribers)) {
                    $results['elements'][$i]->open = intval($oneCampaignStats->open_unique / $oneCampaignStats->total_subscribers * 100);
                }
            }
        }

        $results['total'] = acym_loadObjectList($queryCount);

        return $results;
    }

    public function getOneById($id)
    {
        return $this->decode(acym_loadObject('SELECT campaign.* FROM #__acym_campaign AS campaign WHERE campaign.id = '.intval($id)));
    }

    public function getOneByIdWithMail($id)
    {
        $query = 'SELECT campaign.*, mail.name, mail.subject, mail.body, mail.from_name, mail.from_email, mail.reply_to_name, mail.reply_to_email, mail.bcc 
                FROM #__acym_campaign AS campaign
                JOIN #__acym_mail AS mail ON campaign.mail_id = mail.id
                WHERE campaign.id = '.intval($id);

        return $this->decode(acym_loadObject($query));
    }

    public function get($identifier, $column = 'id')
    {
        return $this->decode(acym_loadObject('SELECT campaign.* FROM #__acym_campaign AS campaign WHERE campaign.'.acym_secureDBColumn($column).' = '.acym_escapeDB($identifier)));
    }

    public function getAllCampaignsNameMailId()
    {
        $query = 'SELECT m.id, m.name 
                FROM #__acym_campaign AS c 
                LEFT JOIN #__acym_mail AS m ON c.mail_id = m.id';

        return $this->decode(acym_loadObjectList($query));
    }

    public function getOneCampaignByMailId($mailId)
    {
        return $this->decode(acym_loadObject('SELECT * FROM #__acym_campaign WHERE mail_id = '.intval($mailId)));
    }

    public function getAutoCampaignFromGeneratedMailId($mailId)
    {
        $queryCampaign = 'SELECT * FROM #__acym_campaign WHERE id = (SELECT parent_id FROM #__acym_campaign WHERE mail_id = '.intval($mailId).')';

        return $this->decode(acym_loadObject($queryCampaign));
    }

    public function manageListsToCampaign($listsIds, $mailId, $unselectedListIds = [])
    {
        if (!empty($unselectedListIds)) {
            acym_arrayToInteger($unselectedListIds);
            acym_query('DELETE FROM #__acym_mail_has_list WHERE mail_id = '.intval($mailId).' AND list_id IN ('.implode(', ', $unselectedListIds).')');
        }

        acym_arrayToInteger($listsIds);
        if (empty($listsIds)) return false;

        $values = [];
        $listsIds = array_unique($listsIds);
        foreach ($listsIds as $id) {
            array_push($values, '('.intval($mailId).', '.intval($id).')');
        }

        if (!empty($values)) {
            acym_query('INSERT IGNORE INTO #__acym_mail_has_list (`mail_id`, `list_id`) VALUES '.implode(',', $values));
        }

        return true;
    }

    public function save($campaignToSave)
    {
        $campaign = clone $campaignToSave;
        if (isset($campaign->tags)) {
            $tags = $campaign->tags;
            unset($campaign->tags);
        }

        foreach ($campaign as $oneAttribute => $value) {
            if (in_array($oneAttribute, $this->encodedColumns)) {
                $campaign->$oneAttribute = json_encode(empty($value) ? [] : $value);
            } else {
                if (empty($value)) continue;
                $campaign->$oneAttribute = strip_tags($value);
            }
        }

        $campaignID = parent::save($campaign);

        if (!empty($campaignID) && isset($tags)) {
            $tagClass = acym_get('class.tag');
            $tagClass->setTags('mail', $campaign->mail_id, $tags);
        }

        return $campaignID;
    }

    public function delete($elements)
    {
        if (!is_array($elements)) {
            $elements = [$elements];
        }

        if (empty($elements)) {
            return 0;
        }

        $mailsToDelete = [];
        foreach ($elements as $id) {
            $mailsToDelete[] = acym_loadResult('SELECT mail_id FROM #__acym_campaign WHERE id = '.intval($id));
            acym_query('UPDATE #__acym_campaign SET mail_id = NULL WHERE id = '.intval($id));
        }

        $mailClass = acym_get('class.mail');
        $mailClass->delete($mailsToDelete);

        return parent::delete($elements);
    }

    public function send($campaignID, $result = 0)
    {
        $campaign = $this->getOneById($campaignID);

        if (empty($campaign->mail_id)) {
            $this->errors[] = 'Mail not found';

            return false;
        }

        $lists = acym_loadResultArray('SELECT list_id FROM #__acym_mail_has_list WHERE mail_id = '.intval($campaign->mail_id));
        if (empty($lists)) {
            $this->errors[] = acym_translation('ACYM_NO_LIST_SELECTED');

            return false;
        }
        acym_arrayToInteger($lists);

        $date = acym_date('now', 'Y-m-d H:i:s', false);
        if (empty($result)) {
            $conditions = [
                '`user`.`active` = 1',
                '`ul`.`status` = 1',
                '`ul`.`list_id` IN ('.implode(',', $lists).')',
            ];
            $config = acym_config();
            if ($config->get('require_confirmation', 1) == 1) $conditions[] = '`user`.`confirmed` = 1';

            $insertQuery = 'INSERT IGNORE INTO `#__acym_queue` (`mail_id`, `user_id`, `sending_date`) 
                        SELECT '.intval($campaign->mail_id).', ul.`user_id`, '.acym_escapeDB($date).' 
                        FROM `#__acym_user_has_list` AS `ul` 
                        JOIN `#__acym_user` AS `user` ON `user`.`id` = `ul`.`user_id` ';

            if (!empty($campaign->sending_params['resendTarget']) && 'new' === $campaign->sending_params['resendTarget']) {
                $insertQuery .= ' LEFT JOIN `#__acym_user_stat` AS `us` ON `us`.`user_id` = `user`.`id` AND `us`.`mail_id` = '.intval($campaign->mail_id);
                $conditions[] = '`us`.`user_id` IS NULL';
            }

            $insertQuery .= ' WHERE '.implode(' AND ', $conditions);
            $result = acym_query($insertQuery);
        }

        if ($campaign->sending_type == self::SENDING_TYPE_NOW) {
            $campaign->sending_date = $date;
            $campaign->draft = 0;
            $this->save($campaign);
        }

        $mailStatClass = acym_get('class.mailstat');
        $mailStat = $mailStatClass->getOneRowByMailId($campaign->mail_id);

        if (empty($mailStat)) {
            $mailStat = [];
            $mailStat['mail_id'] = intval($campaign->mail_id);
            $mailStat['total_subscribers'] = 0;
        } else {
            $mailStat = get_object_vars($mailStat);
        }

        $mailStat['total_subscribers'] += intval($result);
        $mailStat['send_date'] = $date;

        $mailStatClass->save($mailStat);

        if ($result === 0) {
            acym_enqueueMessage(acym_translation('ACYM_NO_USERS_FOUND'), 'warning');
        } else {
            acym_query('UPDATE `#__acym_campaign` SET `sent` = 1, `active` = 1 WHERE `mail_id` = '.intval($campaign->mail_id));
        }

        return $result;
    }

    public function getCampaignForDashboard()
    {
        $query = 'SELECT campaign.*, mail.name as name FROM #__acym_campaign as campaign LEFT JOIN #__acym_mail as mail ON campaign.mail_id = mail.id WHERE `active` = 1 AND `sending_type` = '.acym_escapeDB(self::SENDING_TYPE_SCHEDULED).' AND `sent` = 0 LIMIT 3';

        return $this->decode(acym_loadObjectList($query));
    }

    public function getOpenRateOneCampaign($mail_id)
    {
        $query = 'SELECT sent, open_unique FROM #__acym_mail_stat 
                    WHERE mail_id = '.intval($mail_id).' LIMIT 1';

        return acym_loadObject($query);
    }

    public function getOpenRateAllCampaign()
    {
        $query = 'SELECT SUM(sent) as sent, SUM(open_unique) as open_unique FROM #__acym_mail_stat';

        return acym_loadObject($query);
    }

    public function getBounceRateAllCampaign()
    {
        $query = 'SELECT SUM(sent) as sent, SUM(bounce_unique) as bounce_unique FROM #__acym_mail_stat';

        return acym_loadObject($query);
    }


    public function getBounceRateOneCampaign($mail_id)
    {
        $query = 'SELECT sent, bounce_unique FROM #__acym_mail_stat 
                    WHERE mail_id = '.intval($mail_id).' LIMIT 1';

        return acym_loadObject($query);
    }

    public function getOpenByMonth($mail_id = '', $start = '', $end = '')
    {
        $query = 'SELECT COUNT(user_id) as open, DATE_FORMAT(open_date, \'%Y-%m\') as open_date FROM #__acym_user_stat WHERE open > 0';
        $query .= empty($mail_id) ? '' : ' AND  `mail_id`='.intval($mail_id);
        $query .= empty($start) ? '' : ' AND `open_date` >= '.acym_escapeDB($start);
        $query .= empty($start) ? '' : ' AND `open_date` <= '.acym_escapeDB($end);
        $query .= ' GROUP BY MONTH(open_date), YEAR(open_date) ORDER BY open_date';

        return acym_loadObjectList($query);
    }

    public function getOpenByWeek($mail_id = '', $start = '', $end = '')
    {
        $query = 'SELECT COUNT(user_id) as open, DATE_FORMAT(open_date, \'%Y-%m-%d\') as open_date FROM #__acym_user_stat WHERE open > 0';
        $query .= empty($mail_id) ? '' : ' AND  `mail_id`='.intval($mail_id);
        $query .= empty($start) ? '' : ' AND `open_date` >= '.acym_escapeDB($start);
        $query .= empty($start) ? '' : ' AND `open_date` <= '.acym_escapeDB($end);
        $query .= ' GROUP BY WEEK(open_date), YEAR(open_date) ORDER BY open_date';

        return acym_loadObjectList($query);
    }

    public function getOpenByDay($mail_id = '', $start = '', $end = '')
    {
        $query = 'SELECT COUNT(user_id) as open, DATE_FORMAT(open_date, \'%Y-%m-%d\') as open_date FROM #__acym_user_stat WHERE open > 0';
        $query .= empty($mail_id) ? '' : ' AND  `mail_id`='.intval($mail_id);
        $query .= empty($start) ? '' : ' AND `open_date` >= '.acym_escapeDB($start);
        $query .= empty($start) ? '' : ' AND `open_date` <= '.acym_escapeDB($end);
        $query .= ' GROUP BY DAYOFYEAR(open_date), YEAR(open_date) ORDER BY open_date';

        return acym_loadObjectList($query);
    }

    public function getOpenByHour($mail_id = '', $start = '', $end = '')
    {
        $query = 'SELECT COUNT(user_id) as open, DATE_FORMAT(open_date, \'%Y-%m-%d %H:00:00\') as open_date FROM #__acym_user_stat WHERE open > 0';
        $query .= empty($mail_id) ? '' : ' AND  `mail_id`='.intval($mail_id);
        $query .= empty($start) ? '' : ' AND `open_date` >= '.acym_escapeDB($start);
        $query .= empty($start) ? '' : ' AND `open_date` <= '.acym_escapeDB($end);
        $query .= ' GROUP BY HOUR(open_date), DAYOFYEAR(open_date), YEAR(open_date) ORDER BY open_date';

        return acym_loadObjectList($query);
    }

    public function getLastNewsletters($params)
    {
        $query = 'SELECT m.name, m.id, m.body, m.subject, c.sending_date FROM #__acym_campaign as c
                    INNER JOIN #__acym_mail as m ON c.mail_id = m.id
                    WHERE c.active = 1 AND c.sent = 1';

        $queryCount = 'SELECT COUNT(*) FROM (SELECT m.id FROM #__acym_campaign as c INNER JOIN #__acym_mail as m ON c.mail_id = m.id WHERE c.active = 1 AND c.sent = 1';

        if (isset($params['userId'])) {
            $query .= ' AND m.id IN (SELECT ml.mail_id FROM #__acym_mail_has_list ml
                        INNER JOIN #__acym_user_has_list ul ON ml.list_id = ul.list_id
                        WHERE ul.user_id = '.intval($params['userId']).')';
            $queryCount .= ' AND m.id IN (SELECT ml.mail_id FROM #__acym_mail_has_list ml
                        INNER JOIN #__acym_user_has_list ul ON ml.list_id = ul.list_id
                        WHERE ul.user_id = '.intval($params['userId']).')';
        }

        $query .= ' ORDER BY c.sending_date DESC';

        $page = isset($params['page']) ? $params['page'] : 0;
        $numberPerPage = isset($params['numberPerPage']) ? $params['numberPerPage'] : 0;
        $lastNewsletters = isset($params['limit']) ? $params['limit'] : 0;

        $queryCount .= empty($lastNewsletters) ? '' : ' LIMIT '.intval($lastNewsletters);

        if (!empty($page) && !empty($numberPerPage)) {
            if (!empty($lastNewsletters)) {
                $limit = ((($page * $numberPerPage) > $lastNewsletters) ? fmod($lastNewsletters, $numberPerPage) : $numberPerPage);
            } else {
                $limit = $numberPerPage;
            }

            $offset = ($params['page'] - 1) * $numberPerPage;
            $query .= ' LIMIT '.intval($offset).', '.intval($limit);
        } elseif (!empty($lastNewsletters)) {
            $limit = $lastNewsletters;

            $query .= ' LIMIT '.intval($limit);
        }

        $queryCount .= ') AS r';


        $return = [];

        $return['matchingNewsletters'] = $this->decode(acym_loadObjectList($query));

        $userClass = acym_get('class.user');
        $userEmail = acym_currentUserEmail();
        $user = $userClass->getOneByEmail($userEmail);

        foreach ($return['matchingNewsletters'] as $i => $oneNewsletter) {
            acym_trigger('replaceContent', [&$oneNewsletter]);
            acym_trigger('replaceUserInformation', [&$oneNewsletter, &$user, false]);

            $return['matchingNewsletters'][$i] = $oneNewsletter;
        }

        $return['count'] = acym_loadResult($queryCount);

        return $return;
    }

    public function getListsForCampaign($mailId)
    {
        $query = 'SELECT list_id FROM #__acym_mail_has_list WHERE mail_id = '.intval($mailId);

        return acym_loadResultArray($query);
    }

    public function triggerAutoCampaign()
    {
        $activeAutoCampaigns = acym_loadObjectList(
            'SELECT campaign.*, mail.name 
            FROM #__acym_campaign AS campaign 
            JOIN #__acym_mail AS mail ON campaign.`mail_id` = mail.`id` 
            WHERE `active` = 1 AND `sending_type` = '.acym_escapeDB(self::SENDING_TYPE_AUTO)
        );
        $activeAutoCampaigns = $this->decode($activeAutoCampaigns);

        if (empty($activeAutoCampaigns)) return;

        $mailClass = acym_get('class.mail');
        $time = time();

        foreach ($activeAutoCampaigns as $campaign) {
            $step = new stdClass();
            $step->triggers = $campaign->sending_params;
            $step->last_execution = $campaign->last_trigger;
            $step->next_execution = $campaign->next_trigger;

            $execute = false;
            $data = ['time' => $time];
            acym_trigger('onAcymExecuteTrigger', [&$step, &$execute, &$data], 'plgAcymTime');
            if (!$execute) continue;

            $campaignMail = $mailClass->getOneById($campaign->mail_id);
            $shouldGenerate = $this->_updateAutoCampaign($campaign, $campaignMail, $time, $step->next_execution);
            unset($campaign->name);
            $this->save($campaign);

            if (!$shouldGenerate) continue;

            $generatedCampaign = $this->_generateCampaign($campaign, $campaignMail, $mailClass);

            if (empty($campaign->sending_params['need_confirm_to_send'])) $this->send($generatedCampaign->id);
        }
    }

    private function shouldGenerateCampaign($campaign, $campaignMail)
    {
        $results = acym_trigger('generateByCategory', [&$campaignMail]);

        foreach ($results as $oneResult) {
            if (isset($oneResult->status) && !$oneResult->status) {
                $this->messages[] = acym_translation_sprintf('ACYM_CAMPAIGN_NOT_GENERATED', $campaign->name, $oneResult->message);

                return false;
            }
        }

        return true;
    }

    private function _updateAutoCampaign(&$campaign, $campaignMail, $time, $nextTrigger)
    {
        $campaign->next_trigger = $nextTrigger;

        if (!$this->shouldGenerateCampaign($campaign, $campaignMail)) return false;

        if (empty($campaign->sending_params['number_generated'])) {
            $campaign->sending_params['number_generated'] = 1;
        } else {
            $campaign->sending_params['number_generated']++;
        }
        $campaign->last_trigger = $time;

        return true;
    }

    private function _generateCampaign($campaign, $campaignMail, $mailClass)
    {
        $newMail = $this->_generateMailAutoCampaign($campaignMail, $campaign->sending_params['number_generated']);
        $newCampaign = new stdClass();
        $newCampaign->mail_id = $newMail->id;
        $newCampaign->parent_id = $campaign->id;
        $newCampaign->active = 1;
        $newCampaign->draft = 1;
        $newCampaign->sending_type = self::SENDING_TYPE_NOW;
        $newCampaign->sent = 0;

        $newCampaign->id = $this->save($newCampaign);

        acym_trigger('replaceContent', [&$newMail]);
        $mailClass->save($newMail);

        return $newCampaign;
    }

    private function _generateMailAutoCampaign($newMail, $generatedMail)
    {
        $mailId = $newMail->id;
        unset($newMail->id);
        $newMail->name .= ' #'.$generatedMail;

        $mailClass = acym_get('class.mail');
        $newMail->id = $mailClass->save($newMail);
        $this->_setListToGeneratedCampaign($mailId, $newMail->id);

        return $newMail;
    }

    private function _setListToGeneratedCampaign($parentMailId, $newMailId)
    {
        $mailClass = acym_get('class.mail');
        $lists = $mailClass->getAllListsByMailId($parentMailId);
        $listIds = [];
        foreach ($lists as $list) {
            $listIds[] = $list->id;
        }

        return $this->manageListsToCampaign($listIds, $newMailId);
    }
}

