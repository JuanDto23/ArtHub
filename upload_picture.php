<?php   //Deteccion de errores
session_start(); // Inicia la sesión
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<?php
// Verifica que se haya iniciado sesión o se haya registrado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: login.php");
        exit;
}

$id_user = $_SESSION['id_user'];
$name = $_SESSION['name'];

// Conexión a la base de datos
$servername = "localhost";
$username = "admin";
$password = "admin123";
$dbname = "artHub";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>

<!-- Formulario para subir el dibujo-->
<!DOCTYPE html>
<html>
<head>
        <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
        <meta charset="UTF-8">
        <title>artHub - Subir Dibujo</title>
        <link rel="stylesheet" type="text/css" href="css/upload_picture.css">
</head>
<body>

<form action="upload_picture.php" method="post" enctype="multipart/form-data">
        <input type="file" name="fileToUpload" id="fileToUpload">
	<input type="text" name="picture_name" placeholder="Nombre">
        <input type="number" name="initial_price" placeholder="Precio">
        <button type="submit" value="Upload Image">Subir Dibujo</button>
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(strlen($_POST['picture_name']) >= 1 && strlen($_POST['initial_price']) >= 1){
        // Obtener datos del formulario
        $id_picture = 0;
        $picture_name = $_POST['picture_name'];
        $initial_price = $_POST['initial_price'];
        $name_image= $picture_name . uniqid() . ".jpg";

        // Procesar imagen y generar nombre de archivo único
        $imagen = "uploads/" . $name_image; // Suponiendo que solo suben JPG

        // Mover la imagen al directorio
        move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $imagen);

        // Calcular la fecha de fin de la subasta
        date_default_timezone_set('America/Mexico_City'); 
        $final_time = date('Y-m-d H:i:s', strtotime("+15 minute"));

        /* Insertar en la base de datos.
        Suponemos que cuando se sube la imagen, también empieza a correr la 
        subasta que dura 15 minutos. */
        $sql= "INSERT INTO picture (picture_name, initial_price, registration_date, state) 
                VALUES ('$name_image', $initial_price, NOW(), 'Disponible')";
        $conn->query($sql);
        
        // Obtener id_picture del dibujo creado
        $sql="SELECT id_picture FROM picture WHERE picture_name='$name_image'";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
                $id_picture = $row['id_picture'];
        }

        // Se crea la subasta
        $sql= "INSERT INTO auction (id_picture, initial_time, final_time)
                VALUES ($id_picture, NOW(), '$final_time')";
        $conn->query($sql);

        //Se hace las relacion entre la pintura y artistas
        $sql= "INSERT INTO picture_artist (id_picture, id_artist)
                VALUES ($id_picture, $id_user)";
        $conn->query($sql);

        echo "Dibujo subido exitosamente para subasta.";
     }
     else{
        $error = "Por favor, completa todos los campos.";
     }
     
}
?>

<br/>
<a href="gallery.php" class="boton_regresar">Regresar a Galería</a>
<br/>

<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

<?php
$conn->close();
?>
<br/>
</body>
</html>
