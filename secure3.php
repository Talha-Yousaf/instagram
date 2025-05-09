<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: signin_form.php");
    exit();
}

// Database connection
$conn = mysqli_connect("bbqserver.mysql.database.azure.com", "mylogin", 'TALHAulster"12', "videos1");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle video and thumbnail upload
if (isset($_POST['upload_video'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $publisher = $_POST['publisher'];
    $producer = $_POST['producer'];
    $genre = $_POST['genre'];
    $ageRating = $_POST['age_rating'];

    // Array of public video URLs
    $publicVideoLinks = [
        "https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4",
        "https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/Sintel.mp4",
        "https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4",
        "https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4",
        "https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4"
    ];

    // Array of public thumbnail image URLs
    $publicThumbnails = [
        "https://peach.blender.org/wp-content/uploads/title_anouncement.jpg?x11217",
        "https://upload.wikimedia.org/wikipedia/commons/thumb/e/e0/Sintel_poster.jpg/800px-Sintel_poster.jpg",
        "https://mango.blender.org/wp-content/uploads/2013/05/ToS_poster.jpg",
        "https://orange.blender.org/wp-content/themes/orange/images/common/elephants-dream.jpg",
        "https://via.placeholder.com/300x200.png?text=Sample+Thumbnail"
    ];

    // Pick random video and thumbnail
    $randomVideoUrl = $publicVideoLinks[array_rand($publicVideoLinks)];
    $randomThumbnailUrl = $publicThumbnails[array_rand($publicThumbnails)];

    $uploader_id = $_SESSION['id'];
    $upload_datetime = date("Y-m-d H:i:s");

    // Insert into DB using public URLs
    $sql = "INSERT INTO videos (title, description, publisher, producer, genre, AgeRating, filename, thumbnail, uploader_id, upload_datetime) 
            VALUES ('$title', '$description', '$publisher', '$producer', '$genre', '$ageRating', '$randomVideoUrl', '$randomThumbnailUrl', '$uploader_id', '$upload_datetime')";

    if (mysqli_query($conn, $sql)) {
        echo "Video and thumbnail were assigned from public sources and saved successfully.";
    } else {
        echo "Database Error: " . mysqli_error($conn);
    }
}

// Fetch genres from the Genres table
$genre_query = "SELECT genre_name FROM Genres";
$genre_result = mysqli_query($conn, $genre_query);
$genres = array();
while ($row = mysqli_fetch_assoc($genre_result)) {
    $genres[] = $row['genre_name'];
}

// Fetch all age ratings from the AgeRating table
$age_rating_query = "SELECT rating_name FROM AgeRating";
$age_rating_result = mysqli_query($conn, $age_rating_query);
$age_ratings = array();
while ($row = mysqli_fetch_assoc($age_rating_result)) {
    $age_ratings[] = $row['rating_name'];
}

// Delete selected videos
if (isset($_POST['delete_videos'])) {
    if (isset($_POST['videos']) && !empty($_POST['videos'])) {
        $videos_to_delete = $_POST['videos'];
        foreach ($videos_to_delete as $video_id) {
            // Delete associated likes
            mysqli_query($conn, "DELETE FROM Likes WHERE video_id = $video_id");
            
            // Delete associated dislikes
            mysqli_query($conn, "DELETE FROM Dislikes WHERE video_id = $video_id");
            
            // Delete associated comments
            mysqli_query($conn, "DELETE FROM Comments WHERE video_id = $video_id");
            
            // Fetch video and thumbnail filenames
            $file_query = "SELECT filename, thumbnail FROM videos WHERE id = $video_id";
            $file_result = mysqli_query($conn, $file_query);
            $file_row = mysqli_fetch_assoc($file_result);
            $video_filename = $file_row['filename'];
            $thumbnail_filename = $file_row['thumbnail'];
            
            // Delete video file
            $video_path = "uploads/" . $video_filename;
            if (file_exists($video_path)) {
                unlink($video_path);
            }
            
            // Delete thumbnail file
            $thumbnail_path = "uploads/" . $thumbnail_filename;
            if (file_exists($thumbnail_path)) {
                unlink($thumbnail_path);
            }
            
            // Delete video entry from database
            mysqli_query($conn, "DELETE FROM videos WHERE id = $video_id");
        }
        echo "Selected videos along with their associated likes, dislikes, comments, and files have been deleted.";
    } else {
        echo "No videos selected for deletion.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Page</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #121212;
        color: #ffffff;
        margin: 0;
        padding: 20px;
    }

    .container {
        padding: 20px;
        background-color: #1f1f1f;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.6);
    }

    .nav-tabs {
        background-color: #1f1f1f;
        border-bottom: 1px solid #333;
    }

    .nav-tabs .nav-link {
        color: #cccccc;
        background-color: transparent;
        border: none;
        transition: background-color 0.3s ease;
    }

    .nav-tabs .nav-link.active {
        background-color: #e50914;
        color: #fff;
        border-radius: 6px 6px 0 0;
    }

    .nav-tabs .nav-link:hover {
        background-color: #333;
    }

    .upload-container {
        background-color: #2c2c2c;
        padding: 20px;
        border-radius: 10px;
        margin-top: 20px;
    }

    .upload-container h3,
    .video-container h3 {
        color: #e50914;
    }

    .form-group label {
        color: #ffffff;
    }

    .form-control,
    .form-control-file {
        background-color: #1f1f1f;
        color: #ffffff;
        border: 1px solid #444;
        border-radius: 5px;
        padding: 10px;
    }

    .form-control:focus {
        border-color: #e50914;
        outline: none;
        box-shadow: 0 0 5px rgba(229, 9, 20, 0.7);
    }

    .btn-primary {
        background-color: #e50914;
        border-color: #e50914;
        font-weight: bold;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .btn-primary:hover {
        background-color: transparent;
        color: #e50914;
        border-color: #e50914;
    }

    .btn-danger {
        background-color: #b00610;
        border-color: #b00610;
    }

    .btn-danger:hover {
        background-color: transparent;
        color: #b00610;
    }

    .video-link {
        color: #ffffff;
        text-decoration: none;
        margin-left: 10px;
        transition: color 0.3s ease;
    }

    .video-link:hover {
        color: #e50914;
        text-decoration: underline;
    }

    input[type="checkbox"] {
        transform: scale(1.2);
    }

    .video-container {
        background-color: #2a2a2a;
        padding: 20px;
        border-radius: 10px;
        margin-top: 20px;
    }

    .form-control-file {
        padding: 5px;
    }

    select.form-control option {
        background-color: #1f1f1f;
        color: #ffffff;
    }

    .logout {
        color: #e50914;
        text-decoration: none;
        float: right;
        font-weight: bold;
    }

    .logout:hover {
        text-decoration: underline;
    }
    </style>
</head>

<body>
    <div class="container">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link" href="index.php">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="#">Secure Page</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Logout</a>
            </li>
        </ul>
        <div class="row">
            <div class="col-md-6">
                <!-- Dropdown menu to select videos -->
                <form action="" method="GET" class="mb-3">
                    <label for="view-option">View Videos:</label>
                    <select name="view-option" id="view-option" class="form-control">
                        <option value="all">All Videos</option>
                        <option value="uploaded-by-me">Uploaded by Me</option>
                    </select>
                    <button type="submit" class="btn btn-primary mt-2">View</button>
                </form>

                <!-- Uploaded videos -->
                <div class="video-container">
                    <h3>Uploaded Videos</h3>
                    <form action="" method="POST">
                        <?php
                        // Fetch uploaded videos from the database based on the selected option
                        $view_option = isset($_GET['view-option']) ? $_GET['view-option'] : 'all';

                        if ($view_option === 'all') {
                            $query = "SELECT * FROM videos";
                        } else {
                            $id = $_SESSION['id'];
                            $query = "SELECT * FROM videos WHERE uploader_id = $id";
                        }

                        $result = mysqli_query($conn, $query); // Define $result here

                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<div><input type='checkbox' name='videos[]' value='" . $row['id'] . "'><a href='view_video.php?id=" . $row['id'] . "' class='video-link'>" . $row['title'] . "</a></div>";
                            }
                        } else {
                            echo "No videos uploaded yet.";
                        }
                        ?>
                        <button type="submit" class="btn btn-danger mt-2" name="delete_videos">Delete Selected Videos</button>
                    </form>
                </div>
            </div>

            <div class="col-md-6">
                <!-- Upload form -->
                <div class="upload-container">
                    <h3>Upload Video</h3>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="title">Title:</label>
                            <input type="text" class="form-control" id="title" name="title">
                        </div>
                        <div class="form-group">
                            <label for="description">Description:</label>
                            <textarea class="form-control" id="description" name="description"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="publisher">Publisher:</label>
                            <input type="text" class="form-control" id="publisher" name="publisher">
                        </div>
                        <div class="form-group">
                            <label for="producer">Producer:</label>
                            <input type="text" class="form-control" id="producer" name="producer">
                        </div>
                        <div class="form-group">
                            <label for="genre">Genre:</label>
                            <select class="form-control" id="genre" name="genre">
                                <?php foreach ($genres as $genre) : ?>
                                    <option value="<?php echo $genre; ?>"><?php echo $genre; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="age_rating">Age Rating:</label>
                            <select class="form-control" id="age_rating" name="age_rating">
                                <option value="">Select Age Rating</option>
                                <?php foreach ($age_ratings as $age_rating) : ?>
                                    <option value="<?php echo $age_rating; ?>"><?php echo $age_rating; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="fileToUpload">Select video to upload:</label>
                            <input type="file" class="form-control-file" name="fileToUpload" id="fileToUpload">
                        </div>
                        <div class="form-group">
                            <label for="thumbnailToUpload">Select thumbnail image to upload:</label>
                            <input type="file" class="form-control-file" name="thumbnailToUpload" id="thumbnailToUpload">
                        </div>
                        <button type="submit" class="btn btn-primary" name="upload_video">Upload Video</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>

<?php
mysqli_close($conn);
?>
