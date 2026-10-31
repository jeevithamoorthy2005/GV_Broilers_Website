<?php
require_once __DIR__ . '/db.php';

$siteProfileQuery = mysqli_query(
    $con,
    'SELECT mobile, email, address FROM profile1 LIMIT 1'
);

if ($siteProfileQuery === false) {
    error_log('Unable to load site contact details: ' . mysqli_error($con));
    http_response_code(500);
    exit('Unable to load site contact details. Check the profile1 table.');
}

$siteProfile = mysqli_fetch_assoc($siteProfileQuery);
if ($siteProfile === null) {
    http_response_code(500);
    exit('No contact profile was found in the profile1 table.');
}

$siteMobile = trim((string) $siteProfile['mobile']);
$siteEmail = trim((string) $siteProfile['email']);
$siteAddress = trim((string) $siteProfile['address']);
$sitePhoneDigits = preg_replace('/\D+/', '', $siteMobile);
$siteWhatsappNumber = strlen($sitePhoneDigits) === 10
    ? '91' . $sitePhoneDigits
    : $sitePhoneDigits;
$sitePhoneDisplay = strlen($sitePhoneDigits) === 10
    ? '+91 ' . $sitePhoneDigits
    : $siteMobile;
?>
