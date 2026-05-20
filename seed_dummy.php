<?php
// seed_dummy.php — Seed data dummy untuk testing SIM Kepegawaian
require_once 'config.php';

$database = new Database();
$db = $database->getConnection();

echo "<pre>";
echo "=== Seed Data Dummy SIM Kepegawaian ===\n\n";

// Create additional users if not exist
$users = [
    ['operator', 'operator', 'Operator User', 'operator'],
    ['viewer', 'viewer', 'Viewer User', 'viewer'],
];

$hashOp = hashPassword('admin123');
foreach ($users as $u) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$u[0]]);
    $exists = $stmt->fetchColumn();
    if (!$exists) {
        $db->prepare("INSERT INTO users (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)")
           ->execute([$u[0], $hashOp, $u[2], $u[3]]);
        echo "✅ User created: {$u[0]} (password: admin123)\n";
    } else {
        echo "⏭️  User exists: {$u[0]}\n";
    }
}

// Dummy pegawai data
$pegawai = [
    [
        'nama_lengkap' => 'dr. Ahmad Fauzi, Sp.PD',
        'tempat_lahir' => 'Timika', 'tanggal_lahir' => '1985-03-15', 'agama' => 'Islam',
        'jenis_kelamin' => 'Pria', 'nip' => '198503152010011001',
        'pangkat_golongan' => 'Penata Tk.I / III-d', 'pendidikan' => 'S2 Spesialis Penyakit Dalam',
        'status_pernikahan' => 'Kawin', 'jabatan' => 'Dokter Spesialis Penyakit Dalam',
        'status_kepegawaian' => 'PNS',
        'masa_berlaku_str' => date('Y-m-d', strtotime('+5 days')),
        'masa_berlaku_sip' => date('Y-m-d', strtotime('+45 days')),
        'no_telepon' => '081234567890',
    ],
    [
        'nama_lengkap' => 'Ns. Maria Kogoya, S.Kep',
        'tempat_lahir' => 'Nabire', 'tanggal_lahir' => '1990-07-22', 'agama' => 'Kristen',
        'jenis_kelamin' => 'Wanita', 'nip' => '199007222015032001',
        'pangkat_golongan' => 'Penata Muda / III-a', 'pendidikan' => 'S1 Keperawatan',
        'status_pernikahan' => 'Belum Kawin', 'jabatan' => 'Perawat Pelaksana',
        'status_kepegawaian' => 'PNS',
        'masa_berlaku_str' => date('Y-m-d', strtotime('+10 days')),
        'masa_berlaku_sip' => date('Y-m-d', strtotime('+200 days')),
        'no_telepon' => '081234567891',
    ],
    [
        'nama_lengkap' => 'Yuliana Murip, A.Md.Keb',
        'tempat_lahir' => 'Mimika', 'tanggal_lahir' => '1992-11-08', 'agama' => 'Katolik',
        'jenis_kelamin' => 'Wanita', 'nip' => '199211082018012001',
        'pangkat_golongan' => 'Pengatur Tk.I / II-d', 'pendidikan' => 'D3 Kebidanan',
        'status_pernikahan' => 'Kawin', 'jabatan' => 'Bidan Pelaksana',
        'status_kepegawaian' => 'PNS',
        'masa_berlaku_str' => date('Y-m-d', strtotime('+2 days')),
        'masa_berlaku_sip' => date('Y-m-d', strtotime('+3 days')),
        'no_telepon' => '081234567892',
    ],
    [
        'nama_lengkap' => 'Markus Wenno, S.Farm',
        'tempat_lahir' => 'Jayapura', 'tanggal_lahir' => '1988-05-30', 'agama' => 'Protestan',
        'jenis_kelamin' => 'Pria', 'nip' => '198805302012011002',
        'pangkat_golongan' => 'Penata Muda Tk.I / III-b', 'pendidikan' => 'S1 Farmasi',
        'status_pernikahan' => 'Kawin', 'jabatan' => 'Apoteker',
        'status_kepegawaian' => 'PNS',
        'masa_berlaku_str' => date('Y-m-d', strtotime('+180 days')),
        'masa_berlaku_sip' => date('Y-m-d', strtotime('+150 days')),
        'no_telepon' => '081234567893',
    ],
    [
        'nama_lengkap' => 'Anastasia Kalami, S.KM',
        'tempat_lahir' => 'Timika', 'tanggal_lahir' => '1995-01-14', 'agama' => 'Katolik',
        'jenis_kelamin' => 'Wanita', 'nip' => '', 
        'pangkat_golongan' => '-', 'pendidikan' => 'S1 Kesehatan Masyarakat',
        'status_pernikahan' => 'Belum Kawin', 'jabatan' => 'Petugas Surveilans',
        'status_kepegawaian' => 'Honorer',
        'masa_berlaku_str' => '',
        'masa_berlaku_sip' => '',
        'no_telepon' => '081234567894',
    ],
    [
        'nama_lengkap' => 'Yosef Yikwa, S.ST',
        'tempat_lahir' => 'Mimika', 'tanggal_lahir' => '1993-09-25', 'agama' => 'Protestan',
        'jenis_kelamin' => 'Pria', 'nip' => '',
        'pangkat_golongan' => '-', 'pendidikan' => 'D4 Sanitasi Lingkungan',
        'status_pernikahan' => 'Belum Kawin', 'jabatan' => 'Sanitarian',
        'status_kepegawaian' => 'Honorer',
        'masa_berlaku_str' => '',
        'masa_berlaku_sip' => '',
        'no_telepon' => '081234567895',
    ],
    [
        'nama_lengkap' => 'dr. Rina Wambrauw, Sp.OG',
        'tempat_lahir' => 'Timika', 'tanggal_lahir' => '1982-12-03', 'agama' => 'Protestan',
        'jenis_kelamin' => 'Wanita', 'nip' => '198212032008012001',
        'pangkat_golongan' => 'Penata Tk.I / III-d', 'pendidikan' => 'S2 Spesialis Obstetri Ginekologi',
        'status_pernikahan' => 'Kawin', 'jabatan' => 'Dokter Spesialis Kebidanan',
        'status_kepegawaian' => 'PNS',
        'masa_berlaku_str' => date('Y-m-d', strtotime('+365 days')),
        'masa_berlaku_sip' => date('Y-m-d', strtotime('+300 days')),
        'no_telepon' => '081234567896',
    ],
    [
        'nama_lengkap' => 'Suryadi Utomo, A.Md',
        'tempat_lahir' => 'Surabaya', 'tanggal_lahir' => '1991-06-18', 'agama' => 'Islam',
        'jenis_kelamin' => 'Pria', 'nip' => '',
        'pangkat_golongan' => '-', 'pendidikan' => 'D3 Radiologi',
        'status_pernikahan' => 'Kawin', 'jabatan' => 'Petugas Radiologi',
        'status_kepegawaian' => 'Honorer',
        'masa_berlaku_str' => '',
        'masa_berlaku_sip' => '',
        'no_telepon' => '081234567897',
    ],
    [
        'nama_lengkap' => 'Theresia Tabuni, S.Kep',
        'tempat_lahir' => 'Jayawijaya', 'tanggal_lahir' => '1994-04-10', 'agama' => 'Katolik',
        'jenis_kelamin' => 'Wanita', 'nip' => '',
        'pangkat_golongan' => '-', 'pendidikan' => 'S1 Keperawatan',
        'status_pernikahan' => 'Belum Kawin', 'jabatan' => 'Perawat IGD',
        'status_kepegawaian' => 'Honorer',
        'masa_berlaku_str' => date('Y-m-d', strtotime('+12 days')),
        'masa_berlaku_sip' => date('Y-m-d', strtotime('+8 days')),
        'no_telepon' => '081234567898',
    ],
    [
        'nama_lengkap' => 'dr. Budi Santoso, Sp.An',
        'tempat_lahir' => 'Semarang', 'tanggal_lahir' => '1980-08-27', 'agama' => 'Islam',
        'jenis_kelamin' => 'Pria', 'nip' => '198008272006041001',
        'pangkat_golongan' => 'Penata Tk.I / III-d', 'pendidikan' => 'S2 Spesialis Anestesiologi',
        'status_pernikahan' => 'Kawin', 'jabatan' => 'Dokter Spesialis Anestesi',
        'status_kepegawaian' => 'PNS',
        'masa_berlaku_str' => date('Y-m-d', strtotime('+90 days')),
        'masa_berlaku_sip' => date('Y-m-d', strtotime('+60 days')),
        'no_telepon' => '081234567899',
    ],
    [
        'nama_lengkap' => 'Elisabeth Pigome, SE',
        'tempat_lahir' => 'Timika', 'tanggal_lahir' => '1996-02-19', 'agama' => 'Protestan',
        'jenis_kelamin' => 'Wanita', 'nip' => '',
        'pangkat_golongan' => '-', 'pendidikan' => 'S1 Ekonomi',
        'status_pernikahan' => 'Belum Kawin', 'jabatan' => 'Staf Administrasi',
        'status_kepegawaian' => 'Kontrak',
        'masa_berlaku_str' => '',
        'masa_berlaku_sip' => '',
        'no_telepon' => '081234567900',
    ],
    [
        'nama_lengkap' => 'Firman Sineru, A.Md.Kom',
        'tempat_lahir' => 'Mimika', 'tanggal_lahir' => '1997-10-05', 'agama' => 'Kristen',
        'jenis_kelamin' => 'Pria', 'nip' => '',
        'pangkat_golongan' => '-', 'pendidikan' => 'D3 Teknik Informatika',
        'status_pernikahan' => 'Belum Kawin', 'jabatan' => 'Petugas IT',
        'status_kepegawaian' => 'Kontrak',
        'masa_berlaku_str' => '',
        'masa_berlaku_sip' => '',
        'no_telepon' => '081234567901',
    ],
    [
        'nama_lengkap' => 'dr. Sri Wahyuni',
        'tempat_lahir' => 'Palembang', 'tanggal_lahir' => '1989-03-12', 'agama' => 'Islam',
        'jenis_kelamin' => 'Wanita', 'nip' => '198903122014012001',
        'pangkat_golongan' => 'Penata Muda / III-a', 'pendidikan' => 'S1 Kedokteran',
        'status_pernikahan' => 'Kawin', 'jabatan' => 'Dokter Umum',
        'status_kepegawaian' => 'CPNS',
        'masa_berlaku_str' => date('Y-m-d', strtotime('+250 days')),
        'masa_berlaku_sip' => date('Y-m-d', strtotime('+240 days')),
        'no_telepon' => '081234567902',
    ],
    [
        'nama_lengkap' => 'Kristina Elopere, A.Md',
        'tempat_lahir' => 'Nabire', 'tanggal_lahir' => '1998-06-28', 'agama' => 'Katolik',
        'jenis_kelamin' => 'Wanita', 'nip' => '',
        'pangkat_golongan' => '-', 'pendidikan' => 'D3 Farmasi',
        'status_pernikahan' => 'Belum Kawin', 'jabatan' => 'Asisten Apoteker',
        'status_kepegawaian' => 'Honorer',
        'masa_berlaku_str' => '',
        'masa_berlaku_sip' => '',
        'no_telepon' => '081234567903',
    ],
    [
        'nama_lengkap' => 'Agus Prawira, S.Sos',
        'tempat_lahir' => 'Bandung', 'tanggal_lahir' => '1987-11-11', 'agama' => 'Islam',
        'jenis_kelamin' => 'Pria', 'nip' => '198711112011011001',
        'pangkat_golongan' => 'Penata Muda Tk.I / III-b', 'pendidikan' => 'S1 Ilmu Sosial',
        'status_pernikahan' => 'Kawin', 'jabatan' => 'Kepala Sub Bagian Umum',
        'status_kepegawaian' => 'PNS',
        'masa_berlaku_str' => '',
        'masa_berlaku_sip' => '',
        'no_telepon' => '081234567904',
    ],
];

$insertSql = "INSERT INTO pegawai (
    nama_lengkap, tempat_lahir, tanggal_lahir, agama, jenis_kelamin, nip,
    pangkat_golongan, pendidikan, status_pernikahan, jabatan, status_kepegawaian,
    masa_berlaku_str, masa_berlaku_sip, no_telepon
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $db->prepare($insertSql);
$count = 0;

foreach ($pegawai as $p) {
    // Check duplicate NIP
    if (!empty($p['nip'])) {
        $check = $db->prepare("SELECT COUNT(*) FROM pegawai WHERE nip = ?");
        $check->execute([$p['nip']]);
        if ($check->fetchColumn() > 0) {
            echo "⏭️  Skip (NIP exists): {$p['nama_lengkap']}\n";
            continue;
        }
    }

    $stmt->execute([
        $p['nama_lengkap'], $p['tempat_lahir'], $p['tanggal_lahir'], $p['agama'],
        $p['jenis_kelamin'], $p['nip'], $p['pangkat_golongan'], $p['pendidikan'],
        $p['status_pernikahan'], $p['jabatan'], $p['status_kepegawaian'],
        $p['masa_berlaku_str'], $p['masa_berlaku_sip'], $p['no_telepon']
    ]);
    $count++;
    echo "✅ Added: {$p['nama_lengkap']} ({$p['status_kepegawaian']} - {$p['jabatan']})\n";
}

echo "\n=== Summary ===\n";
$total = $db->query("SELECT COUNT(*) FROM pegawai")->fetchColumn();
$pns = $db->query("SELECT COUNT(*) FROM pegawai WHERE status_kepegawaian='PNS'")->fetchColumn();
$honorer = $db->query("SELECT COUNT(*) FROM pegawai WHERE status_kepegawaian='Honorer'")->fetchColumn();
$cpns = $db->query("SELECT COUNT(*) FROM pegawai WHERE status_kepegawaian='CPNS'")->fetchColumn();
$kontrak = $db->query("SELECT COUNT(*) FROM pegawai WHERE status_kepegawaian='Kontrak'")->fetchColumn();

echo "Total pegawai: $total\n";
echo "  PNS: $pns\n";
echo "  Honorer: $honorer\n";
echo "  CPNS: $cpns\n";
echo "  Kontrak: $kontrak\n";

// STR/SIP expiring info
$expStr = $db->query("SELECT COUNT(*) FROM pegawai WHERE masa_berlaku_str IS NOT NULL AND masa_berlaku_str != '' AND masa_berlaku_str <= date('now', '+30 days')")->fetchColumn();
$expSip = $db->query("SELECT COUNT(*) FROM pegawai WHERE masa_berlaku_sip IS NOT NULL AND masa_berlaku_sip != '' AND masa_berlaku_sip <= date('now', '+30 days')")->fetchColumn();
echo "\n⚠️  STR akan kadaluarsa (≤30 hari): $expStr\n";
echo "⚠️  SIP akan kadaluarsa (≤30 hari): $expSip\n";

echo "\n✅ Seeding selesai!\n";
echo "</pre>";
