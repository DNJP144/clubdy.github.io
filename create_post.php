<?php
session_start();
include("db.php");

// Redirect if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$username = $_SESSION["username"];

$errors = [];
$success = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);

    if (empty($title)) {
        $errors[] = "Title cannot be empty.";
    }

    if (empty($content)) {
        $errors[] = "Post content cannot be empty.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $title, $content);

        if ($stmt->execute()) {
            $success = "Post created successfully.";
        } else {
            $errors[] = "Failed to create post. Try again.";
        }

        $stmt->close();
    }
}

// Fetch posts by logged-in user
$stmt = $conn->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$posts = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Post</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="header-top">
    <img src="logo.jpg" alt="Logo" class="logo">
    <p class="welcome-message">Welcome, <?php echo htmlspecialchars($username); ?>!</p>
</header>

<nav>
    <div class="nav-left">
        <a href="dashboard.php">Dashboard</a>
        <a href="create_post.php">Create Post</a>
    </div>
    <div class="nav-right">
        <a href="logout.php">Logout</a>
    </div>
</nav>

<h2>Create a New Post</h2>

<?php
if (!empty($errors)) {
    echo "<div class='error'>";
    foreach ($errors as $error) {
        echo "<p>$error</p>";
    }
    echo "</div>";
}

if ($success) {
    echo "<div class='success'><p>$success</p></div>";
}
?>

<form method="POST" action="">
    <label>Title:</label><br>
    <input type="text" name="title" required><br><br>

    <label>Content:</label><br>
    <textarea name="content" rows="5" required></textarea><br><br>

    <button type="submit">Post</button>
</form>

<h3>Your Posts</h3>
<?php if ($posts->num_rows > 0): ?>
    <?php while ($row = $posts->fetch_assoc()): ?>
        <div class="post">
            <h4><?php echo htmlspecialchars($row["title"]); ?></h4>
            <p><?php echo nl2br(htmlspecialchars($row["content"])); ?></p>
            <small>Posted on <?php echo $row["created_at"]; ?></small><br>
            <a href="update_post.php?id=<?php echo $row["id"]; ?>">Update</a> |
            <a href="delete_post.php?id=<?php echo $row["id"]; ?>" onclick="return confirm('Are you sure?');">Delete</a>
        </div>
        <hr>
    <?php endwhile; ?>
<?php else: ?>
    <p>You have not made any posts yet.</p>
<?php endif; ?>

</body>
</html>
