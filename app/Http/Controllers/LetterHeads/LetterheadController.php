<?php

/**
 * Letterhead Controller
 * 
 * Main controller for letterhead generation functionality. Handles form display,
 * request validation, file processing, and coordinates between PDF and Word
 * document generation services.
 * 
 * Features:
 * - Form rendering with template options
 * - Multi-format request validation (PDF/Word)
 * - Logo file upload processing with security checks
 * - Service orchestration for document generation
 * - Error handling and user feedback
 * - Temporary file management and cleanup
 * 
 * Routes Handled:
 * - GET /letterhead - Display generation form
 * - POST /letterhead/generate - Process form and generate document
 * 
 * @package     App\Http\Controllers\LetterHeads
 * @category    Web Controller
 * @author      Metasoftdevs <info@metasoftdevs.com>
 * @copyright   2025 Metasoft Developers
 * @license     MIT License
 * @version     1.0.0
 * @link        https://www.metasoftdevs.com
 * @since       File available since Release 1.0.0
 * 
 * @see         \App\Services\LetterheadTemplateService Word document generation
 * @see         \App\Services\PdfLetterheadService PDF document generation
 * @see         \Illuminate\Http\Request Laravel request handling
 */

namespace App\Http\Controllers\LetterHeads;

use App\Http\Controllers\Controller;
use App\Services\LetterheadTemplateService;
use App\Services\PdfLetterheadService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\IOFactory;

class LetterheadController extends Controller
{
    public function showForm()
    {
        $templates = LetterheadTemplateService::getAvailableTemplates();
        return view('letterheads.form', compact('templates'));
    }

    public function generateLetterhead(Request $request)
    {
        $request->validate([
            'template' => 'required|string|in:classic,modern_green,corporate_blue,elegant_gray',
            'company_name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email',
            'website' => 'nullable|url',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'recipient_name' => 'nullable|string|max:255',
            'recipient_title' => 'nullable|string|max:255',
            'recipient_address' => 'nullable|string',
            'letter_content' => 'nullable|string',
            'paper_size' => 'required|string|in:us_letter,a4,legal,custom',
            'custom_width' => 'required_if:paper_size,custom|nullable|numeric|min:1|max:20',
            'custom_height' => 'required_if:paper_size,custom|nullable|numeric|min:1|max:30',
            'output_format' => 'required|string|in:pdf,word',
        ]);

        try {
            // Prepare data array for template service
            $data = [
                'company_name' => $request->company_name,
                'address' => $request->address,
                'phone' => $request->phone,
                'email' => $request->email,
                'website' => $request->website,
                'recipient_name' => $request->recipient_name,
                'recipient_title' => $request->recipient_title,
                'recipient_address' => $request->recipient_address,
                'letter_content' => $request->letter_content,
                'paper_size' => $request->paper_size,
                'custom_width' => $request->custom_width,
                'custom_height' => $request->custom_height,
                'output_format' => $request->output_format,
                'logo_path' => null,
            ];

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('temp', 'public');
                $data['logo_path'] = storage_path('app/public/' . $logoPath);
            }

            // Generate letterhead based on selected format
            if ($request->output_format === 'pdf') {
                // Generate PDF letterhead
                $response = PdfLetterheadService::generateLetterhead($request->template, $data);

                // Clean up temp logo file if it exists
                if (!empty($data['logo_path']) && file_exists($data['logo_path'])) {
                    register_shutdown_function(function () use ($data) {
                        if (file_exists($data['logo_path'])) {
                            unlink($data['logo_path']);
                        }
                    });
                }

                return $response;
            } else {
                // Generate Word letterhead (original functionality)
                $phpWord = LetterheadTemplateService::generateLetterhead($request->template, $data);

                // Generate filename based on template and company
                $templateName = str_replace('_', '-', $request->template);
                $filename = 'letterhead_' . $templateName . '_' .
                    str_replace(' ', '_', strtolower($request->company_name)) . '_' .
                    now()->format('Y-m-d') . '.docx';

                // Create writer and return download response
                $objWriter = IOFactory::createWriter($phpWord, 'Word2007');

                // Clean up temp logo file if it exists
                if (!empty($data['logo_path']) && file_exists($data['logo_path'])) {
                    register_shutdown_function(function () use ($data) {
                        if (file_exists($data['logo_path'])) {
                            unlink($data['logo_path']);
                        }
                    });
                }

                return response()->streamDownload(function () use ($objWriter) {
                    $objWriter->save('php://output');
                }, $filename, [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    'Cache-Control' => 'max-age=0',
                    'Pragma' => 'public',
                ]);
            }
        } catch (Exception $e) {
            // Log the error
            Log::error('Letterhead generation failed: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to generate letterhead: ' . $e->getMessage()]);
        }
    }
}
