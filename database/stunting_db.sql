-- =======================
-- DATABASE
-- =======================
CREATE DATABASE IF NOT EXISTS stunting_db;
USE stunting_db;

-- =======================
-- TABEL USERS
-- =======================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('orang_tua', 'admin') NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =======================
-- TABEL BALITA
-- =======================
CREATE TABLE IF NOT EXISTS balita (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    umur INT NOT NULL,
    jenis_kelamin ENUM('L', 'P') NOT NULL,
    tinggi_badan DECIMAL(5,2) NOT NULL,
    berat_badan DECIMAL(5,2) NOT NULL,
    lingkar_kepala DECIMAL(5,2) NOT NULL,
    lingkar_lengan DECIMAL(5,2) NOT NULL,
    hasil ENUM('Stunting', 'Normal') NOT NULL,
    saran TEXT,
    orang_tua_id INT NOT NULL,
    tanggal_cek DATE NOT NULL,
    CONSTRAINT fk_orangtua
        FOREIGN KEY (orang_tua_id) REFERENCES users(id)
        ON DELETE CASCADE
);

-- =======================
-- DATA ADMIN DEFAULT
-- password = password
-- =======================
INSERT INTO users (username, password, role, nama_lengkap)
VALUES (
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    'Administrator'
);

-- =======================
-- DATA ORANG TUA CONTOH
-- password = password
-- =======================
INSERT INTO users (username, password, role, nama_lengkap)
VALUES (
    'orangtua1',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'orang_tua',
    'Budi Santoso'
);
