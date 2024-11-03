<?php

require __DIR__ . '/vendor/autoload.php';

use PHPJasper\PHPJasper;

$input = __DIR__ . '/examples/json.jrxml'; // ตรวจสอบให้แน่ใจว่าเส้นทางนี้ถูกต้อง
$tempOutputDir = sys_get_temp_dir(); // ใช้ไดเร็กทอรีชั่วคราวของระบบ
$tempPdfPath = $tempOutputDir . '/report_' . uniqid(); // สร้างชื่อไฟล์ PDF ชั่วคราว

// ดึงข้อมูล JSON จาก URL
$url = 'http://api.classicscan.co.th/contacts.json';
$jsonData = file_get_contents($url);

if ($jsonData === FALSE) {
	die('ไม่สามารถดึงข้อมูลจาก URL ได้');
}

$dataFilePath = $tempOutputDir . '/contacts_' . uniqid() . '.json';
file_put_contents($dataFilePath, $jsonData);

$options = [
	'format' => ['pdf'],
	'locale' => 'th',
	'fonts' => __DIR__ . '/fonts',
	'db_connection' => [
		'driver' => 'json',
		'data_file' => $dataFilePath,
	],
	'pdf' => [
		'embedded_fonts' => ['Kanit'] // ระบุฟอนต์ที่ฝัง
	] 
];

$jasper = new PHPJasper();

try {
	// ประมวลผลรายงานและบันทึกเป็น PDF ชั่วคราว
	$jasper->process($input, $tempPdfPath, $options)->execute();
	echo($tempPdfPath);
	
	// ส่ง PDF กลับไปยังผู้ใช้
	header('Content-Description: application/pdf');
	header('Content-Type: application/pdf');
	header('Content-Disposition: inline; filename="invoice.pdf"');
	header('Content-Length: ' . filesize($tempPdfPath,".pdf"));
	readfile($tempPdfPath.".pdf");

	// ลบไฟล์ PDF ชั่วคราวหลังจากการส่ง
	unlink($tempPdfPath.".pdf");
	flush();
		

} catch (Exception $e) {
	echo "เกิดข้อผิดพลาด: " . $e->getMessage();
} finally {
	// ลบไฟล์ JSON ชั่วคราว
	if (file_exists($dataFilePath)) {
		unlink($dataFilePath);
	}
}

?>
