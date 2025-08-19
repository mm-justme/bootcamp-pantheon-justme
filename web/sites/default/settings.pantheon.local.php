<?php
if (isset($_ENV['PANTHEON_ENVIRONMENT'])) {
$settings['file_public_path']  = 'sites/default/files';
$settings['file_private_path'] = $_ENV['HOME'] . '/files/private';
$settings['file_temp_path']    = $_ENV['HOME'] . '/tmp';
}