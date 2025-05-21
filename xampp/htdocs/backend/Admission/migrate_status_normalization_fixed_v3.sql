-- Create status table
CREATE TABLE IF NOT EXISTS status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- Insert statuses
INSERT IGNORE INTO status (name) VALUES ('Approved'), ('Pending'), ('Rejected');

-- Set status_id to 'Pending' where it is NULL
UPDATE user_application ua
JOIN status s ON s.name = 'Pending'
SET ua.status_id = s.id
WHERE ua.status_id IS NULL;

-- Optional: Drop old status column if no longer needed
-- ALTER TABLE user_application DROP COLUMN status;

-- Add foreign key constraint
ALTER TABLE user_application
ADD CONSTRAINT fk_status
FOREIGN KEY (status_id) REFERENCES status(id);
