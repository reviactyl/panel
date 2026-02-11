<?php

return [
    'title' => 'Användare',
    'exceptions' => [
        'delete_self' => 'Du kan inte ta bort ditt eget konto.',
        'user_has_servers' => 'Kan inte ta bort en användare med aktiva servrar kopplade till deras konto. Vänligen ta bort deras servrar innan du fortsätter.',
    ],
    'notices' => [
        'account_created' => 'Kontot har skapats framgångsrikt.',
        'account_updated' => 'Kontot har uppdaterats framgångsrikt.',
    ],
    'details' => [
        'account_details' => 'Kontouppgifter',
        'external_id' => 'Externt ID',
        'username' => 'Användarnamn',
        'email' => 'E-postadress',
        'first_name' => 'Förnamn',
        'last_name' => 'Efternamn',
        'language' => 'Språk',
        'password' => 'Lösenord',
        'password_confirmation' => 'Bekräfta lösenord',
        'root_admin' => 'Root-administratör',
        'root_admin_desc' => 'Denna användare kommer att ha full åtkomst till alla servrar och inställningar i systemet.',
        'privileges' => 'Behörigheter',
        'admin_status' => 'Administratörsstatus',
    ],
];
