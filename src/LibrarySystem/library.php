<?php
namespace LibrarySystem;

class Library {
    private array $users = [];
    private array $books = [];

    public function addUser(User $user): void {
        $this->users[] = $user;
    }

    public function getUsers(): array {
        return $this->users;
    }

    public function addBook(Book $book): void {
        $this->books[] = $book;
    }

    public function removeBook(Book $book): void {
        foreach ($this->books as $i => $b) {
            if ($b->getTitle() === $book->getTitle() && $b->getAuthor() === $book->getAuthor()) {
                unset($this->books[$i]);
                $this->books = array_values($this->books);
                break;
            }
        }
    }

    public function getBooks(): array {
        return $this->books;
    }
}
