-- -----------------------------------------------------
-- Schema boongo
--
-- Datamodel for the "Boongo" platform
-- === Copyright (c) Reborn 2026 ===
-- = Designed by Xsam Technologies =
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Table `countries`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `countries` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `country_name` VARCHAR(255) NOT NULL,
  `country_phone_code` VARCHAR(45) NULL,
  `country_lang_code` VARCHAR(45) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_countries_UNIQUE` (`id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `currencies`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `currencies` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `currency_name` JSON NOT NULL,
  `currency_acronym` VARCHAR(45) NOT NULL,
  `currency_icon` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idcurrencies_UNIQUE` (`id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `groups`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `groups` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `group_name` VARCHAR(255) NOT NULL,
  `group_description` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_groups_UNIQUE` (`id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `statuses`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `statuses` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `status_name` JSON NOT NULL,
  `status_description` TEXT NULL,
  `icon` VARCHAR(45) NULL,
  `color` VARCHAR(45) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `group_id` BIGINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_statuses_UNIQUE` (`id` ASC),
  INDEX `fk_statuses_groups_idx` (`group_id` ASC),
  CONSTRAINT `fk_statuses_groups`
    FOREIGN KEY (`group_id`)
    REFERENCES `groups` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `firstname` VARCHAR(255) NULL,
  `lastname` VARCHAR(255) NULL,
  `surname` VARCHAR(255) NULL,
  `gender` VARCHAR(45) NULL,
  `birthdate` DATE NULL,
  `city` VARCHAR(45) NULL,
  `address_1` TEXT NULL,
  `address_2` TEXT NULL,
  `p_o_box` VARCHAR(45) NULL,
  `email` VARCHAR(255) NULL,
  `phone` VARCHAR(255) NULL,
  `username` VARCHAR(255) NULL,
  `password` TEXT NULL,
  `email_verified_at` DATETIME NULL,
  `phone_verified_at` DATETIME NULL,
  `remember_token` TEXT NULL,
  `api_token` TEXT NULL,
  `avatar_url` TEXT NULL,
  `email_frequency` TINYINT NOT NULL DEFAULT 100,
  `two_factor_secret` TEXT NULL,
  `two_factor_recovery_codes` TEXT NULL,
  `two_factor_confirmed_at` TIMESTAMP NULL,
  `two_factor_phone_confirmed_at` TIMESTAMP NULL,
  `is_incognito` TINYINT NOT NULL DEFAULT 0,
  `promo_code` VARCHAR(45) NULL,
  `is_promoted` TINYINT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `country_id` BIGINT NULL,
  `currency_id` BIGINT NULL,
  `status_id` BIGINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_users_UNIQUE` (`id` ASC),
  INDEX `fk_users_countries_idx` (`country_id` ASC),
  INDEX `fk_users_currencies_idx` (`currency_id` ASC),
  INDEX `fk_users_statuses_idx` (`status_id` ASC),
  CONSTRAINT `fk_users_countries`
    FOREIGN KEY (`country_id`)
    REFERENCES `countries` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_users_currencies`
    FOREIGN KEY (`currency_id`)
    REFERENCES `currencies` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_users_statuses`
    FOREIGN KEY (`status_id`)
    REFERENCES `statuses` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `roles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `roles` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `role_name` VARCHAR(255) NOT NULL,
  `role_description` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_roles_UNIQUE` (`id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `role_user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `role_user` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `role_id` BIGINT NOT NULL,
  `user_id` BIGINT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_roleuser_UNIQUE` (`id` ASC),
  INDEX `fk_roleuser_roles_idx` (`role_id` ASC),
  INDEX `fk_roleuser_users_idx` (`user_id` ASC),
  CONSTRAINT `fk_roleuser_roles`
    FOREIGN KEY (`role_id`)
    REFERENCES `roles` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_roleuser_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `types`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `types` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `type_name` JSON NOT NULL,
  `type_description` TEXT NULL,
  `alias` VARCHAR(45) NULL,
  `icon` VARCHAR(45) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `group_id` BIGINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_types_UNIQUE` (`id` ASC),
  INDEX `fk_types_groups_idx` (`group_id` ASC),
  CONSTRAINT `fk_types_groups`
    FOREIGN KEY (`group_id`)
    REFERENCES `groups` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `organizations`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `organizations` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `org_name` TEXT NULL,
  `org_acronym` VARCHAR(255) NULL,
  `org_description` LONGTEXT NULL,
  `id_number` VARCHAR(255) NULL,
  `phone` VARCHAR(255) NULL,
  `email` VARCHAR(255) NULL,
  `address` TEXT NULL,
  `p_o_box` VARCHAR(45) NULL,
  `legal_status` VARCHAR(255) NULL,
  `year_of_creation` VARCHAR(45) NULL,
  `website_url` TEXT NULL,
  `cover_url` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type_id` BIGINT NULL,
  `status_id` BIGINT NULL,
  `user_id` BIGINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_organizations_UNIQUE` (`id` ASC),
  INDEX `fk_organizations_types_idx` (`type_id` ASC),
  INDEX `fk_organizations_statuses_idx` (`status_id` ASC),
  INDEX `fk_organizations_users_idx` (`user_id` ASC),
  CONSTRAINT `fk_organizations_types`
    FOREIGN KEY (`type_id`)
    REFERENCES `types` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_organizations_statuses`
    FOREIGN KEY (`status_id`)
    REFERENCES `statuses` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_organizations_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `categories`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `category_name` JSON NOT NULL,
  `category_description` TEXT NULL,
  `icon` VARCHAR(45) NULL,
  `color` VARCHAR(45) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `group_id` BIGINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_categories_UNIQUE` (`id` ASC),
  INDEX `fk_categories_groups_idx` (`group_id` ASC),
  CONSTRAINT `fk_categories_groups`
    FOREIGN KEY (`group_id`)
    REFERENCES `groups` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `works`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `works` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `work_title` TEXT NULL,
  `work_content` LONGTEXT NULL,
  `work_url` TEXT NULL,
  `video_source` ENUM('YouTube', 'AWS') NOT NULL DEFAULT 'YouTube',
  `media_length` INT NULL,
  `author` VARCHAR(255) NULL,
  `editor` VARCHAR(255) NULL,
  `is_public` TINYINT NOT NULL DEFAULT 0,
  `consultation_price` DECIMAL(9,2) NULL,
  `number_of_hours` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `currency_id` BIGINT NULL,
  `type_id` BIGINT NULL,
  `status_id` BIGINT NULL,
  `user_id` BIGINT NULL,
  `organization_id` BIGINT NULL,
  `category_id` BIGINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_works_UNIQUE` (`id` ASC),
  INDEX `fk_works_currencies_idx` (`currency_id` ASC),
  INDEX `fk_works_types1_idx` (`type_id` ASC),
  INDEX `fk_works_statuses1_idx` (`status_id` ASC),
  INDEX `fk_works_users1_idx` (`user_id` ASC),
  INDEX `fk_works_organizations1_idx` (`organization_id` ASC),
  INDEX `fk_works_categories1_idx` (`category_id` ASC),
  CONSTRAINT `fk_works_currencies`
    FOREIGN KEY (`currency_id`)
    REFERENCES `currencies` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_works_types1`
    FOREIGN KEY (`type_id`)
    REFERENCES `types` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_works_statuses1`
    FOREIGN KEY (`status_id`)
    REFERENCES `statuses` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_works_users1`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_works_organizations1`
    FOREIGN KEY (`organization_id`)
    REFERENCES `organizations` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_works_categories1`
    FOREIGN KEY (`category_id`)
    REFERENCES `categories` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `category_work`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `category_work` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `category_id` BIGINT NOT NULL,
  `work_id` BIGINT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_categorywork_UNIQUE` (`id` ASC),
  INDEX `fk_categorywork_categories_idx` (`category_id` ASC),
  INDEX `fk_categorywork_works_idx` (`work_id` ASC),
  CONSTRAINT `fk_categorywork_categories`
    FOREIGN KEY (`category_id`)
    REFERENCES `categories` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_categorywork_works`
    FOREIGN KEY (`work_id`)
    REFERENCES `works` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `course_years`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `course_years` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `year` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_courseyears_UNIQUE` (`id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `programs`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `programs` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `class` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `course_year_id` BIGINT NULL,
  `organization_id` BIGINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_programs_UNIQUE` (`id` ASC),
  INDEX `fk_programs_courseyears_idx` (`course_year_id` ASC),
  INDEX `fk_programs_organizations_idx` (`organization_id` ASC),
  CONSTRAINT `fk_programs_courseyears`
    FOREIGN KEY (`course_year_id`)
    REFERENCES `course_years` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_programs_organizations`
    FOREIGN KEY (`organization_id`)
    REFERENCES `organizations` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `circles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `circles` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `circle_name` LONGTEXT NOT NULL,
  `profile_url` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type_id` BIGINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_circles_UNIQUE` (`id` ASC),
  INDEX `fk_circles_types_idx` (`type_id` ASC),
  CONSTRAINT `fk_circles_types`
    FOREIGN KEY (`type_id`)
    REFERENCES `types` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `events`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `events` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `event_title` TEXT NULL,
  `former_event_title` TEXT NULL,
  `event_description` LONGTEXT NULL,
  `start_at` DATETIME NULL,
  `end_at` DATETIME NULL,
  `event_place` TEXT NULL,
  `event_place_address` TEXT NULL,
  `cover_url` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type_id` BIGINT NULL,
  `status_id` BIGINT NULL,
  `organization_id` BIGINT NOT NULL,
  `category_id` BIGINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_events_UNIQUE` (`id` ASC),
  INDEX `fk_events_types_idx` (`type_id` ASC),
  INDEX `fk_events_statuses_idx` (`status_id` ASC),
  INDEX `fk_events_organizations_idx` (`organization_id` ASC),
  INDEX `fk_events_categories_idx` (`category_id` ASC),
  CONSTRAINT `fk_events_types`
    FOREIGN KEY (`type_id`)
    REFERENCES `types` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_events_statuses`
    FOREIGN KEY (`status_id`)
    REFERENCES `statuses` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_events_organizations`
    FOREIGN KEY (`organization_id`)
    REFERENCES `organizations` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_events_categories`
    FOREIGN KEY (`category_id`)
    REFERENCES `categories` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `messages`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `messages` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `message_content` LONGTEXT NOT NULL,
  `doc_title` TEXT NULL,
  `doc_uri` TEXT NULL,
  `doc_page` BIGINT NULL,
  `answered_for` BIGINT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type_id` BIGINT NULL,
  `status_id` BIGINT NULL,
  `user_id` BIGINT NULL,
  `addressee_user_id` BIGINT NULL,
  `addressee_organization_id` BIGINT NULL,
  `addressee_circle_id` BIGINT NULL,
  `event_id` BIGINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_messages_UNIQUE` (`id` ASC),
  INDEX `fk_messages_types_idx` (`type_id` ASC),
  INDEX `fk_messages_statuses_idx` (`status_id` ASC),
  INDEX `fk_messages_users_idx` (`user_id` ASC),
  INDEX `fk_messages_addressee_users_idx` (`addressee_user_id` ASC),
  INDEX `fk_messages_addressee_organizations_idx` (`addressee_organization_id` ASC),
  INDEX `fk_messages_addressee_circles_idx` (`addressee_circle_id` ASC),
  INDEX `fk_messages_events_idx` (`event_id` ASC),
  CONSTRAINT `fk_messages_types`
    FOREIGN KEY (`type_id`)
    REFERENCES `types` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_messages_statuses`
    FOREIGN KEY (`status_id`)
    REFERENCES `statuses` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_messages_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_messages_addressee_users`
    FOREIGN KEY (`addressee_user_id`)
    REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_messages_addressee_organizations`
    FOREIGN KEY (`addressee_organization_id`)
    REFERENCES `organizations` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_messages_addressee_circles`
    FOREIGN KEY (`addressee_circle_id`)
    REFERENCES `circles` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_messages_events`
    FOREIGN KEY (`event_id`)
    REFERENCES `events` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `files`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `files` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `file_name` TEXT NULL,
  `file_url` TEXT NULL,
  `part_title` TEXT NULL,
  `part_cover_url` TEXT NULL,
  `position` INT NULL,
  `media_length` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type_id` BIGINT NULL,
  `work_id` BIGINT NULL,
  `program_id` BIGINT NULL,
  `message_id` BIGINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_files_UNIQUE` (`id` ASC),
  INDEX `fk_files_types_idx` (`type_id` ASC),
  INDEX `fk_files_works_idx` (`work_id` ASC),
  INDEX `fk_files_programs_idx` (`program_id` ASC),
  INDEX `fk_files_messages_idx` (`message_id` ASC),
  CONSTRAINT `fk_files_types`
    FOREIGN KEY (`type_id`)
    REFERENCES `types` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_files_works`
    FOREIGN KEY (`work_id`)
    REFERENCES `works` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_files_programs`
    FOREIGN KEY (`program_id`)
    REFERENCES `programs` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_files_messages`
    FOREIGN KEY (`message_id`)
    REFERENCES `messages` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `payments`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `reference` VARCHAR(45) NULL,
  `provider_reference` VARCHAR(45) NULL,
  `order_number` TEXT NULL,
  `amount` DECIMAL(9,2) NULL,
  `amount_customer` DECIMAL(9,2) NULL,
  `phone` VARCHAR(45) NULL,
  `currency` VARCHAR(45) NULL,
  `channel` VARCHAR(45) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type_id` BIGINT NULL,
  `status_id` BIGINT NULL,
  `user_id` BIGINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_payments_UNIQUE` (`id` ASC),
  INDEX `fk_payments_types_idx` (`type_id` ASC),
  INDEX `fk_payments_statuses_idx` (`status_id` ASC),
  INDEX `fk_payments_users_idx` (`user_id` ASC),
  CONSTRAINT `fk_payments_types`
    FOREIGN KEY (`type_id`)
    REFERENCES `types` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_payments_statuses`
    FOREIGN KEY (`status_id`)
    REFERENCES `statuses` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_payments_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `subscriptions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `subscriptions` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `number_of_hours` INT NULL,
  `price` DECIMAL(9,2) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `currency_id` BIGINT NULL,
  `type_id` BIGINT NULL,
  `category_id` BIGINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_subscriptions_UNIQUE` (`id` ASC),
  INDEX `fk_subscriptions_currencies_idx` (`currency_id` ASC),
  INDEX `fk_subscriptions_types_idx` (`type_id` ASC),
  INDEX `fk_subscriptions_categories_idx` (`category_id` ASC),
  CONSTRAINT `fk_subscriptions_currencies`
    FOREIGN KEY (`currency_id`)
    REFERENCES `currencies` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_subscriptions_types`
    FOREIGN KEY (`type_id`)
    REFERENCES `types` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_subscriptions_categories`
    FOREIGN KEY (`category_id`)
    REFERENCES `categories` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `category_subscription`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `category_subscription` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `category_id` BIGINT NOT NULL,
  `subscription_id` BIGINT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_categorysubscription_UNIQUE` (`id` ASC),
  INDEX `fk_categorysubscription_categories_idx` (`category_id` ASC),
  INDEX `fk_categorysubscription_subscriptions_idx` (`subscription_id` ASC),
  CONSTRAINT `fk_categorysubscription_categories`
    FOREIGN KEY (`category_id`)
    REFERENCES `categories` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_categorysubscription_subscriptions`
    FOREIGN KEY (`subscription_id`)
    REFERENCES `subscriptions` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sessions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` VARCHAR(255) NOT NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `payload` LONGTEXT NULL,
  `last_activity` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` BIGINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  INDEX `fk_sessions_users_idx` (`user_id` ASC),
  CONSTRAINT `fk_sessions_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `personal_access_tokens`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `tokenable_type` VARCHAR(255) NOT NULL,
  `tokenable_id` BIGINT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `token` VARCHAR(64) NOT NULL,
  `abilities` TEXT NULL,
  `last_used_at` TIMESTAMP NULL,
  `expires_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_personalaccesstokens_UNIQUE` (`id` ASC),
  UNIQUE INDEX `token_personalaccesstokens_UNIQUE` (`token` ASC),
  INDEX `tokenabletype_tokenableid_personalaccesstokens_INDEX` (`tokenable_type` ASC, `tokenable_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `password_resets`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NULL,
  `phone` VARCHAR(45) NULL,
  `token` VARCHAR(45) NULL,
  `former_password` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idpassword_resets_UNIQUE` (`id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `work_session`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `work_session` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `work_id` BIGINT NOT NULL,
  `session_id` VARCHAR(255) NOT NULL,
  `read` TINYINT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_worksession_UNIQUE` (`id` ASC),
  INDEX `fk_worksession_works_idx` (`work_id` ASC),
  INDEX `fk_worksession_sessions_idx` (`session_id` ASC),
  CONSTRAINT `fk_worksession_works`
    FOREIGN KEY (`work_id`)
    REFERENCES `works` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_worksession_sessions`
    FOREIGN KEY (`session_id`)
    REFERENCES `sessions` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `likes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `likes` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` BIGINT NOT NULL,
  `for_work_id` BIGINT NULL,
  `for_message_id` BIGINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_likes_UNIQUE` (`id` ASC),
  INDEX `fk_likes_users_idx` (`user_id` ASC),
  INDEX `fk_likes_for_works_idx` (`for_work_id` ASC),
  INDEX `fk_likes_for_messages_idx` (`for_message_id` ASC),
  CONSTRAINT `fk_likes_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_likes_for_works`
    FOREIGN KEY (`for_work_id`)
    REFERENCES `works` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_likes_for_messages`
    FOREIGN KEY (`for_message_id`)
    REFERENCES `messages` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
COMMENT = '			';


-- -----------------------------------------------------
-- Table `notifications`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type_id` BIGINT NULL,
  `status_id` BIGINT NULL,
  `from_user_id` BIGINT NULL,
  `to_user_id` BIGINT NULL,
  `work_id` BIGINT NULL,
  `like_id` BIGINT NULL,
  `event_id` BIGINT NULL,
  `circle_id` BIGINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_notifications_UNIQUE` (`id` ASC),
  INDEX `fk_notifications_types_idx` (`type_id` ASC),
  INDEX `fk_notifications_statuses_idx` (`status_id` ASC),
  INDEX `fk_notifications_fromusers_idx` (`from_user_id` ASC),
  INDEX `fk_notifications_tousers_idx` (`to_user_id` ASC),
  INDEX `fk_notifications_works_idx` (`work_id` ASC),
  INDEX `fk_notifications_likes_idx` (`like_id` ASC),
  INDEX `fk_notifications_events_idx` (`event_id` ASC),
  INDEX `fk_notifications_circles_idx` (`circle_id` ASC),
  CONSTRAINT `fk_notifications_types`
    FOREIGN KEY (`type_id`)
    REFERENCES `types` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_notifications_statuses`
    FOREIGN KEY (`status_id`)
    REFERENCES `statuses` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_notifications_fromusers`
    FOREIGN KEY (`from_user_id`)
    REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_notifications_tousers`
    FOREIGN KEY (`to_user_id`)
    REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_notifications_works`
    FOREIGN KEY (`work_id`)
    REFERENCES `works` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_notifications_likes`
    FOREIGN KEY (`like_id`)
    REFERENCES `likes` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_notifications_events`
    FOREIGN KEY (`event_id`)
    REFERENCES `events` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_notifications_circles`
    FOREIGN KEY (`circle_id`)
    REFERENCES `circles` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `carts`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `carts` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `payment_code` TEXT NULL,
  `entity` ENUM('favorite', 'subscription', 'consultation') NOT NULL DEFAULT 'favorite',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status_id` BIGINT NULL,
  `user_id` BIGINT NULL,
  `payment_id` BIGINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_carts_UNIQUE` (`id` ASC),
  INDEX `fk_carts_statuses_idx` (`status_id` ASC),
  INDEX `fk_carts_users_idx` (`user_id` ASC),
  INDEX `fk_carts_payments_idx` (`payment_id` ASC),
  CONSTRAINT `fk_carts_statuses`
    FOREIGN KEY (`status_id`)
    REFERENCES `statuses` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_carts_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_carts_payments`
    FOREIGN KEY (`payment_id`)
    REFERENCES `payments` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `partners`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `partners` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `name` TEXT NULL,
  `message` TEXT NULL,
  `from_user_id` BIGINT NULL,
  `from_organization_id` BIGINT NULL,
  `image_url` TEXT NULL,
  `website_url` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_partners_UNIQUE` (`id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `event_user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `event_user` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `event_id` BIGINT NOT NULL,
  `user_id` BIGINT NOT NULL,
  `is_speaker` TINYINT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status_id` BIGINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_eventuser_UNIQUE` (`id` ASC),
  INDEX `fk_eventuser_events_idx` (`event_id` ASC),
  INDEX `fk_eventuser_users_idx` (`user_id` ASC),
  INDEX `fk_eventuser_statuses_idx` (`status_id` ASC),
  CONSTRAINT `fk_eventuser_events`
    FOREIGN KEY (`event_id`)
    REFERENCES `events` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_eventuser_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_eventuser_statuses`
    FOREIGN KEY (`status_id`)
    REFERENCES `statuses` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `message_user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `message_user` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `message_id` BIGINT NOT NULL,
  `user_id` BIGINT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status_id` BIGINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_messageuser_UNIQUE` (`id` ASC),
  INDEX `fk_messageuser_messages_idx` (`message_id` ASC),
  INDEX `fk_messageuser_users_idx` (`user_id` ASC),
  INDEX `fk_messageuser_statuses_idx` (`status_id` ASC),
  CONSTRAINT `fk_messageuser_messages`
    FOREIGN KEY (`message_id`)
    REFERENCES `messages` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_messageuser_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_messageuser_statuses`
    FOREIGN KEY (`status_id`)
    REFERENCES `statuses` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `category_partner`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `category_partner` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `category_id` BIGINT NOT NULL,
  `partner_id` BIGINT NOT NULL,
  `activation_code` VARCHAR(45) NULL,
  `promo_code` VARCHAR(45) NULL,
  `number_of_days` BIGINT NOT NULL,
  `is_used` TINYINT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status_id` BIGINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_categorypartner_UNIQUE` (`id` ASC),
  INDEX `fk_categorypartner_categories_idx` (`category_id` ASC),
  INDEX `fk_categorypartner_partners_idx` (`partner_id` ASC),
  INDEX `fk_categorypartner_statuses_idx` (`status_id` ASC),
  CONSTRAINT `fk_categorypartner_categories`
    FOREIGN KEY (`category_id`)
    REFERENCES `categories` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_categorypartner_partners`
    FOREIGN KEY (`partner_id`)
    REFERENCES `partners` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_categorypartner_statuses`
    FOREIGN KEY (`status_id`)
    REFERENCES `statuses` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `organization_user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `organization_user` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `organization_id` BIGINT NOT NULL,
  `user_id` BIGINT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_organizationuser_UNIQUE` (`id` ASC),
  INDEX `fk_organizationuser_organizations_idx` (`organization_id` ASC),
  INDEX `fk_organizationuser_users_idx` (`user_id` ASC),
  CONSTRAINT `fk_organizationuser_organizations`
    FOREIGN KEY (`organization_id`)
    REFERENCES `organizations` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_organizationuser_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `circle_user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `circle_user` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `circle_id` BIGINT NOT NULL,
  `user_id` BIGINT NOT NULL,
  `is_admin` TINYINT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status_id` BIGINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_circleuser_UNIQUE` (`id` ASC),
  INDEX `fk_circleuser_circles_idx` (`circle_id` ASC),
  INDEX `fk_circleuser_users_idx` (`user_id` ASC),
  INDEX `fk_circleuser_statuses_idx` (`status_id` ASC),
  CONSTRAINT `fk_circleuser_circles`
    FOREIGN KEY (`circle_id`)
    REFERENCES `circles` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_circleuser_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_circleuser_statuses`
    FOREIGN KEY (`status_id`)
    REFERENCES `statuses` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `report_reasons`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `report_reasons` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `reason_content` JSON NOT NULL,
  `reports_count` INT NOT NULL,
  `blocked_for` INT NOT NULL,
  `entity` ENUM('work', 'user', 'message') NULL DEFAULT 'user',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_reportreasons_UNIQUE` (`id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `toxic_contents`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `toxic_contents` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `is_reported` TINYINT NOT NULL DEFAULT 1,
  `for_user_id` BIGINT NULL,
  `for_work_id` BIGINT NULL,
  `for_message_id` BIGINT NULL,
  `explanation` LONGTEXT NULL,
  `is_unlocked` TINYINT NOT NULL DEFAULT 1,
  `is_archived` TINYINT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `report_reason_id` BIGINT NOT NULL,
  `user_id` BIGINT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_toxiccontents_UNIQUE` (`id` ASC),
  INDEX `fk_toxiccontents_reportreasons_idx` (`report_reason_id` ASC),
  INDEX `fk_toxiccontents_users_idx` (`user_id` ASC),
  CONSTRAINT `fk_toxiccontents_reportreasons`
    FOREIGN KEY (`report_reason_id`)
    REFERENCES `report_reasons` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_toxiccontents_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `currencies_rates`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `currencies_rates` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `rate` DECIMAL(9,5) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `from_currency_id` BIGINT NOT NULL,
  `to_currency_id` BIGINT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_currenciesrates_UNIQUE` (`id` ASC),
  INDEX `fk_currenciesrates_fromcurrencies_idx` (`from_currency_id` ASC),
  INDEX `fk_currenciesrates_tocurrencies_idx` (`to_currency_id` ASC),
  CONSTRAINT `fk_currenciesrates_fromcurrencies`
    FOREIGN KEY (`from_currency_id`)
    REFERENCES `currencies` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_currenciesrates_tocurrencies`
    FOREIGN KEY (`to_currency_id`)
    REFERENCES `currencies` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `cart_work`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `cart_work` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `cart_id` BIGINT NOT NULL,
  `work_id` BIGINT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status_id` BIGINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_cartwork_UNIQUE` (`id` ASC),
  INDEX `fk_cartwork_carts_idx` (`cart_id` ASC),
  INDEX `fk_cartwork_works_idx` (`work_id` ASC),
  INDEX `fk_cartwork_statuses_idx` (`status_id` ASC),
  CONSTRAINT `fk_cartwork_carts`
    FOREIGN KEY (`cart_id`)
    REFERENCES `carts` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_cartwork_works`
    FOREIGN KEY (`work_id`)
    REFERENCES `works` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_cartwork_statuses`
    FOREIGN KEY (`status_id`)
    REFERENCES `statuses` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `cart_subscription`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `cart_subscription` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `cart_id` BIGINT NOT NULL,
  `subscription_id` BIGINT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status_id` BIGINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_cartsubscription_UNIQUE` (`id` ASC),
  INDEX `fk_cartsubscription_carts_idx` (`cart_id` ASC),
  INDEX `fk_cartsubscription_subscriptions_idx` (`subscription_id` ASC),
  INDEX `fk_cartsubscription_statuses_idx` (`status_id` ASC),
  CONSTRAINT `fk_cartsubscription_carts`
    FOREIGN KEY (`cart_id`)
    REFERENCES `carts` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_cartsubscription_subscriptions`
    FOREIGN KEY (`subscription_id`)
    REFERENCES `subscriptions` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_cartsubscription_statuses`
    FOREIGN KEY (`status_id`)
    REFERENCES `statuses` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `activation_codes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `activation_codes` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `for_partner_id` BIGINT NULL,
  `code` VARCHAR(45) NOT NULL,
  `is_active` TINYINT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` BIGINT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_activationcodes_UNIQUE` (`id` ASC),
  INDEX `fk_activationcodes_users_idx` (`user_id` ASC),
  CONSTRAINT `fk_activationcodes_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `read_notifications`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `read_notifications` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `text_content` TEXT NOT NULL,
  `redirect_url` TEXT NULL,
  `screen` VARCHAR(255) NULL,
  `entity` VARCHAR(255) NULL,
  `entity_id` BIGINT NULL,
  `icon` TEXT NULL,
  `image_url` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notification_id` BIGINT NULL,
  `from_user_id` BIGINT NULL,
  `to_user_id` BIGINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_readnotifications_UNIQUE` (`id` ASC),
  INDEX `fk_readnotifications_notifications_idx` (`notification_id` ASC),
  INDEX `fk_readnotifications_users1_idx` (`from_user_id` ASC),
  INDEX `fk_readnotifications_users2_idx` (`to_user_id` ASC),
  CONSTRAINT `fk_readnotifications_notifications`
    FOREIGN KEY (`notification_id`)
    REFERENCES `notifications` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_readnotifications_users1`
    FOREIGN KEY (`from_user_id`)
    REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_readnotifications_users2`
    FOREIGN KEY (`to_user_id`)
    REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `favorites`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `favorites` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` BIGINT NULL,
  `work_id` BIGINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_favorites_UNIQUE` (`id` ASC),
  INDEX `fk_favorites_users_idx` (`user_id` ASC),
  INDEX `fk_favorites_works_idx` (`work_id` ASC),
  CONSTRAINT `fk_favorites_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_favorites_works`
    FOREIGN KEY (`work_id`)
    REFERENCES `works` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;
