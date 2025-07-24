<?php
/**
 * Page d'accueil - Affiche la liste des livres avec pagination
 */

// Inclure le fichier d'initialisation
require_once __DIR__ . '/includes/init.php';

// Vérifier si c'est une requête pour une URL personnalisée (gestion des 404)
if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] !== '/ReadSphere/' && $_SERVER['REQUEST_URI'] !== '/ReadSphere/index.php') {
    // Si c'est une requête pour un fichier qui n'existe pas
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $script_name = dirname($_SERVER['SCRIPT_NAME']);
    
    // Nettoyer l'URI
    $clean_uri = '/' . trim(str_replace($script_name, '', $request_uri), '/');
    
    // Liste des dossiers et fichiers autorisés
    $allowed_paths = [
        '/book.php',
        '/login.php',
        '/register.php',
        '/dashboard.php',
        '/add_book.php',
        '/edit_book.php',
        '/profile.php',
        '/search.php',
        '/api/'
    ];
    
    // Vérifier si l'URI correspond à un fichier ou dossier existant
    $is_valid_route = false;
    foreach ($allowed_paths as $path) {
        if (strpos($clean_uri, $path) === 0) {
            $is_valid_route = true;
            break;
        }
    }
    
    // Si ce n'est pas une route valide et que le fichier n'existe pas
    if (!$is_valid_route && !file_exists(__DIR__ . $clean_uri) && !is_dir(__DIR__ . $clean_uri)) {
        // Log l'erreur 404
        error_log("Page non trouvée : " . $clean_uri);
        
        // Afficher la page d'erreur 404
        http_response_code(404);
        include __DIR__ . '/includes/error_pages/404.php';
        exit;
    }
}

    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $per_page = 6;
    
    // Récupérer les livres avec pagination
    $books_data = get_books($page, $per_page);
    $books = $books_data['books'];
    $pagination = $books_data['pagination'];

    $page_title = 'Découvrez les derniers livres ajoutés';
    require_once 'templates/header.php';
?>

<div class="mb-8">
    <h1 class="text-3xl font-bold mb-6">Derniers livres ajoutés</h1>
    
    <?php if (empty($books)): ?>
        <div class="bg-white p-6 rounded-lg shadow-md text-center">
            <p class="text-gray-600">Aucun livre n'a été ajouté pour le moment.</p>
            <?php if (is_logged_in()): ?>
                <a href="add_book.php" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Ajouter le premier livre
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($books as $book): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="h-48 bg-gray-200 overflow-hidden">
                        <?php if (!empty($book['image'])): ?>
                            <img src="uploads/<?= htmlspecialchars($book['image']) ?>" alt="<?= htmlspecialchars($book['title']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                <i class="fas fa-book text-5xl"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-4">
                        <h2 class="text-xl font-semibold mb-2">
                            <a href="book.php?id=<?= $book['id'] ?>" class="hover:text-blue-600 transition-colors">
                                <?= htmlspecialchars($book['title']) ?>
                            </a>
                        </h2>
                        <?php if (!empty($book['author'])): ?>
                            <p class="text-gray-600 mb-2">
                                <i class="fas fa-user-edit mr-1"></i> <?= htmlspecialchars($book['author']) ?>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($book['genre'])): ?>
                            <span class="inline-block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2 mb-2">
                                <?= htmlspecialchars($book['genre']) ?>
                            </span>
                        <?php endif; ?>
                        <p class="text-gray-700 mb-3 line-clamp-3">
                            <?= !empty($book['summary']) ? nl2br(htmlspecialchars($book['summary'])) : 'Aucun résumé fourni.' ?>
                        </p>
                        <div class="flex justify-between items-center text-sm text-gray-500">
                            <span><i class="far fa-user mr-1"></i> <?= htmlspecialchars($book['author_username']) ?></span>
                            <span><i class="far fa-calendar-alt mr-1"></i> <?= date('d/m/Y', strtotime($book['created_at'])) ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
            <div class="mt-8 flex justify-center">
                <nav class="flex items-center space-x-1">
                    <?php if ($pagination['current_page'] > 1): ?>
                        <a href="?page=<?= $pagination['current_page'] - 1 ?>" class="px-3 py-2 rounded-l-md border border-gray-300 bg-white text-gray-500 hover:bg-gray-50">
                            Précédent
                        </a>
                    <?php endif; ?>
                    
                    <?php 
                    // Afficher un nombre limité de pages autour de la page courante
                    $start = max(1, $pagination['current_page'] - 2);
                    $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
                    
                    if ($start > 1) {
                        echo '<a href="?page=1" class="px-3 py-2 border-t border-b border-gray-300 bg-white text-gray-500 hover:bg-gray-50">1</a>';
                        if ($start > 2) echo '<span class="px-3 py-2">...</span>';
                    }
                    
                    for ($i = $start; $i <= $end; $i++): ?>
                        <a href="?page=<?= $i ?>" class="px-3 py-2 border-t border-b border-gray-300 <?= $i == $pagination['current_page'] ? 'bg-blue-600 text-white' : 'bg-white text-gray-500 hover:bg-gray-50' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; 
                    
                    if ($end < $pagination['total_pages']) {
                        if ($end < $pagination['total_pages'] - 1) echo '<span class="px-3 py-2">...</span>';
                        echo '<a href="?page=' . $pagination['total_pages'] . '" class="px-3 py-2 border-t border-b border-gray-300 bg-white text-gray-500 hover:bg-gray-50">' . $pagination['total_pages'] . '</a>';
                    }
                    ?>
                    
                    <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                        <a href="?page=<?= $pagination['current_page'] + 1 ?>" class="px-3 py-2 rounded-r-md border border-gray-300 bg-white text-gray-500 hover:bg-gray-50">
                            Suivant
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once 'templates/footer.php'; ?>
