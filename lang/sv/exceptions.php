<?php

return [
    'daemon_connection_failed' => 'Ett undantag uppstod när systemet försökte kommunicera med daemonen vilket resulterade i en HTTP/:code svarskod. Detta undantag har loggats.',
    'node' => [
        'servers_attached' => 'En nod får inte ha några servrar kopplade till sig för att kunna tas bort.',
        'daemon_off_config_updated' => 'Daemonkonfigurationen har uppdaterats, men det uppstod ett fel vid försök att automatiskt uppdatera konfigurationsfilen på Daemon. Du måste uppdatera konfigurationsfilen (config.yml) manuellt för att demonen ska tillämpa dessa ändringar.',
    ],
    'allocations' => [
        'server_using' => 'En server är för närvarande tilldelad denna allokering. En allokering kan endast tas bort om ingen server för närvarande är tilldelad.',
        'too_many_ports' => 'Att lägga till mer än 1000 portar i ett enda intervall på en gång stöds inte.',
        'invalid_mapping' => 'Mappningen som tillhandahölls för :port var ogiltig och kunde inte bearbetas.',
        'cidr_out_of_range' => 'CIDR-notation tillåter endast masker mellan /25 och /32.',
        'port_out_of_range' => 'Portar i en allokering måste vara större än 1024 och mindre än eller lika med 65535.',
    ],
    'nest' => [
        'delete_has_servers' => 'Ett Näste med aktiva servrar kopplade kan inte tas bort från Panelen.',
        'egg' => [
            'delete_has_servers' => 'Ett Egg med aktiva servrar kopplade kan inte tas bort från Panelen.',
            'invalid_copy_id' => 'Ägget som valts för att kopiera ett skript från existerar antingen inte, eller kopierar ett skript självt.',
            'must_be_child' => 'Direktivet "Kopiera Inställningar Från" för detta Egg måste vara ett barnoption för det valda Nästet.',
            'has_children' => 'Detta Egg är förälder till ett eller flera andra Eggs. Vänligen ta bort dessa Eggs innan du tar bort detta Egg.',
        ],
        'variables' => [
            'env_not_unique' => 'Miljövariabeln :name måste vara unik för detta Egg.',
            'reserved_name' => 'Miljövariabeln :name är skyddad och kan inte tilldelas till en variabel.',
            'bad_validation_rule' => 'Valideringsregeln ":rule" är inte en giltig regel för denna applikation.',
        ],
        'importer' => [
            'json_error' => 'Ett fel uppstod vid försök att analysera JSON-filen: :error.',
            'file_error' => 'Den tillhandahållna JSON-filen var inte giltig.',
            'invalid_json_provided' => 'Den tillhandahållna JSON-filen är inte i ett format som kan kännas igen.',
        ],
    ],
    'subusers' => [
        'editing_self' => 'Att redigera ditt eget underanvändarkonto är inte tillåtet.',
        'user_is_owner' => 'Du kan inte lägga till serverägaren som underanvändare för denna server.',
        'subuser_exists' => 'En användare med den e-postadressen är redan tilldelad som underanvändare för denna server.',
    ],
    'subuser_preview' => [
        'start_blocked' => 'Du kan inte starta en ny förhandsvisning medan förhandsvisningsläget är aktiverat.',
        'owner_only' => 'Endast serverägaren kan förhandsgranska en underanvändare.',
        'session_unavailable' => 'Denna förhandsvisning är inte längre tillgänglig.',
        'session_expired' => 'Denna förhandsvisning har löpt ut.',
        'concurrent_start' => 'En förhandsvisning har redan påbörjats.',
        'account_unavailable' => 'Kontoinformationen är inte tillgänglig under förhandsgranskningen av underanvändare.',
        'categories_unavailable' => 'Kategorierna för personliga servrar är inte tillgängliga under förhandsgranskningen av underanvändare.',
        'permission_denied' => 'Du har inte behörighet att utföra den här åtgärden i förhandsgranskningen.',
        'resource_unavailable' => 'Denna resurs är inte tillgänglig under förhandsgranskningen av underanvändare.',
        'live_connection_unavailable' => 'Denna liveanslutning är inte tillgänglig under förhandsgranskning av underanvändare.',
        'file_not_found' => 'Den begärda filen finns inte i den här förhandsvisningen.',
        'file_too_large' => 'Den här filen är för stor för att kunna visas i förhandsgranskningen.',
        'state_too_large' => 'Denna förhandsvisning har nått sin lagringsgräns.',
        'unsafe_pull_url' => 'Endast säkra HTTPS-URL:er kan hämtas till förhandsvisningen.',
        'action_unavailable' => 'Den här åtgärden är inte tillgänglig under förhandsgranskning av underanvändare.',
        'database_limit' => 'Den här servern har nått sin databasgräns.',
        'database_host_unavailable' => 'Det finns ingen databasvärd tillgänglig för den här servern.',
        'task_limit' => 'Det här schemat har nått sin uppgiftsgräns.',
        'allocation_limit' => 'Denna server har nått sin tilldelningsgräns.',
        'allocation_unavailable' => 'Det finns inga ytterligare tilldelningar tillgängliga för denna server.',
        'primary_allocation' => 'Den primära tilldelningen kan inte tas bort.',
        'backup_limit' => 'Denna server har nått sin gräns för säkerhetskopiering.',
        'locked_backup' => 'En låst säkerhetskopia kan inte raderas.',
        'variable_unavailable' => 'Miljövariabeln är inte tillgänglig eller är skrivskyddad.',
        'docker_image_unavailable' => 'Den valda Docker-bilden är inte tillgänglig för den här servern.',
    ],
    'databases' => [
        'delete_has_databases' => 'Kan inte ta bort en databasvärdserver som har aktiva databaser kopplade till sig.',
    ],
    'tasks' => [
        'chain_interval_too_long' => 'Den maximala intervalltiden för en kedjeuppgift är 15 minuter.',
    ],
    'locations' => [
        'has_nodes' => 'Kan inte ta bort en plats som har aktiva noder kopplade till sig.',
    ],
    'users' => [
        'node_revocation_failed' => 'Misslyckades med att återkalla nycklar på <a href=":link">Nod #:node</a>. :error',
    ],
    'deployment' => [
        'no_viable_nodes' => 'Inga noder som uppfyller de specificerade kraven för automatisk distribution kunde hittas.',
        'no_viable_allocations' => 'Inga allokeringar som uppfyller kraven för automatisk distribution hittades.',
    ],
    'api' => [
        'resource_not_found' => 'Den begärda resursen existerar inte på denna server.',
    ],
    'social' => [
        'unlink_only_login' => 'Du kan inte koppla bort din enda inloggningsmetod utan att först ange ett lösenord.',
    ],
];
