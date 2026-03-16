<?php
include "config.php";

if(!isset($_SESSION['user_id'])){
header("Location: login.php");
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<nav class="navbar navbar-dark bg-dark">
<div class="container">

<span class="navbar-brand">
MFA System
</span>

<a href="logout.php" class="btn btn-danger">
Logout
</a>

</div>
</nav>

<div class="container mt-5">

<div class="alert alert-success">
Login successful with MFA 🎉
</div>

</div>

</body>
</html>