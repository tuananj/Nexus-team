<?php

require "vendor/autoload.php";
include "config.php";

use PragmaRX\Google2FA\Google2FA;

$google2fa = new Google2FA();

if(isset($_POST['verify'])){

$code = $_POST['code'];

$secret = $_SESSION['secret'];

$valid = $google2fa->verifyKey($secret,$code);

if($valid){

$_SESSION['verified']=true;

header("Location: dashboard.php");

}else{

$error="Invalid code";

}

}

?>

<!DOCTYPE html>
<html>

<head>

<title>Verify Code</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container">

<div class="row justify-content-center">

<div class="col-md-4">

<div class="card mt-5 shadow">

<div class="card-body">

<h4 class="text-center">Enter Authenticator Code</h4>

<?php if(isset($error)){ ?>

<div class="alert alert-danger">
<?php echo $error; ?>
</div>

<?php } ?>

<form method="POST">

<input type="text" name="code" class="form-control mb-3">

<button class="btn btn-success w-100" name="verify">
Verify
</button>

</form>

</div>

</div>

</div>

</div>

</div>

</body>
</html>