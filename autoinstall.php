<?php

/* AdSense Plugin 1.0.0 */

if (stripos($_SERVER['PHP_SELF'], basename(__FILE__)) !== false) {
    die('This file can not be used on its own.');
}

function plugin_autoinstall_adsense($pi_name)
{
    $piName = 'adsense';
    $displayName = 'AdSense';
    $adminGroup = $displayName . ' Admin';

    return array(
        'info' => array(
            'pi_name'         => $piName,
            'pi_display_name' => $displayName,
            'pi_version'      => '1.0.0',
            'pi_gl_version'   => '2.1.1',
            'pi_homepage'     => 'https://github.com/hostellerie/adsense'
        ),
        'groups' => array(
            $adminGroup => 'Users in this group can administer the AdSense plugin'
        ),
        'features' => array(
            $piName . '.admin' => 'Full access to the AdSense plugin',
            'config.' . $piName . '.tab_main' => 'Access to AdSense configuration'
        ),
        'mappings' => array(
            $piName . '.admin' => array($adminGroup),
            'config.' . $piName . '.tab_main' => array($adminGroup)
        ),
        'tables' => array('adsense_placements')
    );
}

function plugin_load_configuration_adsense($pi_name)
{
    global $_CONF;

    require_once $_CONF['path_system'] . 'classes/config.class.php';

    $defaults = $_CONF['path'] . 'plugins/' . $pi_name . '/install_defaults.php';

    if (!file_exists($defaults)) {
        return false;
    }

    require_once $defaults;

    return function_exists('plugin_initconfig_' . $pi_name)
        && call_user_func('plugin_initconfig_' . $pi_name);
}

function plugin_compatible_with_this_version_adsense($pi_name)
{
    global $_CONF, $_DB_dbms;

    if (defined('VERSION') && version_compare(VERSION, '2.1.1', '<')) {
        return false;
    }

    if (version_compare(PHP_VERSION, '5.6.0', '<')) {
        return false;
    }

    $dbFile = $_CONF['path'] . 'plugins/' . $pi_name . '/sql/' . $_DB_dbms . '_install.php';

    return class_exists('config') && file_exists($dbFile);
}

function plugin_postinstall_adsense($pi_name)
{
    return true;
}
