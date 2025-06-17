<?php

/**
 * Plugin Name: Tisseurs Frontend Publisher
 * Description: WordPress plugin to enable blogpost posting from page with a short code in Tisseurs de Chimeres association
 * Version: 0.1.0
 * Author: Bertrand Madet
 * Licence: GPLv3 or later
 * Licence URI: https://www.gnu.org/licenses/gpl-3.0.txt
 */


// Security : Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class FrontendArticlePublisher {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('frontend_publisher', array($this, 'display_publisher_form'));
        add_action('wp_ajax_submit_frontend_article', array($this, 'handle_article_submission'));
        add_action('wp_ajax_nopriv_submit_frontend_article', array($this, 'handle_article_submission'));
    }
    
    public function init() {
        // Charger les traductions si nécessaire
        load_plugin_textdomain('frontend-publisher', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('frontend-publisher-js', plugin_dir_url(__FILE__) . 'assets/js/frontend-publisher.js', array('jquery'), '1.0.0', true);
        wp_enqueue_style('frontend-publisher-css', plugin_dir_url(__FILE__) . 'assets/css/frontend-publisher.css', array(), '1.0.0');
        
        // JavaScript Variables for AJAX
        wp_localize_script('frontend-publisher-js', 'frontendPublisher', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('frontend_publisher_nonce'),
            'messages' => array(
                'success' => __('Article publié avec succès !', 'frontend-publisher'),
                'error' => __('Erreur lors de la publication de l\'article.', 'frontend-publisher'),
                'required_fields' => __('Veuillez remplir tous les champs obligatoires.', 'frontend-publisher')
            )
        ));
    }
    
    public function display_publisher_form($atts) {
        // Attributes of the shortcode
        $atts = shortcode_atts(array(
            'allowed_roles' => 'editor,author,contributor', // Rôles autorisés
            'post_status' => 'draft', // Statut par défaut des articles
            'show_categories' => 'yes',
            'show_tags' => 'yes',
            'show_featured_image' => 'yes'
        ), $atts);
        
        if (!is_user_logged_in()) {
            return '<div class="frontend-publisher-error">' . __('Vous devez être connecté pour publier un article.', 'frontend-publisher') . '</div>';
        }
        
        if (!$this->user_can_publish($atts['allowed_roles'])) {
            return '<div class="frontend-publisher-error">' . __('Vous n\'avez pas les droits nécessaires pour publier des articles.', 'frontend-publisher') . '</div>';
        }
        
        ob_start();
        ?>
        <div id="frontend-publisher-container">
            <form id="frontend-publisher-form" class="frontend-publisher-form">
                <?php wp_nonce_field('frontend_publisher_nonce', 'frontend_publisher_nonce'); ?>
                
                <div class="form-group">
                    <label for="article_title"><?php _e('Titre de l\'article *', 'frontend-publisher'); ?></label>
                    <input type="text" id="article_title" name="article_title" required>
                </div>
                
                <div class="form-group">
                    <label for="article_content"><?php _e('Contenu de l\'article *', 'frontend-publisher'); ?></label>
                    <?php
                    // Get rich editor
                    wp_editor('', 'article_content', array(
                        'textarea_name' => 'article_content',
                        'textarea_rows' => 10,
                        'teeny' => false,
                        'media_buttons' => true,
                        'tinymce' => array(
                            'toolbar1' => 'bold,italic,underline,strikethrough,|,bullist,numlist,|,link,unlink,|,undo,redo',
                            'toolbar2' => 'formatselect,|,forecolor,backcolor,|,alignleft,aligncenter,alignright,alignjustify'
                        )
                    ));
                    ?>
                </div>
                
                <div class="form-group">
                    <label for="article_excerpt"><?php _e('Extrait (optionnel)', 'frontend-publisher'); ?></label>
                    <textarea id="article_excerpt" name="article_excerpt" rows="3"></textarea>
                </div>
                
                <?php if ($atts['show_categories'] === 'yes'): ?>
                <div class="form-group">
                    <label for="article_categories"><?php _e('Catégories', 'frontend-publisher'); ?></label>
                    <?php
                    $categories = get_categories(array('hide_empty' => false));
                    if (!empty($categories)):
                    ?>
                        <select id="article_categories" name="article_categories[]" multiple>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo esc_attr($category->term_id); ?>">
                                    <?php echo esc_html($category->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($atts['show_tags'] === 'yes'): ?>
                <div class="form-group">
                    <label for="article_tags"><?php _e('Mots-clés (séparés par des virgules)', 'frontend-publisher'); ?></label>
                    <input type="text" id="article_tags" name="article_tags" placeholder="<?php _e('mot-clé1, mot-clé2, mot-clé3', 'frontend-publisher'); ?>">
                </div>
                <?php endif; ?>
                
                <?php if ($atts['show_featured_image'] === 'yes'): ?>
                <div class="form-group">
                    <label for="article_featured_image"><?php _e('Image mise en avant', 'frontend-publisher'); ?></label>
                    <input type="file" id="article_featured_image" name="article_featured_image" accept="image/*">
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="article_status"><?php _e('Statut de publication', 'frontend-publisher'); ?></label>
                    <select id="article_status" name="article_status">
                        <option value="draft" <?php selected($atts['post_status'], 'draft'); ?>><?php _e('Brouillon', 'frontend-publisher'); ?></option>
                        <?php if (current_user_can('publish_posts')): ?>
                            <option value="publish" <?php selected($atts['post_status'], 'publish'); ?>><?php _e('Publié', 'frontend-publisher'); ?></option>
                        <?php endif; ?>
                        <option value="pending" <?php selected($atts['post_status'], 'pending'); ?>><?php _e('En attente de révision', 'frontend-publisher'); ?></option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" id="submit-article" class="frontend-publisher-submit">
                        <?php _e('Publier l\'article', 'frontend-publisher'); ?>
                    </button>
                    <div id="frontend-publisher-loading" class="loading-spinner" style="display:none;">
                        <?php _e('Publication en cours...', 'frontend-publisher'); ?>
                    </div>
                </div>
            </form>
            
            <div id="frontend-publisher-message" class="message" style="display:none;"></div>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    private function user_can_publish($allowed_roles) {
        $current_user = wp_get_current_user();
        $allowed_roles_array = explode(',', $allowed_roles);
        $allowed_roles_array = array_map('trim', $allowed_roles_array);
        
        // Check user roles
        foreach ($allowed_roles_array as $role) {
            if (in_array($role, $current_user->roles)) {
                return true;
            }
        }
        
        // Check specific capability
        return current_user_can('edit_posts');
    }
    
    public function handle_article_submission() {
        if (!wp_verify_nonce($_POST['nonce'], 'frontend_publisher_nonce')) {
            wp_die('Erreur de sécurité');
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('Vous devez être connecté.', 'frontend-publisher')));
        }
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Vous n\'avez pas les droits nécessaires.', 'frontend-publisher')));
        }
        
        $title = sanitize_text_field($_POST['title']);
        $content = wp_kses_post($_POST['content']);
        $excerpt = sanitize_textarea_field($_POST['excerpt']);
        $categories = json_decode(stripslashes($_POST['categories']), true);
        $tags = sanitize_text_field($_POST['tags']);
        $status = sanitize_text_field($_POST['status']);
        
        if (empty($title) || empty($content)) {
            wp_send_json_error(array('message' => __('Le titre et le contenu sont obligatoires.', 'frontend-publisher')));
        }
        
        $allowed_statuses = array('draft', 'pending');
        if (current_user_can('publish_posts')) {
            $allowed_statuses[] = 'publish';
        }
        
        if (!in_array($status, $allowed_statuses)) {
            $status = 'draft';
        }
        
        $post_data = array(
            'post_title' => $title,
            'post_content' => $content,
            'post_excerpt' => $excerpt,
            'post_status' => $status,
            'post_type' => 'post',
            'post_author' => get_current_user_id(),
            'post_category' => is_array($categories) ? array_map('intval', $categories) : array()
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            wp_send_json_error(array('message' => __('Erreur lors de la création de l\'article.', 'frontend-publisher')));
        }
        
        if (!empty($tags)) {
            wp_set_post_tags($post_id, $tags);
        }
        
        if (!empty($_FILES['featured_image']['name'])) {
            $upload_result = $this->handle_featured_image_upload($_FILES['featured_image'], $post_id);
            if (!$upload_result) {
                wp_send_json_success(array('message' => __('Article créé mais l\'image n\'a pas pu être uploadée.', 'frontend-publisher')));
            }
        }
        
        $message = '';
        switch ($status) {
            case 'publish':
                $message = __('Article publié avec succès !', 'frontend-publisher');
                break;
            case 'pending':
                $message = __('Article soumis pour révision !', 'frontend-publisher');
                break;
            default:
                $message = __('Article sauvegardé en brouillon !', 'frontend-publisher');
        }
        
        wp_send_json_success(array('message' => $message, 'post_id' => $post_id));
    }
    
    private function handle_featured_image_upload($file, $post_id) {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        $uploadedfile = $file;
        $upload_overrides = array('test_form' => false);
        
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            $wp_filetype = wp_check_filetype($movefile['file'], null);
            
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => sanitize_file_name($movefile['file']),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            
            $attach_id = wp_insert_attachment($attachment, $movefile['file'], $post_id);
            
            if (!function_exists('wp_generate_attachment_metadata')) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
            }
            
            $attach_data = wp_generate_attachment_metadata($attach_id, $movefile['file']);
            wp_update_attachment_metadata($attach_id, $attach_data);
            
            set_post_thumbnail($post_id, $attach_id);
            
            return true;
        }
        
        return false;
    }
}

new FrontendArticlePublisher();


register_activation_hook(__FILE__, 'frontend_publisher_activate');
function frontend_publisher_activate() {
    if (version_compare(get_bloginfo('version'), '6.0', '<')) {
        wp_die(__('Ce plugin nécessite WordPress 6.0 ou plus récent.', 'frontend-publisher'));
    }
}

register_deactivation_hook(__FILE__, 'frontend_publisher_deactivate');
function frontend_publisher_deactivate() {
    // Do nothing now
}
?>