<?php






















use setasign\Fpdi\Fpdi;





require_once plugin_dir_path(dirname(__FILE__)) . 'resources\php\fpdf\fpdf.php';

require_once plugin_dir_path(dirname(__FILE__)) . 'resources\php\FPDI\src\autoload.php';


//doompdf
require_once plugin_dir_path(dirname(__FILE__)) . 'resources\php\dompdf\autoload.inc.php';

use Dompdf\Dompdf;

//require plugin_dir_path(dirname(__FILE__)) . 'resources\php\pdfcrowd\pdfcrowd.php';
//g-28_unlocked.pdf


class PdfService
{

    public  static function setInputFieldPerPathWithTCPDF($src, $path, $value)
    {
        error_log("setInputFieldPerPathWithTCPDF");

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();
        $pdf->setSourceFile($pdf_path);
        $tplIdx = $pdf->importPage(1);
        $pdf->useTemplate($tplIdx, 10, 10, 200);

        // set font and color for form fields
        $pdf->SetFont('helvetica', '', 12);
        $pdf->SetTextColor(0, 0, 0);

        // set form field value
        $pdf->SetValue($field_name, $new_value);

        // save the updated PDF
        $pdf->Output('updated_pdf.pdf', 'F');
    }
    public static function getExternalPdf($url)
    {
        $tempPdf = RoutesService::getupload("viewer.pdf", "absolute");

        //verificar si el archivo existe
        if (file_exists($tempPdf)) {
            //si existe lo borramos
            unlink($tempPdf);
        }
        //verificar si tengo permisos de escritura
        if (!is_writable(dirname($tempPdf))) {
            chmod(dirname($tempPdf), 0777);
        }
        //verificar si cURL esta habilitado
        if (!function_exists('curl_init')) {
            die('cURL is not installed!');
        }
        // Descarga el archivo PDF desde el servidor externo
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode != 200) {
            die('Error: Unable to download file');
        }
        //verificar si es un pdf
        /*if (strpos($http_response_header[0], "application/pdf") === false) {
            error_log(print_r($http_response_header, true));
            die('Error: Invalid file type');
        }*/

        // Sobreescribe el archivo "viewer.pdf" con el archivo PDF descargado
        if (file_put_contents($tempPdf, $data) === false) {
            die('Error: Unable to write to file');
        }
        // Devolver la ruta del archivo "viewer.pdf"
        $relativePath = RoutesService::getupload("viewer.pdf");
        return $relativePath;
    }
    public static function fillForm($slug, $postData, $id_application)
    {

        $cords = file_get_contents(RoutesService::getemplateform("$slug.json", "url"));
        //si no existe el archivo json

        if ($cords === false) {
            error_log("no existe el archivo json");
            return false;
        }
        /*
            recorrer postData y verificar los campos name_pdf_field si alguno coincide con una key del json
            agregar el valor answer de postData a json

        */
        $cords = json_decode($cords, true);
        foreach ($postData as $key => $value) {

            $name_field_pdf = $value['name_pdf_field']; //name_pdf_field
            $answer = $value['answer']; //answer
            if ($name_field_pdf != "") {

                $name_field_pdf . "<br>";
                foreach ($cords as $name_field => $cords_value) {
                    foreach ($cords_value as $key => $value) {

                        if ($name_field_pdf == $key) {
                            ///validar fecha en 3 pasos
                            //1. si tiene dos /  o - es una fecha
                            //2. si tiene >= de 10 caracteres es una fecha
                            $validate = 0;
                            //si es un string
                            if (is_string($answer)) {
                                if (strpos($answer, "/") !== false) {
                                    $validate++;
                                }
                                if (strpos($answer, "-") !== false) {
                                    $validate++;
                                }
                                if (strlen($answer) >= 10) {
                                    $validate++;
                                }
                                if (strtotime($answer) === false) {
                                    $validate = 0;
                                }
                            }


                            if ($validate >= 2) {
                                $answer = date("m/d/Y", strtotime($answer));
                            }

                            $cords[$name_field][$key]['answer'] = $answer;
                        }
                    }
                }
            }
        }

        try {
            $fpdi = new Fpdi();
            $pdf = RoutesService::getroot() . "templates_files/$slug.pdf";
            //verificar si el archivo existe
            if (!file_exists($pdf)) {
                error_log("no existe el archivo pdf");
                error_log($pdf);
                return false;
            }
            $pageCount = $fpdi->setSourceFile($pdf);
            //agregar pagina
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {

                $fpdi->AddPage();
                $tplIdx = $fpdi->importPage($pageNo);
                $fpdi->useTemplate($tplIdx, 10, 10, 200);
                if (isset($cords[$pageNo - 1])) {
                    for ($i = 0; $i < count($cords[$pageNo - 1]); $i++) {

                        $key = array_keys($cords[$pageNo - 1])[$i];
                        $fpdi->SetFont('Helvetica', '', 10);
                        $fpdi->SetTextColor(0, 0, 0);

                        if (isset($cords[$pageNo - 1][$key]["answer"])) {
                            $write = false;
                            //verificar si tiene el campo options que puede inclur font, size, color, letterspacing, margin-left, margin-top
                            if (isset($cords[$pageNo - 1][$key]["options"])) {

                                $write = false;
                                //verificar si tiene el campo options que puede inclur font, size, color, letterspacing, margin-left, margin-top
                                if (isset($cords[$pageNo - 1][$key]["options"])) {
                                    $size = isset($cords[$pageNo - 1][$key]["options"]["size"])  ? $cords[$pageNo - 1][$key]["options"]["size"] : 10;
                                    $font = isset($cords[$pageNo - 1][$key]["options"]["font"])  ? $cords[$pageNo - 1][$key]["options"]["font"] : "Helvetica";
                                    $color = isset($cords[$pageNo - 1][$key]["options"]["color"])  ? $cords[$pageNo - 1][$key]["options"]["color"] : [0, 0, 0];
                                    $marginLeft = isset($cords[$pageNo - 1][$key]["options"]["margin-left"]) ? $cords[$pageNo - 1][$key]["options"]["margin-left"] : 0;
                                    $marginTop = isset($cords[$pageNo - 1][$key]["options"]["margin-top"])  ? $cords[$pageNo - 1][$key]["options"]["margin-top"] : 0;
                                    $sizeAuto = isset($cords[$pageNo - 1][$key]["options"]["size-auto"])  ? $cords[$pageNo - 1][$key]["options"]["size-auto"] : false;

                                    if ($sizeAuto) {
                                        $proporcion = $sizeAuto["constant"]; //0.8
                                        $container = $sizeAuto["container"]; //15.36mm
                                        $container = $container * 72 / 25.4; //15.36mm a pt
                                        $characters = strlen($cords[$pageNo - 1][$key]["answer"]); // 10
                                        $size = $container / $characters * $proporcion;
                                        $min = $sizeAuto["min"] ?? 0;
                                        $max = $sizeAuto["max"] ?? 100;
                                        if ($size < $min) {
                                            $size = $min;
                                        }
                                        if ($size > $max) {
                                            $size = $max;
                                        }
                                    }



                                    $fpdi->SetFont($font, '', $size);
                                    $fpdi->SetTextColor($color[0], $color[1], $color[2]);
                                    //si letterspacing es mayor a 0 se agrega el espacio entre letras
                                    if (
                                        isset($cords[$pageNo - 1][$key]["options"]["letterspacing"]) &&
                                        $cords[$pageNo - 1][$key]["options"]["letterspacing"] > 0
                                    ) {


                                        $spacingX = $cords[$pageNo - 1][$key]["options"]["letterspacing"];
                                        for ($a = 0; $a < strlen($cords[$pageNo - 1][$key]["answer"]); $a++) {
                                            $letter = $cords[$pageNo - 1][$key]["answer"][$a];
                                            $fpdi->SetXY($cords[$pageNo - 1][$key]["x"] + $spacingX + $marginLeft, $cords[$pageNo - 1][$key]["y"] + $marginTop);
                                            $fpdi->Write(0, $letter);
                                            $spacingX += $cords[$pageNo - 1][$key]["options"]["letterspacing"];
                                        }



                                        $write = true;
                                    }
                                } else {
                                    $marginLeft = 0;
                                    $marginTop = 0;
                                }


                                if (!$write) {

                                    //llenado de checkbox
                                    if (isset($cords[$pageNo - 1][$key]["conditions-check"])) {

                                        foreach ($cords[$pageNo - 1][$key]["conditions-check"] as $condition) {
                                            $answer = $cords[$pageNo - 1][$key]["answer"];
                                            echo $condition["answer"] . " == " . $answer . "<br>";
                                            if ($condition["answer"] == $answer) {
                                                $fpdi->SetXY($cords[$pageNo - 1][$key]["x"], $cords[$pageNo - 1][$key]["y"]);
                                                $fpdi->Write(0, "x");
                                            }
                                        }
                                    } else {

                                        $fpdi->SetXY($cords[$pageNo - 1][$key]["x"] + $marginLeft, $cords[$pageNo - 1][$key]["y"] + $marginTop);
                                        $fpdi->Write(0, $cords[$pageNo - 1][$key]["answer"]);
                                    }
                                } else {
                                    $write = false;
                                }
                            } else {
                                $marginLeft = 0;
                                $marginTop = 0;
                                if (isset($cords[$pageNo - 1][$key]["conditions-check"])) {

                                    foreach ($cords[$pageNo - 1][$key]["conditions-check"] as $condition) {
                                        $answer = $cords[$pageNo - 1][$key]["answer"];

                                        if ($condition["answer"] == $answer) {
                                            $x = $condition["x"];
                                            $y = $condition["y"];
                                            $fpdi->SetXY($x, $y);
                                            $fpdi->Write(0, "x");
                                        }
                                    }
                                } else {
                                    $fpdi->SetXY($cords[$pageNo - 1][$key]["x"] + $marginLeft, $cords[$pageNo - 1][$key]["y"] + $marginTop);
                                    $fpdi->Write(0, $cords[$pageNo - 1][$key]["answer"]);
                                }
                            }
                        }
                    }
                }
            }
            $application_folder = "applications/application_$id_application";

            $fpdi->Output('F', RoutesService::getupload($application_folder . "/$slug.pdf", "absolute"));
            return true;
        } catch (Exception $e) {
            // report the error
            error_log("Error filling pdf: " . $e->getMessage());
            throw $why;
        }
    }
}