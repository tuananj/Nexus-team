<?php

require "vendor/autoload.php";
include "config.php";

use PragmaRX\Google2FA\Google2FA;

$google2fa = new Google2FA();

$secret = $google2fa->generateSecretKey();

$_SESSION['secret'] = $secret;

$QR = $google2fa->getQRCodeUrl(
    "MFA-System",
    "user@test.com",
    $secret
);

?>

<!DOCTYPE html>
<html>

<head>

<title>QR Setup</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container">

<div class="row justify-content-center">

<div class="col-md-4">

<div class="card mt-5 shadow text-center">

<div class="card-body">

<h4>Scan QR Code</h4>

<p>Scan using Google Authenticator</p>

<img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode($QR); ?>">

<br><br>

<a href="verify_qr.php" class="btn btn-primary w-100">
Continue
</a>

</div>

</div>

</div>

</div>

</div>

</body>
</html>