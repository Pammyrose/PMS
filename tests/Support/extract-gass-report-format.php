<?php

// Extract only report presentation from the supplied reference, never its data.
$source = $argv[1] ?? throw new RuntimeException('Supply the reference XLSX path.');
$zip = new ZipArchive;
if ($zip->open($source) !== true) {
    throw new RuntimeException('Cannot open reference workbook.');
}
$sheet = new DOMDocument;
$sheet->loadXML($zip->getFromName('xl/worksheets/sheet1.xml'), LIBXML_NONET);
$strings = new DOMDocument;
$strings->loadXML($zip->getFromName('xl/sharedStrings.xml'), LIBXML_NONET);
$stringsPath = new DOMXPath($strings);
$stringsPath->registerNamespace('s', $sheet->documentElement->namespaceURI);
$shared = [];
foreach ($stringsPath->query('//s:si') as $item) {
    $value = '';
    foreach ($stringsPath->query('.//s:t', $item) as $text) {
        $value .= $text->textContent;
    }
    $shared[] = $value;
}
$path = new DOMXPath($sheet);
$path->registerNamespace('s', $sheet->documentElement->namespaceURI);
$format = new DOMDocument('1.0', 'UTF-8');
$root = $format->appendChild($format->createElement('report-format'));
$root->setAttribute('source', basename($source));
foreach (['xl/styles.xml', 'xl/theme/theme1.xml'] as $entry) {
    $document = new DOMDocument;
    $document->loadXML($zip->getFromName($entry), LIBXML_NONET);
    $root->appendChild($format->importNode($document->documentElement, true));
}
$layout = $root->appendChild($format->createElement('layout'));
foreach (['sheetPr', 'sheetViews', 'sheetFormatPr', 'cols', 'printOptions', 'pageMargins', 'pageSetup', 'headerFooter'] as $name) {
    $node = $path->query('/s:worksheet/s:'.$name)->item(0);
    if ($node) {
        $layout->appendChild($format->importNode($node, true));
    }
}
$headers = $root->appendChild($format->createElement('headers'));
foreach ($path->query('/s:worksheet/s:sheetData/s:row[number(@r) < 15]') as $row) {
    $copy = $format->importNode($row, true);
    foreach ($copy->getElementsByTagName('c') as $cell) {
        if ($cell->getAttribute('t') === 's') {
            $value = $shared[(int) $cell->getElementsByTagName('v')->item(0)->textContent];
            while ($cell->firstChild) {
                $cell->removeChild($cell->firstChild);
            }
            $cell->setAttribute('t', 'inlineStr');
            $inline = $cell->appendChild($format->createElementNS($sheet->documentElement->namespaceURI, 'is'));
            $text = $inline->appendChild($format->createElementNS($sheet->documentElement->namespaceURI, 't'));
            $text->setAttribute('xml:space', 'preserve');
            $text->appendChild($format->createTextNode($value));
        }
    }
    $headers->appendChild($copy);
}
$merges = $root->appendChild($format->createElement('header-merges'));
foreach ($path->query('//s:mergeCell') as $merge) {
    if (preg_match('/:(?:[A-Z]+)(\d+)$/', $merge->getAttribute('ref'), $match) && (int) $match[1] < 15) {
        $merges->appendChild($format->importNode($merge, true));
    }
}
$samples = $root->appendChild($format->createElement('samples'));
foreach (['financial-car' => 15, 'financial-office' => 19, 'section' => 42, 'physical-car' => 48, 'physical-office' => 49, 'physical-province' => 50] as $kind => $number) {
    $sample = $samples->appendChild($format->createElement('sample'));
    $sample->setAttribute('kind', $kind);
    $row = $format->importNode($path->query('//s:row[@r="'.$number.'"]')->item(0), true);
    foreach ($row->getElementsByTagName('c') as $cell) {
        while ($cell->firstChild) {
            $cell->removeChild($cell->firstChild);
        }
        $cell->removeAttribute('t');
    }
    $sample->appendChild($row);
}
$zip->close();
$format->save(dirname(__DIR__, 2).'/resources/templates/gass-accomplishment-format.xml');
echo "Extracted header and formatting only.\n";
