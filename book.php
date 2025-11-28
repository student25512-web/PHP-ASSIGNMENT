<?php
class Book {
    public $id;
    public $title;
    public $author;
    public $genre;
    public $price;

    public function __construct($id, $title, $author, $genre, $price) {
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->genre = $genre;
        $this->price = $price;
    }

    public function getDisplayPrice() {
        return "$" . number_format($this->price, 2);
    }
}