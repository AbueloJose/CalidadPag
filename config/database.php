<?php

class Database {
    
    // Configuración de la conexión
    private $host = "localhost";        
    private $db_name = "bdInscripcion"; 
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
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            echo "Conexion establecida"
        } catch(PDOException $exception) {
            // Si algo sale mal, mostramos el error
            echo "Error de Conexión: " . $exception->getMessage();
        }

        // Devolvemos el objeto de conexión
        
        return $this->conn;
    }
}
?>