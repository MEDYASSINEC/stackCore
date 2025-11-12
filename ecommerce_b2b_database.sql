
-- Table des utilisateurs (entreprises)
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  company_name VARCHAR(255),
  email VARCHAR(255) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  address TEXT,
  phone VARCHAR(20),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table des produits
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255),
  description TEXT,
  price DECIMAL(10,2),
  stock INT,
  min_order_quantity INT DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  is_on_promotion BOOLEAN DEFAULT FALSE,
  promotion_price DECIMAL(10,2),
  views INT DEFAULT 0
);

-- Catégories de produits
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100)
);

CREATE TABLE product_category (
  product_id INT,
  category_id INT,
  PRIMARY KEY (product_id, category_id),
  FOREIGN KEY (product_id) REFERENCES products(id),
  FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Panier
CREATE TABLE cart_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  product_id INT,
  quantity INT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Favoris
CREATE TABLE favorites (
  user_id INT,
  product_id INT,
  PRIMARY KEY (user_id, product_id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Avis sur les produits
CREATE TABLE product_reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT,
  user_id INT,
  rating INT CHECK (rating BETWEEN 1 AND 5),
  comment TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Chatbot FAQ
CREATE TABLE chatbot_faq (
  id INT AUTO_INCREMENT PRIMARY KEY,
  question TEXT,
  answer TEXT
);

-- Commandes
CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  total_amount DECIMAL(10,2),
  status VARCHAR(50) DEFAULT 'en attente',
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Détails de commande
CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT,
  product_id INT,
  quantity INT,
  price_at_purchase DECIMAL(10,2),
  FOREIGN KEY (order_id) REFERENCES orders(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Médias produits (images/vidéos)
CREATE TABLE product_media (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT,
  media_url VARCHAR(255),
  media_type ENUM('image', 'video'),
  is_main BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Questions publiques sur les produits
CREATE TABLE product_questions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT,
  user_id INT,
  question TEXT,
  answer TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  answered_at DATETIME,
  FOREIGN KEY (product_id) REFERENCES products(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Informations de livraison pour les commandes
ALTER TABLE orders
ADD delivery_address TEXT,
ADD tracking_number VARCHAR(100),
ADD delivery_status VARCHAR(50) DEFAULT 'en préparation',
ADD estimated_delivery_date DATE;

-- Notifications
CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  content TEXT,
  is_read BOOLEAN DEFAULT FALSE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Vues produits (historique de navigation)
CREATE TABLE product_views (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  product_id INT,
  viewed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Codes promo
CREATE TABLE discount_codes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) UNIQUE,
  description TEXT,
  discount_percent INT CHECK (discount_percent BETWEEN 1 AND 100),
  valid_from DATE,
  valid_until DATE,
  usage_limit INT,
  used_count INT DEFAULT 0,
  min_order_amount DECIMAL(10,2),
  user_id INT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Lier les codes promo aux commandes
ALTER TABLE orders ADD discount_code_id INT;
ALTER TABLE orders ADD FOREIGN KEY (discount_code_id) REFERENCES discount_codes(id);

-- Historique de connexion
CREATE TABLE user_logins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  login_time DATETIME DEFAULT CURRENT_TIMESTAMP,
  ip_address VARCHAR(45),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Facturation
CREATE TABLE invoices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNIQUE,
  invoice_number VARCHAR(50) UNIQUE,
  tva_percent DECIMAL(5,2),
  total_ht DECIMAL(10,2),
  total_ttc DECIMAL(10,2),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  pdf_url VARCHAR(255),
  FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- Support client
CREATE TABLE support_tickets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  subject VARCHAR(255),
  message TEXT,
  status VARCHAR(50) DEFAULT 'ouvert',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);
CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(255) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('superadmin', 'gestionnaire', 'support') DEFAULT 'gestionnaire',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE ticket_responses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ticket_id INT,
  sender_type ENUM('client', 'admin'),
  sender_id INT,
  message TEXT,
  sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (ticket_id) REFERENCES support_tickets(id),
  -- pas de contrainte sur sender_id car il peut venir de users OU admins
  -- tu peux gérer cela dans le backend
);
