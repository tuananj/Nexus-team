<?php
include "config.php";

if(isset($_POST['login'])){

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE email='$email'";
$result = $conn->query($sql);

if($result->num_rows > 0){

$user = $result->fetch_assoc();

if(password_verify($password,$user['password'])){

$_SESSION['user_id'] = $user['id'];

header("Location: setup_qr.php");

}else{

$error="Wrong password";

}

}else{

$error="User not found";

}

}
?>

<!DOCTYPE html>
<html>

<head>

<title>Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container">

<div class="row justify-content-center">

<div class="col-md-4">

<div class="card mt-5 shadow">

<div class="card-header text-center">
<h4>Login</h4>
</div>

<div class="card-body">

<?php if(isset($error)){ ?>

<div class="alert alert-danger">
<?php echo $error; ?>
</div>

<?php } ?>

<form method="POST">

<div class="mb-3">
<label>Email</label>
<input type="email" name="email" class="form-control">
</div>

<div class="mb-3">
<label>Password</label>
<input type="password" name="password" class="form-control">
</div>

<button class="btn btn-success w-100" name="login">
Login
</button>

</form>

<br>

<a href="register.php">Create account</a>

</div>
</div>
</div>
</div>
</div>

</body>
</html>