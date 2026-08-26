<?php

namespace Models;

class MarkerModel
{
    public ?int $id = null;
    public ?string $barcode_link = null; // Odkaz na AR znacku
    public ?int $barcode_value = null; // Hodnota AR znacky
}