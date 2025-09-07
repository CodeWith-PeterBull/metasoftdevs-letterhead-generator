{{-- 
    Letterhead Generation Form View
    
    Interactive form interface for professional letterhead creation.
    Provides comprehensive user experience with template selection,
    paper size configuration, logo upload, and rich text editing.
    
    Features:
    - Visual template selection with preview cards
    - Dynamic paper size options with custom dimensions
    - Output format selection (PDF/Word)
    - Company information input with validation
    - Logo upload with file type restrictions
    - Rich text editor (Summernote) integration
    - Real-time form validation and user feedback
    - Progressive enhancement with JavaScript
    
    Template: letterheads/form.blade.php
    Layout: layouts.letterhead
    Route: letterhead.form (GET)
    Action: letterhead.generate (POST)
    
    @package     MetaSoft Letterhead Generator
    @category    View Template
    @author      Metasoftdevs <info@metasoftdevs.com>
    @copyright   2025 Metasoft Developers
    @license     MIT License
    @version     1.0.0
    @link        https://www.metasoftdevs.com
    @since       File available since Release 1.0.0
--}}
@extends('layouts.letterhead')

@section('title', 'Generate Letterhead')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-file-word"></i> Generate Company Letterhead <span class="badge bg-success">Beta</span></h4>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('letterhead.generate') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <!-- Template Selection -->
                            <div class="mb-4">
                                <label for="template" class="form-label">Choose Template Style *</label>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="card template-option" onclick="selectTemplate('classic')">
                                            <div class="card-body text-center">
                                                <input type="radio" name="template" id="template_classic" value="classic" checked hidden>
                                                <i class="fas fa-file-alt fa-3x text-secondary mb-2"></i>
                                                <h6 class="card-title">Classic Business</h6>
                                                <p class="card-text small">Traditional formal business letterhead</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="card template-option" onclick="selectTemplate('modern_green')">
                                            <div class="card-body text-center">
                                                <input type="radio" name="template" id="template_modern_green" value="modern_green" hidden>
                                                <i class="fas fa-leaf fa-3x text-success mb-2"></i>
                                                <h6 class="card-title">Modern Green Minimalist</h6>
                                                <p class="card-text small">Clean modern design with green accents</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="card template-option" onclick="selectTemplate('corporate_blue')">
                                            <div class="card-body text-center">
                                                <input type="radio" name="template" id="template_corporate_blue" value="corporate_blue" hidden>
                                                <i class="fas fa-building fa-3x text-primary mb-2"></i>
                                                <h6 class="card-title">Corporate Blue</h6>
                                                <p class="card-text small">Professional blue-themed letterhead</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="card template-option" onclick="selectTemplate('elegant_gray')">
                                            <div class="card-body text-center">
                                                <input type="radio" name="template" id="template_elegant_gray" value="elegant_gray" hidden>
                                                <i class="fas fa-crown fa-3x text-dark mb-2"></i>
                                                <h6 class="card-title">Elegant Gray</h6>
                                                <p class="card-text small">Sophisticated gray and black design</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Paper Size Selection -->
                            <div class="mb-4">
                                <label class="form-label">Paper Size *</label>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <div class="card paper-size-option" onclick="selectPaperSize('us_letter')">
                                            <div class="card-body text-center">
                                                <input type="radio" name="paper_size" id="paper_size_us_letter" value="us_letter" checked hidden>
                                                <i class="fas fa-file fa-2x text-primary mb-2"></i>
                                                <h6 class="card-title">US Letter</h6>
                                                <p class="card-text small">8.5" × 11"</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card paper-size-option" onclick="selectPaperSize('a4')">
                                            <div class="card-body text-center">
                                                <input type="radio" name="paper_size" id="paper_size_a4" value="a4" hidden>
                                                <i class="fas fa-file fa-2x text-success mb-2"></i>
                                                <h6 class="card-title">A4</h6>
                                                <p class="card-text small">210 × 297 mm</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card paper-size-option" onclick="selectPaperSize('legal')">
                                            <div class="card-body text-center">
                                                <input type="radio" name="paper_size" id="paper_size_legal" value="legal" hidden>
                                                <i class="fas fa-file-alt fa-2x text-warning mb-2"></i>
                                                <h6 class="card-title">Legal</h6>
                                                <p class="card-text small">8.5" × 14"</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card paper-size-option" onclick="selectPaperSize('custom')">
                                            <div class="card-body text-center">
                                                <input type="radio" name="paper_size" id="paper_size_custom" value="custom" hidden>
                                                <i class="fas fa-expand-arrows-alt fa-2x text-secondary mb-2"></i>
                                                <h6 class="card-title">Custom</h6>
                                                <p class="card-text small">Custom size</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Custom Dimensions (Hidden by default) -->
                                <div id="custom_dimensions" class="row mt-3" style="display: none;">
                                    <div class="col-md-6">
                                        <label for="custom_width" class="form-label">Width (inches)</label>
                                        <input type="number" step="0.1" min="1" max="20" class="form-control" id="custom_width" name="custom_width" placeholder="8.5">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="custom_height" class="form-label">Height (inches)</label>
                                        <input type="number" step="0.1" min="1" max="30" class="form-control" id="custom_height" name="custom_height" placeholder="11.0">
                                    </div>
                                </div>
                            </div>

                            <!-- Output Format Selection -->
                            <div class="mb-4">
                                <label class="form-label">Output Format *</label>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="card format-option" onclick="selectFormat('pdf')">
                                            <div class="card-body text-center">
                                                <input type="radio" name="output_format" id="format_pdf" value="pdf" checked hidden>
                                                <i class="fas fa-file-pdf fa-2x text-danger mb-2"></i>
                                                <h6 class="card-title">PDF Document</h6>
                                                <p class="card-text small">Portable, web-friendly format</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="card format-option" onclick="selectFormat('word')">
                                            <div class="card-body text-center">
                                                <input type="radio" name="output_format" id="format_word" value="word" hidden>
                                                <i class="fas fa-file-word fa-2x text-primary mb-2"></i>
                                                <h6 class="card-title">Word Document</h6>
                                                <p class="card-text small">Editable Microsoft Word format</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="company_name" class="form-label">Company Name *</label>
                                        <input type="text" class="form-control" id="company_name" name="company_name"
                                            value="{{ old('company_name') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="logo" class="form-label">Company Logo</label>
                                        <input type="file" class="form-control" id="logo" name="logo"
                                            accept="image/jpeg,image/png,image/jpg">
                                        <small class="form-text text-muted">Optional: Upload PNG, JPG, or JPEG (max
                                            2MB)</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Company Address *</label>
                                <textarea class="form-control" id="address" name="address" rows="3" required>{{ old('address') }}</textarea>
                                <small class="form-text text-muted">Enter full address (use line breaks for multiple
                                    lines)</small>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="text" class="form-control" id="phone" name="phone"
                                            value="{{ old('phone') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            value="{{ old('email') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="website" class="form-label">Website</label>
                                        <input type="url" class="form-control" id="website" name="website"
                                            value="{{ old('website') }}" placeholder="https://example.com">
                                    </div>
                                </div>
                            </div>

                            <!-- Recipient Information -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-user"></i> Recipient Information (Optional)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="recipient_name" class="form-label">Recipient Name</label>
                                                <input type="text" class="form-control" id="recipient_name" name="recipient_name"
                                                    value="{{ old('recipient_name') }}" placeholder="e.g., Richard Sanchez">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="recipient_title" class="form-label">Recipient Title</label>
                                                <input type="text" class="form-control" id="recipient_title" name="recipient_title"
                                                    value="{{ old('recipient_title') }}" placeholder="e.g., General Manager">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="recipient_address" class="form-label">Recipient Address</label>
                                        <textarea class="form-control" id="recipient_address" name="recipient_address" rows="3"
                                            placeholder="123 Anywhere St., Any City, ST 12345">{{ old('recipient_address') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="letter_content" class="form-label">Letter Content</label>
                                <textarea class="form-control" id="letter_content" name="letter_content" rows="12">{{ old('letter_content', 'Dear [Recipient Name],

I hope this letter finds you well. I am writing to...

[Your main message content goes here]

Thank you for your time and consideration. I look forward to hearing from you soon.

Sincerely,

[Your Name]
[Your Title]') }}</textarea>
                                <small class="form-text text-muted">Use the rich text editor to format your letter content. You can use placeholders like [Recipient Name] which will be automatically replaced.</small>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-download"></i> Generate & Download Letterhead
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-info-circle"></i> Instructions:</h6>
                            <ul class="mb-0 small">
                                <li>Fill in your company information above</li>
                                <li>Optionally upload a logo (PNG, JPG, or JPEG format)</li>
                                <li>Add letter content or leave blank for a template</li>
                                <li>Click "Generate & Download" to create your Word document</li>
                                <li>The generated document will include your letterhead and can be customized further in
                                    Word</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            .template-option {
                cursor: pointer;
                transition: all 0.3s ease;
                border: 2px solid transparent;
            }
            .template-option:hover {
                border-color: #007bff;
                box-shadow: 0 4px 12px rgba(0,123,255,0.15);
                transform: translateY(-2px);
            }
            .template-option.selected {
                border-color: #28a745;
                background-color: #f8f9fa;
                box-shadow: 0 4px 12px rgba(40,167,69,0.15);
            }
            .template-option.selected .card-body {
                position: relative;
            }
            .template-option.selected .card-body::after {
                content: '\f00c';
                font-family: 'Font Awesome 6 Free';
                font-weight: 900;
                position: absolute;
                top: 10px;
                right: 15px;
                color: #28a745;
                font-size: 1.2em;
            }
            
            .paper-size-option {
                cursor: pointer;
                transition: all 0.3s ease;
                border: 2px solid transparent;
            }
            .paper-size-option:hover {
                border-color: #007bff;
                box-shadow: 0 4px 12px rgba(0,123,255,0.15);
                transform: translateY(-2px);
            }
            .paper-size-option.selected {
                border-color: #28a745;
                background-color: #f8f9fa;
                box-shadow: 0 4px 12px rgba(40,167,69,0.15);
            }
            .paper-size-option.selected .card-body {
                position: relative;
            }
            .paper-size-option.selected .card-body::after {
                content: '\f00c';
                font-family: 'Font Awesome 6 Free';
                font-weight: 900;
                position: absolute;
                top: 10px;
                right: 15px;
                color: #28a745;
                font-size: 1.2em;
            }
            
            .format-option {
                cursor: pointer;
                transition: all 0.3s ease;
                border: 2px solid transparent;
            }
            .format-option:hover {
                border-color: #007bff;
                box-shadow: 0 4px 12px rgba(0,123,255,0.15);
                transform: translateY(-2px);
            }
            .format-option.selected {
                border-color: #28a745;
                background-color: #f8f9fa;
                box-shadow: 0 4px 12px rgba(40,167,69,0.15);
            }
            .format-option.selected .card-body {
                position: relative;
            }
            .format-option.selected .card-body::after {
                content: '\f00c';
                font-family: 'Font Awesome 6 Free';
                font-weight: 900;
                position: absolute;
                top: 10px;
                right: 15px;
                color: #28a745;
                font-size: 1.2em;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            // Method 1: Basic initialization with delay
            $(document).ready(function() {
                console.log('Document ready fired');
                console.log('jQuery version:', typeof $ !== 'undefined' ? $.fn.jquery : 'Not loaded');
                console.log('Target element exists:', $('#letter_content').length > 0);
                
                // Wait for all scripts to load
                setTimeout(function() {
                    console.log('Checking Summernote availability...');
                    console.log('jQuery available:', typeof $ !== 'undefined');
                    console.log('Bootstrap available:', typeof bootstrap !== 'undefined');
                    console.log('Summernote plugin:', typeof $.fn.summernote !== 'undefined' ? 'Available' : 'Not available');
                    console.log('Summernote version:', $.fn.summernote ? $.fn.summernote.version : 'N/A');
                    
                    if (typeof $.fn.summernote !== 'undefined') {
                        console.log('Initializing Summernote...');
                        
                        // Enhanced Summernote initialization with full features
                        $('#letter_content').summernote({
                            height: 300,
                            placeholder: 'Enter your letter content here...',
                            tabsize: 2,
                            focus: false,
                            toolbar: [
                                ['style', ['style']],
                                ['font', ['bold', 'italic', 'underline', 'clear']],
                                ['fontname', ['fontname']],
                                ['fontsize', ['fontsize']],
                                ['color', ['color']],
                                ['para', ['ul', 'ol', 'paragraph']],
                                ['table', ['table']],
                                ['insert', ['link', 'picture', 'hr']],
                                ['view', ['fullscreen', 'codeview', 'help']]
                            ],
                            fontNames: [
                                'Arial', 'Arial Black', 'Comic Sans MS', 'Courier New',
                                'Helvetica Neue', 'Helvetica', 'Impact', 'Lucida Grande', 'Tahoma', 
                                'Times New Roman', 'Verdana', 'Georgia', 'Trebuchet MS'
                            ],
                            fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '20', '22', '24', '26', '28', '30', '32', '36', '40', '44', '48', '54', '60', '66', '72', '80'],
                            lineHeights: ['0.2', '0.3', '0.4', '0.5', '0.6', '0.8', '1.0', '1.2', '1.4', '1.5', '2.0', '3.0'],
                            styleTags: [
                                'p',
                                { title: 'Blockquote', tag: 'blockquote', className: 'blockquote', value: 'blockquote' },
                                'pre', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'
                            ],
                            callbacks: {
                                onInit: function() {
                                    console.log('✅ Enhanced Summernote initialized successfully!');
                                    console.log('Toolbar buttons:', $('.note-toolbar .btn').length);
                                },
                                onChange: function(contents, $editable) {
                                    console.log('Content changed');
                                }
                            }
                        });
                    } else {
                        console.error('❌ Summernote not available - check CDN links');
                        // Fallback: Show a note to user
                        $('#letter_content').after('<div class="alert alert-warning mt-2">Rich text editor is not available. You can still type your letter content in the text area above.</div>');
                    }
                }, 500);

                // Initialize template selection
                $('.template-option').first().addClass('selected');
                
                // Initialize paper size selection
                $('.paper-size-option').first().addClass('selected');
                
                // Initialize format selection
                $('.format-option').first().addClass('selected');
            });
            
            // Method 2: Load event fallback
            $(window).on('load', function() {
                console.log('Window loaded - checking Summernote again...');
                if (typeof $.fn.summernote !== 'undefined' && !$('#letter_content').hasClass('note-editable')) {
                    console.log('Attempting fallback initialization...');
                    $('#letter_content').summernote({
                        height: 300,
                        placeholder: 'Enter your letter content here...',
                        tabsize: 2,
                        focus: false,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'italic', 'underline', 'clear']],
                            ['fontname', ['fontname']],
                            ['fontsize', ['fontsize']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['table', ['table']],
                            ['insert', ['link', 'picture', 'hr']],
                            ['view', ['fullscreen', 'codeview', 'help']]
                        ],
                        fontNames: [
                            'Arial', 'Arial Black', 'Comic Sans MS', 'Courier New',
                            'Helvetica Neue', 'Helvetica', 'Impact', 'Lucida Grande', 'Tahoma', 
                            'Times New Roman', 'Verdana', 'Georgia', 'Trebuchet MS'
                        ],
                        fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '20', '22', '24', '26', '28', '30', '32', '36', '40', '44', '48', '54', '60', '66', '72', '80']
                    });
                }
            });

            function selectTemplate(template) {
                // Remove selected class from all templates
                $('.template-option').removeClass('selected');
                
                // Add selected class to clicked template
                $('#template_' + template).closest('.template-option').addClass('selected');
                
                // Update radio button
                $('input[name="template"]').prop('checked', false);
                $('#template_' + template).prop('checked', true);
            }

            function selectPaperSize(paperSize) {
                // Remove selected class from all paper sizes
                $('.paper-size-option').removeClass('selected');
                
                // Add selected class to clicked paper size
                $('#paper_size_' + paperSize).closest('.paper-size-option').addClass('selected');
                
                // Update radio button
                $('input[name="paper_size"]').prop('checked', false);
                $('#paper_size_' + paperSize).prop('checked', true);
                
                // Show/hide custom dimensions
                if (paperSize === 'custom') {
                    $('#custom_dimensions').show();
                    $('#custom_width').prop('required', true);
                    $('#custom_height').prop('required', true);
                } else {
                    $('#custom_dimensions').hide();
                    $('#custom_width').prop('required', false);
                    $('#custom_height').prop('required', false);
                }
            }

            function selectFormat(format) {
                // Remove selected class from all formats
                $('.format-option').removeClass('selected');
                
                // Add selected class to clicked format
                $('#format_' + format).closest('.format-option').addClass('selected');
                
                // Update radio button
                $('input[name="output_format"]').prop('checked', false);
                $('#format_' + format).prop('checked', true);
            }

            // Form validation and submission handling
            $('form').on('submit', function(e) {
                var form = $(this);
                var submitBtn = form.find('button[type="submit"]');
                var originalBtnText = submitBtn.html();
                
                var companyName = $('#company_name').val().trim();
                var address = $('#address').val().trim();
                var paperSize = $('input[name="paper_size"]:checked').val();
                
                if (!companyName) {
                    alert('Please enter the company name.');
                    $('#company_name').focus();
                    e.preventDefault();
                    return false;
                }
                
                if (!address) {
                    alert('Please enter the company address.');
                    $('#address').focus();
                    e.preventDefault();
                    return false;
                }
                
                if (paperSize === 'custom') {
                    var customWidth = $('#custom_width').val();
                    var customHeight = $('#custom_height').val();
                    
                    if (!customWidth || customWidth < 1 || customWidth > 20) {
                        alert('Please enter a valid width (1-20 inches).');
                        $('#custom_width').focus();
                        e.preventDefault();
                        return false;
                    }
                    
                    if (!customHeight || customHeight < 1 || customHeight > 30) {
                        alert('Please enter a valid height (1-30 inches).');
                        $('#custom_height').focus();
                        e.preventDefault();
                        return false;
                    }
                }
                
                // Show loading spinner
                submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Generating...').prop('disabled', true);
                
                // Set up download completion detection
                var downloadTimer = setInterval(function() {
                    // Check if download started by looking for iframe or blob URL
                    if (document.querySelector('iframe') || window.downloadStarted) {
                        clearInterval(downloadTimer);
                        // Reset button after download starts
                        setTimeout(function() {
                            submitBtn.html(originalBtnText).prop('disabled', false);
                        }, 1000);
                    }
                }, 100);
                
                // Fallback: Reset button after 10 seconds regardless
                setTimeout(function() {
                    clearInterval(downloadTimer);
                    submitBtn.html(originalBtnText).prop('disabled', false);
                }, 10000);
            });
            
            // Better approach: Use Page Visibility API to detect when user returns to tab
            $(document).on('visibilitychange', function() {
                if (!document.hidden) {
                    // User returned to the tab, likely after download
                    var submitBtn = $('button[type="submit"]');
                    if (submitBtn.prop('disabled')) {
                        setTimeout(function() {
                            var originalBtnText = '<i class="fas fa-download"></i> Generate & Download Letterhead';
                            submitBtn.html(originalBtnText).prop('disabled', false);
                        }, 500);
                    }
                }
            });
            
            // Also listen for window focus (fallback)
            $(window).on('focus', function() {
                var submitBtn = $('button[type="submit"]');
                if (submitBtn.prop('disabled') && submitBtn.text().includes('Generating')) {
                    setTimeout(function() {
                        var originalBtnText = '<i class="fas fa-download"></i> Generate & Download Letterhead';
                        submitBtn.html(originalBtnText).prop('disabled', false);
                    }, 1000);
                }
            });
        </script>
    @endpush
@endsection
