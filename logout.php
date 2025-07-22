<?php

/**
 * Page de déconnexion
 */

require_once 'includes/init.php';

// Déconnecter l'utilisateur
logout_user();

// Rediriger vers la page de connexion
set_flash('Vous avez été déconnecté avec succès.', 'info');
redirect('login.php');