<?php

/* AdSense Plugin 1.0.0 - placement administration */

require_once dirname(__FILE__) . '/../../../lib-common.php';
require_once dirname(__FILE__) . '/../../auth.inc.php';

if (!SEC_hasRights('adsense.admin')) {
    COM_accessLog('User tried to access AdSense administration without permission.');
    $content = COM_showMessageText($MESSAGE[29], $MESSAGE[30]);
    COM_output(COM_createHTMLDocument($content, array('pagetitle' => $MESSAGE[30])));
    exit;
}

global $_CONF, $LANG_ADSENSE;

$message = '';

if (isset($_POST['save_placement'])) {
    if (!SEC_checkToken()) {
        $message = COM_showMessageText(
            $LANG_ADSENSE['invalid_token'],
            $LANG_ADSENSE['admin_title']
        );
    } else {
        $placementId = isset($_POST['placement_id']) ? (int) $_POST['placement_id'] : 0;
        $candidate = array(
            'name' => isset($_POST['name']) ? $_POST['name'] : '',
            'slot_id' => isset($_POST['slot_id']) ? $_POST['slot_id'] : '',
            'format' => isset($_POST['format']) ? $_POST['format'] : 'auto',
            'responsive' => !empty($_POST['responsive']),
            'enabled' => !empty($_POST['enabled'])
        );

        if (ADSENSE_savePlacement($candidate, $placementId)) {
            $message = COM_showMessageText(
                $LANG_ADSENSE['placement_saved'],
                $LANG_ADSENSE['admin_title']
            );
        } else {
            $message = COM_showMessageText(
                $LANG_ADSENSE['placement_save_failed'],
                $LANG_ADSENSE['admin_title']
            );
        }
    }
}

if (isset($_POST['delete_placement'])) {
    if (!SEC_checkToken()) {
        $message = COM_showMessageText(
            $LANG_ADSENSE['invalid_token'],
            $LANG_ADSENSE['admin_title']
        );
    } else {
        $placementId = isset($_POST['placement_id']) ? (int) $_POST['placement_id'] : 0;

        if (ADSENSE_deletePlacement($placementId)) {
            $message = COM_showMessageText(
                $LANG_ADSENSE['placement_deleted'],
                $LANG_ADSENSE['admin_title']
            );
        } else {
            $message = COM_showMessageText(
                $LANG_ADSENSE['placement_delete_failed'],
                $LANG_ADSENSE['admin_title']
            );
        }
    }
}

$placements = ADSENSE_getPlacements();
$owners = ADSENSE_otherAutotagOwners();
$legacyMap = ADSENSE_legacyAliasMap();
$availableAliases = ADSENSE_availableLegacyAliases();
$token = SEC_createToken();
$configUrl = $_CONF['site_admin_url'] . '/configuration.php?conf_group=adsense';

$content = COM_startBlock(
    $LANG_ADSENSE['admin_title'],
    '',
    COM_getBlockTemplate('_admin_block', 'header')
);

$content .= $message;
$content .= '<p>' . htmlspecialchars($LANG_ADSENSE['admin_intro'], ENT_QUOTES, 'UTF-8') . '</p>';
$content .= '<p><a href="' . htmlspecialchars($configUrl, ENT_QUOTES, 'UTF-8') . '">'
    . htmlspecialchars($LANG_ADSENSE['open_configuration'], ENT_QUOTES, 'UTF-8')
    . '</a></p>';

$content .= '<h2>' . htmlspecialchars($LANG_ADSENSE['placements_title'], ENT_QUOTES, 'UTF-8') . '</h2>';
$content .= '<p>' . htmlspecialchars($LANG_ADSENSE['placements_help'], ENT_QUOTES, 'UTF-8') . '</p>';

$content .= '<style>'
    . '.adsense-admin-table{width:100%;border-collapse:collapse}'
    . '.adsense-admin-table th,.adsense-admin-table td{padding:.45rem;vertical-align:top}'
    . '.adsense-admin-table input[type=text],.adsense-admin-table select{width:100%;box-sizing:border-box}'
    . '.adsense-admin-actions{white-space:nowrap}'
    . '.adsense-alias-ok{color:inherit}'
    . '.adsense-alias-conflict{font-weight:700}'
    . '@media(max-width:900px){.adsense-admin-wrap{overflow-x:auto}.adsense-admin-table{min-width:760px}}'
    . '</style>';

$content .= '<div class="adsense-admin-wrap"><table class="admin-list adsense-admin-table">';
$content .= '<thead><tr>'
    . '<th>' . htmlspecialchars($LANG_ADSENSE['placement_name'], ENT_QUOTES, 'UTF-8') . '</th>'
    . '<th>' . htmlspecialchars($LANG_ADSENSE['slot_id'], ENT_QUOTES, 'UTF-8') . '</th>'
    . '<th>' . htmlspecialchars($LANG_ADSENSE['format'], ENT_QUOTES, 'UTF-8') . '</th>'
    . '<th>' . htmlspecialchars($LANG_ADSENSE['responsive'], ENT_QUOTES, 'UTF-8') . '</th>'
    . '<th>' . htmlspecialchars($LANG_ADSENSE['enabled'], ENT_QUOTES, 'UTF-8') . '</th>'
    . '<th>' . htmlspecialchars($LANG_ADSENSE['actions'], ENT_QUOTES, 'UTF-8') . '</th>'
    . '</tr></thead><tbody>';

foreach ($placements as $placement) {
    $content .= '<tr><form method="post" action="">';
    $content .= '<td><input type="text" name="name" maxlength="64" value="'
        . htmlspecialchars($placement['name'], ENT_QUOTES, 'UTF-8') . '"></td>';
    $content .= '<td><input type="text" name="slot_id" maxlength="32" value="'
        . htmlspecialchars($placement['slot_id'], ENT_QUOTES, 'UTF-8') . '"></td>';
    $content .= '<td><select name="format">';

    foreach (ADSENSE_allowedFormats() as $format) {
        $content .= '<option value="' . htmlspecialchars($format, ENT_QUOTES, 'UTF-8') . '"'
            . ($placement['format'] === $format ? ' selected' : '') . '>'
            . htmlspecialchars($LANG_ADSENSE['format_' . $format], ENT_QUOTES, 'UTF-8')
            . '</option>';
    }

    $content .= '</select></td>';
    $content .= '<td><input type="checkbox" name="responsive" value="1"'
        . (!empty($placement['responsive']) ? ' checked' : '') . '></td>';
    $content .= '<td><input type="checkbox" name="enabled" value="1"'
        . (!empty($placement['enabled']) ? ' checked' : '') . '></td>';
    $content .= '<td class="adsense-admin-actions">'
        . '<input type="hidden" name="placement_id" value="' . (int) $placement['placement_id'] . '">'
        . '<input type="hidden" name="' . CSRF_TOKEN . '" value="'
        . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">'
        . '<button type="submit" name="save_placement" value="1">'
        . htmlspecialchars($LANG_ADSENSE['save'], ENT_QUOTES, 'UTF-8') . '</button> '
        . '<button type="submit" name="delete_placement" value="1"'
        . ' onclick="return confirm('
        . htmlspecialchars(json_encode($LANG_ADSENSE['confirm_delete']), ENT_QUOTES, 'UTF-8')
        . ');">'
        . htmlspecialchars($LANG_ADSENSE['delete'], ENT_QUOTES, 'UTF-8') . '</button>'
        . '</td>';
    $content .= '</form></tr>';
}

$content .= '<tr><form method="post" action="">';
$content .= '<td><input type="text" name="name" maxlength="64" placeholder="article-middle"></td>';
$content .= '<td><input type="text" name="slot_id" maxlength="32" placeholder="1234567890"></td>';
$content .= '<td><select name="format">';

foreach (ADSENSE_allowedFormats() as $format) {
    $content .= '<option value="' . htmlspecialchars($format, ENT_QUOTES, 'UTF-8') . '">'
        . htmlspecialchars($LANG_ADSENSE['format_' . $format], ENT_QUOTES, 'UTF-8')
        . '</option>';
}

$content .= '</select></td>';
$content .= '<td><input type="checkbox" name="responsive" value="1" checked></td>';
$content .= '<td><input type="checkbox" name="enabled" value="1" checked></td>';
$content .= '<td class="adsense-admin-actions">'
    . '<input type="hidden" name="placement_id" value="0">'
    . '<input type="hidden" name="' . CSRF_TOKEN . '" value="'
    . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">'
    . '<button type="submit" name="save_placement" value="1">'
    . htmlspecialchars($LANG_ADSENSE['add'], ENT_QUOTES, 'UTF-8') . '</button>'
    . '</td>';
$content .= '</form></tr>';

$content .= '</tbody></table></div>';

$content .= '<h2>' . htmlspecialchars($LANG_ADSENSE['legacy_title'], ENT_QUOTES, 'UTF-8') . '</h2>';
$content .= '<p>' . htmlspecialchars($LANG_ADSENSE['legacy_help'], ENT_QUOTES, 'UTF-8') . '</p>';
$content .= '<table class="admin-list adsense-admin-table"><thead><tr>'
    . '<th>' . htmlspecialchars($LANG_ADSENSE['autotag'], ENT_QUOTES, 'UTF-8') . '</th>'
    . '<th>' . htmlspecialchars($LANG_ADSENSE['mapped_placement'], ENT_QUOTES, 'UTF-8') . '</th>'
    . '<th>' . htmlspecialchars($LANG_ADSENSE['status'], ENT_QUOTES, 'UTF-8') . '</th>'
    . '</tr></thead><tbody>';

foreach ($legacyMap as $tag => $placement) {
    $status = '';
    $class = 'adsense-alias-ok';

    if (isset($owners[$tag])) {
        $status = sprintf($LANG_ADSENSE['owned_by_plugin'], $owners[$tag]);
        $class = 'adsense-alias-conflict';
    } elseif ((int) ADSENSE_config('legacy_aliases', 0) !== 1) {
        $status = $LANG_ADSENSE['aliases_disabled'];
    } elseif (isset($availableAliases[$tag])) {
        $status = $LANG_ADSENSE['alias_available'];
    } else {
        $status = $LANG_ADSENSE['alias_unavailable'];
        $class = 'adsense-alias-conflict';
    }

    $content .= '<tr><td><code>[' . htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') . ']</code></td>'
        . '<td><code>' . htmlspecialchars($placement, ENT_QUOTES, 'UTF-8') . '</code></td>'
        . '<td class="' . $class . '">' . htmlspecialchars($status, ENT_QUOTES, 'UTF-8') . '</td></tr>';
}

$content .= '<tr><td><code>[amazon]</code></td><td>—</td><td>'
    . htmlspecialchars($LANG_ADSENSE['external_unchanged'], ENT_QUOTES, 'UTF-8') . '</td></tr>';
$content .= '<tr><td><code>[youtube]</code></td><td>—</td><td>'
    . htmlspecialchars($LANG_ADSENSE['external_unchanged'], ENT_QUOTES, 'UTF-8') . '</td></tr>';
$content .= '</tbody></table>';

$content .= '<h2>' . htmlspecialchars($LANG_ADSENSE['usage_title'], ENT_QUOTES, 'UTF-8') . '</h2>';
$content .= '<p><code>[ad:article-middle]</code></p>';
$content .= '<p>' . htmlspecialchars($LANG_ADSENSE['usage_help'], ENT_QUOTES, 'UTF-8') . '</p>';

$content .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

COM_output(COM_createHTMLDocument($content, array(
    'pagetitle' => $LANG_ADSENSE['admin_title']
)));
