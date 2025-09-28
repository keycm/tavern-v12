<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tavern Publico - Menu</title>
    <link rel="stylesheet" href="CSS/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* ALL STYLES FOR THE MENU PAGE ARE NOW INLINED HERE */

        /* Menu Section Styles */
        .menu-section {
            padding: 60px 0;
            background-color: #f8f8f8;
        }

        .menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 15px 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .category-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .category-btn {
            background-color: #f0f0f0;
            color: #555;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 500;
            transition: background-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .category-btn i { color: #888; }
        .category-btn:hover { background-color: #e0e0e0; }
        .category-btn.active { background-color: #FFD700; color: #333; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .category-btn.active i { color: #333; }

        .search-sort { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
        .search-bar { position: relative; }
        .search-bar input { padding: 10px 15px 10px 40px; border: 1px solid #ddd; border-radius: 25px; font-size: 0.95em; width: 200px; transition: border-color 0.3s ease; }
        .search-bar input:focus { border-color: #FFD700; outline: none; }
        .search-bar i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #999; }
        .sort-by { display: flex; align-items: center; gap: 10px; }
        .sort-by label { font-weight: 500; color: #555; }
        .sort-by select { padding: 8px 15px; border: 1px solid #ddd; border-radius: 25px; background-color: #fff; font-size: 0.95em; cursor: pointer; }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            justify-content: center;
        }

        .menu-item-card {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            text-align: left;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 380px;
        }

        .menu-item-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
        }

        .menu-item-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .menu-item-card h3 {
            font-family: 'Mada', sans-serif;
            font-size: 1.6em;
            margin: 20px 20px 10px;
            color: #222;
            font-weight: 600;
            line-height: 1.3;
        }

        .item-price-add {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px 20px;
            margin-top: auto;
        }

        .item-price-add .price {
            font-family: 'Mada', sans-serif;
            font-size: 1.5em;
            font-weight: 700;
            color: #333;
        }
        
        /* --- THIS IS THE CORRECT BUTTON STYLE --- */
        .view-details-btn {
            background-color: #FFD700; /* Vibrant yellow */
            color: #333; /* Dark color for the icon */
            border: none;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.4rem; /* Icon size */
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
        }

        .view-details-btn i {
            font-weight: 600; /* Make the '+' icon a bit bolder */
            transition: transform 0.2s ease-in-out;
        }

        .view-details-btn:hover {
            background-color: #e6c200; /* Darker yellow on hover */
            transform: scale(1.1);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .view-details-btn:active {
            transform: scale(0.95); /* Press-down effect */
        }


        /* --- STYLES FOR MENU ITEM MODAL --- */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 2000; 
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.6);
            justify-content: center;
            align-items: center;
        }

        .item-modal-content {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            width: 90%;
            max-width: 500px !important;
            padding: 0 !important;
            text-align: left;
            position: relative;
            animation: fadeIn 0.4s;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .item-modal-content .close-button {
            position: absolute;
            top: 10px;
            right: 20px;
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .item-modal-content img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .modal-item-details {
            padding: 25px;
        }

        .modal-item-details h2 {
            font-size: 2em;
            margin-top: 0;
            margin-bottom: 15px;
            text-align: left;
            color: #222;
        }

        .modal-item-details p {
            font-size: 1.1em;
            color: #555;
            line-height: 1.7;
            margin-bottom: 20px;
        }

        .modal-price-tag {
            font-size: 1.8em;
            font-weight: 700;
            color: #333;
            text-align: right;
        }

        /* Responsive adjustments */
        @media (max-width: 1024px) {
            .menu-header { flex-direction: column; align-items: flex-start; }
            .search-sort { width: 100%; justify-content: space-between; }
            .search-bar { flex-grow: 1; }
            .search-bar input { width: 100%; }
        }

        @media (max-width: 768px) {
            .menu-header { padding: 10px 15px; margin-bottom: 30px; }
            .category-buttons { justify-content: center; width: 100%; }
            .category-btn { padding: 8px 15px; font-size: 0.9em; }
            .search-sort { flex-direction: column; align-items: stretch; gap: 15px; }
            .menu-grid { grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        }
    </style>
</head>
<body>

    <?php
    include 'partials/header.php';
    include 'config.php';
    ?>

    <main>
        <section class="menu-section common-padding">
            <div class="container">
                <div class="menu-header">
                    <div class="category-buttons">
                        <button class="category-btn active" data-category="All"><i class="fas fa-list"></i> All Items</button>
                        <button class="category-btn" data-category="Specialty"><i class="fas fa-utensils"></i> Specialty</button>
                        <button class="category-btn" data-category="Breakfast"><i class="fas fa-pizza-slice"></i> All Day Breakfast</button>
                        <button class="category-btn" data-category="Lunch"><i class="fas fa-hamburger"></i> Ala Carte/For Sharing</button>
                        <button class="category-btn" data-category="Sizzlers"><i class="fas fa-fish"></i> Sizzling Plates</button>
                        <button class="category-btn" data-category="Coffee"><i class="fas fa-coffee"></i> Cafe Drinks</button>
                        <button class="category-btn" data-category="Cool Creations"><i class="fas fa-ice-cream"></i> Frappe</button>
                    </div>
                    <div class="search-sort">
                        <div class="search-bar">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" placeholder="Search menu...">
                        </div>
                        <div class="sort-by">
                            <label for="sort-select">Sort by:</label>
                            <select id="sort-select">
                                <option value="popular">Popular</option>
                                <option value="price-low-high">Price (Low to High)</option>
                                <option value="price-high-low">Price (High to Low)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="menu-grid">
                    <?php
                    $sql = "SELECT * FROM menu ORDER BY category, name";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<div class="menu-item-card" 
                                    data-name="' . htmlspecialchars($row['name'], ENT_QUOTES) . '" 
                                    data-image="' . htmlspecialchars($row['image']) . '" 
                                    data-price="₱' . number_format($row['price'], 2) . '" 
                                    data-description="' . htmlspecialchars($row['description'], ENT_QUOTES) . '"
                                    data-category="' . htmlspecialchars($row['category']) . '">';
                            
                            echo '  <img src="' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['name']) . '">';
                            echo '  <h3>' . htmlspecialchars($row['name']) . '</h3>';
                            echo '  <div class="item-price-add">';
                            echo '    <span class="price">₱' . number_format($row['price'], 2) . '</span>';
                            echo '    <button class="view-details-btn"><i class="fas fa-plus"></i></button>';
                            echo '  </div>';
                            echo '</div>';
                        }
                    } else {
                        echo "<p>No menu items found.</p>";
                    }
                    ?>
                </div>
            </div>
        </section>
    </main>

    <?php
    include 'partials/footer.php';
    include 'partials/Signin-Signup.php';
    ?>
    
    <div id="menuItemModal" class="modal">
        <div class="modal-content item-modal-content">
            <span class="close-button">&times;</span>
            <img id="modalItemImage" src="" alt="Menu Item Image">
            <div class="modal-item-details">
                <h2 id="modalItemName"></h2>
                <p id="modalItemDescription"></p>
                <div class="modal-price-tag" id="modalItemPrice"></div>
            </div>
        </div>
    </div>

    <script src="JS/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // --- MODAL LOGIC ---
            const menuItemModal = document.getElementById('menuItemModal');
            const modalCloseButton = menuItemModal.querySelector('.close-button');
            const modalImage = document.getElementById('modalItemImage');
            const modalName = document.getElementById('modalItemName');
            const modalDescription = document.getElementById('modalItemDescription');
            const modalPrice = document.getElementById('modalItemPrice');

            const openItemModal = (card) => {
                modalImage.src = card.dataset.image;
                modalName.textContent = card.dataset.name;
                modalDescription.textContent = card.dataset.description;
                modalPrice.textContent = card.dataset.price;
                menuItemModal.style.display = 'flex';
            };

            const closeItemModal = () => {
                menuItemModal.style.display = 'none';
            };

            document.querySelectorAll('.view-details-btn').forEach(button => {
                button.addEventListener('click', (e) => {
                    const card = e.target.closest('.menu-item-card');
                    openItemModal(card);
                });
            });

            modalCloseButton.addEventListener('click', closeItemModal);
            window.addEventListener('click', (event) => {
                if (event.target == menuItemModal) {
                    closeItemModal();
                }
            });
            
            // --- FILTER AND SORT LOGIC ---
            const categoryButtons = document.querySelectorAll('.category-btn');
            const menuItems = document.querySelectorAll('.menu-item-card');
            const searchInput = document.getElementById('searchInput');
            const sortBySelect = document.getElementById('sort-select');
            
            const filterAndSort = () => {
                const activeCategory = document.querySelector('.category-btn.active').dataset.category;
                const searchTerm = searchInput.value.toLowerCase();
                const sortValue = sortBySelect.value;
                const menuGrid = document.querySelector('.menu-grid');
                let itemsToShow = Array.from(menuItems);

                itemsToShow.forEach(item => {
                    const isVisibleByCategory = activeCategory === 'All' || item.dataset.category === activeCategory;
                    const itemName = item.dataset.name.toLowerCase();
                    const isVisibleBySearch = itemName.includes(searchTerm);
                    item.style.display = (isVisibleByCategory && isVisibleBySearch) ? 'flex' : 'none';
                });

                let visibleItems = itemsToShow.filter(item => item.style.display !== 'none');
                
                visibleItems.sort((a, b) => {
                    if (sortValue === 'popular') return 0;
                    const priceA = parseFloat(a.dataset.price.replace(/[₱,]/g, ''));
                    const priceB = parseFloat(b.dataset.price.replace(/[₱,]/g, ''));
                    if (sortValue === 'price-low-high') return priceA - priceB;
                    if (sortValue === 'price-high-low') return priceB - priceA;
                    return 0;
                });

                visibleItems.forEach(item => menuGrid.appendChild(item));
            };

            categoryButtons.forEach(button => {
                button.addEventListener('click', () => {
                    categoryButtons.forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');
                    filterAndSort();
                });
            });

            searchInput.addEventListener('input', filterAndSort);
            sortBySelect.addEventListener('change', filterAndSort);
        });
    </script>
</body>
</html>