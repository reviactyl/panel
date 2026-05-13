<?php

/**
 * Contains all of the translation strings for different activity log
 * events. These should be keyed by the value in front of the colon (:)
 * in the event name. If there is no colon present, they should live at
 * the top level.
 */
return [
    'entries' => [
        'system-user' => 'Systemanvändare',
        'system' => 'System',
        'using-api-key' => 'Använder API-nyckel',
        'using-sftp' => 'Använder SFTP',
    ],
    'auth' => [
        'fail' => 'Misslyckad inloggning',
        'success' => 'Inloggad',
        'password-reset' => 'Lösenordsåterställning',
        'reset-password' => 'Begärd lösenordsåterställning',
        'checkpoint' => 'Tvåfaktorsautentisering begärd',
        'recovery-token' => 'Använde tvåfaktorsåterställningstoken',
        'token' => 'Löste tvåfaktorsutmaning',
        'ip-blocked' => 'Blockerad förfrågan från olistad IP-adress för :identifier',
        'sftp' => [
            'fail' => 'Misslyckad SFTP-inloggning',
        ],
    ],
    'user' => [
        'account' => [
            'email-changed' => 'Ändrade e-post från :old till :new',
            'password-changed' => 'Ändrade lösenord',
            'language-changed' => 'Ändrade språk från :old till :new',
        ],
        'api-key' => [
            'create' => 'Skapade ny API-nyckel :identifier',
            'delete' => 'Raderad API-nyckel :identifier',
        ],
        'ssh-key' => [
            'create' => 'Lade till SSH-nyckel :fingerprint till kontot',
            'delete' => 'Tog bort SSH-nyckeln :fingerprint från kontot',
        ],
        'two-factor' => [
            'create' => 'Aktiverade tvåfaktorsautentisering',
            'delete' => 'Inaktiverade tvåfaktorsautentisering',
        ],
    ],
    'server' => [
        'reinstall' => 'Ominstallerade server]',
        'console' => [
            'command' => 'Körde ":command" på servern',
        ],
        'power' => [
            'start' => 'Startade servern',
            'stop' => 'Stoppade servern',
            'restart' => 'Startade om servern',
            'kill' => 'Dödade serverprocessen',
        ],
        'backup' => [
            'download' => 'Laddade ner :name backup',
            'delete' => 'Raderade :name backup',
            'restore' => 'Återställde :name backup (raderade filer: :truncate)',
            'restore-complete' => 'Slutförd återställning av :name backup',
            'restore-failed' => 'Det gick inte att slutföra återställningen av :name backup',
            'start' => 'Startade en ny backup :name',
            'complete' => 'Markerade :name-säkerhetskopian som komplett',
            'fail' => 'Markerade :name backup som misslyckad',
            'lock' => 'Låste :name backup',
            'unlock' => 'Lås upp :name backup',
        ],
        'database' => [
            'create' => 'Skapat ny databas :name',
            'rotate-password' => 'Lösenord roterat för databasen :name',
            'delete' => 'Raderad databas :name',
        ],
        'file' => [
            'compress_one' => 'Komprimerad :directory:file',
            'compress_other' => 'Komprimerade :count-filer i :directory',
            'read' => 'Visade innehållet i :file',
            'copy' => 'Skapade en kopia av :file',
            'create-directory' => 'Skapat katalog :directory:name',
            'decompress' => 'Dekomprimerade :files i :directory',
            'delete_one' => 'Raderade :directory:files.0',
            'delete_other' => 'Raderade :count-filer i :directory',
            'download' => 'Laddade ner :file',
            'pull' => 'Laddade ner en fjärrfil från :url till :directory',
            'rename_one' => 'Omdöpt :directory:files.0.from till :directory:files.0.to',
            'rename_other' => 'Omdöpt :count filer i :directory',
            'write' => 'Skrev nytt innehåll till :file',
            'upload' => 'Påbörjade en uppladdning',
            'uploaded' => 'Laddade upp :directory:file',
        ],
        'sftp' => [
            'denied' => 'Blockerade SFTP-åtkomst på grund av behörigheter ',
            'create_one' => 'Skapade :files.0',
            'create_other' => 'Skapat :count nya filer',
            'write_one' => 'Ändrade innehållet i :files.0',
            'write_other' => 'Ändrade innehållet i :count-filer',
            'delete_one' => 'Raderade :files.0',
            'delete_other' => 'Raderade :count-filer',
            'create-directory_one' => 'Skapat katalogen :files.0',
            'create-directory_other' => 'Skapat :count kataloger',
            'rename_one' => 'Omdöpt till :files.0.from till :files.0.to',
            'rename_other' => 'Bytt namn eller flyttade :count-filer',
        ],
        'allocation' => [
            'create' => 'Lade till :allocation till servern',
            'notes' => 'Uppdaterade anteckningarna för :allocation från ":old" till ":new"',
            'primary' => 'Ställ in :allocation som primär servertilldelning',
            'delete' => 'Raderade :allocation-allokeringen',
        ],
        'schedule' => [
            'create' => 'Skapade :name-schemat',
            'update' => 'Uppdaterade :name-schemat',
            'execute' => 'Körde :name-schemat manuellt',
            'delete' => 'Raderade :name-schemat',
        ],
        'task' => [
            'create' => 'Skapat en ny ":action"-uppgift för :name-schemat',
            'update' => 'Uppdaterade ":action"-uppgiften för :name-schemat',
            'delete' => 'Raderade en uppgift för :name-schemat',
        ],
        'settings' => [
            'rename' => 'Döpte om servern från :old till :new',
            'description' => 'Ändrade serverbeskrivningen från :old till :new',
        ],
        'startup' => [
            'edit' => 'Ändrade variabeln :variable från ":old" till ":new"',
            'image' => 'Uppdaterade Docker-bilden för servern från :old till :new',
        ],
        'subuser' => [
            'create' => 'Lade till :email som underanvändare',
            'update' => 'Uppdaterade underanvändarbehörigheterna för :email',
            'delete' => 'Tog bort :email som underanvändare',
        ],
    ],
];
