<?php
/**
 * @package Classifiers
 * @version 1.0
 */
/*
  Plugin Name: Classifiers
  Plugin URI: https://wordpress.org/plugins/classifiers/
  Description: Instead of hard-coding certain texts (selectable options) to your template, make them manageable on the admin side.
  Author: Taavi Aasver
  Version: 1.0
  Author URI: http://taaviaasver.com/
 */

require 'ClassifierManager.php';

register_activation_hook( __FILE__, array( 'ClassifierManager', 'install' ) );

class Classifiers {
    
    protected $manager;
    
    public function __construct() {
        $this->manager = new ClassifierManager();
        add_action( 'admin_menu', array($this, 'wpc_add_admin_menu') );
        add_action( 'wp_ajax_wpc_ajax_getclassifiers', array($this, 'wpc_ajax_getclassifiers') );
        add_action( 'wp_ajax_nopriv_wpc_ajax_getclassifiers', array($this, 'wpc_ajax_getclassifiers') );
    }
    
    function wpc_ajax_getclassifiers() {
        $return = array();
        if (isset($_GET['category'])) {
            $return = $this->manager->getClassifiers(sanitize_text_field( $_GET['category'] ));
        }
        echo json_encode($return);
        die();
    }
    
    public function wpc_add_admin_menu() {
        add_menu_page( 'Classifiers', 'Classifiers', 'manage_options', 'classifiers', array($this, 'wpc_admin'), null, 22);
    }
    
    public function wpc_admin() {
        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }
        
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'viewcategory':
                    $this->categoryView(isset($_GET['id']) ? $_GET['id'] : null);
                    break;
                case 'savecategory':
                    if (isset($_POST['category_title']) && !empty($_POST['category_title'])) {
                        if (isset($_GET['id'])) {
                            $cat = $_GET['id'];
                            $this->manager->renameCategory($cat, sanitize_text_field($_POST['category_title']));
                        } else {
                            $cat = $this->manager->insertCategory(sanitize_text_field($_POST['category_title']));
                        }
                    } else {
                        $this->categoryList();
                    }
                    $this->categoryView($cat);
                    break;
                case 'deletecategory':
                    if (isset($_GET['id'])) {
                        $this->manager->removeCategory($_GET['id']);
                    }
                    $this->categoryList();
                    break;
                case 'viewclassifier':
                    $this->classifierView($_GET['cat'], isset($_GET['id']) ? $_GET['id'] : null);
                    break;
                case 'saveclassifier':
                    $cat = $_GET['cat'];
                    $data = $this->extractClassifier();
                    if (isset($data->title)) {
                        if (isset($_GET['id'])) {
                            $this->manager->editClassifier($_GET['id'], $data);
                        } else {
                            $this->manager->insertClassifier($cat, $data);
                        }
                    }
                    $this->categoryView($cat);
                    break;
                case 'deleteclassifier':
                    $cat = $_GET['cat'];
                    if (isset($_GET['id'])) {
                        $this->manager->removeClassifier($_GET['id']);
                    }
                    $this->categoryView($cat);
                    break;
                case 'deleteclassifierimage':
                    $cat = $_GET['cat'];
                    if (isset($_GET['id'])) {
                        $this->manager->removeClassifierImage($_GET['id']);
                    }
                    $this->categoryView($cat);
                    break;
                default:
                    $this->categoryList();
                    break;
            }
        } else {
            $this->categoryList();
        }
        
    }
    
    public function categoryList() {
        ?>
            <h3><?php _e('Classifier Categories', 'wpc'); ?></h3>
            <table class="widefat importers striped skills" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><?php _e('Title', 'wpc'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->manager->getCategories() as $cat) : ?>
                        <tr>
                            <td><a href="?page=classifiers&action=viewcategory&id=<?php echo $cat->id; ?>"><?php echo $cat->id; ?></a></td>
                            <td><a href="?page=classifiers&action=viewcategory&id=<?php echo $cat->id; ?>"><?php echo $cat->title; ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2"><a href="?page=classifiers&action=viewcategory" class="add button button-primary"><?php _e('Add new', 'wpc'); ?></a></th>
                    </tr>
                </tfoot>
            </table>
        <?php
    }
    
    public function categoryView($cat_id = null) {
        $cat = false;
        if ($cat_id) {
            $cat = $this->manager->getCategory($cat_id);
        }
        ?>
            <h3><?php _e('Add/edit classifier group:', 'wpc'); ?></h3>
            <div style="margin: 20px 0;">
                <form method="post" action="?page=classifiers&action=savecategory<?php echo $cat ? '&id=' . $cat->id : '' ?>">
                    <input style="font-size:1.7em;" type="text" name="category_title" value="<?php echo $cat ? $cat->title : ''; ?>" />
                    <input style="height: 100%; line-height: 35px;" class="add button button-primary" type="submit" value="<?php $cat ? _e('Rename', 'wpc') : _e('Save', 'wpc'); ?>" />
                    <?php if ($cat) : ?>
                        <a style="height: 100%; line-height: 35px;" href="?page=classifiers&action=deletecategory&id=<?php echo $cat ? $cat->id : ''; ?>" class="add button"><?php _e('Delete', 'wpc'); ?></a>
                    <?php endif; ?>
                </form>
            </div>
            <?php if ($cat) : ?>
                <table class="widefat importers striped skills" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th><?php _e('Title', 'wpc'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->manager->getClassifiers($cat->id) as $classifier) : ?>
                            <tr>
                                <td><a href="?page=classifiers&action=viewclassifier&cat=<?php echo $cat->id; ?>&id=<?php echo $classifier->id; ?>"><?php echo $classifier->id; ?></a></td>
                                <td><a href="?page=classifiers&action=viewclassifier&cat=<?php echo $cat->id; ?>&id=<?php echo $classifier->id; ?>"><?php echo $classifier->title; ?></a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2"><a href="?page=classifiers&action=viewclassifier&cat=<?php echo $cat->id; ?>" class="add button button-primary"><?php _e('Add new', 'wpc'); ?></a></th>
                        </tr>
                    </tfoot>
                </table>
            <?php endif; ?>
        <?php
    }
    
    public function classifierView($cat_id, $id = null) {
        $classifier = false;
        $cat = $this->manager->getCategory($cat_id);
        if ($id) {
            $classifier = $this->manager->getClassifier($id);
        }
        ?>
            <h3><?php echo sprintf(__('Add/edit classifier (%s):', 'wpc'), $cat->title); ?></h3>
            <form method="post" action="?page=classifiers&action=saveclassifier&cat=<?php echo $cat->id; ?><?php echo $classifier ? '&id=' . $classifier->id : '' ?>" enctype="multipart/form-data">
                <div>
                    <input type="text" name="classifier_title" value="<?php echo $classifier ? $classifier->title : ''; ?>" placeholder="<?php _e('Title..', 'wpc'); ?>" style="min-width:455px" />
                </div>
                <div>
                    <textarea name="description" placeholder="<?php _e('Description.. (optional)', 'wpc'); ?>" rows="5" style="min-width:455px"><?php echo $classifier ? $classifier->description : ''; ?></textarea>
                </div>
                <div>
                     <strong><?php _e('Position (optional):', 'wpc'); ?></strong> 
                     <input type="number" min="1" name="classifier_position" value="<?php echo $classifier ? $classifier->position : ''; ?>" style="width:50px" />
                </div>
                <div>
                    <strong><?php _e('Image (optional):', 'wpc'); ?></strong> <input type="file" name="image"  multiple="false" />
                </div>
                <?php if ($classifier && $classifier->image_id) : ?>
                    <div>
                        <?php echo wp_get_attachment_image( $classifier->image_id ); ?>
                    </div>
                <?php endif; ?>
                <div>
                    <input class="add button button-primary" type="submit" value="<?php _e('Save', 'wpc'); ?>" />
                    <?php if ($classifier && $classifier->image_id) : ?>
                        <a href="?page=classifiers&action=deleteclassifierimage&cat=<?php echo $cat->id; ?>&id=<?php echo $classifier->id; ?>" class="add button"><?php _e('Delete image', 'wpc'); ?></a>
                    <?php endif; ?>
                    <?php if ($classifier) : ?>
                        <a href="?page=classifiers&action=deleteclassifier&cat=<?php echo $cat->id; ?>&id=<?php echo $classifier->id; ?>" class="add button"><?php _e('Delete', 'wpc'); ?></a>
                    <?php endif; ?>
                </div>
            </form>
        <?php
    }
    
    protected function extractClassifier() {
        $return = new stdClass();
        if (isset($_POST['classifier_title']) && !empty($_POST['classifier_title'])) {
            $return->title = sanitize_text_field($_POST['classifier_title']);
        }
        if (isset($_POST['classifier_position']) && !empty($_POST['classifier_position'])) {
            $return->position = intval(sanitize_text_field($_POST['classifier_position']));
        }
        $return->description = isset($_POST['description']) ? sanitize_text_field($_POST['description']) : null;
        if (isset($_FILES['image']) && !empty($_FILES['image']['name'])) {
            $uploaded_image = $_FILES['image'];
            $image_id = media_handle_sideload($uploaded_image, 0);
            if ( !is_wp_error( $image_id ) ) {
                $return->image_id = $image_id;
            }
        }
        return $return;
    }
    
}
new Classifiers();