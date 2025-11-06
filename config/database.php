<?php
/**
 * Clase para la conexión a la base de datos usando PDO.
 * Este archivo centraliza la configuración y el método de conexión
 * para que cualquier otro script de PHP pueda reutilizarlo fácilmente.
 */
class Database {
    
    // Configuración de la conexión
    private $host = "localhost";      
    private $db_name = "bdInscripcion"; // Tu nombre de BD
    private $username = "root";       
    private $password = "";
    private $charset = "utf8mb4";
    
    // Variable para almacenar la conexión
    public $conn;

    /**
     * Obtiene la conexión a la base de datos.
     * @return PDO|null Retorna el objeto de conexión PDO o null si falla.
     */
    public function connect() {
        // Limpiamos cualquier conexión previa
        $this->conn = null;
        
        // DSN (Data Source Name): Cadena de conexión para PDO
        $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
        
        // Opciones de PDO para un mejor manejo de errores
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,     
            PDO::ATTR_EMULATE_PREPARES   => false,                  
        ];

        try {
            // Intentamos crear la instancia de PDO
            
            // --- ESTA ES LA LÍNEA 37 ---
            // Asegúrate de que termine con un punto y coma ";"
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $exception) { // <-- Esta es la línea 39 (o 38 si quitas espacios)
            // Si algo sale mal, mostramos el error
            echo "Error de Conexión: " . $exception->getMessage();
        }

        // Devolvemos el objeto de conexión
        return $this->conn;
    }
}
?>