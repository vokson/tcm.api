<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MergePdfController extends Controller
{
    public function download(Request $request)
    {

        $output = [];
        $result = null;

//        exec('cd ' . storage_path("app/pdf_merge_storage"), $output, $result);
//        $output[] = 'RETURN ' . $result;
//
//        exec('pwd', $output, $result);
//        $output[] = 'RETURN ' . $result;

        exec('cd ' . storage_path("app/pdf_merge_storage").'; pdftk 1.pdf 2.pdf cat output MERGED.pdf 2>&1', $output, $result);
        $output[] = 'RETURN ' . $result;

        file_put_contents(storage_path("app/pdf_merge_storage/log.txt"),print_r( $output, true));

        $headers = array(
            'Content-Type' => 'application/octet-stream',
            'Access-Control-Expose-Headers' => 'Content-Filename',
            'Content-Filename' => 'MERGED.pdf'
        );

        return response()->download(storage_path("app/pdf_merge_storage/MERGED.pdf"), "", $headers);
    }
}
