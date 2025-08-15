<?php
namespace LibrarySystem;

use LibrarySystem\Traits\LoggerTrait;

class Member extends User {
    use LoggerTrait;

    private array $borrowedBooks = [];
    private array $bookRatings = []; 

    public function role(): string {
        return "Member";
    }

    public function hasBorrowed(Book $book): bool {
        foreach ($this->borrowedBooks as $b) {
            if ($b->getTitle() === $book->getTitle() && $b->getAuthor() === $book->getAuthor()) {
                return true;
            }
        }
        return false;
    }

    public function borrowBook(Book $book): void {
        if ($this->hasBorrowed($book)) {
            $this->log("{$this->name} has already borrowed this book: {$book->getTitle()}", 'warning');
        } else {
            $this->borrowedBooks[] = $book;
            $this->log("{$this->name} borrowed book: {$book->getTitle()}", 'success');
        }
    }

    public function getBorrowedBooks(): array {
        return $this->borrowedBooks;
    }

    public function rateBook(string $bookTitle, int $rating): void {
        $this->bookRatings[$bookTitle] = $rating;
    }

    public function getBookRating(string $bookTitle): int {
        return $this->bookRatings[$bookTitle] ?? 0;
    }
}
