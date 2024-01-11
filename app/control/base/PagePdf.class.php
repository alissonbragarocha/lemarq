<?php

use Adianti\Control\TPage;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Base\TScript;

class PagePdf extends TPage {
    public function __construct()
    {
        parent::__construct();
    }

    public function gerarpdf()
    {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->setCreator(PDF_CREATOR);
        $pdf->setAuthor('Nicola Asuni');
        $pdf->setTitle('TCPDF Example 002');
        $pdf->setSubject('TCPDF Tutorial');
        $pdf->setKeywords('TCPDF, PDF, example, test, guide');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->setMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $pdf->setFont('times', 'BI', 10);
        $pdf->AddPage();
        $txt = "TESTE DE IMPRESSAO ". K_PATH_URL . " ". K_PATH_CACHE;
        
        $pdf->Write(0, $txt, '', 0, 'C', true, 0, false, false, 0);
        $pdf->Output();
    }

    public function modo1(){
        $className = static::class;
        $method = 'gerarpdf';
        TScript::create("window.open('engine.php?class={$className}&method={$method}&static=1', '_blank').focus();");
    }

    public function modo2(){
        $className = static::class;
        $method = 'gerarpdf';

        $object = new TElement('iframe');
        $object->src   = "engine.php?class={$className}&method={$method}&static=1";
        $object->type  = 'application/pdf';
        $object->style = "width: 100%; height:80vh";
        
        parent::add($object);
    }

    // index.php?class=ControlTest&method=modo1&static=1
}