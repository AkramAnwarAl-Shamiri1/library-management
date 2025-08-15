<?php
session_start();

require __DIR__ . '/vendor/autoload.php';

use LibrarySystem\Library;
use LibrarySystem\Book;
use LibrarySystem\Member;
use LibrarySystem\Librarian;
use LibrarySystem\EmailNotification;

if (!isset($_SESSION['library'])) {
    $_SESSION['library'] = serialize(new Library());
}
$library = unserialize($_SESSION['library']);


$currentUser = isset($_SESSION['current_user']) ? unserialize($_SESSION['current_user']) : null;


if (!isset($_SESSION['logs'])) $_SESSION['logs'] = [];


function logWithoutUser(string $message, string $type = 'info') {
    if (!isset($_SESSION['logs'])) $_SESSION['logs'] = [];
    $color = match($type) {
        'success' => 'green',
        'warning' => 'orange',
        'error' => 'red',
        default => 'gray'
    };
    $_SESSION['logs'][] = ['msg' => $message, 'color' => $color];
}


$notifier = new EmailNotification();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $_SESSION['logs'] = []; 

    if (isset($_POST['add_user'])) {
        $role = $_POST['role'];
        $name = $_POST['name'];
        $email = $_POST['email'];

        $nameExists = false;
        $emailExists = false;

        foreach ($library->getUsers() as $u) {
            if (strtolower($u->getName()) === strtolower($name)) $nameExists = true;
            if (strtolower($u->getEmail()) === strtolower($email)) $emailExists = true;
        }

        if ($nameExists) {
            logWithoutUser("Username already exists: $name", 'error');
        } elseif ($emailExists) {
            logWithoutUser("Email is already used: $email", 'error');
        } else {
            $user = $role === 'member' ? new Member($name, $email) : new Librarian($name, $email);
            $library->addUser($user);
            $_SESSION['current_user'] = serialize($user);
            $currentUser = $user;
            $currentUser->log("Welcome to the library, $name!", 'success');
            $currentUser->sendEmailNotification("Hello $name, welcome to the library! Your account has been created successfully.");
        }
    }

    
    if (isset($_POST['view_user'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $found = false;

        foreach ($library->getUsers() as $u) {
            if (strtolower($u->getName()) === strtolower($name) &&
                strtolower($u->getEmail()) === strtolower($email) &&
                strtolower($u->role()) === strtolower($role)) {
                $currentUser = $u;
                $_SESSION['current_user'] = serialize($u);
                $currentUser->log("User found: $name!", 'success');
                $currentUser->sendEmailNotification("Hello $name, you have successfully logged into the library.");
                $found = true;
                break;
            }
        }
        if (!$found) logWithoutUser("User not found.", 'error');
    }

    
    if (isset($_POST['add_book']) && $currentUser instanceof Librarian) {
        $title = $_POST['title'];
        $author = $_POST['author'];
        $exists = false;

        foreach ($library->getBooks() as $b) {
            if (strtolower($b->getTitle()) === strtolower($title) &&
                strtolower($b->getAuthor()) === strtolower($author)) {
                $exists = true;
                break;
            }
        }

        if ($exists) {
            $currentUser->log("Book already exists: $title - $author", 'error');
        } else {
           
            $filePath = "";
            if (isset($_FILES['book_file']) && $_FILES['book_file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $filename = basename($_FILES['book_file']['name']);
                $filePath = 'uploads/' . $filename;
                move_uploaded_file($_FILES['book_file']['tmp_name'], $filePath);
            }

            $book = new Book($title, $author, $filePath);
            $currentUser->addBook($library, $book);
        }
    }

    
    if (isset($_POST['remove_book']) && $currentUser instanceof Librarian) {
        $title = $_POST['remove_title'];
        $found = false;
        foreach ($library->getBooks() as $book) {
            if ($book->getTitle() === $title) {
                $currentUser->removeBook($library, $book);
                $found = true;
                break;
            }
        }
        if (!$found) $currentUser->log("Book not found: $title", 'error');
    }

    if (isset($_POST['borrow_book']) && $currentUser instanceof Member) {
        $bookTitle = $_POST['book_title'];
        $found = false;
        foreach ($library->getBooks() as $book) {
            if ($book->getTitle() === $bookTitle) {
                $currentUser->borrowBook($book);
                $found = true;
                break;
            }
        }
        if (!$found) $currentUser->log("Book not found: $bookTitle", 'error');
    }


    if (isset($_POST['rate_book']) && $currentUser instanceof Member) {
        $bookTitle = $_POST['book_title'];
        $rating = (int)$_POST['book_rating'];
        foreach ($library->getBooks() as $book) {
            if ($book->getTitle() === $bookTitle) {
                $book->rate($currentUser->getId(), $rating);
                $currentUser->log("You rated the book '$bookTitle' with $rating stars", 'success');
                break;
            }
        }
    }
}

$_SESSION['library'] = serialize($library);
if ($currentUser) $_SESSION['current_user'] = serialize($currentUser);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Library Management</title>
<style>
body { font-family: Arial, sans-serif; background: #f0f2f5; margin: 20px; }
h1 { text-align: center; color: #333; }
.dashboard { display: flex; gap: 40px; flex-wrap: wrap; justify-content: center; }
.forms-column { flex: 1; min-width: 350px; }
.tables-column { flex: 1; min-width: 400px; }
form { background: #fff; padding: 20px; margin-bottom: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
form h2 { margin-top: 0; color: #444; }
form input[type="text"], form input[type="email"], form select, form input[type="file"] { width: 100%; padding: 10px; margin: 8px 0 15px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; box-sizing: border-box; }
form button { background-color: #28a745; color: white; padding: 10px 18px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; transition: 0.3s; }
form button:hover { background-color: #218838; }
table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
th { background-color: #007BFF; color: #fff; }
.log-message { padding: 10px 15px; border-radius: 6px; margin-bottom: 15px; font-family: monospace; color:white; }
.star-rating { display: inline-block; }
.star { cursor: pointer; font-size: 18px; color: #ccc; transition: color 0.2s; margin-right: 2px; }
.star.hovered, .star.selected { color: gold; }
</style>
</head>
<body>
<h1>Library Management</h1>

<div class="dashboard">
    <div class="forms-column">
        <?php foreach ($_SESSION['logs'] as $log): ?>
            <div class="log-message" style="background-color: <?= $log['color'] ?>;">
                <?= htmlspecialchars($log['msg']) ?>
            </div>
        <?php endforeach; ?>

        <h2>Add / View User</h2>
        <form method="post">
            Name: <input type="text" name="name" placeholder="Enter name" required>
            Email: <input type="email" name="email" placeholder="Enter email" required>
            Role:
            <select name="role">
                <option value="member">Member</option>
                <option value="librarian">Librarian</option>
            </select>
            <button type="submit" name="add_user">Add User</button>
            <button type="submit" name="view_user">View User</button>
        </form>

        <?php if ($currentUser instanceof Librarian): ?>
        <h2>Add Book</h2>
        <form method="post" enctype="multipart/form-data">
            Title: <input type="text" name="title" placeholder="Enter book title" required>
            Author: <input type="text" name="author" placeholder="Enter author name" required>
            File: <input type="file" name="book_file">
            <button type="submit" name="add_book">Add Book</button>
        </form>
        <?php endif; ?>
    </div>

    <div class="tables-column">
        <h2>Users</h2>
        <table id="usersTable">
            <tr><th>Name</th><th>Email</th><th>Role</th></tr>
            <?php if ($currentUser): ?>
            <tr>
                <td><?= htmlspecialchars($currentUser->getName()) ?></td>
                <td><?= htmlspecialchars($currentUser->getEmail()) ?></td>
                <td><?= htmlspecialchars($currentUser->role()) ?></td>
            </tr>
            <?php endif; ?>
        </table>

        <h2>Books</h2>
        <input type="text" id="searchBooks" placeholder="Search by book title..." style="width:100%; padding:10px; margin-bottom:10px; border-radius:6px; border:1px solid #ccc; box-sizing:border-box;">
        <table id="booksTable">
            <tr><th>Title</th><th>Author</th><th>Rate</th><th>File</th><th>Action</th></tr>
            <?php foreach ($library->getBooks() as $book): ?>
            <tr>
                <td><?= htmlspecialchars($book->getTitle()) ?></td>
                <td><?= htmlspecialchars($book->getAuthor()) ?></td>
                <td>
                <?php if ($currentUser instanceof Member): ?>
                    <div class="star-rating" data-title="<?= htmlspecialchars($book->getTitle()) ?>">
                        <?php 
                        $avg = round($book->getAverageRating());
                        for ($i = 1; $i <= 5; $i++): 
                            $selected = $i <= $avg ? 'selected' : '';
                        ?>
                            <span class="star <?= $selected ?>" data-value="<?= $i ?>">★</span>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
                </td>
                <td>
                    <?php if ($book->getFilePath()): ?>
                        <a href="<?= htmlspecialchars($book->getFilePath()) ?>" target="_blank">Download</a>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($currentUser instanceof Librarian): ?>
                        <form method="post" style="display:inline">
                            <input type="hidden" name="remove_title" value="<?= htmlspecialchars($book->getTitle()) ?>">
                            <button type="submit" name="remove_book">Remove</button>
                        </form>
                    <?php elseif ($currentUser instanceof Member): ?>
                        <form method="post" style="display:inline">
                            <input type="hidden" name="book_title" value="<?= htmlspecialchars($book->getTitle()) ?>">
                            <button type="submit" name="borrow_book">Borrow</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<script>

const bookInput = document.getElementById('searchBooks');
const bookRows = document.querySelectorAll('#booksTable tr:not(:first-child)');
bookInput.addEventListener('input', () => {
    const filter = bookInput.value.toLowerCase();
    bookRows.forEach(row => {
        const title = row.cells[0].textContent.toLowerCase();
        row.style.display = title.includes(filter) ? '' : 'none';
    });
});


document.querySelectorAll('.star-rating').forEach(container => {
    const stars = container.querySelectorAll('.star');
    stars.forEach(star => {
        star.addEventListener('mouseenter', () => {
            stars.forEach(s => s.classList.remove('hovered'));
            for (let i=0; i<star.dataset.value; i++) stars[i].classList.add('hovered');
        });
        star.addEventListener('mouseleave', () => {
            stars.forEach(s => s.classList.remove('hovered'));
        });
        star.addEventListener('click', () => {
            const value = parseInt(star.dataset.value);
            const title = container.dataset.title;

            stars.forEach(s => s.classList.remove('selected'));
            for (let i=0; i<value; i++) stars[i].classList.add('selected');

            const form = document.createElement('form');
            form.method = 'post';
            form.style.display = 'none';
            const inputTitle = document.createElement('input');
            inputTitle.name = 'book_title';
            inputTitle.value = title;
            form.appendChild(inputTitle);
            const inputRating = document.createElement('input');
            inputRating.name = 'book_rating';
            inputRating.value = value;
            form.appendChild(inputRating);
            const inputSubmit = document.createElement('input');
            inputSubmit.name = 'rate_book';
            inputSubmit.type = 'hidden';
            inputSubmit.value = '1';
            form.appendChild(inputSubmit);
            document.body.appendChild(form);
            form.submit();
        });
    });
});
</script>
</body>
</html>
