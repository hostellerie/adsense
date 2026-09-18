<?php

/* English language file for AdSense 1.0.0 */

$LANG_ADSENSE = array(
    'plugin_name' => 'AdSense',
    'autotag_description' => '[ad:PLACEMENT] renders a named AdSense placement.'
);

$LANG_configsections['adsense'] = array(
    'label' => 'AdSense',
    'title' => 'AdSense Configuration'
);

$LANG_confignames['adsense'] = array(
    'enabled' => 'Enable AdSense serving?',
    'serving_mode' => 'Serving mode',
    'publisher_id' => 'Publisher ID',
    'legacy_aliases' => 'Enable compatible legacy autotag aliases?',
    'debug_mode' => 'Administrator placement debug mode?'
);

$LANG_configsubgroups['adsense'] = array(
    'sg_main' => 'Main Settings'
);

$LANG_tab['adsense'] = array(
    'tab_main' => 'Main'
);

$LANG_fs['adsense'] = array(
    'fs_main' => 'Ad serving'
);

$LANG_configselects['adsense'] = array(
    0 => array(
        'Enabled' => 1,
        'Disabled' => 0
    ),
    1 => array(
        'Disabled' => 'disabled',
        'Auto Ads' => 'auto',
        'Manual placements' => 'manual',
        'Hybrid (Auto Ads + manual placements)' => 'hybrid'
    )
);

$PLG_adsense_MESSAGE3001 = 'Plugin upgrade not supported.';
$PLG_adsense_MESSAGE3002 = 'This Geeklog or PHP version is not supported.';
