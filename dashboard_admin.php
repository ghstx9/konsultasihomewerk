<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'admin') {
    header("Location: index.php");
    exit;
}

$msg = "";


// Proses Tambah Konselor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_konselor'])) {
    $nama = $_POST['nama'];
    $nip = $_POST['nip'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $check = $conn->query("SELECT id FROM user WHERE email='$email' UNION SELECT id FROM konselor WHERE nip='$nip'");
    if ($check->num_rows > 0) {
        $msg = "<div class='bg-red-100 text-red-600 p-3 rounded mb-4'>Email atau NIP sudah terdaftar!</div>";
    } else {
        $conn->begin_transaction();
        try {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt_user = $conn->prepare("INSERT INTO user (email, kata_sandi, peran) VALUES (?, ?, 'konselor')");
            $stmt_user->bind_param("ss", $email, $hashed);
            $stmt_user->execute();
            $new_id = $conn->insert_id;

            $spesialisasi = "Konselor Umum";
            $stmt_k = $conn->prepare("INSERT INTO konselor (id_pengguna, nip, nama_lengkap, spesialisasi) VALUES (?, ?, ?, ?)");
            $stmt_k->bind_param("isss", $new_id, $nip, $nama, $spesialisasi);
            $stmt_k->execute();

            $conn->commit();
            $msg = "<div class='bg-green-100 text-green-600 p-3 rounded mb-4'>Konselor berhasil ditambahkan!</div>";
        } catch (Exception $e) {
            $conn->rollback();
            $msg = "<div class='bg-red-100 text-red-600 p-3 rounded mb-4'>Error: " . $e->getMessage() . "</div>";
        }
    }
}

// Proses Hapus Konselor
if (isset($_GET['delete_id'])) {
    $del_id = $_GET['delete_id']; // ID User
    $conn->query("DELETE FROM konselor WHERE id_pengguna = '$del_id'");
    $conn->query("DELETE FROM user WHERE id = '$del_id'");
    header("Location: dashboard_admin.php");
    exit;
}

// Ambil Data Konselor
$sql_list = "SELECT k.*, u.email FROM konselor k JOIN user u ON k.id_pengguna = u.id";
$res_list = $conn->query($sql_list);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
    <style>.lexend-font { font-family: "Lexend", sans-serif; }</style>
</head>
<body class="bg-slate-50 lexend-font min-h-screen">

    <nav class="bg-slate-800 text-white px-6 py-4 flex justify-between items-center">
        <h1 class="font-bold text-xl">Admin Panel</h1>
        <a href="logout.php" class="text-red-400 hover:text-red-300 text-sm">Keluar</a>
    </nav>

    <div class="container mx-auto p-6">
        
        <div class="flex flex-col md:flex-row gap-8">
            
            <div class="w-full md:w-1/3">
                <div class="bg-white p-6 rounded-xl shadow-sm border">
                    <h2 class="font-bold text-lg text-slate-700 mb-4">Tambah Konselor</h2>
                    <?= $msg ?>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase">Nama Lengkap</label>
                            <input type="text" name="nama" required class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase">NIP</label>
                            <input type="text" name="nip" required class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase">Email Login</label>
                            <input type="email" name="email" required class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase">Password</label>
                            <input type="text" name="password" required class="w-full border rounded px-3 py-2" placeholder="Contoh: guru123">
                        </div>
                        <button type="submit" name="add_konselor" class="w-full bg-blue-600 text-white font-bold py-2 rounded hover:bg-blue-700 transition">
                            + Tambah Akun
                        </button>
                    </form>
                </div>
            </div>

            <div class="w-full md:w-2/3">
                <div class="bg-white p-6 rounded-xl shadow-sm border">
                    <h2 class="font-bold text-lg text-slate-700 mb-4">Daftar Konselor</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-600">
                            <thead class="bg-slate-100 text-slate-700 uppercase font-bold text-xs">
                                <tr>
                                    <th class="px-4 py-3">Nama</th>
                                    <th class="px-4 py-3">NIP</th>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <?php if($res_list->num_rows > 0): ?>
                                    <?php while($row = $res_list->fetch_assoc()): ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 font-medium text-slate-800"><?= $row['nama_lengkap'] ?></td>
                                        <td class="px-4 py-3"><?= $row['nip'] ?></td>
                                        <td class="px-4 py-3"><?= $row['email'] ?></td>
                                        <td class="px-4 py-3">
                                            <a href="?delete_id=<?= $row['id_pengguna'] ?>" onclick="return confirm('Hapus akun ini?')" class="text-red-500 hover:underline">Hapus</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">Belum ada data konselor.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

    </div>

</body>
</html>
