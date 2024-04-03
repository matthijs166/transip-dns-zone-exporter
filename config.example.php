<?php 

global $config;

$config = [
    'login' => '', // Your TransIP login
    // Generate a Key Pairs at https://www.transip.nl/cp/account/api/
    // Save to key.txt
    // Make sure your ip is whitelisted
    'privateKey' => file_get_contents( 'key.txt' ),
    'domainName' => '', // The domain name you want to export
    'tags' => [], // Optional: Tags you want to export
    'generateWhitelistOnlyTokens' => true,
    'exportAuthorizations' => false, // Set to true if you want to export the authorizations
];