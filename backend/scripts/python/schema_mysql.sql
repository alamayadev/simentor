-- MySQL Schema for DIPA Monitoring System (simentordb)

SET SQL_MODE='PIPES_AS_CONCAT'; -- Supports || for concat if needed, but better use CONCAT()

CREATE TABLE IF NOT EXISTS budget_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    revision_name VARCHAR(100) NOT NULL,
    revision_date DATE,
    tahun_anggaran INT,
    program_code VARCHAR(20),
    program_name TEXT,
    activity_code VARCHAR(20),
    activity_name TEXT,
    output_code VARCHAR(20),
    output_name TEXT,
    component_code VARCHAR(10),
    component_name TEXT,
    sub_component_code VARCHAR(10),
    sub_component_name TEXT,
    account_code VARCHAR(10),
    account_name TEXT,
    description TEXT,
    volume DOUBLE,
    unit VARCHAR(100),
    unit_price DOUBLE,
    total_amount DOUBLE,
    funding_source VARCHAR(20),
    composite_key VARCHAR(64),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(revision_name, composite_key, tahun_anggaran),
    INDEX idx_rev (revision_name),
    INDEX idx_rev_date (revision_date),
    INDEX idx_rev_year (tahun_anggaran),
    INDEX idx_comp_key (composite_key)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS imported_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_path TEXT NOT NULL,
    file_hash VARCHAR(64) NOT NULL UNIQUE,
    revision_name VARCHAR(100),
    tahun_anggaran INT,
    imported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS budget_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    budget_item_key VARCHAR(64) NOT NULL,
    usage_date DATE NOT NULL,
    description TEXT,
    amount_spent DOUBLE NOT NULL,
    data_source VARCHAR(16) NOT NULL DEFAULT 'sakti',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (budget_item_key)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS budget_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    budget_item_key VARCHAR(64) NOT NULL,
    target_month DATE NOT NULL,
    description TEXT,
    planned_amount DOUBLE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (budget_item_key),
    INDEX (target_month)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS item_mapping (
    old_composite_key VARCHAR(64) NOT NULL,
    new_composite_key VARCHAR(64) NOT NULL,
    PRIMARY KEY (old_composite_key, new_composite_key)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS sakti_realizations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tahun_anggaran INT NOT NULL,
    composite_key VARCHAR(64) NOT NULL,
    amount_pagu DOUBLE DEFAULT 0,
    amount_sakti DOUBLE DEFAULT 0,
    last_imported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (tahun_anggaran, composite_key),
    INDEX (composite_key)
) ENGINE=InnoDB;

-- VIEWS

CREATE OR REPLACE VIEW v_latest_budget AS
SELECT * 
FROM budget_items 
WHERE (tahun_anggaran, revision_date) IN (
    SELECT tahun_anggaran, MAX(revision_date) 
    FROM budget_items 
    GROUP BY tahun_anggaran
);

CREATE OR REPLACE VIEW v_budget_monitoring AS
WITH mapped_usage AS (
    SELECT 
        COALESCE(m.new_composite_key, u.budget_item_key) as final_key,
        u.amount_spent
    FROM budget_usage u
    LEFT JOIN item_mapping m ON u.budget_item_key = m.old_composite_key
)
SELECT 
    b.id,
    b.tahun_anggaran,
    b.program_code, b.activity_code, b.account_code,
    CONCAT(
        '<small>', COALESCE(b.program_name, ''), '</small><br/>',
        '<small>', COALESCE(b.component_name, ''), '</small><br/>',
        '<b>', COALESCE(b.description, ''), '</b><br/>',
        '<small>', COALESCE(b.account_name, ''), '</small>'
    ) AS formatted_description,
    b.total_amount AS total_pagu,
    COALESCE(SUM(mu.amount_spent), 0) AS total_realisasi,
    (b.total_amount - COALESCE(SUM(mu.amount_spent), 0)) AS sisa_anggaran,
    CASE WHEN b.total_amount > 0 THEN ROUND(COALESCE(SUM(mu.amount_spent), 0) / b.total_amount * 100, 2) ELSE 0 END AS persentase_realisasi,
    b.funding_source,
    b.revision_name,
    b.composite_key,
    b.composite_key AS budget_item_key
FROM v_latest_budget b
LEFT JOIN mapped_usage mu ON b.composite_key = mu.final_key
GROUP BY b.id, b.composite_key, b.tahun_anggaran;

CREATE OR REPLACE VIEW v_sakti_reconciliation AS
WITH aggregated_usage AS (
    SELECT 
        COALESCE(m.new_composite_key, u.budget_item_key) as final_key,
        SUM(u.amount_spent) as total_internal
    FROM budget_usage u
    LEFT JOIN item_mapping m ON u.budget_item_key = m.old_composite_key
    GROUP BY final_key
)
SELECT 
    b.id,
    b.tahun_anggaran,
    b.program_code, 
    b.activity_code, 
    b.account_code,
    b.description,
    b.composite_key,
    COALESCE(s.amount_pagu, 0) as pagu_sakti,
    COALESCE(s.amount_sakti, 0) as realisasi_sakti,
    COALESCE(au.total_internal, 0) as realisasi_internal,
    (COALESCE(s.amount_sakti, 0) - COALESCE(au.total_internal, 0)) as selisih,
    s.last_imported_at
FROM v_latest_budget b
LEFT JOIN sakti_realizations s ON b.composite_key = s.composite_key AND b.tahun_anggaran = s.tahun_anggaran
LEFT JOIN aggregated_usage au ON b.composite_key = au.final_key;

CREATE OR REPLACE VIEW v_usage_history AS
SELECT 
    u.id,
    u.usage_date,
    u.description as usage_description,
    u.amount_spent,
    u.data_source,
    b.revision_name as revision_at_time,
    b.description as budget_item_at_time,
    b.total_amount as pagu_at_time,
    u.budget_item_key
FROM budget_usage u
LEFT JOIN budget_items b ON u.budget_item_key = b.composite_key AND u.usage_date >= b.revision_date;

CREATE OR REPLACE VIEW v_expenditure_orphans AS
SELECT 
    u.budget_item_key,
    u.usage_date,
    u.description,
    u.amount_spent,
    'Item removed or renamed in latest revision' as status
FROM budget_usage u
WHERE u.budget_item_key NOT IN (SELECT composite_key FROM v_latest_budget)
  AND u.budget_item_key NOT IN (SELECT old_composite_key FROM item_mapping);

CREATE OR REPLACE VIEW v_item_picker AS
SELECT 
    CONCAT(program_code, '.', activity_code, '.', component_code, ' - ', account_code) as short_code, 
    CONCAT(
        '<small>', COALESCE(program_name, ''), '</small><br/>',
        '<small>', COALESCE(component_name, ''), '</small><br/>',
        '<b>', COALESCE(description, ''), '</b><br/>',
        '<small>', COALESCE(account_name, ''), '</small>'
    ) AS formatted_description,
    funding_source, 
    composite_key,
    composite_key AS budget_item_key
FROM v_latest_budget;
