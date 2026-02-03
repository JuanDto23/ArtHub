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

<!-- Formulario para añadir nuevo artista a algún dibujo del que esté iniciado en sesión-->
<!DOCTYPE html>
<html lang="es">
<html>
    <head>
        <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
        <meta charset="UTF-8">
        <title>ArtHub - Agregar Artista</title>
        <link rel="stylesheet" type="text/css" href="css/add_artist.css">
    </head>
<body>
<form action="add_artist.php" method="post">
    <input type="number" name="id_picture" placeholder="ID pintura">
    <input type="number" name="id_artist" placeholder="ID Artista">
    <input type="submit" name="Agregar" value="Agregar Artista">
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_picture = $_POST['id_picture'];
    $id_artist = $_POST['id_artist'];
    if(strlen($_POST['id_picture']) >= 1 && strlen($_POST['id_artist']) >= 1){
        // Verificar que el usuario tenga pinturas subidas
        $sql= "SELECT * FROM picture_artist WHERE id_artist=$id_user";
        $result = $conn->query($sql);
        if($result){
            // Verificar que la pintura que quiere añadir artista sea de su autoridad
            $sql="SELECT id_picture FROM picture_artist WHERE id_picture=$id_picture AND id_artist=$id_user";
            $result = $conn->query($sql);
            if($result->num_rows > 0 ) {
                // Verificar que el artista ingresado exista
                $sql="SELECT * FROM userP WHERE id_user=$id_artist";
                $result = $conn->query($sql);
                if($result->num_rows > 0 ){
                    // Verificar que el artista ingresado no sea parte de la pintura en cuestión
                    $sql="SELECT * FROM picture_artist WHERE id_picture=$id_picture AND id_artist=$id_artist";
                    $result = $conn->query($sql);
                    if($result->num_rows > 0){
                        echo "Artista ya es parte de los artistas de la pintura";
                    } else{
                        $insert_sql = "INSERT INTO picture_artist (id_picture, id_artist)
                                        VALUES ($id_picture, $id_artist)";
                        $result = $conn->query($insert_sql);
                        echo "Artista con ID: $id_artist se ha agregado con éxito.";
                    }
                }
                else{
                    echo "El ID de artista no se ha encontrado.";
                }

            } else {
                echo "El ID de la pintura no pertenece de su autoridad.";
            } 
        } else {
            $error = "Por favor, completa todos los campos.";
        }
        
    }
}

$sql= "SELECT id_picture, id_artist FROM picture_artist WHERE id_artist=$id_user";
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
