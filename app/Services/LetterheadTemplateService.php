<?php

/**
 * Letterhead Template Service
 * 
 * Handles Microsoft Word document generation for letterheads using PhpWord library.
 * Provides comprehensive template system with four distinct design themes,
 * dynamic paper sizing, logo integration, and rich text content processing.
 * 
 * Features:
 * - Four professional template designs (Classic, Modern Green, Corporate Blue, Elegant Gray)
 * - Dynamic paper size configuration with custom dimensions support
 * - Intelligent logo processing with automatic fallbacks
 * - HTML-to-Word content conversion with formatting preservation
 * - Responsive header layouts with company branding
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
 * @see         https://phpword.readthedocs.io/ PhpWord Documentation
 * @see         \PhpOffice\PhpWord\PhpWord Main PhpWord class
 * @see         \PhpOffice\PhpWord\Shared\Converter Unit conversion utilities
 */

namespace App\Services;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Paper;
use Exception;

class LetterheadTemplateService
{
    public static function getPaperSizes(): array
    {
        return [
            'letter' => [
                'name' => 'US Letter (8.5" × 11")',
                'width' => Converter::inchToTwip(8.5),
                'height' => Converter::inchToTwip(11)
            ],
            'a4' => [
                'name' => 'A4 (210mm × 297mm)',
                'width' => Converter::cmToTwip(21),
                'height' => Converter::cmToTwip(29.7)
            ],
            'legal' => [
                'name' => 'US Legal (8.5" × 14")',
                'width' => Converter::inchToTwip(8.5),
                'height' => Converter::inchToTwip(14)
            ],
            'custom' => [
                'name' => 'Custom Size',
                'width' => null,
                'height' => null
            ]
        ];
    }

    public static function getAvailableTemplates(): array
    {
        return [
            'classic' => [
                'name' => 'Classic Business',
                'description' => 'Traditional formal business letterhead',
                'preview' => 'classic-preview.png'
            ],
            'modern_green' => [
                'name' => 'Modern Green Minimalist',
                'description' => 'Clean modern design with green accents',
                'preview' => 'modern-green-preview.png'
            ],
            'corporate_blue' => [
                'name' => 'Corporate Blue',
                'description' => 'Professional blue-themed letterhead',
                'preview' => 'corporate-blue-preview.png'
            ],
            'elegant_gray' => [
                'name' => 'Elegant Gray',
                'description' => 'Sophisticated gray and black design',
                'preview' => 'elegant-gray-preview.png'
            ]
        ];
    }

    public static function generateLetterhead(string $template, array $data): PhpWord
    {
        $phpWord = new PhpWord();
        
        // Set document properties
        $properties = $phpWord->getDocInfo();
        $properties->setCreator('Letterhead Generator Beta');
        $properties->setCompany($data['company_name']);
        $properties->setTitle($data['company_name'] . ' Letterhead');
        
        // Configure paper size
        $paperSize = $data['paper_size'] ?? 'letter';
        $paperSizes = self::getPaperSizes();
        
        if (isset($paperSizes[$paperSize]) && $paperSize !== 'custom') {
            $phpWord->getSettings()->setThemeFontLang(new \PhpOffice\PhpWord\Style\Language('en-US'));
        }
        
        switch ($template) {
            case 'modern_green':
                return self::createModernGreenTemplate($phpWord, $data);
            case 'corporate_blue':
                return self::createCorporateBlueTemplate($phpWord, $data);
            case 'elegant_gray':
                return self::createElegantGrayTemplate($phpWord, $data);
            case 'classic':
            default:
                return self::createClassicTemplate($phpWord, $data);
        }
    }

    private static function createModernGreenTemplate(PhpWord $phpWord, array $data): PhpWord
    {
        // Get paper size configuration
        $paperSize = $data['paper_size'] ?? 'letter';
        $paperSizes = self::getPaperSizes();
        
        // Beta version: Reduced margins and improved layout
        $sectionOptions = [
            'marginLeft' => Converter::inchToTwip(0.5),
            'marginRight' => Converter::inchToTwip(0.5), 
            'marginTop' => Converter::inchToTwip(0.3),
            'marginBottom' => Converter::inchToTwip(0.5),
        ];
        
        // Set paper size if specified
        if (isset($paperSizes[$paperSize]) && $paperSize !== 'custom') {
            if ($paperSizes[$paperSize]['width'] && $paperSizes[$paperSize]['height']) {
                $sectionOptions['pageSize'] = [
                    'width' => $paperSizes[$paperSize]['width'],
                    'height' => $paperSizes[$paperSize]['height']
                ];
            }
        } elseif ($paperSize === 'custom' && !empty($data['custom_width']) && !empty($data['custom_height'])) {
            $sectionOptions['pageSize'] = [
                'width' => Converter::inchToTwip((float) $data['custom_width']),
                'height' => Converter::inchToTwip((float) $data['custom_height'])
            ];
        }
        
        $section = $phpWord->addSection($sectionOptions);

        // Beta version: Header occupies ~1/5 of paper (approximately 2.2 inches for US Letter)
        $paperHeight = ($paperSize === 'letter') ? 11 : (($paperSize === 'a4') ? 11.7 : 11);
        $headerHeight = $paperHeight / 5; // 1/5 of paper height
        
        // Clean header: Logo left, contacts right, no backgrounds
        $headerTable = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 0,
            'width' => 100 * 50,
            'unit' => 'pct'
        ]);

        $headerRow = $headerTable->addRow(Converter::inchToTwip($headerHeight));
        
        // Logo cell (left side) - clean design
        $logoCell = $headerRow->addCell(3000, [
            'valign' => 'center'
        ]);
        
        if (!empty($data['logo_path'])) {
            try {
                // Clean logo sizing
                $logoSize = min(($headerHeight * 0.8) * 72, 100);
                $logoCell->addImage($data['logo_path'], [
                    'width' => $logoSize,
                    'height' => $logoSize,
                    'alignment' => Jc::LEFT
                ]);
            } catch (Exception $e) {
                // Fallback to company name if logo fails
                $logoCell->addText($data['company_name'], [
                    'name' => 'Arial',
                    'size' => 18,
                    'bold' => true,
                    'color' => '2E7D32'
                ], ['alignment' => Jc::LEFT]);
            }
        } else {
            // Show company name only if no logo
            $logoCell->addText($data['company_name'], [
                'name' => 'Arial',
                'size' => 18,
                'bold' => true,
                'color' => '2E7D32'
            ], ['alignment' => Jc::LEFT]);
        }

        // Contact info cell (right side) - clean design
        $contactCell = $headerRow->addCell(8000, [
            'valign' => 'center'
        ]);

        // Clean contact information styling
        $contactStyle = [
            'name' => 'Arial',
            'size' => 10,
            'color' => '2E7D32'
        ];
        
        if (!empty($data['phone'])) {
            $contactCell->addText($data['phone'], $contactStyle, ['alignment' => Jc::RIGHT]);
            $contactCell->addTextBreak(1);
        }
        
        if (!empty($data['email'])) {
            $contactCell->addText($data['email'], $contactStyle, ['alignment' => Jc::RIGHT]);
            $contactCell->addTextBreak(1);
        }
        
        if (!empty($data['website'])) {
            $contactCell->addText($data['website'], $contactStyle, ['alignment' => Jc::RIGHT]);
        }

        // Single clean underline separator
        $section->addTextBreak(0);
        $lineTable = $section->addTable(['borderSize' => 0]);
        $lineRow = $lineTable->addRow();
        $lineCell = $lineRow->addCell(null, [
            'borderTopSize' => 1,
            'borderTopColor' => '2E7D32'
        ]);
        $lineCell->addText('', [], ['spaceBefore' => 0, 'spaceAfter' => 0]);

        $section->addTextBreak(2);

        // Company address in lower section
        $addressLines = explode("\n", $data['address']);
        foreach ($addressLines as $line) {
            $section->addText(trim($line), [
                'name' => 'Arial',
                'size' => 10,
                'color' => '666666'
            ]);
        }

        $section->addTextBreak(2);

        self::addLetterContent($section, $data);

        return $phpWord;
    }

    private static function createCorporateBlueTemplate(PhpWord $phpWord, array $data): PhpWord
    {
        // Get paper size configuration
        $paperSize = $data['paper_size'] ?? 'letter';
        $paperSizes = self::getPaperSizes();
        
        // Beta version: Reduced margins and improved layout
        $sectionOptions = [
            'marginLeft' => Converter::inchToTwip(0.5),
            'marginRight' => Converter::inchToTwip(0.5),
            'marginTop' => Converter::inchToTwip(0.3),
            'marginBottom' => Converter::inchToTwip(0.5),
        ];
        
        // Set paper size if specified
        if (isset($paperSizes[$paperSize]) && $paperSize !== 'custom') {
            if ($paperSizes[$paperSize]['width'] && $paperSizes[$paperSize]['height']) {
                $sectionOptions['pageSize'] = [
                    'width' => $paperSizes[$paperSize]['width'],
                    'height' => $paperSizes[$paperSize]['height']
                ];
            }
        } elseif ($paperSize === 'custom' && !empty($data['custom_width']) && !empty($data['custom_height'])) {
            $sectionOptions['pageSize'] = [
                'width' => Converter::inchToTwip((float) $data['custom_width']),
                'height' => Converter::inchToTwip((float) $data['custom_height'])
            ];
        }
        
        $section = $phpWord->addSection($sectionOptions);

        // Beta version: Header occupies ~1/5 of paper (approximately 2.2 inches for US Letter)
        $paperHeight = ($paperSize === 'letter') ? 11 : (($paperSize === 'a4') ? 11.7 : 11);
        $headerHeight = $paperHeight / 5; // 1/5 of paper height
        
        // Clean header: Logo left, contacts right, no backgrounds
        $headerTable = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 0,
            'width' => 100 * 50,
            'unit' => 'pct'
        ]);
        $headerRow = $headerTable->addRow(Converter::inchToTwip($headerHeight));
        
        // Logo cell (left side) - clean design
        $logoCell = $headerRow->addCell(3000, [
            'valign' => 'center'
        ]);
        
        if (!empty($data['logo_path'])) {
            try {
                // Clean logo sizing
                $logoSize = min(($headerHeight * 0.8) * 72, 100);
                $logoCell->addImage($data['logo_path'], [
                    'width' => $logoSize,
                    'height' => $logoSize,
                    'alignment' => Jc::LEFT
                ]);
            } catch (Exception $e) {
                // Fallback to company name if logo fails
                $logoCell->addText($data['company_name'], [
                    'name' => 'Arial',
                    'size' => 18,
                    'bold' => true,
                    'color' => '1565C0'
                ], ['alignment' => Jc::LEFT]);
            }
        } else {
            // Show company name only if no logo
            $logoCell->addText($data['company_name'], [
                'name' => 'Arial',
                'size' => 18,
                'bold' => true,
                'color' => '1565C0'
            ], ['alignment' => Jc::LEFT]);
        }

        // Contact info cell (right side) - clean design
        $contactCell = $headerRow->addCell(8000, [
            'valign' => 'center'
        ]);

        // Clean contact information styling
        $contactStyle = [
            'name' => 'Arial',
            'size' => 10,
            'color' => '1565C0'
        ];
        
        if (!empty($data['phone'])) {
            $contactCell->addText($data['phone'], $contactStyle, ['alignment' => Jc::RIGHT]);
            $contactCell->addTextBreak(1);
        }
        
        if (!empty($data['email'])) {
            $contactCell->addText($data['email'], $contactStyle, ['alignment' => Jc::RIGHT]);
            $contactCell->addTextBreak(1);
        }
        
        if (!empty($data['website'])) {
            $contactCell->addText($data['website'], $contactStyle, ['alignment' => Jc::RIGHT]);
        }

        // Single clean underline separator
        $section->addTextBreak(0);
        $lineTable = $section->addTable(['borderSize' => 0]);
        $lineRow = $lineTable->addRow();
        $lineCell = $lineRow->addCell(null, [
            'borderTopSize' => 1,
            'borderTopColor' => '1565C0'
        ]);
        $lineCell->addText('', [], ['spaceBefore' => 0, 'spaceAfter' => 0]);

        $section->addTextBreak(2);

        // Company address in lower section
        $addressLines = explode("\n", $data['address']);
        foreach ($addressLines as $line) {
            $section->addText(trim($line), [
                'name' => 'Arial',
                'size' => 10,
                'color' => '666666'
            ]);
        }

        $section->addTextBreak(2);
        self::addLetterContent($section, $data);

        return $phpWord;
    }

    private static function createElegantGrayTemplate(PhpWord $phpWord, array $data): PhpWord
    {
        // Get paper size configuration
        $paperSize = $data['paper_size'] ?? 'letter';
        $paperSizes = self::getPaperSizes();
        
        // Beta version: Reduced margins and improved layout
        $sectionOptions = [
            'marginLeft' => Converter::inchToTwip(0.5),
            'marginRight' => Converter::inchToTwip(0.5),
            'marginTop' => Converter::inchToTwip(0.3),
            'marginBottom' => Converter::inchToTwip(0.5),
        ];
        
        // Set paper size if specified
        if (isset($paperSizes[$paperSize]) && $paperSize !== 'custom') {
            if ($paperSizes[$paperSize]['width'] && $paperSizes[$paperSize]['height']) {
                $sectionOptions['pageSize'] = [
                    'width' => $paperSizes[$paperSize]['width'],
                    'height' => $paperSizes[$paperSize]['height']
                ];
            }
        } elseif ($paperSize === 'custom' && !empty($data['custom_width']) && !empty($data['custom_height'])) {
            $sectionOptions['pageSize'] = [
                'width' => Converter::inchToTwip((float) $data['custom_width']),
                'height' => Converter::inchToTwip((float) $data['custom_height'])
            ];
        }
        
        $section = $phpWord->addSection($sectionOptions);

        // Beta version: Header occupies ~1/5 of paper (approximately 2.2 inches for US Letter)
        $paperHeight = ($paperSize === 'letter') ? 11 : (($paperSize === 'a4') ? 11.7 : 11);
        $headerHeight = $paperHeight / 5; // 1/5 of paper height
        
        // Clean header: Logo left, contacts right, no backgrounds
        $headerTable = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 0,
            'width' => 100 * 50,
            'unit' => 'pct'
        ]);
        $headerRow = $headerTable->addRow(Converter::inchToTwip($headerHeight));
        
        // Logo cell (left side) - clean design
        $logoCell = $headerRow->addCell(3000, [
            'valign' => 'center'
        ]);
        
        if (!empty($data['logo_path'])) {
            try {
                // Clean logo sizing
                $logoSize = min(($headerHeight * 0.8) * 72, 100);
                $logoCell->addImage($data['logo_path'], [
                    'width' => $logoSize,
                    'height' => $logoSize,
                    'alignment' => Jc::LEFT
                ]);
            } catch (Exception $e) {
                // Fallback to company name if logo fails
                $logoCell->addText($data['company_name'], [
                    'name' => 'Times New Roman',
                    'size' => 18,
                    'bold' => true,
                    'color' => '2C2C2C'
                ], ['alignment' => Jc::LEFT]);
            }
        } else {
            // Show company name only if no logo
            $logoCell->addText($data['company_name'], [
                'name' => 'Times New Roman',
                'size' => 18,
                'bold' => true,
                'color' => '2C2C2C'
            ], ['alignment' => Jc::LEFT]);
        }

        // Contact info cell (right side) - clean design
        $contactCell = $headerRow->addCell(8000, [
            'valign' => 'center'
        ]);

        // Clean contact information styling
        $contactStyle = [
            'name' => 'Times New Roman',
            'size' => 10,
            'color' => '2C2C2C'
        ];
        
        if (!empty($data['phone'])) {
            $contactCell->addText($data['phone'], $contactStyle, ['alignment' => Jc::RIGHT]);
            $contactCell->addTextBreak(1);
        }
        
        if (!empty($data['email'])) {
            $contactCell->addText($data['email'], $contactStyle, ['alignment' => Jc::RIGHT]);
            $contactCell->addTextBreak(1);
        }
        
        if (!empty($data['website'])) {
            $contactCell->addText($data['website'], $contactStyle, ['alignment' => Jc::RIGHT]);
        }

        // Single clean underline separator
        $section->addTextBreak(0);
        $lineTable = $section->addTable(['borderSize' => 0]);
        $lineRow = $lineTable->addRow();
        $lineCell = $lineRow->addCell(null, [
            'borderTopSize' => 1,
            'borderTopColor' => '666666'
        ]);
        $lineCell->addText('', [], ['spaceBefore' => 0, 'spaceAfter' => 0]);

        $section->addTextBreak(2);

        // Company address in lower section
        $addressLines = explode("\n", $data['address']);
        foreach ($addressLines as $line) {
            $section->addText(trim($line), [
                'name' => 'Times New Roman',
                'size' => 10,
                'color' => '666666'
            ]);
        }

        $section->addTextBreak(2);
        self::addLetterContent($section, $data);

        return $phpWord;
    }

    private static function createClassicTemplate(PhpWord $phpWord, array $data): PhpWord
    {
        // Get paper size configuration 
        $paperSize = $data['paper_size'] ?? 'us_letter';
        $paperSizes = self::getPaperSizes();
        
        // Professional 1.5 inch margins
        $sectionOptions = [
            'marginLeft' => Converter::inchToTwip(1.5),
            'marginRight' => Converter::inchToTwip(1.5),
            'marginTop' => Converter::inchToTwip(1.5),
            'marginBottom' => Converter::inchToTwip(1.5),
        ];
        
        // Set paper size if specified
        if (isset($paperSizes[$paperSize]) && $paperSize !== 'custom') {
            if ($paperSizes[$paperSize]['width'] && $paperSizes[$paperSize]['height']) {
                $sectionOptions['pageSize'] = [
                    'width' => $paperSizes[$paperSize]['width'],
                    'height' => $paperSizes[$paperSize]['height']
                ];
            }
        } elseif ($paperSize === 'custom' && !empty($data['custom_width']) && !empty($data['custom_height'])) {
            $sectionOptions['pageSize'] = [
                'width' => Converter::inchToTwip((float) $data['custom_width']),
                'height' => Converter::inchToTwip((float) $data['custom_height'])
            ];
        }
        
        $section = $phpWord->addSection($sectionOptions);

        // Calculate available width considering 1.5" margins on both sides
        // US Letter = 8.5", minus 3" for margins = 5.5" available width
        $availablePageWidth = Converter::inchToTwip(5.5);
        
        // Header layout: Logo left, Company details right
        $headerTable = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 50,
            'width' => $availablePageWidth,
            'unit' => 'dxa' // dxa is the correct unit for twips in PhpWord
        ]);
        
        $headerRow = $headerTable->addRow(Converter::inchToTwip(1.5));

        // Logo cell - 2 inches width
        $logoWidth = Converter::inchToTwip(2.0);
        $logoCell = $headerRow->addCell($logoWidth, [
            'valign' => 'center',
            'borderBottomSize' => 1,
            'borderBottomColor' => '000000'
        ]);
        
        if (!empty($data['logo_path'])) {
            try {
                // Logo can use most of the 2-inch space
                $logoCell->addImage($data['logo_path'], [
                    'width' => 130, // Points - about 1.8 inches
                    'height' => 130,
                    'alignment' => Jc::LEFT
                ]);
            } catch (Exception $e) {
                // Fallback to company name
                $logoCell->addText($data['company_name'], [
                    'name' => 'Times New Roman',
                    'size' => 16,
                    'bold' => true,
                    'color' => '000000'
                ], ['alignment' => Jc::LEFT]);
            }
        } else {
            // Show company name if no logo
            $logoCell->addText($data['company_name'], [
                'name' => 'Times New Roman',
                'size' => 16,
                'bold' => true,
                'color' => '000000'
            ], ['alignment' => Jc::LEFT]);
        }

        // Company details cell - remaining width (3.5 inches)
        $detailsWidth = $availablePageWidth - $logoWidth;
        $detailsCell = $headerRow->addCell($detailsWidth, [
            'valign' => 'center',
            'borderBottomSize' => 1,
            'borderBottomColor' => '000000'
        ]);

        // If logo exists, add company name at top of details
        if (!empty($data['logo_path'])) {
            $detailsCell->addText($data['company_name'], [
                'name' => 'Times New Roman',
                'size' => 18,
                'bold' => true,
                'color' => '000000'
            ], ['alignment' => Jc::RIGHT]);
            $detailsCell->addTextBreak(1);
        }

        // Company address
        $addressLines = explode("\n", $data['address']);
        foreach ($addressLines as $line) {
            $detailsCell->addText(trim($line), [
                'name' => 'Times New Roman',
                'size' => 11,
                'color' => '000000'
            ], ['alignment' => Jc::RIGHT]);
        }

        // Add contact details
        if (!empty($data['phone'])) {
            $detailsCell->addTextBreak(1);
            $detailsCell->addText('Tel: ' . $data['phone'], [
                'name' => 'Times New Roman',
                'size' => 11,
                'color' => '000000'
            ], ['alignment' => Jc::RIGHT]);
        }
        
        if (!empty($data['email'])) {
            $detailsCell->addTextBreak(1);
            $detailsCell->addText('Email: ' . $data['email'], [
                'name' => 'Times New Roman',
                'size' => 11,
                'color' => '000000'
            ], ['alignment' => Jc::RIGHT]);
        }
        
        if (!empty($data['website'])) {
            $detailsCell->addTextBreak(1);
            $detailsCell->addText('Web: ' . $data['website'], [
                'name' => 'Times New Roman',
                'size' => 11,
                'color' => '000000'
            ], ['alignment' => Jc::RIGHT]);
        }

        $section->addTextBreak(2);

        self::addLetterContent($section, $data);

        return $phpWord;
    }

    private static function addLetterContent($section, array $data): void
    {
        // Add date
        $section->addText('Date: ' . now()->format('F d, Y'), [
            'name' => 'Arial',
            'size' => 11
        ]);
        $section->addTextBreak(2);

        // Add recipient information if provided
        if (!empty($data['recipient_name']) || !empty($data['recipient_address'])) {
            $section->addText('To:', [
                'name' => 'Arial',
                'size' => 11,
                'bold' => true
            ]);
            
            if (!empty($data['recipient_name'])) {
                $section->addText($data['recipient_name'], [
                    'name' => 'Arial',
                    'size' => 11,
                    'bold' => true,
                    'color' => '2E7D32'
                ]);
            }
            
            if (!empty($data['recipient_address'])) {
                $recipientLines = explode("\n", $data['recipient_address']);
                foreach ($recipientLines as $line) {
                    $section->addText(trim($line), [
                        'name' => 'Arial',
                        'size' => 11
                    ]);
                }
            }
            $section->addTextBreak(2);
        }

        // Add letter content
        if (!empty($data['letter_content'])) {
            self::addFormattedContent($section, $data['letter_content']);
        } else {
            // Add placeholder content
            $section->addText('Dear [Recipient],', ['name' => 'Arial', 'size' => 11]);
            $section->addTextBreak(2);
            $section->addText('[Your letter content goes here]', ['name' => 'Arial', 'size' => 11]);
            $section->addTextBreak(2);
            $section->addText('Sincerely,', ['name' => 'Arial', 'size' => 11]);
            $section->addTextBreak(3);
            $section->addText('_______________________', ['name' => 'Arial', 'size' => 11]);
            $section->addTextBreak();
            $section->addText('[Your Name]', ['name' => 'Arial', 'size' => 11]);
            $section->addText('[Your Title]', ['name' => 'Arial', 'size' => 11]);
        }
    }

    private static function addFormattedContent($section, string $htmlContent): void
    {
        // Convert HTML content to Word-compatible format
        $content = $htmlContent;
        
        // Handle tables first (before other processing)
        if (preg_match_all('/<table[^>]*>(.*?)<\/table>/is', $content, $tableMatches)) {
            foreach ($tableMatches[0] as $index => $fullTableHtml) {
                $tableContent = $tableMatches[1][$index];
                $tableId = "TABLE_PLACEHOLDER_" . $index;
                $content = str_replace($fullTableHtml, $tableId, $content);
                
                // Process the table after main content
                $section->addTextBreak();
                self::addTableToSection($section, $tableContent);
                $section->addTextBreak();
                
                // Replace the placeholder with empty string
                $content = str_replace($tableId, '', $content);
            }
        }
        
        // Replace HTML line breaks with newlines
        $content = preg_replace('/<br\s*\/?>/i', "\n", $content);
        $content = preg_replace('/<\/p>\s*<p[^>]*>/i', "\n\n", $content);
        $content = str_replace(['</p>', '<p>', '<p>'], ["\n", '', ''], $content);
        
        // Handle ordered lists (numbered)
        if (preg_match_all('/<ol[^>]*>(.*?)<\/ol>/is', $content, $olMatches)) {
            foreach ($olMatches[0] as $index => $fullListHtml) {
                $listContent = $olMatches[1][$index];
                $listId = "OL_PLACEHOLDER_" . $index;
                $content = str_replace($fullListHtml, $listId, $content);
                
                // Process ordered list items
                preg_match_all('/<li[^>]*>(.*?)<\/li>/is', $listContent, $liMatches);
                $listItems = [];
                foreach ($liMatches[1] as $liIndex => $liContent) {
                    $listItems[] = ($liIndex + 1) . ". " . strip_tags(trim($liContent));
                }
                $content = str_replace($listId, "\n" . implode("\n", $listItems) . "\n", $content);
            }
        }
        
        // Handle unordered lists (bulleted)
        if (preg_match_all('/<ul[^>]*>(.*?)<\/ul>/is', $content, $ulMatches)) {
            foreach ($ulMatches[0] as $index => $fullListHtml) {
                $listContent = $ulMatches[1][$index];
                $listId = "UL_PLACEHOLDER_" . $index;
                $content = str_replace($fullListHtml, $listId, $content);
                
                // Process unordered list items
                preg_match_all('/<li[^>]*>(.*?)<\/li>/is', $listContent, $liMatches);
                $listItems = [];
                foreach ($liMatches[1] as $liContent) {
                    $listItems[] = "• " . strip_tags(trim($liContent));
                }
                $content = str_replace($listId, "\n" . implode("\n", $listItems) . "\n", $content);
            }
        }
        
        // Handle divs as paragraphs
        $content = preg_replace('/<\/div>\s*<div[^>]*>/i', "\n", $content);
        $content = str_replace(['<div>', '</div>'], ['', "\n"], $content);
        
        // Process text formatting
        $lines = explode("\n", $content);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                $section->addTextBreak();
                continue;
            }
            
            // Check for formatting in the line
            if (preg_match('/<(b|strong|i|em|u)[^>]*>/', $line)) {
                self::addFormattedLine($section, $line);
            } else {
                // Plain text line
                $cleanLine = strip_tags($line);
                $cleanLine = html_entity_decode($cleanLine, ENT_QUOTES, 'UTF-8');
                
                if (!empty($cleanLine)) {
                    $section->addText($cleanLine, [
                        'name' => 'Arial',
                        'size' => 11,
                        'lineHeight' => 1.15
                    ]);
                }
            }
            $section->addTextBreak();
        }
    }
    
    private static function addTableToSection($section, string $tableHtml): void
    {
        // Parse table content
        preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $tableHtml, $rowMatches);
        
        if (empty($rowMatches[1])) {
            return;
        }
        
        $tableData = [];
        $maxCols = 0;
        
        // Parse rows and cells
        foreach ($rowMatches[1] as $rowHtml) {
            preg_match_all('/<t[hd][^>]*>(.*?)<\/t[hd]>/is', $rowHtml, $cellMatches);
            $row = [];
            foreach ($cellMatches[1] as $cellHtml) {
                $row[] = trim(strip_tags($cellHtml));
            }
            if (!empty($row)) {
                $tableData[] = $row;
                $maxCols = max($maxCols, count($row));
            }
        }
        
        if (empty($tableData) || $maxCols == 0) {
            return;
        }
        
        // Create table in Word document
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '333333',
            'cellMargin' => 80,
            'width' => 5000 // Full width in twips
        ]);
        
        foreach ($tableData as $rowIndex => $rowData) {
            $table->addRow();
            
            // Pad row to max columns
            while (count($rowData) < $maxCols) {
                $rowData[] = '';
            }
            
            foreach ($rowData as $colIndex => $cellData) {
                // Calculate cell width in twips (equal distribution)
                $cellWidth = floor(5000 / $maxCols);
                
                $cellStyle = [
                    'valign' => 'top',
                    'width' => $cellWidth
                ];
                
                // Header row styling
                if ($rowIndex === 0) {
                    $cellStyle['bgColor'] = 'F5F5F5';
                }
                
                $cell = $table->addCell($cellWidth, $cellStyle);
                $cell->addText($cellData, [
                    'name' => 'Arial',
                    'size' => 10,
                    'bold' => $rowIndex === 0
                ]);
            }
        }
    }
    
    private static function addFormattedLine($section, string $line): void
    {
        // Simple approach: split by formatting tags and add with appropriate styling
        $textRun = $section->addTextRun([
            'lineHeight' => 1.15
        ]);
        
        // For now, strip all formatting and add as plain text to avoid Word corruption
        // This ensures the document opens properly
        $cleanLine = strip_tags($line);
        $cleanLine = html_entity_decode($cleanLine, ENT_QUOTES, 'UTF-8');
        
        if (!empty($cleanLine)) {
            $textRun->addText($cleanLine, [
                'name' => 'Arial',
                'size' => 11
            ]);
        }
    }
    
    private static function getBetaSectionOptions(array $data): array
    {
        $paperSize = $data['paper_size'] ?? 'letter';
        $paperSizes = self::getPaperSizes();
        
        // Beta version: Improved margins
        $sectionOptions = [
            'marginLeft' => Converter::inchToTwip(0.5),
            'marginRight' => Converter::inchToTwip(0.5), 
            'marginTop' => Converter::inchToTwip(0.3),
            'marginBottom' => Converter::inchToTwip(0.5),
        ];
        
        // Set paper size
        if (isset($paperSizes[$paperSize]) && $paperSize !== 'custom') {
            if ($paperSizes[$paperSize]['width'] && $paperSizes[$paperSize]['height']) {
                $sectionOptions['pageSize'] = [
                    'width' => $paperSizes[$paperSize]['width'],
                    'height' => $paperSizes[$paperSize]['height']
                ];
            }
        } elseif ($paperSize === 'custom' && !empty($data['custom_width']) && !empty($data['custom_height'])) {
            $sectionOptions['pageSize'] = [
                'width' => Converter::inchToTwip((float) $data['custom_width']),
                'height' => Converter::inchToTwip((float) $data['custom_height'])
            ];
        }
        
        return $sectionOptions;
    }
    
    private static function addBetaLogo($logoCell, array $data, string $color = '2E7D32', string $bgColor = 'FFFFFF'): void
    {
        $paperSize = $data['paper_size'] ?? 'letter';
        $paperHeight = ($paperSize === 'letter') ? 11 : (($paperSize === 'a4') ? 11.7 : 11);
        $headerHeight = $paperHeight / 5;
        
        if (!empty($data['logo_path'])) {
            try {
                $logoSize = min(Converter::twipToPixel(Converter::inchToTwip($headerHeight * 0.8)), 120);
                $logoCell->addImage($data['logo_path'], [
                    'width' => $logoSize,
                    'height' => $logoSize,
                    'alignment' => Jc::CENTER,
                    'marginTop' => 100,
                    'marginBottom' => 100
                ]);
            } catch (Exception $e) {
                $logoCell->addText('[LOGO]', [
                    'name' => 'Arial',
                    'size' => 18,
                    'bold' => true,
                    'color' => $color
                ], ['alignment' => Jc::CENTER, 'spaceBefore' => 200, 'spaceAfter' => 200]);
            }
        } else {
            $initial = strtoupper(substr($data['company_name'], 0, 1));
            $logoCell->addText($initial, [
                'name' => 'Arial',
                'size' => 56,
                'bold' => true,
                'color' => $color === 'FFFFFF' ? $color : 'FFFFFF'
            ], ['alignment' => Jc::CENTER, 'spaceBefore' => 150, 'spaceAfter' => 150]);
        }
    }
}