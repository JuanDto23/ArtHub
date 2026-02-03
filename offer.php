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

<!-- Formulario para ofertar -->
<!DOCTYPE html>
<html lang="es">
<html>
    <head>
        <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
        <meta charset="UTF-8">
        <title>ArtHub - Ofertar</title>
        <link rel="stylesheet" type="text/css" href="css/offer.css">
    </head>
<body>
<form action="offer.php" method="post">
    <input type="number" name="id_picture" placeholder="ID pintura">
    <input type="number" name="amount" placeholder="Catidad">
    <input type="submit" name="Ofertar" value="Ofertar">
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_picture = $_POST['id_picture'];
    $amount = $_POST['amount'];
    if(strlen($_POST['id_picture']) >= 1 && strlen($_POST['amount']) >= 1){
        /* Verifica si existe una subasta en el momento actual de una imagen. 
        Si sí la hay, entonces la consulta será distinto de nulo. */
        $sql= "SELECT * FROM auction WHERE id_picture=$id_picture AND NOW() BETWEEN initial_time AND final_time";
        $result = $conn->query($sql);

        if($result){
            $row= $result->fetch_assoc();
            $id_auction = $row['id_auction'];

            // Obtener la oferta más alta actual
            $sql= "SELECT MAX(amount) as max_bid FROM bid WHERE id_auction = $id_auction";
            $result = $conn->query($sql);

            if ($result) {
                $row = $result->fetch_assoc();
                $max_bid = $row['max_bid']; // Si no hay ofertas, max_bid será null

                if ($max_bid !== null) {
                    // Verificar si la nueva oferta es válida
                    if ($amount > $max_bid) {
                        $sql = "INSERT INTO bid (id_auction, id_bidder, amount, time) 
                                VALUES ($id_auction, $id_user, $amount, NOW())";
                        if ($conn->query($sql)) {
                            echo "Oferta realizada exitosamente.";
                        } else {
                            echo "Error al realizar la oferta: " . $conn->error;
                        }
                    } else {
                        echo "Tu oferta debe ser mayor que la oferta actual ($max_bid).";
                    }
                } else {
                    // No hay ofertas actuales, verificar el precio inicial
                    $result = $conn->query("SELECT initial_price FROM picture WHERE id_picture = $id_picture");
                    if ($result && $row = $result->fetch_assoc()) {
                        $initial_price = $row['initial_price'];
                        if ($amount > $initial_price) {
                            $sql = "INSERT INTO bid (id_auction, id_bidder, amount, time) 
                                    VALUES ($id_auction, $id_user, $amount, NOW())";
                            if ($conn->query($sql)) {
                                echo "Primera oferta realizada exitosamente.";
                            } else {
                                echo "Error al realizar la oferta: " . $conn->error;
                            }
                        } else {
                            echo "Tu oferta debe ser mayor que el precio inicial ($initial_price).";
                        }
                    } else {
                        echo "No se encontró la pintura con ID $id_picture.";
                    }
                }
            } else {
                echo "Error al consultar la base de datos: " . $conn->error;
            }
        
        }
        else {
            "Error al consultar la base de datos: " . $conn->error;
        }
    } else {
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
</body>
</html>
