<?php
/*
Plugin Name: Boutons Flottants
Description: Plugin pour créer plusieurs boutons flottants personnalisables.
Version: 1.1
Author: Naël Gatat
*/

// Charge Font Awesome pour les icônes dans l'interface utilisateur
function boutons_flottants_enqueue_icons() {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
}
add_action('wp_enqueue_scripts', 'boutons_flottants_enqueue_icons');

// Crée une page de menu dans le tableau de bord pour la gestion des boutons
function boutons_flottants_create_menu() {
    add_menu_page(
        'Boutons Flottants', // Titre qui apparaîtra en haut de la page d’administration
        'Boutons Flottants', // Texte du menu dans le tableau de bord WordPress
        'manage_options',    // Droit d’accès requis pour voir ce menu (administrateurs)
        'boutons-flottants', // Identifiant unique de la page
        'boutons_flottants_settings_page', // Génère le contenu de la page
        'dashicons-admin-generic', // Icône associée au menu
        100 // Position dans le menu du tableau de bord
    );
}
add_action('admin_menu', 'boutons_flottants_create_menu');

// Affiche la page d’administration pour ajouter, modifier et supprimer les boutons
function boutons_flottants_settings_page() {
    // Récupère les boutons sauvegardés ou retourne un tableau vide s’il n’y en a pas
    $buttons = get_option('boutons_flottants', []);

    // Définit une liste d’icônes disponibles pour les boutons
    $icon_names = [
        'fas fa-home' => 'Maison',
        'fas fa-user' => 'Utilisateur',
        'fas fa-envelope' => 'Enveloppe',
        'fas fa-cog' => 'Paramètres',
        'fas fa-phone' => 'Téléphone',
        'fas fa-globe' => 'Globe',
        'fas fa-shopping-cart' => 'Panier',
        'fas fa-map-marker-alt' => 'Emplacement'
    ];

    // Définit les options de positionnement des boutons
    $position_names = [
        'bottom-right' => 'En bas à droite',
        'bottom-left' => 'En bas à gauche',
        'top-right' => 'En haut à droite',
        'top-left' => 'En haut à gauche'
    ];

    // Détermine si le mode "édition" est actif
    $edit_mode = false;
    $edit_index = -1;

    // Ajout ou modification d’un bouton en fonction des données du formulaire soumis
    if (isset($_POST['add_button'])) {
        $new_button = [
            'text' => sanitize_text_field($_POST['button_text']),
            'bg_color' => sanitize_hex_color($_POST['button_bg_color']),
            'text_color' => sanitize_hex_color($_POST['button_text_color']),
            'icon' => sanitize_text_field($_POST['button_icon']),
            'position' => sanitize_text_field($_POST['button_position']),
        ];

        // Mise à jour ou ajout d’un nouveau bouton selon l'index
        if (isset($_POST['edit_index']) && $_POST['edit_index'] !== '') {
            $edit_index = intval($_POST['edit_index']);
            $buttons[$edit_index] = $new_button;
        } else {
            $buttons[] = $new_button;
        }

        update_option('boutons_flottants', $buttons);
    }

    // Suppression d’un bouton
    if (isset($_POST['delete_button'])) {
        $index = intval($_POST['button_index']);
        unset($buttons[$index]);
        $buttons = array_values($buttons); // Réindexe le tableau après suppression
        update_option('boutons_flottants', $buttons);
    }

    // Active le mode édition pour un bouton spécifique
    if (isset($_POST['edit_button'])) {
        $edit_mode = true;
        $edit_index = intval($_POST['button_index']);
        $button_to_edit = $buttons[$edit_index];
    }
    ?>

    <div class="wrap">
        <h1>Gestion des Boutons Flottants</h1>
        
        <!-- Formulaire pour ajouter ou modifier un bouton -->
        <form method="post" class="boutons-flottants-form">
            <h2><?php echo $edit_mode ? 'Modifier le bouton' : 'Ajouter un bouton'; ?></h2>
            <input type="hidden" name="edit_index" value="<?php echo $edit_mode ? $edit_index : ''; ?>">
            
            <label>Texte : 
                <input type="text" name="button_text" value="<?php echo $edit_mode ? esc_attr($button_to_edit['text'] ?? '') : ''; ?>" required>
            </label>
            
            <label>Couleur de fond : 
                <input type="color" name="button_bg_color" value="<?php echo $edit_mode ? esc_attr($button_to_edit['bg_color'] ?? '#0073aa') : '#0073aa'; ?>" required>
            </label>
            
            <label>Couleur du texte : 
                <input type="color" name="button_text_color" value="<?php echo $edit_mode ? esc_attr($button_to_edit['text_color'] ?? '#ffffff') : '#ffffff'; ?>" required>
            </label>
            
            <label>Icône :
                <select name="button_icon">
                    <?php foreach ($icon_names as $class => $name): ?>
                        <option value="<?php echo esc_attr($class); ?>" <?php echo $edit_mode && ($button_to_edit['icon'] ?? '') == $class ? 'selected' : ''; ?>><?php echo esc_html($name); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            
            <label>Position : 
                <select name="button_position">
                    <?php foreach ($position_names as $position => $name): ?>
                        <option value="<?php echo esc_attr($position); ?>" <?php echo $edit_mode && ($button_to_edit['position'] ?? '') == $position ? 'selected' : ''; ?>><?php echo esc_html($name); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            
            <button type="submit" name="add_button" class="button button-primary">
                <?php echo $edit_mode ? 'Enregistrer les modifications' : 'Ajouter le bouton'; ?>
            </button>
            
            <?php if ($edit_mode): ?>
                <a href="?page=boutons-flottants" class="button">Annuler</a>
            <?php endif; ?>
        </form>
        
        <!-- Liste des boutons existants avec options de modification ou suppression -->
        <h2>Boutons Actuels</h2>
        <?php if (!empty($buttons)): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Texte</th>
                        <th>Couleur de fond</th>
                        <th>Couleur du texte</th>
                        <th>Icône</th>
                        <th>Position</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($buttons as $index => $button): ?>
                        <tr>
                            <td><?php echo esc_html($button['text'] ?? ''); ?></td>
                            <td style="background-color: <?php echo esc_attr($button['bg_color'] ?? '#0073aa'); ?>; width: 50px;">&nbsp;</td>
                            <td style="background-color: <?php echo esc_attr($button['text_color'] ?? '#ffffff'); ?>; width: 50px;">&nbsp;</td>
                            <td><?php echo esc_html($icon_names[$button['icon'] ?? ''] ?? ''); ?></td>
                            <td><?php echo esc_html($position_names[$button['position'] ?? ''] ?? ''); ?></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="button_index" value="<?php echo $index; ?>">
                                    <button type="submit" name="edit_button" class="button button-secondary">Modifier</button>
                                    <button type="submit" name="delete_button" class="button button-secondary">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Aucun bouton ajouté.</p>
        <?php endif; ?>
    </div>
    <?php
}

// Affiche les boutons flottants sur le site côté utilisateur
function boutons_flottants_display_buttons() {
    $buttons = get_option('boutons_flottants', []);
    
    // Groupe les boutons par position pour un meilleur positionnement
    $grouped_buttons = [];
    foreach ($buttons as $button) {
        $grouped_buttons[$button['position']][] = $button;
    }
    
    // Crée un décalage pour éviter la superposition des boutons qui rendait le tout illisible
    foreach ($grouped_buttons as $position => $buttons) {
        $offset = 0;

        foreach ($buttons as $button) {
            $position_style = '';
            
            // Applique le style de positionnement en fonction de la position
            switch ($position) {
                case 'bottom-right':
                    $position_style = 'bottom:' . (20 + $offset) . 'px; right:20px;';
                    break;
                case 'bottom-left':
                    $position_style = 'bottom:' . (20 + $offset) . 'px; left:20px;';
                    break;
                case 'top-right':
                    $position_style = 'top:' . (20 + $offset) . 'px; right:20px;';
                    break;
                case 'top-left':
                    $position_style = 'top:' . (20 + $offset) . 'px; left:20px;';
                    break;
            }

            // Génère le HTML pour chaque bouton, avec icône et texte
            echo '<div class="boutons-flottants-button" style="position:fixed; ' . $position_style . ' background-color:' . esc_attr($button['bg_color'] ?? '#0073aa') . '; color:' . esc_attr($button['text_color'] ?? '#ffffff') . '; padding:10px; border-radius:5px; cursor:pointer; display:flex; align-items:center;">
                <i class="' . esc_attr($button['icon'] ?? '') . '"></i>&nbsp;' . esc_html($button['text'] ?? '') . '
            </div>';
            
            // Ajoute un espace entre chaque bouton
            $offset += 60;
        }
    }
}
add_action('wp_footer', 'boutons_flottants_display_buttons');
?>
