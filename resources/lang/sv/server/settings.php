<?php

return [
    'title' => 'Inställningar',
    'sftp' => [
        'title' => 'SFTP-detaljer',
        'address' => 'Serveradress',
        'username' => 'Användarnamn',
        'password' => 'Ditt SFTP-lösenord är detsamma som lösenordet du använder för att komma åt denna panel.',
        'button' => 'Starta SFTP',
    ],
    'info' => [
        'title' => 'Felsökningsinformation',
        'node' => 'Nod',
        'server' => 'Server-ID',
        'public-status-page' => 'Offentlig statussida',
    ],
    'rename' => [
        'title' => 'Ändra serverdetaljer',
        'name' => 'Servernamn',
        'description' => 'Serverbeskrivning',
        'button' => 'Spara',
    ],
    'reinstall' => [
        'title' => 'Ominstallera server',
        'confirm-title' => 'Bekräfta ominstallation av server',
        'confirm' => 'Ja, ominstallera server',
        'info' => 'Din server kommer att stoppas och vissa filer kan raderas eller ändras under denna process, är du säker på att du vill fortsätta?',
        'info-1' => 'Att ominstallera din server kommer att stoppa den och sedan köra installationsskriptet som ursprungligen konfigurerade den igen.',
        'info-2' => 'Vissa filer kan raderas eller ändras under denna process, vänligen säkerhetskopiera dina data innan du fortsätter.',
        'button' => 'Ominstallera server',
    ],
];
