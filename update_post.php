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

// Check if post ID is provided
if (!isset($_GET["id"])) {
    header("Location: dashboard.php");
    exit();
}

$post_id = $_GET["id"];

// Fetch the post
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    // Either post not found or doesn't belong to user
    header("Location: dashboard.php");
    exit();
}

$post = $result->fetch_assoc();

// Handle update submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);

    if (empty($title)) {
        $errors[] = "Title cannot be empty.";
    }

    if (empty($content)) {
        $errors[] = "Content cannot be empty.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssii", $title, $content, $post_id, $user_id);

        if ($stmt->execute()) {
            $success = "Post updated successfully.";
            header("Location: dashboard.php");
            exit();
        } else {
            $errors[] = "Failed to update post. Try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Post</title>
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

<h2>Edit Your Post</h2>

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

<form method="POST">
    <label>Title:</label><br>
    <input type="text" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required><br><br>

    <label>Content:</label><br>
    <textarea name="content" rows="5" required><?php echo htmlspecialchars($post['content']); ?></textarea><br><br>

    <button type="submit">Update Post</button>
</form>

</body>
</html>
