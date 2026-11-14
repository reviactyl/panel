<?php

return [

    'label' => 'Webhook',
    'plural_label' => 'Webhooks',

    'sections' => [
        'details' => 'Details',
        'details_description' => 'Webhooks tell another service the moment something happens on the panel.',
        'delivery' => 'Delivery',
        'delivery_description' => 'Where events are sent and how the receiving end can trust them.',
        'message' => 'Discord Message',
        'message_description' => 'What gets posted to the channel for each event.',
        'events' => 'Events',
        'events_description' => 'Choose what this webhook is told about. At least one event is required.',
    ],

    'fields' => [
        'name' => 'Name',
        'description' => 'Description',
        'enabled' => 'Enabled',
        'endpoint' => 'Endpoint URL',
        'type' => 'Type',
        'secret' => 'Signing Secret',
        'headers' => 'Custom Headers',
        'header_name' => 'Header',
        'header_value' => 'Value',
        'message_username' => 'Display Name',
        'message_avatar' => 'Avatar URL',
        'message_content' => 'Message',
        'message_title' => 'Embed Title',
        'message_description' => 'Embed Description',
        'message_color' => 'Embed Color',
        'message_footer' => 'Embed Footer',
        'message_timestamp' => 'Show when the event happened',
    ],

    'placeholders' => [
        'name' => 'Billing sync',
        'description' => 'What this webhook is for.',
    ],

    'helpers' => [
        'secret' => 'When set, every request carries an X-Webhook-Signature header holding "sha256=" followed by the HMAC of the request body made with this secret.',
        'message_variables' => 'Use {{label}} for the name of the event, {{summary}} for a sentence describing it, {{event}}, {{timestamp}}, {{panel}} and {{panel_url}}. Anything in the event can be used too, such as {{data.name}} or {{data.actor.name}}.',
    ],

    'types' => [
        'standard' => 'Standard',
        'discord' => 'Discord',
    ],

    'groups' => [
        'resources' => 'Records',
        'auth' => 'Sign-in Activity',
        'account' => 'Account Activity',
        'server_power' => 'Server Power and Console',
        'server_files' => 'Server Files',
        'server_backups' => 'Server Backups',
        'server_databases' => 'Server Databases',
        'server_schedules' => 'Server Schedules',
        'server_settings' => 'Server Settings',
        'admin' => 'Administration',
    ],

    'resources' => [
        'server' => 'Server',
        'user' => 'User',
        'subuser' => 'Subuser',
        'node' => 'Node',
        'location' => 'Location',
        'nest' => 'Nest',
        'egg' => 'Egg',
        'mount' => 'Mount',
        'database_host' => 'Database host',
        'database' => 'Database',
        'backup' => 'Backup',
        'schedule' => 'Schedule',
        'role' => 'Role',
        'alert' => 'Alert',
    ],

    'model_actions' => [
        'created' => 'created',
        'updated' => 'updated',
        'deleted' => 'deleted',
    ],

    'events' => [
        'model' => ':resource :action',
    ],

    'summary' => [
        'model' => ':label: :name',
        'activity' => ':actor: :label',
        'activity_on' => ':actor: :label (:subjects)',
        'system' => 'System',
    ],

    'columns' => [
        'name' => 'Name',
        'type' => 'Type',
        'endpoint' => 'Sends To',
        'events' => 'Events',
        'last_delivery' => 'Last Delivery',
        'enabled' => 'Enabled',
    ],

    'statuses' => [
        'delivered' => 'Delivered',
        'failed' => 'Failed',
        'never' => 'Nothing sent yet',
    ],

    'deliveries' => [
        'title' => 'Deliveries',
        'label' => 'delivery',
        'plural_label' => 'deliveries',
        'description' => 'Everything sent to this webhook in the last :days days.',
        'empty' => 'Nothing sent yet',
        'empty_description' => 'Deliveries show up here as soon as one of the chosen events happens. Use Send Test to try it now.',
        'no_response' => 'No response',
        'no_body' => 'The endpoint sent nothing back.',
        'disabled' => 'Enable the webhook to send this again.',
        'columns' => [
            'id' => 'Delivery ID',
            'status' => 'Delivered',
            'event' => 'Event',
            'response' => 'Response',
            'attempts' => 'Attempts',
            'duration' => 'Took',
            'sent' => 'Sent',
            'payload' => 'Payload',
            'body' => 'Response Body',
        ],
    ],

    'actions' => [
        'test' => 'Send Test',
        'duplicate' => 'Duplicate',
        'redeliver' => 'Send Again',
        'add_header' => 'Add Header',
        'generate_secret' => 'Generate',
        'select_all' => 'Select all',
        'deselect_all' => 'Deselect all',
    ],

    'notices' => [
        'delivered' => 'The test was delivered.',
        'failed' => 'The test could not be delivered.',
        'responded' => 'The endpoint answered :status in :ms ms.',
        'duplicated' => 'The webhook was duplicated and left disabled.',
        'redelivery_queued' => 'The event is on its way again.',
    ],

    'validation' => [
        'events' => 'Choose at least one event.',
        'header_name' => '":name" is not a valid header name.',
        'header_reserved' => 'The ":name" header is set by the panel and cannot be changed.',
    ],

    'errors' => [
        'invalid_endpoint' => 'The endpoint is not a valid http or https address.',
        'unresolvable' => 'The address for :host could not be found.',
        'private_endpoint' => ':host points to a private or reserved address. Set APP_WEBHOOK_ALLOW_PRIVATE=true to allow deliveries inside your own network.',
        'unreachable' => 'Could not connect to :host.',
        'curl_required' => 'Delivering to a hostname requires the PHP cURL extension, which is not installed.',
    ],

    'copy_suffix' => ':name (copy)',

];
