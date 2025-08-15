<?php
namespace LibrarySystem;

class BookRating {
    private array $ratings = []; 

    public function addRating(int $userId, int $rating): void {
        $this->ratings[$userId] = $rating;
    }

    public function getAverageRating(): float {
        if (empty($this->ratings)) return 0;
        return array_sum($this->ratings) / count($this->ratings);
    }

    public function getRatings(): array {
        return $this->ratings; 
    }

    public function getUserRating(int $userId): int {
        return $this->ratings[$userId] ?? 0; 
    }
}
