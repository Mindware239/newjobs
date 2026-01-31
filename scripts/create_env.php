<?php

// Helper script to create .env from .env.example
if (!file_exists('.env') && file_exists('.env.example')) {
    copy('.env.example', '.env');
    echo ".env file created from .env.example\n";
    echo "Please update the values in .env file\n";
} else {
    echo ".env file already exists or .env.example not found\n";
}

