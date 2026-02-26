<?php
require_once 'db.php';

class Cart {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function add_to_cart($user_id, $product_id, $quantity = 1) {
        // Check if product exists and is active
        $product = $this->db->fetchRow("SELECT * FROM products WHERE id = ? AND status = 'active'", [$product_id]);
        
        if (!$product) {
            return ['success' => false, 'message' => 'محصول یافت نشد'];
        }
        
        // Check stock
        if ($product['stock_quantity'] < $quantity) {
            return ['success' => false, 'message' => 'تعداد موجودی کافی نیست'];
        }
        
        // Check if product already in cart
        $existing_item = $this->db->fetchRow(
            "SELECT * FROM cart WHERE user_id = ? AND product_id = ?",
            [$user_id, $product_id]
        );
        
        if ($existing_item) {
            // Update quantity
            $new_quantity = $existing_item['quantity'] + $quantity;
            
            if ($new_quantity > $product['stock_quantity']) {
                return ['success' => false, 'message' => 'تعداد موجودی کافی نیست'];
            }
            
            $this->db->update('cart', ['quantity' => $new_quantity], 'id = ?', [$existing_item['id']]);
        } else {
            // Add new item to cart
            $this->db->insert('cart', [
                'user_id' => $user_id,
                'product_id' => $product_id,
                'quantity' => $quantity
            ]);
        }
        
        return ['success' => true, 'message' => 'محصول به سبد خرید اضافه شد'];
    }
    
    public function remove_from_cart($user_id, $product_id) {
        $result = $this->db->delete('cart', 'user_id = ? AND product_id = ?', [$user_id, $product_id]);
        
        if ($result > 0) {
            return ['success' => true, 'message' => 'محصول از سبد خرید حذف شد'];
        } else {
            return ['success' => false, 'message' => 'محصول در سبد خرید یافت نشد'];
        }
    }
    
    public function update_cart_quantity($user_id, $product_id, $quantity) {
        if ($quantity <= 0) {
            return $this->remove_from_cart($user_id, $product_id);
        }
        
        // Check stock
        $product = $this->db->fetchRow("SELECT * FROM products WHERE id = ? AND status = 'active'", [$product_id]);
        
        if (!$product) {
            return ['success' => false, 'message' => 'محصول یافت نشد'];
        }
        
        if ($quantity > $product['stock_quantity']) {
            return ['success' => false, 'message' => 'تعداد موجودی کافی نیست'];
        }
        
        $result = $this->db->update('cart', ['quantity' => $quantity], 'user_id = ? AND product_id = ?', [$user_id, $product_id]);
        
        if ($result > 0) {
            return ['success' => true, 'message' => 'تعداد به‌روزرسانی شد'];
        } else {
            return ['success' => false, 'message' => 'خطا در به‌روزرسانی سبد خرید'];
        }
    }
    
    public function get_cart_items($user_id) {
        $sql = "SELECT c.*, p.name, p.price, p.discount_price, p.image, p.stock_quantity 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ? AND p.status = 'active'";
        
        return $this->db->fetchAll($sql, [$user_id]);
    }
    
    public function get_cart_total($user_id) {
        $items = $this->get_cart_items($user_id);
        
        $total = 0;
        foreach ($items as $item) {
            $price = $item['discount_price'] ?: $item['price'];
            $total += $price * $item['quantity'];
        }
        
        return $total;
    }
    
    public function get_cart_item_count($user_id) {
        $sql = "SELECT SUM(quantity) as total_quantity FROM cart WHERE user_id = ?";
        $result = $this->db->fetchRow($sql, [$user_id]);
        
        return $result['total_quantity'] ?: 0;
    }
    
    public function clear_cart($user_id) {
        $this->db->delete('cart', 'user_id = ?', [$user_id]);
        return ['success' => true, 'message' => 'سبد خرید خالی شد'];
    }
    
    public function is_product_in_cart($user_id, $product_id) {
        $sql = "SELECT COUNT(*) as count FROM cart WHERE user_id = ? AND product_id = ?";
        $result = $this->db->fetchRow($sql, [$user_id, $product_id]);
        
        return $result['count'] > 0;
    }
    
    public function get_cart_summary($user_id) {
        $items = $this->get_cart_items($user_id);
        $total = $this->get_cart_total($user_id);
        $item_count = $this->get_cart_item_count($user_id);
        
        return [
            'items' => $items,
            'total' => $total,
            'item_count' => $item_count
        ];
    }
    
    public function move_cart_to_order($user_id, $order_data) {
        $this->db->beginTransaction();
        
        try {
            // Get cart items
            $cart_items = $this->get_cart_items($user_id);
            
            if (empty($cart_items)) {
                throw new Exception('سبد خرید خالی است');
            }
            
            // Create order
            $order_id = $this->db->insert('orders', [
                'user_id' => $user_id,
                'order_number' => generate_order_number(),
                'total_amount' => $this->get_cart_total($user_id),
                'status' => 'pending',
                'payment_method' => $order_data['payment_method'],
                'payment_status' => 'pending',
                'shipping_address' => $order_data['shipping_address'],
                'notes' => $order_data['notes']
            ]);
            
            // Create order items
            foreach ($cart_items as $item) {
                $price = $item['discount_price'] ?: $item['price'];
                
                $this->db->insert('order_items', [
                    'order_id' => $order_id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $price
                ]);
                
                // Update product stock
                $this->db->update('products', [
                    'stock_quantity' => $item['stock_quantity'] - $item['quantity']
                ], 'id = ?', [$item['product_id']]);
            }
            
            // Clear cart
            $this->clear_cart($user_id);
            
            $this->db->commit();
            
            return ['success' => true, 'order_id' => $order_id, 'message' => 'سفارش با موفقیت ثبت شد'];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'خطا در ثبت سفارش: ' . $e->getMessage()];
        }
    }
}

$cart = new Cart();