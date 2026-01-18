<?php

return [
    'exceptions' => [
        'no_new_default_allocation' => 'Sie versuchen, die Standardzuweisung für diesen Server zu löschen, aber es gibt keine Fallback-Zuweisung.',
        'marked_as_failed' => 'Dieser Server wurde als fehlgeschlagen bei einer vorherigen Installation markiert. Der aktuelle Status kann in diesem Zustand nicht umgeschaltet werden.',
        'bad_variable' => 'Es gab einen Validierungsfehler mit der Variable :name.',
        'daemon_exception' => 'Es trat ein Fehler bei der Kommunikation mit dem Daemon auf, der zu einem HTTP/:code Antwortcode führte. Dieser Fehler wurde protokolliert. (Anfrage-ID: :request_id)',
        'default_allocation_not_found' => 'Die angeforderte Standardzuweisung wurde in den Zuweisungen dieses Servers nicht gefunden.',
    ],
    'alerts' => [
        'startup_changed' => 'Die Startkonfiguration für diesen Server wurde aktualisiert. Wenn das Nest oder Ei dieses Servers geändert wurde, findet jetzt eine Neuinstallation statt.',
        'server_deleted' => 'Der Server wurde erfolgreich aus dem System gelöscht.',
        'server_created' => 'Der Server wurde erfolgreich auf dem Panel erstellt. Bitte geben Sie dem Daemon einige Minuten Zeit, um diesen Server vollständig zu installieren.',
        'build_updated' => 'Die Build-Details für diesen Server wurden aktualisiert. Einige Änderungen erfordern möglicherweise einen Neustart, um wirksam zu werden.',
        'suspension_toggled' => 'Der Suspendierungsstatus des Servers wurde auf :status geändert.',
        'rebuild_on_boot' => 'Dieser Server wurde so markiert, dass er einen Docker-Container-Rebuild erfordert. Dies geschieht beim nächsten Start des Servers.',
        'install_toggled' => 'Der Installationsstatus für diesen Server wurde umgeschaltet.',
        'server_reinstalled' => 'Dieser Server wurde für eine Neuinstallation in die Warteschlange gestellt, die jetzt beginnt.',
        'details_updated' => 'Serverdetails wurden erfolgreich aktualisiert.',
        'docker_image_updated' => 'Das Standard-Docker-Image für diesen Server wurde erfolgreich geändert. Ein Neustart ist erforderlich, um diese Änderung zu übernehmen.',
        'node_required' => 'Sie müssen mindestens einen Node konfiguriert haben, bevor Sie diesem Panel einen Server hinzufügen können.',
        'transfer_nodes_required' => 'Sie müssen mindestens zwei Nodes konfiguriert haben, bevor Sie Server übertragen können.',
        'transfer_started' => 'Serverübertragung wurde gestartet.',
        'transfer_not_viable' => 'Der ausgewählte Node verfügt nicht über den erforderlichen Speicherplatz oder Arbeitsspeicher, um diesen Server aufzunehmen.',
    ],
];