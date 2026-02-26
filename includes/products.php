<?php
require_once 'db.php';

class Products {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function get_all_products($limit = null, $offset = null, $category_id = null, $search = null) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 'active'";
        
        $params = [];
        
        if ($category_id) {
            $sql .= " AND p.category_id = ?";
            $params[] = $category_id;
        }
        
        if ($search) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
            
            if ($offset !== null) {
                $sql .= " OFFSET ?";
                $params[] = $offset;
            }
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function get_product($product_id) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = ? AND p.status = 'active'";
        
        return $this->db->fetchRow($sql, [$product_id]);
    }
    
    public function get_products_by_category($category_id, $limit = null, $offset = null) {
        return $this->get_all_products($limit, $offset, $category_id);
    }
    
    public function get_featured_products($limit = 6) {
        return $this->get_all_products($limit);
    }
    
    public function get_related_products($product_id, $category_id, $limit = 4) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.category_id = ? AND p.status = 'active' AND p.id != ?
                ORDER BY p.created_at DESC 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$category_id, $product_id, $limit]);
    }
    
    public function get_categories() {
        $sql = "SELECT * FROM categories ORDER BY parent_id ASC, name ASC";
        return $this->db->fetchAll($sql);
    }
    
    public function get_category($category_id) {
        $sql = "SELECT * FROM categories WHERE id = ?";
        return $this->db->fetchRow($sql, [$category_id]);
    }
    
    public function get_category_tree() {
        $categories = $this->get_categories();
        $tree = [];
        
        foreach ($categories as $category) {
            if ($category['parent_id'] === null) {
                $tree[$category['id']] = $category;
                $tree[$category['id']]['children'] = [];
            }
        }
        
        foreach ($categories as $category) {
            if ($category['parent_id'] !== null) {
                if (isset($tree[$category['parent_id']])) {
                    $tree[$category['parent_id']]['children'][] = $category;
                }
            }
        }
        
        return $tree;
    }
    
    public function get_product_count($category_id = null, $search = null) {
        $sql = "SELECT COUNT(*) as count FROM products WHERE status = 'active'";
        $params = [];
        
        if ($category_id) {
            $sql .= " AND category_id = ?";
            $params[] = $category_id;
        }
        
        if ($search) {
            $sql .= " AND (name LIKE ? OR description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $result = $this->db->fetchRow($sql, $params);
        return $result['count'];
    }
    
    public function get_latest_products($limit = 6) {
        return $this->get_all_products($limit);
    }
    
    public function get_discounted_products($limit = 6) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 'active' AND p.discount_price IS NOT NULL AND p.discount_price < p.price
                ORDER BY p.created_at DESC 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$limit]);
    }
    
    public function add_product($data, $image = null) {
        $product_data = [
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'discount_price' => $data['discount_price'] ?: null,
            'stock_quantity' => $data['stock_quantity'],
            'category_id' => $data['category_id'],
            'status' => $data['status'],
            'created_by' => $data['created_by']
        ];
        
        if ($image) {
            $product_data['image'] = $image;
        }
        
        return $this->db->insert('products', $product_data);
    }
    
    public function update_product($product_id, $data, $image = null) {
        $product_data = [
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'discount_price' => $data['discount_price'] ?: null,
            'stock_quantity' => $data['stock_quantity'],
            'category_id' => $data['category_id'],
            'status' => $data['status']
        ];
        
        if ($image) {
            $product_data['image'] = $image;
        }
        
        return $this->db->update('products', $product_data, 'id = ?', [$product_id]);
    }
    
    public function delete_product($product_id) {
        return $this->db->delete('products', 'id = ?', [$product_id]);
    }
    
    public function get_product_reviews($product_id) {
        $sql = "SELECT r.*, u.full_name as user_name, u.username 
                FROM reviews r 
                LEFT JOIN users u ON r.user_id = u.id 
                WHERE r.product_id = ? 
                ORDER BY r.created_at DESC";
        
        return $this->db->fetchAll($sql, [$product_id]);
    }
    
    public function add_review($data) {
        // Check if user already reviewed this product
        $existing_review = $this->db->fetchRow(
            "SELECT id FROM reviews WHERE user_id = ? AND product_id = ?",
            [$data['user_id'], $data['product_id']]
        );
        
        if ($existing_review) {
            return ['success' => false, 'message' => 'شما قبلاً برای این محصول نظر ثبت کرده‌اید'];
        }
        
        $review_id = $this->db->insert('reviews', [
            'product_id' => $data['product_id'],
            'user_id' => $data['user_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment']
        ]);
        
        if ($review_id) {
            // Update product rating
            $this->update_product_rating($data['product_id']);
            
            return ['success' => true, 'message' => 'نظر شما با موفقیت ثبت شد'];
        } else {
            return ['success' => false, 'message' => 'خطا در ثبت نظر'];
        }
    }
    
    private function update_product_rating($product_id) {
        $sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
                FROM reviews 
                WHERE product_id = ?";
        
        $result = $this->db->fetchRow($sql, [$product_id]);
        
        // Update product table with rating information (if you add rating columns)
        // For now, we'll just keep the reviews in the reviews table
    }
    
    public function get_product_rating($product_id) {
        $sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
                FROM reviews 
                WHERE product_id = ?";
        
        $result = $this->db->fetchRow($sql, [$product_id]);
        
        return [
            'avg_rating' => $result['avg_rating'] ?: 0,
            'review_count' => $result['review_count'] ?: 0
        ];
    }
    
    public function get_user_products($user_id, $limit = null, $offset = null) {
        $sql = "SELECT p.*, c.name as category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.created_by = ?";
        
        $params = [$user_id];
        
        $sql .= " ORDER BY p.created_at DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
            
            if ($offset !== null) {
                $sql .= " OFFSET ?";
                $params[] = $offset;
            }
        }
        
        return $this->db->fetchAll($sql, $params);
    }
}

$products = new Products();