<?php
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../functions/functions.php';

// Lấy tất cả món ăn với thông tin loại món
$query = "SELECT m.*, l.Tenloai 
          FROM monan m 
          LEFT JOIN loaimonan l ON m.Maloai = l.Maloai 
          ORDER BY m.Mamon";
$result = mysqli_query($conn, $query);
$featuredItems = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $featuredItems[] = $row;
    }
}
$totalItems = count($featuredItems);
if (session_status() === PHP_SESSION_NONE) session_start();
if( isset($_SESSION['role'])){
    $role = $_SESSION['role'];
}
else{
    $role = null;
}

// Lấy danh sách loại món ăn từ bảng loaimonan
$categoriesQuery = "SELECT Maloai, Tenloai FROM loaimonan ORDER BY Tenloai";
$categoriesResult = mysqli_query($conn, $categoriesQuery);
$categories = [];
$categoryMap = []; // Map Maloai -> Tenloai

if ($categoriesResult) {
    while ($row = mysqli_fetch_assoc($categoriesResult)) {
        $categories[] = [
            'id' => $row['Maloai'],
            'name' => $row['Tenloai']
        ];
        $categoryMap[$row['Maloai']] = $row['Tenloai'];
    }
}

// Thêm thông tin loại món ăn vào mỗi món
foreach ($featuredItems as &$item) {
    // Nếu đã có Tenloai từ JOIN query
    if (isset($item['Tenloai']) && !empty($item['Tenloai'])) {
        $item['category_id'] = $item['Maloai'];
        $item['category_name'] = $item['Tenloai'];
    } else if (isset($item['Maloai']) && isset($categoryMap[$item['Maloai']])) {
        $item['category_id'] = $item['Maloai'];
        $item['category_name'] = $categoryMap[$item['Maloai']];
    } else {
        $item['category_id'] = 'other';
        $item['category_name'] = 'Khác';
    }
}
unset($item);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thực đơn - Ăn húp hội</title>
    <link rel="stylesheet" href="/assets/css/menu.css">
</head>
<body>
        <?php include __DIR__ . '/../includes/header.php'; ?>
    <!-- Page Title -->
    <div class="page-title">
        <h1>Thực đơn</h1>
        <p>Khám phá các món ăn đặc sắc của chúng tôi</p>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <div class="filter-container">
            <div class="filter-group">
                <label>Danh mục:</label>
                <select class="filter-select" id="categoryFilter" onchange="applyFilters()">
                    <option value="all">Tất cả</option>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['id']); ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="1">Món ăn</option>
                        <option value="2">Đồ uống</option>
                        <option value="3">Tráng miệng</option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="filter-group">
                <label>Giá:</label>
                <select class="filter-select" id="priceFilter" onchange="applyFilters()">
                    <option value="all">Tất cả</option>
                    <option value="0-30000">Dưới 30.000đ</option>
                    <option value="30000-50000">30.000đ - 50.000đ</option>
                    <option value="50000-100000">50.000đ - 100.000đ</option>
                    <option value="100000-999999">Trên 100.000đ</option>
                </select>
            </div>

            <div class="search-box">
                <input type="text" class="search-input" id="searchInput" placeholder="Tìm kiếm món ăn...">
                <button class="btn-search" onclick="applyFilters()">
                    <i class='bx bx-search'></i> Tìm
                </button>
            </div>
        </div>
    </div>

    <!-- Results Info -->
    <div class="results-info">
        <div class="sort-by">
            <label>Sắp xếp:</label>
            <select class="filter-select" id="sortBy" onchange="applyFilters()">
                <option value="default">Mặc định</option>
                <option value="name-asc">Tên A-Z</option>
                <option value="name-desc">Tên Z-A</option>
                <option value="price-asc">Giá thấp đến cao</option>
                <option value="price-desc">Giá cao đến thấp</option>
            </select>
        </div>
    </div>

    <!-- Food Grid -->
    <div class="food-container">
        <div class="food-grid" id="foodGrid">
            <?php if (!empty($featuredItems)): ?>
                <?php foreach ($featuredItems as $it): ?>
                    <div class="food-item" onclick="viewProduct('<?php echo htmlspecialchars($it['Mamon']); ?>')">
                        <div class="food-image">
                            <img src="<?php echo htmlspecialchars(resolveImagePath($it['Anh'] ?? '')); ?>" 
                                 alt="<?php echo htmlspecialchars($it['Tenmon']); ?>"
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div class="food-info">
                            <div class="food-name"><?php echo htmlspecialchars($it['Tenmon']); ?></div>
                            <div class="food-footer">
                                <div class="food-price">
                                    <?php echo number_format($it['Giaban'], 0, ',', '.'); ?> đ
                                </div>
                                <button class="btn-add-cart" onclick="addToCart(event, '<?php echo htmlspecialchars($it['Mamon']); ?>')">
                                    <i class='bx bx-cart-add'></i> Thêm
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px;">
                    <i class='bx bx-bowl-hot' style="font-size: 60px; color: #ccc;"></i>
                    <p style="font-size: 18px; color: #666; margin-top: 20px;">Chưa có món ăn nào!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pagination -->
    <div class="pagination" id="pagination">
        <!-- Pagination will be loaded here -->
    </div>

    <script>
        // Database simulation - All menu items (now loaded from PHP)
        const allMenuItems = [
            <?php if (!empty($featuredItems)): ?>
                <?php foreach ($featuredItems as $index => $it): ?>
                {
                    id: '<?php echo htmlspecialchars($it['Mamon']); ?>',
                    name: '<?php echo addslashes(htmlspecialchars($it['Tenmon'])); ?>',
                    category: '<?php echo htmlspecialchars($it['category_id'] ?? 'other'); ?>',
                    categoryName: '<?php echo addslashes(htmlspecialchars($it['category_name'] ?? 'Khác')); ?>',
                    price: <?php echo intval($it['Giaban']); ?>,
                    image: '<?php echo htmlspecialchars(resolveImagePath($it['Anh'] ?? '')); ?>'
                }<?php echo ($index < count($featuredItems) - 1) ? ',' : ''; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        ];

        console.log('Total items loaded:', allMenuItems.length);
        console.log('Sample item:', allMenuItems[0]);

        // Pagination settings
        const ITEMS_PER_PAGE = 8;
        let currentPage = 1;
        let filteredItems = [...allMenuItems];

        // Format price
        function formatPrice(price) {
            return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        // Apply filters and sorting
        function applyFilters() {
            const category = document.getElementById('categoryFilter').value;
            const priceRange = document.getElementById('priceFilter').value;
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const sortBy = document.getElementById('sortBy').value;

            // Filter by category
            filteredItems = category === 'all' 
                ? [...allMenuItems]
                : allMenuItems.filter(item => item.category === category);

            // Filter by price
            if (priceRange !== 'all') {
                const [min, max] = priceRange.split('-').map(Number);
                filteredItems = filteredItems.filter(item => 
                    item.price >= min && item.price <= max
                );
            }

            // Filter by search term
            if (searchTerm) {
                filteredItems = filteredItems.filter(item =>
                    item.name.toLowerCase().includes(searchTerm)
                );
            }

            // Sort items
            switch(sortBy) {
                case 'name-asc':
                    filteredItems.sort((a, b) => a.name.localeCompare(b.name, 'vi'));
                    break;
                case 'name-desc':
                    filteredItems.sort((a, b) => b.name.localeCompare(a.name, 'vi'));
                    break;
                case 'price-asc':
                    filteredItems.sort((a, b) => a.price - b.price);
                    break;
                case 'price-desc':
                    filteredItems.sort((a, b) => b.price - a.price);
                    break;
            }

            // Reset to page 1
            currentPage = 1;
            renderPage();
        }

        // Render current page
        function renderPage() {
            const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
            const endIndex = startIndex + ITEMS_PER_PAGE;
            const pageItems = filteredItems.slice(startIndex, endIndex);
            const totalPages = Math.ceil(filteredItems.length / ITEMS_PER_PAGE);

            // Render food items
            const grid = document.getElementById('foodGrid');
            if (pageItems.length === 0) {
                grid.innerHTML = `
                    <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px;">
                        <i class='bx bx-bowl-hot' style="font-size: 60px; color: #ccc;"></i>
                        <p style="font-size: 18px; color: #666; margin-top: 20px;">Không tìm thấy món ăn phù hợp!</p>
                    </div>
                `;
            } else {
                grid.innerHTML = pageItems.map(item => `
                    <div class="food-item" onclick="viewProduct('${item.id}')">
                        <div class="food-image">
                            <img src="${item.image}" alt="${item.name}" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div class="food-info">
                            <div class="food-name">${item.name}</div>
                            <div class="food-footer">
                                <div class="food-price">${formatPrice(item.price)} đ</div>
                                <button class="btn-add-cart" onclick="addToCart(event, '${item.id}')">
                                    <i class='bx bx-cart-add'></i> Thêm
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('');
            }

            // Render pagination
            renderPagination(totalPages);

            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Render pagination
        function renderPagination(totalPages) {
            const pagination = document.getElementById('pagination');
            
            if (totalPages <= 1) {
                pagination.innerHTML = '';
                return;
            }

            let html = '';

            // Previous button
            html += `
                <button class="pagination-btn" onclick="goToPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
                    <i class='bx bx-chevron-left'></i> Trước
                </button>
            `;

            // Page numbers
            const maxVisiblePages = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

            if (endPage - startPage < maxVisiblePages - 1) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }

            // First page
            if (startPage > 1) {
                html += `<button class="pagination-btn" onclick="goToPage(1)">1</button>`;
                if (startPage > 2) {
                    html += `<span class="pagination-info">...</span>`;
                }
            }

            // Middle pages
            for (let i = startPage; i <= endPage; i++) {
                html += `
                    <button class="pagination-btn ${i === currentPage ? 'active' : ''}" onclick="goToPage(${i})">
                        ${i}
                    </button>
                `;
            }

            // Last page
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    html += `<span class="pagination-info">...</span>`;
                }
                html += `<button class="pagination-btn" onclick="goToPage(${totalPages})">${totalPages}</button>`;
            }

            // Next button
            html += `
                <button class="pagination-btn" onclick="goToPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
                    Sau <i class='bx bx-chevron-right'></i>
                </button>
            `;

            pagination.innerHTML = html;
        }

        // Go to specific page
        function goToPage(page) {
            const totalPages = Math.ceil(filteredItems.length / ITEMS_PER_PAGE);
            if (page < 1 || page > totalPages) return;
            
            currentPage = page;
            renderPage();
        }

        // View product details
        function viewProduct(mamon) {
            window.location.href = `/pages/chitietmonan.php?mamon=${encodeURIComponent(mamon)}`;
        }

        // Add to cart
        function addToCart(event, mamon) {
            event.stopPropagation();
            event.preventDefault();
            
            console.log('Adding to cart:', mamon);
            
            // Tạo FormData
            const formData = new FormData();
            formData.append('mamon', mamon);
            formData.append('quantity', 1);
            
            // Gửi request thêm vào giỏ hàng
            fetch('/pages/cart-add.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    alert('Đã thêm vào giỏ hàng!');
                    // Cập nhật số lượng giỏ hàng trên header
                    const badge = document.getElementById('cartBadge');
                    if (badge) {
                        badge.textContent = data.cartCount;
                    }
                } else {
                    if (data.needLogin) {
                        alert(data.message);
                        window.location.href = '/pages/login.php';
                    } else {
                        alert(data.message || 'Có lỗi xảy ra!');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi thêm vào giỏ hàng!');
            });
        }

        // Search on Enter key
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyFilters();
            }
        });

        // Initialize
        renderPage();
    </script>
</body>
</html>

<?php include __DIR__ . '/../includes/footer.php'; ?>