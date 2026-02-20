<?php

return [
    'gender' => [
        'Male'   => 'Мужской',
        'Female' => 'Женский',
    ],

    'communication_types' => [
        'Phone'          => 'Телефон',
        'Email'          => 'Email',
        'Address'        => 'Адрес',
        'Whatsapp'       => 'WhatsApp',
        'Telegram'       => 'Telegram',
        'Viber'          => 'Viber',
        'Link'           => 'Ссылка',
        'Geocoordinates' => 'Геокоординаты',
        'Other'          => 'Другое',
    ],

    'users_visibility' => [
        'Private'         => 'Приватный',
        'Public'          => 'Публичный',
        'FriendsOnly'     => 'Только для друзей',
        'RegisteredUsers' => 'Только для зарегистрированных пользователей',
    ],

    'relationship_status' => [
        'NotSpecified'   => 'Не указано',
        'Single'         => 'Не в отношениях',
        'InRelationship' => 'В отношениях',
        'Married'        => 'Женат / замужем',
        'Looking'        => 'В активном поиске',
    ],

    'verification_status' => [
        'NotVerified' => 'Не подтвержден',
        'Verified'    => 'Подтвержден',
    ],

    'promo_status' => [
        'Pending'   => 'Ожидает подтверждения',
        'Confirmed' => 'Подтверждено',
        'Revoked'   => 'Отозвано',
    ],

    'travel_type' => [
        'cycling'          => 'Велопоход',
        'MountainCampaign' => 'Горная кампания',
        'Hiking'           => 'Пеший поход',
        'Kayaks'           => 'Каяки',
        'MountainClimbing' => 'Альпинизм',
    ],

    'travel_visible' => [
        'Public' => 'Публичный',
        'ForMe' => 'Только для меня',
        'Platform' => 'Только для зарегистрированных пользователей',
    ],

    'travel_visible_description' => [
        'Public' => 'Любой пользователь может видеть эту походную программу',
        'ForMe' => 'Только я могу видеть эту походную программу',
        'Platform' => 'Только зарегистрированные пользователи могут видеть эту походную программу',
    ],

    'travel_status' => [
        'Draft' => 'Черновик',
        'Active' => 'Активный',
        'Archived' => 'Архивный',
        'Deleted' => 'Удаленный',
    ],
];
