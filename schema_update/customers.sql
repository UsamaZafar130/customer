-- Create cart table for logged-in users
CREATE TABLE customer_cart (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    item_id INT(11) NULL,
    meal_id INT(11) NULL,
    qty DECIMAL(10,2) NOT NULL DEFAULT '1.00',
    pack_size DECIMAL(10,2) NOT NULL DEFAULT '1.00',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user_id (user_id),
    KEY idx_item_id (item_id),
    KEY idx_meal_id (meal_id),
    FOREIGN KEY (user_id) REFERENCES customer_users(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
    FOREIGN KEY (meal_id) REFERENCES meals(id) ON DELETE CASCADE,
    CONSTRAINT chk_cart_item_or_meal CHECK ((item_id IS NOT NULL AND meal_id IS NULL) OR (item_id IS NULL AND meal_id IS NOT NULL))
);