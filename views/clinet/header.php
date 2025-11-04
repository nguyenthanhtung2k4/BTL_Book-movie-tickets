
<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($siteTitle ?? 'SCARLET CINEMA') ?></title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../asset/css/style.css">
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            primary: '#dc2626',
            'dark-bg': '#0a0a0a'
          },
          boxShadow: {
            'primary-glow': '0 0 20px rgba(255, 255, 255, 0.4), 0 0 8px rgba(255, 255, 255, 0.3)'
          }
        }
      }
    };
  </script>
</head>

<body class="bg-dark-bg text-gray-100 font-[Inter] pt-16" onload="lucide.createIcons();">

  <header class="fixed top-0 w-full bg-dark-bg/90 backdrop-blur-md shadow-lg z-50">
    <nav class="max-w-7xl mx-auto flex justify-between items-center p-4">
      <a href="index.php" class="flex items-center space-x-2">
        <i data-lucide='popcorn' class='text-primary'></i>
        <span class="font-bold text-2xl">SCARLET CINEMA</span>
      </a>
      <div class="hidden md:flex space-x-6">
        <a href="index.php#featured" class="hover:text-primary">NỔI BẬT</a>
        <a href="index.php#current" class="hover:text-primary">ĐANG CHIẾU</a>
        <a href="index.php#upcoming" class="hover:text-primary">SẮP CHIẾU</a>
      </div>
      
      <!-- Dynamic User Menu -->
      <div class="flex items-center space-x-4">
        <?php if (isset($_SESSION['user'])): ?>
          <!-- Logged In User -->
          <span class="text-gray-300 hidden md:inline">
            Xin chào, 
            <span class="font-bold text-white">
              <?= htmlspecialchars($_SESSION['user']['full_name'] ?? $_SESSION['user']['email']) ?>
            </span>
          </span>
          
          <!-- Admin Dashboard Link (Only for Admin) -->
          <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
            <a href="../admin/index.php" 
               class="bg-yellow-600 px-4 py-2 rounded text-black font-semibold hover:bg-yellow-500 transition flex items-center gap-2">
              <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
              <span class="hidden md:inline">Dashboard</span>
            </a>
          <?php endif; ?>
          
          <!-- Profile Link -->
          <a href="profile.php" 
             class="bg-gray-700 px-4 py-2 rounded text-white font-semibold hover:bg-gray-600 transition flex items-center gap-2">
            <i data-lucide="user" class="w-4 h-4"></i>
            <span class="hidden md:inline">Profile</span>
          </a>
          
          <!-- Logout Link -->
          <a href="logout.php" 
             class="bg-red-600 px-4 py-2 rounded text-white font-semibold hover:bg-red-500 transition">
            Đăng Xuất
          </a>
        <?php else: ?>
          <!-- Not Logged In -->
          <a href="account.php?view=login" 
             class="bg-primary px-4 py-2 rounded text-black font-semibold hover:bg-red-500 transition">
            Đăng Nhập
          </a>
        <?php endif; ?>
      </div>
    </nav>
  </header>