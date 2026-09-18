<?php

/* French language file for AdSense 1.0.0 */

$LANG_ADSENSE = array(
    'plugin_name' => 'AdSense',
    'admin_title' => 'AdSense',
    'admin_intro' => 'Gérez ici les emplacements AdSense nommés. Les réglages globaux de diffusion restent dans la configuration Geeklog.',
    'open_configuration' => 'Ouvrir la configuration AdSense',
    'autotag_description' => '[ad:EMPLACEMENT] affiche un emplacement AdSense nommé.',
    'placements_title' => 'Emplacements nommés',
    'placements_help' => 'Créez des noms d’emplacement stables côté Geeklog et associez-les aux identifiants de blocs AdSense. Le contenu doit référencer le nom de l’emplacement, jamais le slot ID.',
    'placement_name' => 'Nom de l’emplacement',
    'slot_id' => 'Slot ID AdSense',
    'format' => 'Format',
    'responsive' => 'Responsive',
    'enabled' => 'Activé',
    'actions' => 'Actions',
    'save' => 'Enregistrer',
    'add' => 'Ajouter un emplacement',
    'delete' => 'Supprimer',
    'confirm_delete' => 'Supprimer cet emplacement AdSense ?',
    'placement_saved' => 'L’emplacement AdSense a été enregistré.',
    'placement_save_failed' => 'Impossible d’enregistrer l’emplacement. Vérifiez son nom, le slot ID et les doublons.',
    'placement_deleted' => 'L’emplacement AdSense a été supprimé.',
    'placement_delete_failed' => 'Impossible de supprimer l’emplacement AdSense.',
    'invalid_token' => 'La requête n’a pas pu être validée. Rechargez la page et réessayez.',
    'format_auto' => 'Automatique',
    'format_rectangle' => 'Rectangle',
    'format_horizontal' => 'Horizontal',
    'format_vertical' => 'Vertical',
    'legacy_title' => 'Compatibilité avec les autotags historiques',
    'legacy_help' => 'AdSense n’expose un alias historique compatible que si les alias sont activés et qu’aucun autre plugin actif ne possède déjà ce nom d’autotag.',
    'autotag' => 'Autotag',
    'mapped_placement' => 'Emplacement associé',
    'status' => 'État',
    'owned_by_plugin' => 'Déjà fourni par le plugin actif : %s',
    'aliases_disabled' => 'Alias historiques désactivés',
    'alias_available' => 'Disponible pour AdSense',
    'alias_unavailable' => 'Indisponible',
    'external_unchanged' => 'Externe — jamais repris par AdSense',
    'usage_title' => 'Utilisation',
    'tooltip_enabled' => 'Interrupteur général de toute diffusion AdSense générée par ce plugin.',
    'tooltip_serving_mode' => 'Désactivé coupe toute diffusion. Annonces automatiques charge uniquement le script Google. Manuel affiche les emplacements nommés. Hybride combine les deux.',
    'tooltip_publisher_id' => 'Identifiant éditeur Google AdSense au format ca-pub-1234567890123456.',
    'tooltip_debug_mode' => 'Pour les administrateurs AdSense, remplace les annonces manuelles par des repères afin de contrôler les emplacements sans afficher de publicité réelle.',
    'tooltip_legacy_aliases' => 'Expose les noms d’autotags historiques compatibles uniquement si aucun autre plugin actif ne les possède déjà.',
    'tooltip_legacy_adsense_placement' => 'Emplacement nommé utilisé pour l’ancien alias adsense lorsqu’il est disponible.',
    'tooltip_legacy_inarticle_placement' => 'Emplacement nommé utilisé pour l’ancien alias inarticle lorsqu’il est disponible.',
    'tooltip_legacy_infeed_placement' => 'Emplacement nommé utilisé pour l’ancien alias infeed lorsqu’il est disponible.',
    'tooltip_legacy_leaderboard_placement' => 'Emplacement nommé utilisé pour l’ancien alias leaderboard lorsqu’il est disponible.',
    'usage_help' => 'Exemple : créez un emplacement article-middle, puis utilisez [ad:article-middle] dans un contenu Geeklog compatible.'
);

$LANG_configsections['adsense'] = array(
    'label' => 'AdSense',
    'title' => 'Configuration AdSense'
);

$LANG_confignames['adsense'] = array(
    'enabled' => 'Activer la diffusion AdSense ?',
    'serving_mode' => 'Mode de diffusion',
    'publisher_id' => 'ID éditeur',
    'debug_mode' => 'Prévisualisation des emplacements pour les administrateurs ?',
    'legacy_aliases' => 'Activer les alias compatibles des anciens autotags ?',
    'legacy_adsense_placement' => 'Emplacement associé à [adsense]',
    'legacy_inarticle_placement' => 'Emplacement associé à [inarticle]',
    'legacy_infeed_placement' => 'Emplacement associé à [infeed]',
    'legacy_leaderboard_placement' => 'Emplacement associé à [leaderboard]'
);

$LANG_configsubgroups['adsense'] = array(
    'sg_main' => 'Paramètres principaux'
);

$LANG_tab['adsense'] = array(
    'tab_main' => 'Principal'
);

$LANG_fs['adsense'] = array(
    'fs_main' => 'Diffusion des annonces',
    'fs_legacy' => 'Autotags historiques'
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
