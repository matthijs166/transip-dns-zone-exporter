<?php 

global $config;

$config = [
    'login' => '', // Your TransIP login
    // Generate a Key Pairs at https://www.transip.nl/cp/account/api/
    // Save to key.txt
    // Make sure your ip is whitelisted
    'privateKey' => file_get_contents( 'key.txt' ),
    'domainNames' => [], // List of Domain names (string) you want to export
    'tags' => [], // Optional: Tags you want to export
    'generateWhitelistOnlyTokens' => true,
    'exportAuthorizationCodes' => false, // Export codes to be used for domain transfer etc.
];