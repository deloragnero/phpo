<?php
/**
 * MiniPdf — générateur PDF ultra-léger, sans dépendance externe (pas de composer requis).
 * Suffisant pour produire des tableaux/rapports texte (export de listes d'inscriptions).
 * Police standard Helvetica (WinAnsiEncoding) => les accents français passent via cp1252.
 */
class MiniPdf
{
    private array $pages = [];
    private string $curPage = '';
    private float $pageW;
    private float $pageH;
    private string $orientation;
    private array $fontUsed = [];

    public function __construct(string $orientation = 'P', string $format = 'A4')
    {
        $this->orientation = $orientation;
        if ($format === 'A4') {
            $this->pageW = $orientation === 'L' ? 841.89 : 595.28;
            $this->pageH = $orientation === 'L' ? 595.28 : 841.89;
        }
    }

    public function addPage(): void
    {
        if ($this->curPage !== '') {
            $this->pages[] = $this->curPage;
        }
        $this->curPage = '';
    }

    private function enc(string $s): string
    {
        // UTF-8 -> Windows-1252 (couvre les accents français), puis échappement PDF
        $converted = @iconv('UTF-8', 'CP1252//TRANSLIT', $s);
        if ($converted === false) $converted = $s;
        $converted = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $converted);
        return $converted;
    }

    public function text(float $x, float $y, string $txt, float $size = 10, bool $bold = false): void
    {
        $font = $bold ? 'F2' : 'F1';
        $this->fontUsed[$font] = true;
        $py = $this->pageH - $y;
        $this->curPage .= "BT /$font $size Tf $x $py Td (" . $this->enc($txt) . ") Tj ET\n";
    }

    public function line(float $x1, float $y1, float $x2, float $y2, float $width = 0.5): void
    {
        $py1 = $this->pageH - $y1;
        $py2 = $this->pageH - $y2;
        $this->curPage .= "$width w $x1 $py1 m $x2 $py2 l S\n";
    }

    public function rectFill(float $x, float $y, float $w, float $h, array $rgb): void
    {
        $py = $this->pageH - $y - $h;
        [$r, $g, $b] = $rgb;
        $this->curPage .= sprintf("%.3f %.3f %.3f rg %s %s %s %s re f\n", $r, $g, $b, $x, $py, $w, $h);
    }

    public function getPageWidth(): float { return $this->pageW; }
    public function getPageHeight(): float { return $this->pageH; }

    public function output(string $filename): void
    {
        if ($this->curPage !== '') {
            $this->pages[] = $this->curPage;
            $this->curPage = '';
        }

        $objects = [];
        $n = 1;

        $catalogId = $n++;
        $pagesId = $n++;
        $fontRegId = $n++;
        $fontBoldId = $n++;

        $pageIds = [];
        $contentIds = [];
        foreach ($this->pages as $content) {
            $pageIds[] = $n++;
            $contentIds[] = $n++;
        }

        $objects[$catalogId] = "<< /Type /Catalog /Pages $pagesId 0 R >>";

        $kids = implode(' ', array_map(fn($id) => "$id 0 R", $pageIds));
        $objects[$pagesId] = "<< /Type /Pages /Kids [$kids] /Count " . count($pageIds) . " >>";

        $objects[$fontRegId] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>";
        $objects[$fontBoldId] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>";

        foreach ($this->pages as $i => $content) {
            $pageId = $pageIds[$i];
            $contentId = $contentIds[$i];
            $objects[$pageId] = "<< /Type /Page /Parent $pagesId 0 R /MediaBox [0 0 {$this->pageW} {$this->pageH}] "
                . "/Resources << /Font << /F1 $fontRegId 0 R /F2 $fontBoldId 0 R >> >> /Contents $contentId 0 R >>";
            $stream = $content;
            $objects[$contentId] = "<< /Length " . strlen($stream) . " >>\nstream\n$stream\nendstream";
        }

        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "$id 0 obj\n$body\nendobj\n";
        }

        $xrefStart = strlen($pdf);
        $count = count($objects) + 1;
        $pdf .= "xref\n0 $count\n";
        $pdf .= "0000000000 65535 f \n";
        for ($id = 1; $id <= count($objects); $id++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$id]);
        }
        $pdf .= "trailer\n<< /Size $count /Root $catalogId 0 R >>\nstartxref\n$xrefStart\n%%EOF";

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
    }
}
