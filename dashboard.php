<?php
session_start();
include("db.php");

// Redirect if not logged inom
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$username = $_SESSION["username"];

// Fetch all posts
$sql = "SELECT posts.*, users.username 
        FROM posts 
        JOIN users ON posts.user_id = users.id 
        ORDER BY posts.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
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

    <h2>All Posts</h2>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="post">
                <h3><?php echo htmlspecialchars($row["title"]); ?></h3>
                <p><?php echo nl2br(htmlspecialchars($row["content"])); ?></p>
                <small>Posted by <?php echo htmlspecialchars($row["username"]); ?> on <?php echo $row["created_at"]; ?></small><br>

                <?php if ($row["user_id"] == $user_id): ?>
                    <a href="update_post.php?id=<?php echo $row["id"]; ?>">Update</a> |
                    <a href="delete_post.php?id=<?php echo $row["id"]; ?>" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                <?php endif; ?>
            </div>
            <hr>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No posts found.</p>
    <?php endif; ?>

</body>
</html>
