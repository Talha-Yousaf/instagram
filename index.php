<?php
session_start();

// Display success message if available
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-success" role="alert">' . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']);
}

// Database connection
$hostname = "bbqserver.mysql.database.azure.com";
$username = "mylogin";
$password = 'TALHAulster"12'; 
$dbname = "videos1";

$conn = mysqli_connect($hostname, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch genres from the genres table
$genreQuery = "SELECT DISTINCT genre_name FROM genres";
$genreResult = mysqli_query($conn, $genreQuery);
$genres = [];
if ($genreResult && mysqli_num_rows($genreResult) > 0) {
    while ($row = mysqli_fetch_assoc($genreResult)) {
        $genres[] = $row['genre_name'];
    }
}

// Fetch age ratings from the agerating table
$ageRatingQuery = "SELECT DISTINCT rating_name FROM agerating";
$ageRatingResult = mysqli_query($conn, $ageRatingQuery);
$ageRatings = [];
if ($ageRatingResult && mysqli_num_rows($ageRatingResult) > 0) {
    while ($row = mysqli_fetch_assoc($ageRatingResult)) {
        $ageRatings[] = $row['rating_name'];
    }
}

// Check if the user clicked the search button
if (isset($_POST['search'])) {
    $search = $_POST['search'];
    $genre = isset($_POST['genre']) ? $_POST['genre'] : '';
    $ageRating = isset($_POST['age_rating']) ? $_POST['age_rating'] : '';

    // Construct the query based on search terms
    $query = "SELECT * FROM videos WHERE (title LIKE '%$search%' OR description LIKE '%$search%' OR Producer LIKE '%$search%' OR Genre LIKE '%$search%' OR AgeRating LIKE '%$search%')";
    if ($genre != '') {
        $query .= " AND Genre = '$genre'";
    }
    if ($ageRating != '') {
        $query .= " AND AgeRating = '$ageRating'";
    }

    $result = mysqli_query($conn, $query);
} else {
    // If not, fetch all videos
    $query = "SELECT * FROM videos";
    $result = mysqli_query($conn, $query);
}

// // Process sign-up form submission
// if (isset($_POST['signup'])) {
//     $username = $_POST['username'];
//     $password = $_POST['password'];
//     $fname = $_POST['fname'];
//     $lname = $_POST['lname'];
//     $email = $_POST['email'];
//     $contact = $_POST['contact'];

//     // Insert data into users table
//     $signup_query = "INSERT INTO users (username, password, FName, LName, Email, ContactNumber) VALUES ('$username', '$password', '$fname', '$lname', '$email', '$contact')";
//     if (mysqli_query($conn, $signup_query)) {
//         // Redirect to sign-in page after successful sign-up
//         header("Location: index.php");
//         exit();
//     } else {
//         echo "Error: " . $signup_query . "<br>" . mysqli_error($conn);
//     }
// }

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Netflix</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        :root {
      --primary-color: #121212;
      --secondary-color: #1f1f1f;
      --accent-color: #e50914;
      --text-color: #ffffff;
      --muted-color: #cccccc;
    }

    body {
      background-color: var(--primary-color);
      color: var(--text-color);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
    }

    .navbar {
      background-color: var(--secondary-color);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .navbar h2 {
      color: var(--accent-color);
      font-weight: bold;
      margin: 0;
    }

    .navbar-links {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .navbar a {
      color: var(--text-color);
      text-decoration: none;
      font-weight: 500;
    }

    .navbar a:hover {
      color: var(--accent-color);
    }

    .search-form {
      margin: 2rem auto;
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      justify-content: center;
    }

    .form-control,
    .btn-search {
      border-radius: 30px;
      padding: 10px 20px;
      border: 1px solid #444;
    }

    .form-control {
      background-color: var(--secondary-color);
      color: var(--text-color);
    }

    .form-control:focus {
      outline: none;
      box-shadow: 0 0 5px var(--accent-color);
    }

    .btn-search {
      background-color: var(--accent-color);
      color: white;
      font-weight: bold;
      transition: background-color 0.3s ease;
    }

    .btn-search:hover {
      background-color: #b00610;
    }

    .video-gallery {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      padding: 2rem;
      justify-content: center;
    }

    .video-card {
      background-color: var(--secondary-color);
      border-radius: 8px;
      width: 180px;
      overflow: hidden;
      cursor: pointer;
      transition: transform 0.3s ease;
    }

    .video-card:hover {
      transform: scale(1.05);
    }

    .video-card img {
      width: 100%;
      height: 120px;
      object-fit: cover;
    }

    .video-card .info {
      padding: 10px;
    }

    .video-card .info h6 {
      margin: 0;
      font-size: 1rem;
      color: var(--text-color);
    }

    .video-card .info p {
      margin: 0.5rem 0 0;
      font-size: 0.875rem;
      color: var(--muted-color);
    }

    .modal-container {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.7);
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .modal-content {
      background-color: var(--secondary-color);
      color: var(--text-color);
      border-radius: 10px;
      padding: 2rem;
      position: relative;
      width: 100%;
      max-width: 400px;
    }

    .close-btn {
      color: var(--muted-color);
      font-size: 1.5rem;
      cursor: pointer;
      position: absolute;
      top: 10px;
      right: 20px;
    }

    .dashboard {
      background-color: var(--secondary-color);
      padding: 2rem;
      margin: 2rem;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    }

    .dashboard h3,
    .dashboard h4 {
      color: var(--accent-color);
    }

    .dashboard .video-list li a {
      color: var(--text-color);
    }

    .dashboard .video-list li a:hover {
      color: var(--accent-color);
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h2>Instagram</h2>
            </div>
            <div class="col-md-6">
                <?php if (isset($_SESSION['username'])) : ?>
                    <span class="username">Welcome, <?php echo $_SESSION['username']; ?></span>
                    <a class="logout" href="logout.php">Logout</a>
                    <?php if ($_SESSION['username'] == 'Admin') : ?>
                        <a class="upload-link" href="secure3.php">Upload Video</a>
                    <?php endif; ?>
                <?php else : ?>
                    <a class="signup" href="#" id="signup-link">Sign Up</a>
                    <a class="logout" href="#" id="signin-link">Sign In</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sign-up form -->
        <div id="signup-form" class="modal-container">
            <div class="modal-content">
                <span class="close-btn">&times;</span>
                <h3>Sign Up</h3>
                <form action="signup-process.php" method="POST">
                    <div class="form-group">
                        <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" id="fname" name="fname" placeholder="First Name" required>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" id="lname" name="lname" placeholder="Last Name" required>
                    </div>
                    <div class="form-group">
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" id="contact" name="contact" placeholder="Contact Number" required>
                    </div>
                    <button type="submit" name="signup" class="btn btn-search">Sign Up</button>
                </form>
            </div>
        </div>

        <!-- Sign-in form -->
        <div id="signin-form" class="modal-container">
            <div class="modal-content">
                <span class="close-btn">&times;</span>
                <h3>Sign In</h3>
                <form action="signin_process.php" method="POST">
                    <div class="form-group">
                        <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    </div>
                    <button type="submit" name="signin" class="btn btn-search">Sign In</button>
                </form>
            </div>
        </div>

        <!-- Search form -->
        <form class="search-form" action="" method="POST">
            <input type="text" name="search" class="form-control" placeholder="Search videos">
            <select name="genre" class="form-control">
                <option value="">Select Genre</option>
                <?php foreach ($genres as $genre) : ?>
                    <option value="<?php echo $genre; ?>"><?php echo $genre; ?></option>
                <?php endforeach; ?>
            </select>
            <select name="age_rating" class="form-control">
                <option value="">Select Age Rating</option>
                <?php foreach ($ageRatings as $rating) : ?>
                    <option value="<?php echo $rating; ?>"><?php echo $rating; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-search">Search</button>
        </form>

        <!-- Display search results -->
        <table class="table">
            <thead>
                <tr>
                    <th>Thumbnail</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Producer</th>
                    <th>Genre</th>
                    <th>Age Rating</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && mysqli_num_rows($result) > 0) {
                    // Display each search result as a table row
                    while ($row = mysqli_fetch_assoc($result)) {
                        $ageRatingClass = strtolower($row['AgeRating']);
                        echo '<tr>';
                        if (isset($_SESSION['username'])) {
                            echo '<td><a href="view_video1.php?filename=' . $row['filename'] . '"><img src="uploads/' . $row['thumbnail'] . '" alt="Thumbnail" class="thumbnail"></a></td>'; // Display thumbnail image with title link
                            echo '<td><a class="video-link" href="view_video1.php?filename=' . $row['filename'] . '">' . $row['title'] . '</a></td>'; // Display title with link
                        } else {
                            $thumbnailSrc = (strpos($row['thumbnail'], 'http') === 0) 
    ? $row['thumbnail'] 
    : 'uploads/' . $row['thumbnail'];

echo '<td><img src="' . $thumbnailSrc . '" alt="Thumbnail" class="thumbnail" style="width: 100px; height: auto;"></td>';
 // Display thumbnail image
                            echo '<td><span class="video-link">' . $row['title'] . '</span> <span style="color:red;">(You are not logged in. Please <a href="#" id="signin-link">Sign In</a> to watch videos)</span></td>'; // Display title with indication to sign in
                        }
                        echo '<td>' . $row['description'] . '</td>';
                        echo '<td>' . $row['Producer'] . '</td>'; // Assuming 'Producer' is a column in the videos table
                        echo '<td>' . $row['Genre'] . '</td>'; // Assuming 'Genre' is a column in the videos table
                        echo '<td class="age-rating ' . $ageRatingClass . '">' . $row['AgeRating'] . '</td>'; // Assuming 'AgeRating' is a column in the videos table
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="6">No videos found</td></tr>';
                }
                ?>
            </tbody>
        </table>

        <?php if (isset($_SESSION['username'])) : ?>
            <!-- Dashboard section for signed-in users -->
            <div class="dashboard">
                <h3>Dashboard</h3>
                <h4>Latest Videos:</h4>
                <ul class="video-list">
                    <?php
                    // Fetch latest videos
                    $latestVideosQuery = "SELECT * FROM videos ORDER BY upload_datetime DESC LIMIT 5";
                    $latestVideosResult = mysqli_query($conn, $latestVideosQuery);
                    if ($latestVideosResult && mysqli_num_rows($latestVideosResult) > 0) {
                        while ($video = mysqli_fetch_assoc($latestVideosResult)) {
                            echo '<li><a href="view_video1.php?filename=' . $video['filename'] . '">' . $video['title'] . '</a></li>';
                        }
                    } else {
                        echo '<li>No videos found</li>';
                    }
                    ?>
                </ul>
                <h4>Latest News:</h4>
                <p>Bernard Hill, the actor whose memorable tones and rugged visage brought to life a variety of fantastic performances, has died. He was 79..</p>
                <p>If you were entertained by Russell Crowe in a cassock (and tootling around on a Lambretta) as Father Gabriele Amorth in last year's The Pope's Exorcist (and its $76.9 million box office off the back of an $18 million budget suggests plenty of you were), you'll no doubt be happy to hear that, according to producer Jeff Katz, Crowe will be battling demonic forces once more in a sequel..</p>
            </div>
        <?php endif; ?>

    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
      document.getElementById("signin-form").style.display = "none";
      document.getElementById("signup-form").style.display = "none";

      document.getElementById("signin-link").addEventListener("click", function (e) {
        e.preventDefault();
        document.getElementById("signup-form").style.display = "none";
        document.getElementById("signin-form").style.display = "flex";
      });

      document.getElementById("signup-link").addEventListener("click", function (e) {
        e.preventDefault();
        document.getElementById("signin-form").style.display = "none";
        document.getElementById("signup-form").style.display = "flex";
      });

      document.querySelectorAll(".close-btn").forEach(function (btn) {
        btn.addEventListener("click", function () {
          document.getElementById("signin-form").style.display = "none";
          document.getElementById("signup-form").style.display = "none";
        });
      });
    });
  </script>

</body>

</html>

<?php
mysqli_close($conn);
?>
