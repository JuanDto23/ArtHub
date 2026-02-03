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

<!-- Formulario para nueva subasta de algún dibujo que nunca se vendió-->
<!DOCTYPE html>
<html lang="es">
<html>
    <head>
        <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
        <meta charset="UTF-8">
        <title>ArtHub - Nueva Subasta</title>
        <link rel="stylesheet" type="text/css" href="css/new_auction.css">
    </head>
<body>
<form action="new_auction.php" method="post">
    <input type="number" name="id_picture" placeholder="ID pintura">
    <input type="submit" name="Subastar" value="Subastar">
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_picture = $_POST['id_picture'];
    if(strlen($_POST['id_picture']) >= 1){
        // Verificar que la pintura ingresada esté en "Cerrado" y que exista
        $sql= "SELECT * FROM picture WHERE id_picture=$id_picture AND state='Cerrado'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Verificar que esa pintura sea de autoría
            $sql="SELECT * FROM picture_artist WHERE id_picture=$id_picture AND id_artist=$id_user";
            $result = $conn->query($sql);
            if($result->num_rows >0){
                
                date_default_timezone_set('America/Mexico_City'); 
                $final_time = date('Y-m-d H:i:s', strtotime("+20 minute"));

                $update_sql = "UPDATE picture SET state = 'Disponible' WHERE id_picture=$id_picture";
                $result_update = $conn->query($update_sql);

                // Realizar la nueva subasta
                $sql= "INSERT INTO auction (id_picture, initial_time, final_time)
                VALUES ($id_picture, NOW(), '$final_time')";
                $conn->query($sql);

                echo "Nueva subasta realizada éxitosamente.";
            } else {
                echo "El ID de pintura ingresa no es de su autoría.";
            }
            
        } else {
            echo "No existe pintura a subastar" . $conn->error;
        }
    } else {
        $error = "Por favor, completa todos los campos.";
    }   
}

// Mostrar imágenes que estén cerradas
$sql= "SELECT p.id_picture, pa.id_artist FROM picture AS p 
        INNER JOIN picture_artist AS pa ON p.id_picture = pa.id_picture 
        WHERE state='Cerrado' AND pa.id_artist = $id_user";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<h2>Imágenes de $name - ID: $id_user</h2>";
    echo "<div style='display: flex; flex-wrap: wrap;'>";

    while ($row = $result->fetch_assoc()) {
        $id_picture = $row['id_picture'];
        // Consulta para obtener picture_name e id_picture
        $image_sql="SELECT picture_name FROM picture WHERE id_picture=$id_picture";
        $result_image = $conn->query($image_sql);
        $row_image = $result_image->fetch_assoc();
        $picture_name = $row_image['picture_name'];

        // Mostrar imagen
        echo "<div style='margin: 10px; text-align: center;'>";
        echo "<img src=uploads/$picture_name alt='Imagen $id_picture' style='width: 200px; height: auto; border: 1px solid #ddd;'>";
        echo "<p>ID Imagen: $id_picture</p>";

        $artists_sql = "SELECT * FROM picture_artist WHERE id_picture = $id_picture";
        $result_artists = $conn->query($artists_sql);
        echo "<p>Artistas (ID's):</p>";
        while ($row_artist = $result_artists->fetch_assoc()) {
            $id_artist=$row_artist['id_artist'];
            echo "<p>ID: $id_artist</p>";
        }

        echo "</div>";
    }
    echo "</div>";
} else {
    echo "No hay pinturas por mostrar.<br>";
}


?>

<br/>
<a href="gallery.php" class="boton_regresar">Regresar a Galería</a>
<br/>

<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

<?php
$conn->close();
?>
</body>
</html>
