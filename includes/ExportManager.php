<?php
/**
 * Export Manager
 * Handles data export in multiple formats with advanced features
 */

class ExportManager {
    private $db;
    private $tempDir;
    private $maxExportRows = 10000;
    private $chunkSize = 1000;
    
    public function __construct($database, $tempDir = null) {
        $this->db = $database;
        $this->tempDir = $tempDir ?: sys_get_temp_dir();
    }
    
    /**
     * Export data in specified format
     */
    public function exportData($data, $format, $options = []) {
        try {
            // Validate format
            $supportedFormats = ['csv', 'excel', 'pdf', 'json', 'xml'];
            if (!in_array($format, $supportedFormats)) {
                throw new Exception("Unsupported export format: {$format}");
            }
            
            // Default options
            $options = array_merge([
                'filename' => 'export_' . date('Y-m-d_H-i-s'),
                'include_headers' => true,
                'include_metadata' => true,
                'compress' => false,
                'password_protect' => false,
                'password' => null,
                'custom_headers' => [],
                'filters_applied' => [],
                'total_records' => count($data)
            ], $options);
            
            // Check data size limits
            if (count($data) > $this->maxExportRows) {
                throw new Exception("Export size exceeds maximum limit of {$this->maxExportRows} rows");
            }
            
            // Generate export based on format
            switch ($format) {
                case 'csv':
                    return $this->exportToCsv($data, $options);
                case 'excel':
                    return $this->exportToExcel($data, $options);
                case 'pdf':
                    return $this->exportToPdf($data, $options);
                case 'json':
                    return $this->exportToJson($data, $options);
                case 'xml':
                    return $this->exportToXml($data, $options);
                default:
                    throw new Exception("Export method not implemented for format: {$format}");
            }
            
        } catch (Exception $e) {
            error_log("Export Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Export to CSV format
     */
    private function exportToCsv($data, $options) {
        if (empty($data)) {
            throw new Exception('No data to export');
        }
        
        $filename = $options['filename'] . '.csv';
        $filepath = $this->tempDir . '/' . $filename;
        
        $file = fopen($filepath, 'w');
        
        // Add BOM for UTF-8 support in Excel
        fwrite($file, "\xEF\xBB\xBF");
        
        // Add metadata if requested
        if ($options['include_metadata']) {
            fputcsv($file, ['# Export Metadata']);
            fputcsv($file, ['# Generated:', date('Y-m-d H:i:s')]);
            fputcsv($file, ['# Total Records:', $options['total_records']]);
            if (!empty($options['filters_applied'])) {
                fputcsv($file, ['# Filters Applied:', json_encode($options['filters_applied'])]);
            }
            fputcsv($file, []); // Empty line
        }
        
        // Write headers
        if ($options['include_headers']) {
            $headers = !empty($options['custom_headers']) ? 
                      $options['custom_headers'] : 
                      array_keys($data[0]);
            fputcsv($file, $headers);
        }
        
        // Write data in chunks to handle large datasets
        $chunks = array_chunk($data, $this->chunkSize);
        foreach ($chunks as $chunk) {
            foreach ($chunk as $row) {
                // Clean data for CSV
                $cleanRow = [];
                foreach ($row as $key => $value) {
                    if (is_array($value) || is_object($value)) {
                        $cleanRow[$key] = json_encode($value);
                    } else {
                        $cleanRow[$key] = $this->cleanCsvValue($value);
                    }
                }
                fputcsv($file, $cleanRow);
            }
        }
        
        fclose($file);
        
        // Compress if requested
        if ($options['compress']) {
            $compressedPath = $this->compressFile($filepath, $filename);
            unlink($filepath);
            $filepath = $compressedPath;
            $filename = basename($compressedPath);
        }
        
        return [
            'success' => true,
            'filepath' => $filepath,
            'filename' => $filename,
            'mimetype' => $options['compress'] ? 'application/zip' : 'text/csv',
            'size' => filesize($filepath)
        ];
    }
    
    /**
     * Export to Excel format (basic implementation)
     */
    private function exportToExcel($data, $options) {
        // For a full Excel implementation, you would use PhpSpreadsheet
        // This is a simplified version that creates an Excel-compatible CSV
        
        $filename = $options['filename'] . '.xlsx';
        $filepath = $this->tempDir . '/' . $filename;
        
        // Create a simple Excel XML format
        $excel = $this->createExcelXml($data, $options);
        file_put_contents($filepath, $excel);
        
        return [
            'success' => true,
            'filepath' => $filepath,
            'filename' => $filename,
            'mimetype' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'size' => filesize($filepath)
        ];
    }
    
    /**
     * Export to PDF format
     */
    private function exportToPdf($data, $options) {
        $filename = $options['filename'] . '.pdf';
        $filepath = $this->tempDir . '/' . $filename;
        
        // Create HTML content for PDF conversion
        $html = $this->createPdfHtml($data, $options);
        
        // For a full PDF implementation, you would use libraries like TCPDF or mPDF
        // This creates an HTML file that can be converted to PDF
        $htmlFile = $this->tempDir . '/' . $options['filename'] . '.html';
        file_put_contents($htmlFile, $html);
        
        // Simple PDF creation (would need proper PDF library)
        $this->createSimplePdf($html, $filepath);
        
        // Clean up HTML file
        if (file_exists($htmlFile)) {
            unlink($htmlFile);
        }
        
        return [
            'success' => true,
            'filepath' => $filepath,
            'filename' => $filename,
            'mimetype' => 'application/pdf',
            'size' => filesize($filepath)
        ];
    }
    
    /**
     * Export to JSON format
     */
    private function exportToJson($data, $options) {
        $filename = $options['filename'] . '.json';
        $filepath = $this->tempDir . '/' . $filename;
        
        $exportData = [
            'metadata' => [
                'generated_at' => date('c'),
                'total_records' => $options['total_records'],
                'filters_applied' => $options['filters_applied'],
                'export_format' => 'json'
            ],
            'data' => $data
        ];
        
        if (!$options['include_metadata']) {
            $exportData = $data;
        }
        
        $json = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($filepath, $json);
        
        // Compress if requested
        if ($options['compress']) {
            $compressedPath = $this->compressFile($filepath, $filename);
            unlink($filepath);
            $filepath = $compressedPath;
            $filename = basename($compressedPath);
        }
        
        return [
            'success' => true,
            'filepath' => $filepath,
            'filename' => $filename,
            'mimetype' => $options['compress'] ? 'application/zip' : 'application/json',
            'size' => filesize($filepath)
        ];
    }
    
    /**
     * Export to XML format
     */
    private function exportToXml($data, $options) {
        $filename = $options['filename'] . '.xml';
        $filepath = $this->tempDir . '/' . $filename;
        
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        $root = $xml->createElement('export');
        $xml->appendChild($root);
        
        // Add metadata
        if ($options['include_metadata']) {
            $metadata = $xml->createElement('metadata');
            $metadata->appendChild($xml->createElement('generated_at', date('c')));
            $metadata->appendChild($xml->createElement('total_records', $options['total_records']));
            $metadata->appendChild($xml->createElement('export_format', 'xml'));
            $root->appendChild($metadata);
        }
        
        // Add data
        $dataElement = $xml->createElement('data');
        foreach ($data as $index => $row) {
            $recordElement = $xml->createElement('record');
            $recordElement->setAttribute('index', $index);
            
            foreach ($row as $key => $value) {
                $fieldElement = $xml->createElement($this->sanitizeXmlElementName($key));
                if (is_array($value) || is_object($value)) {
                    $fieldElement->appendChild($xml->createCDATASection(json_encode($value)));
                } else {
                    $fieldElement->appendChild($xml->createTextNode($this->cleanXmlValue($value)));
                }
                $recordElement->appendChild($fieldElement);
            }
            
            $dataElement->appendChild($recordElement);
        }
        $root->appendChild($dataElement);
        
        $xml->save($filepath);
        
        // Compress if requested
        if ($options['compress']) {
            $compressedPath = $this->compressFile($filepath, $filename);
            unlink($filepath);
            $filepath = $compressedPath;
            $filename = basename($compressedPath);
        }
        
        return [
            'success' => true,
            'filepath' => $filepath,
            'filename' => $filename,
            'mimetype' => $options['compress'] ? 'application/zip' : 'application/xml',
            'size' => filesize($filepath)
        ];
    }
    
    /**
     * Create Excel XML format
     */
    private function createExcelXml($data, $options) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
        $xml .= ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
        $xml .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";
        
        $xml .= '<Worksheet ss:Name="Export">' . "\n";
        $xml .= '<Table>' . "\n";
        
        // Headers
        if ($options['include_headers'] && !empty($data)) {
            $xml .= '<Row>' . "\n";
            foreach (array_keys($data[0]) as $header) {
                $xml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($header) . '</Data></Cell>' . "\n";
            }
            $xml .= '</Row>' . "\n";
        }
        
        // Data rows
        foreach ($data as $row) {
            $xml .= '<Row>' . "\n";
            foreach ($row as $value) {
                $type = is_numeric($value) ? 'Number' : 'String';
                $xml .= '<Cell><Data ss:Type="' . $type . '">' . htmlspecialchars($value) . '</Data></Cell>' . "\n";
            }
            $xml .= '</Row>' . "\n";
        }
        
        $xml .= '</Table>' . "\n";
        $xml .= '</Worksheet>' . "\n";
        $xml .= '</Workbook>';
        
        return $xml;
    }
    
    /**
     * Create HTML for PDF conversion
     */
    private function createPdfHtml($data, $options) {
        $html = '<!DOCTYPE html><html><head>';
        $html .= '<meta charset="UTF-8">';
        $html .= '<title>Export Report</title>';
        $html .= '<style>';
        $html .= 'body { font-family: Arial, sans-serif; margin: 20px; }';
        $html .= 'table { border-collapse: collapse; width: 100%; margin-top: 20px; }';
        $html .= 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }';
        $html .= 'th { background-color: #f2f2f2; font-weight: bold; }';
        $html .= '.metadata { margin-bottom: 20px; padding: 10px; background-color: #f9f9f9; }';
        $html .= '</style>';
        $html .= '</head><body>';
        
        // Metadata
        if ($options['include_metadata']) {
            $html .= '<div class="metadata">';
            $html .= '<h2>Export Report</h2>';
            $html .= '<p><strong>Generated:</strong> ' . date('Y-m-d H:i:s') . '</p>';
            $html .= '<p><strong>Total Records:</strong> ' . $options['total_records'] . '</p>';
            if (!empty($options['filters_applied'])) {
                $html .= '<p><strong>Filters Applied:</strong> ' . htmlspecialchars(json_encode($options['filters_applied'])) . '</p>';
            }
            $html .= '</div>';
        }
        
        // Data table
        if (!empty($data)) {
            $html .= '<table>';
            
            // Headers
            if ($options['include_headers']) {
                $html .= '<thead><tr>';
                foreach (array_keys($data[0]) as $header) {
                    $html .= '<th>' . htmlspecialchars($header) . '</th>';
                }
                $html .= '</tr></thead>';
            }
            
            // Data rows
            $html .= '<tbody>';
            foreach ($data as $row) {
                $html .= '<tr>';
                foreach ($row as $value) {
                    $html .= '<td>' . htmlspecialchars($value) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody>';
            
            $html .= '</table>';
        }
        
        $html .= '</body></html>';
        
        return $html;
    }
    
    /**
     * Create simple PDF (placeholder - would need proper PDF library)
     */
    private function createSimplePdf($html, $filepath) {
        // This is a placeholder - in a real implementation, you would use:
        // - TCPDF
        // - mPDF  
        // - DomPDF
        // - wkhtmltopdf
        
        // For now, just save as HTML with PDF extension
        file_put_contents($filepath, $html);
    }
    
    /**
     * Compress file using ZIP
     */
    private function compressFile($filepath, $originalFilename) {
        $zipFilename = pathinfo($originalFilename, PATHINFO_FILENAME) . '.zip';
        $zipPath = $this->tempDir . '/' . $zipFilename;
        
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($filepath, $originalFilename);
            $zip->close();
            return $zipPath;
        } else {
            throw new Exception('Failed to create ZIP file');
        }
    }
    
    /**
     * Clean CSV value
     */
    private function cleanCsvValue($value) {
        if ($value === null) {
            return '';
        }
        
        // Remove or replace problematic characters
        $value = str_replace(["\r\n", "\r", "\n"], ' ', $value);
        $value = trim($value);
        
        return $value;
    }
    
    /**
     * Clean XML value
     */
    private function cleanXmlValue($value) {
        if ($value === null) {
            return '';
        }
        
        // Remove XML-invalid characters
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
        
        return $value;
    }
    
    /**
     * Sanitize XML element name
     */
    private function sanitizeXmlElementName($name) {
        // Replace invalid characters with underscores
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
        
        // Ensure it starts with a letter or underscore
        if (!preg_match('/^[a-zA-Z_]/', $name)) {
            $name = '_' . $name;
        }
        
        return $name;
    }
    
    /**
     * Get export statistics
     */
    public function getExportStats() {
        return [
            'max_export_rows' => $this->maxExportRows,
            'chunk_size' => $this->chunkSize,
            'temp_directory' => $this->tempDir,
            'supported_formats' => ['csv', 'excel', 'pdf', 'json', 'xml'],
            'temp_files_count' => count(glob($this->tempDir . '/export_*'))
        ];
    }
    
    /**
     * Clean up old temporary files
     */
    public function cleanupTempFiles($maxAge = 3600) {
        $files = glob($this->tempDir . '/export_*');
        $cleaned = 0;
        
        foreach ($files as $file) {
            if (is_file($file) && (time() - filemtime($file)) > $maxAge) {
                if (unlink($file)) {
                    $cleaned++;
                }
            }
        }
        
        return $cleaned;
    }
}
?>