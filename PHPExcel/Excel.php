<?php
require 'Classes/PHPExcel.php';
class Excel{
    /**
     * 写入数据到文件中
     * @param $data
     * @param $dir
     * @return string
     */
    public static function write($data,$dir){
        $excel = self::dataToExcel($data);
        $writer = new PHPExcel_Writer_Excel5($excel);
        $dir = rtrim($dir,'/');
        $path = $dir.'/'.time().'.xls';
        $writer->save($path);
        return $path;
    }

    /**
     * 数据转换成excel对象
     * @param $data  数据需是key=>value的形式
     * @return PHPExcel
     */
    protected static function dataToExcel($data){
        $font_size = 16;

        $excel = new PHPExcel();
        $excel->getProperties()->setCreator("Dave");
        $excel->getProperties()->setLastModifiedBy("Dave");
        $excel->getProperties()->setTitle("Office 2007 Document");
        $excel->getProperties()->setSubject("Office 2007 Document");
        $excel->getProperties()->setDescription("excel");
        $excel->getProperties()->setKeywords("office 2007 php");
        $excel->getProperties()->setCategory("Test result file");
        $excel->setActiveSheetIndex(0);

        $activeSheet = $excel->getActiveSheet();
        if($count = count($data)){
            $headers = array_keys($data[0]);
            $columnIndex = ord('A');
            foreach($headers as $header){
                $cellLocation = chr($columnIndex).'1';
                $activeSheet->setCellValue($cellLocation, $header);
                $activeSheet->getStyle($cellLocation)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $activeSheet->getColumnDimension(chr($columnIndex))->setAutoSize(true);
                $activeSheet->getStyle($cellLocation)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_DARKGREEN);
                $activeSheet->getStyle($cellLocation)->getFont()->setBold(true);
                $activeSheet->getStyle($cellLocation)->getFont()->setSize($font_size);
                $columnIndex++;
            }
        }

        $rowIndex = 2;
        foreach($data as $row){
            $columnIndex = ord('A');
            foreach($row as $cell){
                $cellLocation = chr($columnIndex).$rowIndex;
                $activeSheet->setCellValue($cellLocation, $cell);
                $activeSheet->getStyle($cellLocation)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $activeSheet->getStyle($cellLocation)->getFont()->setSize($font_size);
                $columnIndex++;
            }
            $rowIndex++;
        }
        return $excel;
    }

    /**
     * 读取数据
     * @param $path
     * @return array|bool
     */
    public static function read($path){
        $reader = new PHPExcel_Reader_Excel2007();
        if(!$reader->canRead($path))
        {
            $reader = new PHPExcel_Reader_Excel5();
            if( ! $reader->canRead($path)){
                return false;
            }
        }
        $excel = $reader->load($path);
        $currentSheet = $excel->getSheet(0);
        $allColumn = $currentSheet->getHighestColumn();
        $allRow = $currentSheet->getHighestRow();

        $headers = [];
        for($columnIndex = ord('A');$columnIndex <= ord($allColumn);$columnIndex++){
            $columnCode = chr($columnIndex);
            $headers[] = $currentSheet->getCell($columnCode.'1')->getValue();
        }


        $data = [];
        for ($rowIndex = 1; $rowIndex <= $allRow; $rowIndex++)
        {
            $temp = [];$headerIndex = 0;
            for($columnIndex = ord('A');$columnIndex <= ord($allColumn);$columnIndex++,$headerIndex++){
                $columnCode = chr($columnIndex);
                $temp[$headers[$headerIndex]] = $currentSheet->getCell($columnCode.$rowIndex)->getValue();
            }
            $data[] = $temp;
        }
        return $data;
    }


    /**
     * 导出数据
     * @param $data
     */
    public static function export($data){
        $excel = self::dataToExcel($data);
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.time().'.xls"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $writer = new PHPExcel_Writer_Excel5($excel);
        $writer->save('php://output');
    }
}