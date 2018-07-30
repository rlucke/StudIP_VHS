<? use \Studip\Button; ?>
<a name="autoren"></a>


<form action="<?= $controller->url_for('course/members/edit_autor') ?>" method="post" data-dialog="">
    <?= CSRFProtection::tokenTag() ?>
    <table id="autor" class="default collapsable tablesorter">
        <caption>
        <? if ($is_tutor) : ?>
            <span class="actions">
                <a href="<?= URLHelper::getLink('dispatch.php/messages/write',
                    array('filter' => 'send_sms_to_all',
                        'emailrequest' => 1,
                        'who' => 'autor',
                        'course_id' => $course_id,
                        'default_subject' => $subject))
                ?>" data-dialog>
                    <?= Icon::create('inbox', 'clickable', ['title' => sprintf(_('Nachricht mit Mailweiterleitung an alle %s versenden'),htmlReady($status_groups['autor']))])->asImg(16) ?>
                </a>
           </span>
       <? endif ?>
            <?= $status_groups['autor'] ?>
        </caption>
        <colgroup>
            <col width="20">
            <? if($is_tutor) : ?>
                <? if (!$is_locked) : ?>
                <col width="20">
                <? endif ?>
                <col>
                <col width="15%">
                <? $cols = 6 ?>
                <col width="35%">
                <? $cols_foot = 6?>
            <? else : ?>
                <col>
                <? $cols = 3 ?>
            <? endif ?>

            <col width="80">
        </colgroup>
        <thead>
            <tr class="sortable">
                <? if ($is_tutor && !$is_locked) : ?>
                    <th><input aria-label="<?= sprintf(_('Alle %s ausw�hlen'), $status_groups['autor']) ?>"
                           type="checkbox" name="all" value="1" data-proxyfor=":checkbox[name^=autor]">
                    </th>
                <? endif ?>
                <th></th>
                <th><?=get_title_for_status('autor', 2)?></th>
                <? if($is_tutor) :?>
                <th <?= ($sort_by == 'mkdate' && $sort_status == 'autor') ? sprintf('class="sort%s"', $order) : '' ?>>
                    <a href="<?= URLHelper::getLink(sprintf('?sortby=mkdate&sort_status=autor&order=%s&toggle=%s',
                       $order, ($sort_by == 'mkdate'))) ?>#autoren">
                        <?= _('Anmeldedatum') ?>
                    </a>
                </th>
                <th><?= _('Studiengang') ?></th>
                <? endif ?>
                <th style="text-align: right"><?= _('Aktion') ?></th>
            </tr>
        </thead>
        <tbody>
        <? $nr = $autor_nr?>
        <? foreach($autoren as $autor) : ?>
        <? $fullname = $autor['fullname']?>
            <tr>
                <? if ($is_tutor && !$is_locked) : ?>
                    <td>
                        <input aria-label="<?= sprintf(_('%s ausw�hlen'), $status_groups['autor']) ?>"
                               type="checkbox" name="autor[<?= $autor['user_id'] ?>]" value="1" />
                    </td>
                <? endif ?>
                <td style="text-align: right"><?= (++$nr < 10) ? sprintf('%02d', $nr) : $nr ?></td>
                <td>
                    <a href="<?= URLHelper::getLink("dispatch.php/profile?username=".$autor['username']) ?>" <? if ($autor['mkdate'] >= $last_visitdate) echo 'class="new-member"'; ?>>
                        <?= Avatar::getAvatar($autor['user_id'], $autor['username'])->getImageTag(Avatar::SMALL,
                                array('style' => 'margin-right: 5px', 'title' => htmlReady($fullname))); ?>
                        <?= htmlReady($fullname) ?>
                    <? if ($user_id == $autor['user_id'] && $autor['visible'] == 'no') : ?>
                       (<?= _('unsichtbar') ?>)
                   <? endif ?>
                    </a>
                    <? if ($is_tutor && $autor['comment'] != '') : ?>
                        <?= tooltipHtmlIcon(sprintf('<strong>%s</strong><br>%s', _('Bemerkung'), htmlReady($autor['comment']))) ?>
                    <? endif ?>
                </td>
                <? if ($is_tutor) : ?>
                    <td>
                        <? if(!empty($autor['mkdate'])) : ?>
                            <?= strftime('%x %X', $autor['mkdate'])?>
                        <? endif ?>
                    </td>
                    <td>
                        <?= $this->render_partial("course/members/_studycourse.php", array('study_courses' => UserModel::getUserStudycourse($autor['user_id']))) ?>
                    </td>
                <? endif ?>

                <td style="text-align: right">
                    <? if ($is_tutor) : ?>
                        <a data-dialog title='<?= _('Bemerkung hinzuf�gen') ?>' href="<?=$controller->url_for('course/members/add_comment', $autor['user_id']) ?>">
                            <?= Icon::create('comment', 'clickable')->asImg() ?>
                        </a>
                    <? endif ?>
                    <? if($user_id != $autor['user_id']) : ?>
                        <a href="<?= URLHelper::getLink('dispatch.php/messages/write',
                                    array('filter' => 'send_sms_to_all',
                                    'emailrequest' => 1,
                                    'rec_uname' => $autor['username'],
                                    'default_subject' => $subject))
                                ?>
                        " data-dialog>
                            <?= Icon::create('mail', 'clickable', ['title' => sprintf(_('Nachricht mit Mailweiterleitung an %s senden'),htmlReady($fullname))])->asImg(16) ?>
                        </a>
                    <? endif ?>
                    <? if ($is_tutor && !$is_locked) : ?>
                        <a href="<?= $controller->url_for(sprintf('course/members/cancel_subscription/singleuser/autor/%s',
                                    $autor['user_id'])) ?>">
                            <?= Icon::create('door-leave', 'clickable', ['title' => sprintf(_('%s austragen'),htmlReady($fullname))])->asImg(16) ?>
                        </a>
                    <? endif ?>
                </td>
            </tr>
        <? endforeach ?>
        <? if ($invisibles > 0) : ?>
            <tr>
                <td colspan="<?=$cols?>" class="blank"></td>
            </tr>
            <tr>
                <td colspan="<?=$cols?>">+ <?= sprintf(_('%u unsichtbare %s'), $invisibles, $status_groups['autor']) ?></td>
            </tr>
        <? endif ?>

        </tbody>
        <? if ($is_tutor && !$is_locked && count($autoren) >0) : ?>
        <tfoot>
            <tr>
                <td colspan="<?=$cols_foot?>">
                    <select name="action_autor" id="action_autor" aria-label="<?= _('Aktion ausf�hren') ?>">
                        <option value="">- <?= _('Aktion w�hlen') ?></option>
                        <? if($is_dozent) : ?>
                        <option value="upgrade"><?= sprintf(_('Zu %s hochstufen'),
                            htmlReady($status_groups['tutor'])) ?></option>
                        <? endif ?>
                        <option value="downgrade"><?= sprintf(_('Zu %s herunterstufen'),
                                htmlReady($status_groups['user'])) ?></option>
                            <?php if ($to_waitlist_actions) : ?>
                            <option value="to_admission_first"><?= _('An den Anfang der Warteliste verschieben') ?></option>
                            <option value="to_admission_last"><?= _('Ans Ende der Warteliste verschieben') ?></option>
                            <?php endif ?>
                        <option value="remove"><?= _('Austragen') ?></option>
                            <?php if($is_dozent) : ?>
                            <option value="to_course"><?= _('In andere Veranstaltung verschieben/kopieren') ?></option>
                            <?php endif ?>
                        <option value="message"><?=_('Nachricht senden')?></option>
                    </select>
                    <?= Button::create(_('Ausf�hren'), 'submit_autor') ?>
                </td>
            </tr>
        </tfoot>
        <? endif ?>
    </table>
</form>


