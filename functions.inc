<?php

/* AdSense Plugin 1.0.0 */

if (!defined('GVERSION')) {
    die('This file can not be used on its own.');
}

global $_CONF, $_TABLES, $_DB_table_prefix, $_ADSENSE_CONF, $LANG_ADSENSE;

$adsensePluginPath = $_CONF['path'] . 'plugins/adsense/';

$langFile = $adsensePluginPath . 'language/' . $_CONF['language'] . '.php';
if (file_exists($langFile)) {
    require_once $langFile;
} else {
    require_once $adsensePluginPath . 'language/english.php';
}

$_TABLES['adsense_placements'] = $_DB_table_prefix . 'adsense_placements';

$_ADSENSE_CONF = array();

if (class_exists('config')) {
    $adsenseConfig = config::get_instance();
    $_ADSENSE_CONF = $adsenseConfig->get_config('adsense');

    if (!is_array($_ADSENSE_CONF)) {
        $_ADSENSE_CONF = array();
    }
}

function ADSENSE_config($name, $default)
{
    global $_ADSENSE_CONF;

    return isset($_ADSENSE_CONF[$name]) ? $_ADSENSE_CONF[$name] : $default;
}

function ADSENSE_mode()
{
    $mode = strtolower(trim((string) ADSENSE_config('serving_mode', 'disabled')));
    $allowed = array('disabled', 'auto', 'manual', 'hybrid');

    return in_array($mode, $allowed, true) ? $mode : 'disabled';
}

function ADSENSE_isPublisherId($publisherId)
{
    return preg_match('/^ca-pub-[0-9]{10,30}$/D', trim((string) $publisherId)) === 1;
}

function ADSENSE_isSlotId($slotId)
{
    return preg_match('/^[0-9]{5,30}$/D', trim((string) $slotId)) === 1;
}

function ADSENSE_normalizePlacementName($name)
{
    $name = strtolower(trim((string) $name));
    $name = preg_replace('/[^a-z0-9_-]+/', '-', $name);
    $name = trim($name, '-_');

    if (strlen($name) > 64) {
        $name = substr($name, 0, 64);
    }

    return $name;
}

function ADSENSE_allowedFormats()
{
    return array('auto', 'rectangle', 'horizontal', 'vertical');
}

function ADSENSE_normalizeFormat($format)
{
    $format = strtolower(trim((string) $format));

    return in_array($format, ADSENSE_allowedFormats(), true) ? $format : 'auto';
}

function ADSENSE_isAdminRequest()
{
    global $_CONF;

    $request = '';
    if (isset($_SERVER['SCRIPT_NAME'])) {
        $request = (string) $_SERVER['SCRIPT_NAME'];
    } elseif (isset($_SERVER['PHP_SELF'])) {
        $request = (string) $_SERVER['PHP_SELF'];
    }

    if ($request === '') {
        return false;
    }

    $adminPath = isset($_CONF['site_admin_url'])
        ? (string) parse_url($_CONF['site_admin_url'], PHP_URL_PATH)
        : '/admin';

    if ($adminPath === '') {
        $adminPath = '/admin';
    }

    return strpos($request, rtrim($adminPath, '/') . '/') !== false;
}

function ADSENSE_isDebugPreview()
{
    return (int) ADSENSE_config('debug_mode', 0) === 1
        && function_exists('SEC_hasRights')
        && SEC_hasRights('adsense.admin');
}

function ADSENSE_canServe()
{
    if ((int) ADSENSE_config('enabled', 0) !== 1) {
        return false;
    }

    if (ADSENSE_mode() === 'disabled' || ADSENSE_isAdminRequest()) {
        return false;
    }

    return ADSENSE_isPublisherId(ADSENSE_config('publisher_id', ''));
}

function ADSENSE_shouldLoadBootstrap()
{
    if (!ADSENSE_canServe() || ADSENSE_isDebugPreview()) {
        return false;
    }

    return in_array(ADSENSE_mode(), array('auto', 'manual', 'hybrid'), true);
}

function ADSENSE_bootstrapHtml()
{
    static $rendered = false;

    if ($rendered || !ADSENSE_shouldLoadBootstrap()) {
        return '';
    }

    $publisherId = trim((string) ADSENSE_config('publisher_id', ''));
    $rendered = true;

    return '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client='
        . rawurlencode($publisherId)
        . '" crossorigin="anonymous"></script>' . LB;
}

function plugin_getheadercode_adsense()
{
    return ADSENSE_bootstrapHtml();
}

function ADSENSE_debugPlacement($placement, $message)
{
    if (!ADSENSE_isDebugPreview()) {
        return '';
    }

    return '<div class="adsense-debug" style="border:1px dashed #888;padding:12px;margin:12px 0;text-align:center;"'
        . ' data-adsense-placement="' . htmlspecialchars($placement, ENT_QUOTES, 'UTF-8') . '">'
        . htmlspecialchars($message, ENT_QUOTES, 'UTF-8')
        . '</div>';
}

function ADSENSE_getPlacement($name)
{
    global $_TABLES;

    $name = ADSENSE_normalizePlacementName($name);
    if ($name === '' || empty($_TABLES['adsense_placements'])) {
        return false;
    }

    $result = DB_query(
        "SELECT placement_id, name, slot_id, format, responsive, enabled "
        . "FROM {$_TABLES['adsense_placements']} "
        . "WHERE name = '" . DB_escapeString($name) . "' LIMIT 1"
    );

    if (!$result || DB_numRows($result) < 1) {
        return false;
    }

    return DB_fetchArray($result);
}

function ADSENSE_getPlacements()
{
    global $_TABLES;

    $placements = array();

    if (empty($_TABLES['adsense_placements'])) {
        return $placements;
    }

    $result = DB_query(
        "SELECT placement_id, name, slot_id, format, responsive, enabled, created_at, updated_at "
        . "FROM {$_TABLES['adsense_placements']} ORDER BY name"
    );

    if (!$result) {
        return $placements;
    }

    while ($row = DB_fetchArray($result)) {
        $placements[] = $row;
    }

    return $placements;
}

function ADSENSE_savePlacement($data, $placementId)
{
    global $_TABLES;

    $name = isset($data['name']) ? ADSENSE_normalizePlacementName($data['name']) : '';
    $slotId = isset($data['slot_id']) ? trim((string) $data['slot_id']) : '';
    $format = isset($data['format']) ? ADSENSE_normalizeFormat($data['format']) : 'auto';
    $responsive = !empty($data['responsive']) ? 1 : 0;
    $enabled = !empty($data['enabled']) ? 1 : 0;
    $placementId = (int) $placementId;

    if ($name === '' || !ADSENSE_isSlotId($slotId)) {
        return false;
    }

    $duplicateWhere = "name = '" . DB_escapeString($name) . "'";
    if ($placementId > 0) {
        $duplicateWhere .= ' AND placement_id <> ' . $placementId;
    }

    $duplicateResult = DB_query(
        "SELECT COUNT(*) AS total FROM {$_TABLES['adsense_placements']} WHERE " . $duplicateWhere
    );
    if ($duplicateResult) {
        $duplicateRow = DB_fetchArray($duplicateResult);
        if (isset($duplicateRow['total']) && (int) $duplicateRow['total'] > 0) {
            return false;
        }
    }

    $now = date('Y-m-d H:i:s');

    if ($placementId > 0) {
        DB_query(
            "UPDATE {$_TABLES['adsense_placements']} SET "
            . "name='" . DB_escapeString($name) . "', "
            . "slot_id='" . DB_escapeString($slotId) . "', "
            . "format='" . DB_escapeString($format) . "', "
            . "responsive=" . $responsive . ', '
            . "enabled=" . $enabled . ', '
            . "updated_at='" . DB_escapeString($now) . "' "
            . 'WHERE placement_id=' . $placementId
        );
    } else {
        DB_save(
            $_TABLES['adsense_placements'],
            'name,slot_id,format,responsive,enabled,created_at,updated_at',
            "'" . DB_escapeString($name) . "','"
                . DB_escapeString($slotId) . "','"
                . DB_escapeString($format) . "',"
                . $responsive . ','
                . $enabled . ",'"
                . DB_escapeString($now) . "','"
                . DB_escapeString($now) . "'"
        );
    }

    return DB_error() === 0;
}

function ADSENSE_deletePlacement($placementId)
{
    global $_TABLES;

    $placementId = (int) $placementId;
    if ($placementId < 1) {
        return false;
    }

    DB_delete($_TABLES['adsense_placements'], 'placement_id', $placementId);

    return DB_error() === 0;
}

function ADSENSE_renderPlacement($placement)
{
    $placement = ADSENSE_normalizePlacementName($placement);

    if ($placement === '') {
        return '';
    }

    $mode = ADSENSE_mode();

    if ($mode !== 'manual' && $mode !== 'hybrid') {
        return ADSENSE_debugPlacement($placement, 'AdSense placement disabled by serving mode: ' . $mode);
    }

    $row = ADSENSE_getPlacement($placement);
    if ($row === false) {
        return ADSENSE_debugPlacement($placement, 'Unknown AdSense placement: ' . $placement);
    }

    if (empty($row['enabled'])) {
        return ADSENSE_debugPlacement($placement, 'Disabled AdSense placement: ' . $placement);
    }

    if (!ADSENSE_isSlotId($row['slot_id'])) {
        return ADSENSE_debugPlacement($placement, 'Invalid slot ID for placement: ' . $placement);
    }

    if (!ADSENSE_canServe()) {
        return ADSENSE_debugPlacement($placement, 'AdSense serving is unavailable for placement: ' . $placement);
    }

    if (ADSENSE_isDebugPreview()) {
        return ADSENSE_debugPlacement($placement, 'AdSense preview: ' . $placement . ' / slot ' . $row['slot_id']);
    }

    $publisherId = trim((string) ADSENSE_config('publisher_id', ''));
    $format = ADSENSE_normalizeFormat($row['format']);
    $responsive = !empty($row['responsive']) ? 'true' : 'false';

    return '<ins class="adsbygoogle" style="display:block"'
        . ' data-ad-client="' . htmlspecialchars($publisherId, ENT_QUOTES, 'UTF-8') . '"'
        . ' data-ad-slot="' . htmlspecialchars($row['slot_id'], ENT_QUOTES, 'UTF-8') . '"'
        . ' data-ad-format="' . htmlspecialchars($format, ENT_QUOTES, 'UTF-8') . '"'
        . ' data-full-width-responsive="' . $responsive . '"></ins>'
        . '<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>';
}

function ADSENSE_tagList($value)
{
    if (is_array($value)) {
        return $value;
    }

    if ($value === '' || $value === null) {
        return array();
    }

    return array($value);
}

function ADSENSE_otherAutotagOwners()
{
    global $_PLUGINS;

    $owners = array();

    if (!is_array($_PLUGINS)) {
        return $owners;
    }

    foreach ($_PLUGINS as $pluginName) {
        if ($pluginName === 'adsense') {
            continue;
        }

        $function = 'plugin_autotags_' . $pluginName;
        if (!function_exists($function)) {
            continue;
        }

        $tags = ADSENSE_tagList($function('tagname'));

        foreach ($tags as $tag) {
            $tag = strtolower(trim((string) $tag));
            if ($tag !== '' && !isset($owners[$tag])) {
                $owners[$tag] = $pluginName;
            }
        }
    }

    return $owners;
}

function ADSENSE_legacyAliasMap()
{
    return array(
        'adsense' => ADSENSE_config('legacy_adsense_placement', 'ad-default'),
        'inarticle' => ADSENSE_config('legacy_inarticle_placement', 'article-middle'),
        'infeed' => ADSENSE_config('legacy_infeed_placement', 'feed'),
        'leaderboard' => ADSENSE_config('legacy_leaderboard_placement', 'leaderboard')
    );
}

function ADSENSE_availableLegacyAliases()
{
    if ((int) ADSENSE_config('legacy_aliases', 0) !== 1) {
        return array();
    }

    $owners = ADSENSE_otherAutotagOwners();
    $aliases = array();

    foreach (ADSENSE_legacyAliasMap() as $tag => $placement) {
        if (!isset($owners[$tag])) {
            $aliases[$tag] = ADSENSE_normalizePlacementName($placement);
        }
    }

    return $aliases;
}

function ADSENSE_autotagPlacement($autotag)
{
    $tag = isset($autotag['tag']) ? strtolower(trim((string) $autotag['tag'])) : 'ad';

    if ($tag === 'ad') {
        return isset($autotag['parm1'])
            ? ADSENSE_normalizePlacementName($autotag['parm1'])
            : '';
    }

    $aliases = ADSENSE_availableLegacyAliases();

    return isset($aliases[$tag]) ? $aliases[$tag] : '';
}

function plugin_autotags_adsense($op, $content = '', $autotag = array())
{
    global $LANG_ADSENSE;

    if ($op === 'tagname') {
        $tags = array('ad');

        foreach (array_keys(ADSENSE_availableLegacyAliases()) as $legacyTag) {
            $tags[] = $legacyTag;
        }

        return $tags;
    }

    if ($op === 'description') {
        $description = isset($LANG_ADSENSE['autotag_description'])
            ? $LANG_ADSENSE['autotag_description']
            : '[ad:PLACEMENT] renders a named AdSense placement.';

        return array('ad' => $description);
    }

    if ($op === 'permission') {
        return plugin_autotags_adsense('tagname');
    }

    if ($op === 'nopermission' || $op === 'closetag') {
        return array();
    }

    if ($op !== 'parse') {
        return $content;
    }

    if (!isset($autotag['tagstr'])) {
        return $content;
    }

    $placement = ADSENSE_autotagPlacement($autotag);
    if ($placement === '') {
        return $content;
    }

    return str_replace(
        $autotag['tagstr'],
        ADSENSE_renderPlacement($placement),
        $content
    );
}

function plugin_getadminoption_adsense()
{
    global $_CONF, $LANG_ADSENSE;

    if (!SEC_hasRights('adsense.admin')) {
        return array();
    }

    $label = isset($LANG_ADSENSE['plugin_name']) ? $LANG_ADSENSE['plugin_name'] : 'AdSense';

    return array(
        $label,
        $_CONF['site_admin_url'] . '/plugins/adsense/index.php',
        ''
    );
}

function plugin_cclabel_adsense()
{
    return plugin_getadminoption_adsense();
}

function plugin_chkVersion_adsense()
{
    global $_CONF;

    require_once $_CONF['path'] . 'plugins/adsense/autoinstall.php';
    $params = plugin_autoinstall_adsense('adsense');

    return $params['info']['pi_version'];
}

function plugin_upgrade_adsense()
{
    global $_CONF, $_TABLES;

    require_once $_CONF['path'] . 'plugins/adsense/autoinstall.php';
    require_once $_CONF['path'] . 'plugins/adsense/install_defaults.php';

    if (!plugin_compatible_with_this_version_adsense('adsense')) {
        return 3002;
    }

    if (!plugin_initconfig_adsense()) {
        return false;
    }

    DB_query(
        "UPDATE {$_TABLES['plugins']} "
        . "SET pi_version = '1.0.0', pi_gl_version = '2.1.1' "
        . "WHERE pi_name = 'adsense'"
    );

    return true;
}

function plugin_autouninstall_adsense()
{
    return array(
        'tables'     => array('adsense_placements'),
        'groups'     => array('AdSense Admin'),
        'features'   => array('adsense.admin', 'config.adsense.tab_main'),
        'php_blocks' => array(),
        'vars'       => array()
    );
}
