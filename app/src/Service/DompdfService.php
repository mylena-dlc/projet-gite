<?php 

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class DompdfService
{
    private $pdfOptions;

    public function __construct(array $pdfOptions = [])
    {
        $this->pdfOptions = $pdfOptions;
    }

    public function generatePdf(string $html): string
    {
        $options = new Options();

        // Appliquer les options spécifiées
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isJavascriptEnabled', true);
        
        // Appliquer les options passées lors de la construction
        foreach ($this->pdfOptions as $key => $value) {
            $options->set($key, $value);
        }

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
