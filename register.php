<?php   //Deteccion de errores
session_start(); // Inicia la sesión
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<?php // Establece conexión con la base de datos
$servername = "localhost";
$username = "admin";
$password = "admin123";
$dbname = "artHub";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>

<!-- Formulario par Registrarse -->
<!DOCTYPE html>
<html lang="es">
<html>
    <head>
        <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
        <meta charset="UTF-8">
        <title>ArtHub - Registro</title>
        <link rel="stylesheet" type="text/css" href="css/register.css">
    </head>
<body>
    <form method="post">
        <h1>Ingresa datos</h1>
        <input type="text" name="name" placeholder="Nombre">
        <input type="text" name="email" placeholder="Email">
        <input type="text" name="phone_number" placeholder="Numero de telefono">
        <input type="text" name="pwd" placeholder="Contraseña">
        <input type="submit" name="register" value="Registrarse">
    </form>
    <p>¿Ya tienes alguna cuenta registrada?</p>
    <a href="login.php" class="boton_registrarse">Inicia Sesión</a>

<?php // Registrando nuevo usuario a la base de datos
if(isset($_POST['register'])){

    if(strlen($_POST['name']) >= 1 && strlen($_POST['email']) >= 1 && strlen($_POST['phone_number']) >= 1 && strlen($_POST['pwd']) >= 1){
        $name=$_POST['name'];
        $email=$_POST['email'];
        $phone_number=$_POST['phone_number'];
        $pwd=$_POST['pwd'];

        $sql = "INSERT INTO userP(name, email, phone_number, pwd) VALUES ('$name', '$email', '$phone_number', '$pwd')";
        if ($conn->query($sql) == TRUE) {
            // Se obtiene ID del nuevo usuario para enviarlo hacia gallery.php
            $sql= "SELECT id_user, name FROM userP WHERE email='$email'";
            $result = $conn->query($sql);

            if($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $id_user = $row["id_user"];
                $_SESSION['logged_in'] = true;
                $_SESSION['id_user'] = $id_user;
                $_SESSION['name'] = $name;
                header("Location: gallery.php");
                exit;
            } else {
                $error = "ID no encontrado." . $conn->error;
            }

        } else {
            $error = "Datos inválidos." . $conn->error;
        }

    } else {
        $error = "Por favor, completa todos los campos.";
    }
}

// Cerrar conexión con la base de datos
$conn->close();
?>

<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<br/>
</body>
</html>
