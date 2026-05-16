<?php

/**
 * Contains all of the translation strings for different activity log
 * events. These should be keyed by the value in front of the colon (:)
 * in the event name. If there is no colon present, they should live at
 * the top level.
 */
return [
    'entries' => [
        'system-user' => 'ಸಿಸ್ಟಮ್ ಬಳಕೆದಾರ',
        'system' => 'ವ್ಯವಸ್ಥೆ',
        'using-api-key' => 'API ಕೀಯನ್ನು ಬಳಸುವುದು',
        'using-sftp' => 'SFTP ಬಳಸುವುದು',
    ],
    'auth' => [
        'fail' => 'ಲಾಗಿನ್ ವಿಫಲವಾಗಿದೆ',
        'success' => 'ಲಾಗಿನ್ ಮಾಡಲಾಗಿದೆ',
        'password-reset' => 'ಪಾಸ್ವರ್ಡ್ ಮರುಹೊಂದಿಸಿ',
        'reset-password' => 'ಪಾಸ್‌ವರ್ಡ್ ಮರುಹೊಂದಿಸಲು ವಿನಂತಿಸಲಾಗಿದೆ',
        'checkpoint' => 'ಎರಡು ಅಂಶದ ದೃಢೀಕರಣವನ್ನು ವಿನಂತಿಸಲಾಗಿದೆ',
        'recovery-token' => 'ಎರಡು ಅಂಶಗಳ ಚೇತರಿಕೆಯ ಟೋಕನ್ ಅನ್ನು ಬಳಸಲಾಗಿದೆ',
        'token' => 'ಎರಡು ಅಂಶಗಳ ಸವಾಲನ್ನು ಪರಿಹರಿಸಲಾಗಿದೆ',
        'ip-blocked' => ':identifier ಗಾಗಿ ಪಟ್ಟಿ ಮಾಡದ IP ವಿಳಾಸದಿಂದ ವಿನಂತಿಯನ್ನು ನಿರ್ಬಂಧಿಸಲಾಗಿದೆ',
        'sftp' => [
            'fail' => 'SFTP ಲಾಗಿನ್ ವಿಫಲವಾಗಿದೆ',
        ],
    ],
    'user' => [
        'account' => [
            'email-changed' => ':old ನಿಂದ :new ಗೆ ಇಮೇಲ್ ಅನ್ನು ಬದಲಾಯಿಸಲಾಗಿದೆ',
            'password-changed' => 'ಪಾಸ್ವರ್ಡ್ ಬದಲಾಯಿಸಲಾಗಿದೆ',
            'language-changed' => ':old ನಿಂದ :new ಗೆ ಭಾಷೆಯನ್ನು ಬದಲಾಯಿಸಲಾಗಿದೆ',
        ],
        'api-key' => [
            'create' => 'ಹೊಸ API ಕೀ :identifier ಅನ್ನು ರಚಿಸಲಾಗಿದೆ',
            'delete' => 'ಅಳಿಸಲಾದ API ಕೀ :identifier',
        ],
        'ssh-key' => [
            'create' => 'ಖಾತೆಗೆ SSH ಕೀ :fingerprint ಸೇರಿಸಲಾಗಿದೆ',
            'delete' => 'ಖಾತೆಯಿಂದ SSH ಕೀ :fingerprint ತೆಗೆದುಹಾಕಲಾಗಿದೆ',
        ],
        'two-factor' => [
            'create' => 'ಎರಡು ಅಂಶದ ದೃಢೀಕರಣವನ್ನು ಸಕ್ರಿಯಗೊಳಿಸಲಾಗಿದೆ',
            'delete' => 'ಎರಡು ಅಂಶದ ದೃಢೀಕರಣವನ್ನು ನಿಷ್ಕ್ರಿಯಗೊಳಿಸಲಾಗಿದೆ',
        ],
    ],
    'server' => [
        'reinstall' => 'ಸರ್ವರ್ ಅನ್ನು ಮರುಸ್ಥಾಪಿಸಲಾಗಿದೆ',
        'console' => [
            'command' => 'ಸರ್ವರ್‌ನಲ್ಲಿ ":command" ಅನ್ನು ಕಾರ್ಯಗತಗೊಳಿಸಲಾಗಿದೆ',
        ],
        'power' => [
            'start' => 'ಸರ್ವರ್ ಅನ್ನು ಪ್ರಾರಂಭಿಸಿದೆ',
            'stop' => 'ಸರ್ವರ್ ಅನ್ನು ನಿಲ್ಲಿಸಿದೆ',
            'restart' => 'ಸರ್ವರ್ ಅನ್ನು ಮರುಪ್ರಾರಂಭಿಸಿದೆ',
            'kill' => 'ಸರ್ವರ್ ಪ್ರಕ್ರಿಯೆಯನ್ನು ಕೊಂದರು',
        ],
        'backup' => [
            'download' => ':name ಬ್ಯಾಕಪ್ ಅನ್ನು ಡೌನ್‌ಲೋಡ್ ಮಾಡಲಾಗಿದೆ',
            'delete' => ':name ಬ್ಯಾಕಪ್ ಅಳಿಸಲಾಗಿದೆ',
            'restore' => ':name ಬ್ಯಾಕಪ್ ಅನ್ನು ಮರುಸ್ಥಾಪಿಸಲಾಗಿದೆ (ಅಳಿಸಿದ ಫೈಲ್‌ಗಳು: :truncate)',
            'restore-complete' => ':name ಬ್ಯಾಕಪ್‌ನ ಮರುಸ್ಥಾಪನೆ ಪೂರ್ಣಗೊಂಡಿದೆ',
            'restore-failed' => ':name ಬ್ಯಾಕಪ್ ಮರುಸ್ಥಾಪನೆಯನ್ನು ಪೂರ್ಣಗೊಳಿಸಲು ವಿಫಲವಾಗಿದೆ',
            'start' => 'ಹೊಸ ಬ್ಯಾಕಪ್ :name ಅನ್ನು ಪ್ರಾರಂಭಿಸಲಾಗಿದೆ',
            'complete' => ':name ಬ್ಯಾಕಪ್ ಪೂರ್ಣಗೊಂಡಿದೆ ಎಂದು ಗುರುತಿಸಲಾಗಿದೆ',
            'fail' => ':name ಬ್ಯಾಕಪ್ ವಿಫಲವಾಗಿದೆ ಎಂದು ಗುರುತಿಸಲಾಗಿದೆ',
            'lock' => ':name ಬ್ಯಾಕಪ್ ಅನ್ನು ಲಾಕ್ ಮಾಡಲಾಗಿದೆ',
            'unlock' => ':name ಬ್ಯಾಕಪ್ ಅನ್ನು ಅನ್‌ಲಾಕ್ ಮಾಡಲಾಗಿದೆ',
        ],
        'database' => [
            'create' => 'ಹೊಸ ಡೇಟಾಬೇಸ್ :name ಅನ್ನು ರಚಿಸಲಾಗಿದೆ',
            'rotate-password' => 'ಡೇಟಾಬೇಸ್ :name ಗಾಗಿ ಪಾಸ್‌ವರ್ಡ್ ಅನ್ನು ತಿರುಗಿಸಲಾಗಿದೆ',
            'delete' => 'ಅಳಿಸಲಾದ ಡೇಟಾಬೇಸ್ :name',
        ],
        'file' => [
            'compress_one' => 'ಸಂಕುಚಿತ :directory:file',
            'compress_other' => ':directory ನಲ್ಲಿ :count ಫೈಲ್‌ಗಳನ್ನು ಸಂಕುಚಿತಗೊಳಿಸಲಾಗಿದೆ',
            'read' => ':file ನ ವಿಷಯಗಳನ್ನು ವೀಕ್ಷಿಸಲಾಗಿದೆ',
            'copy' => ':file ನ ನಕಲನ್ನು ರಚಿಸಲಾಗಿದೆ',
            'create-directory' => ':directory:name ಡೈರೆಕ್ಟರಿಯನ್ನು ರಚಿಸಲಾಗಿದೆ',
            'decompress' => ':directory ನಲ್ಲಿ :files ಅನ್ನು ಡಿಕಂಪ್ರೆಸ್ ಮಾಡಲಾಗಿದೆ',
            'delete_one' => ':directory:files.0 ಅಳಿಸಲಾಗಿದೆ',
            'delete_other' => ':directory ನಲ್ಲಿ :count ಫೈಲ್‌ಗಳನ್ನು ಅಳಿಸಲಾಗಿದೆ',
            'download' => ':file ಡೌನ್‌ಲೋಡ್ ಮಾಡಲಾಗಿದೆ',
            'pull' => ':url ನಿಂದ :directory ಗೆ ರಿಮೋಟ್ ಫೈಲ್ ಅನ್ನು ಡೌನ್‌ಲೋಡ್ ಮಾಡಲಾಗಿದೆ',
            'rename_one' => ':directory:files.0.from ಎಂದು :directory:files.0.to ಎಂದು ಮರುಹೆಸರಿಸಲಾಗಿದೆ',
            'rename_other' => ':directory ನಲ್ಲಿ :count ಫೈಲ್‌ಗಳನ್ನು ಮರುಹೆಸರಿಸಲಾಗಿದೆ',
            'write' => ':file ಗೆ ಹೊಸ ವಿಷಯವನ್ನು ಬರೆದಿದ್ದಾರೆ',
            'upload' => 'ಫೈಲ್ ಅಪ್‌ಲೋಡ್ ಆರಂಭಿಸಿದೆ',
            'uploaded' => ':directory:file ಅಪ್‌ಲೋಡ್ ಮಾಡಲಾಗಿದೆ',
        ],
        'sftp' => [
            'denied' => 'ಅನುಮತಿಗಳ ಕಾರಣದಿಂದಾಗಿ SFTP ಪ್ರವೇಶವನ್ನು ನಿರ್ಬಂಧಿಸಲಾಗಿದೆ',
            'create_one' => ':files.0 ರಚಿಸಲಾಗಿದೆ',
            'create_other' => ':count ಹೊಸ ಫೈಲ್‌ಗಳನ್ನು ರಚಿಸಲಾಗಿದೆ',
            'write_one' => ':files.0 ನ ವಿಷಯಗಳನ್ನು ಮಾರ್ಪಡಿಸಲಾಗಿದೆ',
            'write_other' => ':count ಫೈಲ್‌ಗಳ ವಿಷಯಗಳನ್ನು ಮಾರ್ಪಡಿಸಲಾಗಿದೆ',
            'delete_one' => ':files.0 ಅಳಿಸಲಾಗಿದೆ',
            'delete_other' => ':count ಫೈಲ್‌ಗಳನ್ನು ಅಳಿಸಲಾಗಿದೆ',
            'create-directory_one' => ':files.0 ಡೈರೆಕ್ಟರಿಯನ್ನು ರಚಿಸಲಾಗಿದೆ',
            'create-directory_other' => ':count ಡೈರೆಕ್ಟರಿಗಳನ್ನು ರಚಿಸಲಾಗಿದೆ',
            'rename_one' => ':files.0.from ನಿಂದ :files.0.to ಎಂದು ಮರುನಾಮಕರಣ ಮಾಡಲಾಗಿದೆ',
            'rename_other' => ':count ಫೈಲ್‌ಗಳನ್ನು ಮರುಹೆಸರಿಸಲಾಗಿದೆ ಅಥವಾ ಸರಿಸಲಾಗಿದೆ',
        ],
        'allocation' => [
            'create' => ':allocation ಅನ್ನು ಸರ್ವರ್‌ಗೆ ಸೇರಿಸಲಾಗಿದೆ',
            'notes' => ':allocation ಗಾಗಿ ಟಿಪ್ಪಣಿಗಳನ್ನು ":old" ನಿಂದ ":new" ಗೆ ನವೀಕರಿಸಲಾಗಿದೆ',
            'primary' => ':allocation ಅನ್ನು ಪ್ರಾಥಮಿಕ ಸರ್ವರ್ ಹಂಚಿಕೆಯಾಗಿ ಹೊಂದಿಸಿ',
            'delete' => ':allocation ಹಂಚಿಕೆಯನ್ನು ಅಳಿಸಲಾಗಿದೆ',
        ],
        'schedule' => [
            'create' => ':name ವೇಳಾಪಟ್ಟಿಯನ್ನು ರಚಿಸಲಾಗಿದೆ',
            'update' => ':name ವೇಳಾಪಟ್ಟಿಯನ್ನು ನವೀಕರಿಸಲಾಗಿದೆ',
            'execute' => ':name ವೇಳಾಪಟ್ಟಿಯನ್ನು ಹಸ್ತಚಾಲಿತವಾಗಿ ಕಾರ್ಯಗತಗೊಳಿಸಲಾಗಿದೆ',
            'delete' => ':name ವೇಳಾಪಟ್ಟಿಯನ್ನು ಅಳಿಸಲಾಗಿದೆ',
        ],
        'task' => [
            'create' => ':name ವೇಳಾಪಟ್ಟಿಗಾಗಿ ಹೊಸ ":action" ಕಾರ್ಯವನ್ನು ರಚಿಸಲಾಗಿದೆ',
            'update' => ':name ವೇಳಾಪಟ್ಟಿಗಾಗಿ ":action" ಕಾರ್ಯವನ್ನು ನವೀಕರಿಸಲಾಗಿದೆ',
            'delete' => ':name ವೇಳಾಪಟ್ಟಿಗಾಗಿ ಕಾರ್ಯವನ್ನು ಅಳಿಸಲಾಗಿದೆ',
        ],
        'settings' => [
            'rename' => 'ಸರ್ವರ್ ಅನ್ನು :old ನಿಂದ :new ಗೆ ಮರುಹೆಸರಿಸಲಾಗಿದೆ',
            'description' => 'ಸರ್ವರ್ ವಿವರಣೆಯನ್ನು :old ನಿಂದ :new ಗೆ ಬದಲಾಯಿಸಲಾಗಿದೆ',
        ],
        'startup' => [
            'edit' => ':variable ವೇರಿಯೇಬಲ್ ಅನ್ನು ":old" ನಿಂದ ":new" ಗೆ ಬದಲಾಯಿಸಲಾಗಿದೆ',
            'image' => ':old ನಿಂದ :new ಗೆ ಸರ್ವರ್‌ಗಾಗಿ ಡಾಕರ್ ಚಿತ್ರವನ್ನು ನವೀಕರಿಸಲಾಗಿದೆ',
        ],
        'subuser' => [
            'create' => 'ಉಪಬಳಕೆದಾರರಾಗಿ :email ಅನ್ನು ಸೇರಿಸಲಾಗಿದೆ',
            'update' => ':email ಗಾಗಿ ಉಪಬಳಕೆದಾರರ ಅನುಮತಿಗಳನ್ನು ನವೀಕರಿಸಲಾಗಿದೆ',
            'delete' => ':email ಅನ್ನು ಉಪಬಳಕೆದಾರರಾಗಿ ತೆಗೆದುಹಾಕಲಾಗಿದೆ',
        ],
    ],
];
