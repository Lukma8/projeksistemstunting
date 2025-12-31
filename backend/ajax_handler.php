<?php
include 'database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    exit('Access Denied');
}

$action = $_GET['action'] ?? '';

switch($action) {
    case 'recent_balita':
        getRecentBalita();
        break;
    case 'data_balita':
        getDataBalita();
        break;
    case 'data_pengguna':
        getDataPengguna();
        break;
    default:
        echo 'Action not found';
}

function getRecentBalita() {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT b.*, u.nama_lengkap as orang_tua 
              FROM balita b 
              LEFT JOIN users u ON b.orang_tua_id = u.id 
              ORDER BY b.tanggal_cek DESC 
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $badge_color = $row['hasil'] == 'Stunting' ? 
            'bg-red-100 text-red-800' : 'bg-green-100 text-green-800';
        echo '
        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center space-x-4">
                <div class="bg-blue-100 w-10 h-10 rounded-full flex items-center justify-center">
                    <i class="fas fa-baby text-blue-600"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">' . htmlspecialchars($row['nama']) . '</p>
                    <p class="text-sm text-gray-600">' . htmlspecialchars($row['orang_tua']) . '</p>
                </div>
            </div>
            <div class="text-right">
                <span class="px-3 py-1 rounded-full text-xs font-semibold ' . $badge_color . '">
                    ' . $row['hasil'] . '
                </span>
                <p class="text-sm text-gray-600 mt-1">' . $row['tanggal_cek'] . '</p>
            </div>
        </div>';
    }
}

function getDataBalita() {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT b.*, u.nama_lengkap as orang_tua 
              FROM balita b 
              LEFT JOIN users u ON b.orang_tua_id = u.id 
              ORDER BY b.tanggal_cek DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    echo '
    <table class="data-table w-full">
        <thead>
            <tr class="bg-gray-50">
                <th class="px-4 py-2 text-left">Nama</th>
                <th class="px-4 py-2 text-left">Umur</th>
                <th class="px-4 py-2 text-left">Jenis Kelamin</th>
                <th class="px-4 py-2 text-left">Tinggi</th>
                <th class="px-4 py-2 text-left">Berat</th>
                <th class="px-4 py-2 text-left">Status</th>
                <th class="px-4 py-2 text-left">Tanggal</th>
                <th class="px-4 py-2 text-left">Aksi</th>
            </tr>
        </thead>
        <tbody>';
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $badge_color = $row['hasil'] == 'Stunting' ? 
            'bg-red-100 text-red-800' : 'bg-green-100 text-green-800';
        echo '
            <tr>
                <td class="px-4 py-2 border-b">' . htmlspecialchars($row['nama']) . '</td>
                <td class="px-4 py-2 border-b">' . $row['umur'] . ' bln</td>
                <td class="px-4 py-2 border-b">' . ($row['jenis_kelamin'] == 'L' ? 'Laki' : 'Perempuan') . '</td>
                <td class="px-4 py-2 border-b">' . $row['tinggi_badan'] . ' cm</td>
                <td class="px-4 py-2 border-b">' . $row['berat_badan'] . ' kg</td>
                <td class="px-4 py-2 border-b">
                    <span class="px-2 py-1 rounded-full text-xs font-semibold ' . $badge_color . '">
                        ' . $row['hasil'] . '
                    </span>
                </td>
                <td class="px-4 py-2 border-b">' . $row['tanggal_cek'] . '</td>
                <td class="px-4 py-2 border-b">
                    <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm mr-2">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>';
    }
    
    echo '</tbody></table>';
}

function getDataPengguna() {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT id, username, nama_lengkap, role, created_at FROM users ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    echo '
    <table class="w-full bg-white">
        <thead>
            <tr class="bg-gray-50">
                <th class="px-4 py-2 text-left">Username</th>
                <th class="px-4 py-2 text-left">Nama Lengkap</th>
                <th class="px-4 py-2 text-left">Role</th>
                <th class="px-4 py-2 text-left">Tanggal Daftar</th>
                <th class="px-4 py-2 text-left">Aksi</th>
            </tr>
        </thead>
        <tbody>';
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $role_badge = $row['role'] == 'admin' ? 
            'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800';
        echo '
            <tr class="border-b">
                <td class="px-4 py-3">' . htmlspecialchars($row['username']) . '</td>
                <td class="px-4 py-3">' . htmlspecialchars($row['nama_lengkap']) . '</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-semibold ' . $role_badge . '">
                        ' . ucfirst($row['role']) . '
                    </span>
                </td>
                <td class="px-4 py-3">' . $row['created_at'] . '</td>
                <td class="px-4 py-3">
                    <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm mr-2">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>';
    }
    
    echo '</tbody></table>';
}
?>