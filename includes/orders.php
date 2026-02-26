<?php
require_once 'db.php';

class Orders {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create_order($user_id, $order_data) {
        $this->db->beginTransaction();
        
        try {
            // Create order
            $order_id = $this->db->insert('orders', [
                'user_id' => $user_id,
                'order_number' => generate_order_number(),
                'total_amount' => $order_data['total_amount'],
                'status' => 'pending',
                'payment_method' => $order_data['payment_method'],
                'payment_status' => $order_data['payment_status'],
                'shipping_address' => $order_data['shipping_address'],
                'notes' => $order_data['notes']
            ]);
            
            // Create order items
            foreach ($order_data['items'] as $item) {
                $this->db->insert('order_items', [
                    'order_id' => $order_id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);
                
                // Update product stock
                $this->db->update('products', [
                    'stock_quantity' => $item['stock_quantity'] - $item['quantity']
                ], 'id = ?', [$item['product_id']]);
            }
            
            $this->db->commit();
            
            return ['success' => true, 'order_id' => $order_id, 'message' => 'سفارش با موفقیت ثبت شد'];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'خطا در ثبت سفارش: ' . $e->getMessage()];
        }
    }
    
    public function get_order($order_id, $user_id = null) {
        $sql = "SELECT o.*, u.full_name, u.email, u.phone 
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                WHERE o.id = ?";
        
        $params = [$order_id];
        
        if ($user_id !== null) {
            $sql .= " AND o.user_id = ?";
            $params[] = $user_id;
        }
        
        return $this->db->fetchRow($sql, $params);
    }
    
    public function get_order_items($order_id) {
        $sql = "SELECT oi.*, p.name, p.image 
                FROM order_items oi 
                LEFT JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?";
        
        return $this->db->fetchAll($sql, [$order_id]);
    }
    
    public function get_user_orders($user_id, $limit = null, $offset = null) {
        $sql = "SELECT o.* FROM orders o WHERE o.user_id = ? ORDER BY o.created_at DESC";
        
        $params = [$user_id];
        
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
    
    public function get_all_orders($limit = null, $offset = null, $status = null) {
        $sql = "SELECT o.*, u.full_name, u.email 
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                WHERE 1=1";
        
        $params = [];
        
        if ($status) {
            $sql .= " AND o.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY o.created_at DESC";
        
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
    
    public function get_order_count($status = null) {
        $sql = "SELECT COUNT(*) as count FROM orders WHERE 1=1";
        $params = [];
        
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        $result = $this->db->fetchRow($sql, $params);
        return $result['count'];
    }
    
    public function update_order_status($order_id, $status) {
        $allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (!in_array($status, $allowed_statuses)) {
            return ['success' => false, 'message' => 'وضعیت سفارش معتبر نیست'];
        }
        
        $result = $this->db->update('orders', ['status' => $status], 'id = ?', [$order_id]);
        
        if ($result > 0) {
            return ['success' => true, 'message' => 'وضعیت سفارش با موفقیت به‌روزرسانی شد'];
        } else {
            return ['success' => false, 'message' => 'خطا در به‌روزرسانی وضعیت سفارش'];
        }
    }
    
    public function update_payment_status($order_id, $payment_status) {
        $allowed_statuses = ['pending', 'paid', 'failed', 'refunded'];
        
        if (!in_array($payment_status, $allowed_statuses)) {
            return ['success' => false, 'message' => 'وضعیت پرداخت معتبر نیست'];
        }
        
        $result = $this->db->update('orders', ['payment_status' => $payment_status], 'id = ?', [$order_id]);
        
        if ($result > 0) {
            return ['success' => true, 'message' => 'وضعیت پرداخت با موفقیت به‌روزرسانی شد'];
        } else {
            return ['success' => false, 'message' => 'خطا در به‌روزرسانی وضعیت پرداخت'];
        }
    }
    
    public function get_order_statistics() {
        $stats = [];
        
        // Total orders
        $stats['total_orders'] = $this->get_order_count();
        
        // Orders by status
        $stats['pending_orders'] = $this->get_order_count('pending');
        $stats['processing_orders'] = $this->get_order_count('processing');
        $stats['shipped_orders'] = $this->get_order_count('shipped');
        $stats['delivered_orders'] = $this->get_order_count('delivered');
        $stats['cancelled_orders'] = $this->get_order_count('cancelled');
        
        // Total revenue
        $sql = "SELECT SUM(total_amount) as total_revenue FROM orders WHERE payment_status = 'paid'";
        $result = $this->db->fetchRow($sql);
        $stats['total_revenue'] = $result['total_revenue'] ?: 0;
        
        // Monthly revenue
        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_amount) as revenue 
                FROM orders 
                WHERE payment_status = 'paid' 
                GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
                ORDER BY month DESC 
                LIMIT 12";
        $stats['monthly_revenue'] = $this->db->fetchAll($sql);
        
        return $stats;
    }
    
    public function cancel_order($order_id, $user_id = null) {
        $order = $this->get_order($order_id, $user_id);
        
        if (!$order) {
            return ['success' => false, 'message' => 'سفارش یافت نشد'];
        }
        
        if ($order['status'] === 'cancelled') {
            return ['success' => false, 'message' => 'سفارش قبلاً لغو شده است'];
        }
        
        if ($order['status'] === 'delivered') {
            return ['success' => false, 'message' => 'سفارش تحویل داده شده و قابل لغو نیست'];
        }
        
        // Update order status
        $result = $this->db->update('orders', ['status' => 'cancelled'], 'id = ?', [$order_id]);
        
        if ($result > 0) {
            // Restore stock
            $order_items = $this->get_order_items($order_id);
            
            foreach ($order_items as $item) {
                $this->db->update('products', [
                    'stock_quantity' => $item['stock_quantity'] + $item['quantity']
                ], 'id = ?', [$item['product_id']]);
            }
            
            return ['success' => true, 'message' => 'سفارش با موفقیت لغو شد'];
        } else {
            return ['success' => false, 'message' => 'خطا در لغو سفارش'];
        }
    }
}

$orders = new Orders();