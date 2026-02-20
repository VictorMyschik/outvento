<?php

return [
    'gender' => [
        'Male'   => 'Male',
        'Female' => 'Female',
    ],

    'communication_types' => [
        'Phone'          => 'Phone',
        'Email'          => 'Email',
        'Address'        => 'Address',
        'Whatsapp'       => 'WhatsApp',
        'Telegram'       => 'Telegram',
        'Viber'          => 'Viber',
        'Link'           => 'Link',
        'Geocoordinates' => 'Geo coordinates',
        'Other'          => 'Other',
    ],

    'users_visibility' => [
        'Private'         => 'private',
        'Public'          => 'public',
        'FriendsOnly'     => 'friends only',
        'RegisteredUsers' => 'registered users',
    ],

    'relationship_status' => [
        'NotSpecified'   => 'Not specified',
        'Single'         => 'Single',
        'InRelationship' => 'In a relationship',
        'Married'        => 'Married',
        'Looking'        => 'Looking for a relationship',
    ],

    'verification_status' => [
        'NotVerified' => 'Not verified',
        'Verified'    => 'Verified',
    ],

    'promo_status' => [
        'Pending'   => 'Pending confirmation',
        'Confirmed' => 'Confirmed',
        'Revoked'   => 'Revoked',
    ],

    'travel_type' => [
        'cycling'          => 'Cycling',
        'MountainCampaign' => 'Mountain campaign',
        'Hiking'           => 'Hiking',
        'Kayaks'           => 'Kayaks',
        'MountainClimbing' => 'Mountain climbing',
    ],

    'travel_visible' => [
        'Public'   => 'Public',
        'ForMe'    => 'Only for me',
        'Platform' => 'Only for registered users',
    ],

    'travel_visible_description' => [
        'Public'   => 'Public, participates in public searches',
        'ForMe'    => 'Only for me, does not participate in public searches',
        'Platform' => 'Only for registered users, does not participate in public searches',
    ],

    'travel_status' => [
        'Draft'    => 'Draft',
        'Active'   => 'Active',
        'Archived' => 'Archived',
        'Deleted'  => 'Deleted',
    ],
];
