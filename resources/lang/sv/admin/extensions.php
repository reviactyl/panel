<?php

return [

    'label' => 'Förlängning',
    'plural-label' => 'Tillägg',

    'columns' => [
        'id' => 'ID',
        'name' => 'Namn',
        'version' => 'Version',
        'author' => 'Författare',
        'enabled' => 'Aktiverad',
        'updated' => 'Uppdaterad',
        'manifest_json' => 'Manifest JSON',
    ],

    'modals' => [
        'manifest' => 'Förlängningsmanifest',
    ],

    'actions' => [
        'edit' => 'Redigera',
        'upload' => 'Ladda upp',
        'manifest' => 'Visa manifest',
        'disable' => 'Inaktivera',
        'enable' => 'Aktivera',
        'delete' => 'Radera',
        'close' => 'Nära',
    ],

    'alerts' => [
        'enabled' => 'Tillägget är aktiverat.',
        'enable_failed' => 'Det gick inte att aktivera tillägget.',
        'disabled' => 'Tillägget inaktiverat.',
        'disable_failed' => 'Det gick inte att inaktivera tillägget.',
        'uninstalled' => 'Tillägget avinstallerat.',
        'uninstall_failed' => 'Det gick inte att avinstallera tillägget.',
        'could_not_locate_file' => 'Kunde inte hitta uppladdad paketfil.',
        'invalid_file_type' => 'Endast .rext-filer är tillåtna.',
        'upload_hint' => 'Endast .rext-tilläggspaket är tillåtna.',
        'install_failed' => 'Installation av tillägg misslyckades.',
        'install_success' => ':name (:version) har installerats.',
    ],

];
