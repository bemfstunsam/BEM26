<?php
/**
 * Export Data Pendaftaran SUIC 2026 ke Excel (.xlsx)
 * Menggunakan OpenXML via ZipArchive (built-in XAMPP via Apache)
 * Fallback ke SpreadsheetML jika ZipArchive tidak tersedia
 */

session_start();
require_once __DIR__ . '/db.php';

// ── Ambil semua data dari database ───────────────────────────
$conn = getDBConnection();
$result = $conn->query("SELECT id, nama_lengkap, nim, universitas, negara, nomor_hp, email, kategori_idc, file_surat, file_bukti_follow, created_at FROM suic_pendaftaran ORDER BY created_at DESC");

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
$conn->close();

$filename = 'SUIC_2026_Pendaftaran_' . date('Ymd_His');

// ══════════════════════════════════════════════════════════════
// Coba pakai ZipArchive untuk format .xlsx murni
// ══════════════════════════════════════════════════════════════
if (class_exists('ZipArchive')) {

    // ── Helper: angka kolom → huruf (1=A, 2=B, dst) ──────────
    function colLetter($n) {
        $letter = '';
        while ($n > 0) {
            $n--;
            $letter = chr(65 + ($n % 26)) . $letter;
            $n = intdiv($n, 26);
        }
        return $letter;
    }

    // ── Escape XML ────────────────────────────────────────────
    function xe($s) {
        return htmlspecialchars((string)$s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    // ── Headers tabel ─────────────────────────────────────────
    $headers = ['No', 'Nama Lengkap', 'NIM', 'Universitas', 'Negara', 'No HP/WA', 'Email', 'Kategori IDC', 'File Surat', 'File Bukti Follow', 'Tanggal Daftar'];

    // ── Build worksheet XML ───────────────────────────────────
    $sheetRows = '';
    $rowNum = 1;

    // Header row (style 1 = bold)
    $sheetRows .= '<row r="' . $rowNum . '">';
    foreach ($headers as $ci => $h) {
        $col = colLetter($ci + 1) . $rowNum;
        $sheetRows .= '<c r="' . $col . '" t="inlineStr" s="1"><is><t>' . xe($h) . '</t></is></c>';
    }
    $sheetRows .= '</row>';
    $rowNum++;

    // Data rows
    foreach ($data as $i => $row) {
        $cols = [
            $i + 1,
            $row['nama_lengkap'],
            $row['nim'],
            $row['universitas'],
            $row['negara'],
            $row['nomor_hp'],
            $row['email'],
            $row['kategori_idc'] ?: '-',
            $row['file_surat'] ?: '-',
            $row['file_bukti_follow'] ?: '-',
            $row['created_at'],
        ];
        $sheetRows .= '<row r="' . $rowNum . '">';
        foreach ($cols as $ci => $val) {
            $col = colLetter($ci + 1) . $rowNum;
            $sheetRows .= '<c r="' . $col . '" t="inlineStr"><is><t>' . xe($val) . '</t></is></c>';
        }
        $sheetRows .= '</row>';
        $rowNum++;
    }

    // ── Col widths ────────────────────────────────────────────
    $colWidths = '<cols>'
        . '<col min="1" max="1" width="5" customWidth="1"/>'    // No
        . '<col min="2" max="2" width="25" customWidth="1"/>'   // Nama
        . '<col min="3" max="3" width="18" customWidth="1"/>'   // NIM
        . '<col min="4" max="4" width="28" customWidth="1"/>'   // Universitas
        . '<col min="5" max="5" width="15" customWidth="1"/>'   // Negara
        . '<col min="6" max="6" width="18" customWidth="1"/>'   // HP
        . '<col min="7" max="7" width="28" customWidth="1"/>'   // Email
        . '<col min="8" max="8" width="38" customWidth="1"/>'   // Kategori IDC
        . '<col min="9" max="9" width="30" customWidth="1"/>'   // File Surat
        . '<col min="10" max="10" width="30" customWidth="1"/>' // File Bukti
        . '<col min="11" max="11" width="22" customWidth="1"/>' // Tanggal
        . '</cols>';

    $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
           xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  ' . $colWidths . '
  <sheetData>' . $sheetRows . '</sheetData>
</worksheet>';

    // ── Styles (bold untuk header) ────────────────────────────
    $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="2">
    <font><sz val="11"/><name val="Calibri"/></font>
    <font><b/><sz val="11"/><name val="Calibri"/></font>
  </fonts>
  <fills count="2">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
  </fills>
  <borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>
  <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
  <cellXfs count="2">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0"/>
  </cellXfs>
</styleSheet>';

    $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml"  ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml"           ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml"  ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml"             ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>';

    $relsRoot = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';

    $workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
          xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets><sheet name="Pendaftaran SUIC 2026" sheetId="1" r:id="rId1"/></sheets>
</workbook>';

    $workbookRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"   Target="styles.xml"/>
</Relationships>';

    // ── Buat file ZIP di memory ───────────────────────────────
    $tmpFile = tempnam(sys_get_temp_dir(), 'suic_xlsx_');
    $zip = new ZipArchive();
    $zip->open($tmpFile, ZipArchive::OVERWRITE);
    $zip->addFromString('[Content_Types].xml',          $contentTypes);
    $zip->addFromString('_rels/.rels',                  $relsRoot);
    $zip->addFromString('xl/workbook.xml',              $workbookXml);
    $zip->addFromString('xl/_rels/workbook.xml.rels',   $workbookRels);
    $zip->addFromString('xl/worksheets/sheet1.xml',     $sheetXml);
    $zip->addFromString('xl/styles.xml',                $stylesXml);
    $zip->close();

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
    header('Content-Length: ' . filesize($tmpFile));
    header('Cache-Control: max-age=0');
    readfile($tmpFile);
    unlink($tmpFile);
    exit;
}

// ══════════════════════════════════════════════════════════════
// Fallback: SpreadsheetML (dibuka Excel sebagai .xls, kompatibel)
// ══════════════════════════════════════════════════════════════
function xlsEscape($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

$headers = ['No', 'Nama Lengkap', 'NIM', 'Universitas', 'Negara', 'No HP/WA', 'Email', 'Kategori IDC', 'File Surat', 'File Bukti Follow', 'Tanggal Daftar'];

$xml = '<?xml version="1.0" encoding="UTF-8"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">
 <Styles>
  <Style ss:ID="Header">
   <Font ss:Bold="1" ss:Size="11"/>
   <Interior ss:Color="#F97316" ss:Pattern="Solid"/>
   <Font ss:Color="#FFFFFF" ss:Bold="1"/>
  </Style>
  <Style ss:ID="Cell">
   <Font ss:Size="10"/>
  </Style>
 </Styles>
 <Worksheet ss:Name="Pendaftaran SUIC 2026">
  <Table>';

// Header row
$xml .= '<Row>';
foreach ($headers as $h) {
    $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">' . xlsEscape($h) . '</Data></Cell>';
}
$xml .= '</Row>';

// Data rows
foreach ($data as $i => $row) {
    $cols = [
        $i + 1,
        $row['nama_lengkap'],
        $row['nim'],
        $row['universitas'],
        $row['negara'],
        $row['nomor_hp'],
        $row['email'],
        $row['kategori_idc'] ?: '-',
        $row['file_surat'] ?: '-',
        $row['file_bukti_follow'] ?: '-',
        $row['created_at'],
    ];
    $xml .= '<Row>';
    foreach ($cols as $ci => $val) {
        $type = ($ci === 0) ? 'Number' : 'String';
        $xml .= '<Cell ss:StyleID="Cell"><Data ss:Type="' . $type . '">' . xlsEscape($val) . '</Data></Cell>';
    }
    $xml .= '</Row>';
}

$xml .= '
  </Table>
 </Worksheet>
</Workbook>';

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
header('Cache-Control: max-age=0');
echo $xml;
exit;
