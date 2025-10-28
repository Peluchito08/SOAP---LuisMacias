<?php
class ProductoService {
    private $pdo;
    public function __construct() {
        $dsn = 'pgsql:host=localhost;dbname=ws_crud_db;port=5432'; 
        $user = 'practicas'; 
        $password ='123';
          try {
            $this->pdo = new PDO($dsn, $user, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // Error amigable en caso de fallo de conexión a DB
            throw new SoapFault('Server', 'Error de conexión a la DB: ' . $e->getMessage());
        }
    }
    
    public function crearProducto($nombre, $precio, $stock) {
        $sql = "INSERT INTO productos (nombre, precio, stock) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nombre, $precio, $stock]);
        return "Producto creado con exito.";
    }
    // R: Read (Leer)
    // Devuelve los detalles del producto en XML
    public function leerProducto($id) {
        $sql = "SELECT nombre, precio, stock FROM productos WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $producto ? $producto : ["error" => "Producto ID $id no encontrado."];
    }

    public function listarProductos() {
    $sql = "SELECT id, nombre, precio, stock FROM productos ORDER BY id";
    $stmt = $this->pdo->query($sql);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Si no hay registros, devolver mensaje
    if (!$productos) {
        return ["mensaje" => "No hay productos registrados."];
    }

    return $productos;
}

    // U: Update (Actualizar)
    public function actualizarProducto($id, $nombre, $precio, $stock) {
        $sql = "UPDATE productos SET nombre = ?, precio = ?, stock = ? WHERE id = ? RETURNING id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nombre, $precio, $stock, $id]);
        
        return $stmt->rowCount() > 0 ? "Producto ID $id actualizado." : "Producto ID $id no existe.";
    }
    // D: Delete (Eliminar)
    public function eliminarProducto($id) {
        $sql = "DELETE FROM productos WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        
        return $stmt->rowCount() > 0 ? "Producto ID $id eliminado." : "Producto ID $id no existe para eliminar.";
    }
}
// Servidor SOAP: Exponer la clase
$options = array(
    // URI única para identificar el servicio
    'uri' => 'http://localhost/Practica_SOAP/servicioproductos.php' 
);
try {
    $server = new SoapServer(null, $options); 
    $server->setClass('ProductoService');
    $server->handle(); 
} catch (SoapFault $f) {
    echo $f->faultstring;
}
    