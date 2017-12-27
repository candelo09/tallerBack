<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});


// Routes
// Grupo de rutas para el API
$app->group('/api', function () use ($app) {
  // Version group
  $app->group('/v1', function () use ($app) {
    $app->get('/patentes', 'obtenerPatentes');
    $app->get('/codigoPatente/{id}', 'obtenerPatente');
    $app->post('/crear', 'agregarPatente');
    $app->put('/actualizar/{id}', 'actualizarPatente');
    $app->delete('/eliminar/{id}', 'eliminarPatente');
  });
});
