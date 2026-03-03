<?php

echo 'admin_password:' .
    password_hash('admin_password', PASSWORD_ARGON2ID) .
    '<br>';
echo 'user_password:' .
    password_hash('user_password', PASSWORD_ARGON2ID) .
    '<br>';
echo 'viewer_password:' .
    password_hash('viewer_password', PASSWORD_ARGON2ID) .
    '<br>';
