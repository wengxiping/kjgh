<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.4.0
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><form id="acym_form" action="<?php echo acym_completeLink(acym_getVar('cmd', 'ctrl')); ?>" method="post" name="acyForm">
	<div id="acym__campaigns" class="acym__content">
        <?php if (empty($data['allCampaigns']) && empty($data['search']) && empty($data['status']) && empty($data['tag'])) { ?>
			<div class="grid-x text-center">
				<h1 class="acym__listing__empty__title cell"><?php echo acym_translation('ACYM_YOU_DONT_HAVE_ANY_CAMPAIGN'); ?></h1>
				<h1 class="acym__listing__empty__subtitle cell"><?php echo acym_translation('ACYM_CREATE_ONE_NOW'); ?></h1>
				<div class="medium-4"></div>
				<div class="medium-4 cell">
					<button data-task="edit" data-step="chooseTemplate" type="button" class="button expanded acy_button_submit"><?php echo acym_translation('ACYM_CREATE_NEW_CAMPAIGN'); ?></button>
				</div>
				<div class="medium-4"></div>
			</div>
        <?php } else { ?>
			<div class="grid-x grid-margin-x">
				<div class="large-3 medium-8 cell">
                    <?php echo acym_filterSearch($data['search'], 'campaigns_search', 'ACYM_SEARCH'); ?>
				</div>
				<div class="large-3 medium-4 cell">
                    <?php
                    $allTags = new stdClass();
                    $allTags->name = acym_translation('ACYM_ALL_TAGS');
                    $allTags->value = '';
                    array_unshift($data["allTags"], $allTags);

                    echo acym_select($data["allTags"], 'campaigns_tag', acym_escape($data["tag"]), 'class="acym__campaigns__filter__tags"', 'value', 'name');
                    ?>
				</div>
				<div class="large-auto hide-for-large-only hide-for-medium-only hide-for-small-only cell"></div>
				<div class="large-shrink medium-6 cell">
					<button data-task="edit" data-step="chooseTemplate" class="button expanded acy_button_submit"><?php echo acym_translation('ACYM_CREATE_NEW_CAMPAIGN'); ?></button>
				</div>
			</div>
            <?php if (empty($data['allCampaigns'])) { ?>
				<h1 class="cell acym__listing__empty__search__title text-center"><?php echo acym_translation('ACYM_NO_RESULTS_FOUND'); ?></h1>
            <?php } else { ?>
				<div class="cell grid-x margin-top-1">
					<div class="grid-x acym__listing__actions cell auto">
                        <?php
                        $actions = ['delete' => acym_translation('ACYM_DELETE'), 'duplicate' => acym_translation('ACYM_DUPLICATE')];
                        echo acym_listingActions($actions);
                        ?>
						<div class="medium-auto cell">
                            <?php
                            $options = [
                                '' => ['ACYM_ALL', $data['allStatusFilter']->all],
                                'scheduled' => ['ACYM_SCHEDULED', $data['allStatusFilter']->scheduled],
                                'sent' => ['ACYM_SENT', $data['allStatusFilter']->sent],
                                'draft' => ['ACYM_DRAFT', $data['allStatusFilter']->draft],
                                'auto' => ['ACYM_AUTO', $data['allStatusFilter']->auto],
                                'generated' => ['ACYM_GENERATED', $data['allStatusFilter']->generated, $data['generatedPending'] ? 'pending' : ''],
                            ];
                            echo acym_filterStatus($options, $data["status"], 'campaigns_status');
                            ?>
						</div>
					</div>
					<div class="grid-x cell auto">
						<div class="cell acym_listing_sorty-by">
                            <?php echo acym_sortBy(
                                [
                                    'id' => strtolower(acym_translation('ACYM_ID')),
                                    'name' => acym_translation('ACYM_NAME'),
                                    'sending_date' => acym_translation('ACYM_SENDING_DATE'),
                                    'creation_date' => acym_translation('ACYM_DATE_CREATED'),
                                    'draft' => acym_translation('ACYM_DRAFT'),
                                    'active' => acym_translation('ACYM_ACTIVE'),
                                    'scheduled' => acym_translation('ACYM_SCHEDULED'),
                                    'sent' => acym_translation('ACYM_SENT'),
                                ],
                                "campaigns"
                            ); ?>
						</div>
					</div>
				</div>
				<div class="grid-x acym__listing">
					<div class="grid-x cell acym__listing__header">
						<div class="medium-shrink small-1 cell">
							<input id="checkbox_all" type="checkbox">
						</div>
						<div class="grid-x medium-auto small-11 cell acym__listing__header__title__container">
							<div class="medium-auto small-11 cell acym__listing__header__title">
                                <?php echo acym_translation('ACYM_CAMPAIGNS'); ?>
							</div>
							<div class="large-3 medium-3 hide-for-small-only cell acym__listing__header__title">
                                <?php echo acym_translation('ACYM_LISTS'); ?>
							</div>
							<div class="large-2 medium-4 hide-for-small-only cell acym__listing__header__title text-center">
                                <?php echo acym_translation('ACYM_STATUS'); ?>
							</div>
							<div class="large-1 hide-for-small-only hide-for-medium-only text-center cell acym__listing__header__title">
                                <?php echo acym_translation('ACYM_OPEN'); ?>
							</div>
							<div class="large-1 hide-for-small-only hide-for-medium-only text-center cell acym__listing__header__title">
                                <?php echo acym_translation('ACYM_CLICK'); ?>
							</div>
							<div class="large-1 cell hide-for-small-only hide-for-medium-only text-center acym__listing__header__title">
                                <?php echo acym_translation('ACYM_ID'); ?>
							</div>
						</div>
					</div>
                    <?php
                    foreach ($data['allCampaigns'] as $campaign) {
                        if (isset($campaign->display) && !$campaign->display) {
                            continue;
                        }
                        ?>
						<div class="grid-x cell acym__listing__row">
							<div class="medium-shrink small-1 cell">
								<input id="checkbox_<?php echo acym_escape($campaign->id); ?>" type="checkbox" name="elements_checked[]" value="<?php echo acym_escape($campaign->id); ?>">
							</div>
							<div class="grid-x medium-auto small-11 cell acym__campaign__listing acym__listing__title__container">

								<div class="cell medium-auto small-7 acym__listing__title acym__campaign__title">
                                    <?php
                                    $linkTask = 'generated' == $data["status"] ? 'summaryGenerated' : 'edit&step=editEmail';
                                    ?>
									<a class="cell auto" href="<?php echo acym_completeLink('campaigns&task='.$linkTask.'&id='.intval($campaign->id)); ?>">
										<h6 class='acym__listing__title__primary acym_text_ellipsis'>
                                            <?php echo acym_escape($campaign->name); ?>
										</h6>
									</a>
									<p class='acym__listing__title__secondary'>
                                        <?php
                                        if (!empty($campaign->sending_date) && (!$campaign->scheduled || $campaign->sent)) {
                                            echo acym_translation('ACYM_SENDING_DATE').' : '.acym_date($campaign->sending_date, 'M. j, Y');
                                        } elseif ($data['statusAuto'] === $campaign->sending_type) {
                                            $numberCampaignsGenerated = empty($campaign->sending_params['number_generated']) ? '0' : $campaign->sending_params['number_generated'];
                                            echo acym_translation_sprintf('ACYM_X_CAMPAIGN_GENERATED', $numberCampaignsGenerated);
                                        }
                                        ?>
									</p>
								</div>
								<div class="large-3 medium-3 small-5 cell">
                                    <?php
                                    if (!empty($campaign->lists)) {
                                        echo '<div class="grid-x cell text-center">';
                                        foreach ($campaign->lists as $list) {
                                            echo acym_tooltip('<i class="acym_subscription fa fa-circle" style="color:'.acym_escape($list->color).'"></i>', acym_escape($list->name));
                                        }
                                        echo '</div>';
                                    } else {
                                        echo '<div class="cell medium-12">'.(empty($campaign->automation) ? acym_translation('ACYM_NO_LIST_SELECTED') : acym_translation('ACYM_SENT_WITH_AUTOMATION')).'</div>';
                                    }
                                    ?>
								</div>
								<div class="large-2 medium-4 small-11 text-center cell acym__campaign__status">
									<div class="grid-x text-center">
                                        <?php
                                        if ($campaign->sent) {
                                            if (!isset($campaign->subscribers)) $campaign->subscribers = 'x';
                                            echo '<div class="cell acym__campaign__status__status acym__background-color__green"><span>'.acym_translation('ACYM_SENT').' : '.acym_escape($campaign->subscribers).' '.acym_translation('ACYM_RECIPIENTS').'</span></div>';
                                        } elseif ($campaign->scheduled && !$campaign->draft) {
                                            echo '<div class="cell acym__campaign__status__status acym__background-color__orange"><span>'.acym_translation('ACYM_SCHEDULED').' : '.acym_date($campaign->sending_date, 'M. j, Y').'</span></div>';
                                            $target = '<div class="acym__campaign__listing__scheduled__stop grid-x cell xlarge-shrink acym_vcenter" data-campaignid="'.acym_escape($campaign->id).'"><i class="fa fa-times-circle cell shrink show-for-xlarge"></i><span class="cell xlarge-shrink">'.acym_translation('ACYM_CANCEL_SCHEDULING').'</span></div>';
                                            echo '<div class="cell acym__campaign__listing__status__controls"><div class="grid-x text-center"><div class="cell auto"></div>'.acym_tooltip($target, acym_translation('ACYM_STOP_THE_SCHEDULING_AND_SET_CAMPAIGN_AS_DRAFT')).'<div class="cell auto"></div></div></div>';
                                        } elseif ($data['statusAuto'] === $campaign->sending_type && !$campaign->draft) {
                                            echo '<div class="cell acym__campaign__status__status acym__background-color__purple"><span class="acym__color__white">'.acym_translation('ACYM_AUTO').'</span></div>';
                                            $target = '<div class="acym__campaign__listing__automatic__deactivate grid-x cell xlarge-shrink acym_vcenter" data-campaignid="'.acym_escape($campaign->id).'">
															<i class="'.($campaign->active ? 'fa-times-circle' : 'fa-power-off').' fa cell shrink show-for-xlarge"></i>
															<span class="cell xlarge-shrink">'.($campaign->active ? acym_translation('ACYM_DEACTIVATE') : acym_translation('ACYM_ACTIVATE')).'</span>
														</div>';
                                            echo '<div class="cell acym__campaign__listing__status__controls"><div class="grid-x text-center"><div class="cell auto"></div>'.$target.'<div class="cell auto"></div></div></div>';
                                        } elseif ('generated' == $data["status"] && $campaign->draft) {
                                            echo '<div class="cell acym__campaign__status__status acym__background-color__orange"><span>'.acym_translation('ACYM_WAITING_FOR_CONFIRMATION').'</span></div>';
                                        } elseif ('generated' == $data["status"] && !$campaign->active) {
                                            echo '<div class="cell acym__campaign__status__status acym__background-color__red"><span class="acym__color__white">'.acym_translation('ACYM_DISABLED').'</span></div>';
                                        } elseif ($campaign->draft) {
                                            echo '<div class="cell acym__campaign__status__status acym__campaign__status__draft"><span>'.acym_translation('ACYM_DRAFT').'</span></div>';
                                        }
                                        ?>
									</div>
								</div>
								<div class="large-1 hide-for-small-only hide-for-medium-only cell text-center">
                                    <?php
                                    if (!isset($campaign->open)) $campaign->open = 'x';
                                    echo $campaign->sent == 1 ? $campaign->open.'%' : "";
                                    ?>
								</div>
								<div class="large-1 hide-for-small-only hide-for-medium-only cell text-center">
									<!-- TODO a récupérer sur le mail-->
                                    <?php //echo $campaign->status == 'sent' || $campaign->status == 'automatic' ? $campaign->click.'%' : ""; ?>
								</div>
								<h6 class="large-1 hide-for-medium-only hide-for-small-only cell text-center acym__listing__text"><?php echo acym_escape($campaign->id); ?></h6>
							</div>
						</div>
                        <?php
                    }

                    ?>
				</div>
                <?php echo $data['pagination']->display('campaigns'); ?>
            <?php } ?>
        <?php } ?>
        <?php acym_formOptions(); ?>
	</div>
</form>

