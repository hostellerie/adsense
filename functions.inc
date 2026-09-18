<?php

/* AdSense Plugin 1.0.0 */

if (!defined('GVERSION')) {
    die('This file can not be used on its own.');
}

global $_ADSENSE_CONF;

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

    if (!in_array($mode, $allowed, true)) {
        return 'disabled';
    }

    return $mode;
}

function ADSENSE_isPublisherId($publisherId)
{
    return preg_match('/^ca-pub-[0-9]{10,30}$/', trim((string) $publisherId)) === 1;
}

function ADSENSE_isAdminRequest()
{
    global $_CONF;

    if (defined('SECURITY_ADMIN') || defined('ADMIN_MENU')) {
        return true;
    }

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

function ADSENSE_canServe()
{
    if ((int) ADSENSE_config('enabled', 0) !== 1) {
        return false;
    }

    if (ADSENSE_mode() === 'disabled') {
        return false;
    }

    if (ADSENSE_isAdminRequest()) {
        return false;
    }

    return ADSENSE_isPublisherId(ADSENSE_config('publisher_id', ''));
}

function ADSENSE_shouldLoadBootstrap()
{
    if (!ADSENSE_canServe()) {
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

function ADSENSE_debugPlacement($placement)
{
    if ((int) ADSENSE_config('debug_mode', 0) !== 1) {
        return '';
    }

    if (!SEC_hasRights('adsense.admin')) {
        return '';
    }

    return '<div class="adsense-debug" data-adsense-placement="'
        . htmlspecialchars($placement, ENT_QUOTES, 'UTF-8')
        . '">AdSense placement: '
        . htmlspecialchars($placement, ENT_QUOTES, 'UTF-8')
        . '</div>';
}

function ADSENSE_renderPlacement($placement)
{
    $placement = strtolower(trim((string) $placement));
    $placement = preg_replace('/[^a-z0-9_-]/', '', $placement);

    if ($placement === '') {
        return '';
    }

    if (!ADSENSE_canServe()) {
        return ADSENSE_debugPlacement($placement);
    }

    /*
     * Named placement storage is intentionally not hard-coded in this first
     * foundation commit. Until the placement model is implemented, manual
     * placements fail safely instead of embedding slot IDs in content.
     */
    return ADSENSE_debugPlacement($placement);
}

function plugin_autotags_adsense($op, $content = '', $autotag = array())
{
    global $LANG_ADSENSE;

    if ($op === 'tagname') {
        return 'ad';
    }

    if ($op === 'description') {
        $description = isset($LANG_ADSENSE['autotag_description'])
            ? $LANG_ADSENSE['autotag_description']
            : '[ad:PLACEMENT] renders a named AdSense placement.';

        return array('ad' => $description);
    }

    if ($op === 'permission') {
        return array('ad');
    }

    if ($op === 'nopermission' || $op === 'closetag') {
        return array();
    }

    if ($op !== 'parse') {
        return $content;
    }

    $placement = '';

    if (isset($autotag['parm1'])) {
        $placement = trim((string) $autotag['parm1']);
    }

    if ($placement === '' && isset($autotag['tagstr'])) {
        $tagstr = trim((string) $autotag['tagstr']);
        if (stripos($tagstr, '[ad:') === 0 && substr($tagstr, -1) === ']') {
            $placement = substr($tagstr, 4, -1);
        }
    }

    if ($placement === '' || !isset($autotag['tagstr'])) {
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
        $_CONF['site_admin_url'] . '/configuration.php?conf_group=adsense',
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
        'tables'     => array(),
        'groups'     => array('AdSense Admin'),
        'features'   => array('adsense.admin', 'config.adsense.tab_main'),
        'php_blocks' => array(),
        'vars'       => array()
    );
}
