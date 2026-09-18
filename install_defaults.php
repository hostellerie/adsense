<?php

/* AdSense Plugin 1.0.0 - configuration defaults */

if (stripos($_SERVER['PHP_SELF'], basename(__FILE__)) !== false) {
    die('This file can not be used on its own.');
}

global $_ADSENSE_DEFAULT;

$_ADSENSE_DEFAULT = array(
    'enabled'                      => 0,
    'serving_mode'                 => 'disabled',
    'publisher_id'                 => '',
    'legacy_aliases'               => 0,
    'legacy_adsense_placement'     => 'ad-default',
    'legacy_inarticle_placement'   => 'article-middle',
    'legacy_infeed_placement'      => 'feed',
    'legacy_leaderboard_placement' => 'leaderboard',
    'debug_mode'                   => 0
);

function plugin_initconfig_adsense()
{
    global $_ADSENSE_DEFAULT;

    $c = config::get_instance();

    if (!$c->group_exists('adsense')) {
        $c->add('sg_main', NULL, 'subgroup', 0, 0, NULL, 0, true, 'adsense', 0);
        $c->add('tab_main', NULL, 'tab', 0, 0, NULL, 0, true, 'adsense', 0);
        $c->add('fs_main', NULL, 'fieldset', 0, 0, NULL, 0, true, 'adsense', 0);
        $c->add('fs_legacy', NULL, 'fieldset', 0, 0, NULL, 100, true, 'adsense', 0);

        $c->add('enabled', $_ADSENSE_DEFAULT['enabled'], 'select',
            0, 0, 0, 10, true, 'adsense', 0);
        $c->add('serving_mode', $_ADSENSE_DEFAULT['serving_mode'], 'select',
            0, 0, 1, 20, true, 'adsense', 0);
        $c->add('publisher_id', $_ADSENSE_DEFAULT['publisher_id'], 'text',
            0, 0, 0, 30, true, 'adsense', 0);
        $c->add('debug_mode', $_ADSENSE_DEFAULT['debug_mode'], 'select',
            0, 0, 0, 40, true, 'adsense', 0);

        $c->add('legacy_aliases', $_ADSENSE_DEFAULT['legacy_aliases'], 'select',
            0, 0, 0, 110, true, 'adsense', 0);
        $c->add('legacy_adsense_placement', $_ADSENSE_DEFAULT['legacy_adsense_placement'], 'text',
            0, 0, 0, 120, true, 'adsense', 0);
        $c->add('legacy_inarticle_placement', $_ADSENSE_DEFAULT['legacy_inarticle_placement'], 'text',
            0, 0, 0, 130, true, 'adsense', 0);
        $c->add('legacy_infeed_placement', $_ADSENSE_DEFAULT['legacy_infeed_placement'], 'text',
            0, 0, 0, 140, true, 'adsense', 0);
        $c->add('legacy_leaderboard_placement', $_ADSENSE_DEFAULT['legacy_leaderboard_placement'], 'text',
            0, 0, 0, 150, true, 'adsense', 0);
    }

    return true;
}
