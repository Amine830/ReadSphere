<?php

/**
 * Page d'inscription
 */

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

require_once 'includes/init.php';

// Initialisation des variables
$errors = [];
$form_data = [
    'username' => '',
    'email' => ''
];

// Vérification si l'utilisateur est déjà connecté
if (is_logged_in()) {
    redirect('index.php');
}

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = [
        'username' => trim($_POST['username'] ?? ''),
        'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
        'password' => $_POST['password'] ?? '',
        'password_confirm' => $_POST['password_confirm'] ?? ''
    ];
    
    // Validation des données
    if (empty($form_data['username'])) {
        $errors['username'] = 'Le nom d\'utilisateur est requis';
    } elseif (strlen($form_data['username']) < 3) {
        $errors['username'] = 'Le nom d\'utilisateur doit contenir au moins 3 caractères';
    } elseif (username_exists($form_data['username'])) {
        $errors['username'] = 'Ce nom d\'utilisateur est déjà pris';
    }
    
    if (empty($form_data['email'])) {
        $errors['email'] = 'L\'adresse email est requise';
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'L\'adresse email n\'est pas valide';
    } elseif (email_exists($form_data['email'])) {
        $errors['email'] = 'Cette adresse email est déjà utilisée';
    }
    
    if (empty($form_data['password'])) {
        $errors['password'] = 'Le mot de passe est requis';
    } elseif (strlen($form_data['password']) < 8) {
        $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères';
    } elseif ($form_data['password'] !== $form_data['password_confirm']) {
        $errors['password_confirm'] = 'Les mots de passe ne correspondent pas';
    }
    
    // Si pas d'erreurs, création du compte
    if (empty($errors)) {
        $user_id = create_user($form_data);
        
        if ($user_id) {
            // Connexion automatique après inscription
            if (authenticate_user($form_data['email'], $form_data['password'])) {
                set_flash('Votre compte a été créé avec succès ! Un email de vérification a été envoyé à votre adresse email.', 'success');
                redirect('index.php');
            }
        } else {
            $errors['general'] = 'Une erreur est survenue lors de la création du compte. Veuillez réessayer.';
        }
    }
}

// Affichage du formulaire
$page_title = 'Inscription';
require_once 'templates/header.php';
?>

<div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-6">Créer un compte</h1>
    
    <?php if (!empty($errors['general'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($errors['general']) ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="signup.php" class="space-y-4">
        <div>
            <label for="username" class="block text-sm font-medium text-gray-700">Nom d'utilisateur</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($form_data['username']) ?>" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
            <?php if (!empty($errors['username'])): ?>
                <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['username']) ?></p>
            <?php endif; ?>
        </div>
        
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Adresse email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($form_data['email']) ?>" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
            <?php if (!empty($errors['email'])): ?>
                <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['email']) ?></p>
            <?php endif; ?>
        </div>
        
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
            <input type="password" id="password" name="password" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
            <?php if (!empty($errors['password'])): ?>
                <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['password']) ?></p>
            <?php endif; ?>
        </div>
        
        <div>
            <label for="password_confirm" class="block text-sm font-medium text-gray-700">Confirmer le mot de passe</label>
            <input type="password" id="password_confirm" name="password_confirm" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
            <?php if (!empty($errors['password_confirm'])): ?>
                <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['password_confirm']) ?></p>
            <?php endif; ?>
        </div>
        
        <div>
            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                S'inscrire
            </button>
        </div>
    </form>
    
    <div class="mt-4 text-center text-sm text-gray-600">
        Déjà inscrit ? 
        <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500">Se connecter</a>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>