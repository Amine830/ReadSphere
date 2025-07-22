<?php

/**
 * Tableau de bord
 * 
 * Cette page permet de visualiser les statistiques et les livres de l'utilisateur.
 */

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
require_once 'includes/init.php';

// Vérifier que l'utilisateur est connecté
require_auth();

$user = current_user(); 
$is_admin = is_admin();
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 8;

// Récupérer les livres de l'utilisateur avec pagination
try {
    $offset = ($page - 1) * $per_page;
    
    if ($is_admin) {
        // Pour les administrateurs, afficher tous les livres
        $stmt = $pdo->query("SELECT * FROM books ORDER BY created_at DESC LIMIT $per_page OFFSET $offset");
        $books = $stmt->fetchAll();
        $total_books = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
    } else {
        // Pour les utilisateurs normaux, afficher uniquement leurs livres
        $stmt = $pdo->prepare("SELECT * FROM books WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $user['id'], PDO::PARAM_INT);
        $stmt->bindValue(2, $per_page, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $books = $stmt->fetchAll();
        $total_books = $pdo->query("SELECT COUNT(*) FROM books WHERE user_id = " . $user['id'])->fetchColumn();
    }
    
    $total_pages = ceil($total_books / $per_page);
    
    // Récupérer les statistiques pour les administrateurs
    $stats = [];
    if ($is_admin) {
        $stats['total_users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats['active_users'] = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
        $stats['verified_users'] = $pdo->query("SELECT COUNT(*) FROM users WHERE email_verified_at IS NOT NULL")->fetchColumn();
        $stats['total_books'] = $total_books;
        // Compter uniquement les commentaires non supprimés
        $stats['total_comments'] = $pdo->query("SELECT COUNT(*) FROM comments WHERE is_deleted = 0 AND is_admin_deleted = 0")->fetchColumn();
        $stats['deleted_comments'] = $pdo->query("SELECT COUNT(*) FROM comments WHERE is_deleted = 1 OR is_admin_deleted = 1")->fetchColumn();
        
        // Statistiques sur les signalements
        $stats['pending_reports'] = $pdo->query("SELECT COUNT(*) FROM comment_reports WHERE status = 'pending'")->fetchColumn();
        $stats['resolved_reports'] = $pdo->query("SELECT COUNT(*) FROM comment_reports WHERE status = 'resolved'")->fetchColumn();
        $stats['rejected_reports'] = $pdo->query("SELECT COUNT(*) FROM comment_reports WHERE status = 'rejected'")->fetchColumn();
    }
    
} catch (PDOException $e) {
    error_log('Erreur lors de la récupération des données : ' . $e->getMessage());
    $books = [];
    $total_pages = 0;
    $stats = [];
}

$page_title = $is_admin ? 'Tableau de bord administrateur' : 'Mon espace';
require_once 'templates/header.php';
?>

<div class="mb-8">
    <?php if ($is_admin && !empty($stats)): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Utilisateurs -->
        <div class="bg-white p-4 rounded-lg shadow-md border-l-4 border-blue-500">
            <div class="flex justify-between items-start">
                <div>
                    <div class="text-gray-500 text-sm font-medium">Utilisateurs</div>
                    <div class="text-2xl font-bold"><?= $stats['total_users'] ?></div>
                    <div class="text-sm text-gray-500">dont <?= $stats['active_users'] ?> actifs</div>
                </div>
                <div class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded-full">
                    <?= $stats['verified_users'] > 0 ? round(($stats['verified_users'] / $stats['total_users']) * 100) : 0 ?>% vérifiés
                </div>
            </div>
        </div>

        <!-- Livres -->
        <div class="bg-white p-4 rounded-lg shadow-md border-l-4 border-yellow-500">
            <div class="text-gray-500 text-sm font-medium">Livres</div>
            <div class="text-2xl font-bold"><?= $stats['total_books'] ?></div>
            <div class="text-sm text-gray-500">
                <?php 
                $new_books = $pdo->query("SELECT COUNT(*) FROM books WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
                echo $new_books > 0 ? "+$new_books cette semaine" : "Aucun nouveau cette semaine";
                ?>
            </div>
        </div>

        <!-- Commentaires -->
        <div class="bg-white p-4 rounded-lg shadow-md border-l-4 border-purple-500">
            <div class="flex justify-between">
                <div>
                    <div class="text-gray-500 text-sm font-medium">Commentaires</div>
                    <div class="text-2xl font-bold"><?= $stats['total_comments'] ?></div>
                </div>
                <?php if ($stats['deleted_comments'] > 0): ?>
                <div class="text-right">
                    <div class="text-xs text-red-600"><?= $stats['deleted_comments'] ?> supprimés</div>
                </div>
                <?php endif; ?>
            </div>
            <div class="text-sm text-gray-500 mt-1">
                <?php 
                $new_comments = $pdo->query("SELECT COUNT(*) FROM comments WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND is_deleted = 0 AND is_admin_deleted = 0")->fetchColumn();
                echo $new_comments > 0 ? "+$new_comments cette semaine" : "Aucun nouveau cette semaine";
                ?>
            </div>
        </div>

        <!-- Signalements -->
        <div class="bg-white p-4 rounded-lg shadow-md border-l-4 border-red-500">
            <div class="flex justify-between items-center mb-1">
                <div class="text-gray-500 text-sm font-medium">Signalements</div>
                <div class="flex space-x-1">
                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                        <?= $stats['pending_reports'] ?> en attente
                    </span>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2 mt-2">
                <div class="text-center p-2 bg-green-50 rounded">
                    <div class="text-green-600 font-bold"><?= $stats['resolved_reports'] ?></div>
                    <div class="text-xs text-green-500">Résolus</div>
                </div>
                <div class="text-center p-2 bg-gray-100 rounded">
                    <div class="text-gray-600 font-bold"><?= $stats['rejected_reports'] ?></div>
                    <div class="text-xs text-gray-500">Rejetés</div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold"><?= $is_admin ? 'Tous les livres' : 'Mes livres' ?></h1>
        <a href="add_book.php" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center">
            <i class="fas fa-plus mr-2"></i> Ajouter un livre
        </a>
    </div>
    
    <?php if (empty($books)): ?>
        <div class="bg-white p-8 rounded-lg shadow-md text-center">
            <div class="text-gray-400 mb-4">
                <i class="fas fa-book-open text-5xl"></i>
            </div>
            <h2 class="text-xl font-semibold mb-2">Aucun livre ajouté</h2>
            <p class="text-gray-600 mb-4">Commencez par ajouter votre premier livre pour partager vos lectures.</p>
            <a href="add_book.php" class="inline-block bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                Ajouter mon premier livre
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($books as $book): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300 flex flex-col h-full">
                    <div class="h-48 bg-gray-200 overflow-hidden">
                        <?php if (!empty($book['image'])): ?>
                            <img src="uploads/<?= htmlspecialchars($book['image']) ?>" alt="<?= htmlspecialchars($book['title']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                <i class="fas fa-book text-5xl"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-4 flex flex-col flex-grow">
                        <h3 class="font-semibold text-lg mb-1">
                            <a href="book.php?id=<?= $book['id'] ?>" class="hover:text-blue-600 transition-colors">
                                <?= htmlspecialchars($book['title']) ?>
                            </a>
                        </h3>
                        <p class="text-gray-600 text-sm mb-2">
                            <i class="fas fa-user-edit mr-1"></i> <?= htmlspecialchars($book['author']) ?>
                            <?php if ($is_admin): ?>
                                <span class="block text-xs text-gray-500 mt-1">
                                    Ajouté par <?= htmlspecialchars(get_user_by_id($book['user_id'])['username'] ?? 'Utilisateur inconnu') ?>
                                </span>
                            <?php endif; ?>
                        </p>
                        <?php if (!empty($book['genre'])): ?>
                            <span class="inline-block bg-gray-200 rounded-full px-3 py-1 text-xs font-semibold text-gray-700 mb-3">
                                <?= htmlspecialchars($book['genre']) ?>
                            </span>
                        <?php endif; ?>
                        <p class="text-gray-700 mb-4 line-clamp-3 flex-grow text-sm">
                            <?= !empty($book['summary']) ? nl2br(htmlspecialchars(mb_substr($book['summary'], 0, 120) . (mb_strlen($book['summary']) > 120 ? '...' : ''))) : 'Aucun résumé fourni.' ?>
                        </p>
                        <div class="flex justify-between items-center mt-auto pt-3 border-t border-gray-100">
                            <span class="text-sm text-gray-500">
                                <i class="far fa-calendar-alt mr-1"></i> <?= date('d/m/Y', strtotime($book['created_at'])) ?>
                            </span>
                            <div class="flex space-x-2">
                                <a href="edit_book.php?id=<?= $book['id'] ?>" class="text-blue-600 hover:text-blue-800" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="#" onclick="confirmDelete(<?= $book['id'] ?>)" class="text-red-600 hover:text-red-800" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="mt-8 flex justify-center">
                <nav class="inline-flex rounded-md shadow">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>" class="px-3 py-2 rounded-l-md border border-gray-300 bg-white text-gray-500 hover:bg-gray-50">
                            Précédent
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>" class="px-3 py-2 border-t border-b border-gray-300 <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-white text-gray-500 hover:bg-gray-50' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?>" class="px-3 py-2 rounded-r-md border border-gray-300 bg-white text-gray-500 hover:bg-gray-50">
                            Suivant
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
function confirmDelete(bookId) {
    Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: "Vous ne pourrez pas revenir en arrière !",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Oui, supprimer !',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `delete_book.php?id=${bookId}`;
        }
    });
}
</script>

<?php require_once 'templates/footer.php'; ?>
