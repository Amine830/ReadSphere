<?php
/**
 * Gestion de la connexion à la base de données
 * 
 * Ce fichier gère la connexion à la base de données MySQL en utilisant PDO
 * et fournit des fonctions utilitaires pour les requêtes SQL.
 * 
 * @version 2.0
 */

// Vérifier que les constantes nécessaires sont définies
if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER')) {
    throw new RuntimeException('La configuration de la base de données est incomplète');
}

// Configuration PDO
$dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_PERSISTENT         => false,
];

// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Tentative de connexion à la base de données
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    // Définir le fuseau horaire de la base de données
    $pdo->exec("SET time_zone = '+00:00'");
    
} catch (PDOException $e) {
    // Afficher l'erreur complète pour le débogage
    echo "<pre>\n";
    echo "ERREUR DE CONNEXION À LA BASE DE DONNÉES\n";
    echo "Message d'erreur: " . $e->getMessage() . "\n\n";
    echo "Détails de connexion utilisés :\n";
    echo "DSN: $dsn\n";
    echo "Utilisateur: " . DB_USER . "\n";
    echo "Mot de passe: " . (DB_PASS ? '*** (défini)' : 'vide') . "\n";
    echo "</pre>";
    
    // Journalisation de l'erreur
    error_log('Erreur de connexion à la base de données : ' . $e->getMessage());
    
    // Arrêter l'exécution avec le message d'erreur
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}

/**
 * Redirige vers une URL spécifiée
 * 
 * @param string $path Chemin vers lequel rediriger (peut être absolu ou relatif)
 * @param int $statusCode Code HTTP pour la redirection (par défaut : 302)
 * @return void
 */
function redirect(string $path, int $statusCode = 302): void {
    // Si le chemin commence par http:// ou https://, c'est une URL absolue
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        $url = $path;
    }
    // Si le chemin commence par /, c'est un chemin absolu par rapport à la racine du site
    elseif (strpos($path, '/') === 0) {
        $url = $path;
    }
    // Sinon, c'est un chemin relatif, on ajoute BASE_PATH
    else {
        $url = rtrim(BASE_PATH, '/') . '/' . ltrim($path, '/');
    }
    
    header("Location: $url", true, $statusCode);
    exit();
}

/**
 * Nettoie les données utilisateur pour prévenir les attaques XSS
 * 
 * @param mixed $data Données à nettoyer
 * @param bool $stripTags Si vrai, supprime les balises HTML
 * @return mixed Données nettoyées
 */
function sanitize(mixed $data, bool $stripTags = true): mixed {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    
    $data = trim($data);
    if ($stripTags) {
        $data = strip_tags($data);
    }
    return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Vérifie si une requête est de type AJAX
 * 
 * @return bool True si la requête est AJAX, false sinon
 */
function is_ajax_request(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Termine l'exécution du script avec une réponse JSON
 * 
 * @param mixed $data Données à encoder en JSON
 * @param int $statusCode Code HTTP de la réponse
 * @return void
 */
function json_response(mixed $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

/**
 * Valide une adresse email
 * 
 * @param string $email Adresse email à valider
 * @return bool True si l'email est valide, false sinon
 */
function is_valid_email(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}


// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => $_ENV['SESSION_LIFETIME'] ?? 1440,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? null,
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    session_name($_ENV['SESSION_NAME'] ?? 'bookreview_session');
    session_start();
}