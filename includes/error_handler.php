<?php
/**
 * Gestionnaire d'erreurs personnalisé
 * 
 * Ce fichier configure la gestion des erreurs et des exceptions pour l'application.
 */

// Inclure les fonctions nécessaires
require_once __DIR__ . '/functions.php';

// Niveaux d'erreur à afficher selon l'environnement
$is_dev = ($_ENV['APP_ENV'] ?? 'production') === 'development';

if ($is_dev) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
}

// Définir le gestionnaire d'exceptions personnalisé
set_exception_handler(function (Throwable $e) {
    error_log('Exception non capturée : ' . $e->getMessage() . ' dans ' . $e->getFile() . ' à la ligne ' . $e->getLine());
    
    // Nettoyer tout le tampon de sortie
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    if (!headers_sent()) {
        http_response_code(500);
    }
    
    if (is_ajax_request()) {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo json_encode([
            'error' => true,
            'message' => 'Une erreur est survenue. Veuillez réessayer plus tard.'
        ]);
    } else {
        if ($GLOBALS['is_dev'] ?? false) {
            echo '<h1>Erreur</h1>';
            echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p><strong>Fichier:</strong> ' . htmlspecialchars($e->getFile()) . ' à la ligne ' . $e->getLine() . '</p>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        } else {
            echo '<h1>Erreur 500</h1>';
            echo '<p>Une erreur est survenue. Veuillez réessayer plus tard.</p>';
        }
    }
    
    exit(1);
});

// Gestion des erreurs fatales
register_shutdown_function(function() {
    $error = error_get_last();
    
    if ($error !== null && ($error['type'] & (E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR))) {
        error_log(sprintf(
            'Erreur fatale : %s dans %s à la ligne %d',
            $error['message'],
            $error['file'],
            $error['line']
        ));
        
        if (!headers_sent()) {
            http_response_code(500);
        }
        
        if (!is_ajax_request() && ($GLOBALS['is_dev'] ?? false)) {
            echo '<h1>Erreur fatale</h1>';
            echo '<p><strong>Message:</strong> ' . htmlspecialchars($error['message']) . '</p>';
            echo '<p><strong>Fichier:</strong> ' . htmlspecialchars($error['file']) . ' à la ligne ' . $error['line'] . '</p>';
        }
    }
});

// Convertit les erreurs en exceptions
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // Ce code d'erreur n'est pas inclus dans error_reporting
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});