<?php

/* French language file for AdSense 1.0.0 */

$LANG_ADSENSE = array(
    'plugin_name' => 'AdSense',
    'autotag_description' => '[ad:EMPLACEMENT] affiche un emplacement AdSense nommé.'
);

$LANG_configsections['adsense'] = array(
    'label' => 'AdSense',
    'title' => 'Configuration AdSense'
);

$LANG_confignames['adsense'] = array(
    'enabled' => 'Activer la diffusion AdSense ?',
    'serving_mode' => 'Mode de diffusion',
    'publisher_id' => 'ID éditeur',
    'legacy_aliases' => 'Activer les alias compatibles des anciens autotags ?',
    'debug_mode' => 'Mode de prévisualisation des emplacements pour les administrateurs ?'
);

$LANG_configsubgroups['adsense'] = array(
    'sg_main' => 'Paramètres principaux'
);

$LANG_tab['adsense'] = array(
    'tab_main' => 'Principal'
);

$LANG_fs['adsense'] = array(
    'fs_main' => 'Diffusion des annonces'
);

$LANG_configselects['adsense'] = array(
    0 => array(
        'Activé' => 1,
        'Désactivé' => 0
    ),
    1 => array(
        'Désactivé' => 'disabled',
        'Annonces automatiques' => 'auto',
        'Emplacements manuels' => 'manual',
        'Hybride (annonces automatiques + emplacements manuels)' => 'hybrid'
    )
);

$PLG_adsense_MESSAGE3001 = 'Mise à niveau du plugin non prise en charge.';
$PLG_adsense_MESSAGE3002 = 'Cette version de Geeklog ou de PHP n’est pas prise en charge.';
