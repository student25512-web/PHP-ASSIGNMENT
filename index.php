<?php
require_once 'Book.php';

function getAllBooks() {
    $books = [];
    if (!file_exists('books.txt')) return $books;
    
    $lines = file('books.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $id = 1001;
    foreach ($lines as $line) {
        $data = explode(',', $line);
        if (count($data) >= 4) {
            $book = new Book(
                $id++,
                trim($data[0]),
                trim($data[1]),
                trim($data[2]),
                floatval($data[3])
            );
        
            if (isset($data[4])) {
                $book->special_offer = (bool)$data[4];
            }
            $books[] = $book;
        }
    }
    return $books;
}

$allBooks = getAllBooks();
$search = $_GET['q'] ?? '';

if ($search !== '') {
    $searchLower = strtolower($search);
    $books = array_filter($allBooks, function($book) use ($searchLower) {
        return strpos(strtolower($book->title), $searchLower) !== false ||
               strpos(strtolower($book->author), $searchLower) !== false;
    });
} else {
    $books = $allBooks;
}

$lastUpdate = file_exists('books.txt') ? date("d/m/Y", filemtime('books.txt')) : "Today";
$totalBooks = count($books);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>The Reading Nook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style-custom.css">
</head>
<body>

<header class="text-white">
    <div class="container d-flex justify-content-between align-items-center">
        <h1 class="logo mb-0">The Reading Nook</h1>
        <nav>
            <a href="login.php"><i class="bi bi-box-arrow-right"></i> Login</a>
        </nav>
    </div>
</header>

<div class="container main-content">
    <div class="stock-panel w-100">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <h2>Our Current Collection</h2>
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="q" class="search-bar" placeholder="Search by Title or Author..." 
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-light rounded-circle p-3">
                    Search
                </button>
            </form>
        </div>

        <?php if (empty($books)): ?>
            <div class="text-center py-5">
                <p class="text-muted fs-3">No books found. Try another search!</p>
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
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book): ?>
                    <tr>
                        <td><?= $book->id ?></td>
                        <td><strong><?= htmlspecialchars($book->title) ?></strong></td>
                        <td><?= htmlspecialchars($book->author) ?></td>
                        <td><?= htmlspecialchars($book->genre) ?></td>
                        <td>
                            <?= $book->getDisplayPrice() ?>
                        </td>
                          <td>
                        <?php if ($book->special_offer): ?>
                            <span class="badge-offer">Special Offer</span>
                        <?php endif; ?>
                    </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="text-center mt-4">
            <p class="text-muted small">
                Last updated: <?= $lastUpdate ?> • <?= $totalBooks ?> book<?= $totalBooks !== 1 ? 's' : '' ?> in stock
            </p>
        </div>
    </div>
</div>

</body>
</html>