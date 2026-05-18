CREATE DATABASE IF NOT EXISTS auction_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE auction_system;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS messages, announcements, commission_rates, platform_fees, auction_templates, warnings, user_reports, listing_reports, reviews, watchlist, auto_bids, bids, listing_images, seller_verification_requests, listings, categories, users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(30),
    bio TEXT,
    profile_pic VARCHAR(255),
    role ENUM('buyer','seller','moderator','admin') NOT NULL DEFAULT 'buyer',
    seller_verified TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    reputation_score DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    parent_id INT NULL,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    `condition` ENUM('new','like_new','good','fair') NOT NULL,
    starting_price DECIMAL(10,2) NOT NULL,
    reserve_price DECIMAL(10,2) NULL,
    current_bid DECIMAL(10,2) DEFAULT 0.00,
    end_datetime DATETIME NOT NULL,
    status ENUM('pending_review','active','ended','cancelled','rejected') DEFAULT 'pending_review',
    winner_bid_id INT NULL,
    rejection_reason TEXT,
    cancel_reason TEXT,
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB;

CREATE TABLE listing_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    display_order INT DEFAULT 0,
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE bids (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    buyer_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    is_auto_bid TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(id)
) ENGINE=InnoDB;

ALTER TABLE listings ADD CONSTRAINT fk_winner_bid FOREIGN KEY (winner_bid_id) REFERENCES bids(id) ON DELETE SET NULL;

CREATE TABLE auto_bids (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    buyer_id INT NOT NULL,
    max_amount DECIMAL(10,2) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    UNIQUE KEY unique_auto_bid (listing_id, buyer_id),
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE watchlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    listing_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_watchlist (buyer_id, listing_id),
    FOREIGN KEY (buyer_id) REFERENCES users(id),
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE seller_verification_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    motivation TEXT NOT NULL, 
    id_document_path VARCHAR(255) NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    reviewed_by INT NULL,
    reject_reason TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    reviewee_id INT NOT NULL,
    rating TINYINT NOT NULL,
    review_text TEXT NOT NULL,
    response_text TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES listings(id),
    FOREIGN KEY (reviewer_id) REFERENCES users(id),
    FOREIGN KEY (reviewee_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE listing_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    reporter_id INT NOT NULL,
    reason VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('pending','resolved','dismissed') DEFAULT 'pending',
    moderator_note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES listings(id),
    FOREIGN KEY (reporter_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE user_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reporter_id INT NOT NULL,
    reported_user_id INT NOT NULL,
    reason VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('pending','resolved','escalated','dismissed') DEFAULT 'pending',
    moderator_note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES users(id),
    FOREIGN KEY (reported_user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE warnings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    issued_by INT NOT NULL,
    reason TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (issued_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE auction_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    category_id INT NOT NULL,
    `condition` ENUM('new','like_new','good','fair') NOT NULL,
    starting_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (seller_id) REFERENCES users(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB;

CREATE TABLE platform_fees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    seller_id INT NOT NULL,
    final_price DECIMAL(10,2) NOT NULL,
    commission_rate DECIMAL(5,2) NOT NULL,
    commission_amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES listings(id),
    FOREIGN KEY (seller_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE commission_rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NULL,
    rate DECIMAL(5,2) NOT NULL,
    is_default TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    posted_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (posted_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    listing_id INT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id),
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO users (name,email,password_hash,phone,bio,role,seller_verified,is_active,reputation_score) VALUES
('Admin User','admin@example.com','$2y$12$o9XW.x/V7X/mbiaGDNZooeksHuyyq2kSaRuuxdcudj71UsA4KPwUu','01700000000','Platform administrator','admin',0,1,5.00),
('Moderator User','moderator@example.com','$2y$12$o9XW.x/V7X/mbiaGDNZooeksHuyyq2kSaRuuxdcudj71UsA4KPwUu','01700000001','Platform moderator','moderator',0,1,4.80),
('Verified Seller','seller@example.com','$2y$12$o9XW.x/V7X/mbiaGDNZooeksHuyyq2kSaRuuxdcudj71UsA4KPwUu','01700000002','I sell quality used electronics.','seller',1,1,4.70),
('Demo Buyer','buyer@example.com','$2y$12$o9XW.x/V7X/mbiaGDNZooeksHuyyq2kSaRuuxdcudj71UsA4KPwUu','01700000003','Auction buyer account','buyer',0,1,4.50);

INSERT INTO categories (name, description, parent_id) VALUES
('Electronics','Phones, laptops, gadgets',NULL),
('Books','Academic and general books',NULL),
('Furniture','Home and office furniture',NULL),
('Mobile Phones','Smartphones and accessories',1),
('Laptops','Laptop computers',1);

INSERT INTO listings (seller_id,category_id,title,description,`condition`,starting_price,reserve_price,current_bid,end_datetime,status,is_featured) VALUES
(3,4,'iPhone 12 Used','Good condition iPhone 12 with charger.','good',25000,30000,25000,DATE_ADD(NOW(), INTERVAL 2 DAY),'active',1),
(3,5,'Dell Latitude Laptop','Business laptop, 8GB RAM, SSD.','like_new',18000,22000,18000,DATE_ADD(NOW(), INTERVAL 3 DAY),'active',0),
(3,2,'Database Systems Book','Clean copy for university courses.','good',500,NULL,500,DATE_ADD(NOW(), INTERVAL 1 DAY),'pending_review',0);

INSERT INTO listing_images (listing_id,image_path,display_order) VALUES
(1,'assets/images/auction-placeholder.png',1),
(2,'assets/images/auction-placeholder.png',1),
(3,'assets/images/auction-placeholder.png',1);

INSERT INTO commission_rates (seller_id, rate, is_default) VALUES (NULL, 5.00, 1);
INSERT INTO announcements (title,message,posted_by) VALUES ('Welcome','Welcome to the Online Auction System demo.',1);
