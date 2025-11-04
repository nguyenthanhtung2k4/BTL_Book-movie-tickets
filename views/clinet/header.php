

<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($siteTitle) ?></title>

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
          bboxShadow: {
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
      <a href="#" class="flex items-center space-x-2">
        <i data-lucide='popcorn' class='text-primary'></i>
        <span class="font-bold text-2xl">SCARLET CINEMA</span>
      </a>
      <div class="hidden md:flex space-x-6">
        <a href="#featured" class="hover:text-primary">NỔI BẬT</a>
        <a href="#current" class="hover:text-primary">ĐANG CHIẾU</a>
        <a href="#upcoming" class="hover:text-primary">SẮP CHIẾU</a>
      </div>
      <a href="account.php?view=login" class="bg-primary px-4 py-2 rounded text-black font-semibold hover:bg-red-500 transition">Đăng Nhập</a>
    </nav>
  </header>