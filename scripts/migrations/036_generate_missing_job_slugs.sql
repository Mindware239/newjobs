-- Generate slugs for existing jobs that don't have them
UPDATE jobs 
SET slug = LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
    TRIM(title), 
    ' ', '-'), 
    '&', 'and'), 
    '@', 'at'), 
    '#', ''), 
    '$', ''), 
    '%', ''), 
    '^', ''), 
    '*', ''), 
    '(', ''), 
    ')', '')
)
WHERE slug IS NULL OR slug = '';

-- Handle duplicate slugs by appending job ID
UPDATE jobs j1
INNER JOIN (
    SELECT slug, COUNT(*) as cnt, MIN(id) as first_id
    FROM jobs
    WHERE slug IS NOT NULL AND slug != ''
    GROUP BY slug
    HAVING cnt > 1
) j2 ON j1.slug = j2.slug AND j1.id != j2.first_id
SET j1.slug = CONCAT(j1.slug, '-', j1.id);

