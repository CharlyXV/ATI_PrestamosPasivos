<?php
// Guardar como test-ldap.php en la carpeta public

// Mostrar todos los errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Prueba de conexión LDAP</h1>";

// Carga las variables de entorno desde el archivo .env
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

try {
    echo "<h2>Configuración LDAP:</h2>";
    echo "Host: " . (getenv('LDAP_HOST') ?: 'No configurado') . "<br>";
    echo "Puerto: " . (getenv('LDAP_PORT') ?: '389') . "<br>";
    echo "Base DN: " . (getenv('LDAP_BASE_DN') ?: 'No configurado') . "<br>";
    echo "Usuario: " . (getenv('LDAP_USERNAME') ?: 'No configurado') . "<br>";
    echo "Contraseña: " . (getenv('LDAP_PASSWORD') ? '*****' : 'No configurada') . "<br>";
    
    // Configurar la conexión LDAP
    $connection = new \LdapRecord\Connection([
        'hosts' => [getenv('LDAP_HOST')],
        'port' => getenv('LDAP_PORT', 389),
        'base_dn' => getenv('LDAP_BASE_DN'),
        'username' => getenv('LDAP_USERNAME'),
        'password' => getenv('LDAP_PASSWORD'),
        'timeout' => 5,
    ]);
    
    echo "<h2>Intentando conectar...</h2>";
    
    // Intentar conexión
    $connection->connect();
    
    if ($connection->isConnected()) {
        echo "<p style='color:green'>✓ Conexión exitosa al servidor LDAP</p>";
        
        echo "<h2>Intentando autenticación de administrador...</h2>";
        
        // Intentar autenticación con credenciales de admin
        if ($connection->auth()->attempt(getenv('LDAP_USERNAME'), getenv('LDAP_PASSWORD'))) {
            echo "<p style='color:green'>✓ Autenticación exitosa con credenciales de administrador</p>";
            
            echo "<h2>Prueba de búsqueda de usuarios:</h2>";
            
            // Contar usuarios
            $users = $connection->query()->where('objectClass', 'user')->get();
            echo "<p>Se encontraron " . count($users) . " usuarios en el directorio</p>";
            
            // Mostrar los primeros 5 usuarios
            echo "<p>Mostrando los primeros 5 usuarios:</p>";
            echo "<ul>";
            $count = 0;
            foreach ($users as $user) {
                if ($count >= 5) break;
                
                if (isset($user['cn'][0])) {
                    echo "<li>" . htmlspecialchars($user['cn'][0]) . 
                         " (samaccountname: " . (isset($user['samaccountname'][0]) ? htmlspecialchars($user['samaccountname'][0]) : 'No disponible') . ")" .
                         "</li>";
                    $count++;
                }
            }
            echo "</ul>";
        } else {
            echo "<p style='color:red'>✗ Fallo en la autenticación con credenciales de administrador</p>";
        }
    } else {
        echo "<p style='color:red'>✗ No se pudo conectar al servidor LDAP</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Traza del error:</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>