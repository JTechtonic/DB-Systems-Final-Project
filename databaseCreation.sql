-- Create the database
CREATE DATABASE IF NOT EXISTS mainDB;
USE mainDB;

-- Create Universities table
CREATE TABLE Universities (
    university_id INT PRIMARY KEY,
    name VARCHAR(255) UNIQUE NOT NULL,
    location TEXT NOT NULL,
    description TEXT,
    email_suffix VARCHAR(255) NOT NULL
);

-- Create Users table
CREATE TABLE Users (
    user_id INT PRIMARY KEY,
    university_id INT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    user_level ENUM('super_admin', 'admin', 'student') NOT NULL,
    FOREIGN KEY (university_id) REFERENCES Universities(university_id) ON DELETE SET NULL
);

-- Create RSOs table
CREATE TABLE RSOs (
    rso_id INT PRIMARY KEY,
    university_id INT,
    name VARCHAR(255) UNIQUE NOT NULL,
    admin_id INT,
    active BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (university_id) REFERENCES Universities(university_id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- Create RSO_Members table
CREATE TABLE RSO_Members (
    rso_id INT,
    user_id INT,
    PRIMARY KEY (rso_id, user_id),
    FOREIGN KEY (rso_id) REFERENCES RSOs(rso_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- Create Locations table
CREATE TABLE Locations (
    location_id INT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    latitude DECIMAL(9,6) NOT NULL,
    longitude DECIMAL(9,6) NOT NULL
);

-- Create Events table
CREATE TABLE Events (
    event_id INT PRIMARY KEY,
    university_id INT,
    location_id INT NOT NULL,
    rso_id INT,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    time TIME NOT NULL,
    date DATE NOT NULL,
    contact_phone VARCHAR(20),
    contact_email VARCHAR(255),
    visibility ENUM('public', 'private', 'rso') NOT NULL,
    approval_status BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (university_id) REFERENCES Universities(university_id) ON DELETE SET NULL,
    FOREIGN KEY (location_id) REFERENCES Locations(location_id) ON DELETE CASCADE,
    FOREIGN KEY (rso_id) REFERENCES RSOs(rso_id) ON DELETE SET NULL
);

-- Create Public_Events table
CREATE TABLE Public_Events (
    event_id INT PRIMARY KEY,
    created_by INT,
    FOREIGN KEY (event_id) REFERENCES Events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES Users(user_id) ON DELETE SET NULL
);

-- Create Private_Events table
CREATE TABLE Private_Events (
    event_id INT PRIMARY KEY,
    created_by INT,
    FOREIGN KEY (event_id) REFERENCES Events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES Users(user_id) ON DELETE SET NULL
);

-- Create RSO_Events table
CREATE TABLE RSO_Events (
    event_id INT PRIMARY KEY,
    rso_id INT,
    created_by INT,
    FOREIGN KEY (event_id) REFERENCES Events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (rso_id) REFERENCES RSOs(rso_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES Users(user_id) ON DELETE SET NULL
);

-- Create Comments table
CREATE TABLE Comments (
    comment_id INT PRIMARY KEY,
    event_id INT,
    user_id INT,
    text TEXT NOT NULL,
    time TIME NOT NULL,
    date DATE NOT NULL,
    FOREIGN KEY (event_id) REFERENCES Events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- Trigger: Activate RSO if it has more than 4 members
DELIMITER $$

CREATE TRIGGER check_rso_activation
AFTER INSERT ON RSO_Members
FOR EACH ROW
BEGIN
    DECLARE member_count INT;

    SELECT COUNT(*) INTO member_count
    FROM RSO_Members
    WHERE rso_id = NEW.rso_id;

    IF member_count > 4 THEN
        UPDATE RSOs
        SET active = TRUE
        WHERE rso_id = NEW.rso_id;
    END IF;
END $$

DELIMITER ;

-- Trigger: Deactivate RSO if it has fewer than 5 members
DELIMITER $$

CREATE TRIGGER deactivate_rso
AFTER DELETE ON RSO_Members
FOR EACH ROW
BEGIN
    DECLARE member_count INT;

    SELECT COUNT(*) INTO member_count
    FROM RSO_Members
    WHERE rso_id = OLD.rso_id;

    IF member_count < 5 THEN
        UPDATE RSOs
        SET active = FALSE
        WHERE rso_id = OLD.rso_id;
    END IF;
END $$

DELIMITER ;

-- Trigger: Prevent overlapping events within 1 hour
DELIMITER $$

CREATE TRIGGER check_event_time_before_insert
BEFORE INSERT ON Events
FOR EACH ROW
BEGIN
    IF EXISTS (
        SELECT 1 FROM Events
        WHERE date = NEW.date
        AND ABS(TIMESTAMPDIFF(MINUTE, time, NEW.time)) < 60
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Another event is already scheduled within 1 hour on this date.';
    END IF;
END $$

DELIMITER ;
