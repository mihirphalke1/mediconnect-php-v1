<?php
/**
 * Redirects to the book_appointment.php in the pages directory
 * This file exists for backward compatibility with any existing links
 */

// Include configuration
require_once 'includes/config.php';

// Forward any query parameters
$queryString = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';

// Redirect to the pages version
header("Location: pages/book_appointment.php{$queryString}");
exit(); 