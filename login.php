<?php   //Detección de errores
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

<!-- Formulario de Inicio de Sesión -->
<!DOCTYPE html>
<html lang="es">
<html>
    <head>
        <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
        <meta charset="UTF-8">
        <title>ArtHub - Inicio de sesión</title>
        <link rel="stylesheet" type="text/css" href="css/login.css">
    </head>
<body>
    <form method="post">
        <h1>Ingresa datos</h1>
        <input type="number" name="id_user" placeholder="ID">
        <input type="text" name="pwd" placeholder="Contraseña">
        <input type="submit" name="login" value="Iniciar Sesión">
    </form>
    <p>¿No tienes alguna cuenta registrada?</p>
    <a href="register.php" class="boton_registrarse">Regístrate</a>

<?php // Comprobación que datos ingresados sean correctos
if(isset($_POST['login'])){
    if(strlen($_POST['id_user']) >= 1 && strlen($_POST['pwd']) >= 1){
        $id_user=$_POST['id_user'];
        $pwd=$_POST['pwd'];

        // Verificando que la contraseña e ID ingresados sean correctos
        $sql= "SELECT id_user, name FROM userP WHERE id_user='$id_user' AND pwd='$pwd'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $_SESSION['logged_in'] = true;
            $_SESSION['id_user'] = $row['id_user'];
            $_SESSION['name'] = $row['name'];
            header("Location: gallery.php");
            exit;
	    }
        else{
            $error = "ID o contraseña incorrectas.";
        }	
    } else{
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

