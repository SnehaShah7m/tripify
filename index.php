<?php
require_once 'config.php';

// Get search parameter for home page
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query for trips
if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT trips.*, users.username FROM trips JOIN users ON trips.user_id = users.id WHERE trips.title LIKE ? OR trips.description LIKE ? ORDER BY trips.created_at DESC LIMIT 6");
    $search_param = "%$search%";
    $stmt->execute([$search_param, $search_param]);
    $trips = $stmt->fetchAll();
} else {
    $trips = $pdo->query("SELECT trips.*, users.username FROM trips JOIN users ON trips.user_id = users.id ORDER BY trips.created_at DESC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tripify - Share Your Adventures</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .search-bar {
            max-width: 500px;
            margin: 1rem auto 0;
        }
        .search-bar form {
            display: flex;
            gap: 0.5rem;
        }
        .search-bar input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #cdcdca;
            border-radius: 2rem;
            font-size: 1rem;
        }
        .search-bar button {
            padding: 12px 24px;
            background: #1e1e20;
            color: white;
            border: none;
            border-radius: 2rem;
            cursor: pointer;
        }
        .search-bar button:hover {
            background: #2c2c2e;
        }
        .search-info {
            text-align: center;
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
        <div class="hero-section">
            <h1>Explore, Share, Inspire ✈️</h1>
            <p>Document your journeys, share amazing experiences, and discover incredible trips from fellow travelers around the world.</p>
            
            <!-- Search Bar on Home Page -->
            <div class="search-bar">
                <form method="GET" action="index.php">
                    <input type="text" name="search" placeholder="Search trips by title or description..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="fas fa-search"></i> Search</button>
                </form>
            </div>
            
            <?php if (!isLoggedIn()): ?>
                <div style="margin-top: 1.5rem;">
                    <a href="register.php" class="btn btn-primary">Join Tripify Now</a>
                    <a href="login.php" class="btn btn-outline">Sign In</a>
                </div>
            <?php else: ?>
                <div style="margin-top: 1.5rem;">
                    <a href="create_trip.php" class="btn btn-primary">Share Your Trip</a>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($search): ?>
            <div class="search-info">
                <div class="alert alert-info" style="background: #e3f2fd; padding: 0.75rem; border-radius: 0.5rem;">
                    <i class="fas fa-search"></i> Showing results for: "<strong><?php echo htmlspecialchars($search); ?></strong>"
                    <a href="index.php" style="margin-left: 1rem;">Clear search</a>
                </div>
            </div>
        <?php endif; ?>
        
        <h2 style="margin-bottom: 1rem;">✨ <?php echo $search ? 'Search Results' : 'Recent Journeys'; ?></h2>
        <div class="trips-grid">
            <?php 
            $displayTrips = $search ? $trips : array_slice($trips, 0, 3);
            if (count($displayTrips) > 0):
                foreach ($displayTrips as $trip): 
            ?>
                <div class="card">
                    <?php 
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
                        <p class="card-description"><?php echo htmlspecialchars(substr($trip['description'], 0, 120)); ?>...</p>
                        <?php if ($trip['youtube_link']): ?>
                            <a href="<?php echo htmlspecialchars($trip['youtube_link']); ?>" target="_blank" style="color: #ff0000; text-decoration: none;">
                                <i class="fab fa-youtube"></i> Watch Video
                            </a>
                        <?php endif; ?>
                        <div class="card-meta">
                            <span class="user-badge">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($trip['username']); ?>
                            </span>
                            <span><i class="far fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($trip['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
            <?php 
                endforeach;
            else:
            ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 3rem;">
                    <i class="fas fa-search" style="font-size: 3rem; color: #999;"></i>
                    <h3>No trips found matching "<?php echo htmlspecialchars($search); ?>"</h3>
                    <p>Try different keywords or <a href="create_trip.php">create your own trip</a></p>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!$search && count($trips) > 3): ?>
            <div style="text-align: center; margin-top: 2rem;">
                <a href="trips.php" class="btn btn-outline">View All Trips →</a>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>© 2025 Tripify — Share your adventures ✈️ | College Project</p>
    </footer>
</body>
</html>