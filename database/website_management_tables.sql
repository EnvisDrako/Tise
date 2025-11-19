-- Website Management Tables
-- Separate from CRM functionality

-- Notices Table
CREATE TABLE IF NOT EXISTS `notices` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `type` ENUM('general', 'important', 'urgent', 'event') DEFAULT 'general',
  `start_date` DATE NOT NULL,
  `end_date` DATE NULL,
  `attachment_path` VARCHAR(255) NULL,
  `attachment_name` VARCHAR(255) NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_by` INT(11) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_dates` (`start_date`, `end_date`),
  KEY `idx_active` (`is_active`),
  KEY `idx_type` (`type`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Downloads Table
CREATE TABLE IF NOT EXISTS `downloads` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `category` ENUM('syllabus', 'forms', 'brochures', 'prospectus', 'results', 'other') DEFAULT 'other',
  `file_type` ENUM('upload', 'link') DEFAULT 'upload',
  `file_path` VARCHAR(500) NULL COMMENT 'File path for uploads',
  `external_link` VARCHAR(500) NULL COMMENT 'External URL for links',
  `file_size` VARCHAR(50) NULL,
  `download_count` INT(11) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_by` INT(11) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_active` (`is_active`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Placements Table
CREATE TABLE IF NOT EXISTS `placements` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `student_name` VARCHAR(255) NOT NULL,
  `company_name` VARCHAR(255) NOT NULL,
  `position` VARCHAR(255) NOT NULL,
  `package` VARCHAR(100) NULL COMMENT 'Salary package',
  `placement_year` YEAR NOT NULL,
  `company_logo` VARCHAR(255) NULL,
  `description` TEXT NULL,
  `is_featured` TINYINT(1) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_by` INT(11) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_year` (`placement_year`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_active` (`is_active`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data for testing
INSERT INTO `notices` (`title`, `content`, `type`, `start_date`, `end_date`, `created_by`) VALUES
('Welcome to Taset Academy', 'We are pleased to announce the opening of new courses for the upcoming semester.', 'general', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 1),
('Important: Exam Schedule Released', 'The examination schedule for all courses has been released. Please check your respective portals.', 'important', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 15 DAY), 1);

INSERT INTO `downloads` (`title`, `description`, `category`, `file_type`, `external_link`, `created_by`) VALUES
('Course Syllabus 2024', 'Complete syllabus for all courses offered in 2024', 'syllabus', 'link', 'https://example.com/syllabus.pdf', 1),
('Admission Form', 'Download the admission application form', 'forms', 'link', 'https://example.com/admission-form.pdf', 1);

INSERT INTO `placements` (`student_name`, `company_name`, `position`, `package`, `placement_year`, `created_by`) VALUES
('John Doe', 'Tech Corp', 'Software Engineer', '12 LPA', YEAR(CURDATE()), 1),
('Jane Smith', 'Digital Solutions', 'Data Analyst', '10 LPA', YEAR(CURDATE()), 1);
