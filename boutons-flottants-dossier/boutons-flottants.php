<?php
/*
Plugin Name: Boutons Flottants
Description: Plugin pour créer plusieurs boutons flottants personnalisables.
Version: 1.0
Author: Naël Gatat
*/

// Fonction qui charge les icônes dans l'interface utilisateur
function boutons_flottants_enqueue_icons() {
    wp_enqueue_style('dashicons');
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
}
add_action('wp_enqueue_scripts', 'boutons_flottants_enqueue_icons');

// Fonction qui crée un menu dans le tableau de bord admin pour gérer les boutons flottants
function boutons_flottants_create_menu() {
    add_menu_page(
        'Boutons Flottants',  // Titre affiché en haut de la page 
        'Boutons Flottants',  // Texte du menu dans la barre de navigation du tableau de bord
        'manage_options',     // Permissions nécessaires pour accéder à cette page (ici, être connecté en tant qu'admin)
        'boutons-flottants',  // Identifiant unique pour la page
        'boutons_flottants_settings_page', // Fonction qui génère le contenu de la page d'administration
        'dashicons-admin-generic', // Icône pour le menu
        100 // Position du menu dans le tableau de bord
    );
}
add_action('admin_menu', 'boutons_flottants_create_menu');

// Fonction qui génère la page admin pour ajouter, modifier et supprimer les boutons
function boutons_flottants_settings_page() {
    // Récupère les boutons enregistrés, sinon renvoie un tableau vide
    $buttons = get_option('boutons_flottants', []);
    
    // Liste des icônes, avec des noms clairs
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

    // Différentes options de position pour les boutons, selectionnable par l'admin
    $position_names = [
        'bottom-right' => 'En bas à droite',
        'bottom-left' => 'En bas à gauche',
        'top-right' => 'En haut à droite',
        'top-left' => 'En haut à gauche'
    ];
    // Mode par défaut : ajout d'un nouveau bouton + index du bouton à éditer (par défaut: aucun)
    $edit_mode = false; 
    $edit_index = -1; 

    // Gestion du formulaire pour ajouter ou modifier un bouton
    if (isset($_POST['add_button'])) {
        // Création d'un nouveau bouton avec les champs du formulaire, en vérifiant la sécurité des données
        $new_button = [
            'text' => sanitize_text_field($_POST['button_text']),
            'bg_color' => sanitize_hex_color($_POST['button_bg_color']),
            'text_color' => sanitize_hex_color($_POST['button_text_color']),
            'icon' => sanitize_text_field($_POST['button_icon']),
            'position' => sanitize_text_field($_POST['button_position']),
        ];

        // Vérifie si on est en mode édition ou ajout
        if (isset($_POST['edit_index']) && $_POST['edit_index'] !== '') {
            // Mode édition : remplace le bouton existant à l'index donné
            $edit_index = intval($_POST['edit_index']);
            $buttons[$edit_index] = $new_button;
        } else {
            // Mode ajout : ajoute le nouveau bouton à la fin du tableau
            $buttons[] = $new_button;
        }

        // Sauvegarde la liste des boutons dans la base de données
        update_option('boutons_flottants', $buttons);
    }

    // Gestion de la suppression d'un bouton
    if (isset($_POST['delete_button'])) {
        $index = intval($_POST['button_index']); // Récupère l'index du bouton à supprimer
        unset($buttons[$index]); // Supprime le bouton en question du tableau
        $buttons = array_values($buttons); // Réindexe le tableau pour éviter les "trous" dans les indices
        update_option('boutons_flottants', $buttons); // Met à jour les boutons dans la base de données
    }

    // Activation du mode édition lorsqu'on clique sur "Modifier"
    if (isset($_POST['edit_button'])) {
        $edit_mode = true; // On passe en mode édition
        $edit_index = intval($_POST['button_index']); // Récupère l'index du bouton à modifier
        $button_to_edit = $buttons[$edit_index]; // Charge les données du bouton pour remplir le formulaire
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
        
        <!-- Liste des boutons existants pour les afficher, modifier ou bien les supprimer -->
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

// Fonction qui affiche les boutons flottants côté utilisateur, en tenant compte de leur position
function boutons_flottants_display_buttons() {
    $buttons = get_option('boutons_flottants', []);
    
    // On regroupe les boutons par position (haut, bas, gauche, droite) pour un affichage organisé
    $grouped_buttons = [];
    foreach ($buttons as $button) {
        $grouped_buttons[$button['position']][] = $button;
    }
    
    // Pour chaque groupe de boutons, on ajoute un décalage pour éviter qu'ils ne se superpose, ce qui autrement rendait illisible la lecture des boutons
    foreach ($grouped_buttons as $position => $buttons) {
        $offset = 0; // Point de départ pour le décalage des boutons

        foreach ($buttons as $button) {
            $position_style = '';
            
            // Définir la position du bouton en fonction de son emplacement (haut, bas, gauche, droite)
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

            // Génère le HTML pour chaque bouton avec ses styles et couleurs
            echo '<div class="boutons-flottants-button" style="position:fixed; ' . $position_style . ' background-color:' . esc_attr($button['bg_color'] ?? '#0073aa') . '; color:' . esc_attr($button['text_color'] ?? '#ffffff') . '; padding:10px; border-radius:5px; cursor:pointer; display:flex; align-items:center;">
                <i class="' . esc_attr($button['icon'] ?? '') . '"></i> ' . esc_html($button['text'] ?? '') . '
            </div>';
            
            // Ajoute un décalage pour que chaque bouton soit un peu plus haut/bas que le précédent
            $offset += 60;
        }
    }
}
// Fonction qui ajoute la fonction boutons_flottants_display_buttons au hook wp_footer. Ce hook est déclenché dans le pied de page de chaque page du site
add_action('wp_footer', 'boutons_flottants_display_buttons');

/*
Conclusion :
Ce plugin crée une interface dans le tableau de bord pour ajouter, modifier et supprimer des boutons flottants personnalisables (texte, couleur, icône, position). 
Les boutons sont enregistrés et affichés sur toutes les pages du site, avec leur position et styles configurés par l'admin.
*/
