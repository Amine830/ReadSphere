<?php
/**
 * Fichier d'initialisation principal
 * Doit être inclus en premier dans chaque page
 */

// 1. Définir le chemin racine si non défini
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// 2. Définir le chemin de base si non défini
if (!defined('BASE_PATH')) {
    $base_path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    define('BASE_PATH', $base_path);
}

// 3. Activer l'affichage des erreurs en fonction de l'environnement
$is_dev = (getenv('APP_ENV') === 'development' || in_array($_SERVER['SERVER_ADDR'] ?? '', ['127.0.0.1', '::1']));

if ($is_dev) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
}

// 4. Définir le fuseau horaire
date_default_timezone_set('Europe/Paris');

// 5. Inclure la configuration des chemins
require_once __DIR__ . '/config/paths.php';

// 6. Créer les répertoires nécessaires s'ils n'existent pas
$requiredDirs = [
    UPLOADS_PATH,
    AVATARS_PATH,
    THUMBNAILS_PATH,
    CACHE_PATH,
    IMAGE_CACHE_PATH,
    VIEW_CACHE_PATH
];

foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// 7. Charger les variables d'environnement
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
}

// 8. Inclure les chemins
$paths_file = __DIR__ . '/config/paths.php';
if (!file_exists($paths_file)) {
    die("ERREUR: Le fichier de configuration des chemins est introuvable ($paths_file)");
}
require_once $paths_file;

// 9. Vérifier que les constantes essentielles sont définies
$requiredConstants = [
    'INCLUDE_PATH', 'TEMPLATES_PATH', 'UPLOADS_PATH', 'CACHE_PATH',
    'AVATARS_PATH', 'AVATARS_URL', 'THUMBNAILS_PATH', 'THUMBNAILS_URL',
    'DEFAULT_AVATAR_PATH', 'DEFAULT_AVATAR_URL', 'BASE_URL'
];

foreach ($requiredConstants as $constant) {
    if (!defined($constant)) {
        die("ERREUR: La constante $constant n'est pas définie dans config/paths.php");
    }
}

// 10. Créer les répertoires nécessaires
$requiredDirs = [
    AVATARS_PATH,
    THUMBNAILS_PATH,
    CACHE_PATH . '/images',
    CACHE_PATH . '/views',
    UPLOADS_PATH . '/books',
    UPLOADS_PATH . '/covers'
];

foreach ($requiredDirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}


// 11. Configurer et démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    // Inclure la configuration de session
    require_once __DIR__ . '/config.php';
    
    // Configurer la session
    if (function_exists('configure_session')) {
        configure_session();
    }
    
    // Démarrer la session
    session_start();
}

// 12. Inclure les classes utilitaires
spl_autoload_register(function ($class) {
    $classFile = __DIR__ . '/utils/' . $class . '.php';
    if (file_exists($classFile)) {
        require_once $classFile;
    }
});
require_once __DIR__ . '/config.php';

// 13. Vérifier les constantes requises
if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER')) {
    die('ERREUR: Configuration de la base de données incomplète');
}

// 14. Configurer la session
if (function_exists('configure_session')) {
    configure_session();
}


// 15. Inclure les dépendances principales
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/user_operations.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/book_operations.php';
require_once __DIR__ . '/comment_operations.php';
require_once __DIR__ . '/error_handler.php';
require_once __DIR__ . '/moderation_logs.php';