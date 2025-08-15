<?php
namespace LibrarySystem;

use LibrarySystem\Traits\LoggerTrait;

class Librarian extends User {
    use LoggerTrait;

    public function role(): string {
        return "Librarian";
    }

    public function addBook(Library $library, Book $book): void {
        $library->addBook($book);
        $this->log("Added book: {$book->getTitle()} - {$book->getAuthor()}", 'success');
    }

    public function removeBook(Library $library, Book $book): void {
        $library->removeBook($book);
        $this->log("Removed book: {$book->getTitle()}", 'warning');
    }
}
?>