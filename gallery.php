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

<!DOCTYPE html>
<html lang="es">
<html>
    <head>
        <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
        <meta charset="UTF-8">
        <title>artHub - Galería</title>
        <link rel="stylesheet" type="text/css" href="css/gallery.css">
    </head>
<body>
<!-- Botones de navegación -->
<header>
<nav>
    <ul>
        <li><a href="add_artist.php" class="boton_agregar_artista">AÑADIR ARTISTA</a></li>
        <li><a href="upload_picture.php" class="boton_publicar">PUBLICAR</a></li>
        <li><a href="offer.php" class="boton_ofertar">OFERTAR</a></li>
        <li><a href="new_auction.php" class="boton_nueva_subasta">NUEVA SUBASTA</a></li>
        <li><a href="logout.php" class="boton_cerrar_sesión">CERRAR SESIÓN</a></li>
    </ul>                                  
</nav>
</header>
<h1>Bienvenido, <?php echo htmlspecialchars($name); ?> (ID: <?php echo htmlspecialchars($id_user); ?>)</h1>
<br/>

<?php
// Comprobación de subastas cerradas de imaégenes disponibles
$sql= "SELECT auction.id_auction, auction.id_picture, auction.id_winner, auction.initial_time, auction.final_time 
       FROM auction 
       INNER JOIN picture ON auction.id_picture = picture.id_picture 
       WHERE NOW() > auction.final_time AND picture.state = 'Disponible'";
$result = $conn->query($sql);

// Verificar si hay resultados
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id_picture = $row['id_picture'];
        $id_auction = $row['id_auction'];
        
        // Obtener la mayor oferta de la imagen en cuestión
        $max_sql = "SELECT MAX(amount) AS max_amount FROM bid WHERE id_auction = $id_auction";
        $max_result = $conn->query($max_sql);
        $row = $max_result->fetch_assoc();
        $max_amount = $row['max_amount']; // Si no hay ofertas, max_amount será null

        // Si hubo ofertas, entonces marcar como "Vendido", asignar el dueño a la lista de dueños y añadir ganador a la subasta
        if ($max_amount !== NULL){
            $bid_sql = "SELECT id_bidder FROM bid WHERE amount=$max_amount AND id_auction = $id_auction";
            $bid_result = $conn->query($bid_sql);
            $bid_row = $bid_result->fetch_assoc();
            $id_winner = $bid_row['id_bidder'];
            
            $update_sql = "UPDATE picture SET state = 'Vendido' WHERE id_picture = $id_picture";
            $conn->query($update_sql);
            
            $insert_sql = "INSERT INTO owner_list (id_picture, id_owner) VALUES ($id_picture, $id_winner)";
            $conn->query($insert_sql);

            $update_sql = "UPDATE auction SET id_winner = $id_winner WHERE id_auction = $id_auction";
            $conn->query($update_sql);

            echo "Imagen ID $id_picture ha sido vendida por $max_amount al usuario ID $id_winner.<br>";
        } else {
            // Si no hubo ofertas, marcar como "Cerrado"
            $update_sql = "UPDATE picture SET state = 'Cerrado' WHERE id_picture = $id_picture";
            $conn->query($update_sql);
            echo "Imagen ID $id_picture no tuvo ofertas y se ha cerrado.<br>";
        }
    }
}

// Mostrar imágenes con estado "Disponible"
$sql= "SELECT auction.id_auction, auction.id_picture, auction.id_winner, auction.initial_time, auction.final_time, picture.picture_name, picture.initial_price
       FROM auction 
       INNER JOIN picture ON auction.id_picture = picture.id_picture 
       WHERE NOW() BETWEEN initial_time AND final_time AND picture.state = 'Disponible'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<h2>Imágenes Disponibles</h2>";
    echo "<div style='display: flex; flex-wrap: wrap;'>";

    while ($row = $result->fetch_assoc()) {
        $id_auction = $row['id_auction'];
        $id_picture = $row['id_picture'];
        $picture_name= $row['picture_name']; // nombre del archivo de la imagen
        $final_time = $row['final_time'];
        $initial_price = $row['initial_price'];
        $max_sql = "SELECT MAX(amount) AS max_amount FROM bid WHERE id_auction = $id_auction";
        $max_result = $conn->query($max_sql);
        $row = $max_result->fetch_assoc();
        $max_amount = $row['max_amount']; // Si no hay ofertas, max_amount será null

        // Mostrar imagen
        echo "<div style='margin: 10px; text-align: center;'>";
        echo "<img src=uploads/$picture_name alt='Imagen $id_picture' style='width: 200px; height: auto; border: 1px solid #ddd;'>";
        echo "<p>Fecha limite de oferta: $final_time</p>";
        if($max_amount != NULL){
            echo "<p> Oferta más alta: $max_amount</p>";
        }
        else{
            echo "<p>Oferta inicial: $initial_price</p>";
        }
        echo "<p>ID Imagen: $id_picture</p>";
        echo "</div>";
    }
    echo "</div>";
} else {
    echo "No hay subastas disponibles por el momento. Vuelva más tarde.<br>";
}

// Cerrar conexión
$conn->close();
?>
<br/>
</body>
</html>
