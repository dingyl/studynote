<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/23
 * Time: 上午11:22
 */
class Magick
{
//    private static $_soffice = "/usr/bin/soffice";
    private static $_soffice = "/Applications/LibreOffice.app/Contents/MacOS/soffice";
    private static $_convert_temp_dir = __DIR__."/temp/";

    static function ppt2png($ppt){
        $pdf_file = self::ppt2pdf($ppt);
        if($pdf_file){
            $pngs = self::pdf2png($pdf_file);
            unlink($pdf_file);
            if($pngs){
                return $pngs;
            }
        }
        return false;
    }

    static function ppt2pdf($ppt){
        $info = pathinfo($ppt);
        $ppt_name = $info['filename'];
        $extension = $info['extension'];
        if($extension!="ppt"){
            return false;
        }
        $command = self::$_soffice." --headless --invisible --convert-to pdf --outdir ".self::$_convert_temp_dir." $ppt";
        exec($command);
        $pdf_file = self::$_convert_temp_dir.$ppt_name.".pdf";
        if(file_exists($pdf_file)){
            return $pdf_file;
        }
        return false;
    }

    static function pdf2png($pdf,$page = -1)
    {
        if (!extension_loaded('imagick')) {
            return false;
        }
        if (!file_exists($pdf)) {
            return false;
        }
        if (!is_readable($pdf)) {
            return false;
        }
        $return = [];
        $im = new Imagick();
        $im->setResolution(100, 100);
        $im->setCompressionQuality(100);
        if ($page == -1){
            $im->readImage($pdf);
        }else{
            $im->readImage($pdf . "[" . $page . "]");
        }
        foreach ($im as $Key => $Var) {
            $Var->setImageFormat('png');
            $filename = self::$_convert_temp_dir . md5($Key . time()) . '.png';
            if ($Var->writeImage($filename) == true) {
                $return[] = $filename;
            }
        }
        return $return;
    }
}