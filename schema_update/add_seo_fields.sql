-- Add SEO fields to items table
ALTER TABLE items ADD COLUMN seo_title VARCHAR(255) NULL AFTER image;
ALTER TABLE items ADD COLUMN seo_short_description TEXT NULL AFTER seo_title;
ALTER TABLE items ADD COLUMN seo_description TEXT NULL AFTER seo_short_description;
ALTER TABLE items ADD COLUMN slug VARCHAR(255) NULL AFTER seo_description;
ALTER TABLE items ADD UNIQUE KEY unique_item_slug (slug);

-- Add SEO fields to meals table
ALTER TABLE meals ADD COLUMN seo_title VARCHAR(255) NULL AFTER deleted_at;
ALTER TABLE meals ADD COLUMN seo_short_description TEXT NULL AFTER seo_title;
ALTER TABLE meals ADD COLUMN seo_description TEXT NULL AFTER seo_short_description;
ALTER TABLE meals ADD COLUMN slug VARCHAR(255) NULL AFTER seo_description;
ALTER TABLE meals ADD UNIQUE KEY unique_meal_slug (slug);

-- Update existing items with basic SEO data (only for items with images)
UPDATE items SET 
    seo_title = name,
    seo_short_description = CONCAT(name, ' - Premium frozen product, ', default_pack_size, ' pieces per pack'),
    seo_description = CONCAT(name, ' - High quality frozen product. Package contains ', default_pack_size, ' pieces. Price: Rs. ', (price_per_unit * default_pack_size), ' per pack.'),
    slug = LOWER(REPLACE(REPLACE(REPLACE(REPLACE(name, ' ', '-'), '&', 'and'), '.', ''), ',', ''))
WHERE image IS NOT NULL AND deleted_at IS NULL;

-- Update existing meals with basic SEO data 
UPDATE meals SET 
    seo_title = name,
    seo_short_description = CONCAT(name, ' - Ready-to-eat meal, Rs. ', price),
    seo_description = CONCAT(name, ' - Delicious ready-to-eat meal prepared with premium ingredients. Price: Rs. ', price, ' per meal.'),
    slug = LOWER(REPLACE(REPLACE(REPLACE(REPLACE(name, ' ', '-'), '&', 'and'), '.', ''), ',', ''))
WHERE active = 1 AND deleted_at IS NULL;