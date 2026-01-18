<?php

return [
    'validation' => [
        'fqdn_not_resolvable' => 'Der angegebene FQDN oder die IP-Adresse kann nicht in eine gültige IP-Adresse aufgelöst werden.',
        'fqdn_required_for_ssl' => 'Ein vollqualifizierter Domainname, der in eine öffentliche IP-Adresse aufgelöst wird, ist erforderlich, um SSL für diesen Node zu verwenden.',
    ],
    'notices' => [
        'allocations_added' => 'Zuweisungen wurden diesem Node erfolgreich hinzugefügt.',
        'node_deleted' => 'Node wurde erfolgreich aus dem Panel entfernt.',
        'location_required' => 'Sie müssen mindestens einen Standort konfiguriert haben, bevor Sie einen Node zu diesem Panel hinzufügen können.',
        'node_created' => 'Neuer Node wurde erfolgreich erstellt. Sie können den Daemon auf dieser Maschine automatisch konfigurieren, indem Sie die Registerkarte "Konfiguration" besuchen. <strong>Bevor Sie Server hinzufügen können, müssen Sie zuerst mindestens eine IP-Adresse und einen Port zuweisen.</strong>',
        'node_updated' => 'Die Node-Informationen wurden aktualisiert. Wenn Daemon-Einstellungen geändert wurden, müssen Sie ihn neu starten, damit diese Änderungen wirksam werden.',
        'unallocated_deleted' => 'Alle nicht zugewiesenen Ports für <code>:ip</code> wurden gelöscht.',
    ],
];