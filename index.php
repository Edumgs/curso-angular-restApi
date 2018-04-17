<?php
  require_once 'vendor/autoload.php';

  $app = new \Slim\Slim();

  // Configuracion de cabeceras
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Allow: GET, POST, OPTIONS, PUT, DELETE");
  $method = $_SERVER['REQUEST_METHOD'];
  if($method == "OPTIONS") {
    die();
  }

  $conn_string = "host=localhost port=5432 dbname=curso_angular user=eduardo password=123456 options='--client_encoding=UTF8'";
  $result = array(
    'status' => 'error',
    'code' => 404,
    'message' => 'Error de conexion'
  );
  $db = pg_connect($conn_string) or die (json_encode($result). pg_last_error());

  // Prueba de funcionamiento
  $app->get("/pruebas", function() use($app) {
    echo "Hola mundo desde prueba\n";
  });

  // Listar todos los productos
  $app->get("/productos", function() use($app, $db) {
    $query = "SELECT * FROM productos ORDER BY id DESC;";
    $select = pg_query($query);

    $productos = array();
    while ($producto = pg_fetch_assoc($select)) {
      $productos[] = $producto;
    }

    $result = array(
      'status' => 'success',
      'code' => 200,
      'data' => $productos
    );

    echo json_encode($result);

    pg_close($db);
  });

  // Devolver un solo productos
  $app->get("/productos/:id", function($id) use($app, $db) {
    $query = "SELECT * FROM productos WHERE id = ".$id.";";
    $select = pg_query($db, $query);

    $result = array(
      'status' => 'error',
      'code' => 404,
      'message' => 'Producto no encontrado'
    );

    if (pg_num_rows($select) != 0) {
      $producto = pg_fetch_assoc($select);
      $result = array(
        'status' => 'success',
        'code' => 200,
        'data' => $producto
      );
    }

    echo json_encode($result);

    pg_close($db);
  });

  // Eliminar un productos
  $app->get("/productos/delete/:id", function($id) use($app, $db) {
    $query = "DELETE FROM productos WHERE id = ".$id.";";
    $delete = pg_query($db, $query);

    $result = array(
      'status' => 'error',
      'code' => 404,
      'message' => 'El producto no se ha eliminado'
    );

    if ($delete) {
      $result = array(
        'status' => 'success',
        'code' => 200,
        'message' => 'El producto se ha eliminado correctamente'
      );
    }

    echo json_encode($result);

    pg_close($db);
  });

  // Actualizar un producto
  $app->post("/productos/update/:id", function($id) use($app, $db) {
    $json = $app->request->post('json');
    $data = json_decode($json, true);
    $query = "UPDATE productos SET ".
             "nombre = '{$data['nombre']}', ".
             "descripcion = '{$data['descripcion']}', ".
             "precio = '{$data['precio']}' WHERE id = {$id};";
    $update = pg_query($db, $query);

    $result = array(
      'status' => 'error',
      'code' => 404,
      'message' => 'El producto no se ha actualizado correctamente'
    );

    if ($update) {
      $result = array(
        'status' => 'success',
        'code' => 200,
        'message' => 'El Producto se ha actualizado correctamente'
      );
    }

    echo json_encode($result);

    pg_close($db);
  });

  // Subir una imagen de un producto
  $app->post("/productos/update_file", function() use($app, $db) {
    $result = array(
      'status' => 'error',
      'code' => 404,
      'message' => 'El archivo no ha podido subirse'
    );

    if (isset($_FILES['uploads'])) {
      $piramideUploader = new PiramideUploader();
      $upload = $piramideUploader->upload("image", "uploads", "/home/eduardo/Pictures", array('image/jpeg', 'image/png', 'image/gif'));
      $file = $piramideUploader->getInfoFile();
      $file_name = $file['complete_name'];

      if (!(isset($upload) && $upload['uploaded'] == false)) {
        $result = array(
          'status' => 'success',
          'code' => 200,
          'message' => 'El archivo se ha subido con correctamente'
        );
      }
    }

    echo json_encode($result);
  });

  // Guardar un producto
  $app->post("/productos", function() use($app, $db) {
    $json = $app->request->post('json');
    $data = json_decode($json, true);

    if (!isset($data['nombre'])) {
      $data['nombre'] = null;
    }
    if (!isset($data['descripcion'])) {
      $data['descripcion'] = null;
    }
    if (!isset($data['precio'])) {
      $data['precio'] = null;
    }
    if (!isset($data['imagen'])) {
      $data['imagen'] = null;
    }

    $query = "INSERT INTO productos(nombre, descripcion, precio, imagen) VALUES(".
             "'{$data['nombre']}',".
             "'{$data['descripcion']}',".
             "'{$data['precio']}',".
             "'{$data['imagen']}'".
             ");";

    $insert = pg_query($db, $query);

    $result = array(
      'status' => 'error',
      'code' => 404,
      'message' => 'El producto no se ha creado correctamente'
    );

    if ($insert) {
      $result = array(
        'status' => 'success',
        'code' => 200,
        'message' => 'El producto se ha creado correctamente'
      );
    }

    echo json_encode($result);

    pg_close($db);
  });

  $app->run();
?>
