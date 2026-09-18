<?php

/* AdSense Plugin 1.0.0 - configuration defaults */

if (stripos($_SERVER['PHP_SELF'], basename(__FILE__)) !== false) {
    die('This file can not be used on its own.');
}

global $_ADSENSE_DEFAULT;

$_ADSENSE_DEFAULT = array(
    'enabled'        => 0,
    'serving_mode'   => 'disabled',
    'publisher_id'   => '',
    'legacy_aliases' => 0,
    'debug_mode'     => 0
);

function plugin_initconfig_adsense()
{
    global $_ADSENSE_DEFAULT;

    $c = config::get_instance();

    if (!$c->group_exists('adsense')) {
        $c->add('sg_main', NULL, 'subgroup', 0, 0, NULL, 0, true, 'adsense', 0);
        $c->add('tab_main', NULL, 'tab', 0, 0, NULL, 0, true, 'adsense', 0);
        $c->add('fs_main', NULL, 'fieldset', 0, 0, NULL, 0, true, 'adsense', 0);

        $c->add(
            'enabled',
            $_ADSENSE_DEFAULT['enabled'],
            'select',
            0,
            0,
            0,
            10,
            true,
            'adsense',
            0
        );

        $c->add(
            'serving_mode',
            $_ADSENSE_DEFAULT['serving_mode'],
            'select',
            0,
            0,
            1,
            20,
            true,
            'adsense',
            0
        );

        $c->add(
            'publisher_id',
            $_ADSENSE_DEFAULT['publisher_id'],
            'text',
            0,
            0,
            0,
            30,
            true,
            'adsense',
            0
        );

        $c->add(
            'legacy_aliases',
            $_ADSENSE_DEFAULT['legacy_aliases'],
            'select',
            0,
            0,
            0,
            40,
            true,
            'adsense',
            0
        );

        $c->add(
            'debug_mode',
            $_ADSENSE_DEFAULT['debug_mode'],
            'select',
            0,
            0,
            0,
            50,
            true,
            'adsense',
            0
        );
    }

    return true;
}
