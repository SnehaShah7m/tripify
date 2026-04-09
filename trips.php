<?php
require_once 'config.php';

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 6; // Trips per page
$offset = ($page - 1) * $limit;

// Build query with filters
$query = "SELECT trips.*, users.username FROM trips JOIN users ON trips.user_id = users.id";
$count_query = "SELECT COUNT(*) as total FROM trips JOIN users ON trips.user_id = users.id";
$params = [];

// Add search condition
if (!empty($search)) {
    $query .= " WHERE trips.title LIKE ? OR trips.description LIKE ?";
    $count_query .= " WHERE trips.title LIKE ? OR trips.description LIKE ?";
    $search_param = "%$search%";
    $params = [$search_param, $search_param];
}

// Add sorting
switch ($sort) {
    case 'oldest':
        $query .= " ORDER BY trips.created_at ASC";
        break;
    case 'title_asc':
        $query .= " ORDER BY trips.title ASC";
        break;
    case 'title_desc':
        $query .= " ORDER BY trips.title DESC";
        break;
    default: // newest
        $query .= " ORDER BY trips.created_at DESC";
}

// Add pagination
$query .= " LIMIT $limit OFFSET $offset";

// Execute queries
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$trips = $stmt->fetchAll();

// Get total count for pagination
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_trips = $count_stmt->fetch()['total'];
$total_pages = ceil($total_trips / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Trips - Tripify</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .search-section {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .search-form {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        .search-group {
            flex: 1;
            min-width: 200px;
        }
        .search-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #1e1e20;
        }
        .search-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cdcdca;
            border-radius: 0.75rem;
            font-size: 1rem;
        }
        .search-input:focus {
            outline: none;
            border-color: #1e1e20;
        }
        .btn-search {
            background-color: #1e1e20;
            color: white;
            padding: 10px 24px;
            border: none;
            border-radius: 0.75rem;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn-search:hover {
            background-color: #2c2c2e;
        }
        .btn-reset {
            background-color: #eaeae8;
            color: #1e1e20;
            padding: 10px 24px;
            border: none;
            border-radius: 0.75rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .sort-buttons {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        .sort-btn {
            padding: 6px 16px;
            background: #f0f0ed;
            color: #1e1e20;
            text-decoration: none;
            border-radius: 2rem;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        .sort-btn.active {
            background-color: #1e1e20;
            color: white;
        }
        .sort-btn:hover {
            background-color: #2c2c2e;
            color: white;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        .pagination a, .pagination span {
            padding: 8px 16px;
            background: white;
            border: 1px solid #cdcdca;
            color: #1e1e20;
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.3s;
        }
        .pagination a:hover {
            background-color: #1e1e20;
            color: white;
        }
        .pagination .active {
            background-color: #1e1e20;
            color: white;
            border-color: #1e1e20;
        }
        .results-count {
            text-align: center;
            margin-top: 1rem;
            color: #666;
            font-size: 0.9rem;
        }
        .no-results {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 1rem;
        }
        .no-results i {
            font-size: 3rem;
            color: #999;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-brand">
                <i class="fas fa-compass"></i>
                <span>Tripify</span>
            </a>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="trips.php" class="nav-link">All Trips</a>
                <?php if (isLoggedIn()): ?>
                    <a href="create_trip.php" class="nav-link">+ Post Trip</a>
                    <div class="user-greeting">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </div>
                    <a href="logout.php" class="btn-logout">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link">Login</a>
                    <a href="register.php" class="nav-link">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1 style="margin-bottom: 1rem;">🌍 All Travel Stories</h1>
        
        <!-- Search and Filter Section -->
        <div class="search-section">
            <form method="GET" action="trips.php" class="search-form">
                <div class="search-group">
                    <label><i class="fas fa-search"></i> Search Trips</label>
                    <input type="text" name="search" class="search-input" placeholder="Search by title or description..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="search-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn-search"><i class="fas fa-search"></i> Search</button>
                    <?php if ($search): ?>
                        <a href="trips.php" class="btn-reset"><i class="fas fa-times"></i> Clear</a>
                    <?php endif; ?>
                </div>
            </form>
            
            <div class="sort-buttons">
                <span style="margin-right: 0.5rem; color: #666;">Sort by:</span>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'newest', 'page' => 1])); ?>" class="sort-btn <?php echo $sort == 'newest' ? 'active' : ''; ?>">Newest First</a>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'oldest', 'page' => 1])); ?>" class="sort-btn <?php echo $sort == 'oldest' ? 'active' : ''; ?>">Oldest First</a>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'title_asc', 'page' => 1])); ?>" class="sort-btn <?php echo $sort == 'title_asc' ? 'active' : ''; ?>">Title A-Z</a>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'title_desc', 'page' => 1])); ?>" class="sort-btn <?php echo $sort == 'title_desc' ? 'active' : ''; ?>">Title Z-A</a>
            </div>
        </div>
        
        <?php if ($search): ?>
            <div class="alert alert-info" style="background: #e3f2fd; margin-bottom: 1rem;">
                <i class="fas fa-info-circle"></i> Showing results for: "<strong><?php echo htmlspecialchars($search); ?></strong>" 
                (<?php echo $total_trips; ?> trip<?php echo $total_trips != 1 ? 's' : ''; ?> found)
            </div>
        <?php endif; ?>
        
        <div class="trips-grid">
            <?php if (count($trips) > 0): ?>
                <?php foreach ($trips as $trip): ?>
                    <div class="card">
                        <?php 
                        // Handle both uploaded images and URL images
                        $image_path = $trip['image_url'];
                        $image_exists = false;
                        
                        if ($image_path && file_exists($image_path)) {
                            $image_exists = true;
                        } elseif ($image_path && filter_var($image_path, FILTER_VALIDATE_URL)) {
                            $image_exists = true;
                        }
                        ?>
                        
                        <?php if ($image_exists): ?>
                            <img src="<?php echo htmlspecialchars($trip['image_url']); ?>" class="card-image" alt="Trip image" onerror="this.src='https://placehold.co/600x400/eaeae8/1e1e20?text=No+Image'">
                        <?php else: ?>
                            <div class="card-image" style="display: flex; align-items: center; justify-content: center; background: #eaeae8;">
                                <i class="fas fa-image" style="font-size: 3rem; color: #999;"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-content">
                            <h3 class="card-title"><?php echo htmlspecialchars($trip['title']); ?></h3>
                            <p class="card-description"><?php echo nl2br(htmlspecialchars(substr($trip['description'], 0, 150))); ?><?php echo strlen($trip['description']) > 150 ? '...' : ''; ?></p>
                            
                            <?php if ($trip['youtube_link']): 
                                preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\?]+)/', $trip['youtube_link'], $matches);
                                if ($matches):
                            ?>
                                <div style="margin: 1rem 0; position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 0.5rem;">
                                    <iframe style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" 
                                            src="https://www.youtube.com/embed/<?php echo $matches[1]; ?>" 
                                            frameborder="0" allowfullscreen></iframe>
                                </div>
                            <?php else: ?>
                                <a href="<?php echo htmlspecialchars($trip['youtube_link']); ?>" target="_blank" style="color: #ff0000; text-decoration: none;">
                                    <i class="fab fa-youtube"></i> Watch on YouTube
                                </a>
                            <?php endif; endif; ?>
                            
                            <div class="card-meta">
                                <span class="user-badge">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($trip['username']); ?>
                                </span>
                                <span><i class="far fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($trip['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results" style="grid-column: 1/-1;">
                    <i class="fas fa-search"></i>
                    <h3>No trips found</h3>
                    <p>Try different search terms or <?php if (!isLoggedIn()): ?><a href="register.php">join Tripify</a> to share your first trip!<?php else: ?><a href="create_trip.php">create your first trip</a><?php endif; ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"><i class="fas fa-chevron-left"></i> Previous</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next <i class="fas fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>
            <div class="results-count">
                Showing <?php echo $offset + 1; ?> - <?php echo min($offset + $limit, $total_trips); ?> of <?php echo $total_trips; ?> trips
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>© 2025 Tripify — Share your adventures ✈️ | College Project</p>
    </footer>
</body>
</html>