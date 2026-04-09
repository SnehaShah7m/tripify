<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $youtube_link = trim($_POST['youtube_link']);
    $image_path = '';
    
    if (empty($title) || empty($description)) {
        $error = "Title and description are required!";
    } else {
        // Handle image upload
        if (isset($_FILES['trip_image']) && $_FILES['trip_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/';
            
            // Create uploads directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Get file info
            $file_name = $_FILES['trip_image']['name'];
            $file_tmp = $_FILES['trip_image']['tmp_name'];
            $file_size = $_FILES['trip_image']['size'];
            $file_error = $_FILES['trip_image']['error'];
            
            // Allowed file types
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
            $file_type = mime_content_type($file_tmp);
            
            // Check file type
            if (in_array($file_type, $allowed_types)) {
                // Check file size (max 10MB)
                if ($file_size <= 10 * 1024 * 1024) {
                    // Generate unique filename
                    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
                    $new_filename = time() . '_' . uniqid() . '.' . $file_extension;
                    $destination = $upload_dir . $new_filename;
                    
                    // Move uploaded file
                    if (move_uploaded_file($file_tmp, $destination)) {
                        $image_path = $destination;
                    } else {
                        $error = "Failed to upload image!";
                    }
                } else {
                    $error = "Image size must be less than 10MB!";
                }
            } else {
                $error = "Only JPG, PNG, GIF, WEBP images are allowed!";
            }
        }
        
        // If no error, save trip to database
        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO trips (user_id, title, description, image_url, youtube_link) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $title, $description, $image_path, $youtube_link]);
                $success = "Trip posted successfully!";
                
                // Clear form after success
                echo '<script>setTimeout(function(){ window.location.href = "trips.php"; }, 1500);</script>';
            } catch(PDOException $e) {
                $error = "Failed to save trip: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Trip - Tripify</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .image-preview {
            margin-top: 10px;
            display: none;
        }
        .image-preview img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 5px;
        }
        .file-input-wrapper {
            position: relative;
            margin-bottom: 10px;
        }
        .file-input-wrapper input[type="file"] {
            padding: 10px;
            border: 1px solid #cdcdca;
            border-radius: 0.75rem;
            width: 100%;
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
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?> Redirecting to trips...</div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <h2 style="text-align: center; margin-bottom: 1.5rem;">
                <i class="fas fa-plus-circle"></i> Share Your Trip
            </h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Trip Title *</label>
                    <input type="text" name="title" class="form-control" required maxlength="120">
                </div>
                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="description" class="form-control" required placeholder="Tell us about your amazing journey..."></textarea>
                </div>
                <div class="form-group">
                    <label>Upload Image (optional)</label>
                    <div class="file-input-wrapper">
                        <input type="file" name="trip_image" id="trip_image" accept="image/*" onchange="previewImage(this)">
                    </div>
                    <div class="image-preview" id="imagePreview">
                        <img id="previewImg" src="#" alt="Image preview">
                    </div>
                    <small style="color: #666;">Supported formats: JPG, PNG, GIF, WEBP (Max 5MB)</small>
                </div>
                <div class="form-group">
                    <label>YouTube Video Link (optional)</label>
                    <input type="url" name="youtube_link" class="form-control" placeholder="https://www.youtube.com/watch?v=...">
                </div>
                <button type="submit" name="create_trip" class="btn btn-primary" style="width: 100%;">Publish Trip</button>
            </form>
        </div>
    </div>

    <footer class="footer">
        <p>© 2025 Tripify — Share your adventures ✈️ | College Project</p>
    </footer>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
                previewImg.src = '#';
            }
        }
    </script>
</body>
</html>