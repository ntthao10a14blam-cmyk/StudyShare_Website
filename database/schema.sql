-- =============================================================
-- STUDYHUB — DATABASE SCHEMA
-- File: database/schema.sql
-- Chạy file này để tạo toàn bộ cấu trúc bảng.
-- Chỉ cần chạy MỘT LẦN khi cài đặt lần đầu.
-- Thay đổi sau này dùng file trong database/migrations/
-- =============================================================

CREATE DATABASE IF NOT EXISTS studyhub_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE studyhub_db;

-- -------------------------------------------------------------
-- BẢNG: users — Tài khoản người dùng
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id          BIGINT AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(100)  NOT NULL,
    email       VARCHAR(255)  NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,
    avatar_path VARCHAR(255)  NULL,
    role        ENUM('user','admin') NOT NULL DEFAULT 'user',
    status      ENUM('active','locked') NOT NULL DEFAULT 'active',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- BẢNG: subjects — Môn học
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS subjects (
    id          BIGINT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150)  NOT NULL UNIQUE,
    description TEXT          NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- BẢNG: tags — Từ khoá
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tags (
    id         BIGINT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(50) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- BẢNG: documents — Tài liệu
-- Trạng thái moderation_status:
--   not_submitted : mới tạo, chưa gửi duyệt
--   pending       : đã gửi duyệt, chờ Admin xem
--   approved      : Admin duyệt → hiển thị công khai
--   rejected      : Admin từ chối
--   hidden        : Admin ẩn do vi phạm (chủ sở hữu không tự chuyển lại)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS documents (
    id                 BIGINT AUTO_INCREMENT PRIMARY KEY,
    title              VARCHAR(255)  NOT NULL,
    description        LONGTEXT      NULL,
    file_path          VARCHAR(255)  NOT NULL,
    original_file_name VARCHAR(255)  NOT NULL,
    file_size          BIGINT        NOT NULL,
    visibility         ENUM('private','public') NOT NULL DEFAULT 'private',
    moderation_status  ENUM('not_submitted','pending','approved','rejected','hidden') NOT NULL DEFAULT 'not_submitted',
    submitted_at       DATETIME NULL,
    published_at       DATETIME NULL,
    download_count     INT NOT NULL DEFAULT 0,
    created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at         DATETIME NULL,
    user_id            BIGINT NULL,
    subject_id         BIGINT NULL,
    INDEX idx_documents_user             (user_id),
    INDEX idx_documents_subject          (subject_id),
    INDEX idx_documents_visibility_status (visibility, moderation_status),
    CONSTRAINT fk_documents_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_documents_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- BẢNG: document_images — Ảnh minh hoạ của tài liệu
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS document_images (
    id          BIGINT AUTO_INCREMENT PRIMARY KEY,
    image_path  VARCHAR(255) NOT NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    document_id BIGINT NOT NULL,
    CONSTRAINT fk_document_images_document FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- BẢNG: document_tags — Từ khoá của tài liệu (nhiều-nhiều)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS document_tags (
    document_id BIGINT NOT NULL,
    tag_id      BIGINT NOT NULL,
    PRIMARY KEY (document_id, tag_id),
    CONSTRAINT fk_document_tags_document FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_document_tags_tag      FOREIGN KEY (tag_id)      REFERENCES tags(id)      ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- BẢNG: document_reviews — Lịch sử duyệt tài liệu bởi Admin
-- Ràng buộc: nếu action != 'approved' thì reason bắt buộc
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS document_reviews (
    id          BIGINT AUTO_INCREMENT PRIMARY KEY,
    action      ENUM('approved','rejected','hidden') NOT NULL,
    reason      TEXT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    document_id BIGINT NOT NULL,
    admin_id    BIGINT NOT NULL,
    CONSTRAINT fk_document_reviews_document FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_document_reviews_admin    FOREIGN KEY (admin_id)    REFERENCES users(id)    ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_review_reason CHECK (action = 'approved' OR (reason IS NOT NULL AND CHAR_LENGTH(TRIM(reason)) > 0))
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- BẢNG: document_comments — Bình luận trên tài liệu
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS document_comments (
    id          BIGINT AUTO_INCREMENT PRIMARY KEY,
    content     TEXT NOT NULL,
    status      ENUM('visible','hidden') NOT NULL DEFAULT 'visible',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at  DATETIME NULL,
    document_id BIGINT NOT NULL,
    user_id     BIGINT NOT NULL,
    CONSTRAINT fk_document_comments_document FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_document_comments_user     FOREIGN KEY (user_id)     REFERENCES users(id)    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- BẢNG: collections — Bộ sưu tập của User
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS collections (
    id          BIGINT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    description TEXT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    user_id     BIGINT NOT NULL,
    CONSTRAINT fk_collections_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- BẢNG: collection_documents — Tài liệu trong bộ sưu tập
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS collection_documents (
    collection_id BIGINT NOT NULL,
    document_id   BIGINT NOT NULL,
    added_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (collection_id, document_id),
    CONSTRAINT fk_collection_documents_collection FOREIGN KEY (collection_id) REFERENCES collections(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_collection_documents_document   FOREIGN KEY (document_id)   REFERENCES documents(id)  ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- BẢNG: favorites — Tài liệu yêu thích
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS favorites (
    user_id     BIGINT NOT NULL,
    document_id BIGINT NOT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, document_id),
    CONSTRAINT fk_favorites_user     FOREIGN KEY (user_id)     REFERENCES users(id)    ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_favorites_document FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- BẢNG: questions — Câu hỏi học tập
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS questions (
    id               BIGINT AUTO_INCREMENT PRIMARY KEY,
    title            VARCHAR(255) NOT NULL,
    content          LONGTEXT NOT NULL,
    status           ENUM('visible','hidden') NOT NULL DEFAULT 'visible',
    created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at       DATETIME NULL,
    user_id          BIGINT NOT NULL,
    subject_id       BIGINT NOT NULL,
    accepted_answer_id BIGINT NULL,
    CONSTRAINT fk_questions_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_questions_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- BẢNG: question_images — Ảnh minh hoạ câu hỏi
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS question_images (
    id          BIGINT AUTO_INCREMENT PRIMARY KEY,
    image_path  VARCHAR(255) NOT NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    question_id BIGINT NOT NULL,
    CONSTRAINT fk_question_images_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- BẢNG: question_tags — Từ khoá của câu hỏi
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS question_tags (
    question_id BIGINT NOT NULL,
    tag_id      BIGINT NOT NULL,
    PRIMARY KEY (question_id, tag_id),
    CONSTRAINT fk_question_tags_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_question_tags_tag      FOREIGN KEY (tag_id)      REFERENCES tags(id)      ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- BẢNG: answers — Câu trả lời
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS answers (
    id          BIGINT AUTO_INCREMENT PRIMARY KEY,
    content     LONGTEXT NOT NULL,
    status      ENUM('visible','hidden') NOT NULL DEFAULT 'visible',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at  DATETIME NULL,
    question_id BIGINT NOT NULL,
    user_id     BIGINT NOT NULL,
    UNIQUE KEY uq_answers_question_id (question_id, id),
    CONSTRAINT fk_answers_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_answers_user     FOREIGN KEY (user_id)     REFERENCES users(id)    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Khoá ngoại accepted_answer (thêm sau khi có bảng answers)
ALTER TABLE questions
    ADD CONSTRAINT fk_questions_accepted_answer
    FOREIGN KEY (id, accepted_answer_id) REFERENCES answers(question_id, id)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

-- -------------------------------------------------------------
-- BẢNG: reports — Báo cáo vi phạm
-- Chỉ một trong document_id / comment_id / question_id / answer_id được khác NULL
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS reports (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    reason          VARCHAR(255) NOT NULL,
    details         TEXT NULL,
    status          ENUM('pending','resolved','dismissed') NOT NULL DEFAULT 'pending',
    handled_by      BIGINT NULL,
    resolution_note TEXT NULL,
    handled_at      DATETIME NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    document_id     BIGINT NULL,
    reporter_id     BIGINT NOT NULL,
    comment_id      BIGINT NULL,
    question_id     BIGINT NULL,
    answer_id       BIGINT NULL,
    CONSTRAINT fk_reports_handler  FOREIGN KEY (handled_by)  REFERENCES users(id)             ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_reports_document FOREIGN KEY (document_id) REFERENCES documents(id)         ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_reports_reporter FOREIGN KEY (reporter_id) REFERENCES users(id)             ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_reports_comment  FOREIGN KEY (comment_id)  REFERENCES document_comments(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_reports_question FOREIGN KEY (question_id) REFERENCES questions(id)         ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_reports_answer   FOREIGN KEY (answer_id)   REFERENCES answers(id)           ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_reports_one_target CHECK (
        (document_id IS NOT NULL) + (comment_id IS NOT NULL) +
        (question_id IS NOT NULL) + (answer_id IS NOT NULL) = 1
    )
) ENGINE=InnoDB;
