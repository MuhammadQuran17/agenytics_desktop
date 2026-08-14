<?php

namespace App\Services\AiChat;

use Illuminate\Http\UploadedFile;
use Smalot\PdfParser\Parser;

class PdfTextExtractor
{
    public function extract(UploadedFile $file): string
    {
        return (new Parser)->parseFile($file->getRealPath())->getText();
    }
}
