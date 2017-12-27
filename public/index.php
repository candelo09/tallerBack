<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';

// Run app

function getConnection() {
    $dbhost="127.0.0.1";
    $dbuser="root";
    $dbpass="";
    $dbname="patentes";
    $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}

function obtenerPatentes($response) {
    $sql = "SELECT * FROM patentesregis";
    try {
        $stmt = getConnection()->query($sql);
        $patentes = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        
        return json_encode($patentes);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}


function agregarPatente($request) {
    $emp = json_decode($request->getBody());
    
    $sql = "INSERT INTO patentesregis (nombrePatente, areaConocimiento, autorPatente, anioPatente) VALUES (:nombrePatente, :areaConocimiento, :autorPatente, :anioPatente)";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("nombrePatente", $emp->nombrePatente);
        $stmt->bindParam("areaConocimiento", $emp->areaConocimiento);
        $stmt->bindParam("autorPatente", $emp->autorPatente);
        $stmt->bindParam("anioPatente", $emp->anioPatente);
        $stmt->execute();
        $emp->id = $db->lastInsertId();
        $db = null;
        echo json_encode($emp);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function actualizarPatente($request) {
    $emp = json_decode($request->getBody());
    $id = $request->getAttribute('id');
    $sql = "UPDATE patentesregis SET nombrePatente=:nombrePatente, areaConocimiento=:areaConocimiento, autorPatente=:autorPatente, anioPatente=:anioPatente WHERE id=:id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("nombrePatente", $emp->nombre);
        $stmt->bindParam("areaConocimiento", $emp->areaConocimiento);
        $stmt->bindParam("autorPatente", $emp->autorPatente);
        $stmt->bindParam("anioPatente", $emp->anioPatente);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $db = null;
        echo json_encode($emp);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}


function eliminarPatente($request) {
    $id = $request->getAttribute('id');
    $sql = "DELETE FROM patentesregis WHERE id=:id";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $db = null;
        echo '{"error":{"text":"Se elimino patente"}}';
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

$app->run();
