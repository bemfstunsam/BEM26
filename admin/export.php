<?php
/**
 * Export Excel Registrasi SUIC 2026 (IDC)
 * Format: Microsoft Excel SpreadsheetML (.xls) / OpenXML
 * Mendukung semua filter: q, kategori, status, negara, rentang tanggal.
 * Dilindungi otentikasi admin & pencatatan audit log.
 *
 * URL: /bem/admin/export.php?q=...&kategori=...&status=...
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/db.php';

requireAuth();

// ── Parameter filter ──────────────────────────────────────────
$search    = trim(mb_substr($_GET['q']       ?? '', 0, 100));
$fKategori = trim($_GET['kategori']          ?? '');
$fNegara   = trim(mb_substr($_GET['negara']  ?? '', 0, 100));
$fStatus   = trim($_GET['status']            ?? '');
$fDateFrom = trim($_GET['date_from']         ?? '');
$fDateTo   = trim($_GET['date_to']           ?? '');

// Validasi enum
if (!in_array($fKategori, array_merge([''], IDC_CATEGORIES), true)) $fKategori = '';
if (!in_array($fStatus, array_merge([''], REG_STATUSES), true))     $fStatus   = '';

$validDate = function(string $d): bool {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) return false;
    [$y, $m, $dd] = explode('-', $d);
    return checkdate((int)$m, (int)$dd, (int)$y);
};
if (!$validDate($fDateFrom)) $fDateFrom = '';
if (!$validDate($fDateTo))   $fDateTo   = '';
if ($fDateFrom && $fDateTo && $fDateFrom > $fDateTo) {
    [$fDateFrom, $fDateTo] = [$fDateTo, $fDateFrom];
}

// ── Query builder ─────────────────────────────────────────────
$conn       = getDBConnection();
$conditions = [];
$params     = [];
$types      = '';

if ($search !== '') {
    $conditions[] = '(nama_lengkap LIKE ? OR nim LIKE ? OR email LIKE ? OR universitas LIKE ?)';
    $like = '%' . $search . '%';
    $params = array_merge($params, [$like, $like, $like, $like]);
    $types .= 'ssss';
}
if ($fKategori !== '') {
    $conditions[] = 'kategori_idc = ?';
    $params[]  = $fKategori;
    $types    .= 's';
}
if ($fNegara !== '') {
    $conditions[] = 'negara = ?';
    $params[]  = $fNegara;
    $types    .= 's';
}
if ($fStatus !== '') {
    $conditions[] = 'status = ?';
    $params[]  = $fStatus;
    $types    .= 's';
}
if ($fDateFrom !== '') {
    $conditions[] = 'DATE(created_at) >= ?';
    $params[]  = $fDateFrom;
    $types    .= 's';
}
if ($fDateTo !== '') {
    $conditions[] = 'DATE(created_at) <= ?';
    $params[]  = $fDateTo;
    $types    .= 's';
}

$where = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';

$sql  = "SELECT id, nama_lengkap, nim, universitas, negara, nomor_hp, email,
                kategori_idc, status, file_surat, file_bukti_follow,
                DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS tgl_daftar
         FROM suic_pendaftaran $where ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
$stmt->close();

// Audit log
auditLog($conn, 'export_excel', 'suic_pendaftaran', null,
    'Export Excel: filter=' . (http_build_query(array_filter([
        'q' => $search, 'kategori' => $fKategori, 'negara' => $fNegara,
        'status' => $fStatus, 'date_from' => $fDateFrom, 'date_to' => $fDateTo,
    ])) ?: 'semua') . ' (' . count($data) . ' baris)'
);
$conn->close();

function xe(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

$filename = 'SUIC_2026_IDC_Pendaftaran_' . date('Ymd_His') . '.xls';

if (ob_get_level()) ob_end_clean();

header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate');
header('Pragma: public');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Author>BEM FST UNSAM</Author>
  <Title>Data Registrasi SUIC 2026</Title>
  <Created><?= date('Y-m-d\TH:i:s\Z') ?></Created>
 </DocumentProperties>
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Center"/>
   <Borders/>
   <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#000000"/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="TitleHeader">
   <Font ss:FontName="Calibri" ss:Size="14" ss:Bold="1" ss:Color="#FFFFFF"/>
   <Interior ss:Color="#EA580C" ss:Pattern="Solid"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
  </Style>
  <Style ss:ID="SubTitle">
   <Font ss:FontName="Calibri" ss:Size="10" ss:Italic="1" ss:Color="#64748B"/>
   <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
  </Style>
  <Style ss:ID="ColHeader">
   <Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#FFFFFF"/>
   <Interior ss:Color="#0F172A" ss:Pattern="Solid"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#334155"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#334155"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#334155"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#334155"/>
   </Borders>
  </Style>
  <Style ss:ID="Cell">
   <Font ss:FontName="Calibri" ss:Size="10" ss:Color="#0F172A"/>
   <Alignment ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
   </Borders>
  </Style>
  <Style ss:ID="CellCenter">
   <Font ss:FontName="Calibri" ss:Size="10" ss:Color="#0F172A"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
   </Borders>
  </Style>
  <Style ss:ID="StatusPending">
   <Font ss:FontName="Calibri" ss:Size="10" ss:Bold="1" ss:Color="#C2410C"/>
   <Interior ss:Color="#FFEDD5" ss:Pattern="Solid"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
   </Borders>
  </Style>
  <Style ss:ID="StatusApproved">
   <Font ss:FontName="Calibri" ss:Size="10" ss:Bold="1" ss:Color="#15803D"/>
   <Interior ss:Color="#DCFCE7" ss:Pattern="Solid"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
   </Borders>
  </Style>
  <Style ss:ID="StatusRejected">
   <Font ss:FontName="Calibri" ss:Size="10" ss:Bold="1" ss:Color="#B91C1C"/>
   <Interior ss:Color="#FEE2E2" ss:Pattern="Solid"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
   </Borders>
  </Style>
 </Styles>
 <Worksheet ss:Name="Registrasi SUIC 2026">
  <Table ss:DefaultRowHeight="20">
   <Column ss:Width="35"/>   <!-- No -->
   <Column ss:Width="45"/>   <!-- ID -->
   <Column ss:Width="160"/>  <!-- Nama -->
   <Column ss:Width="95"/>   <!-- NIM -->
   <Column ss:Width="170"/>  <!-- Universitas -->
   <Column ss:Width="95"/>   <!-- Negara -->
   <Column ss:Width="110"/>  <!-- No HP -->
   <Column ss:Width="170"/>  <!-- Email -->
   <Column ss:Width="200"/>  <!-- Kategori IDC -->
   <Column ss:Width="85"/>   <!-- Status -->
   <Column ss:Width="120"/>  <!-- Dokumen Surat -->
   <Column ss:Width="120"/>  <!-- Dokumen Bukti -->
   <Column ss:Width="125"/>  <!-- Tanggal Daftar -->

   <!-- Judul Laporan -->
   <Row ss:Height="28">
    <Cell ss:MergeAcross="12" ss:StyleID="TitleHeader">
     <Data ss:Type="String">SAMUDRA UNIVERSITY INTERNATIONAL COMPETITION (SUIC) 2026 — INTERNATIONAL DESIGN COMPETITION</Data>
    </Cell>
   </Row>

   <!-- Subtitle metadata -->
   <Row ss:Height="18">
    <Cell ss:MergeAcross="12" ss:StyleID="SubTitle">
     <Data ss:Type="String">Diunduh pada: <?= date('d/m/Y H:i:s') ?> | Total: <?= count($data) ?> Pendaftar | Admin: <?= xe($_SESSION['admin_nama'] ?? 'Admin') ?></Data>
    </Cell>
   </Row>

   <!-- Header Kolom -->
   <Row ss:Height="26">
    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">No</Data></Cell>
    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">ID</Data></Cell>
    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Nama Lengkap</Data></Cell>
    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">NIM</Data></Cell>
    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Universitas</Data></Cell>
    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Negara</Data></Cell>
    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">No HP / WA</Data></Cell>
    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Email</Data></Cell>
    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Kategori IDC</Data></Cell>
    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Status</Data></Cell>
    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Surat Rekomendasi</Data></Cell>
    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Bukti Follow</Data></Cell>
    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Tanggal Daftar</Data></Cell>
   </Row>

   <!-- Baris Data -->
   <?php foreach ($data as $i => $row):
       $statusStyle = match($row['status']) {
           'approved' => 'StatusApproved',
           'rejected' => 'StatusRejected',
           default    => 'StatusPending',
       };
       $statusLabel = match($row['status']) {
           'approved' => 'Disetujui',
           'rejected' => 'Ditolak',
           default    => 'Pending',
       };
   ?>
   <Row ss:Height="22">
    <Cell ss:StyleID="CellCenter"><Data ss:Type="Number"><?= $i + 1 ?></Data></Cell>
    <Cell ss:StyleID="CellCenter"><Data ss:Type="Number"><?= (int)$row['id'] ?></Data></Cell>
    <Cell ss:StyleID="Cell"><Data ss:Type="String"><?= xe($row['nama_lengkap']) ?></Data></Cell>
    <Cell ss:StyleID="CellCenter"><Data ss:Type="String"><?= xe($row['nim']) ?></Data></Cell>
    <Cell ss:StyleID="Cell"><Data ss:Type="String"><?= xe($row['universitas']) ?></Data></Cell>
    <Cell ss:StyleID="CellCenter"><Data ss:Type="String"><?= xe($row['negara']) ?></Data></Cell>
    <Cell ss:StyleID="CellCenter"><Data ss:Type="String"><?= xe($row['nomor_hp']) ?></Data></Cell>
    <Cell ss:StyleID="Cell"><Data ss:Type="String"><?= xe($row['email']) ?></Data></Cell>
    <Cell ss:StyleID="Cell"><Data ss:Type="String"><?= xe($row['kategori_idc'] ?? '-') ?></Data></Cell>
    <Cell ss:StyleID="<?= $statusStyle ?>"><Data ss:Type="String"><?= $statusLabel ?></Data></Cell>
    <Cell ss:StyleID="CellCenter"><Data ss:Type="String"><?= !empty($row['file_surat']) ? 'Tersedia' : '-' ?></Data></Cell>
    <Cell ss:StyleID="CellCenter"><Data ss:Type="String"><?= !empty($row['file_bukti_follow']) ? 'Tersedia' : '-' ?></Data></Cell>
    <Cell ss:StyleID="CellCenter"><Data ss:Type="String"><?= xe($row['tgl_daftar']) ?></Data></Cell>
   </Row>
   <?php endforeach; ?>

  </Table>
 </Worksheet>
</Workbook>
<?php
exit;
