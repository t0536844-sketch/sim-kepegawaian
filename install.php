<?php
echo "<h2>Installation Wizard - Sistem Kepegawaian RSUD Mimika</h2>";
echo "<hr>";

// Langkah 1: Test Koneksi Database
echo "<h3>Step 1: Testing Database Connection</h3>";
try {
    $conn = new mysqli("localhost", "root", "");
    
    if ($conn->connect_error) {
        echo "<div style='color:red;'>❌ MySQL Connection Failed: " . $conn->connect_error . "</div>";
        echo "<p>Please make sure:</p>";
        echo "<ul>";
        echo "<li>XAMPP is running</li>";
        echo "<li>MySQL service is started</li>";
        echo "<li>MySQL password is correct</li>";
        echo "</ul>";
    } else {
        echo "<div style='color:green;'>✅ MySQL Connection Successful!</div>";
        
        // Langkah 2: Buat Database
        echo "<h3>Step 2: Creating Database</h3>";
        $sql = "CREATE DATABASE IF NOT EXISTS rsud_mimika_kepegawaian 
                CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
        
        if ($conn->query($sql) === TRUE) {
            echo "<div style='color:green;'>✅ Database created successfully!</div>";
            
            // Langkah 3: Pilih Database
            $conn->select_db("rsud_mimika_kepegawaian");
            
            // Langkah 4: Buat Tabel
            echo "<h3>Step 3: Creating Tables</h3>";
            
            // SQL untuk semua tabel
            $sql_tables = "
            -- Tabel pegawai
            CREATE TABLE IF NOT EXISTS pegawai (
                id INT PRIMARY KEY AUTO_INCREMENT,
                timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                nama_lengkap VARCHAR(255) NOT NULL,
                tempat_lahir VARCHAR(100),
                tanggal_lahir DATE,
                agama VARCHAR(50),
                jenis_kelamin ENUM('Pria', 'Wanita'),
                nip VARCHAR(50) UNIQUE,
                pangkat_golongan VARCHAR(50),
                pendidikan VARCHAR(255),
                status_pernikahan VARCHAR(50),
                jabatan VARCHAR(255),
                status_kepegawaian VARCHAR(50),
                link_sk TEXT,
                jumlah_keluarga INT DEFAULT 0,
                alamat_rumah TEXT,
                link_ktp TEXT,
                link_kartu_keluarga TEXT,
                link_ijazah TEXT,
                link_str TEXT,
                masa_berlaku_str DATE,
                link_sip TEXT,
                masa_berlaku_sip DATE,
                nomor_kartu_pegawai VARCHAR(100),
                link_npwp TEXT,
                link_foto TEXT,
                link_akta_lahir TEXT,
                link_akta_nikah TEXT,
                link_skp TEXT,
                link_sk_kenaikan_pangkat TEXT,
                link_sk_jabatan TEXT,
                link_sk_mutasi TEXT,
                link_sk_pensiun TEXT,
                link_sertifikat TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            -- Tabel users
            CREATE TABLE IF NOT EXISTS users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                nama_lengkap VARCHAR(255),
                role ENUM('admin', 'operator', 'viewer') DEFAULT 'operator',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            -- Tabel logs
            CREATE TABLE IF NOT EXISTS logs (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT,
                action VARCHAR(100),
                table_name VARCHAR(100),
                record_id INT,
                description TEXT,
                ip_address VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ";
            
            // Eksekusi pembuatan tabel
            if ($conn->multi_query($sql_tables)) {
                do {
                    if ($result = $conn->store_result()) {
                        $result->free();
                    }
                } while ($conn->more_results() && $conn->next_result());
                
                echo "<div style='color:green;'>✅ Tables created successfully!</div>";
                
                // Langkah 5: Insert default user
                echo "<h3>Step 4: Creating Default User</h3>";
                $password_hash = hash('sha256', 'admin123');
                $sql_user = "INSERT IGNORE INTO users (username, password, nama_lengkap, role) 
                            VALUES ('admin', '$password_hash', 'Administrator', 'admin')";
                
                if ($conn->query($sql_user) === TRUE) {
                    echo "<div style='color:green;'>✅ Default user created!</div>";
                    echo "<p><strong>Login Details:</strong></p>";
                    echo "<ul>";
                    echo "<li>Username: <strong>admin</strong></li>";
                    echo "<li>Password: <strong>admin123</strong></li>";
                    echo "</ul>";
                }
                
                // Langkah 6: Insert sample data
                echo "<h3>Step 5: Inserting Sample Data</h3>";
                $sample_data = "INSERT IGNORE INTO pegawai (
                    nama_lengkap, tempat_lahir, tanggal_lahir, agama, jenis_kelamin, nip,
                    pangkat_golongan, pendidikan, status_pernikahan, jabatan, status_kepegawaian,
                    link_sk, jumlah_keluarga, alamat_rumah
                ) VALUES 
                ('Uji coba data', 'Nabire', '1990-01-31', 'Konghucu', 'Pria', '123123123',
                 'IV/a', 'S1 ilmu kesehatan masyarakat', 'Menikah', 'Staf', 'PNS',
                 'https://drive.google.com/open?id=1aYrA86pYxZ9fkAOWtfqCoy6QSGXUGiw-', 2, 'DINAS'),
                
                ('Dr. Budi Santoso', 'Jakarta', '1980-05-15', 'Islam', 'Pria', '19800515001',
                 'IV/c', 'S2 Kedokteran', 'Menikah', 'Dokter Spesialis', 'PNS',
                 'https://drive.google.com/open?id=1', 3, 'Jl. Sudirman No. 123'),
                
                ('Siti Rahayu', 'Surabaya', '1992-08-20', 'Islam', 'Wanita', '19920820001',
                 'III/b', 'D3 Keperawatan', 'Belum Menikah', 'Perawat', 'Honorer',
                 'https://drive.google.com/open?id=2', 0, 'Jl. A. Yani No. 45')";
                
                if ($conn->multi_query($sample_data)) {
                    echo "<div style='color:green;'>✅ Sample data inserted successfully!</div>";
                }
                
            } else {
                echo "<div style='color:red;'>❌ Error creating tables: " . $conn->error . "</div>";
            }
            
        } else {
            echo "<div style='color:red;'>❌ Error creating database: " . $conn->error . "</div>";
        }
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<div style='color:red;'>❌ Error: " . $e->getMessage() . "</div>";
}

// Langkah terakhir
echo "<hr>";
echo "<h3>🎉 Installation Complete!</h3>";
echo "<p>Next steps:</p>";
echo "<ol>";
echo "<li>Delete or rename this <strong>install.php</strong> file for security</li>";
echo "<li>Access the application: <a href='login.php'>http://localhost/kepegawaian_rsud/login.php</a></li>";
echo "<li>Login with username: <strong>admin</strong> and password: <strong>admin123</strong></li>";
echo "</ol>";
echo "<a href='login.php' style='padding:10px 20px; background:#4CAF50; color:white; text-decoration:none; border-radius:5px;'>Go to Login Page</a>";
?>