use moodle;
CREATE TABLE `ods_test_reservation_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `register_id` int(100) NOT NULL,
  `class` int(100) NOT NULL,
  `instructors` text,
  `test_type` char(10) NOT NULL,
  `original_test_time` char(16),
  `test_date` char(10) NOT NULL,
  `test_start_time` char(5) NOT NULL,
  `test_duration` int(11) NOT NULL,
  `testing_instructions` text,
  `accommodation` text,
  `return_type` text,
  `is_valid` int(1),
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)

CREATE TABLE `ods_test_reservation_transaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(10) NOT NULL,
  `executor` int(11),
  `data` text,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)