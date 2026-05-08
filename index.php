<?php
session_start();

// Name: Tareq ElRayyes
// ID:120230697
// Instructor: Mohammed Zoqlam
// Project: PHP Book Library

// Initialize Books Data in Session
if (!isset($_SESSION["books"])) {
    $_SESSION["books"] = [
        [
            "id"        => 1,
            "title"     => "Clean Code",
            "author"    => "Robert Martin",
            "genre"     => "Technology",
            "year"      => 2008,
            "pages"     => 431,
            "image_url" => ""
        ],
        [
            "id"        => 2,
            "title"     => "A Brief History of Time",
            "author"    => "Stephen Hawking",
            "genre"     => "Science",
            "year"      => 1988,
            "pages"     => 212,
            "image_url" => ""
        ],
        [
            "id"        => 3,
            "title"     => "Sapiens",
            "author"    => "Yuval Harari",
            "genre"     => "History",
            "year"      => 2011,
            "pages"     => 443,
            "image_url" => ""
        ],
    ];
}

// Reference session books array
$books = &$_SESSION["books"];

// Allowed genres
$genres = [
    "Fiction",
    "Non-Fiction",
    "Science",
    "History",
    "Biography",
    "Technology"
];


// Sanitize input
function sanitize($value): string {
    return trim($value ?? "");
}

// Return old form value
function old(string $field, array $data): string {
    return htmlspecialchars($data[$field] ?? "", ENT_QUOTES, "UTF-8");
}

// Add Bootstrap invalid class
function invalidClass(string $field, array $errors): string {
    return isset($errors[$field]) ? " is-invalid" : "";
}

// Generate sortable table header links
function sortLink(string $column, string $current, string $dir, string $search): string {

    $newDir = ($column === $current && $dir === "asc") ? "desc" : "asc";

    $arrow = "";

    if ($column === $current) {
        $arrow = $dir === "asc" ? " ▲" : " ▼";
    }

    $query = http_build_query([
        "sort"   => $column,
        "dir"    => $newDir,
        "search" => $search
    ]);

    return '<a href="?' . $query . '" class="text-white text-decoration-none">'
        . htmlspecialchars(ucfirst($column), ENT_QUOTES, "UTF-8")
        . $arrow .
        '</a>';
}

// Search & Sort Logic

$searchTerm = sanitize($_GET["search"] ?? "");
$sortColumn = $_GET["sort"] ?? "";
$sortDir    = $_GET["dir"] ?? "asc";

$displayBooks = $books;

// Search filter
if ($searchTerm !== "") {

    $displayBooks = [];

    foreach ($books as $book) {

        if (
            stripos($book["title"], $searchTerm) !== false ||
            stripos($book["author"], $searchTerm) !== false
        ) {
            $displayBooks[] = $book;
        }
    }
}

// Sort books
$allowedSortColumns = ["id", "title", "author", "genre", "year", "pages"];

if (
    $sortColumn !== "" &&
    in_array($sortColumn, $allowedSortColumns)
) {

    usort($displayBooks, function ($a, $b) use ($sortColumn, $sortDir) {

        $valueA = $a[$sortColumn];
        $valueB = $b[$sortColumn];

        if (is_numeric($valueA)) {
            $compare = $valueA - $valueB;
        } else {
            $compare = strcasecmp($valueA, $valueB);
        }

        return $sortDir === "desc" ? -$compare : $compare;
    });
}

// Form Processing

$errors        = [];
$submittedData = [];

$editMode = false;
$editId   = null;

// Delete Book

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["delete_id"])
) {

    $deleteId = (int)$_POST["delete_id"];

    $books = array_values(array_filter($books, function ($book) use ($deleteId) {

        return $book["id"] !== $deleteId;
    }));

    $_SESSION["success"] = "Book deleted successfully.";

    header("Location: index.php");
    exit;
}

// Edit Mode

if (isset($_GET["edit_id"])) {

    $editMode = true;
    $editId   = (int)$_GET["edit_id"];

    foreach ($books as $book) {

        if ($book["id"] === $editId) {

            $submittedData = $book;
            break;
        }
    }
}

// Add / Update Book

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["save_book"])
) {

    $submittedData["title"]     = sanitize($_POST["title"] ?? "");
    $submittedData["author"]    = sanitize($_POST["author"] ?? "");
    $submittedData["genre"]     = sanitize($_POST["genre"] ?? "");
    $submittedData["year"]      = sanitize($_POST["year"] ?? "");
    $submittedData["pages"]     = sanitize($_POST["pages"] ?? "");
    $submittedData["image_url"] = sanitize($_POST["image_url"] ?? "");

    $editId   = isset($_POST["edit_id"]) ? (int)$_POST["edit_id"] : null;
    $editMode = ($editId !== null);


    // Title validation
    if ($submittedData["title"] === "") {

        $errors["title"] = "Title is required.";

    } elseif (
        mb_strlen($submittedData["title"]) < 3 ||
        mb_strlen($submittedData["title"]) > 120
    ) {

        $errors["title"] = "Title must be between 3 and 120 characters.";
    }

    // Author validation
    if ($submittedData["author"] === "") {

        $errors["author"] = "Author name is required.";

    } else {

        $authorParts = array_filter(explode(" ", $submittedData["author"]));

        if (count($authorParts) < 2) {

            $errors["author"] = "Author name must contain at least two words.";
        }
    }

    // Genre validation
    if ($submittedData["genre"] === "") {

        $errors["genre"] = "Genre is required.";

    } elseif (!in_array($submittedData["genre"], $genres)) {

        $errors["genre"] = "Selected genre is invalid.";
    }

    // Year validation
    $currentYear = (int)date("Y");

    if ($submittedData["year"] === "") {

        $errors["year"] = "Year is required.";

    } elseif (!ctype_digit($submittedData["year"])) {

        $errors["year"] = "Year must be a valid integer.";

    } elseif (
        (int)$submittedData["year"] < 1000 ||
        (int)$submittedData["year"] > $currentYear
    ) {

        $errors["year"] = "Year must be between 1000 and {$currentYear}.";
    }

    // Pages validation
    if ($submittedData["pages"] === "") {

        $errors["pages"] = "Pages field is required.";

    } elseif (
        !ctype_digit($submittedData["pages"]) ||
        (int)$submittedData["pages"] <= 0
    ) {

        $errors["pages"] = "Pages must be a positive integer.";
    }

    // Image URL validation
    if ($submittedData["image_url"] !== "") {

        if (
            !preg_match('/\.(jpg|jpeg|png|gif)(\?.*)?$/i', $submittedData["image_url"])
        ) {

            $errors["image_url"] =
                "Image URL must end with .jpg, .jpeg, .png, or .gif";
        }
    }

    // Save Book

    if (empty($errors)) {

        // Update existing book
        if ($editMode && $editId !== null) {

            foreach ($books as &$book) {

                if ($book["id"] === $editId) {

                    $book["title"]     = $submittedData["title"];
                    $book["author"]    = $submittedData["author"];
                    $book["genre"]     = $submittedData["genre"];
                    $book["year"]      = (int)$submittedData["year"];
                    $book["pages"]     = (int)$submittedData["pages"];
                    $book["image_url"] = $submittedData["image_url"];

                    break;
                }
            }

            unset($book);

            $_SESSION["success"] = "Book updated successfully.";

        } else {

            // Generate new ID
            $maxId = 0;

            foreach ($books as $book) {

                if ($book["id"] > $maxId) {
                    $maxId = $book["id"];
                }
            }

            $newId = $maxId + 1;

            // Add new book
            $books[] = [
                "id"        => $newId,
                "title"     => $submittedData["title"],
                "author"    => $submittedData["author"],
                "genre"     => $submittedData["genre"],
                "year"      => (int)$submittedData["year"],
                "pages"     => (int)$submittedData["pages"],
                "image_url" => $submittedData["image_url"]
            ];

            $_SESSION["success"] = "Book added successfully.";
        }

        $submittedData = [];

        header("Location: index.php");
        exit;
    }
}

// Success Message

$successMsg = "";

if (isset($_SESSION["success"])) {

    $successMsg = $_SESSION["success"];

    unset($_SESSION["success"]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>PHP Book Library</title>

    <!-- Bootstrap 5 CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <style>

        body {
            background-color: #f8f9fa;
        }

        .book-thumb {
            width: 40px;
            height: 55px;
            object-fit: cover;
            border-radius: 4px;
        }

        th a {
            font-weight: bold;
        }

    </style>

</head>

<body>

<div class="container py-4">

    <!-- Page Title -->
    <div class="text-center mb-4">

        <h1 class="display-5 fw-bold text-primary">
            PHP Book Library
        </h1>

        <p class="text-muted">
            Manage your personal book collection
        </p>

    </div>

    <!-- Success Alert -->
    <?php if ($successMsg !== ""): ?>

        <div class="alert alert-success alert-dismissible fade show">

            <?= htmlspecialchars($successMsg, ENT_QUOTES, "UTF-8") ?>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>

        </div>

    <?php endif; ?>

    <div class="row g-4">

        <!-- Left Column -->
        <div class="col-md-4">

            <div class="card shadow-sm">

                <div class="card-header bg-primary text-white">

                    <h5 class="mb-0">
                        <?= $editMode ? "Edit Book" : "Add New Book" ?>
                    </h5>

                </div>

                <div class="card-body">

                    <!-- General Error Alert -->
                    <?php if (!empty($errors)): ?>

                        <div class="alert alert-danger alert-dismissible fade show">

                            Please fix the form errors.

                            <button type="button"
                                    class="btn-close"
                                    data-bs-dismiss="alert"></button>

                        </div>

                    <?php endif; ?>

                    <!-- Book Form -->
                    <form method="POST">

                        <input type="hidden"
                               name="save_book"
                               value="1">

                        <?php if ($editMode): ?>

                            <input type="hidden"
                                   name="edit_id"
                                   value="<?= (int)$editId ?>">

                        <?php endif; ?>

                        <!-- Title -->
                        <div class="mb-3">

                            <label class="form-label">
                                Title
                            </label>

                            <input type="text"
                                   name="title"
                                   class="form-control<?= invalidClass("title", $errors) ?>"
                                   value="<?= old("title", $submittedData) ?>">

                            <?php if (isset($errors["title"])): ?>

                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors["title"]) ?>
                                </div>

                            <?php endif; ?>

                        </div>

                        <!-- Author -->
                        <div class="mb-3">

                            <label class="form-label">
                                Author
                            </label>

                            <input type="text"
                                   name="author"
                                   class="form-control<?= invalidClass("author", $errors) ?>"
                                   value="<?= old("author", $submittedData) ?>">

                            <?php if (isset($errors["author"])): ?>

                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors["author"]) ?>
                                </div>

                            <?php endif; ?>

                        </div>

                        <!-- Genre -->
                        <div class="mb-3">

                            <label class="form-label">
                                Genre
                            </label>

                            <select name="genre"
                                    class="form-select<?= invalidClass("genre", $errors) ?>">

                                <option value="">
                                    -- Select Genre --
                                </option>

                                <?php foreach ($genres as $genre): ?>

                                    <option value="<?= htmlspecialchars($genre) ?>"
                                        <?= old("genre", $submittedData) === $genre ? "selected" : "" ?>>

                                        <?= htmlspecialchars($genre) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                            <?php if (isset($errors["genre"])): ?>

                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors["genre"]) ?>
                                </div>

                            <?php endif; ?>

                        </div>

                        <!-- Year -->
                        <div class="mb-3">

                            <label class="form-label">
                                Year
                            </label>

                            <input type="number"
                                   name="year"
                                   class="form-control<?= invalidClass("year", $errors) ?>"
                                   value="<?= old("year", $submittedData) ?>">

                            <?php if (isset($errors["year"])): ?>

                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors["year"]) ?>
                                </div>

                            <?php endif; ?>

                        </div>

                        <!-- Pages -->
                        <div class="mb-3">

                            <label class="form-label">
                                Pages
                            </label>

                            <input type="number"
                                   name="pages"
                                   class="form-control<?= invalidClass("pages", $errors) ?>"
                                   value="<?= old("pages", $submittedData) ?>">

                            <?php if (isset($errors["pages"])): ?>

                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors["pages"]) ?>
                                </div>

                            <?php endif; ?>

                        </div>

                        <!-- Image URL -->
                        <div class="mb-3">

                            <label class="form-label">
                                Cover Image URL
                            </label>

                            <input type="text"
                                   name="image_url"
                                   class="form-control<?= invalidClass("image_url", $errors) ?>"
                                   value="<?= old("image_url", $submittedData) ?>">

                            <?php if (isset($errors["image_url"])): ?>

                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors["image_url"]) ?>
                                </div>

                            <?php endif; ?>

                        </div>

                        <div class="d-grid gap-2">

                            <button type="submit"
                                    class="btn btn-primary">

                                <?= $editMode ? "Update Book" : "Add Book" ?>

                            </button>

                            <?php if ($editMode): ?>

                                <a href="index.php"
                                   class="btn btn-outline-secondary">

                                    Cancel Edit

                                </a>

                            <?php endif; ?>

                        </div>

                    </form>

                </div>

            </div>

        </div>

        <div class="col-md-8">

            <!-- Search Form -->
            <form method="GET" class="mb-3">

                <div class="input-group">

                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Search by title or author..."
                           value="<?= htmlspecialchars($searchTerm) ?>">

                    <?php if ($sortColumn): ?>

                        <input type="hidden"
                               name="sort"
                               value="<?= htmlspecialchars($sortColumn) ?>">

                        <input type="hidden"
                               name="dir"
                               value="<?= htmlspecialchars($sortDir) ?>">

                    <?php endif; ?>

                    <button class="btn btn-outline-primary">
                        Search
                    </button>

                </div>

            </form>

            <div class="card shadow-sm">

                <div class="card-header bg-dark text-white d-flex justify-content-between">

                    <h5 class="mb-0">
                        Books List
                    </h5>

                    <span class="badge bg-primary">
                        <?= count($displayBooks) ?> Books
                    </span>

                </div>

                <div class="table-responsive">

                    <table class="table table-striped table-hover table-bordered mb-0">

                        <thead class="table-dark">

                        <tr>

                            <th><?= sortLink("id", $sortColumn, $sortDir, $searchTerm) ?></th>

                            <th><?= sortLink("title", $sortColumn, $sortDir, $searchTerm) ?></th>

                            <th><?= sortLink("author", $sortColumn, $sortDir, $searchTerm) ?></th>

                            <th><?= sortLink("genre", $sortColumn, $sortDir, $searchTerm) ?></th>

                            <th><?= sortLink("year", $sortColumn, $sortDir, $searchTerm) ?></th>

                            <th><?= sortLink("pages", $sortColumn, $sortDir, $searchTerm) ?></th>

                            <th>Cover</th>

                            <th>Actions</th>

                        </tr>

                        </thead>

                        <tbody>

                        <?php foreach ($displayBooks as $book): ?>

                            <tr>

                                <td><?= (int)$book["id"] ?></td>

                                <td><?= htmlspecialchars($book["title"]) ?></td>

                                <td><?= htmlspecialchars($book["author"]) ?></td>

                                <td><?= htmlspecialchars($book["genre"]) ?></td>

                                <td><?= (int)$book["year"] ?></td>

                                <td><?= (int)$book["pages"] ?></td>

                                <td>

                                    <?php if ($book["image_url"] !== ""): ?>

                                        <img src="<?= htmlspecialchars($book["image_url"]) ?>"
                                             class="book-thumb"
                                             alt="Book Cover">

                                    <?php else: ?>

                                        —

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <a href="?edit_id=<?= (int)$book["id"] ?>"
                                       class="btn btn-warning btn-sm">

                                        Edit

                                    </a>

                                    <button type="button"
                                            class="btn btn-danger btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal"
                                            data-book-id="<?= (int)$book["id"] ?>"
                                            data-book-title="<?= htmlspecialchars($book["title"]) ?>">

                                        Delete

                                    </button>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- Delete Modal -->
<div class="modal fade"
     id="deleteModal"
     tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header bg-danger text-white">

                <h5 class="modal-title">
                    Confirm Delete
                </h5>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body">

                Are you sure you want to delete:

                <strong id="modalBookTitle"></strong> ?

            </div>

            <div class="modal-footer">

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                    Cancel

                </button>

                <form method="POST">

                    <input type="hidden"
                           name="delete_id"
                           id="deleteBookId">

                    <button type="submit"
                            class="btn btn-danger">

                        Delete

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

const deleteModal = document.getElementById('deleteModal');

deleteModal.addEventListener('show.bs.modal', function(event) {

    const button = event.relatedTarget;

    const bookId = button.getAttribute('data-book-id');

    const bookTitle = button.getAttribute('data-book-title');

    document.getElementById('deleteBookId').value = bookId;

    document.getElementById('modalBookTitle').textContent = bookTitle;

});

</script>

</body>
</html>