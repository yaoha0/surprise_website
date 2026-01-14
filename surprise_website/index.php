<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session for storing memories
session_start();

// Initialize memories array if not exists
if (!isset($_SESSION['memories'])) {
    $_SESSION['memories'] = [
        [
            'title' => 'Our First Date',
            'description' => 'The day we first met and I knew you were special. Every moment with you has been magical.',
            'image' => 'https://images.unsplash.com/photo-1518199266791-5375a83190b7?w=800',
            'type' => 'image'
        ],
        [
            'title' => 'Our Adventure Together',
            'description' => 'Remember when we explored new places and made unforgettable memories? I cherish every second.',
            'image' => 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=800',
            'type' => 'image'
        ]
    ];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_memory'])) {
    $title = isset($_POST['title']) ? htmlspecialchars(trim($_POST['title'])) : '';
    $description = isset($_POST['description']) ? htmlspecialchars(trim($_POST['description'])) : '';
    
    if (!empty($title) && !empty($description)) {
        // Handle file upload
        $mediaPath = '';
        $mediaType = 'none';
        
        if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            
            // Create uploads directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExtension = strtolower(pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION));
            $fileName = uniqid('memory_', true) . '.' . $fileExtension;
            $targetPath = $uploadDir . $fileName;
            
            // Check file size (max 10MB)
            if ($_FILES['media']['size'] <= 10485760) {
                if (move_uploaded_file($_FILES['media']['tmp_name'], $targetPath)) {
                    $mediaPath = $targetPath;
                    
                    // Determine media type
                    $allowedImages = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    $allowedVideos = ['mp4', 'webm', 'ogg', 'mov'];
                    
                    if (in_array($fileExtension, $allowedImages)) {
                        $mediaType = 'image';
                    } elseif (in_array($fileExtension, $allowedVideos)) {
                        $mediaType = 'video';
                    }
                }
            }
        }
        
        // Add new memory to session
        $_SESSION['memories'][] = [
            'title' => $title,
            'description' => $description,
            'image' => $mediaPath,
            'type' => $mediaType
        ];
        
        // Redirect to prevent form resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Handle memory deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $index = (int)$_GET['delete'];
    if (isset($_SESSION['memories'][$index]) && $index >= 2) { // Only allow deleting user-added memories
        // Delete file if exists and is in uploads directory
        if (!empty($_SESSION['memories'][$index]['image']) && 
            strpos($_SESSION['memories'][$index]['image'], 'uploads/') === 0 && 
            file_exists($_SESSION['memories'][$index]['image'])) {
            unlink($_SESSION['memories'][$index]['image']);
        }
        unset($_SESSION['memories'][$index]);
        $_SESSION['memories'] = array_values($_SESSION['memories']); // Reindex array
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Handle clear all memories
if (isset($_GET['clear_all'])) {
    // Delete all uploaded files
    foreach ($_SESSION['memories'] as $index => $memory) {
        if ($index >= 2 && !empty($memory['image']) && 
            strpos($memory['image'], 'uploads/') === 0 && 
            file_exists($memory['image'])) {
            unlink($memory['image']);
        }
    }
    // Reset to default memories
    $_SESSION['memories'] = [
        [
            'title' => 'Our First Date',
            'description' => 'The day we first met and I knew you were special. Every moment with you has been magical.',
            'image' => 'https://images.unsplash.com/photo-1518199266791-5375a83190b7?w=800',
            'type' => 'image'
        ],
        [
            'title' => 'Our Adventure Together',
            'description' => 'Remember when we explored new places and made unforgettable memories? I cherish every second.',
            'image' => 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=800',
            'type' => 'image'
        ]
    ];
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Happy Anniversary ❤️</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Happy Anniversary!</h1>
        <div class="hearts">❤️ 💕 ❤️</div>
        <p class="subtitle">Our Beautiful Journey Together</p>

        <div class="timeline" id="timeline">
            <?php if (count($_SESSION['memories']) > 0): ?>
                <?php foreach ($_SESSION['memories'] as $index => $memory): ?>
                    <div class="memory-card">
                        <h3><?php echo htmlspecialchars($memory['title']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($memory['description'])); ?></p>
                        
                        <?php if ($memory['type'] === 'image' && !empty($memory['image'])): ?>
                            <div class="media-container">
                                <img src="<?php echo htmlspecialchars($memory['image']); ?>" alt="<?php echo htmlspecialchars($memory['title']); ?>">
                            </div>
                        <?php elseif ($memory['type'] === 'video' && !empty($memory['image'])): ?>
                            <div class="media-container">
                                <video controls>
                                    <source src="<?php echo htmlspecialchars($memory['image']); ?>" type="video/<?php echo pathinfo($memory['image'], PATHINFO_EXTENSION); ?>">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($index >= 2): // Allow deletion of user-added memories only ?>
                            <a href="?delete=<?php echo $index; ?>" 
                               style="color: #f39c12; font-size: 0.9em; text-decoration: none; display: inline-block; margin-top: 10px;" 
                               onclick="return confirm('Delete this memory?')">🗑️ Delete</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; color: #999;">No memories yet. Add your first one!</p>
            <?php endif; ?>
        </div>

        <button class="add-memory-btn" onclick="openModal()">+ Add New Memory</button>
        
        <?php if (count($_SESSION['memories']) > 2): ?>
            <div style="text-align: center; margin-top: 20px;">
                <a href="?clear_all=1" 
                   style="color: #f39c12; text-decoration: none; font-size: 0.9em;" 
                   onclick="return confirm('Are you sure you want to clear all user-added memories?')">Clear All Memories</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="modal" id="modal">
        <div class="modal-content">
            <h2>Add a New Memory</h2>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Memory Title</label>
                    <input type="text" id="title" name="title" required maxlength="100">
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required maxlength="1000"></textarea>
                </div>

                <div class="form-group">
                    <label for="media">Add Photo or Video (Max 10MB)</label>
                    <input type="file" id="media" name="media" accept="image/*,video/*">
                    <div class="preview" id="preview"></div>
                </div>

                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" name="add_memory" class="btn btn-primary">Add Memory</button>
                </div>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>