<?php

class DownloadService
{



    public static function download_pdf($src)
    {
        $src = $src . ".pdf";
        $url = RoutesService::getupload($src, "absolute");
        error_log("Download: " . $url);


        if (file_exists($url)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($url) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($url));
            readfile($url);
            exit;
        } else {
            error_log("Donwload Failed: File not found");
            echo "File not found";
        }
    }
}