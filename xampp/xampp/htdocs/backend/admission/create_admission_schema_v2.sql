CREATE TABLE IF NOT EXISTS Users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    uid VARCHAR(255) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS user_application (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    dob DATE NOT NULL,
    course VARCHAR(255) NOT NULL,
    contact VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    guardianName VARCHAR(255) NOT NULL,
    guardianRelation VARCHAR(255) NOT NULL,
    guardianAddress TEXT NOT NULL,
    status VARCHAR(50) DEFAULT NULL,
    status_id INT DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES Users(id),
    FOREIGN KEY (status_id) REFERENCES status(id)
);
