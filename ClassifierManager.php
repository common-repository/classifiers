<?php

class ClassifierManager {
    
    protected $db;
    protected $category_table;
    protected $classifier_table;
    
    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        $this->category_table = $this->db->prefix . 'wpc_category';
        $this->classifier_table = $this->db->prefix . 'wpc_classifier';
    }
    
    public function getCategories() {
        return $this->db->get_results('SELECT * FROM ' . $this->category_table . ' ORDER BY id');
    }
    
    public function getCategory($id) {
        return $this->db->get_row('SELECT * FROM ' . $this->category_table . ' WHERE id=' . $id);
    }
    
    public function insertCategory($name) {
        $this->db->insert($this->category_table, array('title' => $name));
        return $this->db->insert_id;
    }
    
    public function renameCategory($id, $name) {
        $this->db->update($this->category_table, array('title' => $name), array('id' => $id));
    }
    
    public function removeCategory($id) {
        $this->db->delete($this->category_table, array('id' => $id));
    }
    
    public function getClassifiers($cat_id) {
        $result = $this->db->get_results('SELECT * FROM ' . $this->classifier_table . ' WHERE category=' . $cat_id . ' ORDER BY -position DESC');
        foreach ($result as $item) {
            if ($item->image_id) {
                $item->image_src = wp_get_attachment_image_src( $item->image_id )[0];
            }
        }
        return $result;
    }
    
    public function getClassifier($id) {
        return $this->db->get_row('SELECT * FROM ' . $this->classifier_table . ' WHERE id=' . $id);
    }
    
    public function insertClassifier($category, $data) {
        $fields = array(
            'category' => $category,
            'title' => $data->title,
            'description' => $data->description,
        );
        if (isset($data->position) && $data->position) {
            $fields['position'] = $data->position;
        }
        if (isset($data->image_id) && $data->image_id) {
            $fields['image_id'] = $data->image_id;
        }
        $this->db->insert($this->classifier_table, $fields);
        return $this->db->insert_id;
    }
    
    public function editClassifier($id, $data) {
        $fields = array(
            'title' => $data->title,
            'description' => $data->description
        );
        if (isset($data->position) && $data->position) {
            $fields['position'] = $data->position;
        }
        if (isset($data->image_id) && $data->image_id) {
            $fields['image_id'] = $data->image_id;
        }
        $this->db->update($this->classifier_table, $fields, array('id' => $id));
    }
    
    public function removeClassifier($id) {
        $this->removeClassifierImage($id);
        $this->db->delete($this->classifier_table, array('id' => $id));
    }
    
    public function removeClassifierImage($id) {
        $classifier = $this->getClassifier($id);
        wp_delete_attachment( $classifier->image_id, true );
        $this->db->update($this->classifier_table, array('image_id' => null), array('id' => $id));
    }
    
    /*
     * Setup db tables
     */
    static function install() {
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $category_table = $wpdb->prefix . 'wpc_category';
        $category_sql = "CREATE TABLE $category_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            title varchar(55) NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY title (title)
        ) $charset_collate;";
        dbDelta($category_sql);
        
        $classifier_table = $wpdb->prefix . 'wpc_classifier';
        $classifier_sql = "CREATE TABLE $classifier_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            category int(11) NOT NULL,
            title varchar(55) NOT NULL,
            description text,
            image_id bigint(20) unsigned,
            position int(11) unsigned,
            PRIMARY KEY  (id),
            UNIQUE KEY title (category, title),
            CONSTRAINT wpc_classifier_category FOREIGN KEY (category) REFERENCES $category_table (id) ON DELETE CASCADE ON UPDATE CASCADE
        ) $charset_collate;";
        dbDelta($classifier_sql);
    }

}