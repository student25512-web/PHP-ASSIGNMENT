<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header('Location: login.php'); exit;
}
require_once 'Book.php';

function getAllBooks() {
    $books = [];
    if (!file_exists('books.txt')) return $books;
    
    $lines = file('books.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $id = 1001;
    foreach ($lines as $line) {
        $d = explode(',', $line);
        if (count($d) >= 4) {
            $book = new Book(
                $id++,
                trim($d[0]),
                trim($d[1]),
                trim($d[2]),
                floatval($d[3])
            );
            $book->special_offer = isset($d[4]) ? (bool)$d[4] : ($book->price < 20);
            $books[] = $book;
        }
    }
    return $books;
}

function saveAllBooks($books) {
    $content = '';
    foreach ($books as $book) {
        $special = $book->special_offer ? '1' : '0';
        $content .= "$book->title,$book->author,$book->genre,$book->price,$special" . PHP_EOL;
    }
    file_put_contents('books.txt', $content);
}

$search = trim($_GET['q'] ?? '');
$allBooks = getAllBooks();

if ($search !== '') {
    $searchLower = strtolower($search);
    $books = array_filter($allBooks, function($book) use ($searchLower) {
        return strpos(strtolower($book->title), $searchLower) !== false ||
               strpos(strtolower($book->author), $searchLower) !== false;
    });
} else {
    $books = $allBooks;
}


$message = '';
if (isset($_GET['msg']) && $_GET['msg'] === 'added') {
    $message = "New book added successfully!";
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $books = getAllBooks(); 

    if (isset($_POST['update_book'])) {
        $index = (int)$_POST['edit_index'];
        if (isset($books[$index])) {
            $books[$index]->title = trim($_POST['title']);
            $books[$index]->author = trim($_POST['author']);
            $books[$index]->genre = trim($_POST['genre']);
            $books[$index]->price = floatval($_POST['price']);
            $books[$index]->special_offer = isset($_POST['special_offer']);
            saveAllBooks($books);
            $message = "Book updated successfully!";
        }
    }
    elseif (isset($_POST['delete_book'])) {
        $index = (int)$_POST['delete_index'];
        if (isset($books[$index])) {
            unset($books[$index]);
            $books = array_values($books);
            saveAllBooks($books);
            $message = "Book deleted successfully!";
        }
    }
}


$editBook = null;
$editIndex = null;
if (isset($_GET['edit'])) {
    $editIndex = (int)$_GET['edit'];
    if (isset($allBooks[$editIndex])) {
        $editBook = $allBooks[$editIndex];
    }
}

$lastUpdate = file_exists('books.txt') ? date("d/m/Y", filemtime('books.txt')) : "Never";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>The Reading Nook - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style-custom.css">
</head>
<body>

<header class="text-white">
    <div class="container d-flex justify-content-between align-items-center">
        <h1 class="logo mb-0">The Reading Nook</h1>
        <nav>
            <a href="admin.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
</header>

<div class=" main-content p-5">

  
    <?php if ($message): ?>
        <div class="position-fixed top-0 end-0 p-4" style="z-index: 9999;">
            <div id="liveToast" class="toast align-items-center text-white border-0 <?= strpos($message, 'deleted') !== false ? 'bg-danger' : 'bg-success' ?>">
                <div class="d-flex">
                    <div class="toast-body fw-semibold fs-5">
                        <?= htmlspecialchars($message) ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="stock-panel">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <h2>Current Stock (<?= count($books) ?> books)</h2>
            
           
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="q" class="search-bar" placeholder="Search by Title or Author..." 
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-light rounded-circle">Search</button>
            </form>
        </div>

        <?php if (empty($books)): ?>
            <div class="text-center py-5 text-muted">
                <h4>No books found</h4>
                <p>Try searching with different keywords.</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Genre</th>
                        <th>Price</th>
                        <th>Offer</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $index => $book): ?>
                    <tr <?= $book->special_offer ? 'style="background:#fffbe6;"' : '' ?>>
                        <td><?= $book->id ?></td>
                        <td><strong><?= htmlspecialchars($book->title) ?></strong></td>
                        <td><?= htmlspecialchars($book->author) ?></td>
                        <td><?= htmlspecialchars($book->genre) ?></td>
                        <td><?= $book->getDisplayPrice() ?></td>
                        <td>
                            <?php if ($book->special_offer): ?>
                                <span class="badge-offer">Special Offer</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="admin.php?edit=<?= $index ?>&q=<?= urlencode($search) ?>" 
                               class="btn btn-warning btn-sm">Edit</a>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this book?')">
                                <input type="hidden" name="delete_index" value="<?= $index ?>">
                                <button type="submit" name="delete_book" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- ADD / EDIT PANEL -->
    <div class="add-panel">
        <?php if ($editBook): ?>
            <h2>Edit Book #<?= $editBook->id ?></h2>
            <form method="POST">
                <input type="hidden" name="edit_index" value="<?= $editIndex ?>">
                <div class="input-group"><input type="text" name="title" value="<?= htmlspecialchars($editBook->title) ?>" required></div>
                <div class="input-group"><input type="text" name="author" value="<?= htmlspecialchars($editBook->author) ?>" required></div>
                <div class="input-group"><input type="text" name="genre" value="<?= htmlspecialchars($editBook->genre) ?>" required></div>
                <div class="input-group"><input type="number" step="0.01" name="price" value="<?= $editBook->price ?>" required></div>
                <div class="input-group text-center py-3">
                    <label class="text-white">
                        <input type="checkbox" name="special_offer" <?= $editBook->special_offer ? 'checked' : '' ?>>
                        <strong> Special Offer</strong>
                    </label>
                </div>
                <button type="submit" name="update_book" class="save-btn">Update Book</button>
                <a href="admin.php<?= $search ? '?q=' . urlencode($search) : '' ?>" 
                   class="btn btn-secondary d-block text-center mt-2">Cancel</a>
            </form>
        <?php else: ?>
            <h2>Add New Book</h2>
            <form action="process_add.php" method="POST">
                <div class="input-group"><input type="text" name="title" placeholder="Title" required></div>
                <div class="input-group"><input type="text" name="author" placeholder="Author" required></div>
                <div class="input-group"><input type="text" name="genre" placeholder="Genre" required></div>
                <div class="input-group"><input type="number" step="0.01" name="price" placeholder="Price" required></div>
                <div class="input-group text-center py-3">
                 &nbsp;   <label class="text-white">
                        <input type="checkbox" name="special_offer">
                    </label>
                    &nbsp;&nbsp; <strong> Special Offer</strong>
                </div>
                <button type="submit" class="save-btn">Save Book</button>
            </form>
        <?php endif; ?>
        <div class="last-update">Last Update: <?= $lastUpdate ?></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toastEl = document.getElementById('liveToast');
        if (toastEl) {
            new bootstrap.Toast(toastEl, { delay: 4000 }).show();
        }
    });
</script>
</body>
</html>