<?php
//error_reporting(0);
include 'PHPExcel.php';
include 'PHPExcel/Reader/Excel2007.php';
include 'PHPExcel/Reader/Excel5.php';

$filePath = "../1514261002.xls";
$PHPReader = new PHPExcel_Reader_Excel2007();
if(!$PHPReader->canRead($filePath))
   {
       $PHPReader = new PHPExcel_Reader_Excel5();
       if( ! $PHPReader->canRead($filePath)){
           echo 'no Excel';
           return ;
       }
}

$PHPExcel = $PHPReader->load($filePath); //读取文件
$currentSheet = $PHPExcel->getSheet(0); //读取第一个工作簿
$allColumn = $currentSheet->getHighestColumn(); // 所有列数
$allRow = $currentSheet->getHighestRow(); // 所有行数

$data = array(); //下面是读取想要获取的列的内容
for ($rowIndex = 1; $rowIndex <= $allRow; $rowIndex++)
    {
        $data[] = array(
                '名字' => $cell = $currentSheet->getCell('A'.$rowIndex)->getValue(),
        '性别' => $cell = $currentSheet->getCell('B'.$rowIndex)->getValue(),
        '年龄' => $cell = $currentSheet->getCell('C'.$rowIndex)->getValue(),
        '出生日期' => $cell = $currentSheet->getCell('D'.$rowIndex)->getValue(),
        '电话号码' => $cell = $currentSheet->getCell('E'.$rowIndex)->getValue(),
    );
}

echo "<pre>";
print_r($data);
echo "</pre>";