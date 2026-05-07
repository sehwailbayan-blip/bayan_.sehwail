<?php
session_start();
$books = [
    [
        "id"     => 1,
        "title"  => "Clean Code",
        "author" => "Robert Martin",
        "genre"  => "Technology",
        "year"   => 2008,
        "pages"  => 431
    ],
    [
        "id"     => 2,
        "title"  => "Sapiens",
        "author" => "Yuval Harari",
        "genre"  => "History",
        "year"   => 2011,
        "pages"  => 443
    ],
    [
        "id"     => 3,
        "title"  => "A Brief History of Time",
        "author" => "Stephen Hawking",
        "genre"  => "Science",
        "year"   => 1988,
        "pages"  => 212
    ],
];
$genres = ["Fiction", "Non-Fiction", "Science", "History", "Biography", "Technology"];
$errors        = [];
$submittedData = [];
$editMode      = false;
$editId        = null;
$successMsg    = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {
    $deleteId = (int)$_POST["delete_id"];
    $books = array_values(array_filter($books, fn($b) => $b["id"] !== $deleteId));
    $_SESSION["success"] = "Book deleted successfully.";
    header("Location: " . $_SERVER["PHP_SELF"]);
    exit;
}
if (isset($_GET["edit_id"]) && !isset($_POST["submit_book"])) {
    $editId   = (int)$_GET["edit_id"];
    $editMode = true;
    foreach ($books as $b) {
        if ($b["id"] === $editId) {
            $submittedData = $b;
            break;
        }
    }
}
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit_book"])) {

    // Sanitize inputs
    $submittedData["title"]  = htmlspecialchars(trim($_POST["title"]  ?? ""));
    $submittedData["author"] = htmlspecialchars(trim($_POST["author"] ?? ""));
    $submittedData["genre"]  = htmlspecialchars(trim($_POST["genre"]  ?? ""));
    $submittedData["year"]   = htmlspecialchars(trim($_POST["year"]   ?? ""));
    $submittedData["pages"]  = htmlspecialchars(trim($_POST["pages"]  ?? ""));

    $isUpdate = isset($_POST["edit_id"]) && $_POST["edit_id"] !== "";
    if ($isUpdate) {
        $submittedData["id"] = (int)$_POST["edit_id"];
    }

    // Validate title
    if ($submittedData["title"] === "") {
        $errors["title"] = "Title is required.";
    } elseif (strlen($submittedData["title"]) < 3 || strlen($submittedData["title"]) > 120) {
        $errors["title"] = "Title must be between 3 and 120 characters.";
    }

    // Validate author
    if ($submittedData["author"] === "") {
        $errors["author"] = "Author is required.";
    } elseif (str_word_count($submittedData["author"]) < 2) {
        $errors["author"] = "Author must include first and last name.";
    }

    // Validate genre
    if ($submittedData["genre"] === "") {
        $errors["genre"] = "Genre is required.";
    } elseif (!in_array($submittedData["genre"], $genres)) {
        $errors["genre"] = "Selected genre is not valid.";
    }

    // Validate year
    $currentYear = (int)date("Y");
    if ($submittedData["year"] === "") {
        $errors["year"] = "Year is required.";
    } elseif (!ctype_digit($submittedData["year"]) ||
              (int)$submittedData["year"] < 1000 ||
              (int)$submittedData["year"] > $currentYear) {
        $errors["year"] = "Year must be between 1000 and {$currentYear}.";
    }

    // Validate pages
    if ($submittedData["pages"] === "") {
        $errors["pages"] = "Pages is required.";
    } elseif (!ctype_digit($submittedData["pages"]) || (int)$submittedData["pages"] < 1) {
        $errors["pages"] = "Pages must be a positive integer.";
    }
    if (empty($errors)) {

        if ($isUpdate) {
            foreach ($books as &$b) {
                if ($b["id"] === $submittedData["id"]) {
                    $b["title"]  = $submittedData["title"];
                    $b["author"] = $submittedData["author"];
                    $b["genre"]  = $submittedData["genre"];
                    $b["year"]   = (int)$submittedData["year"];
                    $b["pages"]  = (int)$submittedData["pages"];
                    break;
                }
            }
            unset($b);
            $_SESSION["success"] = "Book updated successfully!";
        } else {
            $maxId = 0;
            foreach ($books as $b) {
                if ($b["id"] > $maxId) {
                    $maxId = $b["id"];
                }
            }
            $newId = $maxId + 1;

            $books[] = [
                "id"     => $newId,
                "title"  => $submittedData["title"],
                "author" => $submittedData["author"],
                "genre"  => $submittedData["genre"],
                "year"   => (int)$submittedData["year"],
                "pages"  => (int)$submittedData["pages"],
            ];
            $_SESSION["success"] = "Book added successfully!";
        }

        $submittedData = [];
        header("Location: " . $_SERVER["PHP_SELF"]);
        exit;
    }
}
// Search
$searchTerm   = htmlspecialchars(trim($_GET["search"] ?? ""));
$displayBooks = $books;

if ($searchTerm !== "") {
    $displayBooks = array_filter($books, function ($b) use ($searchTerm) {
        return stripos($b["title"],  $searchTerm) !== false ||
               stripos($b["author"], $searchTerm) !== false;
    });
}

// Sort
$sortCol = $_GET["sort"] ?? "id";
$sortDir = $_GET["dir"]  ?? "asc";
$allowed = ["id", "title", "author", "genre", "year", "pages"];
if (!in_array($sortCol, $allowed)) {
    $sortCol = "id";
}

usort($displayBooks, function ($a, $b) use ($sortCol, $sortDir) {
    $cmp = is_string($a[$sortCol])
        ? strcasecmp($a[$sortCol], $b[$sortCol])
        : $a[$sortCol] <=> $b[$sortCol];
    return $sortDir === "desc" ? -$cmp : $cmp;
});

// Helper functions for sort links
function sortUrl(string $col): string {
    $currentCol = $_GET["sort"] ?? "id";
    $currentDir = $_GET["dir"]  ?? "asc";
    $newDir     = ($currentCol === $col && $currentDir === "asc") ? "desc" : "asc";
    $search     = isset($_GET["search"]) ? "&search=" . urlencode($_GET["search"]) : "";
    return "?sort={$col}&dir={$newDir}{$search}";
}

function sortIcon(string $col): string {
    $currentCol = $_GET["sort"] ?? "id";
    $currentDir = $_GET["dir"]  ?? "asc";
    if ($currentCol !== $col) return " ↕";
    return $currentDir === "asc" ? " ↑" : " ↓";
}

// Read session success message
if (!empty($_SESSION["success"])) {
    $successMsg = $_SESSION["success"];
    unset($_SESSION["success"]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Book Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body         { background-color: #f0f4f8; }
        .sort-link   { color: inherit; text-decoration: none; font-weight: 600; }
        .sort-link:hover { text-decoration: underline; }
        .section-title {
            font-weight: 700;
            border-left: 4px solid #0d6efd;
            padding-left: .6rem;
            margin-bottom: 1.2rem;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-primary mb-4 shadow-sm">
    <div class="container">
        <span class="navbar-brand fw-bold fs-4">&#128218; My Book Library</span>
        <span class="text-white-50 small">Assignment 2 - PHP Arrays & Forms</span>
    </div>
</nav>

<div class="container">
    <?php if ($successMsg !== ""): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        ✅ <?= htmlspecialchars($successMsg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- LEFT: Form (4 columns) -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body">

                    <h5 class="section-title">
                        <?= $editMode ? "✏️ Edit Book" : "➕ Add New Book" ?>
                    </h5>

                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        ⚠️ Please fix the errors below.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">

                        <?php if ($editMode): ?>
                        <input type="hidden" name="edit_id" value="<?= (int)($submittedData["id"] ?? 0) ?>">
                        <?php endif; ?>

                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                            <input type="text" id="title" name="title"
                                class="form-control <?= isset($errors["title"]) ? "is-invalid" : "" ?>"
                                value="<?= htmlspecialchars($submittedData["title"] ?? "") ?>"
                                placeholder="e.g. The Great Gatsby">
                            <?php if (isset($errors["title"])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors["title"]) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Author -->
                        <div class="mb-3">
                            <label for="author" class="form-label fw-semibold">Author <span class="text-danger">*</span></label>
                            <input type="text" id="author" name="author"
                                class="form-control <?= isset($errors["author"]) ? "is-invalid" : "" ?>"
                                value="<?= htmlspecialchars($submittedData["author"] ?? "") ?>"
                                placeholder="e.g. Robert Martin">
                            <?php if (isset($errors["author"])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors["author"]) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Genre -->
                        <div class="mb-3">
                            <label for="genre" class="form-label fw-semibold">Genre <span class="text-danger">*</span></label>
                            <select id="genre" name="genre"
                                class="form-control <?= isset($errors["genre"]) ? "is-invalid" : "" ?>">
                                <option value="">-- Select Genre --</option>
                                <?php foreach ($genres as $g): ?>
                                <option value="<?= htmlspecialchars($g) ?>"
                                    <?= (($submittedData["genre"] ?? "") === $g) ? "selected" : "" ?>>
                                    <?= htmlspecialchars($g) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors["genre"])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors["genre"]) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Year -->
                        <div class="mb-3">
                            <label for="year" class="form-label fw-semibold">Year <span class="text-danger">*</span></label>
                            <input type="number" id="year" name="year"
                                class="form-control <?= isset($errors["year"]) ? "is-invalid" : "" ?>"
                                value="<?= htmlspecialchars($submittedData["year"] ?? "") ?>"
                                placeholder="e.g. 2023" min="1000" max="<?= (int)date("Y") ?>">
                            <?php if (isset($errors["year"])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors["year"]) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Pages -->
                        <div class="mb-3">
                            <label for="pages" class="form-label fw-semibold">Pages <span class="text-danger">*</span></label>
                            <input type="number" id="pages" name="pages"
                                class="form-control <?= isset($errors["pages"]) ? "is-invalid" : "" ?>"
                                value="<?= htmlspecialchars($submittedData["pages"] ?? "") ?>"
                                placeholder="e.g. 320" min="1">
                            <?php if (isset($errors["pages"])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors["pages"]) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Submit -->
                        <div class="d-grid">
                            <button type="submit" name="submit_book" class="btn btn-primary">
                                <?= $editMode ? "💾 Update Book" : "➕ Add Book" ?>
                            </button>
                            <?php if ($editMode): ?>
                            <a href="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" class="btn btn-outline-secondary mt-2">Cancel</a>
                            <?php endif; ?>
                        </div>

                    </form>
                </div>
            </div>
        </div><!-- /col-lg-4 -->
        <!-- RIGHT: Table (8 columns) -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">

                    <h5 class="section-title">
                        📖 Book Collection
                        <span class="badge bg-primary ms-1"><?= count($books) ?></span>
                    </h5>

                    <!-- Search bar -->
                    <form method="GET" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" class="mb-3">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control"
                                placeholder="Search by title or author..."
                                value="<?= htmlspecialchars($searchTerm) ?>">
                            <button class="btn btn-outline-primary" type="submit">🔍 Search</button>
                            <?php if ($searchTerm !== ""): ?>
                            <a href="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" class="btn btn-outline-secondary">✕ Clear</a>
                            <?php endif; ?>
                        </div>
                    </form>

                    <?php if ($searchTerm !== "" && empty($displayBooks)): ?>
                    <div class="alert alert-warning">
                        No books found matching "<?= htmlspecialchars($searchTerm) ?>".
                    </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered align-middle mb-0">
                            <thead class="table-primary">
                                <tr>
                                    <th><a href="<?= sortUrl("id") ?>"     class="sort-link"># <?=     sortIcon("id")     ?></a></th>
                                    <th><a href="<?= sortUrl("title") ?>"  class="sort-link">Title <?=  sortIcon("title")  ?></a></th>
                                    <th><a href="<?= sortUrl("author") ?>" class="sort-link">Author <?= sortIcon("author") ?></a></th>
                                    <th><a href="<?= sortUrl("genre") ?>"  class="sort-link">Genre <?=  sortIcon("genre")  ?></a></th>
                                    <th><a href="<?= sortUrl("year") ?>"   class="sort-link">Year <?=   sortIcon("year")   ?></a></th>
                                    <th><a href="<?= sortUrl("pages") ?>"  class="sort-link">Pages <?=  sortIcon("pages")  ?></a></th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($displayBooks)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No books yet. Add one using the form!
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($displayBooks as $book): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string)$book["id"]) ?></td>
                                    <td><?= htmlspecialchars($book["title"]) ?></td>
                                    <td><?= htmlspecialchars($book["author"]) ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($book["genre"]) ?></span></td>
                                    <td><?= htmlspecialchars((string)$book["year"]) ?></td>
                                    <td><?= htmlspecialchars((string)$book["pages"]) ?></td>
                                    <td>
                                        <a href="?edit_id=<?= htmlspecialchars((string)$book["id"]) ?>"
                                           class="btn btn-sm btn-warning me-1">✏️ Edit</a>
                                        <button type="button"
                                                class="btn btn-sm btn-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteModal"
                                                data-book-id="<?= htmlspecialchars((string)$book["id"]) ?>"
                                                data-book-title="<?= htmlspecialchars($book["title"]) ?>">
                                            🗑️ Delete
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div><!-- /col-lg-8 -->

    </div><!-- /row -->
</div><!-- /container -->
<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">🗑️ Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <strong id="modalBookTitle"></strong>?<br>
                <span class="text-muted small">This action cannot be undone.</span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
                    <input type="hidden" name="delete_id" id="modalDeleteId" value="">
                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Footer -->
<footer class="text-center text-muted small py-4 mt-4 border-top">
    Assignment 2 – PHP Arrays & Forms | Islamic University of Gaza | <?= (int)date("Y") ?>
</footer>

<!-- Bootstrap 5 JS via CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Fill modal with correct book ID and title when delete is clicked
    const deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', function (event) {
        const btn   = event.relatedTarget;
        const id    = btn.getAttribute('data-book-id');
        const title = btn.getAttribute('data-book-title');
        document.getElementById('modalDeleteId').value        = id;
        document.getElementById('modalBookTitle').textContent = title;
    });
</script>

</body>
</html>