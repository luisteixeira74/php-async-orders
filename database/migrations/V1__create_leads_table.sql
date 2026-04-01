CREATE TABLE leads (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255),
    message TEXT,
    intent VARCHAR(50),
    priority VARCHAR(20),
    score FLOAT,
    suggested_action VARCHAR(255),
    created_at DATETIME
);