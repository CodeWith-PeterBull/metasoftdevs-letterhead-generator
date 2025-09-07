<?php

/**
 * PDF Letterhead Service
 * 
 * Handles PDF document generation for letterheads using DomPDF library.
 * Provides HTML-based template rendering with embedded CSS styling,
 * optimized for professional print output and web distribution.
 * 
 * Features:
 * - HTML/CSS based template system with print optimizations
 * - Base64 logo embedding for reliable image display
 * - Professional typography and layout controls
 * - Summernote rich text content processing
 * - Multi-template support with consistent styling
 * - Paper size management and custom dimensions
 * 
 * @package     App\Services
 * @category    Document Generation
 * @author      Metasoftdevs <info@metasoftdevs.com>
 * @copyright   2025 Metasoft Developers
 * @license     MIT License
 * @version     1.0.0
 * @link        https://www.metasoftdevs.com
 * @since       File available since Release 1.0.0
 * 
 * @see         https://github.com/barryvdh/laravel-dompdf DomPDF Laravel Package
 * @see         \Barryvdh\DomPDF\Facade\Pdf PDF Facade
 * @see         \Illuminate\Http\Response Laravel Response
 */

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PdfLetterheadService
{
    public static function generateLetterhead(string $template, array $data): Response
    {
        // Generate HTML content based on template
        $html = self::generateHtmlTemplate($template, $data);

        // Create PDF with professional settings
        $pdf = Pdf::loadHtml($html)
            ->setPaper(self::getPaperSize($data['paper_size'] ?? 'us_letter'), 'portrait')
            ->setOptions([
                'defaultFont' => 'Times-Roman',
                'isRemoteEnabled' => true,
                'isPhpEnabled' => false,
                'isHtml5ParserEnabled' => true,
                'isFontSubsettingEnabled' => true,
                'defaultMediaType' => 'print',
                'dpi' => 150,
                'fontHeightRatio' => 1.1,
                'enable_css_float' => true
            ]);

        // Generate filename
        $templateName = str_replace('_', '-', $template);
        $filename = 'letterhead_' . $templateName . '_' .
            str_replace(' ', '_', strtolower($data['company_name'])) . '_' .
            now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    private static function getPaperSize(string $paperSize): array|string
    {
        switch ($paperSize) {
            case 'us_letter':
            case 'letter':
                return 'letter';
            case 'a4':
                return 'a4';
            case 'legal':
                return 'legal';
            case 'custom':
                // For custom, we'll use letter as default and handle custom sizing in HTML/CSS
                return 'letter';
            default:
                return 'letter';
        }
    }

    private static function generateHtmlTemplate(string $template, array $data): string
    {
        switch ($template) {
            case 'modern_green':
                return self::createModernGreenTemplate($data);
            case 'corporate_blue':
                return self::createCorporateBlueTemplate($data);
            case 'elegant_gray':
                return self::createElegantGrayTemplate($data);
            case 'classic':
            default:
                return self::createClassicTemplate($data);
        }
    }

    private static function createClassicTemplate(array $data): string
    {
        $logoHtml = '';
        if (!empty($data['logo_path']) && file_exists($data['logo_path'])) {
            $logoBase64 = base64_encode(file_get_contents($data['logo_path']));
            $logoMime = mime_content_type($data['logo_path']);
            $logoHtml = '<img src="data:' . $logoMime . ';base64,' . $logoBase64 . '" alt="Logo" class="logo">';
        } else {
            $logoHtml = '<div class="company-name-logo">' . htmlspecialchars($data['company_name']) . '</div>';
        }

        $addressLines = explode("\n", $data['address']);
        $addressHtml = '';
        foreach ($addressLines as $line) {
            $addressHtml .= '<div>' . htmlspecialchars(trim($line)) . '</div>';
        }

        $contactHtml = '';
        if (!empty($data['phone'])) {
            $contactHtml .= '<div>Tel: ' . htmlspecialchars($data['phone']) . '</div>';
        }
        if (!empty($data['email'])) {
            $contactHtml .= '<div>Email: ' . htmlspecialchars($data['email']) . '</div>';
        }
        if (!empty($data['website'])) {
            $contactHtml .= '<div>Web: ' . htmlspecialchars($data['website']) . '</div>';
        }

        $letterContent = self::processLetterContent($data);

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Letterhead - ' . htmlspecialchars($data['company_name']) . '</title>
            <style>
                @page {
                    margin: 5% 7%;
                    size: letter;
                }
                
                body {
                    margin: 0;
                    padding: 0;
                    font-family: "Times New Roman", Times, serif;
                    font-size: 11pt;
                    line-height: 1.4;
                    color: #000;
                    background: #fff;
                }
                
                .header {
                    display: table;
                    width: 100%;
                    border-bottom: 1pt solid #000;
                    padding-bottom: 10pt;
                    margin-bottom: 20pt;
                    height: 1.5in;
                }
                
                .logo-section {
                    display: table-cell;
                    width: 2.2in;
                    vertical-align: middle;
                    padding-right: 20pt;
                }
                
                .logo {
                    max-width: 2in;
                    max-height: 2in;
                    width: auto;
                    height: auto;
                }
                
                .company-name-logo {
                    font-size: 16pt;
                    font-weight: bold;
                    color: #000;
                }
                
                .details-section {
                    display: table-cell;
                    vertical-align: middle;
                    text-align: right;
                }
                
                .company-name-header {
                    font-size: 18pt;
                    font-weight: bold;
                    margin-bottom: 8pt;
                    color: #000;
                }
                
                .address, .contact {
                    font-size: 11pt;
                    line-height: 1.3;
                    color: #000;
                    margin-bottom: 6pt;
                }
                
                .content {
                    margin-top: 20pt;
                    font-size: 11pt;
                    line-height: 1.6;
                    color: #000;
                }
                
                .date {
                    margin-bottom: 20pt;
                    font-size: 11pt;
                }
                
                .recipient {
                    margin-bottom: 20pt;
                    font-size: 11pt;
                }
                
                .recipient-name {
                    font-weight: bold;
                    margin-bottom: 6pt;
                }
                
                p {
                    margin: 0 0 12pt 0;
                }
                
                /* Print optimizations */
                @media print {
                    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    .header { border-bottom: 1pt solid #000 !important; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="logo-section">
                    ' . $logoHtml . '
                </div>
                <div class="details-section">
                    ' . (!empty($data['logo_path']) && file_exists($data['logo_path']) ?
            '<div class="company-name-header">' . htmlspecialchars($data['company_name']) . '</div>' : '') . '
                    <div class="address">
                        ' . $addressHtml . '
                    </div>
                    <div class="contact">
                        ' . $contactHtml . '
                    </div>
                </div>
            </div>
            
            <div class="content">
                ' . $letterContent . '
            </div>
        </body>
        </html>';
    }

    private static function createModernGreenTemplate(array $data): string
    {
        // Similar structure but with green accents
        $logoHtml = '';
        if (!empty($data['logo_path']) && file_exists($data['logo_path'])) {
            $logoBase64 = base64_encode(file_get_contents($data['logo_path']));
            $logoMime = mime_content_type($data['logo_path']);
            $logoHtml = '<img src="data:' . $logoMime . ';base64,' . $logoBase64 . '" alt="Logo" class="logo">';
        } else {
            $logoHtml = '<div class="company-name-logo">' . htmlspecialchars($data['company_name']) . '</div>';
        }

        $addressLines = explode("\n", $data['address']);
        $addressHtml = '';
        foreach ($addressLines as $line) {
            $addressHtml .= '<div>' . htmlspecialchars(trim($line)) . '</div>';
        }

        $contactHtml = '';
        if (!empty($data['phone'])) {
            $contactHtml .= '<div>' . htmlspecialchars($data['phone']) . '</div>';
        }
        if (!empty($data['email'])) {
            $contactHtml .= '<div>' . htmlspecialchars($data['email']) . '</div>';
        }
        if (!empty($data['website'])) {
            $contactHtml .= '<div>' . htmlspecialchars($data['website']) . '</div>';
        }

        $letterContent = self::processLetterContent($data);

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Letterhead - ' . htmlspecialchars($data['company_name']) . '</title>
            <style>
                @page {
                    margin: 5% 7%;
                    size: letter;
                }
                
                body {
                    margin: 0;
                    padding: 0;
                    font-family: Arial, sans-serif;
                    font-size: 11pt;
                    line-height: 1.4;
                    color: #000;
                    background: #fff;
                }
                
                .header {
                    display: table;
                    width: 100%;
                    border-bottom: 1pt solid #2E7D32;
                    padding-bottom: 10pt;
                    margin-bottom: 20pt;
                    height: 1.5in;
                }
                
                .logo-section {
                    display: table-cell;
                    width: 2.2in;
                    vertical-align: middle;
                    padding-right: 20pt;
                }
                
                .logo {
                    max-width: 2in;
                    max-height: 2in;
                    width: auto;
                    height: auto;
                }
                
                .company-name-logo {
                    font-size: 16pt;
                    font-weight: bold;
                    color: #2E7D32;
                }
                
                .details-section {
                    display: table-cell;
                    vertical-align: middle;
                    text-align: right;
                }
                
                .company-name-header {
                    font-size: 18pt;
                    font-weight: bold;
                    margin-bottom: 8pt;
                    color: #2E7D32;
                }
                
                .address, .contact {
                    font-size: 11pt;
                    line-height: 1.3;
                    color: #000;
                    margin-bottom: 6pt;
                }
                
                .content {
                    margin-top: 20pt;
                    font-size: 11pt;
                    line-height: 1.6;
                    color: #000;
                }
                
                .date {
                    margin-bottom: 20pt;
                    font-size: 11pt;
                }
                
                .recipient {
                    margin-bottom: 20pt;
                    font-size: 11pt;
                }
                
                .recipient-name {
                    font-weight: bold;
                    margin-bottom: 6pt;
                    color: #2E7D32;
                }
                
                p {
                    margin: 0 0 12pt 0;
                }
                
                @media print {
                    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    .header { border-bottom: 1pt solid #2E7D32 !important; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="logo-section">
                    ' . $logoHtml . '
                </div>
                <div class="details-section">
                    ' . (!empty($data['logo_path']) && file_exists($data['logo_path']) ?
            '<div class="company-name-header">' . htmlspecialchars($data['company_name']) . '</div>' : '') . '
                    <div class="address">
                        ' . $addressHtml . '
                    </div>
                    <div class="contact">
                        ' . $contactHtml . '
                    </div>
                </div>
            </div>
            
            <div class="content">
                ' . $letterContent . '
            </div>
        </body>
        </html>';
    }

    private static function createCorporateBlueTemplate(array $data): string
    {
        // Corporate Blue template - similar to Modern Green but with blue colors
        $logoHtml = '';
        if (!empty($data['logo_path']) && file_exists($data['logo_path'])) {
            $logoBase64 = base64_encode(file_get_contents($data['logo_path']));
            $logoMime = mime_content_type($data['logo_path']);
            $logoHtml = '<img src="data:' . $logoMime . ';base64,' . $logoBase64 . '" alt="Logo" class="logo">';
        } else {
            $logoHtml = '<div class="company-name-logo">' . htmlspecialchars($data['company_name']) . '</div>';
        }

        $addressLines = explode("\n", $data['address']);
        $addressHtml = '';
        foreach ($addressLines as $line) {
            $addressHtml .= '<div>' . htmlspecialchars(trim($line)) . '</div>';
        }

        $contactHtml = '';
        if (!empty($data['phone'])) {
            $contactHtml .= '<div>' . htmlspecialchars($data['phone']) . '</div>';
        }
        if (!empty($data['email'])) {
            $contactHtml .= '<div>' . htmlspecialchars($data['email']) . '</div>';
        }
        if (!empty($data['website'])) {
            $contactHtml .= '<div>' . htmlspecialchars($data['website']) . '</div>';
        }

        $letterContent = self::processLetterContent($data);

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Letterhead - ' . htmlspecialchars($data['company_name']) . '</title>
            <style>
                @page {
                    margin: 5% 7%;
                    size: letter;
                }
                
                body {
                    margin: 0;
                    padding: 0;
                    font-family: Arial, sans-serif;
                    font-size: 11pt;
                    line-height: 1.4;
                    color: #000;
                    background: #fff;
                }
                
                .header {
                    display: table;
                    width: 100%;
                    border-bottom: 1pt solid #1565C0;
                    padding-bottom: 10pt;
                    margin-bottom: 20pt;
                    height: 1.5in;
                }
                
                .logo-section {
                    display: table-cell;
                    width: 2.2in;
                    vertical-align: middle;
                    padding-right: 20pt;
                }
                
                .logo {
                    max-width: 2in;
                    max-height: 2in;
                    width: auto;
                    height: auto;
                }
                
                .company-name-logo {
                    font-size: 16pt;
                    font-weight: bold;
                    color: #1565C0;
                }
                
                .details-section {
                    display: table-cell;
                    vertical-align: middle;
                    text-align: right;
                }
                
                .company-name-header {
                    font-size: 18pt;
                    font-weight: bold;
                    margin-bottom: 8pt;
                    color: #1565C0;
                }
                
                .address, .contact {
                    font-size: 11pt;
                    line-height: 1.3;
                    color: #000;
                    margin-bottom: 6pt;
                }
                
                .content {
                    margin-top: 20pt;
                    font-size: 11pt;
                    line-height: 1.6;
                    color: #000;
                }
                
                .date {
                    margin-bottom: 20pt;
                    font-size: 11pt;
                }
                
                .recipient {
                    margin-bottom: 20pt;
                    font-size: 11pt;
                }
                
                .recipient-name {
                    font-weight: bold;
                    margin-bottom: 6pt;
                    color: #1565C0;
                }
                
                p {
                    margin: 0 0 12pt 0;
                }
                
                @media print {
                    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    .header { border-bottom: 1pt solid #1565C0 !important; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="logo-section">
                    ' . $logoHtml . '
                </div>
                <div class="details-section">
                    ' . (!empty($data['logo_path']) && file_exists($data['logo_path']) ?
            '<div class="company-name-header">' . htmlspecialchars($data['company_name']) . '</div>' : '') . '
                    <div class="address">
                        ' . $addressHtml . '
                    </div>
                    <div class="contact">
                        ' . $contactHtml . '
                    </div>
                </div>
            </div>
            
            <div class="content">
                ' . $letterContent . '
            </div>
        </body>
        </html>';
    }

    private static function createElegantGrayTemplate(array $data): string
    {
        // Elegant Gray template
        $logoHtml = '';
        if (!empty($data['logo_path']) && file_exists($data['logo_path'])) {
            $logoBase64 = base64_encode(file_get_contents($data['logo_path']));
            $logoMime = mime_content_type($data['logo_path']);
            $logoHtml = '<img src="data:' . $logoMime . ';base64,' . $logoBase64 . '" alt="Logo" class="logo">';
        } else {
            $logoHtml = '<div class="company-name-logo">' . htmlspecialchars($data['company_name']) . '</div>';
        }

        $addressLines = explode("\n", $data['address']);
        $addressHtml = '';
        foreach ($addressLines as $line) {
            $addressHtml .= '<div>' . htmlspecialchars(trim($line)) . '</div>';
        }

        $contactHtml = '';
        if (!empty($data['phone'])) {
            $contactHtml .= '<div>' . htmlspecialchars($data['phone']) . '</div>';
        }
        if (!empty($data['email'])) {
            $contactHtml .= '<div>' . htmlspecialchars($data['email']) . '</div>';
        }
        if (!empty($data['website'])) {
            $contactHtml .= '<div>' . htmlspecialchars($data['website']) . '</div>';
        }

        $letterContent = self::processLetterContent($data);

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Letterhead - ' . htmlspecialchars($data['company_name']) . '</title>
            <style>
                @page {
                    margin: 5% 7%;
                    size: letter;
                }
                
                body {
                    margin: 0;
                    padding: 0;
                    font-family: "Times New Roman", Times, serif;
                    font-size: 11pt;
                    line-height: 1.4;
                    color: #000;
                    background: #fff;
                }
                
                .header {
                    display: table;
                    width: 100%;
                    border-bottom: 1pt solid #666666;
                    padding-bottom: 10pt;
                    margin-bottom: 20pt;
                    height: 1.5in;
                }
                
                .logo-section {
                    display: table-cell;
                    width: 2.2in;
                    vertical-align: middle;
                    padding-right: 20pt;
                }
                
                .logo {
                    max-width: 2in;
                    max-height: 2in;
                    width: auto;
                    height: auto;
                }
                
                .company-name-logo {
                    font-size: 16pt;
                    font-weight: bold;
                    color: #2C2C2C;
                }
                
                .details-section {
                    display: table-cell;
                    vertical-align: middle;
                    text-align: right;
                }
                
                .company-name-header {
                    font-size: 18pt;
                    font-weight: bold;
                    margin-bottom: 8pt;
                    color: #2C2C2C;
                }
                
                .address, .contact {
                    font-size: 11pt;
                    line-height: 1.3;
                    color: #000;
                    margin-bottom: 6pt;
                }
                
                .content {
                    margin-top: 20pt;
                    font-size: 11pt;
                    line-height: 1.6;
                    color: #000;
                }
                
                .date {
                    margin-bottom: 20pt;
                    font-size: 11pt;
                }
                
                .recipient {
                    margin-bottom: 20pt;
                    font-size: 11pt;
                }
                
                .recipient-name {
                    font-weight: bold;
                    margin-bottom: 6pt;
                    color: #2C2C2C;
                }
                
                p {
                    margin: 0 0 12pt 0;
                }
                
                @media print {
                    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    .header { border-bottom: 1pt solid #666666 !important; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="logo-section">
                    ' . $logoHtml . '
                </div>
                <div class="details-section">
                    ' . (!empty($data['logo_path']) && file_exists($data['logo_path']) ?
            '<div class="company-name-header">' . htmlspecialchars($data['company_name']) . '</div>' : '') . '
                    <div class="address">
                        ' . $addressHtml . '
                    </div>
                    <div class="contact">
                        ' . $contactHtml . '
                    </div>
                </div>
            </div>
            
            <div class="content">
                ' . $letterContent . '
            </div>
        </body>
        </html>';
    }

    private static function processLetterContent(array $data): string
    {
        $content = $data['letter_content'] ?? '';

        // Build the letter content with date, recipient, and main content
        $html = '<div class="date">Date: ' . now()->format('F d, Y') . '</div>';

        // Add recipient information if provided
        if (!empty($data['recipient_name']) || !empty($data['recipient_address'])) {
            $html .= '<div class="recipient">';
            $html .= '<div style="font-weight: bold; margin-bottom: 6pt;">To:</div>';

            if (!empty($data['recipient_name'])) {
                $html .= '<div class="recipient-name">' . htmlspecialchars($data['recipient_name']) . '</div>';
                if (!empty($data['recipient_title'])) {
                    $html .= '<div style="margin-bottom: 6pt;">' . htmlspecialchars($data['recipient_title']) . '</div>';
                }
            }

            if (!empty($data['recipient_address'])) {
                $recipientLines = explode("\n", $data['recipient_address']);
                foreach ($recipientLines as $line) {
                    $html .= '<div>' . htmlspecialchars(trim($line)) . '</div>';
                }
            }
            $html .= '</div>';
        }

        $html .= '<div class="content">';

        if (empty($content)) {
            // Default placeholder content
            $html .= '
                <p>Dear [Recipient],</p>
                <p>[Your letter content goes here]</p>
                <p>Sincerely,</p>
                <br><br>
                <p>_______________________<br>
                [Your Name]<br>
                [Your Title]</p>
            ';
        } else {
            // Process rich text content from Summernote - preserve HTML formatting
            $processedContent = self::processSummernoteContent($content);
            $html .= $processedContent;
        }

        $html .= '</div>';

        return $html;
    }

    private static function processSummernoteContent(string $content): string
    {
        // Clean up and optimize Summernote HTML for PDF rendering

        // Fix common Summernote formatting issues
        $content = str_replace(['<div><br></div>', '<div><br/></div>', '<div><br /></div>'], '<p>&nbsp;</p>', $content);

        // Convert div-based structure to paragraph-based for better PDF rendering
        $content = preg_replace('/<div([^>]*)>(.*?)<\/div>/is', '<p$1>$2</p>', $content);

        // Clean up multiple consecutive <br> tags
        $content = preg_replace('/(<br[^>]*>\s*){3,}/i', '<br><br>', $content);

        // Ensure proper paragraph spacing
        $content = str_replace('</p><p>', '</p><p style="margin-top: 12pt;">', $content);

        // Handle text formatting - ensure styles are preserved
        // Bold, italic, underline should already be in HTML format from Summernote

        // Handle lists - ensure proper styling
        $content = str_replace('<ul>', '<ul style="margin: 12pt 0; padding-left: 20pt; list-style-type: disc;">', $content);
        $content = str_replace('<ol>', '<ol style="margin: 12pt 0; padding-left: 20pt; list-style-type: decimal;">', $content);
        $content = str_replace('<li>', '<li style="margin-bottom: 6pt; display: list-item;">', $content);

        // Handle tables - ensure proper styling and borders
        $content = str_replace('<table>', '<table style="width: 100%; border-collapse: collapse; margin: 12pt 0; border: 1px solid #333;">', $content);
        $content = str_replace('<th>', '<th style="border-right: 1px solid #333; border-bottom: 1px solid #333; padding: 8pt; background-color: #f5f5f5; font-weight: bold; text-align: left;">', $content);
        $content = str_replace('<td>', '<td style="border-right: 1px solid #333; border-bottom: 1px solid #333; padding: 8pt; text-align: left; vertical-align: top;">', $content);
        $content = str_replace('<tr>', '<tr>', $content);

        // Handle font sizes - convert Summernote font sizes to print-friendly sizes
        $content = preg_replace('/font-size:\s*(\d+)px/i', 'font-size: $1pt', $content);

        // Handle alignment
        $content = str_replace('text-align: left', 'text-align: left', $content);
        $content = str_replace('text-align: center', 'text-align: center', $content);
        $content = str_replace('text-align: right', 'text-align: right', $content);
        $content = str_replace('text-align: justify', 'text-align: justify', $content);

        // Clean up extra whitespace but preserve intentional formatting
        $content = preg_replace('/\s*\n\s*/', ' ', $content);

        // Ensure content starts with a paragraph if it doesn't already
        if (!preg_match('/^\s*<[^>]+>/', $content)) {
            $content = '<p>' . $content . '</p>';
        }

        return $content;
    }
}
