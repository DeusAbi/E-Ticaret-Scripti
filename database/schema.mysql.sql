CREATE TABLE IF NOT EXISTS settings (
    setting_key   VARCHAR(100) NOT NULL PRIMARY KEY,
    setting_value MEDIUMTEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(190) NOT NULL,
    email         VARCHAR(190) NOT NULL UNIQUE,
    phone         VARCHAR(40),
    password      VARCHAR(255) NOT NULL,
    role          VARCHAR(20) NOT NULL DEFAULT 'customer',
    is_active     INT NOT NULL DEFAULT 1,
    newsletter    INT NOT NULL DEFAULT 0,
    last_login_at DATETIME NULL,
    created_at    DATETIME NOT NULL,
    updated_at    DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_resets (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(190) NOT NULL,
    token_hash VARCHAR(128) NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_password_resets_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_attempts (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ip         VARCHAR(64) NOT NULL,
    email      VARCHAR(190),
    created_at DATETIME NOT NULL,
    INDEX idx_login_attempts_ip (ip, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS addresses (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    title       VARCHAR(190) NOT NULL,
    full_name   VARCHAR(190) NOT NULL,
    phone       VARCHAR(40) NOT NULL,
    city        VARCHAR(100) NOT NULL,
    district    VARCHAR(100) NOT NULL,
    address     TEXT NOT NULL,
    postal_code VARCHAR(20),
    is_default  INT NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL,
    INDEX idx_addresses_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    parent_id   INT UNSIGNED,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    name        VARCHAR(190) NOT NULL,
    slug        VARCHAR(190) NOT NULL UNIQUE,
    description TEXT,
    icon        VARCHAR(60),
    image       VARCHAR(255),
    sort_order  INT NOT NULL DEFAULT 0,
    is_active   INT NOT NULL DEFAULT 1,
    created_at  DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
    id                  INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    category_id         INT UNSIGNED,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    name                VARCHAR(190) NOT NULL,
    slug                VARCHAR(190) NOT NULL UNIQUE,
    sku                 VARCHAR(100) UNIQUE,
    barcode             VARCHAR(100),
    brand               VARCHAR(120),
    short_description   TEXT,
    description         TEXT,
    specs               TEXT,
    price               DECIMAL(12,2) NOT NULL DEFAULT 0,
    compare_price       DECIMAL(12,2),
    cost_price          DECIMAL(12,2),
    tax_rate            DECIMAL(12,2) NOT NULL DEFAULT 20,
    stock               INT NOT NULL DEFAULT 0,
    low_stock_threshold INT NOT NULL DEFAULT 5,
    status              VARCHAR(30) NOT NULL DEFAULT 'active',
    is_featured         INT NOT NULL DEFAULT 0,
    views               INT NOT NULL DEFAULT 0,
    sales_count         INT NOT NULL DEFAULT 0,
    meta_title          VARCHAR(255),
    meta_description    TEXT,
    created_at          DATETIME NOT NULL,
    updated_at          DATETIME NULL,
    INDEX idx_products_category (category_id),
    INDEX idx_products_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_images (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    path       VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    INDEX idx_product_images_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reviews (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    product_id  INT UNSIGNED NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    user_id     INT UNSIGNED,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    author_name VARCHAR(190) NOT NULL,
    rating      INT NOT NULL,
    title       VARCHAR(190),
    comment     TEXT NOT NULL,
    is_approved INT NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL,
    INDEX idx_reviews_product (product_id, is_approved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS wishlists (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    product_id INT UNSIGNED NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    created_at DATETIME NOT NULL,
    UNIQUE (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS coupons (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    code       VARCHAR(64) NOT NULL UNIQUE,
    type       VARCHAR(20) NOT NULL DEFAULT 'percent',
    value      DECIMAL(12,2) NOT NULL DEFAULT 0,
    min_total  DECIMAL(12,2) NOT NULL DEFAULT 0,
    max_uses   INT NOT NULL DEFAULT 0,
    used_count INT NOT NULL DEFAULT 0,
    starts_at  DATETIME NULL,
    expires_at DATETIME NULL,
    is_active  INT NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    order_no        VARCHAR(40) NOT NULL UNIQUE,
    user_id         INT UNSIGNED,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    status          VARCHAR(30) NOT NULL DEFAULT 'pending',
    payment_method  VARCHAR(30) NOT NULL,
    payment_status  VARCHAR(30) NOT NULL DEFAULT 'pending',
    payment_ref     VARCHAR(190),
    subtotal        DECIMAL(12,2) NOT NULL DEFAULT 0,
    discount        DECIMAL(12,2) NOT NULL DEFAULT 0,
    shipping_fee    DECIMAL(12,2) NOT NULL DEFAULT 0,
    payment_fee     DECIMAL(12,2) NOT NULL DEFAULT 0,
    tax_total       DECIMAL(12,2) NOT NULL DEFAULT 0,
    total           DECIMAL(12,2) NOT NULL DEFAULT 0,
    coupon_code     VARCHAR(64),
    customer_name   VARCHAR(190) NOT NULL,
    customer_email  VARCHAR(190) NOT NULL,
    customer_phone  VARCHAR(40) NOT NULL,
    ship_city       VARCHAR(100) NOT NULL,
    ship_district   VARCHAR(100) NOT NULL,
    ship_address    TEXT NOT NULL,
    ship_postal     VARCHAR(20),
    invoice_type    VARCHAR(20) NOT NULL DEFAULT 'individual',
    invoice_company VARCHAR(190),
    invoice_tax_office VARCHAR(120),
    invoice_tax_no  VARCHAR(40),
    note            TEXT,
    cargo_company   VARCHAR(100),
    tracking_no     VARCHAR(100),
    admin_note      TEXT,
    ip_address      VARCHAR(64),
    created_at      DATETIME NOT NULL,
    updated_at      DATETIME NULL,
    INDEX idx_orders_user (user_id),
    INDEX idx_orders_status (status),
    INDEX idx_orders_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    order_id     INT UNSIGNED NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    product_id   INT UNSIGNED,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    product_name VARCHAR(255) NOT NULL,
    sku          VARCHAR(100),
    image        VARCHAR(255),
    price        DECIMAL(12,2) NOT NULL,
    quantity     INT NOT NULL,
    total        DECIMAL(12,2) NOT NULL,
    INDEX idx_order_items_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_history (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    order_id   INT UNSIGNED NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    status     VARCHAR(30) NOT NULL,
    note       TEXT,
    created_by INT,
    created_at DATETIME NOT NULL,
    INDEX idx_order_history_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pages (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title      VARCHAR(190) NOT NULL,
    slug       VARCHAR(190) NOT NULL UNIQUE,
    content    TEXT,
    show_in_footer INT NOT NULL DEFAULT 1,
    is_active  INT NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sliders (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    eyebrow     VARCHAR(190),
    title       VARCHAR(190) NOT NULL,
    subtitle    TEXT,
    button_text VARCHAR(100),
    button_link VARCHAR(255),
    image       VARCHAR(255),
    theme       VARCHAR(20) NOT NULL DEFAULT 'dark',
    bg_color    VARCHAR(20) NOT NULL DEFAULT '#111827',
    sort_order  INT NOT NULL DEFAULT 0,
    is_active   INT NOT NULL DEFAULT 1,
    created_at  DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS messages (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(190) NOT NULL,
    email      VARCHAR(190) NOT NULL,
    phone      VARCHAR(40),
    subject    VARCHAR(190) NOT NULL,
    message    TEXT NOT NULL,
    is_read    INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(190) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
