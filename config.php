<?php
// Runtime configuration. BASE_URL may be supplied by deployment; otherwise
// derive it from the current request without embedding host credentials.
$configuredBaseUrl = trim((string) (getenv('BASE_URL') ?: ''));
if ($configuredBaseUrl === '') {
  $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
  $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
  $scheme = $https ? 'https' : 'http';
  $configuredBaseUrl = $scheme . '://' . $host . '/sow/Optica/';
}

define('BASE_URL', rtrim($configuredBaseUrl, '/') . '/');

define('RESET_EXPIRA', 1800);

function base_url(): string {
  return rtrim(BASE_URL, '/') . '/';
}
