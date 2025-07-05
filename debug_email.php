<?php

require_once './vendor/autoload.php';

use Illuminate\Support\Facades\Validator;

echo "Testing email validation...\n";

$validator = Validator::make(['email' => 'invalid-email'], ['email' => 'required|email']);

if ($validator->fails()) {
    echo "Validation failed for 'invalid-email': " . print_r($validator->errors()->toArray(), true);
} else {
    echo "Validation passed for 'invalid-email'\n";
}

$validator2 = Validator::make(['email' => 'test@example.com'], ['email' => 'required|email']);

if ($validator2->fails()) {
    echo "Validation failed for 'test@example.com': " . print_r($validator2->errors()->toArray(), true);
} else {
    echo "Validation passed for 'test@example.com'\n";
}