<?php
namespace LibrarySystem;

class Book {
    private string $title;
    private string $author;
    private array $ratings = [];
    private string $filePath = "";

    public function __construct(string $title, string $author, string $filePath = "") {
        $this->title = $title;
        $this->author = $author;
        $this->filePath = $filePath;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getAuthor(): string {
        return $this->author;
    }

    public function rate(int $userId, int $rating) {
        $this->ratings[$userId] = $rating;
    }

    public function getAverageRating(): float {
        if (count($this->ratings) === 0) return 0;
        return array_sum($this->ratings) / count($this->ratings);
    }

    public function getFilePath(): string {
        return $this->filePath;
    }

    public function setFilePath(string $path): void {
        $this->filePath = $path;
    }
}
