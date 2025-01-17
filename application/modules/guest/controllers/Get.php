<?php

if ( ! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * InvoicePlane
 *
 * @author      InvoicePlane Developers & Contributors
 * @copyright   Copyright (c) 2012 - 2018 InvoicePlane.com
 * @license     https://invoiceplane.com/license.txt
 * @link        https://invoiceplane.com
 */

/**
 * Class Guest.
 */
final class Get extends Base_Controller
{
    public function attachment($filename): void
    {
        $path = UPLOADS_CFILES_FOLDER;
        $filePath = $path . $filename;

        if ( ! str_starts_with(realpath($filePath), $path)) {
            header('Status: 403 Forbidden');
            echo '<h1>Forbidden</h1>';
            exit;
        }

        $filePath = realpath($filePath);

        if (file_exists($filePath)) {
            $pathParts = pathinfo($filePath);
            $fileSize = filesize($filePath);

            header('Expires: -1');
            header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0');
            header("Content-Disposition: attachment; filename=\"{$filename}\"");
            header('Content-Type: application/octet-stream');
            header('Content-Length: ' . $fileSize);

            echo file_get_contents($filePath);
            exit;
        }

        show_404();
        exit;
    }
}
