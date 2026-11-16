<?php

return [
    'title' => 'Activity log',
    'subtitle' => 'Who changed what and when',
    'fields' => [
        'date' => 'Date',
        'user' => 'User',
        'model' => 'Table',
        'event' => 'Event',
        'description' => 'Description',
        'changes' => 'Changes',
        'from' => 'From',
        'to' => 'To',
    ],
    'events' => [
        'created' => 'created',
        'updated' => 'updated',
        'deleted' => 'deleted',
    ],
    'all_users' => 'All users',
    'all_models' => 'All tables',
    'system' => 'System',
    'no_changes' => 'No changes recorded',
];
