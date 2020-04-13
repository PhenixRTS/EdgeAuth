<?php

require_once __DIR__ . '/../vendor/autoload.php';
use EdgeAuth\TokenBuilder;

$builtToken = ( new TokenBuilder() )
    ->withApplicationId('my-application-id')
    ->withSecret('my-secret')
    ->expiresInSeconds(3600)
    ->forChannel('us-northeast#my-application-id#my-channel.1345')
    ->build();

error_log($builtToken);
